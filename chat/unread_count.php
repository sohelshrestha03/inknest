<?php
session_start();
include "../config/database.php";
header("Content-Type: application/json");

if (!isset($_SESSION["user_id"])) {
    echo json_encode([
        "success" => false,
        "count" => 0
    ]);
    exit();
}

$userId = (int) $_SESSION["user_id"];
$sql = "
    SELECT COUNT(cm.id)
    FROM chat_messages cm
    INNER JOIN chat_conversations cc
        ON cc.id = cm.conversation_id
    WHERE cc.user_id = ?
    AND cc.status = 'Open'
    AND cm.sender_type = 'admin'
    AND cm.is_read = 0
";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param(
    $stmt,
    "i",
    $userId
);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result(
    $stmt,
    $unreadCount
);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

echo json_encode([
    "success" => true,
    "count" => (int) $unreadCount
]);
?>