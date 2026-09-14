<?php
ob_start();
mysqli_report(MYSQLI_REPORT_OFF);
session_start();
include "config/database.php";
header("Content-Type: application/json; charset=UTF-8");
function responseJson($success, $message)
{
    while (ob_get_level()) {
        ob_end_clean();
    }
    echo json_encode([
        "success" => $success,
        "message" => $message
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
if (!isset($_SESSION["user_id"])) {
    responseJson(false, "Please login first.");
}
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    responseJson(false, "Invalid request.");
}
$userId = (int)$_SESSION["user_id"];
$productId = isset($_POST["product_id"])
    ? (int)$_POST["product_id"]
    : 0;
$quantity = isset($_POST["quantity"])
    ? (int)$_POST["quantity"]
    : 0;
if ($productId <= 0) {
    responseJson(false, "Invalid product.");
}
if ($quantity <= 0) {
    responseJson(false, "Invalid quantity.");
}
mysqli_begin_transaction($conn);
try {
    $stockSql = mysqli_prepare(
        $conn,
        "UPDATE products
         SET stock = stock + ?
         WHERE id = ?"
    );
    if (!$stockSql) {
        throw new Exception("Unable to prepare stock query.");
    }
    mysqli_stmt_bind_param(
        $stockSql,
        "ii",
        $quantity,
        $productId
    );
    if (!mysqli_stmt_execute($stockSql)) {
        mysqli_stmt_close($stockSql);
        throw new Exception("Failed to restore product stock.");
    }
    if (mysqli_stmt_affected_rows($stockSql) <= 0) {
        mysqli_stmt_close($stockSql);
        throw new Exception("Product not found.");
    }
    mysqli_stmt_close($stockSql);
    $activityType = "Remove from Cart";
    $activitySql = mysqli_prepare(
        $conn,
        "INSERT INTO user_product_activity
        (user_id, product_id, activity_type)
        VALUES (?, ?, ?)"
    );
    if (!$activitySql) {
        throw new Exception(
            "Unable to prepare activity query."
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
            "Failed to record cart activity."
        );
    }
    mysqli_stmt_close($activitySql);
    mysqli_commit($conn);
    responseJson(
        true,
        "Product removed from cart and stock restored."
    );
} catch (Throwable $e) {
    mysqli_rollback($conn);
    responseJson(
        false,
        $e->getMessage()
    );
}
?>
