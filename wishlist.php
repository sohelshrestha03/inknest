<?php
session_start();
include "config/database.php";
header("Content-Type: application/json; charset=UTF-8");
if (!isset($_SESSION["user_id"])) {
    echo json_encode([
        "success" => false,
        "message" => "Please login first."
    ]);
    exit();
}
$userId = (int)$_SESSION["user_id"];
$productId = (int)($_POST["product_id"] ?? 0);
if ($productId <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid product."
    ]);
    exit();
}
$productStmt = mysqli_prepare(
    $conn,
    "SELECT id
     FROM products
     WHERE id = ?
     LIMIT 1"
);
if (!$productStmt) {
    echo json_encode([
        "success" => false,
        "message" => "Product query failed."
    ]);
    exit();
}
mysqli_stmt_bind_param(
    $productStmt,
    "i",
    $productId
);
mysqli_stmt_execute($productStmt);
mysqli_stmt_bind_result(
    $productStmt,
    $foundProductId
);
$productExists=mysqli_stmt_fetch($productStmt);
mysqli_stmt_close($productStmt);
if (!$productExists) {
    echo json_encode([
        "success" => false,
        "message" => "Product not found."
    ]);
    exit();
}
$checkStmt = mysqli_prepare(
    $conn,
    "SELECT id
     FROM wishlist
     WHERE user_id = ?
       AND product_id = ?
     LIMIT 1"
);
if (!$checkStmt) {
    echo json_encode([
        "success" => false,
        "message" => "Wishlist check failed."
    ]);
    exit();
}
mysqli_stmt_bind_param(
    $checkStmt,
    "ii",
    $userId,
    $productId
);
mysqli_stmt_execute($checkStmt);
mysqli_stmt_bind_result(
    $checkStmt,
    $wishlistId
);
$isWishlisted =mysqli_stmt_fetch($checkStmt);
mysqli_stmt_close($checkStmt);
if ($isWishlisted) {
    $deleteStmt = mysqli_prepare(
        $conn,
        "DELETE FROM wishlist
         WHERE user_id = ?
           AND product_id = ?"
    );
    if (!$deleteStmt) {
        echo json_encode([
            "success" => false,
            "message" => "Unable to remove wishlist."
        ]);
        exit();
    }
    mysqli_stmt_bind_param(
        $deleteStmt,
        "ii",
        $userId,
        $productId
    );
    if (!mysqli_stmt_execute($deleteStmt)) {
        $error =mysqli_stmt_error($deleteStmt);
        mysqli_stmt_close($deleteStmt);
        echo json_encode([
            "success" => false,
            "message" =>
                "Unable to remove wishlist: " . $error
        ]);
        exit();
    }
    mysqli_stmt_close($deleteStmt);
    $activityType = "Unlike";
    $activityStmt = mysqli_prepare(
        $conn,
        "INSERT INTO user_product_activity
        (
            user_id,
            product_id,
            activity_type,
            created_at
        )
        VALUES (?, ?, ?, NOW())"
    );
    if (!$activityStmt) {
        echo json_encode([
            "success" => false,
            "message" =>
                "Wishlist removed, but activity query failed: " .
                mysqli_error($conn)
        ]);
        exit();
    }
    mysqli_stmt_bind_param(
        $activityStmt,
        "iis",
        $userId,
        $productId,
        $activityType
    );
    if (!mysqli_stmt_execute($activityStmt)) {
        $error=mysqli_stmt_error($activityStmt);
        mysqli_stmt_close($activityStmt);
        echo json_encode([
            "success" => false,
            "message" =>
                "Unlike could not be saved: " . $error
        ]);
        exit();
    }
    mysqli_stmt_close($activityStmt);
    echo json_encode([
        "success" => true,
        "wishlisted" => false,
        "activity_saved" => true,
        "activity_type" => "Unlike",
        "message" => "Removed from wishlist."
    ]);
    exit();
}
$insertStmt = mysqli_prepare(
    $conn,
    "INSERT INTO wishlist
    (
        user_id,
        product_id
    )
    VALUES (?, ?)"
);
if (!$insertStmt) {
    echo json_encode([
        "success" => false,
        "message" => "Wishlist insert failed."
    ]);
    exit();
}
mysqli_stmt_bind_param(
    $insertStmt,
    "ii",
    $userId,
    $productId
);
if (!mysqli_stmt_execute($insertStmt)) {
    $error = mysqli_stmt_error($insertStmt);
    mysqli_stmt_close($insertStmt);
    echo json_encode([
        "success" => false,
        "message" =>
            "Unable to add wishlist: " . $error
    ]);
    exit();
}
mysqli_stmt_close($insertStmt);
$activityType = "Like";
$activityStmt = mysqli_prepare(
    $conn,
    "INSERT INTO user_product_activity
    (
        user_id,
        product_id,
        activity_type,
        created_at
    )
    VALUES (?, ?, ?, NOW())"
);
if (!$activityStmt) {
    echo json_encode([
        "success" => false,
        "message" =>
            "Wishlist added, but activity query failed: " .
            mysqli_error($conn)
    ]);
    exit();
}
mysqli_stmt_bind_param(
    $activityStmt,
    "iis",
    $userId,
    $productId,
    $activityType
);
if (!mysqli_stmt_execute($activityStmt)) {
    $error=mysqli_stmt_error($activityStmt);
    mysqli_stmt_close($activityStmt);
    echo json_encode([
        "success" => false,
        "message" =>
            "Like could not be saved: " . $error
    ]);
    exit();
}
mysqli_stmt_close($activityStmt);
echo json_encode([
    "success" => true,
    "wishlisted" => true,
    "activity_saved" => true,
    "activity_type" => "Like",
    "message" => "Added to wishlist."
]);
exit();
?>