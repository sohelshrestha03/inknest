<?php
session_start();
include "config/database.php";
header("Content-Type: application/json");


if (!isset($_SESSION["user_id"])) {
    echo json_encode([
        "success" => false,
        "message" => "User is not logged in."
    ]);
    exit();
}


$userId = (int) $_SESSION["user_id"];
$data = json_decode(
    file_get_contents("php://input"),
    true
);


if (!isset($data["product_id"]) ||!is_numeric($data["product_id"])) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid product ID."
    ]);

    exit();
}


$productId = (int) $data["product_id"];
$activityType = "Added to Cart";

$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO user_product_activity
        (user_id, product_id, activity_type)
     VALUES (?, ?, ?)"
);


if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Could not prepare activity query."
    ]);

    exit();
}


mysqli_stmt_bind_param(
    $stmt,
    "iis",
    $userId,
    $productId,
    $activityType
);


if (mysqli_stmt_execute($stmt)) {
    echo json_encode([
        "success" => true,
        "message" => "Activity recorded successfully."
    ]);

} else {
    echo json_encode([
        "success" => false,
        "message" => "Could not record activity."
    ]);
}
mysqli_stmt_close($stmt);
?>