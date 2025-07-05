<?php
require_once __DIR__ . '/db_connect.php';
$res = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
$products = [];
while ($row = $res->fetch_assoc()) $products[] = $row;

$productRatings = [];
$productLoves = [];
$productIds = array_column($products, 'id');
if (count($productIds)) {
    $ids = implode(',', array_map('intval', $productIds));
    // Ratings
    $res = $conn->query("SELECT product_id, AVG(rating) as avg_rating, COUNT(*) as total_ratings FROM product_ratings WHERE product_id IN ($ids) GROUP BY product_id");
    while ($row = $res->fetch_assoc()) {
        $productRatings[$row['product_id']] = $row;
    }
    // Loves
    $res = $conn->query("SELECT product_id, COUNT(*) as total_loves FROM product_loves WHERE product_id IN ($ids) GROUP BY product_id");
    while ($row = $res->fetch_assoc()) {
        $productLoves[$row['product_id']] = $row['total_loves'];
    }
}

$selectedCategory = isset($_GET['category']) ? strtolower(trim($_GET['category'])) : '';
if ($selectedCategory) {
    $products = array_filter($products, function ($p) use ($selectedCategory) {
        return strtolower($p['category']) === $selectedCategory;
    });
    // Re-index array for clean foreach
    $products = array_values($products);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="icon" type="image/png" href="../images/logo-removebg-preview (1).png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Shop | EcoNest</title>
    <style>
        /* Global styles */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            background-color: #f4f4f4;
            display: flex;
            flex-direction: column;
        }

        header {
            background-color: #2f6b29;
            color: white;
            padding: 1rem;
            text-align: center;
        }

        header nav a {
            color: white;
            margin: 0 15px;
            text-decoration: none;
            font-size: 0.6em;
            /* Match index.html font size */
            transition: color 0.2s;
        }

        .shop-container {
            display: flex;
            max-width: 1200px;
            margin: 20px auto;
            gap: 20px;
        }

        /* Sidebar styles */
        .sidebar {
            width: 200px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 20px;
            height: fit-content;
        }

        .sidebar h3 {
            font-size: 1.2rem;
            margin-bottom: 10px;
            color: #2f6b29;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
                                                                                                                                                                                                                                                                }

        .sidebar ul li {
            margin-bottom: 8px;
        }

        .sidebar ul li a {
            text-decoration: none;
            color: #2f6b29;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }

        .sidebar ul li a:hover {
            color: #5d8c56;
        }

        /* Product grid styles */
        .product-grid {
            flex: 1;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            /* 3 products per row */
            gap: 20px;
        }

        .product-card {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
        }

        .product-card img {
            width: 100%;
            height: auto;
            border-radius: 8px;
            max-height: 180px;
            object-fit: contain;
        }

        .product-card h3 {
            font-size: 1rem;
            margin-top: 10px;
            text-align: center;
        }

        .product-card p {
            font-size: 1rem;
            color: #2f6b29;
            margin: 8px 0;
        }

        .product-card .btn {
            padding: 8px 15px;
            background-color: #2f6b29;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 0.9rem;
            margin-top: auto;
            transition: background-color 0.3s ease;
        }

        .product-card .btn:hover {
            background-color: #5d8c56;
        }

        .no-products-message {
            text-align: center;
            color: red;
            font-size: 1.2rem;
            margin-top: 20px;
        }

        footer {
            background-color: #2f6b29;
            color: white;
            text-align: center;
            padding: 20px;
            font-size: 1rem;
            margin-top: 40px;
        }

        /* Responsive styles */
        @media (max-width: 768px) {
            .shop-container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                margin-bottom: 20px;
            }

            .product-grid {
                grid-template-columns: repeat(2, 1fr);
                /* 2 products per row on smaller screens */
            }
        }

        @media (max-width: 480px) {
            .product-grid {
                grid-template-columns: repeat(1, 1fr);
                /* 1 product per row on very small screens */
            }
        }

        /* Modal styles */
        .modal {
            display: none;
            /* Hidden by default */
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
            /* Black background with opacity */
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: #fff;
            margin: auto;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 800px;
            text-align: left;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            animation: fadeIn 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        nav a:hover {
            color: #FFD700;
        }

        .modal-body {
            display: flex;
            gap: 20px;
        }

        .modal-image {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .modal-image img {
            width: 100%;
            max-width: 300px;
            height: auto;
            border-radius: 8px;
            object-fit: contain;
        }

        .modal-details {
            flex: 2;
            display: flex;
            flex-direction: column;
        }

        .modal-details h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .modal-details p {
            font-size: 1rem;
            margin: 5px 0;
        }

        .modal-details label {
            font-size: 1rem;
            margin: 10px 0 5px;
        }

        .modal-details select,
        .modal-details input[type="number"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }

        .modal-details select:focus,
        .modal-details input[type="number"]:focus {
            outline: none;
            border-color: #2f6b29;
            box-shadow: 0 0 5px rgba(47, 107, 41, 0.5);
        }

        .modal-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .modal-buttons .btn {
            padding: 10px 20px;
            background-color: #2f6b29;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 1rem;
            transition: background-color 0.3s ease;
            cursor: pointer;
        }

        .modal-buttons .btn:hover {
            background-color: #5d8c56;
        }

        .modal .close {
            position: absolute;
            top: 10px;
            right: 20px;
            font-size: 1.5rem;
            color: #333;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .modal .close:hover {
            color: #000;
        }

        /* New styles for modal layout */
        .modal-body {
            display: flex;
            flex-direction: row;
            gap: 20px;
        }

        .modal-image {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .modal-image img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
        }

        .modal-details {
            flex: 2;
            display: flex;
            flex-direction: column;
        }

        .modal-details h3 {
            font-size: 1.5rem;
            margin: 0 0 10px;
            color: #2f6b29;
        }

        .modal-details p {
            font-size: 1rem;
            margin: 5px 0;
            color: #333;
        }

        .modal-buttons {
            margin-top: auto;
            display: flex;
            gap: 10px;
        }

        .star-rating .star {
            color: #ccc;
            font-size: 1.2em;
            cursor: pointer;
            transition: color 0.2s;
        }

        .star-rating .star.selected {
            color: #FFD700;
        }

        .love-btn {
            color: #bbb;
            transition: color 0.2s;
        }

        .love-btn.loved,
        .love-btn:hover {
            color: #e74c3c !important;
        }
    </style>
</head>

<body>


    <header>
        <h1 style="margin:0;display:flex;align-items:center;justify-content:center;gap:8px;">
            EcoNest
            <nav style="flex:1;display:flex;justify-content:center;gap:3px;align-items:center;">
                <a href="../pages/index.html">Home</a>
                <a href="../modules/account.php">My Account</a>
                <a href="cart.php" id="cartLink" style="position:relative;display:flex;align-items:center;gap:6px;">
                    <i class="fa fa-shopping-cart" style="font-size:1em;"></i>
                    <span id="cartCount" style="background:#e74c3c;color:#fff;padding:2px 8px;border-radius:50%;font-size:0.9em;position:absolute;top:-10px;right:-14px;">0</span>
                </a>
            </nav>
        </h1>
    </header>

    <section class="shop-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <h3>Categories</h3>
            <ul>
                <li><a href="#" data-category="bamboo">Bamboo Products</a></li>
                <li><a href="#" data-category="beeswax">Beeswax Products</a></li>
                <li><a href="#" data-category="reusable">Reusable Products</a></li>
                <li><a href="#" data-category="compostable">Compostable Products</a></li>
                <li><a href="#" data-category="upcycled">Upcycled Products</a></li>
                <li><a href="#" data-category="skincare">Natural Skincare</a></li>
                <li><a href="#" data-category="cleaning">Eco-Friendly Cleaning</a></li>
                <li><a href="#" data-category="fashion">Sustainable Fashion</a></li>
                <li><a href="#" data-category="gardening">Gardening and Outdoor</a></li>
                <li><a href="#" data-category="home">Home and Kitchen</a></li>
            </ul>
        </aside>

        <!-- Product Grid -->
        <div class="product-grid" id="productGrid">
            <?php if (count($products)): ?>
                <?php foreach ($products as $p): ?>
                    <div class="product-card"
                        data-category="<?php echo htmlspecialchars(strtolower($p['category'])); ?>"
                        data-description="<?php echo htmlspecialchars($p['description']); ?>">
                        <img src="../images/<?php echo htmlspecialchars($p['image']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>" />
                        <h3><?php echo htmlspecialchars($p['name']); ?></h3>
                        <div class="product-rating-love" style="margin:8px 0;">
                            <!-- Show average rating -->
                            <span class="star-rating" data-product="<?php echo $p['id']; ?>">
                                <?php
                                $avg = isset($productRatings[$p['id']]['avg_rating']) ? round($productRatings[$p['id']]['avg_rating'], 1) : 0;
                                $total = isset($productRatings[$p['id']]['total_ratings']) ? $productRatings[$p['id']]['total_ratings'] : 0;
                                for ($i = 1; $i <= 5; $i++) {
                                    echo '<i class="star' . ($i <= round($avg) ? ' selected' : '') . '" data-value="' . $i . '">&#9733;</i>';
                                }
                                ?>
                                <span style="font-size:0.95em;color:#888;">(<?php echo $avg; ?>/5, <?php echo $total; ?>)</span>
                            </span>
                            <!-- Show love count -->
                            <span class="love-btn" data-product="<?php echo $p['id']; ?>" style="cursor:pointer;font-size:1.3em;color:#bbb;margin-left:12px;">
                                &#10084; <span class="love-count"><?php echo isset($productLoves[$p['id']]) ? $productLoves[$p['id']] : 0; ?></span>
                            </span>
                        </div>
                        <p>KSh <?php echo htmlspecialchars($p['price']); ?></p>
                        <button type="button" class="btn view-details-btn">Details</button>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-products-message" id="noProductsMessage">
                    Sorry, no products found.
                </div>
            <?php endif; ?>
        </div>
        <div class="no-products-message" id="noProductsMessage" style="display:none;">
            Sorry, no products match your filters.
        </div>
    </section>

    <!-- Product Details Modal -->
    <div id="productModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div class="modal-body">
                <!-- Left Section: Product Image with 3D tilt effect -->
                <div class="modal-image" data-tilt data-tilt-max="15" data-tilt-speed="400">
                    <img id="modalImage" src="" alt="Product Image" />
                </div>

                <!-- Right Section: Product Details -->
                <div class="modal-details">
                    <h3 id="modalTitle"></h3>
                    <p id="modalPrice" style="color: #2f6b29; font-weight: bold;"></p>
                    <p id="modalDescription" style="margin: 10px 0;"></p>

                    <!-- Color Selection -->
                    <label for="productColor">Select Color:</label>
                    <select id="productColor">
                        <option value="Default">Default</option>
                        <option value="Red">Red</option>
                        <option value="Blue">Blue</option>
                        <option value="Green">Green</option>
                        <option value="Black">Black</option>
                        <option value="White">White</option>
                    </select>
                    <input type="hidden" id="selected_color" name="product_color" value="Default">

                    <!-- Quantity Selection -->
                    <label for="productQuantity">Quantity:</label>
                    <input type="number" id="productQuantity" min="1" value="1" />

                    <!-- Buttons -->
                    <div class="modal-buttons">
                        <button id="addToCartButton" class="btn">Add to Cart</button>
                        <form id="shopNowForm" action="shop.php" method="POST" style="display:inline;">
                            <input type="hidden" name="product_name" id="modalProductName">
                            <input type="hidden" name="product_price" id="modalProductPrice">
                            <input type="hidden" name="product_image" id="modalProductImage">
                            <input type="hidden" name="product_color" id="modalProductColor">
                            <input type="hidden" name="product_quantity" id="modalProductQuantity">
                            <button type="submit" class="btn" id="shopNowSubmitBtn">Shop Now</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="cartNotification" style="display:none;position:fixed;top:30px;left:50%;transform:translateX(-50%);background-color: #ddd;color:#c94723;padding:16px 32px;border-radius:8px;box-shadow:0 4px 20px rgba(0,0,0,0.12);z-index:2000;font-size:1.1em;font-weight:bold;">
        <!-- Message will be set by JS -->
    </div>

    <footer>
        <p>&copy; 2025 EcoNest. All rights reserved.</p>
    </footer>

    <script>
        // Get modal elements
        const modal = document.getElementById('productModal');
        const modalImage = document.getElementById('modalImage');
        const modalTitle = document.getElementById('modalTitle');
        const modalPrice = document.getElementById('modalPrice');
        const modalDescription = document.getElementById('modalDescription');
        const productColor = document.getElementById('productColor');
        const productQuantity = document.getElementById('productQuantity');
        const addToCartButton = document.getElementById('addToCartButton');
        const closeModal = document.querySelector('.modal .close');

        // For the new Shop Now form
        const shopNowForm = document.getElementById('shopNowForm');
        const modalProductName = document.getElementById('modalProductName');
        const modalProductPrice = document.getElementById('modalProductPrice');
        const modalProductImage = document.getElementById('modalProductImage');
        const modalProductColor = document.getElementById('modalProductColor');
        const modalProductQuantity = document.getElementById('modalProductQuantity');
        const shopNowSubmitBtn = document.getElementById('shopNowSubmitBtn');

        // Cart array to store selected products
        let cart = JSON.parse(localStorage.getItem('cart')) || [];

        // Add event listeners to "View Details" buttons
        document.querySelectorAll('.product-card').forEach(card => {
            card.querySelector('.view-details-btn').addEventListener('click', function(e) {
                e.preventDefault();

                // Populate modal with product details
                const imageSrc = card.querySelector('img').src;
                const title = card.querySelector('h3').textContent;
                const priceText = card.querySelector('p').textContent;
                const price = parseFloat(priceText.replace(/[^\d.]/g, ''));
                const description = card.getAttribute('data-description') || '';

                modalImage.src = imageSrc;
                modalTitle.textContent = title;
                modalPrice.textContent = `KSh ${price}`;
                modalDescription.textContent = description;

                // Reset color and quantity to default values
                productColor.value = "Default";
                productQuantity.value = 1;

                // Set hidden form values for Shop Now
                modalProductName.value = title;
                modalProductPrice.value = price;
                modalProductImage.value = imageSrc;
                modalProductColor.value = productColor.value;
                modalProductQuantity.value = productQuantity.value;

                // Update the Shop Now button text to show correct total
                shopNowSubmitBtn.textContent = `Shop Now (KSh ${price})`;

                // Update color/quantity on change
                productColor.onchange = function() {
                    modalProductColor.value = this.value;
                };
                productQuantity.oninput = function() {
                    modalProductQuantity.value = this.value;
                    // Update button text with correct total
                    const total = price * parseInt(this.value || 1);
                    shopNowSubmitBtn.textContent = `Shop Now (KSh ${total})`;
                };

                // Add product details to the "Add to Cart" button
                addToCartButton.dataset.name = title;
                addToCartButton.dataset.price = price;
                addToCartButton.dataset.image = imageSrc;

                // Show the modal
                modal.style.display = 'flex'; // Use flex to center the modal
            });
        });

        // Handle "Add to Cart" button click
        addToCartButton.addEventListener('click', function() {
            const product = {
                name: this.dataset.name,
                price: this.dataset.price,
                image: this.dataset.image,
                color: productColor.value, // Get selected color
                quantity: parseInt(productQuantity.value) // Get selected quantity
            };

            // Add product to the cart array
            cart.push(product);

            // Save the cart to local storage
            localStorage.setItem('cart', JSON.stringify(cart));

            // Notify the user
            showCartNotification(`${product.quantity} x ${product.name} (${product.color}) has been added to your cart.`);
            updateCartCount();
        });

        // Close the modal
        closeModal.addEventListener('click', () => {
            modal.style.display = 'none';
        });

        // Close modal when clicking outside of it
        window.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });

        function updateCartCount() {
            const cart = JSON.parse(localStorage.getItem('cart')) || [];
            const badge = document.getElementById('cartCount');
            if (badge) badge.textContent = cart.length;
        }
        // Initial update
        updateCartCount();
    </script>

    <script>
        function getQueryParam(name) {
            const url = new URL(window.location.href);
            return url.searchParams.get(name);
        }
        if (getQueryParam('login_required')) {
            // Show styled message
            const msg = document.createElement('div');
            msg.style = "position:fixed;top:30px;left:50%;transform:translateX(-50%);background:#fff;color:#2f6b29;border:2px solid #e74c3c;padding:18px 30px;border-radius:8px;box-shadow:0 4px 20px rgba(0,0,0,0.12);z-index:2000;font-size:1.1em;";
            msg.innerHTML = "<b>Login Required</b><br>You must be logged in to proceed with your purchase.<br>Redirecting to login...";
            document.body.appendChild(msg);
            setTimeout(function() {
                window.location.href = "login.php";
            }, 5000);
        }
    </script>

    <script>
        function showCartNotification(msg) {
            const notif = document.getElementById('cartNotification');
            notif.textContent = msg;
            notif.style.display = 'block';
            setTimeout(() => {
                notif.style.display = 'none';
            }, 3000); // Hide after 3 seconds
        }
    </script>

    <script>
        document.querySelectorAll('.star-rating .star').forEach(function(star) {
            star.addEventListener('click', function() {
                const productId = this.closest('.star-rating').getAttribute('data-product');
                const rating = this.getAttribute('data-value');
                fetch('rate_product.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: 'product_id=' + encodeURIComponent(productId) + '&rating=' + encodeURIComponent(rating)
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) location.reload(); // reload to update average
                        else alert(data.message || 'Failed to rate');
                    });
            });
        });

        document.querySelectorAll('.love-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const productId = this.getAttribute('data-product');
                fetch('love_product.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: 'product_id=' + encodeURIComponent(productId)
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) location.reload(); // reload to update love count
                        else alert(data.message || 'Failed to love');
                    });
            });
        });
    </script>

    <!-- Add Vanilla Tilt at the end of your file -->
    <script src="https://cdn.jsdelivr.net/npm/vanilla-tilt@1.8.0/dist/vanilla-tilt.min.js"></script>
    <script>
        (function() {
            const productGrid = document.getElementById('productGrid');
            const noProductsMessage = document.getElementById('noProductsMessage');
            const productCards = Array.from(productGrid.getElementsByClassName('product-card'));
            const categoryLinks = document.querySelectorAll('.sidebar ul li a');

            function filterProductsByCategory(category) {
                let visibleCount = 0;
                productCards.forEach(card => {
                    const productCategory = card.getAttribute('data-category');
                    if (category === '' || category === productCategory) {
                        card.style.display = '';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });
                noProductsMessage.style.display = visibleCount === 0 ? 'block' : 'none';

                // Highlight selected category in sidebar
                categoryLinks.forEach(link => {
                    if (link.getAttribute('data-category') === category) {
                        link.style.fontWeight = 'bold';
                        link.style.color = '#FFD700';
                    } else {
                        link.style.fontWeight = '';
                        link.style.color = '';
                    }
                });
            }

            // Add click event listeners to category links
            categoryLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const category = this.getAttribute('data-category');
                    // Update URL without reloading
                    const url = new URL(window.location);
                    url.searchParams.set('category', category);
                    window.history.replaceState({}, '', url);
                    filterProductsByCategory(category);
                });
            });

            // On page load, filter by category from URL if present
            function getCategoryFromUrl() {
                const params = new URLSearchParams(window.location.search);
                return params.get('category') ? params.get('category').toLowerCase() : '';
            }
            filterProductsByCategory(getCategoryFromUrl());
        })();
    </script>

</body>

</html>