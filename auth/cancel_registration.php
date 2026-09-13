<?php
session_start();
unset(
    $_SESSION["pending_registration"],
    $_SESSION["register_email"],
    $_SESSION["register_otp_verified"],
    $_SESSION["otp_verified"],
    $_SESSION["otp_verified_email"],
    $_SESSION["otp_verified_purpose"],
    $_SESSION["otp_verified_user_id"],
    $_SESSION["otp_email"],
    $_SESSION["otp_purpose"]
);
echo json_encode(["success" => true]);
exit();
?>