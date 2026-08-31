<?php

session_start();

include "config/database.php";

if (!isset($_SESSION["reset_user_id"])) {
    header("Location: forgot_password.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newPassword = $_POST["new_password"];
    $confirmPassword = $_POST["confirm_password"];

    if (empty($newPassword) || empty($confirmPassword)) {
        $error = "Please fill in both fields.";
    } elseif (strlen($newPassword) < 8) {
        $error = "Password must be at least 8 characters.";
    } elseif ($newPassword !== $confirmPassword) {
        $error = "Passwords do not match.";
    } else {
        $passwordHash = password_hash(
            $newPassword,
            PASSWORD_DEFAULT
        );
        $sql = mysqli_prepare(
            $conn,
            "UPDATE users SET new_Password = ?, confirm_Password = ? WHERE id = ?"
        );

        mysqli_stmt_bind_param(
            $sql,
            "ssi",
            $passwordHash,
            $passwordHash,
            $_SESSION["reset_user_id"]
        );

        if (mysqli_stmt_execute($sql)) {
            unset($_SESSION["reset_user_id"]);
            echo "<script>
                    alert('Password changed successfully.');
                    window.location='login.php';
                  </script>";
            exit();
        } else {
            $error = "Something went wrong. Please try again.";
        }
        mysqli_stmt_close($sql);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | Inknest</title>
    <link rel="stylesheet" href="css/reset_password.css?v=<?php echo time(); ?>">
    <script src="js/reset_password.js" defer></script>
</head>

<body>

<nav class="navigation">
    <h1>Inknest</h1>
    <a href="login.php">Back</a>
</nav>


<div class="reset-container">
    <div class="reset-card">
        <h2>Reset Password</h2>
        <p class="subtitle">Create your new password</p>

        <?php if (!empty($error)): ?>
            <div class="error-box">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form id="resetForm" action="reset_password.php" method="post">
            <div class="data">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" placeholder="Enter new password" required>
            </div>


            <div class="data">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required>
            </div>

            <button type="submit">Reset Password</button>
        </form>
    </div>
</div>

</body>
</html>