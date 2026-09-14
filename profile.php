<?php

session_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

require_once "config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$userId = (int) $_SESSION["user_id"];

$error = "";
$success = "";
$sendOtpNow = false;

$userSql = mysqli_prepare(
    $conn,
    "
    SELECT
        id,
        first_name,
        last_name,
        user_name,
        email,
        phone_no
    FROM users
    WHERE id = ?
    LIMIT 1
    "
);

if (!$userSql) {
    session_destroy();
    header("Location: login.php");
    exit();
}

mysqli_stmt_bind_param(
    $userSql,
    "i",
    $userId
);

if (!mysqli_stmt_execute($userSql)) {
    mysqli_stmt_close($userSql);
    session_destroy();
    header("Location: login.php");
    exit();
}

mysqli_stmt_bind_result(
    $userSql,
    $dbUserId,
    $firstName,
    $lastName,
    $username,
    $email,
    $phone
);

if (!mysqli_stmt_fetch($userSql)) {
    mysqli_stmt_close($userSql);
    session_destroy();
    header("Location: login.php");
    exit();
}

mysqli_stmt_close($userSql);

$profilePicture = "";

$profileSql = mysqli_prepare(
    $conn,
    "
    SELECT profile_picture
    FROM user_profiles
    WHERE user_id = ?
    LIMIT 1
    "
);

if ($profileSql) {
    mysqli_stmt_bind_param(
        $profileSql,
        "i",
        $userId
    );

    if (mysqli_stmt_execute($profileSql)) {
        mysqli_stmt_bind_result(
            $profileSql,
            $profilePictureValue
        );

        if (mysqli_stmt_fetch($profileSql)) {
            $profilePicture = $profilePictureValue ?? "";
        }
    }

    mysqli_stmt_close($profileSql);
}

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["update_profile"])
) {
    $newFirstName = trim($_POST["first_name"] ?? "");
    $newLastName = trim($_POST["last_name"] ?? "");
    $newUsername = trim($_POST["username"] ?? "");

    $newEmail = strtolower(
        trim($_POST["email"] ?? "")
    );

    $newPhone = trim($_POST["phone"] ?? "");

    if (
        $newFirstName === "" ||
        $newLastName === "" ||
        $newUsername === "" ||
        $newEmail === "" ||
        $newPhone === ""
    ) {
        $error = "Please fill in all fields.";
    } elseif (
        !preg_match(
            "/^[A-Za-z]+$/",
            $newFirstName
        )
    ) {
        $error = "First name can contain letters only.";
    } elseif (
        !preg_match(
            "/^[A-Za-z]+$/",
            $newLastName
        )
    ) {
        $error = "Last name can contain letters only.";
    } elseif (
        !filter_var(
            $newEmail,
            FILTER_VALIDATE_EMAIL
        )
    ) {
        $error = "Please enter a valid email address.";
    } elseif (
        !preg_match(
            "/^[0-9]{10}$/",
            $newPhone
        )
    ) {
        $error = "Phone number must contain exactly 10 digits.";
    }

    if ($error === "") {
        $checkUsername = mysqli_prepare(
            $conn,
            "
            SELECT id
            FROM users
            WHERE user_name = ?
              AND id != ?
            LIMIT 1
            "
        );

        if (!$checkUsername) {
            $error = "Database error. Please try again.";
        } else {
            mysqli_stmt_bind_param(
                $checkUsername,
                "si",
                $newUsername,
                $userId
            );

            mysqli_stmt_execute($checkUsername);

            mysqli_stmt_bind_result(
                $checkUsername,
                $duplicateUsernameId
            );

            if (mysqli_stmt_fetch($checkUsername)) {
                $error = "Username is already taken.";
            }

            mysqli_stmt_close($checkUsername);
        }
    }

    if ($error === "") {
        $checkEmail = mysqli_prepare(
            $conn,
            "
            SELECT id
            FROM users
            WHERE email = ?
              AND id != ?
            LIMIT 1
            "
        );

        if (!$checkEmail) {
            $error = "Database error. Please try again.";
        } else {
            mysqli_stmt_bind_param(
                $checkEmail,
                "si",
                $newEmail,
                $userId
            );

            mysqli_stmt_execute($checkEmail);

            mysqli_stmt_bind_result(
                $checkEmail,
                $duplicateEmailId
            );

            if (mysqli_stmt_fetch($checkEmail)) {
                $error = "Email is already registered.";
            }

            mysqli_stmt_close($checkEmail);
        }
    }

    if ($error === "") {
        $checkPhone = mysqli_prepare(
            $conn,
            "
            SELECT id
            FROM users
            WHERE phone_no = ?
              AND id != ?
            LIMIT 1
            "
        );

        if (!$checkPhone) {
            $error = "Database error. Please try again.";
        } else {
            mysqli_stmt_bind_param(
                $checkPhone,
                "si",
                $newPhone,
                $userId
            );

            mysqli_stmt_execute($checkPhone);

            mysqli_stmt_bind_result(
                $checkPhone,
                $duplicatePhoneId
            );

            if (mysqli_stmt_fetch($checkPhone)) {
                $error = "Phone number is already registered.";
            }

            mysqli_stmt_close($checkPhone);
        }
    }

    if ($error === "") {
        $emailChanged = $newEmail !== strtolower($email);
        $phoneChanged = $newPhone !== $phone;

        if ($emailChanged || $phoneChanged) {
            if ($emailChanged) {
                $otpPurpose = "change_email";
            } else {
                $otpPurpose = "change_phone";
            }

            $_SESSION["pending_profile_update"] = [
                "first_name" => $newFirstName,
                "last_name" => $newLastName,
                "username" => $newUsername,
                "email" => $newEmail,
                "phone" => $newPhone
            ];

            $_SESSION["profile_otp_action"] = "account_update";
            $_SESSION["profile_otp_email"] = strtolower($email);
            $_SESSION["profile_otp_purpose"] = $otpPurpose;

            unset(
                $_SESSION["otp_verified"],
                $_SESSION["otp_verified_email"],
                $_SESSION["otp_verified_purpose"],
                $_SESSION["otp_verified_user_id"]
            );

            $sendOtpNow = true;
        } else {
            $updateSql = mysqli_prepare(
                $conn,
                "
                UPDATE users
                SET
                    first_name = ?,
                    last_name = ?,
                    user_name = ?,
                    email = ?,
                    phone_no = ?
                WHERE id = ?
                "
            );

            if (!$updateSql) {
                $error = "Database error. Please try again.";
            } else {
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

                mysqli_stmt_close($updateSql);
            }
        }
    }
}

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["upload_picture"])
) {
    if (!isset($_FILES["profile_picture"])) {
        $error = "Please select an image.";
    } else {
        $file = $_FILES["profile_picture"];

        if ($file["error"] !== UPLOAD_ERR_OK) {
            $error = "There was an error uploading the image.";
        } elseif (
            $file["size"] > 5 * 1024 * 1024
        ) {
            $error = "Image size must be less than 5 MB.";
        } else {
            $imageInfo = getimagesize(
                $file["tmp_name"]
            );

            if ($imageInfo === false) {
                $error = "The uploaded file is not a valid image.";
            } else {
                $finfo = finfo_open(
                    FILEINFO_MIME_TYPE
                );

                $mimeType = finfo_file(
                    $finfo,
                    $file["tmp_name"]
                );

                finfo_close($finfo);

                $allowedTypes = [
                    "image/jpeg" => "jpg",
                    "image/png" => "png",
                    "image/webp" => "webp"
                ];

                if (!isset($allowedTypes[$mimeType])) {
                    $error = "Only JPG, PNG, and WEBP images are allowed.";
                } else {
                    $extension = $allowedTypes[$mimeType];

                    $uploadDirectory = "images/profile/";

                    if (!is_dir($uploadDirectory)) {
                        mkdir(
                            $uploadDirectory,
                            0755,
                            true
                        );
                    }

                    $newFileName =
                        "profile_" .
                        $userId .
                        "_" .
                        bin2hex(
                            random_bytes(8)
                        ) .
                        "." .
                        $extension;

                    $destination =
                        $uploadDirectory .
                        $newFileName;

                    if (
                        move_uploaded_file(
                            $file["tmp_name"],
                            $destination
                        )
                    ) {
                        $oldPicture = "";

                        $oldSql = mysqli_prepare(
                            $conn,
                            "
                            SELECT profile_picture
                            FROM user_profiles
                            WHERE user_id = ?
                            LIMIT 1
                            "
                        );

                        if ($oldSql) {
                            mysqli_stmt_bind_param(
                                $oldSql,
                                "i",
                                $userId
                            );

                            mysqli_stmt_execute($oldSql);

                            mysqli_stmt_bind_result(
                                $oldSql,
                                $oldPictureValue
                            );

                            if (mysqli_stmt_fetch($oldSql)) {
                                $oldPicture =
                                    $oldPictureValue ?? "";
                            }

                            mysqli_stmt_close($oldSql);
                        }

                        $checkProfile = mysqli_prepare(
                            $conn,
                            "
                            SELECT id
                            FROM user_profiles
                            WHERE user_id = ?
                            LIMIT 1
                            "
                        );

                        $profileExists = false;

                        if ($checkProfile) {
                            mysqli_stmt_bind_param(
                                $checkProfile,
                                "i",
                                $userId
                            );

                            mysqli_stmt_execute(
                                $checkProfile
                            );

                            mysqli_stmt_bind_result(
                                $checkProfile,
                                $profileId
                            );

                            $profileExists =
                                mysqli_stmt_fetch(
                                    $checkProfile
                                );

                            mysqli_stmt_close(
                                $checkProfile
                            );
                        }

                        $dbSuccess = false;

                        if ($profileExists) {
                            $updatePicture =
                                mysqli_prepare(
                                    $conn,
                                    "
                                    UPDATE user_profiles
                                    SET profile_picture = ?
                                    WHERE user_id = ?
                                    "
                                );

                            if ($updatePicture) {
                                mysqli_stmt_bind_param(
                                    $updatePicture,
                                    "si",
                                    $newFileName,
                                    $userId
                                );

                                $dbSuccess =
                                    mysqli_stmt_execute(
                                        $updatePicture
                                    );

                                mysqli_stmt_close(
                                    $updatePicture
                                );
                            }
                        } else {
                            $insertPicture =
                                mysqli_prepare(
                                    $conn,
                                    "
                                    INSERT INTO user_profiles
                                    (
                                        user_id,
                                        profile_picture
                                    )
                                    VALUES (?, ?)
                                    "
                                );

                            if ($insertPicture) {
                                mysqli_stmt_bind_param(
                                    $insertPicture,
                                    "is",
                                    $userId,
                                    $newFileName
                                );

                                $dbSuccess =
                                    mysqli_stmt_execute(
                                        $insertPicture
                                    );

                                mysqli_stmt_close(
                                    $insertPicture
                                );
                            }
                        }

                        if ($dbSuccess) {
                            if ($oldPicture !== "") {
                                $oldFile =
                                    $uploadDirectory .
                                    basename(
                                        $oldPicture
                                    );

                                if (
                                    file_exists($oldFile) &&
                                    is_file($oldFile)
                                ) {
                                    unlink($oldFile);
                                }
                            }

                            $profilePicture =
                                $newFileName;

                            $success =
                                "Profile picture updated successfully.";
                        } else {
                            if (
                                file_exists(
                                    $destination
                                )
                            ) {
                                unlink($destination);
                            }

                            $error =
                                "Failed to save profile picture.";
                        }
                    } else {
                        $error =
                            "Failed to upload profile picture.";
                    }
                }
            }
        }
    }
}

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["delete_picture"])
) {
    $pictureToDelete = "";

    $deleteSql = mysqli_prepare(
        $conn,
        "
        SELECT profile_picture
        FROM user_profiles
        WHERE user_id = ?
        LIMIT 1
        "
    );

    if ($deleteSql) {
        mysqli_stmt_bind_param(
            $deleteSql,
            "i",
            $userId
        );

        mysqli_stmt_execute($deleteSql);

        mysqli_stmt_bind_result(
            $deleteSql,
            $pictureValue
        );

        if (mysqli_stmt_fetch($deleteSql)) {
            $pictureToDelete =
                $pictureValue ?? "";
        }

        mysqli_stmt_close($deleteSql);
    }

    $removeSql = mysqli_prepare(
        $conn,
        "
        DELETE FROM user_profiles
        WHERE user_id = ?
        "
    );

    if (!$removeSql) {
        $error =
            "Failed to delete profile picture.";
    } else {
        mysqli_stmt_bind_param(
            $removeSql,
            "i",
            $userId
        );

        if (
            mysqli_stmt_execute(
                $removeSql
            )
        ) {
            mysqli_stmt_close(
                $removeSql
            );

            if ($pictureToDelete !== "") {
                $picturePath =
                    "images/profile/" .
                    basename(
                        $pictureToDelete
                    );

                if (
                    file_exists($picturePath) &&
                    is_file($picturePath)
                ) {
                    unlink($picturePath);
                }
            }

            $profilePicture = "";

            $success =
                "Profile picture deleted successfully.";
        } else {
            mysqli_stmt_close(
                $removeSql
            );

            $error =
                "Failed to delete profile picture.";
        }
    }
}

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["change_password"])
) {
    $currentPassword =
        $_POST["current_password"] ?? "";

    $newPassword =
        $_POST["new_password"] ?? "";

    $confirmPassword =
        $_POST["confirm_password"] ?? "";

    if (
        $currentPassword === "" ||
        $newPassword === "" ||
        $confirmPassword === ""
    ) {
        $error =
            "Please fill in all password fields.";
    } elseif (
        strlen($newPassword) < 8
    ) {
        $error =
            "New password must be at least 8 characters.";
    } elseif (
        $newPassword !== $confirmPassword
    ) {
        $error =
            "New passwords do not match.";
    } else {
        $passwordSql =
            mysqli_prepare(
                $conn,
                "
                SELECT new_Password
                FROM users
                WHERE id = ?
                LIMIT 1
                "
            );

        if (!$passwordSql) {
            $error =
                "Unable to verify current password.";
        } else {
            mysqli_stmt_bind_param(
                $passwordSql,
                "i",
                $userId
            );

            mysqli_stmt_execute(
                $passwordSql
            );

            mysqli_stmt_bind_result(
                $passwordSql,
                $storedPassword
            );

            if (
                !mysqli_stmt_fetch(
                    $passwordSql
                )
            ) {
                $storedPassword = "";
            }

            mysqli_stmt_close(
                $passwordSql
            );

            if (
                !password_verify(
                    $currentPassword,
                    $storedPassword
                )
            ) {
                $error =
                    "Current password is incorrect.";
            } else {
                $_SESSION["pending_password_hash"] =
                    password_hash(
                        $newPassword,
                        PASSWORD_DEFAULT
                    );

                $_SESSION["profile_otp_action"] =
                    "change_password";

                $_SESSION["profile_otp_email"] =
                    strtolower($email);

                $_SESSION["profile_otp_purpose"] =
                    "change_password";

                unset(
                    $_SESSION["otp_verified"],
                    $_SESSION["otp_verified_email"],
                    $_SESSION["otp_verified_purpose"],
                    $_SESSION["otp_verified_user_id"]
                );

                $sendOtpNow = true;
            }
        }
    }
}

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["complete_otp_update"])
) {
    $sessionOtpEmail =
        strtolower(
            trim(
                $_SESSION["profile_otp_email"] ?? ""
            )
        );

    $verifiedOtpEmail =
        strtolower(
            trim(
                $_SESSION["otp_verified_email"] ?? ""
            )
        );

    $otpIsVerified =
        isset(
            $_SESSION["otp_verified"],
            $_SESSION["otp_verified_email"],
            $_SESSION["otp_verified_purpose"]
        ) &&
        $_SESSION["otp_verified"] === true &&
        $sessionOtpEmail !== "" &&
        $verifiedOtpEmail === $sessionOtpEmail;

    if (!$otpIsVerified) {
        $error =
            "Please verify the OTP first.";
    } else {
        $action =
            $_SESSION["profile_otp_action"] ?? "";

        if ($action === "change_password") {
            if (
                !isset(
                    $_SESSION["pending_password_hash"]
                )
            ) {
                $error =
                    "Password reset session expired. Please try again.";
            } else {
                $hashedPassword =
                    $_SESSION["pending_password_hash"];

                $updatePassword =
                    mysqli_prepare(
                        $conn,
                        "
                        UPDATE users
                        SET
                            new_Password = ?,
                            confirm_Password = ?
                        WHERE id = ?
                        "
                    );

                if (!$updatePassword) {
                    $error =
                        "Failed to change password.";
                } else {
                    mysqli_stmt_bind_param(
                        $updatePassword,
                        "ssi",
                        $hashedPassword,
                        $hashedPassword,
                        $userId
                    );

                    if (
                        mysqli_stmt_execute(
                            $updatePassword
                        )
                    ) {
                        $success =
                            "Password changed successfully.";

                        unset(
                            $_SESSION["pending_password_hash"]
                        );
                    } else {
                        $error =
                            "Failed to change password.";
                    }

                    mysqli_stmt_close(
                        $updatePassword
                    );
                }
            }
        } elseif (
            $action === "account_update"
        ) {
            $pending =
                $_SESSION["pending_profile_update"]
                ?? null;

            if (!$pending) {
                $error =
                    "Account update session expired. Please try again.";
            } else {
                $pendingFirstName =
                    trim(
                        $pending["first_name"] ?? ""
                    );

                $pendingLastName =
                    trim(
                        $pending["last_name"] ?? ""
                    );

                $pendingUsername =
                    trim(
                        $pending["username"] ?? ""
                    );

                $pendingEmail =
                    strtolower(
                        trim(
                            $pending["email"] ?? ""
                        )
                    );

                $pendingPhone =
                    trim(
                        $pending["phone"] ?? ""
                    );

                $duplicateFound = false;

                $checkUsername =
                    mysqli_prepare(
                        $conn,
                        "
                        SELECT id
                        FROM users
                        WHERE user_name = ?
                          AND id != ?
                        LIMIT 1
                        "
                    );

                if (!$checkUsername) {
                    $error =
                        "Database error. Please try again.";

                    $duplicateFound = true;
                } else {
                    mysqli_stmt_bind_param(
                        $checkUsername,
                        "si",
                        $pendingUsername,
                        $userId
                    );

                    mysqli_stmt_execute(
                        $checkUsername
                    );

                    mysqli_stmt_bind_result(
                        $checkUsername,
                        $duplicateUsernameId
                    );

                    if (
                        mysqli_stmt_fetch(
                            $checkUsername
                        )
                    ) {
                        $error =
                            "Username is already taken.";

                        $duplicateFound = true;
                    }

                    mysqli_stmt_close(
                        $checkUsername
                    );
                }

                if (!$duplicateFound) {
                    $checkEmail =
                        mysqli_prepare(
                            $conn,
                            "
                            SELECT id
                            FROM users
                            WHERE email = ?
                              AND id != ?
                            LIMIT 1
                            "
                        );

                    if (!$checkEmail) {
                        $error =
                            "Database error. Please try again.";

                        $duplicateFound = true;
                    } else {
                        mysqli_stmt_bind_param(
                            $checkEmail,
                            "si",
                            $pendingEmail,
                            $userId
                        );

                        mysqli_stmt_execute(
                            $checkEmail
                        );

                        mysqli_stmt_bind_result(
                            $checkEmail,
                            $duplicateEmailId
                        );

                        if (
                            mysqli_stmt_fetch(
                                $checkEmail
                            )
                        ) {
                            $error =
                                "Email is already registered.";

                            $duplicateFound = true;
                        }

                        mysqli_stmt_close(
                            $checkEmail
                        );
                    }
                }

                if (!$duplicateFound) {
                    $checkPhone =
                        mysqli_prepare(
                            $conn,
                            "
                            SELECT id
                            FROM users
                            WHERE phone_no = ?
                              AND id != ?
                            LIMIT 1
                            "
                        );

                    if (!$checkPhone) {
                        $error =
                            "Database error. Please try again.";

                        $duplicateFound = true;
                    } else {
                        mysqli_stmt_bind_param(
                            $checkPhone,
                            "si",
                            $pendingPhone,
                            $userId
                        );

                        mysqli_stmt_execute(
                            $checkPhone
                        );

                        mysqli_stmt_bind_result(
                            $checkPhone,
                            $duplicatePhoneId
                        );

                        if (
                            mysqli_stmt_fetch(
                                $checkPhone
                            )
                        ) {
                            $error =
                                "Phone number is already registered.";

                            $duplicateFound = true;
                        }

                        mysqli_stmt_close(
                            $checkPhone
                        );
                    }
                }

                if (!$duplicateFound) {
                    $updateProfile =
                        mysqli_prepare(
                            $conn,
                            "
                            UPDATE users
                            SET
                                first_name = ?,
                                last_name = ?,
                                user_name = ?,
                                email = ?,
                                phone_no = ?
                            WHERE id = ?
                            "
                        );

                    if (!$updateProfile) {
                        $error =
                            "Failed to update account information.";
                    } else {
                        mysqli_stmt_bind_param(
                            $updateProfile,
                            "sssssi",
                            $pendingFirstName,
                            $pendingLastName,
                            $pendingUsername,
                            $pendingEmail,
                            $pendingPhone,
                            $userId
                        );

                        if (
                            mysqli_stmt_execute(
                                $updateProfile
                            )
                        ) {
                            $_SESSION["username"] =
                                $pendingUsername;

                            $firstName =
                                $pendingFirstName;

                            $lastName =
                                $pendingLastName;

                            $username =
                                $pendingUsername;

                            $email =
                                $pendingEmail;

                            $phone =
                                $pendingPhone;

                            $success =
                                "Account information updated successfully.";

                            unset(
                                $_SESSION["pending_profile_update"]
                            );
                        } else {
                            $error =
                                "Failed to update account information.";
                        }

                        mysqli_stmt_close(
                            $updateProfile
                        );
                    }
                }
            }
        }

        if ($error === "") {
            unset(
                $_SESSION["otp_verified"],
                $_SESSION["otp_verified_email"],
                $_SESSION["otp_verified_purpose"],
                $_SESSION["otp_verified_user_id"],
                $_SESSION["profile_otp_action"],
                $_SESSION["profile_otp_email"],
                $_SESSION["profile_otp_purpose"]
            );
        }
    }
}

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["delete_account"])
) {
    $deletePassword =
        $_POST["delete_account_password"] ?? "";

    if ($deletePassword === "") {
        $error =
            "Please enter your current password.";
    } else {
        $passwordCheckSql =
            mysqli_prepare(
                $conn,
                "
                SELECT new_Password
                FROM users
                WHERE id = ?
                LIMIT 1
                "
            );

        if (!$passwordCheckSql) {
            $error =
                "Unable to verify your password. Please try again.";
        } else {
            mysqli_stmt_bind_param(
                $passwordCheckSql,
                "i",
                $userId
            );

            if (
                !mysqli_stmt_execute(
                    $passwordCheckSql
                )
            ) {
                mysqli_stmt_close(
                    $passwordCheckSql
                );

                $error =
                    "Unable to verify your password. Please try again.";
            } else {
                mysqli_stmt_bind_result(
                    $passwordCheckSql,
                    $storedDeletePassword
                );

                if (
                    !mysqli_stmt_fetch(
                        $passwordCheckSql
                    )
                ) {
                    $storedDeletePassword = "";
                }

                mysqli_stmt_close(
                    $passwordCheckSql
                );

                if (
                    !password_verify(
                        $deletePassword,
                        $storedDeletePassword
                    )
                ) {
                    $error =
                        "Current password is incorrect.";
                } else {
                    $pictureToDelete = "";

                    $pictureSql =
                        mysqli_prepare(
                            $conn,
                            "
                            SELECT profile_picture
                            FROM user_profiles
                            WHERE user_id = ?
                            LIMIT 1
                            "
                        );

                    if ($pictureSql) {
                        mysqli_stmt_bind_param(
                            $pictureSql,
                            "i",
                            $userId
                        );

                        mysqli_stmt_execute(
                            $pictureSql
                        );

                        mysqli_stmt_bind_result(
                            $pictureSql,
                            $pictureValue
                        );

                        if (
                            mysqli_stmt_fetch(
                                $pictureSql
                            )
                        ) {
                            $pictureToDelete =
                                $pictureValue ?? "";
                        }

                        mysqli_stmt_close(
                            $pictureSql
                        );
                    }

                    mysqli_begin_transaction(
                        $conn
                    );

                    try {
                        $otpDeleteSql =
                            mysqli_prepare(
                                $conn,
                                "
                                DELETE FROM password_otps
                                WHERE user_id = ?
                                   OR email = ?
                                "
                            );

                        if (!$otpDeleteSql) {
                            throw new Exception(
                                "Failed to prepare OTP deletion."
                            );
                        }

                        mysqli_stmt_bind_param(
                            $otpDeleteSql,
                            "is",
                            $userId,
                            $email
                        );

                        if (
                            !mysqli_stmt_execute(
                                $otpDeleteSql
                            )
                        ) {
                            mysqli_stmt_close(
                                $otpDeleteSql
                            );

                            throw new Exception(
                                "Failed to delete OTP records."
                            );
                        }

                        mysqli_stmt_close(
                            $otpDeleteSql
                        );

                        $profileDeleteSql =
                            mysqli_prepare(
                                $conn,
                                "
                                DELETE FROM user_profiles
                                WHERE user_id = ?
                                "
                            );

                        if (!$profileDeleteSql) {
                            throw new Exception(
                                "Failed to prepare profile deletion."
                            );
                        }

                        mysqli_stmt_bind_param(
                            $profileDeleteSql,
                            "i",
                            $userId
                        );

                        if (
                            !mysqli_stmt_execute(
                                $profileDeleteSql
                            )
                        ) {
                            mysqli_stmt_close(
                                $profileDeleteSql
                            );

                            throw new Exception(
                                "Failed to delete profile."
                            );
                        }

                        mysqli_stmt_close(
                            $profileDeleteSql
                        );

                        $userDeleteSql =
                            mysqli_prepare(
                                $conn,
                                "
                                DELETE FROM users
                                WHERE id = ?
                                LIMIT 1
                                "
                            );

                        if (!$userDeleteSql) {
                            throw new Exception(
                                "Failed to prepare account deletion."
                            );
                        }

                        mysqli_stmt_bind_param(
                            $userDeleteSql,
                            "i",
                            $userId
                        );

                        if (
                            !mysqli_stmt_execute(
                                $userDeleteSql
                            )
                        ) {
                            mysqli_stmt_close(
                                $userDeleteSql
                            );

                            throw new Exception(
                                "Failed to delete account."
                            );
                        }

                        if (
                            mysqli_stmt_affected_rows(
                                $userDeleteSql
                            ) !== 1
                        ) {
                            mysqli_stmt_close(
                                $userDeleteSql
                            );

                            throw new Exception(
                                "Account could not be deleted."
                            );
                        }

                        mysqli_stmt_close(
                            $userDeleteSql
                        );

                        if (
                            !mysqli_commit($conn)
                        ) {
                            throw new Exception(
                                "Failed to complete account deletion."
                            );
                        }

                        if ($pictureToDelete !== "") {
                            $picturePath =
                                "images/profile/" .
                                basename(
                                    $pictureToDelete
                                );

                            if (
                                file_exists(
                                    $picturePath
                                ) &&
                                is_file(
                                    $picturePath
                                )
                            ) {
                                unlink(
                                    $picturePath
                                );
                            }
                        }

                        $_SESSION = [];

                        if (
                            ini_get(
                                "session.use_cookies"
                            )
                        ) {
                            $params =
                                session_get_cookie_params();

                            setcookie(
                                session_name(),
                                "",
                                time() - 42000,
                                $params["path"],
                                $params["domain"],
                                $params["secure"],
                                $params["httponly"]
                            );
                        }

                        session_destroy();

                        header(
                            "Location: login.php?account_deleted=1"
                        );

                        exit();
                    } catch (Throwable $e) {
                        mysqli_rollback(
                            $conn
                        );

                        error_log(
                            "Account deletion error: " .
                            $e->getMessage()
                        );

                        $error =
                            "Unable to delete your account. Please try again.";
                    }
                }
            }
        }
    }
}

$profileImageUrl = "";

if ($profilePicture !== "") {
    $profileImageUrl =
        "images/profile/" .
        htmlspecialchars(
            $profilePicture,
            ENT_QUOTES,
            "UTF-8"
        );
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

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Manage Profile | Inknest</title>

    <link
        rel="stylesheet"
        href="css/profile.css?v=<?php echo time(); ?>"
    >
</head>

<body>

<nav class="navbar">

    <a href="home.php" class="logo">
        Inknest
    </a>

    <div class="nav-right">

        <div class="user-info">

            <?php if ($profilePicture !== ""): ?>

                <img
                    src="<?php echo $profileImageUrl; ?>"
                    alt="Profile Picture"
                    class="profile-picture-small"
                >

            <?php else: ?>

                <div class="profile-placeholder-small">
                    <?php
                    echo htmlspecialchars(
                        $initial,
                        ENT_QUOTES,
                        "UTF-8"
                    );
                    ?>
                </div>

            <?php endif; ?>

            <span>
                Hi,
                <?php
                echo htmlspecialchars(
                    $username,
                    ENT_QUOTES,
                    "UTF-8"
                );
                ?>
            </span>

        </div>

        <a href="cart.php">
            Cart
        </a>

        <a href="home.php">
            Back
        </a>

        <a href="logout.php">
            Logout
        </a>

    </div>

</nav>

<main class="container">

    <div class="page-title">

        <h1>
            Manage Profile
        </h1>

        <p>
            Change your account information and profile picture.
        </p>

    </div>

    <?php if ($error !== ""): ?>

        <div class="message error">

            <?php
            echo htmlspecialchars(
                $error,
                ENT_QUOTES,
                "UTF-8"
            );
            ?>

        </div>

    <?php endif; ?>

    <?php if ($success !== ""): ?>

        <div class="message success">

            <?php
            echo htmlspecialchars(
                $success,
                ENT_QUOTES,
                "UTF-8"
            );
            ?>

        </div>

    <?php endif; ?>

    <div class="profile-grid">

        <div class="card picture-section">

            <h2>
                Profile Picture
            </h2>

            <?php if ($profilePicture !== ""): ?>

                <img
                    src="<?php echo $profileImageUrl; ?>"
                    alt="Profile Picture"
                    class="profile-image-large"
                >

            <?php else: ?>

                <div class="profile-placeholder-large">

                    <?php
                    echo htmlspecialchars(
                        $initial,
                        ENT_QUOTES,
                        "UTF-8"
                    );
                    ?>

                </div>

            <?php endif; ?>

            <p class="picture-info">
                JPG, PNG or WEBP<br>
                Maximum size: 5 MB
            </p>

            <form
                method="POST"
                enctype="multipart/form-data"
            >

                <input
                    type="file"
                    name="profile_picture"
                    class="file-input"
                    accept="image/jpeg,image/png,image/webp"
                    required
                >

                <button
                    type="submit"
                    name="upload_picture"
                    class="btn btn-primary"
                >
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
                        onclick="return confirm('Are you sure you want to delete your profile picture?');"
                    >
                        Delete Picture
                    </button>

                </form>

            <?php endif; ?>

        </div>

        <div class="card">

            <h2>
                Account Information
            </h2>

            <form method="POST">

                <div class="form-row">

                    <div class="form-group">

                        <label for="first_name">
                            First Name
                        </label>

                        <input
                            type="text"
                            id="first_name"
                            name="first_name"
                            value="<?php
                                echo htmlspecialchars(
                                    $firstName,
                                    ENT_QUOTES,
                                    "UTF-8"
                                );
                            ?>"
                            required
                        >

                    </div>

                    <div class="form-group">

                        <label for="last_name">
                            Last Name
                        </label>

                        <input
                            type="text"
                            id="last_name"
                            name="last_name"
                            value="<?php
                                echo htmlspecialchars(
                                    $lastName,
                                    ENT_QUOTES,
                                    "UTF-8"
                                );
                            ?>"
                            required
                        >

                    </div>

                </div>

                <div class="form-group">

                    <label for="username">
                        Username
                    </label>

                    <input
                        type="text"
                        id="username"
                        name="username"
                        value="<?php
                            echo htmlspecialchars(
                                $username,
                                ENT_QUOTES,
                                "UTF-8"
                            );
                        ?>"
                        required
                    >

                </div>

                <div class="form-group">

                    <label for="email">
                        Email
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?php
                            echo htmlspecialchars(
                                $email,
                                ENT_QUOTES,
                                "UTF-8"
                            );
                        ?>"
                        required
                    >

                </div>

                <div class="form-group">

                    <label for="phone">
                        Phone Number
                    </label>

                    <input
                        type="text"
                        id="phone"
                        name="phone"
                        value="<?php
                            echo htmlspecialchars(
                                $phone,
                                ENT_QUOTES,
                                "UTF-8"
                            );
                        ?>"
                        maxlength="10"
                        pattern="[0-9]{10}"
                        required
                    >

                </div>

                <button
                    type="submit"
                    name="update_profile"
                    class="btn btn-primary"
                >
                    Save Changes
                </button>

            </form>

            <hr class="section-divider">

            <h2>
                Change Password
            </h2>

            <p class="password-note">
                Your new password must be at least 8 characters.
            </p>

            <form method="POST">

                <div class="form-group">

                    <label for="current_password">
                        Current Password
                    </label>

                    <input
                        type="password"
                        id="current_password"
                        name="current_password"
                        required
                    >

                </div>

                <div class="form-group">

                    <label for="new_password">
                        New Password
                    </label>

                    <input
                        type="password"
                        id="new_password"
                        name="new_password"
                        minlength="8"
                        required
                    >

                </div>

                <div class="form-group">

                    <label for="confirm_password">
                        Confirm Password
                    </label>

                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        minlength="8"
                        required
                    >

                </div>

                <div class="forgot-password">

                    <a href="forgot_password.php">
                        Forgot Password?
                    </a>

                </div>

                <button
                    type="submit"
                    name="change_password"
                    class="btn btn-primary"
                >
                    Change Password
                </button>

            </form>

            <?php
            if (
                $sendOtpNow ||
                (
                    isset(
                        $_SESSION["profile_otp_action"],
                        $_SESSION["profile_otp_email"]
                    ) &&
                    !isset(
                        $_SESSION["otp_verified"]
                    )
                )
            ):
            ?>

                <hr class="section-divider">

                <h2>
                    Verify OTP
                </h2>

                <p class="password-note">
                    A verification code has been sent to your current email address.
                </p>

                <div class="form-group">

                    <label for="profile_otp">
                        Verification Code
                    </label>

                    <input
                        type="text"
                        id="profile_otp"
                        maxlength="6"
                        inputmode="numeric"
                        autocomplete="one-time-code"
                        placeholder="Enter 6-digit OTP"
                    >

                </div>

                <button
                    type="button"
                    id="verifyProfileOtp"
                    class="btn btn-primary"
                >
                    Verify OTP
                </button>

                <button
                    type="button"
                    id="resendProfileOtp"
                    class="btn btn-primary"
                    style="margin-top:10px;"
                >
                    Resend OTP
                </button>

                <p
                    id="profileOtpMessage"
                    class="password-note"
                ></p>

                <form
                    id="completeOtpForm"
                    method="POST"
                    style="display:none;"
                >

                    <input
                        type="hidden"
                        name="complete_otp_update"
                        value="1"
                    >

                </form>

            <?php endif; ?>

        </div>

    </div>

    <hr class="section-divider">

    <div class="delete-account-section">

        <h2>
            Delete Account
        </h2>

        <p class="delete-account-warning">
            Permanently delete your Inknest account and all
            associated account data. This action cannot be undone.
        </p>

        <form
            method="POST"
            id="deleteAccountForm"
        >

            <div class="form-group">

                <label for="delete_account_password">
                    Current Password
                </label>

                <input
                    type="password"
                    id="delete_account_password"
                    name="delete_account_password"
                    placeholder="Enter your current password"
                    required
                >

            </div>

            <button
                type="submit"
                name="delete_account"
                value="1"
                class="btn btn-danger delete-account-btn"
            >
                Delete My Account
            </button>

        </form>

    </div>

</main>

<script>

const phoneInput =
    document.getElementById("phone");

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

const passwordButton =
    document.querySelector(
        'button[name="change_password"]'
    );

if (passwordButton) {

    const passwordForm =
        passwordButton.closest("form");

    passwordForm.addEventListener(
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

            if (
                newPassword !==
                confirmPassword
            ) {

                event.preventDefault();

                alert(
                    "New passwords do not match."
                );

            }

        }
    );

}

const profileOtpInput =
    document.getElementById(
        "profile_otp"
    );

const verifyProfileOtp =
    document.getElementById(
        "verifyProfileOtp"
    );

const resendProfileOtp =
    document.getElementById(
        "resendProfileOtp"
    );

const profileOtpMessage =
    document.getElementById(
        "profileOtpMessage"
    );

const profileOtpEmail =
    <?php
    echo json_encode(
        $_SESSION["profile_otp_email"] ?? $email
    );
    ?>;

const profileOtpPurpose =
    <?php
    echo json_encode(
        $_SESSION["profile_otp_purpose"]
        ?? "change_password"
    );
    ?>;

function setProfileOtpMessage(
    message,
    isError = false
) {

    if (!profileOtpMessage) {
        return;
    }

    profileOtpMessage.textContent =
        message;

    profileOtpMessage.style.color =
        isError
            ? "#c62828"
            : "#555";

}

async function sendProfileOtp() {

    if (!profileOtpEmail) {

        setProfileOtpMessage(
            "Email address is missing.",
            true
        );

        return;

    }

    if (resendProfileOtp) {
        resendProfileOtp.disabled = true;
    }

    setProfileOtpMessage(
        "Sending OTP..."
    );

    const formData =
        new FormData();

    formData.append(
        "email",
        profileOtpEmail
    );

    formData.append(
        "purpose",
        profileOtpPurpose
    );

    try {

        const response =
            await fetch(
                "auth/send_otp.php",
                {
                    method: "POST",
                    body: formData
                }
            );

        const text =
            await response.text();

        let data;

        try {

            data =
                JSON.parse(text);

        } catch (error) {

            console.error(
                "Invalid OTP response:",
                text
            );

            throw new Error(
                "Server returned an invalid response."
            );

        }

        if (!data.success) {

            throw new Error(
                data.message ||
                "Unable to send OTP."
            );

        }

        setProfileOtpMessage(
            "OTP sent successfully. Check your email."
        );

        if (profileOtpInput) {
            profileOtpInput.focus();
        }

        setTimeout(
            function () {

                if (resendProfileOtp) {
                    resendProfileOtp.disabled =
                        false;
                }

            },
            60000
        );

    } catch (error) {

        console.error(
            "Send OTP error:",
            error
        );

        setProfileOtpMessage(
            error.message ||
            "Unable to send OTP.",
            true
        );

        if (resendProfileOtp) {
            resendProfileOtp.disabled =
                false;
        }

    }

}

if (verifyProfileOtp) {

    verifyProfileOtp.addEventListener(
        "click",
        async function () {

            const otp =
                profileOtpInput
                    ? profileOtpInput.value.trim()
                    : "";

            if (
                !/^[0-9]{6}$/.test(otp)
            ) {

                setProfileOtpMessage(
                    "Please enter a valid 6-digit OTP.",
                    true
                );

                return;
            }

            verifyProfileOtp.disabled =
                true;

            setProfileOtpMessage(
                "Verifying OTP..."
            );

            const formData =
                new FormData();

            formData.append(
                "email",
                profileOtpEmail
            );

            formData.append(
                "otp",
                otp
            );

            formData.append(
                "purpose",
                profileOtpPurpose
            );

            try {

                const response =
                    await fetch(
                        "auth/verify_otp.php",
                        {
                            method: "POST",
                            body: formData
                        }
                    );

                const text =
                    await response.text();

                let data;

                try {

                    data =
                        JSON.parse(text);

                } catch (error) {

                    console.error(
                        "Invalid verification response:",
                        text
                    );

                    throw new Error(
                        "Server returned an invalid response."
                    );

                }

                if (!data.success) {

                    throw new Error(
                        data.message ||
                        "Invalid OTP."
                    );

                }

                setProfileOtpMessage(
                    "OTP verified. Updating your account..."
                );

                if (profileOtpInput) {
                    profileOtpInput.disabled =
                        true;
                }

                verifyProfileOtp.disabled =
                    true;

                if (resendProfileOtp) {
                    resendProfileOtp.disabled =
                        true;
                }

                const completeForm =
                    document.getElementById(
                        "completeOtpForm"
                    );

                if (completeForm) {
                    completeForm.submit();
                }

            } catch (error) {

                console.error(
                    "Verify OTP error:",
                    error
                );

                setProfileOtpMessage(
                    error.message ||
                    "Unable to verify OTP.",
                    true
                );

                verifyProfileOtp.disabled =
                    false;

            }

        }
    );

}

if (resendProfileOtp) {

    resendProfileOtp.addEventListener(
        "click",
        function () {

            if (
                !resendProfileOtp.disabled
            ) {
                sendProfileOtp();
            }

        }
    );

}

if (profileOtpInput) {

    profileOtpInput.addEventListener(
        "input",
        function () {

            this.value =
                this.value
                    .replace(/\D/g, "")
                    .slice(0, 6);

        }
    );

    profileOtpInput.addEventListener(
        "keydown",
        function (event) {

            if (event.key === "Enter") {

                event.preventDefault();

                if (verifyProfileOtp) {
                    verifyProfileOtp.click();
                }

            }

        }
    );

}

<?php if ($sendOtpNow): ?>

sendProfileOtp();

<?php endif; ?>

const fileInput =
    document.querySelector(
        'input[name="profile_picture"]'
    );

if (fileInput) {

    fileInput.addEventListener(
        "change",
        function () {

            const file =
                this.files[0];

            if (!file) {
                return;
            }

            const allowedTypes = [
                "image/jpeg",
                "image/png",
                "image/webp"
            ];

            if (
                !allowedTypes.includes(
                    file.type
                )
            ) {

                alert(
                    "Only JPG, PNG and WEBP images are allowed."
                );

                this.value = "";

                return;

            }

            if (
                file.size >
                5 * 1024 * 1024
            ) {

                alert(
                    "Image size must be less than 5 MB."
                );

                this.value = "";

            }

        }
    );

}

</script>

<script src="js/users.js?v=<?php echo time(); ?>"></script>

<footer class="footer">

    <div class="footer-content">

        <div class="footer-brand">

            <h2>
                Inknest
            </h2>

            <p>
                Your trusted online shopping destination.
            </p>

        </div>

        <div class="footer-links">

            <div class="footer-contact">

                <h3>
                    Contact Us
                </h3>

                <p>
                    <strong>Phone:</strong>
                    +977-9800000000
                </p>

                <p>
                    <strong>Email:</strong>
                    support@inknest.com
                </p>

                <p>
                    <strong>Address:</strong>
                    Kathmandu, Nepal
                </p>

            </div>

        </div>

    </div>

    <div class="footer-bottom">

        <p>
            &copy;
            <?php echo date("Y"); ?>
            Inknest.
            All rights reserved.
        </p>

    </div>

</footer>

</body>
</html>