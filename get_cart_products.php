<?php
ob_start();
mysqli_report(MYSQLI_REPORT_OFF);
session_start();
include "config/database.php";
header("Content-Type: application/json; charset=UTF-8");
function respond($success, $message = "", $products = [])
{
    while (ob_get_level()) {
        ob_end_clean();
    }
    echo json_encode([
        "success" => $success,
        "message" => $message,
        "products" => $products
    ], JSON_UNESCAPED_UNICODE);
    exit();
}
if (!isset($_SESSION["user_id"])) {
    respond(false, "Please login first.", []);
}
$ids = isset($_GET["ids"])
    ? trim($_GET["ids"])
    : "";
if ($ids === "") {
    respond(true, "Cart is empty.", []);
}
$rawIds = explode(",", $ids);
$productIds = [];
foreach ($rawIds as $id) {
    $id = (int)trim($id);
    if ($id > 0) {
        $productIds[] = $id;
    }
}
$productIds = array_values(array_unique($productIds));
if (empty($productIds)) {
    respond(true, "Cart is empty.", []);
}
$idList = implode(",", $productIds);
$sql = "
    SELECT
        id,
        product_name,
        description,
        price,
        image,
        stock
    FROM products
    WHERE id IN ($idList)
";
$result = mysqli_query($conn, $sql);
if (!$result) {
    respond(false, "Unable to load cart products.", []);
}
$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    $products[] = [
        "id" => (int)$row["id"],
        "product_name" => $row["product_name"],
        "description" => $row["description"],
        "price" => (float)$row["price"],
        "image" => $row["image"],
        "stock" => (int)$row["stock"]
    ];
}
mysqli_free_result($result);
respond(
    true,
    "Cart loaded successfully.",
    $products
);
?>