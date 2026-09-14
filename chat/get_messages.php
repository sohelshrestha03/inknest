<?php
session_start();
include "../config/database.php";
header("Content-Type: application/json");
if (!isset($_SESSION["user_id"])) {
    echo json_encode([
        "success" => false,
        "message" => "Please login first."
    ]);
    exit();
}
$userId = (int) $_SESSION["user_id"];
$conversationId =
    isset($_GET["conversation_id"])
        ? (int) $_GET["conversation_id"]
        : 0;
if ($conversationId <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid conversation."
    ]);
    exit();
}
$sql = "
    SELECT id
    FROM chat_conversations
    WHERE id = ?
    AND user_id = ?
    LIMIT 1
";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $conversationId,
    $userId
);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);
if (mysqli_stmt_num_rows($stmt) === 0) {
    mysqli_stmt_close($stmt);
    echo json_encode([
        "success" => false,
        "message" => "Unauthorized conversation."
    ]);
    exit();
}
mysqli_stmt_close($stmt);
$sql = "
    SELECT
        id,
        sender_type,
        sender_id,
        message,
        is_read,
        created_at
    FROM chat_messages
    WHERE conversation_id = ?
    ORDER BY id ASC
";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param(
    $stmt,
    "i",
    $conversationId
);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result(
    $stmt,
    $messageId,
    $senderType,
    $senderId,
    $message,
    $isRead,
    $createdAt
);
$messages = [];
while (mysqli_stmt_fetch($stmt)) {
    $messages[] = [
        "id" => (int) $messageId,
        "sender_type" => $senderType,
        "sender_id" => (int) $senderId,
        "message" => $message,
        "is_read" => (int) $isRead,
        "created_at" => $createdAt
    ];
}
mysqli_stmt_close($stmt);
echo json_encode([
    "success" => true,
    "messages" => $messages
]);
?>