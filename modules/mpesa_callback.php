<?php
session_start();
$data = json_decode(file_get_contents('php://input'), true);
$checkoutRequestID = $data['Body']['stkCallback']['CheckoutRequestID'] ?? null;
$resultCode = $data['Body']['stkCallback']['ResultCode'] ?? null;

$conn = new mysqli("localhost", "root", "", "ecommerce");
if ($conn->connect_error) exit();

if ($checkoutRequestID) {
    if ($resultCode == 0) {
        // Payment successful
        $conn->query("UPDATE transactions SET order_status='received' WHERE checkout_request_id='$checkoutRequestID'");
    } else {
        // Payment failed/cancelled
        $conn->query("UPDATE transactions SET order_status='failed' WHERE checkout_request_id='$checkoutRequestID'");
    }
}

echo json_encode(["ResultCode" => 0, "ResultDesc" => "Accepted"]);
