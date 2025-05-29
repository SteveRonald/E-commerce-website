<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Payment Successful | EcoNest</title>
    <style>
        body { background: #f4f4f4; font-family: Arial, sans-serif; }
        .container {
            max-width: 420px;
            margin: 80px auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
            padding: 36px 30px 28px 30px;
            text-align: center;
        }
        .success-icon {
            font-size: 3em;
            color: #2ecc71;
            margin-bottom: 18px;
        }
        .success-msg {
            color: #2f6b29;
            font-size: 1.2em;
            margin-bottom: 12px;
            font-weight: bold;
        }
        .details {
            color: #444;
            margin-bottom: 18px;
        }
        a {
            color: #2f6b29;
            text-decoration: none;
            font-weight: bold;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-icon">&#10004;</div>
        <div class="success-msg">Payment Successful!</div>
        <div class="details">
            Thank you for your payment.<br>
            Your order has been received and is being processed.<br>
            You will receive a confirmation SMS soon.
        </div>
        <a href="../modules/shop.php">Continue Shopping</a>
    </div>
</body>
</html>