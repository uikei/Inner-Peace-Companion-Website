<?php
// config.php - Database configuration
session_start();

$host = 'localhost';
$dbname = 'innerpeacecomp_web';
$username = 'root';
$password = 'root';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// For testing purposes, set a default user_id
// Remove this in production and use actual session user_id
/*
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; // Default test user
}
*/
?>

