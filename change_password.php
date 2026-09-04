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
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $currentPassword = $_POST["current_password"] ?? "";
    $newPassword = $_POST["new_password"] ?? "";
    $confirmPassword = $_POST["confirm_password"] ?? "";

    if (
        trim($currentPassword) === "" ||
        trim($newPassword) === "" ||
        trim($confirmPassword) === ""
    ) {
        $error = "Please fill in all fields.";
    } elseif (strlen($newPassword) < 8) {
        $error = "New password must be at least 8 characters.";
    } elseif ($newPassword !== $confirmPassword) {
        $error = "New passwords do not match.";
    } else {
        $stmt = mysqli_prepare(
            $conn,
            "SELECT new_Password
             FROM users
             WHERE id = ?"
        );
        mysqli_stmt_bind_param($stmt, "i", $userId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if (!$user) {
            $error = "User account not found.";
        } elseif (!password_verify($currentPassword, $user["new_Password"])) {
            $error = "Current password is incorrect.";
        } else {
            $newPasswordHash = password_hash(
                $newPassword,
                PASSWORD_DEFAULT
            );
            $update = mysqli_prepare(
                $conn,
                "UPDATE users
                 SET new_Password = ?,
                     confirm_Password = ?
                 WHERE id = ?"
            );
            mysqli_stmt_bind_param(
                $update,
                "ssi",
                $newPasswordHash,
                $newPasswordHash,
                $userId
            );

            if (mysqli_stmt_execute($update)) {
                $success = "Password changed successfully.";
            } else {
                $error = "Unable to change password.";
            }
            mysqli_stmt_close($update);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password | Inknest</title>
    <link rel="stylesheet" href="css/change_password.css?v=<?php echo time(); ?>">
    <script src="js/change_password.js" defer></script>
</head>

<body>
<div class="container">
    <h1>Change Password</h1>
    <p class="subtitle">Update your Inknest account password.</p>

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


    <form method="POST" action="change_password.php" id="passwordForm">
        <div class="form-group">
            <label for="current_password">Current Password</label>
            <input type="password" id="current_password" name="current_password" placeholder="Enter current password" autocomplete="current-password" required>
        </div>

        <div class="form-group">
            <label for="new_password">New Password</label>
            <input type="password" id="new_password" name="new_password" placeholder="Enter new password" minlength="8" autocomplete="new-password" required>
            <small>Password must be at least 8 characters.</small>
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirm New Password</label>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" autocomplete="new-password" required>
        </div>
        <button type="submit">Change Password</button>
    </form>
    <a href="profile.php" class="back">Back to Profile</a>
</div>
</body>
</html>