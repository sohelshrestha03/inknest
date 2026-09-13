<?php
session_start();
include "../config/database.php";
header("Content-Type: application/json; charset=UTF-8");

$email=strtolower(trim($_POST["email"] ?? ""));
$otp = trim($_POST["otp"] ?? "");
$purpose = trim($_POST["purpose"] ?? "");
$allowedPurposes = [
    "register",
    "password_reset",
    "change_password"
];

if (!filter_var($email,FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid email address."
    ]);
    exit();
}

if (!preg_match("/^[0-9]{6}$/",$otp)) {
    echo json_encode([
        "success" => false,
        "message" => "OTP must be 6 digits."
    ]);
    exit();
}

if (!in_array($purpose,$allowedPurposes,true)) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid OTP purpose."
    ]);
    exit();
}
$stmt = mysqli_prepare(
    $conn,
    "
    SELECT
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
    LIMIT 1
    "
);

if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Database error."
    ]);
    exit();
}
mysqli_stmt_bind_param(
    $stmt,
    "ss",
    $email,
    $purpose
);

mysqli_stmt_execute(
    $stmt
);
$result = mysqli_stmt_get_result(
    $stmt
);
$otpRecord = mysqli_fetch_assoc(
    $result
);
mysqli_stmt_close(
    $stmt
);

if (!$otpRecord) {
    echo json_encode([
        "success" => false,
        "message" => "OTP not found or expired."
    ]);
    exit();
}

if ((int) $otpRecord["verified"] === 1) {
    echo json_encode([
        "success" => false,
        "message" => "This OTP has already been used."
    ]);
    exit();
}
$attempts=(int) $otpRecord["attempts"];
if ($attempts >= 5) {
    echo json_encode([
        "success" => false,
        "message" =>"Too many incorrect attempts. Please request a new OTP."
    ]);
    exit();
}
$expiresTimestamp =strtotime(
        $otpRecord["expires_at"]
    );

if ($expiresTimestamp === false || time() > $expiresTimestamp) {
    echo json_encode([
        "success" => false,
        "message" =>"OTP has expired. Please request a new OTP."
    ]);
    exit();
}

if (!password_verify(
        $otp,
        $otpRecord["otp_hash"])) {
    $attemptStmt = mysqli_prepare(
        $conn,
        "
        UPDATE password_otps
        SET attempts = attempts + 1
        WHERE id = ?
        "
    );

    if ($attemptStmt) {
        $otpId =(int) $otpRecord["id"];
        mysqli_stmt_bind_param(
            $attemptStmt,
            "i",
            $otpId
        );

        mysqli_stmt_execute(
            $attemptStmt
        );

        mysqli_stmt_close(
            $attemptStmt
        );
    }
    $remaining =4 - $attempts;
    if ($remaining < 0) {
        $remaining = 0;
    }
    echo json_encode([
        "success" => false,
        "message" =>
            "Incorrect OTP. " .
            $remaining .
            " attempts remaining."
    ]);
    exit();
}

$otpId = (int) $otpRecord["id"];
$verifyStmt = mysqli_prepare(
    $conn,
    "
    UPDATE password_otps
    SET verified = 1
    WHERE id = ?
    "
);

if (!$verifyStmt) {
    echo json_encode([
        "success" => false,
        "message" =>
            "Failed to verify OTP."
    ]);
    exit();
}

mysqli_stmt_bind_param(
    $verifyStmt,
    "i",
    $otpId
);

if (!mysqli_stmt_execute($verifyStmt)) {

    mysqli_stmt_close(
        $verifyStmt
    );
    echo json_encode([
        "success" => false,
        "message" =>
            "Failed to verify OTP."
    ]);

    exit();
}

mysqli_stmt_close(
    $verifyStmt
);

$_SESSION["otp_verified"] =true;
$_SESSION["otp_verified_email"] =$email;
$_SESSION["otp_verified_purpose"] =$purpose;

$_SESSION["otp_verified_user_id"] =
    $otpRecord["user_id"] !== null
        ? (int) $otpRecord["user_id"]
        : null;

if ($purpose === "register") {
    $_SESSION["register_otp_verified"] =true;
    $_SESSION["register_email"] =$email;
}


if ($purpose === "password_reset") {
    $_SESSION["reset_user_id"]=(int) $otpRecord["user_id"];
    $_SESSION["reset_email"] =$email;
}

if ($purpose === "change_password") {
    $_SESSION["change_password_verified"] =true;
    $_SESSION["change_password_user_id"] = (int) $otpRecord["user_id"];
    $_SESSION["change_password_email"] = $email;
}


echo json_encode([
    "success" => true,
    "message" => "OTP verified successfully.",
    "purpose" => $purpose
]);
exit();
?>