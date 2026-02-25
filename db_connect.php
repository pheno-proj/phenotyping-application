<?php
// Database Configuration
$host = 'localhost';
$db_name = 'phenotyping_db';
$username = 'root';
$password = ''; // Default XAMPP password is empty

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password);
    // Set PDO to throw exceptions on error
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Set default fetch mode to associative array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}
?>
