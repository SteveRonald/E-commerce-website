<?php
// db_connect.php
// Centralized database connection for EcoNest

// Localhost (XAMPP) only
$conn = new mysqli(
    "localhost", // Hostname
    "root",      // Username
    "",          // Password
    "ecommerce"  // Database name
);
if ($conn->connect_error) die("DB connection failed: " . $conn->connect_error);
?>
