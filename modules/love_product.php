<?php
session_start();
$conn = new mysqli("localhost", "root", "", "ecommerce");
$user_id = $_SESSION['user_id'] ?? null;
$product_id = intval($_POST['product_id'] ?? 0);
if ($product_id) {
    // Optional: Only one love per user per product
    if ($user_id) {
        $res = $conn->query("SELECT id FROM product_loves WHERE product_id=$product_id AND user_id=$user_id");
        if ($res->num_rows == 0) {
            $conn->query("INSERT INTO product_loves (product_id, user_id) VALUES ($product_id, $user_id)");
        }
    } else {
        $conn->query("INSERT INTO product_loves (product_id) VALUES ($product_id)");
    }
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
}
