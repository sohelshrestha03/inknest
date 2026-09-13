<?php
session_start();
include "../config/database.php";
header("Content-Type: application/json");

if (!isset($_SESSION["admin_id"])) {
    echo json_encode([
        "success" => false
    ]);
    exit();
}

$conversationId =
    isset($_POST["conversation_id"])
        ? (int) $_POST["conversation_id"]
        : 0;

if ($conversationId <= 0) {
    echo json_encode([
        "success" => false
    ]);
    exit();
}

$sql = "
    UPDATE chat_messages
    SET is_read = 1
    WHERE conversation_id = ?
    AND sender_type = 'user'
";

$stmt = mysqli_prepare(
    $conn,
    $sql
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $conversationId
);

mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);
echo json_encode([
    "success" => true
]);
?>