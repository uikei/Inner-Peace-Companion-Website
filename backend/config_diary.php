<?php

session_start();

require_once __DIR__ . '/env.php';

try {
    $pdo = new PDO(
        "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_DATABASE']};charset=utf8mb4",
        $_ENV['DB_USERNAME'],
        $_ENV['DB_PASSWORD']
    );
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

