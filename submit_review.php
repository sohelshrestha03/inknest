<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");
include "config/database.php";
function respond($success, $message)
{
    echo json_encode([
        "success" => $success,
        "message" => $message
    ]);

    exit();
}
if (!isset($_SESSION["user_id"])) {
    respond(false, "Please login first.");
}
$userId = (int)$_SESSION["user_id"];
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    respond(false, "Invalid request method.");
}
$productId = isset($_POST["product_id"])
    ? (int)$_POST["product_id"]
    : 0;
$rating = isset($_POST["rating"])
    ? (int)$_POST["rating"]
    : 0;
$comment = trim($_POST["comment"] ?? "");
if ($productId <= 0) {
    respond(false, "Invalid product.");
}
if ($rating < 1 || $rating > 5) {
    respond(false, "Please select a valid rating.");
}
if ($comment === "") {
    respond(false, "Comment cannot be empty.");
}
if (strlen($comment) > 2000) {
    respond(false, "Comment is too long.");
}
$productSql = mysqli_prepare(
    $conn,
    "SELECT id
     FROM products
     WHERE id = ?
     LIMIT 1"
);
if (!$productSql) {
    respond(false, "Database error.");
}
mysqli_stmt_bind_param(
    $productSql,
    "i",
    $productId
);
mysqli_stmt_execute($productSql);
mysqli_stmt_store_result($productSql);
$productExists =mysqli_stmt_num_rows($productSql) > 0;
mysqli_stmt_close($productSql);
if (!$productExists) {
    respond(false, "Product not found.");
}
$checkSql = mysqli_prepare(
    $conn,
    "SELECT id
     FROM product_reviews
     WHERE product_id = ?
     AND user_id = ?
     AND parent_review_id IS NULL
     LIMIT 1"
);
if (!$checkSql) {
    respond(false, "Database error.");
}
mysqli_stmt_bind_param(
    $checkSql,
    "ii",
    $productId,
    $userId
);
mysqli_stmt_execute($checkSql);
mysqli_stmt_store_result($checkSql);
$alreadyReviewed =mysqli_stmt_num_rows($checkSql) > 0;
mysqli_stmt_close($checkSql);
if ($alreadyReviewed) {
    respond(false, "You have already reviewed this product.");
}
$insertSql = mysqli_prepare(
    $conn,
    "INSERT INTO product_reviews
    (
        product_id,
        user_id,
        rating,
        comment,
        parent_review_id,
        created_at
    )
    VALUES (?, ?, ?, ?, NULL, NOW())"
);
if (!$insertSql) {
    respond(false, "Unable to prepare review.");
}
mysqli_stmt_bind_param(
    $insertSql,
    "iiis",
    $productId,
    $userId,
    $rating,
    $comment
);
if (!mysqli_stmt_execute($insertSql)) {
    mysqli_stmt_close($insertSql);
    respond(
        false,
        "Unable to submit review."
    );
}
mysqli_stmt_close($insertSql);
respond(
    true,
    "Review submitted successfully."
);
?>