<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $phone = trim($_POST['phone']);

    if ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $conn = new mysqli("localhost", "root", "", "ecommerce");
        if ($conn->connect_error) die("DB error");
        $stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = "Email already registered.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, password_hash, address, city, phone) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $name, $email, $hash, $address, $city, $phone);
            $stmt->execute();
            $_SESSION['user_id'] = $stmt->insert_id;
            $_SESSION['user_name'] = $name;
            header("Location: account.php");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | EcoNest</title>
    <link rel="stylesheet" href="../css/style.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f4f4f4; margin:0; }
        .navbar {
            width: 100%;
            background: #2f6b29;
            padding: 0;
            margin: 0 0 30px 0;
            box-shadow: 0 2px 8px rgba(47,107,41,0.06);
        }
        .navbar-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
        }
        .navbar-logo {
            font-size: 2em;
            font-weight: bold;
            color: #fff;
            letter-spacing: 1px;
            font-family: 'Segoe UI', Arial, sans-serif;
            padding: 0 10px;
        }
        .navbar ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
        }
        .navbar li {
            margin: 0;
        }
        .navbar a {
            display: block;
            color: #fff;
            text-decoration: none;
            padding: 18px 22px;
            font-weight: 500;
            font-size: 1.08em;
            border-radius: 4px;
            transition: background 0.2s;
        }
        .navbar a:hover, .navbar .active {
            color: #FFD700;
        }
        .auth-container {
            max-width: 600px;
            margin: 40px auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
            padding: 30px 50px 20px 50px;
        }
        .auth-container h2 {
            color: #2f6b29;
            margin-bottom: 18px;
            text-align: center;
        }
        .auth-container label {
            font-weight: 600;
            color: #2f6b29;
            margin-bottom: 2px;
            display: block;
        }
        .auth-container input[type="text"],
        .auth-container input[type="email"],
        .auth-container input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 6px 0 12px 0;
            border: 1px solid #c8e6c9;
            border-radius: 6px;
            font-size: 1rem;
        }
        .auth-container button {
            width: 100%;
            background: #2f6b29;
            color: #fff;
            border: none;
            padding: 12px;
            border-radius: 6px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
            transition: background 0.3s;
        }
        .auth-container button:hover {
            background: #5d8c56;
        }
        .auth-container .error {
            color: #e74c3c;
            background: #fbeaea;
            border: 1px solid #e74c3c;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 15px;
            text-align: center;
        }
        .auth-container p {
            text-align: center;
            margin-top: 18px;
        }
        .auth-container a {
            color: #2f6b29;
            text-decoration: underline;
        }
        @media (max-width: 700px) {
            .auth-container { max-width: 98vw; padding: 18px 5vw 10px 5vw; }
            .navbar-content { flex-direction: column; align-items: flex-start; padding: 0 8px; }
            .navbar-logo { font-size: 1.3em; padding: 10px 0 0 0; }
            .navbar ul { width: 100%; }
            .navbar a { padding: 14px 10px; font-size: 1em; }
        }
        .footer-social a:hover {
            color: #FFD700 !important;
        }
        .footer-social a {
            color: #fff;
            text-decoration: none;
            font-size: 1.3em;
            margin: 0 12px;
        }
          #gSignInWrapper {
            text-align: center;
            margin-top: 20px;
        }
        .g_id_signin {
            display: inline-block;
            margin-top: 10px;
        }
        .g_id_signin button {
            width: 100%;
            padding: 12px;
            border-radius: 6px;
            font-size: 1.1rem;
            font-weight: bold;
            background-color: #4285F4;
            color: white;
            border: none;
            cursor: pointer;
        }
        .g_id_signin button:hover {
            background-color: #357ae8;
        }
    </style>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyATRKrClVAx58qZ-4MrTBp4q42pHwMT1xc&libraries=places"></script>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-content">
            <span class="navbar-logo">EcoNest 🌿</span>
            <ul>
                <li><a href="../pages/index.html">Home</a></li>
                <li><a href="../modules/shop_main.php">Shop</a></li>
                <li><a href="../modules/cart.php">Cart</a></li>
                <li><a href="../modules/register.php" class="active">Register</a></li>
                <li><a href="../modules/login.php">Login</a></li>
            </ul>
        </div>
    </nav>
    <div class="auth-container">
        <h2>Create Account</h2>
        <?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>
        <form method="POST">
            <div style="display:flex;gap:18px;">
                <div style="flex:1;">
                    <label>Name:</label>
                    <input type="text" name="name" required>
                    <label>Email:</label>
                    <input type="email" name="email" required>
                    <label>Password:</label>
                    <input type="password" name="password" required>
                    <label>Confirm Password:</label>
                    <input type="password" name="confirm" required>
                </div>
                <div style="flex:1;">
                    <label>Address:</label>
                    <input type="text" name="address" id="addressInput" required>
                    <label>City/Town:</label>
                    <input type="text" name="city" id="cityInput" required>
                    <label>Phone:</label>
                    <input type="text" name="phone" required>
                    <button type="button" id="showMapBtn" style="background:#2980b9;color:#fff;border:none;padding:6px 18px;border-radius:5px;cursor:pointer;margin-bottom:10px;margin-top:10px;">Select Location on Map</button>
                    <div id="mapContainer" style="display:none;margin-bottom:10px;">
                        <div id="map" style="width:100%;height:250px;border-radius:8px;"></div>
                    </div>
                </div>
            </div>
            <button type="submit">Register</button>
        </form>
        <!-- Google Sign-In Section -->
<div style="text-align:center;margin:32px 0 24px 0;">
    <div style="font-size:1.1em;color:#2f6b29;font-weight:600;margin-bottom:10px;letter-spacing:0.5px;">
      Or
    </div>
    <div id="gSignInWrapper">
        <div id="g_id_onload"
            data-client_id="633377089412-nq4smjp6ugsb5qvi7orvv7kr1epb9hg1.apps.googleusercontent.com"
            data-context="signup"
            data-ux_mode="redirect"
            data-login_uri="http://localhost/e-commerce/EcoNest/modules/google_callback.php"
            data-auto_prompt="false">
        </div>
        <div class="g_id_signin"
            data-type="standard"
            data-shape="rectangular"
            data-theme="outline"
            data-text="signup_with"
            data-size="large">
        </div>
    </div>
</div>
<script src="https://accounts.google.com/gsi/client" async defer></script>
        <p>Already have an account? <a href="../modules/login.php">Login</a></p>
    </div>
    <script>
let map, marker, geocoder, autocomplete;

document.getElementById('showMapBtn').onclick = function() {
    let mapDiv = document.getElementById('mapContainer');
    mapDiv.style.display = mapDiv.style.display === 'none' ? 'block' : 'none';
    if (!map) initMap();
};

// Optional: Autocomplete for address input
autocomplete = new google.maps.places.Autocomplete(document.getElementById('addressInput'));
autocomplete.addListener('place_changed', function() {
    let place = autocomplete.getPlace();
    if (place.geometry) {
        if (map && marker) {
            map.setCenter(place.geometry.location);
            marker.setPosition(place.geometry.location);
        }
        fillAddressFromLatLng(place.geometry.location);
    }
});

function initMap() {
    geocoder = new google.maps.Geocoder();
    let defaultLatLng = { lat: -1.286389, lng: 36.817223 }; // Nairobi default
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
}

function fillAddressFromLatLng(latlng) {
    geocoder.geocode({ location: latlng }, function(results, status) {
        if (status === 'OK' && results[0]) {
            document.getElementById('addressInput').value = results[0].formatted_address;
            // Improved city/town extraction
            let city = '';
            let county = '';
            let sublocality = '';
            for (let comp of results[0].address_components) {
                if (comp.types.includes('locality')) city = comp.long_name;
                if (comp.types.includes('administrative_area_level_2')) county = comp.long_name;
                if (comp.types.includes('sublocality')) sublocality = comp.long_name;
            }
            // Prefer city, then sublocality, then county
            document.getElementById('cityInput').value = city || sublocality || county || '';
        }
    });
}
</script>
    <footer style="background:#2f6b29;color:#fff;padding:24px 0;text-align:center;">
        <p style="margin:0;font-size:1.2em;">Follow us on social media</p>
 <div class="footer-social" style="margin: 12px 0; display: flex; justify-content: center; gap: 24px; flex-wrap: wrap;">
            <a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a>
            <a href="#" title="Twitter"><i class="fab fa-twitter"></i></a>
            <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
        </div>
        <p>&copy; 2025 EcoNest. All rights reserved.</p>
    </footer>
</body>
</html>