<?php
session_start();
include "config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}
$userId = $_SESSION["user_id"];
$error = "";
$success = "";
$stmt = mysqli_prepare(
    $conn,
    "SELECT user_name FROM users WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $newUsername = trim($_POST["username"] ?? "");
    if ($newUsername === "") {
        $error = "Username is required.";
    } elseif (strlen($newUsername) < 4) {
        $error = "Username must be at least 4 characters.";
    } elseif (!preg_match('/^[A-Za-z0-9_]+$/', $newUsername)) {
        $error = "Username can contain letters, numbers and underscore only.";
    } else {
        $check = mysqli_prepare(
            $conn,
            "SELECT id FROM users 
             WHERE user_name = ? AND id != ?"
        );
        mysqli_stmt_bind_param(
            $check,
            "si",
            $newUsername,
            $userId
        );
        mysqli_stmt_execute($check);
        $checkResult = mysqli_stmt_get_result($check);

        if (mysqli_num_rows($checkResult) > 0) {
            $error = "Username is already taken.";
        } else {
            $update = mysqli_prepare(
                $conn,
                "UPDATE users 
                 SET user_name = ? 
                 WHERE id = ?"
            );
            mysqli_stmt_bind_param(
                $update,
                "si",
                $newUsername,
                $userId
            );

            if (mysqli_stmt_execute($update)) {
                $_SESSION["username"] = $newUsername;
                $user["user_name"] = $newUsername;
                $success = "Username changed successfully.";
            } else {
                $error = "Unable to change username.";
            }
            mysqli_stmt_close($update);
        }
        mysqli_stmt_close($check);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Username | Inknest</title>
    <link rel="stylesheet" href="css/change_username.css?v=<?php echo time(); ?>">
    <script src="js/change_username.js" defer></script>
</head>

<body>
<div class="container">
    <h1>Change Username</h1>
    <p class="subtitle">Update your Inknest username.</p>

    <?php if ($error !== ""): ?>
        <div class="message error">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if ($success !== ""): ?>
        <div class="message success">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="change_username.php" id="usernameForm">
        <div class="form-group">
            <label for="username">New Username</label>
            <input type="text" id="username" name="username" maxlength="50" placeholder="Enter new username" autocomplete="username" required>
        </div>
        <button type="submit">Change Username</button>
    </form>
    <a href="profile.php" class="back">Back to Profile</a>
</div>
</body>
</html>