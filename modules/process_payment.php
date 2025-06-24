<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../phpmailer-master/src/PHPMailer.php';
require '../phpmailer-master/src/SMTP.php';
require '../phpmailer-master/src/Exception.php';

// Adjust path if needed
session_start();

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Connect to DB
    $conn = new mysqli("localhost", "root", "", "ecommerce");
    if ($conn->connect_error) {
        header("Location: ../pages/cart.html?status=error&message=Database connection failed.");
        exit();
    }

    // Fetch latest address, city, phone for delivery
    if ($user_id) {
        $stmt = $conn->prepare("SELECT address, city, phone FROM users WHERE id=?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($address, $city, $phone);
        $stmt->fetch();
        $stmt->close();
        $delivery_address = $address . ', ' . $city . ' (' . $phone . ')';
    } else {
        $delivery_address = null;
    }

    // Handle cart order
    if (isset($_POST['cart_data'])) {
        $cart = json_decode($_POST['cart_data'], true);
        $fullName = htmlspecialchars($_POST['full_name']);
        $mpesaNumber = htmlspecialchars($_POST['mpesa_number']);

        foreach ($cart as $item) {
            $productName = htmlspecialchars($item['name']);
            $productPrice = floatval($item['price']);
            $productColor = htmlspecialchars($item['color']);
            $productQuantity = intval($item['quantity']);
            $totalPrice = $productPrice * $productQuantity;
            $productImage = htmlspecialchars($item['image']);
            $response = "Cart order";
            $order_status = "received";
            $stmt = $conn->prepare("INSERT INTO transactions (product_name, product_price, product_color, product_quantity, total_price, full_name, mpesa_number, response, user_id, delivery_address, order_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sdsidssssss", $productName, $productPrice, $productColor, $productQuantity, $totalPrice, $fullName, $mpesaNumber, $response, $user_id, $delivery_address, $order_status);
            $stmt->execute();
            $stmt->close();
        }
        $conn->close();

        // Send email for cart order
        $userEmail = '';
        if ($user_id) {
            $conn2 = new mysqli("localhost", "root", "", "ecommerce");
            $stmt2 = $conn2->prepare("SELECT email FROM users WHERE id=?");
            $stmt2->bind_param("i", $user_id);
            $stmt2->execute();
            $stmt2->bind_result($userEmail);
            $stmt2->fetch();
            $stmt2->close();
            $conn2->close();
        } else {
            $userEmail = isset($_POST['email']) ? $_POST['email'] : '';
        }

        if ($userEmail) {
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'okothroni863@gmail.com';
                $mail->Password   = 'lmag tcnr iyki avzx';
                $mail->SMTPSecure = 'tls';
                $mail->Port       = 587;

                $mail->setFrom('okothroni863@gmail.com', 'EcoNest');
                $mail->addAddress($userEmail, $fullName);

                // Build cart items HTML
                $itemsHtml = '';
                foreach ($cart as $item) {
                    $itemsHtml .= "<li>{$item['name']} (Color: {$item['color']}, Qty: {$item['quantity']}, Price: KSh {$item['price']})</li>";
                }

                $mail->isHTML(true);
                $mail->Subject = 'Your EcoNest Cart Order Confirmation';
                $mail->Body    = "
                    <h2>Thank you for your order, {$fullName}!</h2>
                    <p>Order Details:</p>
                    <ul>
                        {$itemsHtml}
                    </ul>
                    <p>Delivery Address: {$delivery_address}</p>
                    <p>We will contact you soon.</p>
                ";
                $mail->AltBody = "Thank you for your order, {$fullName}!\nOrder Details:\n" . strip_tags($itemsHtml) . "\nDelivery Address: {$delivery_address}";

                $mail->send();
            } catch (Exception $e) {
                // Optionally log error
            }
        }

        // Set a flag to clear cart on next page load
        echo "<script>sessionStorage.setItem('clearCart', '1');window.location.href='../modules/success.php?status=success&message=Cart order placed successfully.&emailmsg=Order confirmation email sent! Check your inbox.';</script>";
        exit();
    }

    // Handle single product order
    // Optional: CSRF check
    if (isset($_SESSION['csrf_token']) && isset($_POST['csrf_token'])) {
        if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            header("Location:../modules/shop.php?status=error&message=Invalid CSRF token.");
            exit();
        }
    }

    $productName = htmlspecialchars($_POST['product_name']);
    $productPrice = floatval($_POST['product_price']);
    $productColor = htmlspecialchars($_POST['product_color']);
    $productQuantity = intval($_POST['product_quantity']);
    $fullName = htmlspecialchars($_POST['full_name']);
    $mpesaNumber = htmlspecialchars($_POST['mpesa_number']);
    $totalPrice = $productPrice * $productQuantity;
    $response = "No MPESA push sent";
    $order_status = "received";

    // Validate MPESA number (Kenyan format: 07XXXXXXXX, 01XXXXXXXX, 011XXXXXXX)
    if (!preg_match('/^(07\d{8}|01\d{8}|011\d{7})$/', $mpesaNumber)) {
        header("Location:../modules/shop.php?status=error&message=Invalid MPESA number. It must be a valid Safaricom number.");
        exit();
    }

    // Validate quantity
    if ($productQuantity <= 0) {
        header("Location: ../modules/shop.php?status=error&message=Invalid product quantity.");
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO transactions (product_name, product_price, product_color, product_quantity, total_price, full_name, mpesa_number, response, user_id, delivery_address, order_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sdsidssssss", $productName, $productPrice, $productColor, $productQuantity, $totalPrice, $fullName, $mpesaNumber, $response, $user_id, $delivery_address, $order_status);

    if ($stmt->execute()) {
        // Get user email (fetch from DB)
        $userEmail = '';
        if ($user_id) {
            $stmt2 = $conn->prepare("SELECT email FROM users WHERE id=?");
            $stmt2->bind_param("i", $user_id);
            $stmt2->execute();
            $stmt2->bind_result($userEmail);
            $stmt2->fetch();
            $stmt2->close();
        } else {
            // If guest checkout, get from form if available
            $userEmail = isset($_POST['email']) ? $_POST['email'] : '';
        }

        if ($userEmail) {
            $mail = new PHPMailer(true);
            try {
                //Server settings
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'okothroni863@gmail.com'; // Your Gmail address
                $mail->Password   = 'lmag tcnr iyki avzx';    // Gmail App Password (not your Gmail password)
                $mail->SMTPSecure = 'tls';
                $mail->Port       = 587;

                //Recipients
                $mail->setFrom('okothroni863@gmail.com', 'EcoNest');
                $mail->addAddress($userEmail, $fullName);

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Your EcoNest Order Confirmation';
                $mail->Body    = "
                    <h2>Thank you for your order, {$fullName}!</h2>
                    <p>Order Details:</p>
                    <ul>
                        <li>Product: {$productName}</li>
                        <li>Color: {$productColor}</li>
                        <li>Quantity: {$productQuantity}</li>
                        <li>Total: KSh {$totalPrice}</li>
                        <li>Delivery Address: {$delivery_address}</li>
                    </ul>
                    <p>We will contact you soon.</p>
                ";
                $mail->AltBody = "Thank you for your order, {$fullName}!\nProduct: {$productName}\nColor: {$productColor}\nQuantity: {$productQuantity}\nTotal: KSh {$totalPrice}\nDelivery Address: {$delivery_address}";

                $mail->send();
                // Optionally log success
            } catch (Exception $e) {
                // Optionally log error: $mail->ErrorInfo
            }
        }


        header("Location: success.php?status=success&message=Your order has been placed successfully.&emailmsg=Order confirmation email sent! Check your inbox.");
        exit();
    } else {
        header("Location: ../modules/shop.php?status=error&message=Failed to save transaction.");
        exit();
    }

    $stmt->close();
    $conn->close();
    exit();
} else {
    header("Location:../modules/shop.php?status=error&message=Invalid request method.");
    exit();
}
