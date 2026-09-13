<?php
session_start();
include "../config/database.php";
header("Content-Type: application/json");

if (!isset($_SESSION["user_id"])) {
    echo json_encode([
        "success" => false
    ]);
    exit();
}

$userId = (int) $_SESSION["user_id"];
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
    UPDATE chat_messages cm
    INNER JOIN chat_conversations cc
        ON cc.id = cm.conversation_id
    SET cm.is_read = 1
    WHERE cm.conversation_id = ?
    AND cc.user_id = ?
    AND cm.sender_type = 'admin'
";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $conversationId,
    $userId
);

mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);
echo json_encode([
    "success" => true
]);
?>