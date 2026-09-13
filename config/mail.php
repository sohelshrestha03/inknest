<?php
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
require_once __DIR__ . "/../vendor/autoload.php";

function sendOtpEmail(
    string $email,
    string $otp,
    string $purpose = "register"
): bool {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = "smtp.gmail.com";
        $mail->SMTPAuth = true;
        $mail->Username = "da9000913@gmail.com";
        $mail->Password = "aajaunwqxqavvtra";
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->setFrom(
            $mail->Username,
            "Inknest"
        );
        $mail->addAddress($email);

        switch ($purpose) {
            case "register":
                $subject = "Inknest Registration OTP";
                break;
            case "password_reset":
                $subject = "Inknest Password Reset OTP";
                break;
            case "change_password":
                $subject = "Inknest Password Change OTP";
                break;
            case "change_email":
                $subject = "Inknest Email Verification OTP";
                break;
            case "change_phone":
                $subject = "Inknest Phone Verification OTP";
                break;
            default:
                $subject = "Inknest Verification OTP";
                break;
        }

        $mail->Subject = $subject;
        $mail->isHTML(true);
        $mail->Body = "
            <div style='
                max-width:500px;
                margin:0 auto;
                padding:30px;
                font-family:Arial,sans-serif;
                background:#ffffff;
                border:1px solid #eeeeee;
                border-radius:10px;
            '>
                <h2 style='color:#111111;'>
                    Inknest
                </h2>
                <p>
                    Your verification code is:
                </p>
                <div style='
                    margin:20px 0;
                    padding:15px;
                    text-align:center;
                    font-size:32px;
                    font-weight:bold;
                    letter-spacing:8px;
                    background:#f5f5f5;
                    border-radius:8px;
                    color:#111111;
                '>
                    {$otp}
                </div>
                <p style='color:#555555;'>
                    This OTP expires in <strong>10 minutes</strong>.
                </p>
                <p style='
                    margin-top:20px;
                    font-size:13px;
                    color:#777777;
                '>
                    If you did not request this code,
                    you can safely ignore this email.
                </p>
            </div>
        ";

        $mail->AltBody ="Your Inknest verification code is: " .$otp .". This OTP expires in 10 minutes.";
        return $mail->send();
    } catch (Exception $e) {
        error_log("OTP mail error: " .$mail->ErrorInfo);
        return false;
    }
}