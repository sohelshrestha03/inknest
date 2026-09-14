<?php
session_start();
include "../config/database.php";
header("Content-Type: application/json");
if (!isset($_SESSION["admin_id"])) {
    echo json_encode([
        "success" => false,
        "count" => 0
    ]);
    exit();
}
$sql = "
    SELECT COUNT(cm.id)
    FROM chat_messages cm
    INNER JOIN chat_conversations cc
        ON cc.id = cm.conversation_id
    WHERE cc.status = 'Open'
    AND cm.sender_type = 'user'
    AND cm.is_read = 0
";
$result = mysqli_query(
    $conn,
    $sql
);
$count = 0;
if ($result) {
    $row=mysqli_fetch_row($result);
    $count=(int) $row[0];
}
echo json_encode([
    "success" => true,
    "count" => $count
]);
?>