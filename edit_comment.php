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

$comment = trim($_POST["comment"] ?? "");

if ($commentId <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid comment."
    ]);
    exit();
}

if ($comment === "") {
    echo json_encode([
        "success" => false,
        "message" => "Comment cannot be empty."
    ]);
    exit();
}

if (mb_strlen($comment) > 2000) {
    echo json_encode([
        "success" => false,
        "message" => "Comment cannot exceed 2000 characters."
    ]);
    exit();
}

/*
 * Only additional comments can be edited.
 * The original review has parent_review_id = NULL.
 */
$stmt = mysqli_prepare(
    $conn,
    "UPDATE product_reviews
     SET comment = ?
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
    "sii",
    $comment,
    $commentId,
    $userId
);

if (!mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);

    echo json_encode([
        "success" => false,
        "message" => "Unable to update comment."
    ]);
    exit();
}

$affectedRows = mysqli_stmt_affected_rows($stmt);

mysqli_stmt_close($stmt);

if ($affectedRows <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Comment not found or you are not allowed to edit it."
    ]);
    exit();
}

echo json_encode([
    "success" => true,
    "message" => "Comment updated successfully.",
    "comment" => $comment
]);
