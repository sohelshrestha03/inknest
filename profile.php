<?php
session_start();
include "config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION["user_id"];
$stmt = mysqli_prepare(
    $conn,
    "SELECT first_name, last_name, user_name, email, phone_no
     FROM users
     WHERE id = ?"
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Profile | Inknest</title>
    <link rel="stylesheet" href="css/profile.css?v=<?php echo time(); ?>">
</head>

<body>
<nav class="navbar">
    <h1>Inknest</h1>
    <div class="nav-links">
        <a href="cart.php">Cart</a>
        <a href="home.php">Back</a>
    </div>
</nav>


<main class="container">
    <div class="profile-header">
        <h2>Manage Profile</h2>
        <p>Manage your account information.</p>
    </div>


    <div class="profile-card">
        <div class="profile-info">
            <h3>Account Information</h3>
            <p>
                <strong>Name:</strong>
                <?php
                echo htmlspecialchars(
                    $user["first_name"] . " " . $user["last_name"]
                );
                ?>
            </p>

            <p>
                <strong>Username:</strong>
                <?php echo htmlspecialchars($user["user_name"]); ?>
            </p>

            <p>
                <strong>Email:</strong>
                <?php echo htmlspecialchars($user["email"]); ?>
            </p>

            <p>
                <strong>Phone:</strong>
                <?php echo htmlspecialchars($user["phone_no"]); ?>
            </p>
        </div>


        <div class="profile-actions">
            <h3>Account Settings</h3>
            <a href="change_username.php">Change Username</a>
            <a href="change_email.php">Change Email</a>
            <a href="change_password.php">Change Password</a>
        </div>
    </div>
</main>
</body>
</html>