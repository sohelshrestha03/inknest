<?php

session_start();

header("Content-Type: application/json; charset=UTF-8");

ini_set("display_errors", "0");
ini_set("log_errors", "1");

function jsonResponse(
    bool $success,
    string $message,
    array $extra = []
) {
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
    require_once __DIR__ . "/../config/mail.php";

    if (!isset($conn) || !$conn) {
        jsonResponse(false, "Database connection failed.");
    }

    $email = strtolower(
        trim($_POST["email"] ?? "")
    );

    $purpose = trim(
        $_POST["purpose"] ?? ""
    );

    $allowedPurposes = [
        "register",
        "password_reset",
        "change_password",
        "change_email",
        "change_phone"
    ];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonResponse(
            false,
            "Please enter a valid email address."
        );
    }

    if (!in_array($purpose, $allowedPurposes, true)) {
        jsonResponse(
            false,
            "Invalid OTP purpose."
        );
    }

    $userId = null;

    $stmt = mysqli_prepare(
        $conn,
        "
        SELECT id
        FROM users
        WHERE email = ?
        LIMIT 1
        "
    );

    if (!$stmt) {
        throw new Exception(
            "Failed to prepare user lookup: " .
            mysqli_error($conn)
        );
    }

    mysqli_stmt_bind_param(
        $stmt,
        "s",
        $email
    );

    if (!mysqli_stmt_execute($stmt)) {

        $error = mysqli_stmt_error($stmt);

        mysqli_stmt_close($stmt);

        throw new Exception(
            "Failed to check user: " . $error
        );
    }

    mysqli_stmt_bind_result(
        $stmt,
        $foundUserId
    );

    if (mysqli_stmt_fetch($stmt)) {
        $userId = (int)$foundUserId;
    }

    mysqli_stmt_close($stmt);

    if (
        $purpose === "register" &&
        $userId !== null
    ) {
        jsonResponse(
            false,
            "An account with this email already exists."
        );
    }

    $existingAccountPurposes = [
        "password_reset",
        "change_password",
        "change_email",
        "change_phone"
    ];

    if (
        in_array(
            $purpose,
            $existingAccountPurposes,
            true
        ) &&
        $userId === null
    ) {
        jsonResponse(
            false,
            "No account was found with this email."
        );
    }

    $rateStmt = mysqli_prepare(
        $conn,
        "
        SELECT id
        FROM password_otps
        WHERE email = ?
          AND purpose = ?
          AND created_at >= DATE_SUB(
                NOW(),
                INTERVAL 60 SECOND
              )
        ORDER BY id DESC
        LIMIT 1
        "
    );

    if (!$rateStmt) {
        throw new Exception(
            "Failed to prepare rate limit query: " .
            mysqli_error($conn)
        );
    }

    mysqli_stmt_bind_param(
        $rateStmt,
        "ss",
        $email,
        $purpose
    );

    if (!mysqli_stmt_execute($rateStmt)) {

        $error = mysqli_stmt_error($rateStmt);

        mysqli_stmt_close($rateStmt);

        throw new Exception(
            "Failed to check OTP rate limit: " .
            $error
        );
    }

    mysqli_stmt_bind_result(
        $rateStmt,
        $existingOtpId
    );

    if (mysqli_stmt_fetch($rateStmt)) {

        mysqli_stmt_close($rateStmt);

        jsonResponse(
            false,
            "Please wait 60 seconds before requesting another OTP."
        );
    }

    mysqli_stmt_close($rateStmt);

    $deleteStmt = mysqli_prepare(
        $conn,
        "
        DELETE FROM password_otps
        WHERE email = ?
          AND purpose = ?
        "
    );

    if (!$deleteStmt) {
        throw new Exception(
            "Failed to prepare OTP cleanup: " .
            mysqli_error($conn)
        );
    }

    mysqli_stmt_bind_param(
        $deleteStmt,
        "ss",
        $email,
        $purpose
    );

    if (!mysqli_stmt_execute($deleteStmt)) {

        $error = mysqli_stmt_error($deleteStmt);

        mysqli_stmt_close($deleteStmt);

        throw new Exception(
            "Failed to clean old OTP: " .
            $error
        );
    }

    mysqli_stmt_close($deleteStmt);

    $otp = (string)random_int(
        100000,
        999999
    );

    $otpHash = password_hash(
        $otp,
        PASSWORD_DEFAULT
    );

    $expiresAt = date(
        "Y-m-d H:i:s",
        time() + 600
    );

    $insertStmt = mysqli_prepare(
        $conn,
        "
        INSERT INTO password_otps
        (
            user_id,
            email,
            otp_hash,
            purpose,
            expires_at,
            attempts,
            verified
        )
        VALUES (?, ?, ?, ?, ?, 0, 0)
        "
    );

    if (!$insertStmt) {
        throw new Exception(
            "Failed to prepare OTP insert: " .
            mysqli_error($conn)
        );
    }

    mysqli_stmt_bind_param(
        $insertStmt,
        "issss",
        $userId,
        $email,
        $otpHash,
        $purpose,
        $expiresAt
    );

    if (!mysqli_stmt_execute($insertStmt)) {

        $error = mysqli_stmt_error($insertStmt);

        mysqli_stmt_close($insertStmt);

        throw new Exception(
            "Failed to insert OTP: " .
            $error
        );
    }

    mysqli_stmt_close($insertStmt);

    $emailSent = sendOtpEmail(
        $email,
        $otp,
        $purpose
    );

    if (!$emailSent) {

        $cleanupStmt = mysqli_prepare(
            $conn,
            "
            DELETE FROM password_otps
            WHERE email = ?
              AND purpose = ?
            "
        );

        if ($cleanupStmt) {

            mysqli_stmt_bind_param(
                $cleanupStmt,
                "ss",
                $email,
                $purpose
            );

            mysqli_stmt_execute(
                $cleanupStmt
            );

            mysqli_stmt_close(
                $cleanupStmt
            );
        }

        jsonResponse(
            false,
            "Unable to send OTP email. Please check your mail settings."
        );
    }

    $_SESSION["otp_email"] = $email;
    $_SESSION["otp_purpose"] = $purpose;

    jsonResponse(
        true,
        "OTP sent successfully. Please check your email."
    );

} catch (Throwable $e) {

    error_log(
        "OTP ERROR: " .
        $e->getMessage() .
        " | File: " .
        $e->getFile() .
        " | Line: " .
        $e->getLine()
    );

    jsonResponse(
        false,
        "Unable to process OTP request. Please try again."
    );
}
?>