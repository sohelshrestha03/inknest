<?php
session_start();
include "../config/database.php";
header("Content-Type: application/json; charset=UTF-8");

if (!isset($_SESSION["admin_id"])) {
    echo json_encode([
        "success" => false,
        "message" => "Unauthorized."
    ]);
    exit();
}

$sql = "
    SELECT
        cc.id,
        cc.user_id,
        cc.status,
        cc.created_at,
        cc.updated_at,
        u.first_name,
        u.last_name,
        u.user_name,
        u.email,
        up.profile_picture,
        (
            SELECT cm.message
            FROM chat_messages cm
            WHERE cm.conversation_id = cc.id
            ORDER BY cm.id DESC
            LIMIT 1
        ) AS last_message,
        (
            SELECT cm.created_at
            FROM chat_messages cm
            WHERE cm.conversation_id = cc.id
            ORDER BY cm.id DESC
            LIMIT 1
        ) AS last_message_time,
        (
            SELECT COUNT(cm2.id)
            FROM chat_messages cm2
            WHERE cm2.conversation_id = cc.id
            AND cm2.sender_type = 'user'
            AND cm2.is_read = 0
        ) AS unread_count
    FROM chat_conversations cc
    INNER JOIN users u
        ON u.id = cc.user_id
    LEFT JOIN user_profiles up
        ON up.user_id = u.id
    WHERE cc.status = 'Open'
    ORDER BY cc.updated_at DESC

";


$result =
    mysqli_query(
        $conn,
        $sql
    );

if (!$result) {
    echo json_encode([
        "success" => false,
        "message" =>
            "Database error: " .
            mysqli_error($conn)
    ]);

    exit();
}

$conversations = [];

while ($row = mysqli_fetch_assoc($result)) {
    $firstName =trim($row["first_name"] ?? "");
    $lastName =trim($row["last_name"] ?? "");
    $name =trim($firstName ." " .$lastName);

    if ($name === "") {
        $name =trim($row["user_name"] ?? "");
    }
    if ($name === "") {
        $name = "Customer";
    }

    $profilePicture =trim($row["profile_picture"] ?? "");
    $lastMessage = trim($row["last_message"] ?? "");
    $lastMessageTime = $row["last_message_time"] ?? "";
    $unreadCount =(int) ($row["unread_count"] ?? 0);
    $conversations[] = [
        "id" =>(int) $row["id"],
        "user_id" =>(int) $row["user_id"],
        "name" =>$name,
        "username" =>$row["user_name"] ?? "",
        "email" =>$row["email"] ?? "",
        "profile_picture" =>$profilePicture,
        "status" =>$row["status"] ?? "",
        "last_message" =>$lastMessage,
        "last_message_time" =>$lastMessageTime,
        "unread_count" =>$unreadCount
    ];
}

echo json_encode(
    [
        "success" =>true,
        "conversations" =>$conversations
    ],
    JSON_UNESCAPED_UNICODE
);
exit();
?>