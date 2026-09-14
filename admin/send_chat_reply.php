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
$adminId =(int) $_SESSION["admin_id"];
$conversationId =
    isset($_POST["conversation_id"])
        ? (int) $_POST["conversation_id"]
        : 0;
$message =trim($_POST["message"] ?? "");
if ($conversationId <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid conversation."
    ]);
    exit();
}
if ($message === "") {
    echo json_encode([
        "success" => false,
        "message" => "Message cannot be empty."
    ]);
    exit();
}
if (mb_strlen($message) > 2000) {
    echo json_encode([
        "success" => false,
        "message" => "Message is too long."
    ]);
    exit();
}
$sql = "
    SELECT id, status
    FROM chat_conversations
    WHERE id = ?
    LIMIT 1
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
    $foundId,
    $status
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
if ($status !== "Open") {
    echo json_encode([
        "success" => false,
        "message" => "Conversation is closed."
    ]);
    exit();
}
$sql = "
    INSERT INTO chat_messages
    (
        conversation_id,
        sender_type,
        sender_id,
        message,
        is_read
    )
    VALUES
    (
        ?,
        'admin',
        ?,
        ?,
        0
    )
";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param(
    $stmt,
    "iis",
    $conversationId,
    $adminId,
    $message
);
if (!mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    echo json_encode([
        "success" => false,
        "message" => "Could not send reply."
    ]);
    exit();
}
$messageId =mysqli_insert_id($conn);
mysqli_stmt_close($stmt);
$sql = "
    UPDATE chat_conversations
    SET updated_at = CURRENT_TIMESTAMP
    WHERE id = ?
";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param(
    $stmt,
    "i",
    $conversationId
);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);
echo json_encode([
    "success" => true,
    "message_id" => (int) $messageId
]);
?>