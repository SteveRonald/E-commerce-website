<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $conn = new mysqli("localhost", "root", "", "ecommerce");
    if ($conn->connect_error) die("DB error");
    $stmt = $conn->prepare("SELECT id, name, password_hash FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($id, $name, $hash);
    if ($stmt->fetch() && password_verify($password, $hash)) {
        $_SESSION['user_id'] = $id;
        $_SESSION['user_name'] = $name;
        if (isset($_SESSION['pending_product'])) {
            $pending = $_SESSION['pending_product'];
            unset($_SESSION['pending_product']);
            // Redirect to shop.php with POST data
            echo "<form id='redirectForm' method='POST' action='shop.php'>";
            foreach ($pending as $k => $v) {
                $v = htmlspecialchars($v, ENT_QUOTES);
                echo "<input type='hidden' name='$k' value='$v'>";
            }
            echo "</form>
            <script>document.getElementById('redirectForm').submit();</script>";
            exit();
        } else {
            $redirect = $_GET['redirect'] ?? $_POST['redirect'] ?? '';
            if ($redirect) {
                header("Location: " . htmlspecialchars($redirect));
            } else {
                header("Location: account.php");
            }
            exit();
        }
    } else {
        $error = "Invalid email or password.
         please register first if you don't have an account.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | EcoNest</title>
    <link rel="stylesheet" href="../css/style.css">
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
        .auth-container input[type="password"] {
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
        .auth-container .error {
            color: #e74c3c;
            background: #fbeaea;
            border: 1px solid #e74c3c;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 15px;
            text-align: center;
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
        #gSignInWrapper {
            text-align: center;
            margin-top: 20px;
        }
        .g_id_signin {
            display: inline-block;
            margin-top: 10px;
        }
        .g_id_signin button {
            width: 100%;
            padding: 12px;
            border-radius: 6px;
            font-size: 1.1rem;
            font-weight: bold;
            background-color: #4285F4;
            color: white;
            border: none;
            cursor: pointer;
        }
        .g_id_signin button:hover {
            background-color: #357ae8;
        }
        .auth-container a {
            color: #2f6b29;
            text-decoration: none;
            transition: text-decoration 0.2s;
        }
        .auth-container a:hover {
            text-decoration: underline;
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
                <li><a href="../modules/login.php" class="active">Login</a></li>
            </ul>
        </div>
    </nav>
    <div class="auth-container">
        <h2>Login</h2>
        <?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>
        <form method="POST">
            <label>Email:</label>
            <input type="email" name="email" required>
            <label>Password:</label>
            <input type="password" name="password" required>
            <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($_GET['redirect'] ?? ''); ?>">
            <button type="submit">Login</button>
        </form>
        <div style="text-align:center;margin:32px 0 24px 0;">
            <div style="font-size:1.1em;color:#2f6b29;font-weight:600;margin-bottom:10px;letter-spacing:0.5px;">
                Or
            </div>
            <div id="gSignInWrapper">
                <div id="g_id_onload"
                    data-client_id="633377089412-nq4smjp6ugsb5qvi7orvv7kr1epb9hg1.apps.googleusercontent.com"
                    data-context="signin"
                    data-ux_mode="redirect"
                    data-login_uri="http://localhost/e-commerce/EcoNest/modules/google_callback.php"
                    data-auto_prompt="false">
                </div>
                <div class="g_id_signin"
                    data-type="standard"
                    data-shape="rectangular"
                    data-theme="outline"
                    data-text="signin_with"
                    data-size="large">
                </div>
            </div>
        </div>
        <p>No account? <a href="../modules/register.php">Register</a></p>
        <p>forgot password? <a href="../modules/reset_password.php">Reset it</a></p>
    </div>

    <footer  style="background-color: #2f6b29; color: white; text-align: center; padding: 30px 10px 18px 10px; font-size: 1rem;">
        <div  style="margin: 12px 0; display: flex; justify-content: center; gap: 24px; flex-wrap: wrap;">
            <a href="../pages/about.html" style="color:#fff;text-decoration:none;font-size:1.08em;transition:color 0.2s;">About</a>
            <a href="../pages/contact.html" style="color:#fff;text-decoration:none;font-size:1.08em;transition:color 0.2s;">Contact</a>
            <a href="../pages/privacy.html" style="color:#fff;text-decoration:none;font-size:1.08em;transition:color 0.2s;">Privacy Policy</a>
            <a href="../pages/faq.html" style="color:#fff;text-decoration:none;font-size:1.08em;transition:color 0.2s;">FAQ</a>
            <a href="../modules/shop_main.php" style="color:#fff;text-decoration:none;font-size:1.08em;transition:color 0.2s;">Shop</a>
        </div>
        <div style="margin: 12px 0; display: flex; justify-content: center; gap: 24px; flex-wrap: wrap;">
            <a href="#" title="Facebook" style="color:#fff;font-size:1.3em;"><i class="fab fa-facebook-f"></i></a>
            <a href="#" title="Twitter" style="color:#fff;font-size:1.3em;"><i class="fab fa-twitter"></i></a>
            <a href="#" title="Instagram" style="color:#fff;font-size:1.3em;"><i class="fab fa-instagram"></i></a>
        </div>
        <p>&copy; 2025 EcoNest. All rights reserved.</p>
    </footer>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://accounts.google.com/gsi/client" async defer></script>
</body>
</html>