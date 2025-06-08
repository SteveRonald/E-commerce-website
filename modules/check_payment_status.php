<?php
session_start();
header('Content-Type: application/json');
if (isset($_GET['ref'])) {
    $ref = $_GET['ref'];
} elseif (isset($_SESSION['pending_checkout'])) {
    $ref = $_SESSION['pending_checkout'];
} else {
    echo json_encode(['status' => 'pending']);
    exit;
}

$conn = new mysqli("localhost", "root", "", "ecommerce");
$res = $conn->query("SELECT order_status FROM transactions WHERE checkout_request_id='$ref' LIMIT 1");
if ($res && $row = $res->fetch_assoc()) {
    if ($row['order_status'] == 'received') {
        unset($_SESSION['pending_checkout']);
        echo json_encode(['status' => 'success']);
        exit;
    }
    if ($row['order_status'] == 'failed') {
        echo json_encode(['status' => 'failed']);
        exit;
    }
}
echo json_encode(['status' => 'pending']);
