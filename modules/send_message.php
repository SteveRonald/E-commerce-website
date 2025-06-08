<?php
header('Content-Type: application/json');
require '../phpmailer-master/src/PHPMailer.php';
require '../phpmailer-master/src/SMTP.php';
require '../phpmailer-master/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$msg = '';
$status = 'error';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars($_POST['name'] ?? '');
    $email = htmlspecialchars($_POST['email'] ?? '');
    $subject = htmlspecialchars($_POST['subject'] ?? '');
    $message = htmlspecialchars($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $msg = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = "Invalid email address.";
    } else {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'okothroni863@gmail.com';
            $mail->Password = 'lmag tcnr iyki avzx';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom($email, $name);
            $mail->addAddress('okothroni863@gmail.com');
            $mail->addReplyTo($email, $name);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = "<p>You have received a new message from <strong>$name</strong>.</p>
                           <p><strong>Subject:</strong> $subject</p>
                           <p><strong>Message:</strong><br>$message</p>";
            $mail->AltBody = "You have received a new message from $name.\n\nSubject: $subject\n\nMessage:\n$message";

            $mail->send();
            $msg = "Message sent successfully.";
            $status = 'success';
        } catch (Exception $e) {
            $msg = "Failed to send the message. Error: {$mail->ErrorInfo}";
        }
    }
}
echo json_encode(['status' => $status, 'message' => $msg]);
exit;
?>
<!DOCTYPE html>
<html>

<head>
    <title>Send Message</title>
    <style>
        #msg {
            display: none;
            margin: 20px 0;
            padding: 12px;
            border-radius: 6px;
            font-weight: bold;
        }

        #msg.success {
            background: #e8f5e9;
            color: #256029;
            border: 1px solid #256029;
        }

        #msg.error {
            background: #ffebee;
            color: #c0392b;
            border: 1px solid #c0392b;
        }
    </style>
</head>

<body>
    <form id="contactForm" method="POST">
        <!-- your form fields here -->
    </form>
    <div id="msg"></div>
    <script>
        // Display PHP message if set
        <?php if (!empty($msg)): ?>
            var msgDiv = document.getElementById('msg');
            msgDiv.textContent = <?php echo json_encode($msg); ?>;
            msgDiv.className = '<?= (strpos($msg, "successfully") !== false) ? "success" : "error" ?>';
            msgDiv.style.display = 'block';
            setTimeout(function() {
                msgDiv.style.display = 'none';
            }, 4000);
        <?php endif; ?>
    </script>
</body>

</html>