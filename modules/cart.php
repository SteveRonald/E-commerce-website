<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location:../modules/login.php?redirect=cart.php&msg=Please login or register before checking out.");
    exit();
}

// Handle messages (from ?msg= or session)
$msg = '';
if (!empty($_GET['msg'])) {
    $msg = htmlspecialchars($_GET['msg']);
} elseif (!empty($_SESSION['cart_msg'])) {
    $msg = htmlspecialchars($_SESSION['cart_msg']);
    unset($_SESSION['cart_msg']);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" type="image/png" href="../images/logo.jpg">
    <title>Cart | EcoNest</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; }
        .container { max-width: 800px; margin: 30px auto; background: #fff; padding: 20px; border-radius: 8px; }
        h1 { color: #2f6b29; }
        .cart-item { display: flex; align-items: center; border-bottom: 1px solid #ddd; padding: 10px 0; }
        .cart-item img { width: 70px; height: 70px; object-fit: contain; border-radius: 8px; margin-right: 20px; }
        .cart-item-details { flex: 1; }
        .cart-item-remove { color: red; cursor: pointer; font-size: 1.2em; padding: 0 8px; }
        .cart-total { text-align: right; font-weight: bold; font-size: 1.2em; margin-top: 20px; }
        .empty-cart { color: #888; font-size: 1.1em; text-align: center; margin-top: 40px; }
        .checkout-btn { background: #2f6b29; color: #fff; border: none; padding: 12px 30px; border-radius: 5px; font-size: 1em; cursor: pointer; }
        .checkout-btn:hover { background: #5d8c56; }
        .msg {
            background: #e8f5e9;
            color: #25611f;
            border: 1px solid #c8e6c9;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 18px;
            text-align: center;
            font-weight: 500;
            font-size: 1.08em;
            display: none;
        }
        .msg.error {
            background: #fbeaea;
            color: #e74c3c;
            border: 1px solid #e74c3c;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Your Cart</h1>
        <div id="cartMsg" class="msg<?php echo (isset($_GET['error']) || isset($_SESSION['cart_error'])) ? ' error' : ''; ?>">
            <?php echo $msg; ?>
        </div>
        <div id="cartItems"></div>
        <div class="cart-total" id="cartTotal"></div>
        <div class="empty-cart" id="emptyCart" style="display:none;">Your cart is empty.</div>
        <button class="checkout-btn" id="checkoutBtn" style="display:none;">Checkout</button>
        <button class="checkout-btn" id="clearCartBtn" style="background:#e74c3c;display:none;margin-left:10px;">Clear Cart</button>

        <!-- Checkout Modal -->
        <div id="checkoutModal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.35);z-index:1000;align-items:center;justify-content:center;">
            <div style="background:#fff;padding:28px 24px 18px 24px;border-radius:10px;max-width:350px;width:90%;margin:auto;box-shadow:0 8px 32px rgba(0,0,0,0.18);position:relative;">
                <h2 style="color:#2f6b29;text-align:center;margin-bottom:18px;">Checkout</h2>
                <form id="checkoutForm">
                    <label style="font-weight:600;color:#2f6b29;">Full Name</label>
                    <input type="text" id="modalFullName" style="width:100%;padding:10px;margin:8px 0 16px 0;border:1px solid #c8e6c9;border-radius:6px;font-size:1rem;" required>
                    <label style="font-weight:600;color:#2f6b29;">MPESA Number</label>
                    <input type="text" id="modalMpesaNumber" style="width:100%;padding:10px;margin:8px 0 16px 0;border:1px solid #c8e6c9;border-radius:6px;font-size:1rem;" required placeholder="07XXXXXXXX">
                    <div id="modalError" style="color:#e74c3c;text-align:center;margin-bottom:10px;display:none;"></div>
                    <button type="submit" class="checkout-btn" style="width:100%;margin-bottom:8px;">Confirm & Pay</button>
                    <button type="button" id="closeModalBtn" style="width:100%;background-color:#e74c3c;margin: bottom 8%;border-radius :5px;padding: 6px;border-color:none">Cancel</button>
                </form>
            </div>
        </div>
    </div>
    <form id="cartCheckoutForm" action="../modules/process_payment.php" method="POST" style="display:none;">
        <input type="hidden" name="cart_data" id="cartDataInput">
        <input type="hidden" name="full_name" id="cartFullName">
        <input type="hidden" name="mpesa_number" id="cartMpesaNumber">
    </form>
    <script>
    window.addEventListener('DOMContentLoaded', function() {
        var msgDiv = document.getElementById('cartMsg');
        if (msgDiv && msgDiv.textContent.trim() !== "") {
            msgDiv.style.display = '';
            setTimeout(function() {
                msgDiv.style.display = 'none';
            }, 4000);
        }
    });

    document.getElementById('checkoutBtn').onclick = function(e) {
        e.preventDefault();
        const cart = JSON.parse(localStorage.getItem('cart')) || [];
        if(cart.length === 0) {
            showMsg("Your cart is empty.", true);
            return;
        }
        document.getElementById('checkoutModal').style.display = 'flex';
        document.getElementById('modalFullName').value = '';
        document.getElementById('modalMpesaNumber').value = '';
        document.getElementById('modalError').style.display = 'none';
    };

    document.getElementById('closeModalBtn').onclick = function() {
        document.getElementById('checkoutModal').style.display = 'none';
    };

    document.getElementById('checkoutForm').onsubmit = function(e) {
        e.preventDefault();
        const fullName = document.getElementById('modalFullName').value.trim();
        const mpesaNumber = document.getElementById('modalMpesaNumber').value.trim();
        const errorDiv = document.getElementById('modalError');
        if(!fullName || fullName.length < 3) {
            errorDiv.textContent = "Please enter a valid name.";
            errorDiv.style.display = '';
            return;
        }
        if(!/^0[7-9][0-9]{8}$/.test(mpesaNumber)) {
            errorDiv.textContent = "Please enter a valid MPESA number.";
            errorDiv.style.display = '';
            return;
        }
        errorDiv.style.display = 'none';
        // Submit the form
        const cart = JSON.parse(localStorage.getItem('cart')) || [];
        document.getElementById('cartDataInput').value = JSON.stringify(cart);
        document.getElementById('cartFullName').value = fullName;
        document.getElementById('cartMpesaNumber').value = mpesaNumber;
        document.getElementById('checkoutModal').style.display = 'none';
        document.getElementById('cartCheckoutForm').submit();
    };

    function showMsg(msg, isError) {
        var msgDiv = document.getElementById('cartMsg');
        msgDiv.textContent = msg;
        msgDiv.className = 'msg' + (isError ? ' error' : '');
        msgDiv.style.display = '';
        setTimeout(function() {
            msgDiv.style.display = 'none';
        }, 4000);
    }

    function renderCart() {
        const cart = JSON.parse(localStorage.getItem('cart')) || [];
        const cartItemsDiv = document.getElementById('cartItems');
        const cartTotalDiv = document.getElementById('cartTotal');
        const emptyCartDiv = document.getElementById('emptyCart');
        const checkoutBtn = document.getElementById('checkoutBtn');
        const clearCartBtn = document.getElementById('clearCartBtn');
        cartItemsDiv.innerHTML = '';
        let total = 0;
        if(cart.length === 0) {
            emptyCartDiv.style.display = '';
            checkoutBtn.style.display = 'none';
            cartTotalDiv.textContent = '';
            if(clearCartBtn) clearCartBtn.style.display = 'none';
            return;
        }
        emptyCartDiv.style.display = 'none';
        checkoutBtn.style.display = '';
        if(clearCartBtn) clearCartBtn.style.display = '';
        cart.forEach((item, idx) => {
            const itemTotal = item.price * item.quantity;
            total += itemTotal;
            cartItemsDiv.innerHTML += `
                <div class="cart-item">
                    <img src="${item.image}" alt="">
                    <div class="cart-item-details">
                        <div><b>${item.name}</b></div>
                        <div>Color: ${item.color || 'Default'}, Qty: ${item.quantity}</div>
                        <div>Price: KSh ${item.price} × ${item.quantity} = <b>KSh ${itemTotal}</b></div>
                    </div>
                    <span class="cart-item-remove" onclick="removeFromCart(${idx})">&times;</span>
                </div>
            `;
        });
        cartTotalDiv.textContent = `Total: KSh ${total}`;
    }
    function removeFromCart(idx) {
        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        cart.splice(idx,1);
        localStorage.setItem('cart', JSON.stringify(cart));
        renderCart();
        updateCartCount();
    }
    function updateCartCount() {
        const cart = JSON.parse(localStorage.getItem('cart')) || [];
        const badge = document.getElementById('cartCount');
        if (badge) badge.textContent = cart.length;
    }
    function clearCart() {
        localStorage.removeItem('cart');
        renderCart();
        updateCartCount();
        showMsg("Cart cleared.", false);
    }
    document.getElementById('clearCartBtn').onclick = clearCart;
    renderCart();
    updateCartCount();
    </script>
</body>
</html>