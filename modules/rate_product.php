
<?php
session_start();
$conn = null;
require_once __DIR__ . '/db_connect.php';
$user_id = $_SESSION['user_id'] ?? null;
$product_id = intval($_POST['product_id'] ?? 0);
$rating = intval($_POST['rating'] ?? 0);
if ($product_id && $rating >= 1 && $rating <= 5) {
    // Optional: Only one rating per user per product
    if ($user_id) {
        $conn->query("DELETE FROM product_ratings WHERE product_id=$product_id AND user_id=$user_id");
        $conn->query("INSERT INTO product_ratings (product_id, user_id, rating) VALUES ($product_id, $user_id, $rating)");
    } else {
        $conn->query("INSERT INTO product_ratings (product_id, rating) VALUES ($product_id, $rating)");
    }
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
}
