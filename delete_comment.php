<?php
session_start();
header("Content-Type: application/json");
include "config/database.php";
if (!isset($_SESSION["user_id"])) {
    echo json_encode([
        "success" => false,
        "message" => "Please login first."
    ]);
    exit();
}
$userId = (int)$_SESSION["user_id"];
$commentId = isset($_POST["comment_id"])
    ? (int)$_POST["comment_id"]
    : 0;
if ($commentId <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid comment."
    ]);
    exit();
}
$stmt = mysqli_prepare(
    $conn,
    "DELETE FROM product_reviews
     WHERE id = ?
       AND user_id = ?
       AND parent_review_id IS NOT NULL
     LIMIT 1"
);
if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Unable to prepare query."
    ]);
    exit();
}
mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $commentId,
    $userId
);
if (!mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    echo json_encode([
        "success" => false,
        "message" => "Unable to delete comment."
    ]);
    exit();
}
$affectedRows = mysqli_stmt_affected_rows($stmt);
mysqli_stmt_close($stmt);
if ($affectedRows <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Comment not found or you are not allowed to delete it."
    ]);
    exit();
}
echo json_encode([
    "success" => true,
    "message" => "Comment deleted successfully."
]);
?>