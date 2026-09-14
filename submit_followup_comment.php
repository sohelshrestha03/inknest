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
$reviewId = isset($_POST["review_id"])
    ? (int)$_POST["review_id"]
    : 0;
$comment = trim($_POST["comment"] ?? "");
if ($productId <= 0) {
    respond(false, "Invalid product.");
}
if ($reviewId <= 0) {
    respond(false, "Invalid review.");
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
if (mysqli_stmt_num_rows($productSql) === 0) {
    mysqli_stmt_close($productSql);
    respond(false, "Product not found.");
}
mysqli_stmt_close($productSql);
$reviewSql = mysqli_prepare(
    $conn,
    "SELECT id, rating
     FROM product_reviews
     WHERE id = ?
       AND product_id = ?
       AND user_id = ?
       AND parent_review_id IS NULL
     LIMIT 1"
);
if (!$reviewSql) {
    respond(false, "Database error.");
}
mysqli_stmt_bind_param(
    $reviewSql,
    "iii",
    $reviewId,
    $productId,
    $userId
);
mysqli_stmt_execute($reviewSql);
mysqli_stmt_bind_result(
    $reviewSql,
    $parentReviewId,
    $parentRating
);
if (!mysqli_stmt_fetch($reviewSql)) {
    mysqli_stmt_close($reviewSql);
    respond(
        false,
        "The review could not be found."
    );
}
mysqli_stmt_close($reviewSql);
$parentReviewId = (int)$parentReviewId;
$parentRating = (int)$parentRating;
if ($parentRating < 1 || $parentRating > 5) {
    respond(false, "The original review has an invalid rating.");
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
    VALUES (?, ?, ?, ?, ?, NOW())"
);
if (!$insertSql) {
    respond(false, "Unable to prepare comment.");
}
mysqli_stmt_bind_param(
    $insertSql,
    "iiisi",
    $productId,
    $userId,
    $parentRating,
    $comment,
    $parentReviewId
);
if (!mysqli_stmt_execute($insertSql)) {
    mysqli_stmt_close($insertSql);
    respond(
        false,
        "Unable to add comment."
    );
}
mysqli_stmt_close($insertSql);
respond(
    true,
    "Comment added successfully."
);
?>