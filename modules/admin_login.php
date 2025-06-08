<?php
session_start();
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) {
    header("Location: admin.php");
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $conn = new mysqli("localhost", "root", "", "ecommerce");
    if ($conn->connect_error) die("DB error");
    $stmt = $conn->prepare("SELECT id, password_hash, role FROM admins WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($id, $hash, $role);
    if ($stmt->fetch() && password_verify($password, $hash)) {
        $_SESSION['is_admin'] = true;
        $_SESSION['admin_id'] = $id;
        $_SESSION['admin_username'] = $username;
        $_SESSION['admin_role'] = $role;
        header("Location: admin.php");
        exit();
    } else {
        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | EcoNest</title>
    <link rel="icon" type="image/png" href="../images/logo.jpg">
    <style>
        body {
            background: #f4f4f4;
            font-family: 'Segoe UI', Arial, sans-serif;
        }

        .admin-auth-container {
            max-width: 400px;
            margin: 60px auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
            padding: 30px 30px 20px 30px;
        }

        h2 {
            color: #2f6b29;
            text-align: center;
        }

        label {
            font-weight: 600;
            color: #2f6b29;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 8px 0 16px 0;
            border: 1px solid #c8e6c9;
            border-radius: 6px;
            font-size: 1rem;
        }

        button {
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

        button:hover {
            background: #5d8c56;
        }

        .error {
            color: #e74c3c;
            background: #fbeaea;
            border: 1px solid #e74c3c;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 15px;
            text-align: center;
        }

        p {
            text-align: center;
        }

        a {
            color: #2f6b29;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <!-- Admin Navbar/Header -->
    <nav style="width:100%;background:#2f6b29;padding:0;margin-bottom:30px;box-shadow:0 2px 8px rgba(47,107,41,0.06);">
        <div style="max-width:600px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;padding:0 24px;">
            <span style="font-size:1.7em;font-weight:bold;color:#fff;letter-spacing:1px;font-family:'Segoe UI',Arial,sans-serif;padding:10px 0;">
                EcoNest <span style="color:#ffe082;">Admin</span>
            </span>
            <span style="color:#fff;font-size:1.1em;font-weight:500;">Admin Panel</span>
        </div>
    </nav>
    <div class="admin-auth-container">
        <h2>Admin Login</h2>
        <?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>
        <form method="POST">
            <label>Username:</label>
            <input type="text" name="username" required>
            <label>Password:</label>
            <input type="password" name="password" required>
            <button type="submit">Login</button>
        </form>
        <p>Not registered? <a href="../modules/admin_register.php">Register</a></p>
    </div>
</body>

</html>