<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$conn = new mysqli("localhost", "root", "", "ecommerce");
$user_id = $_SESSION['user_id'];

// Handle profile picture upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
    $target_dir = "../uploads/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
    $file = $_FILES['profile_picture'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    if (in_array($ext, $allowed) && $file['size'] < 2 * 1024 * 1024) {
        $filename = "user_" . $user_id . "_" . time() . "." . $ext;
        $target_file = $target_dir . $filename;
        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            $conn->query("UPDATE users SET profile_picture='$target_file' WHERE id=$user_id");
        }
    }
    header("Location: account.php");
    exit();
}

// Handle name update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_name'])) {
    $new_name = trim($_POST['edit_name']);
    if ($new_name && strlen($new_name) >= 2) {
        $stmt = $conn->prepare("UPDATE users SET name=? WHERE id=?");
        $stmt->bind_param("si", $new_name, $user_id);
        $stmt->execute();
        $stmt->close();
        $_SESSION['user_name'] = $new_name;
        header("Location: account.php?msg=" . urlencode("Name updated!"));
        exit();
    }
}

// Handle email update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_email'])) {
    $new_email = trim($_POST['edit_email']);
    if (filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $stmt = $conn->prepare("UPDATE users SET email=? WHERE id=?");
        $stmt->bind_param("si", $new_email, $user_id);
        $stmt->execute();
        $stmt->close();
        header("Location: account.php?msg=" . urlencode("Email updated!"));
        exit();
    }
}

// Handle address update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_address'])) {
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $phone = trim($_POST['phone']);
    $stmt = $conn->prepare("UPDATE users SET address=?, city=?, phone=? WHERE id=?");
    $stmt->bind_param("sssi", $address, $city, $phone, $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: account.php?msg=" . urlencode("Address updated!"));
    exit();
}

// Handle order cancellation
if (isset($_GET['cancel_order'])) {
    $order_id = intval($_GET['cancel_order']);
    // Check if order belongs to user, is less than 24hrs old, and status is 'received'
    $stmt = $conn->prepare("SELECT id, created_at, order_status FROM transactions WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $stmt->bind_result($oid, $created_at, $order_status);
    if ($stmt->fetch()) {
        $order_time = strtotime($created_at);
    }
    $stmt->close(); // <-- Close before running another query!

    if (isset($order_status) && $order_status === 'received' && (time() - $order_time) < 86400) {
        $conn->query("DELETE FROM transactions WHERE id=$order_id");
        $msg = "Order cancelled.";
    } else {
        $msg = "Cannot cancel this order.";
    }
    header("Location: account.php?msg=" . urlencode($msg));
    exit();
}

// Get user info
$stmt = $conn->prepare("SELECT name, email, profile_picture, address, city, phone FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $email, $profile_picture, $address, $city, $phone);
$stmt->fetch();
$stmt->close();

// Get orders
$stmt = $conn->prepare("SELECT id, product_name, product_price, product_color, product_quantity, total_price, order_status, created_at FROM transactions WHERE user_id=? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($oid, $pname, $pprice, $pcolor, $pqty, $ptotal, $order_status, $created);
$orders = [];
while ($stmt->fetch()) {
    $orders[] = [$oid, $pname, $pprice, $pcolor, $pqty, $ptotal, $order_status, $created];
}
$stmt->close();
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account | EcoNest</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="icon" type="image/png" href="../images/logo.jpg">
    <style>
        html,
        body {
            height: 100%;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            ;
        }

        body {
            min-height: 100vh;
            height: 100vh;
            background: #f4f4f4;
            overflow: hidden;
            /* Prevent body scroll */
        }

        .account-wrapper {
            display: flex;
            height: 100vh;
            /* Fill the viewport */
            max-width: 1000vw;
            /* Prevent horizontal overflow */
            margin: 0;
            border-radius: 0;
            box-shadow: none;
            background: #fff;
        }

        .sidebar {
            width: 250px;
            background: #2f6b29;
            color: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 10;
        }

        .main-content {
            flex: 1;
            margin-left: 250px;
            /* Same as sidebar width */
            height: 100vh;
            /* Make it same height as sidebar/browser */
            min-height: 100vh;
            /* Ensure it always fills the viewport */
            overflow-y: auto;
            padding: 48px 48px 48px 48px;
            background: #fff;
            box-sizing: border-box;
        }

        /* ...rest of your styles... */
        .account-wrapper {
            display: flex;
            max-width: 1500px;
            margin: 40px auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.09);
            min-height: 650px;
            overflow: hidden;
        }

        .sidebar {
            width: 250px;
            background: #2f6b29;
            color: #fff;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .sidebar-profile {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 38px 0 22px 0;
            width: 100%;
            border-bottom: 1px solid #256021;
        }

        .sidebar-pic {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 3px solid #fff;
            margin-bottom: 10px;
            object-fit: cover;
        }

        .sidebar-name {
            font-weight: bold;
            font-size: 1.1em;
            margin-bottom: 6px;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
            width: 100%;
        }

        .sidebar-link {
            display: block;
            padding: 16px 38px;
            color: #fff;
            text-decoration: none;
            cursor: pointer;
            transition: background 0.2s;
            font-size: 1.07em;
            border-left: 4px solid transparent;
        }

        .sidebar-link.active,
        .sidebar-link:hover {
            background: #256021;
            border-left: 4px solid #fff;
        }

        .main-content {
            flex: 1;
            margin-left: 250px;
            /* Same as sidebar width */
            height: 100vh;
            /* Make it same height as sidebar/browser */
            min-height: 100vh;
            /* Ensure it always fills the viewport */
            overflow-y: auto;
            padding: 48px 48px 48px 48px;
            background: #fff;
            box-sizing: border-box;
        }

        .account-section {
            display: none;
        }

        .account-section.active {
            display: block;
        }

        .logout-btn {
            background: #e74c3c;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 8px 20px;
            font-size: 1rem;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.3s;
            /*margin: 32px 0 0 0;*/
            position: absolute;
            width: 80%;
            margin-left: 20px;
            margin-top: 20px;

        }

        .logout-btn:hover {
            background: #c0392b;
        }

        /* Profile section styles */
        .profile-section-flex {
            display: flex;
            align-items: flex-start;
            gap: 36px;
        }

        .profile-pic-lg {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #2f6b29;
            background: #e8f5e9;
        }

        .profile-details b {
            color: #2f6b29;
        }

        .upload-form {
            margin-top: 10px;
        }

        .upload-form input[type="file"] {
            margin-bottom: 8px;
        }

        .msg {
            background: #e8f5e9;
            color: #2f6b29;
            border: 1px solid #2f6b29;
            border-radius: 6px;
            padding: 10px 18px;
            margin-bottom: 18px;
            text-align: center;
        }

        /* Orders table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        th,
        td {
            padding: 10px 8px;
            border-bottom: 1px solid #e0e0e0;
            text-align: left;
        }

        th {
            background: #e8f5e9;
            color: #2f6b29;
        }

        tr:last-child td {
            border-bottom: none;
        }

        .no-orders {
            color: #888;
            text-align: center;
            margin: 30px 0;
        }

        .cancel-btn {
            background: #e74c3c;
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 6px 15px;
            cursor: pointer;
            font-size: 1em;
            transition: background 0.2s;
        }

        .cancel-btn:disabled {
            background: #ccc;
            color: #888;
            cursor: not-allowed;
        }

        .order-status {
            text-transform: capitalize;
            font-weight: bold;
        }

        .order-status.received {
            color: #e67e22;
        }

        .order-status.in_delivery {
            color: #2980b9;
        }

        .order-status.delivered {
            color: #27ae60;
        }
    </style>
</head>

<body>
    <div class="account-wrapper">
        <!-- Sidebar Navigation -->
        <div class="sidebar">
            <div class="sidebar-profile">
                <img src="<?php echo ($profile_picture && file_exists($profile_picture)) ? htmlspecialchars($profile_picture) . '?v=' . time() : '/images/default-avatar.png'; ?>" class="sidebar-pic" alt="Profile Picture">
                <div class="sidebar-name"><?php echo htmlspecialchars($name); ?></div>
            </div>
            <ul class="sidebar-menu">
                <li class="sidebar-link active" data-section="profile">Profile</li>
                <li class="sidebar-link" data-section="orders">Orders</li>
                <li class="sidebar-link" data-section="address">Address</li>
                <li><a href="../modules/user_logout.php" class="logout-btn" style="text-align:center;">Logout</a></li>
            </ul>
        </div>
        <!-- Main Content -->
        <div class="main-content">
            <?php if (!empty($_GET['msg'])): ?>
                <div class="msg" id="autoHideMsg"><?php echo htmlspecialchars($_GET['msg']); ?></div>
            <?php endif; ?>

            <!-- Profile Section -->
            <section id="profile-section" class="account-section active">
                <h2 style="margin-top:0;">My Profile</h2>
                <div class="profile-section-flex">
                    <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;">
                        <img src="<?php echo ($profile_picture && file_exists($profile_picture)) ? htmlspecialchars($profile_picture) . '?v=' . time() : '../images/default-avatar.png'; ?>" class="profile-pic-lg" alt="Profile Picture">
                        <form class="upload-form" method="POST" enctype="multipart/form-data" style="margin-top:10px;">
                            <label for="profile_picture" style="font-size:0.95em;"><b>Change Picture</b></label><br>
                            <input type="file" name="profile_picture" accept="image/*" required style="margin-bottom:7px;">
                            <button type="submit" style="background:#2f6b29;color:#fff;border:none;padding:6px 18px;border-radius:5px;cursor:pointer;font-size:0.95em;">Upload</button>
                        </form>
                    </div>
                    <div class="profile-details" style="flex:1;">
                        <form id="profileEditForm" method="POST" style="margin-bottom:0;">
                            <div style="display:flex;align-items:center;margin-bottom:10px;">
                                <b>Name:</b>
                                <span id="displayName" style="margin-left:8px;"><?php echo htmlspecialchars($name); ?></span>
                                <input type="text" name="edit_name" id="editNameInput" value="<?php echo htmlspecialchars($name); ?>" style="display:none;margin-left:8px;padding:4px 8px;border-radius:4px;border:1px solid #c8e6c9;">
                                <button type="button" id="editNameBtn" style="background:none;border:none;cursor:pointer;margin-left:6px;font-size:1.1em;" title="Edit Name">&#9998;</button>
                                <button type="submit" name="save_name" id="saveNameBtn" style="display:none;background:#2f6b29;color:#fff;border:none;padding:3px 12px;border-radius:4px;margin-left:6px;font-size:0.95em;">Save</button>
                            </div>
                            <div style="display:flex;align-items:center;margin-bottom:10px;">
                                <b>Email:</b>
                                <span id="displayEmail" style="margin-left:8px;"><?php echo htmlspecialchars($email); ?></span>
                                <input type="email" name="edit_email" id="editEmailInput" value="<?php echo htmlspecialchars($email); ?>" style="display:none;margin-left:8px;padding:4px 8px;border-radius:4px;border:1px solid #c8e6c9;">
                                <button type="button" id="editEmailBtn" style="background:none;border:none;cursor:pointer;margin-left:6px;font-size:1.1em;" title="Edit Email">&#9998;</button>
                                <button type="submit" name="save_email" id="saveEmailBtn" style="display:none;background:#2f6b29;color:#fff;border:none;padding:3px 12px;border-radius:4px;margin-left:6px;font-size:0.95em;">Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>

            <!-- Orders Section -->
            <section id="orders-section" class="account-section">
                <h2 style="margin-top:0;">Your Orders</h2>
                <?php if (empty($orders)): ?>
                    <div class="no-orders">No orders yet.</div>
                <?php else: ?>
                    <table>
                        <tr>
                            <th>Product</th>
                            <th>Color</th>
                            <th>Qty</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                        <?php foreach ($orders as $o):
                            $order_time = strtotime($o[7]);
                            $can_cancel = ($o[6] === 'received' && (time() - $order_time) < 86400);
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($o[1]); ?></td>
                                <td><?php echo htmlspecialchars($o[3]); ?></td>
                                <td><?php echo $o[4]; ?></td>
                                <td>KSh <?php echo $o[2]; ?></td>
                                <td>KSh <?php echo $o[5]; ?></td>
                                <td class="order-status <?php echo str_replace(' ', '_', $o[6]); ?>"><?php echo $o[6]; ?></td>
                                <td><?php echo $o[7]; ?></td>
                                <td>
                                    <?php if ($can_cancel): ?>
                                        <button class="cancel-btn custom-cancel-btn" data-order="<?php echo $o[0]; ?>">Cancel</button>
                                    <?php elseif ($o[6] === 'received'): ?>
                                        <button class="cancel-btn" disabled>Cannot cancel</button>
                                    <?php else: ?>
                                        <span style="color:#888;">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                <?php endif; ?>
            </section>

            <!-- Address Section -->
            <section id="address-section" class="account-section">
                <h2 style="margin-top:0;">Delivery Address</h2>
                <form method="POST" style="max-width:400px;">
                    <label><b>Address:</b></label><br>
                    <input type="text" id="addressInput" name="address" value="<?php echo htmlspecialchars($address ?? ''); ?>" placeholder="Street, Building, etc." style="width:100%;padding:7px;margin-bottom:7px;"><br>
                    <label><b>City/Town:</b></label><br>
                    <input type="text" id="cityInput" name="city" value="<?php echo htmlspecialchars($city ?? ''); ?>" placeholder="City/Town" style="width:100%;padding:7px;margin-bottom:7px;"><br>
                    <label><b>Phone:</b></label><br>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($phone ?? ''); ?>" placeholder="Phone Number" style="width:100%;padding:7px;margin-bottom:7px;"><br>
                    <button type="button" id="showMapBtn" style="background:#2980b9;color:#fff;border:none;padding:6px 18px;border-radius:5px;cursor:pointer;margin-bottom:10px;">Select Location on Map</button>
                    <div id="mapContainer" style="display:none;margin-bottom:10px;">
                        <div id="map" style="width:100%;height:300px;border-radius:8px;"></div>
                    </div>
                    <button type="submit" name="save_address" style="background:#2f6b29;color:#fff;border:none;padding:6px 18px;border-radius:5px;cursor:pointer;">Save Address</button>
                </form>
            </section>
        </div>
    </div>

    <!-- Cancel Modal -->
    <div id="cancelModal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;z-index:3000;background:rgba(0,0,0,0.35);justify-content:center;align-items:center;">
        <div style="background:#fff;padding:32px 28px 22px 28px;border-radius:12px;box-shadow:0 4px 24px rgba(0,0,0,0.13);max-width:350px;text-align:center;">
            <h3 style="color:#e74c3c;margin-top:0;">Cancel Order?</h3>
            <p style="color:#2f6b29;margin:18px 0 28px 0;">Are you sure you want to cancel this order?<br>This action cannot be undone.</p>
            <button id="cancelConfirmBtn" style="background:#e74c3c;color:#fff;padding:8px 28px;border:none;border-radius:5px;font-size:1.1em;cursor:pointer;margin-right:12px;">Yes, Cancel</button>
            <button id="cancelCloseBtn" style="background:#2f6b29;color:#fff;padding:8px 22px;border:none;border-radius:5px;font-size:1.1em;cursor:pointer;">No</button>
        </div>
    </div>

    <script>
        let map, marker, geocoder, autocomplete;

        document.getElementById('showMapBtn').onclick = function() {
            let mapDiv = document.getElementById('mapContainer');
            mapDiv.style.display = mapDiv.style.display === 'none' ? 'block' : 'none';
            if (!map) initMap();
        };

        function initMap() {
            geocoder = new google.maps.Geocoder();
            let defaultLatLng = {
                lat: -1.286389,
                lng: 36.817223
            }; // Default: Nairobi
            map = new google.maps.Map(document.getElementById('map'), {
                center: defaultLatLng,
                zoom: 13
            });

            marker = new google.maps.Marker({
                map: map,
                position: defaultLatLng,
                draggable: true
            });

            // Try HTML5 geolocation.
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    let pos = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };
                    map.setCenter(pos);
                    marker.setPosition(pos);
                    fillAddressFromLatLng(pos);
                });
            }

            map.addListener('click', function(e) {
                marker.setPosition(e.latLng);
                fillAddressFromLatLng(e.latLng);
            });

            marker.addListener('dragend', function(e) {
                fillAddressFromLatLng(e.latLng);
            });

            // Optional: Autocomplete for address input
            autocomplete = new google.maps.places.Autocomplete(document.getElementById('addressInput'));
            autocomplete.addListener('place_changed', function() {
                let place = autocomplete.getPlace();
                if (place.geometry) {
                    map.setCenter(place.geometry.location);
                    marker.setPosition(place.geometry.location);
                    fillAddressFromLatLng(place.geometry.location);
                }
            });
        }

        function fillAddressFromLatLng(latlng) {
            geocoder.geocode({
                location: latlng
            }, function(results, status) {
                if (status === 'OK' && results[0]) {
                    document.getElementById('addressInput').value = results[0].formatted_address;
                    // Try to extract city/town from address components
                    let city = '';
                    for (let comp of results[0].address_components) {
                        if (comp.types.includes('locality')) city = comp.long_name;
                        if (comp.types.includes('administrative_area_level_2') && !city) city = comp.long_name;
                    }
                    document.getElementById('cityInput').value = city;
                }
            });
        }
    </script>
    <!-- Google Maps API (replace YOUR_API_KEY) -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyATRKrClVAx58qZ-4MrTBp4q42pHwMT1xc&libraries=places"></script>

    <script>
        // Sidebar navigation
        document.querySelectorAll('.sidebar-link[data-section]').forEach(function(link) {
            link.addEventListener('click', function() {
                document.querySelectorAll('.sidebar-link').forEach(l => l.classList.remove('active'));
                this.classList.add('active');
                document.querySelectorAll('.account-section').forEach(s => s.classList.remove('active'));
                document.getElementById(this.getAttribute('data-section') + '-section').classList.add('active');
            });
        });

        // Inline edit for name/email
        document.getElementById('editNameBtn').onclick = function() {
            document.getElementById('displayName').style.display = 'none';
            document.getElementById('editNameInput').style.display = '';
            document.getElementById('saveNameBtn').style.display = '';
            this.style.display = 'none';
            return false;
        };
        document.getElementById('editEmailBtn').onclick = function() {
            document.getElementById('displayEmail').style.display = 'none';
            document.getElementById('editEmailInput').style.display = '';
            document.getElementById('saveEmailBtn').style.display = '';
            this.style.display = 'none';
            return false;
        };

        // Auto-hide message
        if (document.getElementById('autoHideMsg')) {
            setTimeout(function() {
                document.getElementById('autoHideMsg').style.display = 'none';
            }, 3000);
        }

        // Cancel order modal
        document.querySelectorAll('.custom-cancel-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                var orderId = this.getAttribute('data-order');
                var modal = document.getElementById('cancelModal');
                modal.style.display = 'flex';
                modal.setAttribute('data-order', orderId);
            });
        });
        document.getElementById('cancelCloseBtn').onclick = function() {
            document.getElementById('cancelModal').style.display = 'none';
        };
        document.getElementById('cancelConfirmBtn').onclick = function() {
            var modal = document.getElementById('cancelModal');
            var orderId = modal.getAttribute('data-order');
            window.location.href = "account.php?cancel_order=" + encodeURIComponent(orderId);
        };
    </script>
</body>

</html>