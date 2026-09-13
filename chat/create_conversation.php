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
$sql = "
    SELECT id
    FROM chat_conversations
    WHERE user_id = ?
    AND status = 'Open'
    ORDER BY id DESC
    LIMIT 1
";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Database error."
    ]);
    exit();
}

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $userId
);

mysqli_stmt_execute($stmt);

mysqli_stmt_bind_result(
    $stmt,
    $conversationId
);

$found = mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

if ($found) {
    echo json_encode([
        "success" => true,
        "conversation_id" => (int) $conversationId
    ]);
    exit();
}

$sql = "
    INSERT INTO chat_conversations
    (
        user_id,
        status
    )
    VALUES
    (
        ?,
        'Open'
    )
";

$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Could not create conversation."
    ]);

    exit();
}

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $userId
);
if (!mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    echo json_encode([
        "success" => false,
        "message" => "Could not create conversation."
    ]);
    exit();
}
$conversationId = mysqli_insert_id($conn);
mysqli_stmt_close($stmt);
echo json_encode([
    "success" => true,
    "conversation_id" => (int) $conversationId
]);
?>