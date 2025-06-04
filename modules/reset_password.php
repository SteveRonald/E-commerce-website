<?php
session_start();
$host = "localhost";
$username = "root";
$password = "";
$database = "ecommerce";
$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$msg = "";
$success_reset = false;

function generateOTP($length = 6) {
    return str_pad(random_int(0, 999999), $length, '0', STR_PAD_LEFT);
}

require_once 'send_otp_mail.php';

// Step 1: Send OTP
if (isset($_POST["send_otp"])) {
    $email = trim($_POST["email"]);
    $check = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $otp = generateOTP();
        $expiry = date("Y-m-d H:i:s", time() + 300);
        $update = $conn->prepare("UPDATE users SET otp_code = ?, otp_expiry = ? WHERE email = ?");
        $update->bind_param("sss", $otp, $expiry, $email);
        $update->execute();

        if (sendOTPEmail($email, $otp)) {
            $_SESSION["email"] = $email;
            $msg = "OTP sent to your email.";
        } else {
            $msg = "❗ Failed to send OTP. Try again.";
        }
    } else {
        $msg = "❗ Email not found.";
    }
}

// Step 2: Reset Password
if (isset($_POST["reset_password"])) {
    $email = $_SESSION["email"] ?? "";
    $otp = trim($_POST["otp"]);
    $new_pass = $_POST["new_password"];
    $hashed = password_hash($new_pass, PASSWORD_DEFAULT);

    $query = $conn->prepare("SELECT otp_code, otp_expiry FROM users WHERE email = ?");
    $query->bind_param("s", $email);
    $query->execute();
    $res = $query->get_result()->fetch_assoc();

    if ($res) {
        if ($res["otp_code"] === $otp && strtotime($res["otp_expiry"]) >= time()) {
            $update = $conn->prepare("UPDATE users SET password_hash = ?, otp_code = NULL, otp_expiry = NULL WHERE email = ?");
            $update->bind_param("ss", $hashed, $email);
            $update->execute();

            $msg = "Password reset successful. Redirecting to login page...";
            $success_reset = true;

            echo "<script>
                setTimeout(function() {
                    window.location.href = 'login.php';
                }, 2500);
            </script>";
        } else {
            $msg = "❗ Invalid or expired OTP.";
        }
    } else {
        $msg = "❗ Email session expired.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reset Password | EcoNest</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f4f4f4; margin:0; }
        .navbar {
            width: 100%;
            background: #2f6b29;
            padding: 0;
            margin: 0 0 30px 0;
            box-shadow: 0 2px 8px rgba(47,107,41,0.06);
        }
        .navbar-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
        }
        .navbar-logo {
            font-size: 2em;
            font-weight: bold;
            color: #fff;
            letter-spacing: 1px;
            font-family: 'Segoe UI', Arial, sans-serif;
            padding: 0 10px;
        }
        .navbar ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
        }
        .navbar li {
            margin: 0;
        }
        .navbar a {
            display: block;
            color: #fff;
            text-decoration: none;
            padding: 18px 22px;
            font-weight: 500;
            font-size: 1.08em;
            border-radius: 4px;
            transition: background 0.2s;
        }
        .navbar a:hover, .navbar .active {
            color: #FFD700;
        }
        .auth-container {
            max-width: 400px;
            margin: 50px auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
            padding: 30px 40px 20px 40px;
        }
        .auth-container h2 {
            color: #2f6b29;
            margin-bottom: 20px;
            text-align: center;
        }
        .auth-container label {
            font-weight: 600;
            color: #2f6b29;
        }
        .auth-container input[type="email"],
        .auth-container input[type="password"],
        .auth-container input[type="text"] {
            width: 100%;
            padding: 12px;
            margin: 8px 0 18px 0;
            border: 1px solid #c8e6c9;
            border-radius: 6px;
            font-size: 1rem;
        }
        .auth-container button {
            width: 100%;
            background: #2f6b29;
            color: #fff;
            border: none;
            padding: 12px;
            border-radius: 6px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
            transition: background 0.3s;
        }
        .auth-container button:hover {
            background: #5d8c56;
        }
        .auth-container .msg {
            color: #27ae60;
            background: #eafaf1;
            border: 1px solid #27ae60;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 15px;
            text-align: center;
            font-weight: 600;
        }
        .auth-container .error {
            color: #e74c3c;
            background: #fbeaea;
            border: 1px solid #e74c3c;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 15px;
            text-align: center;
            font-weight: 600;
        }
        .auth-container p {
            text-align: center;
            margin-top: 18px;
        }
        .auth-container a {
            color: #2f6b29;
            text-decoration: underline;
        }
        @media (max-width: 700px) {
            .auth-container { max-width: 98vw; padding: 18px 5vw 10px 5vw; }
            .navbar-content { flex-direction: column; align-items: flex-start; padding: 0 8px; }
            .navbar-logo { font-size: 1.3em; padding: 10px 0 0 0; }
            .navbar ul { width: 100%; }
            .navbar a { padding: 14px 10px; font-size: 1em; }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-content">
            <span class="navbar-logo">EcoNest 🌿</span>
            <ul>
                <li><a href="../pages/index.html">Home</a></li>
                <li><a href="../modules/shop_main.php">Shop</a></li>
                <li><a href="../modules/cart.php">Cart</a></li>
                <li><a href="../modules/register.php">Register</a></li>
                <li><a href="../modules/login.php">Login</a></li>
            </ul>
        </div>
    </nav>
    <div class="auth-container">
        <h2>Reset Password</h2>
        <?php
            // Destroy session only after showing the success message
            if ($success_reset) {
                session_unset();
                session_destroy();
            }
        ?>
        <!-- Step 1: Enter Email -->
        <?php if (!$success_reset && !isset($_SESSION["email"])): ?> 
            <form method="POST" autocomplete="off">
                <label>Email:</label>
                <input type="email" name="email" required>
                <button type="submit" name="send_otp">Send OTP</button>
            </form>
        <!-- Step 2: Enter OTP + New Password -->
        <?php elseif (!$success_reset): ?>
            <form method="POST" autocomplete="off">
                <label>Enter OTP:</label>
                <input type="text" name="otp" maxlength="6" required>

                <label>New Password:</label>
                <input type="password" name="new_password" required>

                <button type="submit" name="reset_password">Reset Password</button>
            </form>
        <?php endif; ?>

        <?php if ($msg): ?>
            <div id="msgBox" class="<?= strpos($msg, '❗') !== false ? 'error' : 'msg' ?>"><?= htmlspecialchars($msg) ?></div>
            <script>
                setTimeout(function() {
                    var box = document.getElementById('msgBox');
                    if (box) box.style.display = 'none';
                }, 3500); // 3.5 seconds
            </script>
        <?php endif; ?>

        <p>
            <a href="login.php"><i class="fa fa-arrow-left"></i> Back to Login</a>
        </p>
    </div>
   
</body>
</html>
