<?php
session_start();
include "config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}
$userId = (int) $_SESSION["user_id"];
$error = "";
$success = "";
$sql = mysqli_prepare(
    $conn,
    "SELECT id, first_name, last_name, user_name, email, phone_no
     FROM users
     WHERE id = ?"
);
mysqli_stmt_bind_param($sql, "i", $userId);
mysqli_stmt_execute($sql);
$result = mysqli_stmt_get_result($sql);

if (!$result || mysqli_num_rows($result) !== 1) {
    session_destroy();
    header("Location: login.php");
    exit();
}
$user = mysqli_fetch_assoc($result);
$firstName = $user["first_name"];
$lastName = $user["last_name"];
$username = $user["user_name"];
$email = $user["email"];
$phone = $user["phone_no"];
$profilePicture = "";
$profileSql = mysqli_prepare(
    $conn,
    "SELECT profile_picture
     FROM user_profiles
     WHERE user_id = ?"
);
mysqli_stmt_bind_param($profileSql, "i", $userId);
mysqli_stmt_execute($profileSql);
$profileResult = mysqli_stmt_get_result($profileSql);

if ($profileResult && mysqli_num_rows($profileResult) === 1) {
    $profileData = mysqli_fetch_assoc($profileResult);
    $profilePicture = $profileData["profile_picture"] ?? "";
}

if (isset($_POST["update_profile"])) {
    $newFirstName = trim($_POST["first_name"] ?? "");
    $newLastName = trim($_POST["last_name"] ?? "");
    $newUsername = trim($_POST["username"] ?? "");
    $newEmail = trim($_POST["email"] ?? "");
    $newPhone = trim($_POST["phone"] ?? "");
    if ($newFirstName === "" ||$newLastName === "" ||$newUsername === "" ||$newEmail === "" ||$newPhone === "") {
        $error = "Please fill in all fields.";
    } elseif (!preg_match("/^[A-Za-z]+$/", $newFirstName)) {
        $error = "First name can contain letters only.";
    } elseif (!preg_match("/^[A-Za-z]+$/", $newLastName)) {
        $error = "Last name can contain letters only.";
    } elseif (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (!preg_match("/^[0-9]{10}$/", $newPhone)) {
        $error = "Phone number must contain exactly 10 digits.";
    } else {
        $checkUsername = mysqli_prepare(
            $conn,
            "SELECT id
             FROM users
             WHERE user_name = ?
             AND id != ?"
        );
        mysqli_stmt_bind_param(
            $checkUsername,
            "si",
            $newUsername,
            $userId
        );
        mysqli_stmt_execute($checkUsername);
        $usernameResult = mysqli_stmt_get_result($checkUsername);
        if ($usernameResult && mysqli_num_rows($usernameResult) > 0) {
            $error = "Username is already taken.";
        } else {
            $checkEmail = mysqli_prepare(
                $conn,
                "SELECT id
                 FROM users
                 WHERE email = ?
                 AND id != ?"
            );
            mysqli_stmt_bind_param(
                $checkEmail,
                "si",
                $newEmail,
                $userId
            );
            mysqli_stmt_execute($checkEmail);
            $emailResult = mysqli_stmt_get_result($checkEmail);
            if ($emailResult && mysqli_num_rows($emailResult) > 0) {
                $error = "Email is already registered.";
            } else {
                $updateSql = mysqli_prepare(
                    $conn,
                    "UPDATE users
                     SET first_name = ?,
                         last_name = ?,
                         user_name = ?,
                         email = ?,
                         phone_no = ?
                     WHERE id = ?"
                );
                mysqli_stmt_bind_param(
                    $updateSql,
                    "sssssi",
                    $newFirstName,
                    $newLastName,
                    $newUsername,
                    $newEmail,
                    $newPhone,
                    $userId
                );

                if (mysqli_stmt_execute($updateSql)) {
                    $_SESSION["username"] = $newUsername;
                    $firstName = $newFirstName;
                    $lastName = $newLastName;
                    $username = $newUsername;
                    $email = $newEmail;
                    $phone = $newPhone;
                    $success = "Profile updated successfully.";
                } else {
                    $error = "Failed to update profile.";
                }
            }
        }
    }
}

if (isset($_POST["upload_picture"])) {
    if (!isset($_FILES["profile_picture"])) {
        $error = "Please select an image.";
    } else {
        $file = $_FILES["profile_picture"];
        if ($file["error"] !== UPLOAD_ERR_OK) {
            $error = "There was an error uploading the image.";
        } elseif ($file["size"] > 5 * 1024 * 1024) {
            $error = "Image size must be less than 5 MB.";
        } else {
            $imageInfo = getimagesize($file["tmp_name"]);
            if ($imageInfo === false) {
                $error = "The uploaded file is not a valid image.";
            } else {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file(
                    $finfo,
                    $file["tmp_name"]
                );
                finfo_close($finfo);
                $allowedTypes = [
                    "image/jpeg" => "jpg",
                    "image/png"  => "png",
                    "image/webp" => "webp"
                ];

                if (!array_key_exists($mimeType, $allowedTypes)) {
                    $error = "Only JPG, PNG, and WEBP images are allowed.";
                } else {
                    $extension = $allowedTypes[$mimeType];
                    $uploadDirectory = "images/profile/";
                    if (!is_dir($uploadDirectory)) {
                        mkdir($uploadDirectory, 0755, true);
                    }
                    $newFileName =
                        "profile_" .
                        $userId .
                        "_" .
                        bin2hex(random_bytes(8)) .
                        "." .
                        $extension;
                    $destination =
                        $uploadDirectory .
                        $newFileName;

                    if (move_uploaded_file(
                        $file["tmp_name"],
                        $destination
                    )) {
                        $oldPicture = "";
                        $oldSql = mysqli_prepare(
                            $conn,
                            "SELECT profile_picture
                             FROM user_profiles
                             WHERE user_id = ?"
                        );
                        mysqli_stmt_bind_param(
                            $oldSql,
                            "i",
                            $userId
                        );
                        mysqli_stmt_execute($oldSql);
                        $oldResult = mysqli_stmt_get_result($oldSql);

                        if ($oldResult && mysqli_num_rows($oldResult) === 1) {
                            $oldData=mysqli_fetch_assoc($oldResult);
                            $oldPicture =$oldData["profile_picture"] ?? "";
                        }
                        $checkProfile = mysqli_prepare(
                            $conn,
                            "SELECT id
                             FROM user_profiles
                             WHERE user_id = ?"
                        );
                        mysqli_stmt_bind_param(
                            $checkProfile,
                            "i",
                            $userId
                        );
                        mysqli_stmt_execute($checkProfile);
                        $checkResult=mysqli_stmt_get_result($checkProfile);
                        if ($checkResult && mysqli_num_rows($checkResult) === 1) {
                            $updatePicture = mysqli_prepare(
                                $conn,
                                "UPDATE user_profiles
                                 SET profile_picture = ?
                                 WHERE user_id = ?"
                            );
                            mysqli_stmt_bind_param(
                                $updatePicture,
                                "si",
                                $newFileName,
                                $userId
                            );

                            $dbSuccess=mysqli_stmt_execute($updatePicture);
                        } else {
                            $insertPicture = mysqli_prepare(
                                $conn,
                                "INSERT INTO user_profiles
                                 (user_id, profile_picture)
                                 VALUES (?, ?)"
                            );
                            mysqli_stmt_bind_param(
                                $insertPicture,
                                "is",
                                $userId,
                                $newFileName
                            );
                            $dbSuccess =mysqli_stmt_execute($insertPicture);
                        }

                        if ($dbSuccess) {
                            if ($oldPicture !== "") {
                                $oldFile = $uploadDirectory .
                                    basename($oldPicture);
                                if (file_exists($oldFile) && is_file($oldFile)) {
                                    unlink($oldFile);
                                }
                            }
                            $profilePicture = $newFileName;
                            $success ="Profile picture updated successfully.";
                        } else {
                            if (file_exists($destination)) {
                                unlink($destination);
                            }
                            $error ="Failed to save profile picture.";
                        }
                    } else {
                        $error ="Failed to upload profile picture.";
                    }
                }
            }
        }
    }
}

if (isset($_POST["delete_picture"])) {
    $deleteSql = mysqli_prepare(
        $conn,
        "SELECT profile_picture
         FROM user_profiles
         WHERE user_id = ?"
    );
    mysqli_stmt_bind_param(
        $deleteSql,
        "i",
        $userId
    );
    mysqli_stmt_execute($deleteSql);
    $deleteResult =mysqli_stmt_get_result($deleteSql);
    $pictureToDelete = "";
    if ($deleteResult && mysqli_num_rows($deleteResult) === 1) {
        $deleteData =mysqli_fetch_assoc($deleteResult);
        $pictureToDelete =$deleteData["profile_picture"] ?? "";
    }
    $removeSql = mysqli_prepare(
        $conn,
        "DELETE FROM user_profiles
         WHERE user_id = ?"
    );
    mysqli_stmt_bind_param(
        $removeSql,
        "i",
        $userId
    );
    if (mysqli_stmt_execute($removeSql)) {
        if ($pictureToDelete !== "") {
            $picturePath ="images/profile/" .basename($pictureToDelete);
            if (file_exists($picturePath) && is_file($picturePath)) {
                unlink($picturePath);
            }
        }
        $profilePicture = "";
        $success ="Profile picture deleted successfully.";
    } else {
        $error="Failed to delete profile picture.";
    }
}

if (isset($_POST["change_password"])) {
    $currentPassword =$_POST["current_password"] ?? "";
    $newPassword =$_POST["new_password"] ?? "";
    $confirmPassword =$_POST["confirm_password"] ?? "";

    if ($currentPassword === "" ||$newPassword === "" ||$confirmPassword === "") {
        $error ="Please fill in all password fields.";
    } elseif (strlen($newPassword) < 8) {
        $error ="New password must be at least 8 characters.";
    } elseif ($newPassword !== $confirmPassword) {
        $error ="New passwords do not match.";
    } else {
        $passwordSql = mysqli_prepare(
            $conn,
            "SELECT new_Password
             FROM users
             WHERE id = ?"
        );
        mysqli_stmt_bind_param(
            $passwordSql,
            "i",
            $userId
        );
        mysqli_stmt_execute($passwordSql);
        $passwordResult =mysqli_stmt_get_result($passwordSql);
        $passwordData =mysqli_fetch_assoc($passwordResult);
        $storedPassword =$passwordData["new_Password"] ?? "";

        if (!password_verify($currentPassword,$storedPassword)) {
            $error ="Current password is incorrect.";
        } else {
            $hashedPassword =password_hash($newPassword,PASSWORD_DEFAULT);
            $updatePassword = mysqli_prepare(
                $conn,
                "UPDATE users
                 SET new_Password = ?,
                     confirm_Password = ?
                 WHERE id = ?"
            );
            mysqli_stmt_bind_param(
                $updatePassword,
                "ssi",
                $hashedPassword,
                $hashedPassword,
                $userId
            );
            if (mysqli_stmt_execute($updatePassword)) {
                $success="Password changed successfully.";
            } else {
                $error ="Failed to change password.";
            }
        }
    }
}

$profileImageUrl = "";
if ($profilePicture !== "") {
    $profileImageUrl = "images/profile/" .htmlspecialchars($profilePicture);
}

$initial = strtoupper(
    substr(
        trim($username),
        0,
        1
    )
);
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
    <a href="home.php" class="logo">Inknest</a>
    <div class="nav-right">
        <div class="user-info">
            <?php if ($profilePicture !== ""): ?>
                <img src="<?php echo $profileImageUrl; ?>" alt="Profile Picture" class="profile-picture-small">
            <?php else: ?>
                <div class="profile-placeholder-small">
                    <?php echo htmlspecialchars($initial); ?>
                </div>
            <?php endif; ?>
            <span>Hi,<?php echo htmlspecialchars($username); ?></span>
        </div>

        <a href="cart.php">Cart</a>
        <a href="home.php">Back</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>



<main class="container">
    <div class="page-title">
        <h1>Manage Profile</h1>
        <p>Change your account information and profile picture.</p>
    </div>

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

    <div class="profile-grid">
        <div class="card picture-section">
            <h2>Profile Picture</h2>
            <?php if ($profilePicture !== ""): ?>
                <img src="<?php echo $profileImageUrl; ?>" alt="Profile Picture" class="profile-image-large">
            <?php else: ?>
                <div class="profile-placeholder-large">
                    <?php echo htmlspecialchars($initial); ?>
                </div>
            <?php endif; ?>

            <p class="picture-info">
                JPG, PNG or WEBP<br>
                Maximum size: 5 MB
            </p>

            <form method="POST" enctype="multipart/form-data">
                <input type="file" name="profile_picture" class="file-input" accept="image/jpeg,image/png,image/webp" required>
                <button type="submit" name="upload_picture" class="btn btn-primary">
                    <?php
                    echo $profilePicture !== ""
                        ? "Change Picture"
                        : "Upload Picture";
                    ?>
                </button>
            </form>

            <?php if ($profilePicture !== ""): ?>
                <form method="POST">

                    <button
                        type="submit"
                        name="delete_picture"
                        class="btn btn-danger"
                        onclick="return confirm('Are you sure you want to delete your profile picture?');">
                        Delete Picture
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2>Account Information</h2>
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($firstName); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($lastName); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>

                <div class="form-group">
                    <label for="phone"> Phone Number </label>

                    <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" maxlength="10" pattern="[0-9]{10}" required>
                </div>
                <button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
            </form>


            <hr class="section-divider">
            <h2>Change Password</h2>
            <p class="password-note">Your new password must be at least 8 characters.</p>
            <form method="POST">
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>

                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" minlength="8" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" minlength="8" required>
                </div>

                <div class="forgot-password">
                    <a href="forgot_password.php">Forgot Password?</a>
                </div>
                
                <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
            </form>
        </div>
    </div>
</main>

<script>
    const phoneInput =document.getElementById("phone");
    if (phoneInput) {
        phoneInput.addEventListener(
            "input",
            function () {
                this.value =
                    this.value
                        .replace(/\D/g, "")
                        .slice(0, 10);
            }
        );
    }

    const passwordForm =document.querySelector('button[name="change_password"]');
    if (passwordForm) {
        passwordForm
            .closest("form")
            .addEventListener(
                "submit",
                function (event) {
                    const newPassword =
                        document.getElementById(
                            "new_password"
                        ).value;
                    const confirmPassword =
                        document.getElementById(
                            "confirm_password"
                        ).value;
                    if (newPassword !==confirmPassword) {
                        event.preventDefault();
                        alert("New passwords do not match.");
                    }
                }
            );
    }

    const fileInput =document.querySelector('input[name="profile_picture"]');
    if (fileInput) {
        fileInput.addEventListener(
            "change",
            function () {
                const file = this.files[0];
                if (!file) {
                    return;
                }
                const allowedTypes = [
                    "image/jpeg",
                    "image/png",
                    "image/webp"
                ];

                if (!allowedTypes.includes(file.type)) {
                    alert("Only JPG, PNG and WEBP images are allowed.");
                    this.value = "";
                    return;
                }

                if (file.size >5 * 1024 * 1024) {
                    alert("Image size must be less than 5 MB.");
                    this.value = "";
                    return;
                }
            }
        );
    }
</script>
</body>
</html>