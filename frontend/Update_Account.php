<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.html");
    exit();
}
require_once __DIR__ . '/../backend/env.php';

$conn = new mysqli(
    $_ENV['DB_HOST'],
    $_ENV['DB_USERNAME'],
    $_ENV['DB_PASSWORD'],
    $_ENV['DB_DATABASE']
);

$user_id = $_SESSION['user_id'];
$new_username = trim($_POST['new_username']);
$new_password = trim($_POST['new_password']);
$confirm_password = trim($_POST['confirm_password']);

if ($new_password !== $confirm_password) {
    echo "<script>alert('❌ Passwords do not match!'); window.history.back();</script>";
    exit();
}

$hashed = password_hash($new_password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE signup_web SET user_username=?, user_pass=?, user_compass=? WHERE user_id=?");
$stmt->bind_param("sssi", $new_username, $hashed, $hashed, $user_id);

if ($stmt->execute()) {
    $_SESSION['user_username'] = $new_username;
    echo "<script>alert('✅ Profile updated successfully!'); window.location.href='Setting.php';</script>";
} else {
    echo "<script>alert('❌ Update failed.'); window.history.back();</script>";
}
