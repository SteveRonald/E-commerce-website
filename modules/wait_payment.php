<?php
// filepath: c:\xampp\htdocs\E-Commerce\EcoNest\wait_payment.php
session_start();
$phone = isset($_GET['phone']) ? htmlspecialchars($_GET['phone']) : '';
$amount = isset($_GET['amount']) ? htmlspecialchars($_GET['amount']) : '';
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Waiting for Payment | EcoNest</title>
    <style>
        body {
            background: #f4f4f4;
            font-family: Arial, sans-serif;
        }

        .container {
            max-width: 420px;
            margin: 80px auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            padding: 36px 30px 28px 30px;
            text-align: center;
        }

        .loader {
            border: 6px solid #e0e0e0;
            border-top: 6px solid #2f6b29;
            border-radius: 50%;
            width: 48px;
            height: 48px;
            animation: spin 1s linear infinite;
            margin: 0 auto 18px auto;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .info {
            color: #2f6b29;
            font-size: 1.1em;
            margin-bottom: 12px;
        }

        .phone {
            color: #444;
            font-weight: bold;
        }

        .amount {
            color: #e67e22;
            font-weight: bold;
        }

        .error-msg {
            color: #e74c3c;
            margin-top: 18px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="loader"></div>
        <div class="info">
            Please complete the payment of <span class="amount">Ksh <?php echo $amount; ?></span> on your phone.<br>
            An MPESA prompt was sent to <span class="phone"><?php echo $phone; ?></span>.
        </div>
        <div style="color:#888;font-size:0.98em;">
            After payment, your order will be processed automatically.<br>
            <br>
            <b>Do not close this page.</b>
        </div>
        <div id="errorMsg" class="error-msg"></div>
    </div>
    <script>
        const ref = "<?php echo isset($_GET['ref']) ? $_GET['ref'] : ''; ?>";
        // Poll every 5 seconds for payment status
        let pollCount = 0;

        function checkPaymentStatus() {
            fetch('check_payment_status.php?ref=' + encodeURIComponent(ref))
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        window.location.href = 'payment_success.php';
                    } else if (data.status === 'failed') {
                        document.getElementById('errorMsg').textContent = "Payment failed or was cancelled. Please try again.";
                    } else {
                        // Keep polling if still pending
                        pollCount++;
                        if (pollCount < 36) { // Poll for up to 3 minutes
                            setTimeout(checkPaymentStatus, 5000);
                        } else {
                            document.getElementById('errorMsg').textContent = "Payment confirmation timed out. Please check your MPESA app.";
                        }
                    }
                })
                .catch(() => {
                    setTimeout(checkPaymentStatus, 5000);
                });
        }
        checkPaymentStatus();
    </script>
</body>

</html>