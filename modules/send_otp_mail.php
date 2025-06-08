<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../phpmailer-master/src/PHPMailer.php';
require '../phpmailer-master/src/SMTP.php';
require '../phpmailer-master/src/Exception.php';

function sendOTPEmail($toEmail, $otp)
{
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'okothroni863@gmail.com';        // your Gmail address
        $mail->Password   = 'lmag tcnr iyki avzx';           // your Gmail App Password (do not share)
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('okothroni863@gmail.com', 'EcoNest Support - Do Not Reply');
        $mail->addAddress($toEmail);

        // This tells users not to reply, but it's not a technical block.
        // Use the correct header below instead of 'Reply-TO'
        $mail->addReplyTo('no-reply@example.com', 'No Reply');

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your OTP Code for Password Reset';
        $mail->Body    = "
            <h3>Dear User,</h3>
            <p>Your OTP is: <strong>$otp</strong></p>
            <p>This OTP will expire in 5 minutes.</p>
            <br>
            <p style='color:gray; font-size:12px;'>This is an automated email from EcoNest. Please do not reply to this email.</p>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
