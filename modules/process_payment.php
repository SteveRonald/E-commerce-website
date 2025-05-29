<?php
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
        header("Location: ../modules/success.php?status=success&message=Cart order placed successfully");
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
        header("Location: ../modules/success.php?status=success&message=Transaction successful, and order sent");
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
?>