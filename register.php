<?php
include "config/database.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $first = trim($_POST["fname"]);
    $last = trim($_POST["lname"]);
    $userName = trim($_POST["uname"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["contact"]);
    $new = $_POST["npassword"];
    $cpass = $_POST["cpassword"];

    if ($new !== $cpass) {
        echo "<script>
                alert('Passwords do not match.');
                window.history.back();
              </script>";
        exit();
    }

    $check = mysqli_prepare(
        $conn,
        "SELECT user_name, email, phone_no
         FROM users
         WHERE user_name = ?
            OR email = ?
            OR phone_no = ?"
    );

    mysqli_stmt_bind_param(
        $check,
        "sss",
        $userName,
        $email,
        $phone
    );

    mysqli_stmt_execute($check);
    $result = mysqli_stmt_get_result($check);

    if (mysqli_num_rows($result) > 0) {
        while ($existingUser = mysqli_fetch_assoc($result)) {
            if ($existingUser["user_name"] === $userName) {
                echo "<script>
                        alert('Username is already used. Please choose another username.');
                        window.history.back();
                      </script>";
                exit();
            }

            if ($existingUser["email"] === $email) {
                echo "<script>
                        alert('Email is already used. Please use another email.');
                        window.history.back();
                      </script>";
                exit();
            }

            if ($existingUser["phone_no"] === $phone) {
                echo "<script>
                        alert('Phone number is already used. Please use another phone number.');
                        window.history.back();
                      </script>";
                exit();
            }
        }
    }

    mysqli_stmt_close($check);

    $newHash = password_hash($new, PASSWORD_DEFAULT);
    $insert = mysqli_prepare(
        $conn,
        "INSERT INTO users
        (first_name, last_name, user_name, email, phone_no, new_Password, confirm_Password)
        VALUES (?, ?, ?, ?, ?, ?, ?)"
    );

    mysqli_stmt_bind_param(
        $insert,
        "sssssss",
        $first,
        $last,
        $userName,
        $email,
        $phone,
        $newHash,
        $newHash
    );
    try {
        if (mysqli_stmt_execute($insert)) {
            echo "<script>
                    alert('You are registered successfully.');
                    window.location='login.php';
                  </script>";
            exit();
        }
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) {
            echo "<script>
                    alert('Username, email, or phone number is already registered.');
                    window.history.back();
                  </script>";
        } else {
            echo "Registration failed. Please try again.";
        }
        exit();
    }
    mysqli_stmt_close($insert);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Inknest</title>
    <link rel="stylesheet" href="css/register.css?v=<?php echo time(); ?>">
    <script src="js/register.js" defer></script>
</head>

<body>

<nav class="navigation">
    <h1>Inknest</h1>
    <a href="login.php">Back</a>
</nav>

<div class="register-container">
    <div class="register-card">
        <h2>Create Account</h2>
        <p class="subtitle">
            Register your account
        </p>

        <form id="registerForm" action="register.php" method="post">
            <div class="data">
                <label for="fname">First Name</label>
                <input type="text" id="fname" name="fname" placeholder="Enter your first name" autocomplete="off" required>
            </div>

            <div class="data">
                <label for="lname">Last Name</label>
                <input type="text" id="lname" name="lname" placeholder="Enter your last name" autocomplete="off" required>
            </div>

            <div class="data">
                <label for="uname">Username</label>
                <input type="text" id="uname" name="uname" placeholder="Enter your username" autocomplete="off" required>
            </div>

            <div class="data">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" autocomplete="off" required>
            </div>

            <div class="data">
                <label for="contact">Phone Number</label>
                <input type="text" id="contact" name="contact" placeholder="Enter phone number" maxlength="10" autocomplete="off"  required>
            </div>

            <div class="data">
                <label for="npassword">Password</label>
                <input type="password" id="npassword" name="npassword" placeholder="Create a password" required>
            </div>

            <div class="data">
                <label for="cpassword">Confirm Password</label>
                <input type="password" id="cpassword" name="cpassword" placeholder="Confirm your password" required>
            </div>

            <div class="buttons">
                <button type="submit">Register</button>
                <button type="reset" class="cancel">Cancel</button>
            </div>

            <p class="login-link">
                Already have an account?
                <a href="login.php">Login</a>
            </p>
        </form>
    </div>
</div>
</body>
</html>
