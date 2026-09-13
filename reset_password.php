<?php
session_start();
require_once "config/database.php";

if (!isset($_SESSION["reset_user_id"]) || !isset($_SESSION["reset_email"])) {
    header("Location: forgot_password.php");
    exit();
}

$userId = (int) $_SESSION["reset_user_id"];
$email = strtolower(
    trim(
        $_SESSION["reset_email"]
    )
);
$error = "";
$otpStep = false;
$otpVerified =isset($_SESSION["otp_verified"]) &&
    $_SESSION["otp_verified"] === true &&
    (
        $_SESSION["otp_verified_purpose"] ?? ""
    ) === "password_reset" &&
    strtolower(
        trim(
            $_SESSION["otp_verified_email"] ?? ""
        )
    ) === $email;

function redirectWithAlert(
    string $message,
    string $location
): never {
    echo "
        <script>
            alert(" . json_encode($message) . ");
            window.location=" . json_encode($location) . ";
        </script>
    ";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["send_otp"])) {
    $newPassword=$_POST["new_password"] ?? "";
    $confirmPassword=$_POST["confirm_password"] ?? "";
    if ($newPassword === "" || $confirmPassword === "") {
        $error="Please fill in both password fields.";
    } elseif (strlen($newPassword) < 8) {
        $error="Password must be at least 8 characters.";
    } elseif ($newPassword !== $confirmPassword) {
        $error="Passwords do not match.";
    } else {
        $_SESSION["pending_reset_password_hash"] =
            password_hash(
                $newPassword,
                PASSWORD_DEFAULT
            );
        unset(
            $_SESSION["otp_verified"],
            $_SESSION["otp_verified_email"],
            $_SESSION["otp_verified_purpose"],
            $_SESSION["otp_verified_user_id"]
        );
        $otpStep = true;
    }
}

if (isset($_GET["otp"]) && $_GET["otp"] === "1") {
    $otpStep = true;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["complete_reset"])) {
    $otpVerified =isset($_SESSION["otp_verified"]) &&
        $_SESSION["otp_verified"] === true &&
        (
            $_SESSION["otp_verified_purpose"] ?? ""
        ) === "password_reset" &&
        strtolower(
            trim(
                $_SESSION["otp_verified_email"] ?? ""
            )
        ) === $email;

    if (!$otpVerified) {
        redirectWithAlert(
            "Please verify the OTP first.",
            "reset_password.php"
        );
    }
    $passwordHash =$_SESSION["pending_reset_password_hash"] ?? "";
    if ($passwordHash === "") {
        redirectWithAlert(
            "Password reset session expired. Please try again.",
            "forgot_password.php"
        );
    }
    $stmt = mysqli_prepare(
        $conn,
        "
        UPDATE users
        SET
            new_Password = ?,
            confirm_Password = ?
        WHERE id = ?
        "
    );

    if (!$stmt) {
        redirectWithAlert(
            "Something went wrong. Please try again.",
            "reset_password.php"
        );
    }
    mysqli_stmt_bind_param(
        $stmt,
        "ssi",
        $passwordHash,
        $passwordHash,
        $userId
    );

    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        redirectWithAlert(
            "Unable to change password. Please try again.",
            "reset_password.php"
        );
    }
    mysqli_stmt_close($stmt);
    unset(
        $_SESSION["reset_user_id"],
        $_SESSION["reset_email"],
        $_SESSION["pending_reset_password_hash"],
        $_SESSION["otp_verified"],
        $_SESSION["otp_verified_email"],
        $_SESSION["otp_verified_purpose"],
        $_SESSION["otp_verified_user_id"],
        $_SESSION["otp_email"],
        $_SESSION["otp_purpose"]
    );
    redirectWithAlert(
        "Password changed successfully.",
        "login.php"
    );
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        Reset Password | Inknest
    </title>
    <link rel="stylesheet" href="css/reset_password.css?v=<?php echo time(); ?>">
</head>

<body>
<nav class="navigation">
    <h1>
        Inknest
    </h1>
    <a href="login.php">
        Back
    </a>
</nav>

<div class="reset-container">
    <div class="reset-card">
        <h2>Reset Password</h2>
        <p class="subtitle">Create your new password</p>
        <?php if ($error !== ""): ?>
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
        <form id="resetForm" action="reset_password.php" method="post">
            <div class="data">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" placeholder="Enter new password" minlength="8" required>
            </div>

            <div class="data">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" minlength="8" required>
            </div>

            <?php if (!$otpStep): ?>
                <button type="submit" name="send_otp" value="1" id="sendOtpButton">Send OTP</button>
            <?php endif; ?>
        </form>

        <?php if ($otpStep && !$otpVerified): ?>
            <div
                id="otpSection"
                style="
                    margin-top:20px;
                    padding-top:20px;
                    border-top:1px solid #eeeeee;
                ">
                <div class="data">
                    <label for="otp">Verification Code</label>
                    <input type="text" id="otp" maxlength="6" inputmode="numeric" autocomplete="one-time-code" placeholder="Enter 6-digit OTP">
                </div>
                <button type="button" id="verifyOtpButton">Verify OTP</button>
                <button type="button"
                    id="resendOtpButton"
                    style="
                        margin-top:10px;
                        background:none;
                        border:none;
                        cursor:pointer;
                        text-decoration:underline;
                    ">Resend OTP</button>
                <p id="otpMessage"
                    style="
                        margin-top:10px;
                        font-size:13px;
                    "></p>
            </div>
        <?php endif; ?>
    </div>
</div>
<script>
document.addEventListener("DOMContentLoaded",function () {
        const sendOtpButton =document.getElementById("sendOtpButton");
        const otpInput =document.getElementById("otp");
        const verifyOtpButton =document.getElementById("verifyOtpButton");
        const resendOtpButton =document.getElementById("resendOtpButton");
        const otpMessage =document.getElementById("otpMessage");
        const otpSection =document.getElementById("otpSection");
        const resetForm =document.getElementById("resetForm");
        const email =<?php echo json_encode($email); ?>;
        const purpose ="password_reset";
        function showMessage(
            message,
            isError = false
        ) {
            if (!otpMessage) {
                return;
            }
            otpMessage.textContent=message;
            otpMessage.style.color =
                isError
                    ? "#c62828"
                    : "#555555";
        }

        async function sendOtp() {
            if (!email) {
                showMessage(
                    "Email address is missing.",
                    true
                );
                return;
            }

            if (resendOtpButton) {
                resendOtpButton.disabled =true;
            }
            showMessage("Sending OTP...");
            const formData =new FormData();
            formData.append(
                "email",
                email
            );
            formData.append(
                "purpose",
                purpose
            );
            try {
                const response=await fetch(
                        "auth/send_otp.php",
                        {
                            method: "POST",
                            body: formData
                        }
                    );
                const text=await response.text();
                let data;
                try {
                    data=JSON.parse(text);
                } catch (error) {
                    console.error(
                        "Invalid server response:",
                        text
                    );
                    throw new Error(
                        "Server returned an invalid response."
                    );
                }
                if (!data.success) {
                    throw new Error(
                        data.message ||
                        "Failed to send OTP."
                    );
                }
                if (otpSection) {
                    otpSection.style.display ="block";
                }
                showMessage("OTP sent to your email.");
                if (otpInput) {
                    otpInput.focus();
                }
                setTimeout(
                    function () {
                        if (resendOtpButton) {
                            resendOtpButton.disabled =false;
                        }
                    },
                    60000
                );
            } catch (error) {
                console.error(
                    "Send OTP error:",
                    error
                );
                showMessage(
                    error.message ||
                    "Unable to send OTP.",
                    true
                );
                if (resendOtpButton) {
                    resendOtpButton.disabled =false;
                }
            }
        }

        if (verifyOtpButton) {
            verifyOtpButton.addEventListener(
                "click",
                async function () {
                    const otp =otpInput
                            ? otpInput.value.trim()
                            : "";
                    if (!/^[0-9]{6}$/.test(otp)) {
                        showMessage(
                            "Please enter a valid 6-digit OTP.",
                            true
                        );
                        return;
                    }
                    verifyOtpButton.disabled =true;
                    showMessage("Verifying OTP...");
                    const formData=new FormData();
                    formData.append(
                        "email",
                        email
                    );
                    formData.append(
                        "otp",
                        otp
                    );
                    formData.append(
                        "purpose",
                        purpose
                    );

                    try {
                        const response = await fetch(
                                "auth/verify_otp.php",
                                {
                                    method: "POST",
                                    body: formData
                                }
                            );
                        const text=await response.text();
                        let data;
                        try {
                            data =JSON.parse(text);
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
                        showMessage("OTP verified. Changing password...");
                        if (otpInput) {
                            otpInput.disabled =true;
                        }
                        verifyOtpButton.disabled=true;
                        if (resendOtpButton) {
                            resendOtpButton.disabled =true;
                        }
                        const completeInput =document.createElement("input");
                        completeInput.type ="hidden";
                        completeInput.name ="complete_reset";
                        completeInput.value ="1";
                        resetForm.appendChild(
                            completeInput
                        );
                        resetForm.setAttribute(
                            "novalidate",
                            "novalidate"
                        );
                        resetForm.submit();
                    } catch (error) {
                        console.error(
                            "Verify OTP error:",
                            error
                        );
                        showMessage(
                            error.message ||
                            "Unable to verify OTP.",
                            true
                        );
                        verifyOtpButton.disabled =false;
                    }
                }
            );
        }

        if (resendOtpButton) {
            resendOtpButton.addEventListener(
                "click",
                function () {
                    if (!resendOtpButton.disabled) {
                        sendOtp();
                    }
                }
            );
        }
        if (otpInput) {
            otpInput.addEventListener(
                "input",
                function () {
                    this.value =this.value.replace(/\D/g,"").slice(0,6);
                }
            );
            otpInput.addEventListener(
                "keydown",
                function (event) {
                    if (event.key === "Enter") {
                        event.preventDefault();
                        verifyOtpButton.click();
                    }
                }
            );
        }
        <?php if ($otpStep && !$otpVerified): ?>
        sendOtp();
        <?php endif; ?>
    }
);
</script>
</body>
</html>