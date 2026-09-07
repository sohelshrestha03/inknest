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

$productId = isset($_POST["product_id"])
    ? (int) $_POST["product_id"]
    : 0;

if ($productId <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid product."
    ]);
    exit();
}

$sql = mysqli_prepare(
    $conn,
    "UPDATE products
     SET stock = stock + 1
     WHERE id = ?"
);
mysqli_stmt_bind_param(
    $sql,
    "i",
    $productId
);
mysqli_stmt_execute($sql);
mysqli_stmt_close($sql);

$stockSql = mysqli_prepare(
    $conn,
    "SELECT stock FROM products WHERE id = ?"
);
mysqli_stmt_bind_param(
    $stockSql,
    "i",
    $productId
);

mysqli_stmt_execute($stockSql);
$result = mysqli_stmt_get_result($stockSql);
$product = mysqli_fetch_assoc($result);
$stock = (int) $product["stock"];
mysqli_stmt_close($stockSql);

echo json_encode([
    "success" => true,
    "stock" => $stock
]);
exit();
?>