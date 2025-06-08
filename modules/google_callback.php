<?php
session_start();

$id_token = '';
if (isset($_POST['credential'])) {
    $id_token = $_POST['credential'];
} elseif (isset($_GET['credential'])) {
    $id_token = $_GET['credential'];
}

if ($id_token) {
    $client_id = '633377089412-nq4smjp6ugsb5qvi7orvv7kr1epb9hg1.apps.googleusercontent.com'; // Replace with your client ID

    // Verify token using Google API
    $url = "https://oauth2.googleapis.com/tokeninfo?id_token=" . $id_token;
    $response = file_get_contents($url);
    $payload = json_decode($response, true);

    if ($payload && isset($payload['email'])) {
        $email = $payload['email'];
        $name = $payload['name'];

        // Check if user exists
        $conn = new mysqli("localhost", "root", "", "ecommerce");
        $stmt = $conn->prepare("SELECT id, address, city, phone FROM users WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            // User exists, log them in
            $stmt->bind_result($id, $address, $city, $phone);
            $stmt->fetch();
            $_SESSION['user_id'] = $id;
            $_SESSION['user_name'] = $name;
            header("Location: ../modules/account.php");
            exit();
        } else {
            // User does not exist, ask for address/city/phone
            $_SESSION['google_name'] = $name;
            $_SESSION['google_email'] = $email;
            header("Location: ../modules/complete_profile.php");
            exit();
        }
    }
}
header("Location: ../modules/register.php?error=Google+Sign-In+failed");
exit();
