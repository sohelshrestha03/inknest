<?php

session_start();

include "config/database.php";

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);

    if (empty($email)) {
        $error = "Please enter your email.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email.";
    } else {
        $sql = mysqli_prepare(
            $conn,
            "SELECT id FROM users WHERE email = ?"
        );

        mysqli_stmt_bind_param($sql, "s", $email);
        mysqli_stmt_execute($sql);
        $result = mysqli_stmt_get_result($sql);

        if (mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);
            $_SESSION["reset_user_id"] = $user["id"];
            header("Location: reset_password.php");
            exit();
        } else {
            $error = "No account found with this email.";
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
        <p class="subtitle">Enter your registered email address</p>

        <?php if (!empty($error)): ?>
            <div class="error-box">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>


        <form id="forgotForm" action="forgot_password.php" method="post">

            <div class="data">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>
            </div>


            <button type="submit">Continue</button>
            <p class="login-link">Remember your password?<a href="login.php">Login</a></p>
        </form>
    </div>
</div>

</body>
</html>