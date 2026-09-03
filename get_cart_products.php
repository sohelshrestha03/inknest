<?php
session_start();
include "config/database.php";
header("Content-Type: application/json");

if (!isset($_SESSION["user_id"])) {
    echo json_encode([]);
    exit();
}

if (!isset($_GET["ids"]) || trim($_GET["ids"]) === "") {
    echo json_encode([]);
    exit();
}

$ids = explode(",", $_GET["ids"]);
$ids = array_map("intval", $ids);
$ids = array_filter($ids, function ($id) {
    return $id > 0;
});
$ids = array_unique($ids);

if (empty($ids)) {
    echo json_encode([]);
    exit();
}

$placeholders = implode(",", array_fill(0, count($ids), "?"));
$sql = "SELECT id, product_name, description, price, image FROM products WHERE id IN ($placeholders)";
$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    echo json_encode([
        "error" => "Failed to prepare database query."
    ]);
    exit();
}
$types = str_repeat("i", count($ids));
mysqli_stmt_bind_param($stmt, $types, ...$ids);
if (!mysqli_stmt_execute($stmt)) {
    echo json_encode([
        "error" => "Failed to execute database query."
    ]);
    exit();
}
$result = mysqli_stmt_get_result($stmt);
$products = [];
while ($product = mysqli_fetch_assoc($result)) {
    $products[] = $product;
}
mysqli_stmt_close($stmt);
echo json_encode($products);
?>