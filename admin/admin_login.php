<?php
session_start();

include "../config/database.php";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    if (empty($username) || empty($password)) {
        $error = "Please enter username and password.";
    } else {
        $sql = mysqli_prepare(
            $conn,
            "SELECT id, username, password
             FROM admins
             WHERE username = ?"
        );

        mysqli_stmt_bind_param(
            $sql,
            "s",
            $username
        );

        mysqli_stmt_execute($sql);
        $result = mysqli_stmt_get_result($sql);

        if (mysqli_num_rows($result) === 1) {
            $admin = mysqli_fetch_assoc($result);
            if (password_verify(
                $password,
                $admin["password"]
            )) {
                session_regenerate_id(true);
                $_SESSION["admin_id"] = $admin["id"];
                $_SESSION["admin_username"] = $admin["username"];
                header("Location: admin_dashboard.php");
                exit();
            } else {
                $error = "Invalid username or password.";
            }
        } else {
            $error = "Invalid username or password.";
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
    <title>Admin Login | Inknest</title>
    <link rel="stylesheet" href="../css/admin_login.css?v=<?php echo time(); ?>">
    <script src="../js/admin_login.js" defer></script>
</head>

<body>
<div class="login-container">
    <div class="login-card">
        <h1>Inknest</h1>
        <h2>Admin Login</h2>
        <p class="subtitle">Sign in to manage your store</p>

        <?php if (!empty($error)): ?>
            <div class="error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>


        <form id="adminLoginForm" action="admin_login.php" method="post">
            <div class="data">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter username" autocomplete="off" required>
            </div>

            <div class="data">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter password" required>
            </div>

            <button type="submit">Login</button>
        </form>
        <p class="back"><a href="../login.php">Customer Login</a></p>
    </div>
</div>

</body>
</html>