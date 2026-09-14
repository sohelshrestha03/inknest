<?php 
 
session_start(); 
date_default_timezone_set("Asia/Kathmandu"); 
require_once "config/database.php"; 
 
$otpStep = false; 
$errorMessage = ""; 
 
$pendingRegistration = $_SESSION["pending_registration"] ?? []; 
 
$formFirst = $pendingRegistration["fname"] ?? ""; 
$formLast = $pendingRegistration["lname"] ?? ""; 
$formUsername = $pendingRegistration["uname"] ?? ""; 
$formEmail = $pendingRegistration["email"] ?? ""; 
$formPhone = $pendingRegistration["contact"] ?? ""; 
$formDob = $pendingRegistration["dob"] ?? ""; 
 
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
 
if ( 
    $_SERVER["REQUEST_METHOD"] === "POST" && 
    isset($_POST["final_register"]) 
) { 
 
    if ( 
        !isset($_SESSION["register_otp_verified"]) || 
        $_SESSION["register_otp_verified"] !== true 
    ) { 
 
        redirectWithAlert( 
            "Please verify your email with OTP first.", 
            "register.php" 
        ); 
    } 
 
    $pendingRegistration = 
        $_SESSION["pending_registration"] ?? null; 
 
    if (!$pendingRegistration) { 
 
        redirectWithAlert( 
            "Registration session expired. Please register again.", 
            "register.php" 
        ); 
    } 
 
    $first = trim( 
        $pendingRegistration["fname"] ?? "" 
    ); 
 
    $last = trim( 
        $pendingRegistration["lname"] ?? "" 
    ); 
 
    $userName = trim( 
        $pendingRegistration["uname"] ?? "" 
    ); 
 
    $email = strtolower( 
        trim( 
            $pendingRegistration["email"] ?? "" 
        ) 
    ); 
 
    $phone = trim( 
        $pendingRegistration["contact"] ?? "" 
    ); 
 
    $dob = trim( 
        $pendingRegistration["dob"] ?? "" 
    ); 
 
    $passwordHash = 
        $pendingRegistration["password_hash"] ?? ""; 
 
    $verifiedEmail = strtolower( 
        trim( 
            $_SESSION["register_email"] ?? "" 
        ) 
    ); 
 
    if ( 
        $email === "" || 
        $email !== $verifiedEmail 
    ) { 
 
        redirectWithAlert( 
            "Email verification does not match.", 
            "register.php" 
        ); 
    } 
 
    if ( 
        $first === "" || 
        $last === "" || 
        $userName === "" || 
        $email === "" || 
        $phone === "" || 
        $dob === "" || 
        $passwordHash === "" 
    ) { 
 
        redirectWithAlert( 
            "Registration data is incomplete. Please register again.", 
            "register.php" 
        ); 
    } 
 
    $dobDate = DateTime::createFromFormat( 
        "!Y-m-d", 
        $dob 
    ); 
 
    $dobErrors = DateTime::getLastErrors(); 
 
    if ($dobErrors === false) { 
 
        $dobErrors = [ 
            "warning_count" => 0, 
            "error_count" => 0 
        ]; 
    } 
 
    $today = new DateTime("today"); 
 
    if ( 
        !$dobDate || 
        $dobErrors["warning_count"] > 0 || 
        $dobErrors["error_count"] > 0 || 
        $dobDate->format("Y-m-d") !== $dob 
    ) { 
 
        redirectWithAlert( 
            "Please enter a valid date of birth.", 
            "register.php" 
        ); 
    } 
 
    if ($dobDate > $today) { 
 
        redirectWithAlert( 
            "Date of birth cannot be in the future.", 
            "register.php" 
        ); 
    } 
 
    $minimumBirthDate = 
        (clone $today)->modify("-18 years"); 
 
    if ($dobDate > $minimumBirthDate) { 
 
        redirectWithAlert( 
            "You must be at least 18 years old to register.", 
            "register.php" 
        ); 
    } 
 
    $check = mysqli_prepare( 
        $conn, 
        " 
        SELECT 
            id, 
            user_name, 
            email, 
            phone_no 
        FROM users 
        WHERE user_name = ? 
           OR email = ? 
           OR phone_no = ? 
        LIMIT 1 
        " 
    ); 
 
    if (!$check) { 
 
        error_log( 
            "Registration duplicate check error: " . 
            mysqli_error($conn) 
        ); 
 
        redirectWithAlert( 
            "Database error. Please try again.", 
            "register.php" 
        ); 
    } 
 
    mysqli_stmt_bind_param( 
        $check, 
        "sss", 
        $userName, 
        $email, 
        $phone 
    ); 
 
    if (!mysqli_stmt_execute($check)) { 
 
        $dbError = 
            mysqli_stmt_error($check); 
 
        mysqli_stmt_close($check); 
 
        error_log( 
            "Registration duplicate check error: " . 
            $dbError 
        ); 
 
        redirectWithAlert( 
            "Database error. Please try again.", 
            "register.php" 
        ); 
    } 
 
    mysqli_stmt_bind_result( 
        $check, 
        $existingId, 
        $existingUsername, 
        $existingEmail, 
        $existingPhone 
    ); 
 
    if (mysqli_stmt_fetch($check)) { 
 
        mysqli_stmt_close($check); 
 
        if ( 
            strtolower($existingEmail) === $email 
        ) { 
 
            unset( 
                $_SESSION["pending_registration"], 
                $_SESSION["register_email"], 
                $_SESSION["register_otp_verified"], 
                $_SESSION["otp_verified"], 
                $_SESSION["otp_verified_email"], 
                $_SESSION["otp_verified_purpose"], 
                $_SESSION["otp_verified_user_id"] 
            ); 
 
            redirectWithAlert( 
                "This email address is already registered. Please use another email.", 
                "register.php" 
            ); 
        } 
 
        if ( 
            $existingUsername === $userName 
        ) { 
 
            redirectWithAlert( 
                "Username is already used. Please choose another username.", 
                "register.php" 
            ); 
        } 
 
        if ( 
            $existingPhone === $phone 
        ) { 
 
            redirectWithAlert( 
                "Phone number is already used. Please choose another phone number.", 
                "register.php" 
            ); 
        } 
 
    } else { 
 
        mysqli_stmt_close($check); 
    } 
 
    $insert = mysqli_prepare( 
        $conn, 
        " 
        INSERT INTO users 
        ( 
            first_name, 
            last_name, 
            user_name, 
            email, 
            phone_no, 
            date_of_birth, 
            new_Password, 
            confirm_Password 
        ) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?) 
        " 
    ); 
 
    if (!$insert) { 
 
        error_log( 
            "Registration insert prepare error: " . 
            mysqli_error($conn) 
        ); 
 
        redirectWithAlert( 
            "Registration failed. Please try again.", 
            "register.php" 
        ); 
    } 
 
    mysqli_stmt_bind_param( 
        $insert, 
        "ssssssss", 
        $first, 
        $last, 
        $userName, 
        $email, 
        $phone, 
        $dob, 
        $passwordHash, 
        $passwordHash 
    ); 
 
    if ( 
        mysqli_stmt_execute($insert) 
    ) { 
 
        mysqli_stmt_close($insert); 
 
        $deleteOtp = mysqli_prepare( 
            $conn, 
            " 
            DELETE FROM password_otps 
            WHERE email = ? 
              AND purpose = 'register' 
            " 
        ); 
 
        if ($deleteOtp) { 
 
            mysqli_stmt_bind_param( 
                $deleteOtp, 
                "s", 
                $email 
            ); 
 
            mysqli_stmt_execute( 
                $deleteOtp 
            ); 
 
            mysqli_stmt_close( 
                $deleteOtp 
            ); 
        } 
 
        unset( 
            $_SESSION["pending_registration"], 
            $_SESSION["register_otp_verified"], 
            $_SESSION["register_email"], 
            $_SESSION["otp_verified"], 
            $_SESSION["otp_verified_email"], 
            $_SESSION["otp_verified_purpose"], 
            $_SESSION["otp_verified_user_id"], 
            $_SESSION["otp_email"], 
            $_SESSION["otp_purpose"] 
        ); 
 
        redirectWithAlert( 
            "You are registered successfully.", 
            "login.php" 
        ); 
    } 
 
    $dbError = 
        mysqli_stmt_error($insert); 
 
    $dbErrorNo = 
        mysqli_stmt_errno($insert); 
 
    mysqli_stmt_close($insert); 
 
    error_log( 
        "Registration insert error [" . 
        $dbErrorNo . 
        "]: " . 
        $dbError 
    ); 
 
    if ( 
        $dbErrorNo === 1062 
    ) { 
 
        redirectWithAlert( 
            "Username, email, or phone number is already registered.", 
            "register.php" 
        ); 
    } 
 
    redirectWithAlert( 
        "Registration failed. Please try again.", 
        "register.php" 
    ); 
} 
 
if ( 
    $_SERVER["REQUEST_METHOD"] === "POST" && 
    isset($_POST["send_otp"]) 
) { 
 
    $first = trim( 
        $_POST["fname"] ?? "" 
    ); 
 
    $last = trim( 
        $_POST["lname"] ?? "" 
    ); 
 
    $userName = trim( 
        $_POST["uname"] ?? "" 
    ); 
 
    $email = strtolower( 
        trim( 
            $_POST["email"] ?? "" 
        ) 
    ); 
 
    $phone = trim( 
        $_POST["contact"] ?? "" 
    ); 
 
    $dob = trim( 
        $_POST["dob"] ?? "" 
    ); 
 
    $newPassword = 
        $_POST["npassword"] ?? ""; 
 
    $confirmPassword = 
        $_POST["cpassword"] ?? ""; 
 
    $formFirst = $first; 
    $formLast = $last; 
    $formUsername = $userName; 
    $formEmail = $email; 
    $formPhone = $phone; 
    $formDob = $dob; 
 
    if ( 
        $first === "" || 
        $last === "" || 
        $userName === "" || 
        $email === "" || 
        $phone === "" || 
        $dob === "" || 
        $newPassword === "" || 
        $confirmPassword === "" 
    ) { 
 
        $errorMessage = 
            "Please fill in all fields."; 
 
    } elseif ( 
        !preg_match( 
            "/^[A-Za-z]+$/", 
            $first 
        ) 
    ) { 
 
        $errorMessage = 
            "First name can contain letters only."; 
 
    } elseif ( 
        !preg_match( 
            "/^[A-Za-z]+$/", 
            $last 
        ) 
    ) { 
 
        $errorMessage = 
            "Last name can contain letters only."; 
 
    } elseif ( 
        !filter_var( 
            $email, 
            FILTER_VALIDATE_EMAIL 
        ) 
    ) { 
 
        $errorMessage = 
            "Please enter a valid email address."; 
 
    } elseif ( 
        !preg_match( 
            "/^[0-9]{10}$/", 
            $phone 
        ) 
    ) { 
 
        $errorMessage = 
            "Please enter a valid 10-digit phone number."; 
 
    } elseif ( 
        strlen($newPassword) < 8 
    ) { 
 
        $errorMessage = 
            "Password must be at least 8 characters."; 
 
    } elseif ( 
        $newPassword !== $confirmPassword 
    ) { 
 
        $errorMessage = 
            "Passwords do not match."; 
 
    } else { 
 
        $dobDate = DateTime::createFromFormat( 
            "!Y-m-d", 
            $dob 
        ); 
 
        $dobErrors = DateTime::getLastErrors(); 
 
        if ($dobErrors === false) { 
 
            $dobErrors = [ 
                "warning_count" => 0, 
                "error_count" => 0 
            ]; 
        } 
 
        $today = new DateTime("today"); 
 
        if ( 
            !$dobDate || 
            $dobErrors["warning_count"] > 0 || 
            $dobErrors["error_count"] > 0 || 
            $dobDate->format("Y-m-d") !== $dob 
        ) { 
 
            $errorMessage = 
                "Please enter a valid date of birth."; 
 
        } elseif ( 
            $dobDate > $today 
        ) { 
 
            $errorMessage = 
                "Date of birth cannot be in the future."; 
 
        } else { 
 
            $minimumBirthDate = 
                (clone $today)->modify("-18 years"); 
 
            if ( 
                $dobDate > $minimumBirthDate 
            ) { 
 
                $errorMessage = 
                    "You must be at least 18 years old to register."; 
 
            } 
        } 
    } 
 
    if ( 
        $errorMessage === "" 
    ) { 
 
        $check = mysqli_prepare( 
            $conn, 
            " 
            SELECT 
                user_name, 
                email, 
                phone_no 
            FROM users 
            WHERE user_name = ? 
               OR email = ? 
               OR phone_no = ? 
            LIMIT 1 
            " 
        ); 
 
        if (!$check) { 
 
            $errorMessage = 
                "Database error. Please try again."; 
 
        } else { 
 
            mysqli_stmt_bind_param( 
                $check, 
                "sss", 
                $userName, 
                $email, 
                $phone 
            ); 
 
            if ( 
                !mysqli_stmt_execute($check) 
            ) { 
 
                $dbError = 
                    mysqli_stmt_error($check); 
 
                mysqli_stmt_close($check); 
 
                error_log( 
                    "Registration check error: " . 
                    $dbError 
                ); 
 
                $errorMessage = 
                    "Database error. Please try again."; 
 
            } else { 
 
                mysqli_stmt_bind_result( 
                    $check, 
                    $existingUsername, 
                    $existingEmail, 
                    $existingPhone 
                ); 
 
                if ( 
                    mysqli_stmt_fetch($check) 
                ) { 
 
                    if ( 
                        strtolower( 
                            $existingEmail 
                        ) === $email 
                    ) { 
 
                        $errorMessage = 
                            "This email address is already registered. Please use another email."; 
 
                    } elseif ( 
                        $existingUsername === 
                        $userName 
                    ) { 
 
                        $errorMessage = 
                            "Username is already used. Please choose another username."; 
 
                    } elseif ( 
                        $existingPhone === 
                        $phone 
                    ) { 
 
                        $errorMessage = 
                            "Phone number is already used. Please choose another phone number."; 
                    } 
                } 
 
                mysqli_stmt_close( 
                    $check 
                ); 
            } 
        } 
    } 
 
    if ( 
        $errorMessage === "" 
    ) { 
 
        $_SESSION["pending_registration"] = [ 
 
            "fname" => 
                $first, 
 
            "lname" => 
                $last, 
 
            "uname" => 
                $userName, 
 
            "email" => 
                $email, 
 
            "contact" => 
                $phone, 
 
            "dob" => 
                $dob, 
 
            "password_hash" => 
                password_hash( 
                    $newPassword, 
                    PASSWORD_DEFAULT 
                ) 
        ]; 
 
        $_SESSION["register_email"] = 
            $email; 
 
        unset( 
            $_SESSION["register_otp_verified"], 
            $_SESSION["otp_verified"], 
            $_SESSION["otp_verified_email"], 
            $_SESSION["otp_verified_purpose"], 
            $_SESSION["otp_verified_user_id"] 
        ); 
 
        $otpStep = 
            true; 
 
        $formFirst = 
            $first; 
 
        $formLast = 
            $last; 
 
        $formUsername = 
            $userName; 
 
        $formEmail = 
            $email; 
 
        $formPhone = 
            $phone; 
 
        $formDob = 
            $dob; 
    } 
} 
 
?> 
 
<!DOCTYPE html> 
<html lang="en"> 
 
<head> 
 
    <meta charset="UTF-8"> 
 
    <meta 
        name="viewport" 
        content="width=device-width, initial-scale=1.0" 
    > 
 
    <title> 
        Register | Inknest 
    </title> 
 
    <link 
        rel="stylesheet" 
        href="css/register.css?v=<?php echo time(); ?>" 
    > 
 
    <script 
        src="js/register.js?v=<?php echo time(); ?>" 
        defer 
    ></script> 
 
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
 
<div class="register-container"> 
 
    <div class="register-card"> 
 
        <h2> 
            Create Account 
        </h2> 
 
        <p class="subtitle"> 
            Register your account 
        </p> 
 
        <?php if ($errorMessage !== ""): ?> 
 
            <div 
                style=" 
                    margin-bottom:15px; 
                    padding:10px; 
                    color:#c62828; 
                    background:#ffebee; 
                    border-radius:6px; 
                    font-size:14px; 
                " 
            > 
 
                <?php 
                echo htmlspecialchars( 
                    $errorMessage, 
                    ENT_QUOTES, 
                    "UTF-8" 
                ); 
                ?> 
 
            </div> 
 
        <?php endif; ?> 
 
        <form 
            id="registerForm" 
            action="register.php" 
            method="post" 
        > 
 
            <div class="data"> 
 
                <label for="fname"> 
                    First Name 
                </label> 
 
                <input 
                    type="text" 
                    id="fname" 
                    name="fname" 
                    placeholder="Enter your first name" 
                    autocomplete="off" 
                    value="<?php 
                        echo htmlspecialchars( 
                            $formFirst, 
                            ENT_QUOTES, 
                            "UTF-8" 
                        ); 
                    ?>" 
                    required 
                > 
 
            </div> 
 
            <div class="data"> 
 
                <label for="lname"> 
                    Last Name 
                </label> 
 
                <input 
                    type="text" 
                    id="lname" 
                    name="lname" 
                    placeholder="Enter your last name" 
                    autocomplete="off" 
                    value="<?php 
                        echo htmlspecialchars( 
                            $formLast, 
                            ENT_QUOTES, 
                            "UTF-8" 
                        ); 
                    ?>" 
                    required 
                > 
 
            </div> 
 
            <div class="data"> 
 
                <label for="uname"> 
                    Username 
                </label> 
 
                <input 
                    type="text" 
                    id="uname" 
                    name="uname" 
                    placeholder="Enter your username" 
                    autocomplete="off" 
                    value="<?php 
                        echo htmlspecialchars( 
                            $formUsername, 
                            ENT_QUOTES, 
                            "UTF-8" 
                        ); 
                    ?>" 
                    required 
                > 
 
            </div> 
 
            <div class="data"> 
 
                <label for="email"> 
                    Email 
                </label> 
 
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    placeholder="Enter your email" 
                    autocomplete="off" 
                    value="<?php 
                        echo htmlspecialchars( 
                            $formEmail, 
                            ENT_QUOTES, 
                            "UTF-8" 
                        ); 
                    ?>" 
                    required 
                > 
 
            </div> 
 
            <div class="data"> 
 
                <label for="contact"> 
                    Phone Number 
                </label> 
 
                <input 
                    type="text" 
                    id="contact" 
                    name="contact" 
                    placeholder="Enter phone number" 
                    maxlength="10" 
                    autocomplete="off" 
                    value="<?php 
                        echo htmlspecialchars( 
                            $formPhone, 
                            ENT_QUOTES, 
                            "UTF-8" 
                        ); 
                    ?>" 
                    required 
                > 
 
            </div> 
 
            <div class="data"> 
 
                <label for="dob"> 
                    Date of Birth 
                </label> 
 
                <input 
                    type="date" 
                    id="dob" 
                    name="dob" 
                    value="<?php 
                        echo htmlspecialchars( 
                            $formDob, 
                            ENT_QUOTES, 
                            "UTF-8" 
                        ); 
                    ?>" 
                    required 
                > 
 
            </div> 
 
            <div class="data"> 
 
                <label for="npassword"> 
                    New Password 
                </label> 
 
                <input 
                    type="password" 
                    id="npassword" 
                    name="npassword" 
                    placeholder="Create a password" 
                    minlength="8" 
                    required 
                > 
 
            </div> 
 
            <div class="data"> 
 
                <label for="cpassword"> 
                    Confirm Password 
                </label> 
 
                <input 
                    type="password" 
                    id="cpassword" 
                    name="cpassword" 
                    placeholder="Confirm your password" 
                    minlength="8" 
                    required 
                > 
 
            </div> 
 
            <?php if (!$otpStep): ?> 
 
                <div class="buttons"> 
 
                    <button 
                        type="submit" 
                        name="send_otp" 
                        value="1" 
                        id="sendOtpButton" 
                    > 
                        Register 
                    </button> 
 
                    <button 
                        type="reset" 
                        class="cancel" 
                        id="cancelButton" 
                    > 
                        Cancel 
                    </button> 
 
                </div> 
 
            <?php endif; ?> 
 
            <div 
                id="otpSection" 
                data-email="<?php 
                    echo htmlspecialchars( 
                        $_SESSION["register_email"] ?? 
                        $formEmail, 
                        ENT_QUOTES, 
                        "UTF-8" 
                    ); 
                ?>" 
                data-purpose="register" 
                style=" 
                    <?php 
                    echo $otpStep 
                        ? "display:block;" 
                        : "display:none;"; 
                    ?> 
 
                    margin-top:20px; 
                    padding-top:20px; 
                    border-top:1px solid #eeeeee; 
                " 
            > 
 
                <div class="data"> 
 
                    <label for="otp"> 
                        Email Verification Code 
                    </label> 
 
                    <input 
                        type="text" 
                        id="otp" 
                        maxlength="6" 
                        inputmode="numeric" 
                        autocomplete="one-time-code" 
                        placeholder="Enter 6-digit OTP" 
                    > 
 
                </div> 
 
                <div class="buttons"> 
 
                    <button 
                        type="button" 
                        id="verifyOtpButton" 
                    > 
                        Verify OTP 
                    </button> 
 
                </div> 
 
                <p 
                    id="otpMessage" 
                    style=" 
                        margin-top:10px; 
                        font-size:13px; 
                    " 
                ></p> 
 
                <button 
                    type="button" 
                    id="resendOtpButton" 
                    style=" 
                        margin-top:10px; 
                        background:none; 
                        border:none; 
                        cursor:pointer; 
                        text-decoration:underline; 
                    " 
                > 
                    Resend OTP 
                </button> 
 
            </div> 
 
            <p class="login-link"> 
 
                Already have an account? 
 
                <a href="login.php"> 
                    Login 
                </a> 
 
            </p> 
 
        </form> 
 
    </div> 
 
</div> 
 
<script> 
 
document.addEventListener( 
    "DOMContentLoaded", 
    function () { 
 
        const otpSection = 
            document.getElementById( 
                "otpSection" 
            ); 
 
        const otpInput = 
            document.getElementById( 
                "otp" 
            ); 
 
        const verifyOtpButton = 
            document.getElementById( 
                "verifyOtpButton" 
            ); 
 
        const resendOtpButton = 
            document.getElementById( 
                "resendOtpButton" 
            ); 
 
        const otpMessage = 
            document.getElementById( 
                "otpMessage" 
            ); 
 
        if ( 
            !otpSection || 
            !otpInput || 
            !verifyOtpButton || 
            !resendOtpButton 
        ) { 
 
            return; 
        } 
 
        const email = 
            ( 
                otpSection.dataset.email || 
                "" 
            ).trim(); 
 
        const purpose = 
            "register"; 
 
        function showMessage( 
            message, 
            isError = false 
        ) { 
 
            if (!otpMessage) { 
                return; 
            } 
 
            otpMessage.textContent = 
                message; 
 
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
 
            showMessage( 
                "Sending OTP..." 
            ); 
 
            resendOtpButton.disabled = 
                true; 
 
            const formData = 
                new FormData(); 
 
            formData.append( 
                "email", 
                email 
            ); 
 
            formData.append( 
                "purpose", 
                purpose 
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
 
                otpSection.style.display = 
                    "block"; 
 
                showMessage( 
                    "OTP sent to your email." 
                ); 
 
                otpInput.focus(); 
 
                setTimeout( 
                    function () { 
 
                        resendOtpButton.disabled = 
                            false; 
 
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
 
                resendOtpButton.disabled = 
                    false; 
            } 
        } 
 
        verifyOtpButton.addEventListener( 
            "click", 
            async function () { 
 
                const otp = 
                    otpInput.value.trim(); 
 
                if ( 
                    !/^[0-9]{6}$/.test( 
                        otp 
                    ) 
                ) { 
 
                    showMessage( 
                        "Please enter a valid 6-digit OTP.", 
                        true 
                    ); 
 
                    return; 
                } 
 
                verifyOtpButton.disabled = 
                    true; 
 
                showMessage( 
                    "Verifying OTP..." 
                ); 
 
                const formData = 
                    new FormData(); 
 
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
 
                    showMessage( 
                        "Email verified. Creating your account..." 
                    ); 
 
                    otpInput.disabled = 
                        true; 
 
                    verifyOtpButton.disabled = 
                        true; 
 
                    resendOtpButton.disabled = 
                        true; 
 
                    const registerForm = 
                        document.getElementById( 
                            "registerForm" 
                        ); 
 
                    registerForm.setAttribute( 
                        "novalidate", 
                        "novalidate" 
                    ); 
 
                    const finalInput = 
                        document.createElement( 
                            "input" 
                        ); 
 
                    finalInput.type = 
                        "hidden"; 
 
                    finalInput.name = 
                        "final_register"; 
 
                    finalInput.value = 
                        "1"; 
 
                    registerForm.appendChild( 
                        finalInput 
                    ); 
 
                    registerForm.submit(); 
 
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
 
                    verifyOtpButton.disabled = 
                        false; 
                } 
 
            } 
        ); 
 
        resendOtpButton.addEventListener( 
            "click", 
            function () { 
 
                if ( 
                    !resendOtpButton.disabled 
                ) { 
 
                    sendOtp(); 
                } 
            } 
        ); 
 
        otpInput.addEventListener( 
            "input", 
            function () { 
 
                this.value = 
                    this.value 
                        .replace( 
                            /\D/g, 
                            "" 
                        ) 
                        .slice( 
                            0, 
                            6 
                        ); 
            } 
        ); 
 
        otpInput.addEventListener( 
            "keydown", 
            function (event) { 
 
                if ( 
                    event.key === "Enter" 
                ) { 
 
                    event.preventDefault(); 
 
                    verifyOtpButton.click(); 
                } 
 
            } 
        ); 
 
        <?php if ($otpStep): ?> 
 
        sendOtp(); 
 
        <?php endif; ?> 
 
    } 
); 
 
</script> 

</body> 
</html>