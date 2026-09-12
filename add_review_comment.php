<?php

session_start();
header(
"Content-Type: application/json; charset=utf-8"
);

include "config/database.php";

function jsonResponse(
    $success,
    $message,
    $extra = []
) {

    echo json_encode(
        array_merge(
            [
                "success" => $success,
                "message" => $message
            ],
            $extra
        )
    );

    exit();
}


if (!isset($_SESSION["user_id"])) {
    jsonResponse(
        false,
        "You must be logged in to add a comment."
    );
}

$userId = (int) $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    jsonResponse(
        false,
        "Invalid request method."
    );
}

$productId =isset($_POST["product_id"])
    ? (int) $_POST["product_id"]
    : 0;

$reviewId =
    isset($_POST["review_id"])
    ? (int) $_POST["review_id"]
    : 0;

$comment =
    isset($_POST["comment"])
    ? trim($_POST["comment"])
    : "";

if ($productId <= 0) {
    jsonResponse(
        false,
        "Invalid product."
    );
}

if ($reviewId <= 0) {
    jsonResponse(
        false,
        "Invalid review."
    );
}

if ($comment === "") {
    jsonResponse(
        false,
        "Please enter a comment."
    );
}

if (mb_strlen($comment) > 2000) {
    jsonResponse(
        false,
        "Comment must be 2000 characters or less."
    );
}

$checkReviewSql = "
    SELECT
        id,
        product_id,
        user_id,
        parent_review_id

    FROM product_reviews

    WHERE id = ?
      AND product_id = ?
      AND user_id = ?
      AND parent_review_id IS NULL

    LIMIT 1
";

$checkReviewStmt =mysqli_prepare(
        $conn,
        $checkReviewSql
    );

if (!$checkReviewStmt) {
    jsonResponse(
        false,
        "Unable to prepare review check."
    );
}

mysqli_stmt_bind_param(
    $checkReviewStmt,
    "iii",
    $reviewId,
    $productId,
    $userId
);

if (!mysqli_stmt_execute($checkReviewStmt)) {
    mysqli_stmt_close(
        $checkReviewStmt
    );
    jsonResponse(
        false,
        "Unable to verify your review."
    );
}

$result=mysqli_stmt_get_result(
        $checkReviewStmt
    );


if (!$result || mysqli_num_rows($result) === 0) {
    mysqli_stmt_close(
        $checkReviewStmt
    );
    jsonResponse(
        false,
        "You can only add comments to your own original review."
    );
}

mysqli_stmt_close(
    $checkReviewStmt
);

$insertSql = "
    INSERT INTO product_reviews
    (
        product_id,
        user_id,
        parent_review_id,
        rating,
        comment,
        created_at
    )
    VALUES
    (
        ?,
        ?,
        ?,
        NULL,
        ?,
        NOW()
    )
";

$insertStmt =
    mysqli_prepare(
        $conn,
        $insertSql
    );

if (!$insertStmt) {
    jsonResponse(
        false,
        "Unable to prepare comment insertion."
    );
}

mysqli_stmt_bind_param(
    $insertStmt,
    "iiis",
    $productId,
    $userId,
    $reviewId,
    $comment
);

if (!mysqli_stmt_execute($insertStmt)) {
    $error = mysqli_stmt_error(
            $insertStmt
        );
    mysqli_stmt_close(
        $insertStmt
    );
    jsonResponse(
        false,
        "Database error: " . $error
    );
}
$newCommentId=mysqli_insert_id($conn);
mysqli_stmt_close(
    $insertStmt
);
jsonResponse(
    true,
    "Additional comment added successfully.",
    [
        "comment_id" => $newCommentId
    ]
);