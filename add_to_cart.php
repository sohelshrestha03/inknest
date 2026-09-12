<?php
session_start();
include "config/database.php";
header("Content-Type: application/json");

if (!isset($_SESSION["user_id"])) {
    echo json_encode([
        "success" => false,
        "message" => "Please login first."
    ]);
    exit();
}

$userId = (int) $_SESSION["user_id"];
$productId = isset($_POST["product_id"])
    ? (int) $_POST["product_id"]
    : 0;
$quantity = isset($_POST["quantity"])
    ? (int) $_POST["quantity"]
    : 1;

if ($productId <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid product."
    ]);
    exit();
}

if ($quantity <= 0) {
    $quantity = 1;
}

try {
    mysqli_begin_transaction($conn);
    $productSql = mysqli_prepare(
        $conn,
        "SELECT id, product_name, stock
         FROM products
         WHERE id = ?
         FOR UPDATE"
    );

    if (!$productSql) {
        throw new Exception("Could not prepare product query.");
    }

    mysqli_stmt_bind_param(
        $productSql,
        "i",
        $productId
    );

    if (!mysqli_stmt_execute($productSql)) {
        mysqli_stmt_close($productSql);
        throw new Exception("Could not check product stock.");
    }

    $productResult = mysqli_stmt_get_result($productSql);

    if (!$productResult || mysqli_num_rows($productResult) === 0) {
        mysqli_stmt_close($productSql);
        throw new Exception("Product not found.");
    }

    $product = mysqli_fetch_assoc($productResult);
    $currentStock = (int) $product["stock"];
    $productName = $product["product_name"];

    mysqli_stmt_close($productSql);

    if ($currentStock < $quantity) {
        throw new Exception(
            "Only " . $currentStock . " item(s) available."
        );
    }


    $newStock = $currentStock - $quantity;
    $updateStockSql = mysqli_prepare(
        $conn,
        "UPDATE products
         SET stock = ?
         WHERE id = ?"
    );

    if (!$updateStockSql) {
        throw new Exception("Could not prepare stock update.");
    }

    mysqli_stmt_bind_param(
        $updateStockSql,
        "ii",
        $newStock,
        $productId
    );

    if (!mysqli_stmt_execute($updateStockSql)) {
        mysqli_stmt_close($updateStockSql);
        throw new Exception("Could not update product stock.");
    }

    mysqli_stmt_close($updateStockSql);
    $activityType = "Added to Cart";
    $activitySql = mysqli_prepare(
        $conn,
        "INSERT INTO user_product_activity
            (user_id, product_id, activity_type)
         VALUES (?, ?, ?)"
    );

    if (!$activitySql) {
        throw new Exception(
            "Could not prepare activity query."
        );
    }

    mysqli_stmt_bind_param(
        $activitySql,
        "iis",
        $userId,
        $productId,
        $activityType
    );

    if (!mysqli_stmt_execute($activitySql)) {
        mysqli_stmt_close($activitySql);
        throw new Exception(
            "Could not record product activity."
        );
    }

    mysqli_stmt_close($activitySql);

    mysqli_commit($conn);
    echo json_encode([
        "success" => true,
        "message" => $productName . " added to cart.",
        "product_id" => $productId,
        "quantity" => $quantity,
        "stock" => $newStock
    ]);
    exit();
} catch (Exception $e) {
    mysqli_rollback($conn);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
    exit();
}
?>