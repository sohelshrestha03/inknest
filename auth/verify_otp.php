<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");
ini_set("display_errors", "0");
ini_set("log_errors", "1");
function jsonResponse(bool $success, string $message, array $extra = [])
{
    echo json_encode(
        array_merge(
            [
                "success" => $success,
                "message" => $message
            ],
            $extra
        ),
        JSON_UNESCAPED_UNICODE
    );
    exit();
}
try {
    require_once __DIR__ . "/../config/database.php";

    if (!isset($conn) || !$conn) {
        jsonResponse(false, "Database connection failed.");
    }
    $email = strtolower(trim($_POST["email"] ?? ""));
    $otp = trim($_POST["otp"] ?? "");
    $purpose = trim($_POST["purpose"] ?? "");
    $allowedPurposes = [
        "register",
        "password_reset",
        "change_password",
        "change_email",
        "change_phone",
        "delete_account"
    ];
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonResponse(false, "Invalid email address.");
    }
    if (!preg_match("/^[0-9]{6}$/", $otp)) {
        jsonResponse(false, "OTP must be 6 digits.");
    }
    if (!in_array($purpose, $allowedPurposes, true)) {
        jsonResponse(false, "Invalid OTP purpose.");
    }
    $stmt = mysqli_prepare(
        $conn,
        "SELECT
            id,
            user_id,
            email,
            otp_hash,
            expires_at,
            attempts,
            verified
         FROM password_otps
         WHERE email = ?
           AND purpose = ?
         ORDER BY id DESC
         LIMIT 1"
    );
    if (!$stmt) {
        jsonResponse(false, "Database error.");
    }
    mysqli_stmt_bind_param(
        $stmt,
        "ss",
        $email,
        $purpose
    );
    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        jsonResponse(false, "Database error.");
    }
    mysqli_stmt_bind_result(
        $stmt,
        $otpId,
        $userId,
        $otpEmail,
        $otpHash,
        $expiresAt,
        $attempts,
        $verified
    );
    if (!mysqli_stmt_fetch($stmt)) {
        mysqli_stmt_close($stmt);
        jsonResponse(false, "OTP not found or expired.");
    }
    mysqli_stmt_close($stmt);
    if ((int)$verified === 1) {
        jsonResponse(false, "This OTP has already been used.");
    }
    $attempts = (int)$attempts;
    if ($attempts >= 5) {
        jsonResponse(
            false,
            "Too many incorrect attempts. Please request a new OTP."
        );
    }
    $expiresTimestamp = strtotime($expiresAt);
    if ($expiresTimestamp === false || time() > $expiresTimestamp) {
        jsonResponse(
            false,
            "OTP has expired. Please request a new OTP."
        );
    }
    if (!password_verify($otp, $otpHash)) {
        $attemptStmt = mysqli_prepare(
            $conn,
            "UPDATE password_otps
             SET attempts = attempts + 1
             WHERE id = ?"
        );
        if ($attemptStmt) {
            mysqli_stmt_bind_param(
                $attemptStmt,
                "i",
                $otpId
            );
            mysqli_stmt_execute($attemptStmt);
            mysqli_stmt_close($attemptStmt);
        }
        $remaining = 4 - $attempts;
        if ($remaining < 0) {
            $remaining = 0;
        }
        jsonResponse(
            false,
            "Incorrect OTP. " .
            $remaining .
            " attempts remaining."
        );
    }
    $verifyStmt = mysqli_prepare(
        $conn,
        "UPDATE password_otps
         SET verified = 1
         WHERE id = ?"
    );
    if (!$verifyStmt) {
        jsonResponse(false, "Failed to verify OTP.");
    }
    mysqli_stmt_bind_param(
        $verifyStmt,
        "i",
        $otpId
    );
    if (!mysqli_stmt_execute($verifyStmt)) {
        mysqli_stmt_close($verifyStmt);
        jsonResponse(false, "Failed to verify OTP.");
    }
    mysqli_stmt_close($verifyStmt);
    $_SESSION["otp_verified"] = true;
    $_SESSION["otp_verified_email"] = $email;
    $_SESSION["otp_verified_purpose"] = $purpose;
    $_SESSION["otp_verified_user_id"] =
        $userId !== null
            ? (int)$userId
            : null;
    if ($purpose === "register") {
        $_SESSION["register_otp_verified"] = true;
        $_SESSION["register_email"] = $email;
    }
    if ($purpose === "password_reset") {
        $_SESSION["reset_user_id"] =
            $userId !== null
                ? (int)$userId
                : 0;
        $_SESSION["reset_email"] = $email;
    }
    if ($purpose === "change_password") {
        $_SESSION["change_password_verified"] = true;
        $_SESSION["change_password_user_id"] =
            $userId !== null
                ? (int)$userId
                : 0;

        $_SESSION["change_password_email"] = $email;
    }
    if ($purpose === "change_email") {
        $_SESSION["change_email_verified"] = true;
        $_SESSION["change_email_user_id"] =
            $userId !== null
                ? (int)$userId
                : 0;

        $_SESSION["change_email_email"] = $email;
    }
    if ($purpose === "change_phone") {
        $_SESSION["change_phone_verified"] = true;
        $_SESSION["change_phone_user_id"] =
            $userId !== null
                ? (int)$userId
                : 0;

        $_SESSION["change_phone_email"] = $email;
    }
    if ($purpose === "delete_account") {
        $_SESSION["delete_account_verified"] = true;
        $_SESSION["delete_account_user_id"] =
            $userId !== null
                ? (int)$userId
                : 0;

        $_SESSION["delete_account_email"] = $email;
    }
    jsonResponse(
        true,
        "OTP verified successfully.",
        [
            "purpose" => $purpose
        ]
    );
} catch (Throwable $e) {
    error_log(
        "VERIFY OTP ERROR: " .
        $e->getMessage() .
        " | File: " .
        $e->getFile() .
        " | Line: " .
        $e->getLine()
    );
    jsonResponse(
        false,
        "Unable to verify OTP. Please try again."
    );
}
?>