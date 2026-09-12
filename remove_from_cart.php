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
    : 0;

if ($productId <= 0 || $quantity <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid product or quantity."
    ]);
    exit();
}

mysqli_begin_transaction($conn);

try {
    $sql = mysqli_prepare(
        $conn,
        "UPDATE products
         SET stock = stock + ?
         WHERE id = ?"
    );


    if (!$sql) {
        throw new Exception("Database error.");
    }

    mysqli_stmt_bind_param(
        $sql,
        "ii",
        $quantity,
        $productId
    );


    if (!mysqli_stmt_execute($sql)) {
        mysqli_stmt_close($sql);
        throw new Exception("Failed to restore stock.");
    }
    mysqli_stmt_close($sql);
    $activityType = "Remove from Cart";
    $activitySql = mysqli_prepare(
        $conn,
        "INSERT INTO user_product_activity
            (user_id, product_id, activity_type)
         VALUES (?, ?, ?)"
    );

    if (!$activitySql) {
        throw new Exception("Could not prepare activity query.");
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
        throw new Exception("Failed to record activity.");
    }

    mysqli_stmt_close($activitySql);
    mysqli_commit($conn);
    echo json_encode([
        "success" => true,
        "message" => "Product removed from cart and stock restored."
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