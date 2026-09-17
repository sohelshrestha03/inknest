<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
mysqli_report(MYSQLI_REPORT_OFF);
include "config/database.php";
if (!isset($_SESSION["user_id"])) {
    echo json_encode([
        "success" => false,
        "wishlisted" => false,
        "message" => "Please login first."
    ]);
    exit();
}
$userId = (int) $_SESSION["user_id"];
$productId = isset($_POST["product_id"]) ? (int) $_POST["product_id"] : 0;
if ($productId <= 0) {
    echo json_encode([
        "success" => false,
        "wishlisted" => false,
        "message" => "Invalid product."
    ]);
    exit();
}
$stmt = mysqli_prepare(
    $conn,
    "SELECT id
     FROM products
     WHERE id = ?
     AND is_deleted = 0
     LIMIT 1"
);
if (!$stmt) {
    echo json_encode([
        "success" => false,
        "wishlisted" => false,
        "message" => "Product query failed."
    ]);
    exit();
}
mysqli_stmt_bind_param($stmt, "i", $productId);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);
if (mysqli_stmt_num_rows($stmt) === 0) {
    mysqli_stmt_close($stmt);
    echo json_encode([
        "success" => false,
        "wishlisted" => false,
        "message" => "Product not found."
    ]);
    exit();
}
mysqli_stmt_close($stmt);
$stmt = mysqli_prepare(
    $conn,
    "SELECT product_id
     FROM wishlist
     WHERE user_id = ?
     AND product_id = ?
     LIMIT 1"
);
if (!$stmt) {
    echo json_encode([
        "success" => false,
        "wishlisted" => false,
        "message" => "Wishlist query failed."
    ]);
    exit();
}
mysqli_stmt_bind_param($stmt, "ii", $userId, $productId);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);
$exists = mysqli_stmt_num_rows($stmt) > 0;
mysqli_stmt_close($stmt);
if ($exists) {
    $stmt = mysqli_prepare(
        $conn,
        "DELETE FROM wishlist
         WHERE user_id = ?
         AND product_id = ?"
    );
    if (!$stmt) {
        echo json_encode([
            "success" => false,
            "wishlisted" => true,
            "message" => "Remove query failed."
        ]);
        exit();
    }
    mysqli_stmt_bind_param($stmt, "ii", $userId, $productId);
    $removed = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    if (!$removed) {
        echo json_encode([
            "success" => false,
            "wishlisted" => true,
            "message" => "Unable to remove from wishlist."
        ]);
        exit();
    }
    echo json_encode([
        "success" => true,
        "wishlisted" => false,
        "message" => "Removed from wishlist."
    ]);
    exit();
}
$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO wishlist (user_id, product_id)
     VALUES (?, ?)"
);
if (!$stmt) {
    echo json_encode([
        "success" => false,
        "wishlisted" => false,
        "message" => "Add query failed."
    ]);
    exit();
}
mysqli_stmt_bind_param($stmt, "ii", $userId, $productId);
$added = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);
if (!$added) {
    echo json_encode([
        "success" => false,
        "wishlisted" => false,
        "message" => "Unable to add to wishlist."
    ]);
    exit();
}
echo json_encode([
    "success" => true,
    "wishlisted" => true,
    "message" => "Added to wishlist."
]);
exit();
?>