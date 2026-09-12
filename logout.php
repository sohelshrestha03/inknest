<?php
session_start();
include "config/database.php";

if (isset($_SESSION["user_id"])) {
    $userId = (int) $_SESSION["user_id"];
    $activitySql = mysqli_prepare(
        $conn,
        "INSERT INTO user_product_activity
         (user_id, product_id, activity_type, created_at)
         VALUES (?, NULL, 'Logged Out', NOW())"
    );

    if ($activitySql) {
        mysqli_stmt_bind_param(
            $activitySql,
            "i",
            $userId
        );
        mysqli_stmt_execute($activitySql);
        mysqli_stmt_close($activitySql);
    }
}

$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        "",
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}
session_destroy();
header("Location: login.php");
exit();
?>