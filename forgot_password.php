<?php
session_start();
include "config/database.php";
$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email=strtolower(trim($_POST["email"] ?? ""));
    if ($email === "") {
        $error="Please enter your email.";
    } elseif (!filter_var($email,FILTER_VALIDATE_EMAIL)) {
        $error="Please enter a valid email.";
    } else {
        $sql =
            mysqli_prepare(
                $conn,
                "
                SELECT id, email
                FROM users
                WHERE email = ?
                LIMIT 1
                "
            );
        if (!$sql) {
            $error ="Something went wrong. Please try again.";
        } else {
            mysqli_stmt_bind_param(
                $sql,
                "s",
                $email
            );
            mysqli_stmt_execute($sql);
            $result=mysqli_stmt_get_result($sql);
            if (mysqli_num_rows($result) === 1) {
                $user =mysqli_fetch_assoc($result);
                $_SESSION["reset_user_id"]=(int) $user["id"];
                $_SESSION["reset_email"]=$user["email"];
                unset(
                    $_SESSION["otp_verified"],
                    $_SESSION["otp_verified_email"],
                    $_SESSION["otp_verified_purpose"],
                    $_SESSION["otp_verified_user_id"]
                );
                header("Location: reset_password.php");
                exit();
            } else {
                $error ="No account found with this email.";
            }
            mysqli_stmt_close(
                $sql
            );
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | Inknest</title>
    <link rel="stylesheet" href="css/forgot_password.css?v=<?php echo time(); ?>">
    <script src="js/forgot_password.js" defer></script>
</head>
<body>
<nav class="navigation">
    <h1>Inknest</h1>
    <a href="login.php">Back</a>
</nav>
<div class="forgot-container">
    <div class="forgot-card">
        <h2>Forgot Password?</h2>
        <p class="subtitle">Enter Your Registered Email</p>
        <?php if (!empty($error)): ?>
            <div class="error-box">
                <?php
                echo htmlspecialchars(
                    $error,
                    ENT_QUOTES,
                    "UTF-8"
                );
                ?>
            </div>
        <?php endif; ?>
        <form id="forgotForm" action="forgot_password.php" method="post">
            <div class="data">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" autocomplete="email"  required>
            </div>
            <button type="submit">Continue</button>
            <p class="login-link">Remember your password?
                <a href="login.php">Login</a>
            </p>
        </form>
    </div>
</div>
</body>
</html>