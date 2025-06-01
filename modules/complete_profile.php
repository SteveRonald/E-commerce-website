<?php
session_start();
if (!isset($_SESSION['google_email'])) {
    header("Location: register.php");
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $phone = trim($_POST['phone']);
    $name = $_SESSION['google_name'];
    $email = $_SESSION['google_email'];

    $conn = new mysqli("localhost", "root", "", "ecommerce");
    $stmt = $conn->prepare("INSERT INTO users (name, email, address, city, phone) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $email, $address, $city, $phone);
    $stmt->execute();
    $_SESSION['user_id'] = $stmt->insert_id;
    $_SESSION['user_name'] = $name;
    unset($_SESSION['google_name'], $_SESSION['google_email']);
    header("Location: account.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Complete Profile</title>
    <style>
        body { background: #f4f4f4; font-family: 'Segoe UI', Arial, sans-serif; }
        .profile-container {
            max-width: 400px;
            margin: 60px auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
            padding: 30px 40px 20px 40px;
        }
        h2 { color: #2f6b29; text-align: center; }
        label { font-weight: 600; color: #2f6b29; margin-bottom: 2px; display: block; }
        input[type="text"] {
            width: 100%; padding: 10px; margin: 6px 0 12px 0;
            border: 1px solid #c8e6c9; border-radius: 6px; font-size: 1rem;
        }
        button {
            width: 100%; background: #2f6b29; color: #fff; border: none;
            padding: 12px; border-radius: 6px; font-size: 1.1rem; font-weight: bold;
            cursor: pointer; margin-top: 10px; transition: background 0.3s;
        }
        button:hover { background: #5d8c56; }
        #showMapBtn {
            background:#2980b9;color:#fff;border:none;padding:6px 18px;border-radius:5px;cursor:pointer;margin-bottom:10px;margin-top:10px;width:auto;
        }
        #mapContainer { display:none;margin-bottom:10px; }
        #map { width:100%;height:250px;border-radius:8px; }
    </style>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyATRKrClVAx58qZ-4MrTBp4q42pHwMT1xc&libraries=places"></script>
</head>
<body>
    <div class="profile-container">
        <h2>Complete Your Profile</h2>
        <form method="POST">
            <label>Address:</label>
            <input type="text" name="address" id="addressInput" required>
            <label>City/Town:</label>
            <input type="text" name="city" id="cityInput" required>
            <label>Phone:</label>
            <input type="text" name="phone" required>
            <button type="button" id="showMapBtn">Select Location on Map</button>
            <div id="mapContainer">
                <div id="map"></div>
            </div>
            <button type="submit">Finish Registration</button>
        </form>
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
    geocoder.geocode({ location: latlng }, function(results, status) {
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
</body>
</html>