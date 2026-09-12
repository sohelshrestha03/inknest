<?php
session_start();
include "../config/database.php";

header("Content-Type: application/json");

if (!isset($_SESSION["admin_id"])) {
    echo json_encode([
        "success" => false,
        "message" => "Unauthorized access."
    ]);
    exit();
}

$adminId = (int)$_SESSION["admin_id"];

$productId = isset($_POST["product_id"])
    ? (int)$_POST["product_id"]
    : 0;

$action = isset($_POST["action"])
    ? trim($_POST["action"])
    : "";

$quantity = isset($_POST["quantity"])
    ? (int)$_POST["quantity"]
    : 0;


if ($productId <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid product ID."
    ]);
    exit();
}


if (!in_array($action, ["add", "reduce", "set"], true)) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid stock action."
    ]);
    exit();
}


if ($quantity < 0) {
    echo json_encode([
        "success" => false,
        "message" => "Quantity cannot be negative."
    ]);
    exit();
}

if (($action === "add" || $action === "reduce") && $quantity <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Quantity must be greater than 0."
    ]);
    exit();
}


try {
    mysqli_begin_transaction($conn);
    $stmt = mysqli_prepare(
        $conn,
        "SELECT product_name, stock
         FROM products
         WHERE id = ?
         FOR UPDATE"
    );

    if (!$stmt) {
        throw new Exception("Failed to prepare product query.");
    }

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $productId
    );

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $product = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    if (!$product) {
        throw new Exception("Product not found.");
    }


    $productName = $product["product_name"];
    $oldStock = (int)$product["stock"];
    $newStock = $oldStock;
    $activityAction = null;
    $activityQuantity = 0;


    if ($action === "add") {
        $newStock = $oldStock + $quantity;
        $activityAction = "Increase";
        $activityQuantity = $quantity;
    }
    elseif ($action === "reduce") {
        if ($quantity > $oldStock) {
            throw new Exception(
                "Cannot reduce stock by {$quantity}. Current stock is {$oldStock}."
            );
        }
        $newStock = $oldStock - $quantity;
        $activityAction = "Decrease";
        $activityQuantity = $quantity;
    }
    elseif ($action === "set") {
        $newStock = $quantity;
        if ($newStock > $oldStock) {
            $activityAction = "Increase";
            $activityQuantity = $newStock - $oldStock;
        } elseif ($newStock < $oldStock) {
            $activityAction = "Decrease";
            $activityQuantity = $oldStock - $newStock;
        }
    }
    if ($newStock < 0) {
        throw new Exception("Stock cannot be negative.");
    }
    $updateStmt = mysqli_prepare(
        $conn,
        "UPDATE products
         SET stock = ?
         WHERE id = ?"
    );

    if (!$updateStmt) {
        throw new Exception("Failed to prepare stock update.");
    }

    mysqli_stmt_bind_param(
        $updateStmt,
        "ii",
        $newStock,
        $productId
    );
    if (!mysqli_stmt_execute($updateStmt)) {
        mysqli_stmt_close($updateStmt);
        throw new Exception("Failed to update stock.");
    }
    mysqli_stmt_close($updateStmt);

    if ($activityAction !== null && $activityQuantity > 0) {
        $historyStmt = mysqli_prepare(
            $conn,
            "INSERT INTO stock_activity
            (
                admin_id,
                product_id,
                action,
                quantity,
                old_stock,
                new_stock
            )
            VALUES (?, ?, ?, ?, ?, ?)"
        );

        if (!$historyStmt) {
            throw new Exception("Failed to prepare stock history.");
        }

        mysqli_stmt_bind_param(
            $historyStmt,
            "iisiii",
            $adminId,
            $productId,
            $activityAction,
            $activityQuantity,
            $oldStock,
            $newStock
        );

        if (!mysqli_stmt_execute($historyStmt)) {
            mysqli_stmt_close($historyStmt);
            throw new Exception("Failed to record stock history.");
        }
        mysqli_stmt_close($historyStmt);
    }
    mysqli_commit($conn);
    echo json_encode([
        "success" => true,
        "message" => "Stock updated successfully.",
        "product_name" => $productName,
        "old_stock" => $oldStock,
        "stock" => $newStock,
        "action" => $activityAction,
        "quantity" => $activityQuantity
    ]);
} catch (Exception $e) {
    mysqli_rollback($conn);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
exit();
?>