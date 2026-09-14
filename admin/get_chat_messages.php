<?php
session_start();
include "../config/database.php";
header("Content-Type: application/json");
if (!isset($_SESSION["admin_id"])) {
    echo json_encode([
        "success" => false,
        "message" => "Unauthorized."
    ]);
    exit();
}
$conversationId=isset($_GET["conversation_id"])
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
    SELECT
        cc.id,
        cc.user_id,
        cc.status,
        u.first_name,
        u.last_name,
        u.user_name,
        u.email,
        up.profile_picture
    FROM chat_conversations cc
    INNER JOIN users u
        ON u.id = cc.user_id
    LEFT JOIN user_profiles up
        ON up.user_id = cc.user_id
    WHERE cc.id = ?
    LIMIT 1
";
$stmt = mysqli_prepare(
    $conn,
    $sql
);
if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Failed to prepare conversation query."
    ]);
    exit();
}
mysqli_stmt_bind_param(
    $stmt,
    "i",
    $conversationId
);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result(
    $stmt,
    $id,
    $userId,
    $status,
    $firstName,
    $lastName,
    $username,
    $email,
    $profilePicture
);
if (!mysqli_stmt_fetch($stmt)) {
    mysqli_stmt_close($stmt);
    echo json_encode([
        "success" => false,
        "message" => "Conversation not found."
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
$stmt = mysqli_prepare(
    $conn,
    $sql
);
if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Failed to prepare message query."
    ]);
    exit();
}
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
        "id" =>(int) $messageId,
        "sender_type" => $senderType,
        "sender_id" =>(int) $senderId,
        "message" =>$message,
        "is_read" =>(int) $isRead,
        "created_at" =>$createdAt
    ];
}
mysqli_stmt_close($stmt);
echo json_encode([
    "success" => true,
    "conversation" => [
        "id" =>(int) $id,
        "user_id" =>(int) $userId,
        "name" =>trim(
                $firstName .
                " " .
                $lastName
            ),
        "username" =>$username,
        "email" =>$email,
        "profile_picture" =>$profilePicture ?? "",
        "status" =>$status
    ],
    "messages" =>$messages
]);
?>