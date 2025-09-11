<?php
// Database connection settings
$host = 'localhost';
$dbname = 'rental_management';
$username = 'root';
$password = ''; // Default XAMPP MySQL password is empty

try {
    // Create a new PDO instance for database connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    // Set error mode to exception for better error handling
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Enable prepared statements for security
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    // Log the error (for now, just die with a message; we'll improve this later)
    die("Connection failed: " . $e->getMessage());
}
?>