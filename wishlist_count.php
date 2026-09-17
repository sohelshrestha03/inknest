<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
include "config/database.php";
if (!isset($_SESSION["user_id"])) {
    echo json_encode([
        "success" => false,
        "count" => 0,
        "message" => "Please login first."
    ]);

    exit();
}
$userId = (int) $_SESSION["user_id"];
$stmt = mysqli_prepare(
    $conn,
    "SELECT COUNT(*)
     FROM wishlist w
     INNER JOIN products p
        ON w.product_id = p.id
     WHERE w.user_id = ?
       AND p.is_deleted = 0"
);
if (!$stmt) {
    echo json_encode([
        "success" => false,
        "count" => 0,
        "message" => "Wishlist count query failed."
    ]);
    exit();
}
mysqli_stmt_bind_param(
    $stmt,
    "i",
    $userId
);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result(
    $stmt,
    $count
);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);
echo json_encode([
    "success" => true,
    "count" => (int) $count
]);
exit();
?>