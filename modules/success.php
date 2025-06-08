<?php
$status = $_GET['status'] ?? '';
$message = $_GET['message'] ?? '';
$emailMsg = $_GET['emailmsg'] ?? '';
$redirectUrl = $status === 'success' ? 'shop_main.php' : 'shop.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../images/logo.jpg">
    <title><?php echo $status === 'success' ? 'Success' : 'Error'; ?></title>
    <style>
        body {
            background: #f4f4f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
            padding: 2rem;
            text-align: center;
            width: 100%;
            max-width: 400px;
            position: relative;
        }

        .icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            font-size: 2.5rem;
            color: #fff;
            margin-bottom: 20px;
        }

        .icon.success {
            background: rgb(40, 173, 96);
        }

        .icon.error {
            background: #e74c3c;
        }

        .card-title {
            font-size: 1.8rem;
            font-weight: bold;
            color: #23243a;
            margin-bottom: 10px;
        }

        .card-message {
            color: #555;
            font-size: 1.1rem;
            margin-bottom: 20px;
        }

        .email-message {
            color: #2f6b29;
            font-size: 1rem;
            margin-bottom: 18px;
            background: #e8f5e9;
            border-radius: 8px;
            padding: 10px 0;
        }

        .progress-container {
            width: 100%;
            background: #e0e0e0;
            border-radius: 10px;
            overflow: hidden;
            height: 10px;
            margin-top: 20px;
            position: relative;
        }

        .progress-bar {
            height: 100%;
            background: #2ecc71;
            width: 100%;
            transition: width 1s linear;
        }

        .countdown {
            margin-top: 10px;
            font-size: 0.9rem;
            color: #888;
        }

        .retry-btn {
            margin-top: 1.5rem;
            padding: 0.7rem 1.5rem;
            background: #e74c3c;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .retry-btn:hover {
            background: #c0392b;
        }
    </style>
</head>

<body>
    <div class="card">
        <?php if ($status === 'success'): ?>
            <div class="icon success">&#10003;</div>
            <div class="card-title">Payment Successful</div>
            <div class="card-message"><?php echo htmlspecialchars($message ?: 'Your order has been placed successfully.'); ?></div>
            <?php if ($emailMsg): ?>
                <div class="email-message"><?php echo htmlspecialchars($emailMsg); ?></div>
            <?php endif; ?>
            <div class="progress-container">
                <div class="progress-bar" id="progressBar"></div>
            </div>
            <div class="countdown" id="countdownText">Redirecting in 5 seconds...</div>
            <script>
                let countdown = 5;
                const progressBar = document.getElementById('progressBar');
                const countdownText = document.getElementById('countdownText');
                const interval = setInterval(() => {
                    countdown--;
                    progressBar.style.width = `${(countdown / 5) * 100}%`;
                    countdownText.textContent = `Redirecting in ${countdown} second${countdown === 1 ? '' : 's'}...`;
                    if (countdown <= 0) {
                        clearInterval(interval);
                        window.location.href = "<?php echo $redirectUrl; ?>";
                    }
                }, 1000);
            </script>
        <?php else: ?>
            <div class="icon error">&#10007;</div>
            <div class="card-title">Payment Failed</div>
            <div class="card-message"><?php echo htmlspecialchars($message ?: 'An error occurred during payment.'); ?></div>
            <a href="<?php echo $redirectUrl; ?>" class="retry-btn">Retry</a>
        <?php endif; ?>
    </div>
</body>

</html>