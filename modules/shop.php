<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    // Save the POST data in session to restore after login
    $_SESSION['pending_product'] = $_POST;
    header("Location: shop_main.php?login_required=1");
    exit();
}

// Only accept POST from the shop form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productName = htmlspecialchars($_POST['product_name'] ?? '');
    $productPrice = floatval($_POST['product_price'] ?? 0);
    $productImage = htmlspecialchars($_POST['product_image'] ?? '');
    $productColor = htmlspecialchars($_POST['product_color'] ?? 'Default');
    $productQuantity = intval($_POST['product_quantity'] ?? 1);
    $totalPrice = $productPrice * $productQuantity;
} else {
    // If not POST, show error
    echo "<h1>Error: Invalid access.</h1>";
    echo "<p>Please go back and select a product to shop.</p>";
    exit();
}

// CSRF protection (optional, but recommended)
$csrf_token = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrf_token;

// Status/message for redirects
$message = isset($_GET['message']) ? htmlspecialchars($_GET['message']) : null;
$status = isset($_GET['status']) ? htmlspecialchars($_GET['status']) : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Now - <?php echo $productName; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .message {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .message.info {
            background-color: #cce5ff;
            color: #004085;
            border: 1px solid #b8daff;
        }

        .product-details {
            margin-bottom: 20px;
        }

        .product-details h3 {
            text-align: center;
            margin-bottom: 15px;
            font-size: 1.5rem;
            color: #333;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .product-item {
            background: #f9f9f9;
            padding: 10px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .product-item img {
            width: 100%;
            max-height: 150px;
            object-fit: contain;
            margin-bottom: 10px;
        }

        form {
            margin-top: 20px;
        }

        form label {
            display: block;
            margin: 10px 0 5px;
            font-weight: bold;
        }

        form input {
            font-size: 0.9rem;
            padding: 8px;
            margin-bottom: 15px;
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        form input:focus {
            border-color: #2f6b29;
            outline: none;
            box-shadow: 0 0 5px rgba(47, 107, 41, 0.3);
        }

        form .input-group {
            margin-bottom: 15px;
        }

        form .error-message {
            color: #dc3545;
            font-size: 0.85rem;
            margin-top: -10px;
            margin-bottom: 10px;
            display: none;
        }

        form button {
            font-size: 0.9rem;
            padding: 10px;
            background-color: #2f6b29;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        form button:hover {
            background-color: #5d8c56;
        }

        .payment-methods {
            margin: 20px 0;
            text-align: center;
        }

        .payment-methods .payment-title {
            font-size: 1rem;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .payment-methods .methods {
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        .payment-methods .method {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .payment-methods .method:hover {
            border-color: #10b981;
            background-color: #f0fdf4;
        }

        .payment-methods .method input[type="radio"] {
            display: none;
        }

        .payment-methods .method img {
            width: 50px;
            height: auto;
            margin-bottom: 5px;
            filter: grayscale(100%);
            opacity: 0.7;
            transition: all 0.3s ease;
        }

        .payment-methods .method input[type="radio"]:checked + img {
            filter: grayscale(0);
            opacity: 1;
        }

        .payment-methods .method span {
            font-size: 0.9rem;
            color: #333;
        }

        form label {
            font-size: 0.9rem;
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
        }

        form input {
            font-size: 0.9rem;
            padding: 8px;
            margin-bottom: 15px;
        }

        form button {
            font-size: 0.9rem;
            padding: 10px;
            background-color: #2f6b29;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            text-align: center;
            
        }

        form button:hover {
            background-color: #5d8c56;
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            backdrop-filter: blur(3px);
        }

        .loading-content {
            text-align: center;
            color: white;
            background-color: rgba(0, 0, 0, 0.6);
            padding: 30px;
            border-radius: 10px;
            max-width: 80%;
        }

        .spinner {
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top: 4px solid white;
            width: 40px;
            height: 40px;
            margin: 0 auto 20px;
            animation: spin 1s linear infinite;
        }

        #confirmationModal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            max-width: 500px;
            width: 90%;
        }

        .modal-title {
            margin-top: 0;
        }

        .modal-footer {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .modal-button {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .confirm-button {
            background-color: #2f6b29;
            color: white;
        }

        .cancel-button {
            background-color: #f4f4f4;
            color: #333;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @media (max-width: 480px) {
            .container {
                margin: 20px auto;
                padding: 15px;
            }
        }

        /* Additional styles for product grid */
        .product-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            width: 100%;
        }

        .product-item {
            background-color: #f9f9f9;
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .product-item img {
            width: 100%;
            height: auto;
            border-radius: 5px;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .product-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <?php if ($message && $status): ?>
        <div class="message <?php echo $status; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <!-- Product Details Section -->
    <div class="product-details">
        <h3>Product Details</h3>
        <div class="product-grid">
            <div class="product-item">
                <img src="<?php echo $productImage; ?>" alt="<?php echo $productName; ?>">
            </div>
            <div class="product-item">
                <p><strong>Product Name:</strong> <?php echo $productName; ?></p>
                <p><strong>Unit Price:</strong> KSh <?php echo number_format($productPrice, 2); ?></p>
            </div>
            <div class="product-item">
                <p><strong>Color:</strong> <?php echo $productColor; ?></p>
                <p><strong>Quantity:</strong> <?php echo $productQuantity; ?></p>
            </div>
            <div class="product-item">
                <p><strong>Total:</strong> KSh <?php echo number_format($totalPrice, 2); ?></p>
            </div>
        </div>
    </div>

    <!-- Payment Methods Section -->
    <div class="payment-methods">
        <p class="payment-title">Select Payment Method</p>
        <div class="methods">
            <label class="method">
                <input type="radio" name="payment_method" value="MPESA" onclick="showPaymentMethod('mpesa')">
                <img src="../images/M-PESA-logo-2.png" alt="MPESA" title="MPESA">
                <span>MPESA</span>
            </label>
            <label class="method">
                <input type="radio" name="payment_method" value="Credit Card" onclick="showPaymentMethod('credit_card')">
                <img src="../images/credit.jpg" alt="Credit Card" title="Credit Card">
                <span>Credit Card</span>
            </label>
        </div>
    </div>

    <!-- Payment Form -->
    <form id="paymentForm" action="../modules/process_payment.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        <input type="hidden" name="product_name" value="<?php echo $productName; ?>">
        <input type="hidden" name="product_price" value="<?php echo $productPrice; ?>">
        <input type="hidden" name="product_image" value="<?php echo $productImage; ?>">
        <input type="hidden" name="product_color" value="<?php echo $productColor; ?>">
        <input type="hidden" name="product_quantity" value="<?php echo $productQuantity; ?>">

        <!-- MPESA Payment Inputs -->
        <div id="mpesaInputs" style="display: none;">
            <label for="full_name">Full Name (as it appears on MPESA):</label>
            <input type="text" id="full_name" name="full_name" required minlength="3">
            <div id="fullNameError" class="error-message" style="display:none;">Please enter your full name (at least 3 characters).</div>

            <label for="mpesa_number">MPESA Number:</label>
            <input type="tel" id="mpesa_number" name="mpesa_number" maxlength="10" pattern="^0[7-9][0-9]{8}$" required>
            <div id="mpesaNumberError" class="error-message" style="display:none;">Please enter a valid MPESA number (e.g. 07XXXXXXXX).</div>
        </div>

        <!-- Credit Card Placeholder -->
        <div id="creditCardInputs" style="display: none;">
            <p style="text-align: center; color: #888;">Credit Card payment is coming soon!</p>
        </div>

        <button type="submit" style="display:none;" id="payBtn">Pay KSh <?php echo number_format($totalPrice, 2); ?> Now</button>
    </form>
</div>

<!-- Confirmation Modal -->
<div id="confirmationModal">
    <div class="modal-content">
        <h3 class="modal-title">Confirm Payment</h3>
        <p>Are you sure you want to proceed with the payment?</p>
        <div id="paymentDetails">
            <p><strong>Product:</strong> <span id="confirmProductName"></span></p>
            <p><strong>Price:</strong> KSh <span id="confirmPrice"></span></p>
            <p><strong>MPESA Number:</strong> <span id="confirmMpesaNumber"></span></p>
        </div>
        <div class="modal-footer">
            <button class="modal-button cancel-button" id="cancelPayment">Cancel</button>
            <button class="modal-button confirm-button" id="confirmPayment">Confirm Payment</button>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="loading-overlay" style="display: none;">
    <div class="loading-content">
        <div class="spinner"></div>
        <p id="loadingMessage">Processing your payment. Please wait...</p>
        <p id="instructionMessage">You will receive an MPESA prompt shortly. Please enter your PIN to complete the transaction.</p>
    </div>
</div>

<script>
    // Form validation and submission handling
    const form = document.getElementById('paymentForm');
    const loadingOverlay = document.getElementById('loadingOverlay');
    const confirmationModal = document.getElementById('confirmationModal');
    const fullNameInput = document.getElementById('full_name');
    const mpesaNumberInput = document.getElementById('mpesa_number');
    const fullNameError = document.getElementById('fullNameError');
    const mpesaNumberError = document.getElementById('mpesaNumberError');
    
    // Confirmation modal elements
    const confirmProductName = document.getElementById('confirmProductName');
    const confirmPrice = document.getElementById('confirmPrice');
    const confirmMpesaNumber = document.getElementById('confirmMpesaNumber');
    const cancelPaymentBtn = document.getElementById('cancelPayment');
    const confirmPaymentBtn = document.getElementById('confirmPayment');

    // Event listeners for real-time validation
    fullNameInput.addEventListener('input', validateFullName);
    mpesaNumberInput.addEventListener('input', validateMpesaNumber);

    // Validate full name
    function validateFullName() {
        if (fullNameInput.value.length < 3) {
            fullNameError.style.display = 'block';
            fullNameInput.classList.add('invalid');
            return false;
        } else {
            fullNameError.style.display = 'none';
            fullNameInput.classList.remove('invalid');
            return true;
        }
    }

    // Validate MPESA number
    function validateMpesaNumber() {
        const mpesaRegex = /^0[7-9][0-9]{8}$/;
        if (!mpesaRegex.test(mpesaNumberInput.value)) {
            mpesaNumberError.style.display = 'block';
            mpesaNumberInput.classList.add('invalid');
            return false;
        } else {
            mpesaNumberError.style.display = 'none';
            mpesaNumberInput.classList.remove('invalid');
            return true;
        }
    }

    // Format MPESA number as user types
    mpesaNumberInput.addEventListener('input', function(e) {
        // Remove all non-digits
        let value = e.target.value.replace(/\D/g, '');
        
        // Ensure it starts with 0
        if (value.length > 0 && value[0] !== '0') {
            value = '0' + value;
        }
        
        // Limit to 10 digits
        if (value.length > 10) {
            value = value.slice(0, 10);
        }
        
        // Update the input value
        e.target.value = value;
    });

    // Show confirmation modal instead of direct form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate form before showing confirmation
        const isFullNameValid = validateFullName();
        const isMpesaNumberValid = validateMpesaNumber();
        
        if (isFullNameValid && isMpesaNumberValid) {
            // Populate confirmation modal with form values
            confirmProductName.textContent = '<?php echo $productName; ?>';
            confirmPrice.textContent = '<?php echo number_format($totalPrice, 2); ?>';
            confirmMpesaNumber.textContent = mpesaNumberInput.value;
            
            // Show confirmation modal
            confirmationModal.style.display = 'flex';
        }
    });

    // Cancel payment button in confirmation modal
    cancelPaymentBtn.addEventListener('click', function() {
        confirmationModal.style.display = 'none';
    });

    // Confirm payment button in confirmation modal
    confirmPaymentBtn.addEventListener('click', function() {
        confirmationModal.style.display = 'none';
        loadingOverlay.style.display = 'flex';
        
        // Submit the form
        form.submit();
    });

    // Status message handling
    <?php if ($status && $message): ?>
    setTimeout(() => {
        const messageElement = document.querySelector('.message');
        if (messageElement) {
            messageElement.style.opacity = '0.7';
            setTimeout(() => {
                messageElement.style.display = 'none';
            }, 500);
        }
    }, 5000);
    <?php endif; ?>

    // Update hidden inputs for color and quantity
    const productColor = document.getElementById('productColor');
    const productQuantity = document.getElementById('productQuantity');
    const selectedColorInput = document.getElementById('selected_color');
    const selectedQuantityInput = document.getElementById('selected_quantity');

    productColor.addEventListener('change', function () {
        selectedColorInput.value = this.value;
    });

    productQuantity.addEventListener('input', function () {
        selectedQuantityInput.value = this.value;
    });

    // Show payment method inputs dynamically
    function showPaymentMethod(method) {
        const mpesaInputs = document.getElementById('mpesaInputs');
        const creditCardInputs = document.getElementById('creditCardInputs');

        if (method === 'mpesa') {
            mpesaInputs.style.display = 'block';
            creditCardInputs.style.display = 'none';
        } else if (method === 'credit_card') {
            mpesaInputs.style.display = 'none';
            creditCardInputs.style.display = 'block';
        }
    }

    // Hide payment fields and button by default
    document.getElementById('mpesaInputs').style.display = 'none';
    document.getElementById('creditCardInputs').style.display = 'none';
    document.getElementById('payBtn').style.display = 'none';

    // Show relevant payment fields when method is selected
    function showPaymentMethod(method) {
        document.getElementById('mpesaInputs').style.display = (method === 'mpesa') ? 'block' : 'none';
        document.getElementById('creditCardInputs').style.display = (method === 'credit_card') ? 'block' : 'none';
        document.getElementById('payBtn').style.display = 'block';
    }
</script>

</body>
</html>