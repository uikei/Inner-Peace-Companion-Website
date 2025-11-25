<?php
/**
 * Database Configuration
 * PDO connection for innerpeacecomp_web database
 */

// Database credentials
$host = 'localhost';      // or '127.0.0.1'
$dbname = 'innerpeacecomp_web';
$username = 'root';       // your database username
$password = '';         
$port = '3306';       

// For MAMP users, uncomment this line:
// $port = '8889';

// DSN (Data Source Name)
$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

// PDO options
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,      // Throw exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,            // Fetch associative arrays by default
    PDO::ATTR_EMULATE_PREPARES   => false,                       // Use real prepared statements
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"          // Set charset
];

try {
    // Create PDO instance
    $pdo = new PDO($dsn, $username, $password, $options);
    
    // Optional: uncomment to debug connection
    // echo "Database connected successfully!";
    
} catch (PDOException $e) {
    // Log error and show user-friendly message
    error_log("Database Connection Error: " . $e->getMessage());
    die("Connection failed. Please check database configuration.");
}

// Return the PDO connection for use in other files
return $pdo;
?>