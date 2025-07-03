<?php
// db_connect.php
// Centralized database connection for EcoNest (InfinityFree)
$conn = new mysqli(
    "sql301.infinityfree.com", // Hostname
    "if0_39384208",            // Username
    "steveronald",             // Password
    "if0_39384208_ecommerce",  // Database name
    3306                        // Port (optional)
);
if ($conn->connect_error) die("DB connection failed: " . $conn->connect_error);
?>
