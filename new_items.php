<?php
session_start();
header("Content-Type: application/json");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
include "config/database.php";
if (!isset($_SESSION["user_id"])) {
    echo json_encode([
        "success" => false,
        "message" => "Please login first."
    ]);
    exit();
}
$lastId = isset($_GET["last_id"])
    ? (int) $_GET["last_id"]
    : 0;
$latestId = 0;
$latestResult = mysqli_query(
    $conn,
    "SELECT MAX(id) AS latest_id
     FROM products
     WHERE is_deleted = 0"
);
if ($latestResult) {
    $latestRow = mysqli_fetch_assoc($latestResult);
    $latestId = (int) (
        $latestRow["latest_id"] ?? 0
    );
    mysqli_free_result($latestResult);
}
if ($lastId <= 0) {
    echo json_encode([
        "success" => true,
        "latest_id" => $latestId,
        "new_items" => []
    ]);
    exit();
}
$newItems = [];
$stmt = mysqli_prepare(
    $conn,
    "SELECT
        id,
        product_name,
        category,
        image
     FROM products
     WHERE is_deleted = 0
       AND id > ?
     ORDER BY id DESC
     LIMIT 10"
);
if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Unable to load notifications."
    ]);
    exit();
}
mysqli_stmt_bind_param(
    $stmt,
    "i",
    $lastId
);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $newItems[] = [
            "id" => (int) $row["id"],
            "product_name" => $row["product_name"],
            "category" => $row["category"] ?? "",
            "image" => $row["image"] ?? ""
        ];
    }
    mysqli_free_result($result);
}
mysqli_stmt_close($stmt);
echo json_encode([
    "success" => true,
    "latest_id" => $latestId,
    "new_items" => $newItems
]);
?>