<?php
session_start();
include "config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}
$userId = (int) $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: home.php");
    exit();
}

$productId = isset($_POST["product_id"])
    ? (int) $_POST["product_id"]
    : 0;

$rating = isset($_POST["rating"])
    ? (int) $_POST["rating"]
    : 0;

$comment = trim(
    $_POST["comment"] ?? ""
);

if ($productId <= 0) {
    header("Location: home.php");
    exit();
}

if ($rating < 1 || $rating > 5) {
    header(
        "Location: product_details.php?id=" .
        $productId .
        "&error=invalid_rating"
    );
    exit();
}

if (strlen($comment) > 1000) {
    header(
        "Location: product_details.php?id=" .
        $productId .
        "&error=comment_too_long"
    );
    exit();
}

$productSql = mysqli_prepare(
    $conn,
    "SELECT id
     FROM products
     WHERE id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $productSql,
    "i",
    $productId
);

mysqli_stmt_execute(
    $productSql
);

$productResult = mysqli_stmt_get_result(
    $productSql
);

$productExists =
    $productResult &&
    mysqli_num_rows($productResult) > 0;

mysqli_stmt_close(
    $productSql
);

if (!$productExists) {
    header("Location: home.php");
    exit();
}

$checkSql = mysqli_prepare(
    $conn,
    "SELECT id
     FROM product_reviews
     WHERE product_id = ?
     AND user_id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $checkSql,
    "ii",
    $productId,
    $userId
);

mysqli_stmt_execute(
    $checkSql
);

$checkResult = mysqli_stmt_get_result(
    $checkSql
);

$alreadyReviewed =
    $checkResult &&
    mysqli_num_rows($checkResult) > 0;

mysqli_stmt_close(
    $checkSql
);

if ($alreadyReviewed) {

    header(
        "Location: product_details.php?id=" .
        $productId .
        "&error=already_reviewed"
    );

    exit();
}

$insertSql = mysqli_prepare(
    $conn,
    "INSERT INTO product_reviews
        (
            product_id,
            user_id,
            rating,
            comment
        )
     VALUES (?, ?, ?, ?)"
);

if (!$insertSql) {
    header(
        "Location: product_details.php?id=" .
        $productId .
        "&error=database"
    );
    exit();
}

mysqli_stmt_bind_param(
    $insertSql,
    "iiis",
    $productId,
    $userId,
    $rating,
    $comment
);

if (mysqli_stmt_execute($insertSql)) {
    mysqli_stmt_close($insertSql);
    header(
        "Location: product_details.php?id=" .
        $productId .
        "&review=success"
    );
    exit();
}

mysqli_stmt_close($insertSql);

header(
    "Location: product_details.php?id=" .
    $productId .
    "&error=database"
);
exit();
?>