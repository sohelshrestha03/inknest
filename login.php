<?php
session_start();

include "config/database.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = trim($_POST["login"]);
    $password = $_POST["password"];

    if (empty($login) || empty($password)) {
        $error = "Please enter username/phone and password.";
    } else {
        $sql = mysqli_prepare(
            $conn,
            "SELECT id, first_name, last_name, user_name, phone_no, new_Password
             FROM users
             WHERE user_name = ? OR phone_no = ?"
        );

        mysqli_stmt_bind_param(
            $sql,
            "ss",
            $login,
            $login
        );

        mysqli_stmt_execute($sql);

        $result = mysqli_stmt_get_result($sql);

        if (mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);
            if (password_verify($password, $user["new_Password"])) {
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["username"] = $user["user_name"];
                $_SESSION["first_name"] = $user["first_name"];
                $_SESSION["last_name"] = $user["last_name"];
                header("Location: home.php");
                exit();
            } else {
                $error = "Invalid username/phone or password.";
            }
        } else {
            $error = "Invalid username/phone or password.";
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
    <title>Login | Inknest</title>
    <link rel="stylesheet" href="css/login.css?v=<?php echo time(); ?>">
    <script src="js/login.js" defer></script>
</head>

<body>
<nav class="navigation">
    <h1>Inknest</h1>
</nav>


<div class="login-container">
    <div class="login-card">
        <h2>Welcome Back</h2>
        <p class="subtitle">Login to your account</p>


        <?php if (!empty($error)): ?>
            <div class="error-box">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>


        <form id="loginForm" action="login.php" method="post">
            <div class="data">
                <label for="login">Username or Phone Number</label>
                <input type="text" id="login" name="login" placeholder="Enter username or phone" autocomplete="off" required>
            </div>


            <div class="data">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>


            <div class="forgot">
                <a href="forgot_password.php">Forgot password?</a>
            </div>


            <div class="buttons">
                <button type="submit">Login</button>
            </div>


            <p class="register-link">Don't have an account?<a href="register.php">Register</a></p>
        </form>
    </div>
</div>

</body>
</html>