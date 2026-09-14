<?php
ob_start();
mysqli_report(MYSQLI_REPORT_OFF);
session_start();
include "config/database.php";
header("Content-Type: application/json; charset=UTF-8");
function respond($success, $message, $extra = [])
{
    if (ob_get_length()) {
        ob_clean();
    }
    echo json_encode(array_merge([
        "success" => $success,
        "message" => $message
    ], $extra));
    exit;
}
if (!isset($_SESSION["user_id"])) {
    respond(false, "Please login first.");
}
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    respond(false, "Invalid request.");
}
$userId = (int)$_SESSION["user_id"];
$productId = isset($_POST["product_id"])
    ? (int)$_POST["product_id"]
    : 0;
$quantity = isset($_POST["quantity"])
    ? (int)$_POST["quantity"]
    : 1;
if ($productId <= 0) {
    respond(false, "Invalid product.");
}
if ($quantity <= 0) {
    $quantity = 1;
}
$productSql = mysqli_prepare(
    $conn,
    "SELECT id, product_name, stock
     FROM products
     WHERE id = ?
     LIMIT 1"
);
if (!$productSql) {
    respond(false, "Unable to check product.");
}
mysqli_stmt_bind_param(
    $productSql,
    "i",
    $productId
);
if (!mysqli_stmt_execute($productSql)) {
    mysqli_stmt_close($productSql);
    respond(false, "Unable to check product.");
}
mysqli_stmt_bind_result(
    $productSql,
    $dbProductId,
    $productName,
    $stock
);
if (!mysqli_stmt_fetch($productSql)) {
    mysqli_stmt_close($productSql);
    respond(false, "Product not found.");
}
mysqli_stmt_close($productSql);
$stock = (int)$stock;
if ($stock <= 0) {
    respond(
        false,
        "This product is out of stock.",
        [
            "stock" => 0
        ]
    );
}
if ($quantity > $stock) {
    respond(
        false,
        "Only " . $stock . " item(s) available.",
        [
            "stock" => $stock
        ]
    );
}
$activityType = "Added to Cart";
$activitySql = mysqli_prepare(
    $conn,
    "INSERT INTO user_product_activity
    (user_id, product_id, activity_type)
    VALUES (?, ?, ?)"
);
if ($activitySql) {
    mysqli_stmt_bind_param(
        $activitySql,
        "iis",
        $userId,
        $productId,
        $activityType
    );
    mysqli_stmt_execute($activitySql);
    mysqli_stmt_close($activitySql);
}
respond(
    true,
    $productName . " added to cart.",
    [
        "product_id" => $productId,
        "quantity" => $quantity,
        "stock" => $stock
    ]
);
?>