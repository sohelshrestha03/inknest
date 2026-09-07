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

$sql = mysqli_prepare(
    $conn,
    "UPDATE products
     SET stock = stock + ?
     WHERE id = ?"
);

if (!$sql) {
    echo json_encode([
        "success" => false,
        "message" => "Database error."
    ]);
    exit();
}

mysqli_stmt_bind_param(
    $sql,
    "ii",
    $quantity,
    $productId
);

if (!mysqli_stmt_execute($sql)) {
    mysqli_stmt_close($sql);

    echo json_encode([
        "success" => false,
        "message" => "Failed to restore stock."
    ]);
    exit();
}
mysqli_stmt_close($sql);
echo json_encode([
    "success" => true,
    "message" => "Product removed from cart and stock restored."
]);

exit();
?>