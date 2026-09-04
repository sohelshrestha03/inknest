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
    "SELECT email FROM users WHERE id = ?"
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
    $newEmail = trim($_POST["email"] ?? "");
    if ($newEmail === "") {
        $error = "Email is required.";
    } elseif (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        $check = mysqli_prepare(
            $conn,
            "SELECT id FROM users
             WHERE email = ? AND id != ?"
        );
        mysqli_stmt_bind_param(
            $check,
            "si",
            $newEmail,
            $userId
        );
        mysqli_stmt_execute($check);
        $checkResult = mysqli_stmt_get_result($check);

        if (mysqli_num_rows($checkResult) > 0) {
            $error = "This email is already registered.";
        } else {
            $update = mysqli_prepare(
                $conn,
                "UPDATE users
                 SET email = ?
                 WHERE id = ?"
            );
            mysqli_stmt_bind_param(
                $update,
                "si",
                $newEmail,
                $userId
            );

            if (mysqli_stmt_execute($update)) {
                $user["email"] = $newEmail;
                $success = "Email changed successfully.";
            } else {
                $error = "Unable to change email.";
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
    <title>Change Email | Inknest</title>
    <link rel="stylesheet" href="css/change_email.css?v=<?php echo time(); ?>">
    <script src="js/change_email.js" defer></script>
</head>

<body>
<div class="container">
    <h1>Change Email</h1>
    <p class="subtitle">Update your Inknest email address.</p>
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

    <form method="POST" action="change_email.php" id="emailForm">
        <div class="form-group">
            <label for="email">New Email</label>
            <input type="email" id="email" name="email" maxlength="100"
            placeholder="Enter new email" autocomplete="email" required>
        </div>
        <button type="submit">Change Email</button>
    </form>
    <a href="profile.php" class="back">Back to Profile</a>
</div>
</body>
</html>