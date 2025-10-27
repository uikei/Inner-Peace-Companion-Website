<?php
session_start();
if (!isset($_SESSION['signup_id'])) {
    header("Location: Login.html");
    exit();
}

$servername = 'localhost';
$username = 'root';
$password = '';
$dbname = 'innerpeacecomp_web';

$conn = new mysqli($servername, $username, $password, $dbname);

$signup_id = $_SESSION['signup_id'];
$new_username = trim($_POST['new_username']);
$new_password = trim($_POST['new_password']);
$confirm_password = trim($_POST['confirm_password']);

if ($new_password !== $confirm_password) {
    echo "<script>alert('❌ Passwords do not match!'); window.history.back();</script>";
    exit();
}

$hashed = password_hash($new_password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE signup_web SET signup_username=?, signup_pass=?, signup_compass=? WHERE signup_id=?");
$stmt->bind_param("sssi", $new_username, $hashed, $hashed, $signup_id);

if ($stmt->execute()) {
    $_SESSION['signup_username'] = $new_username;
    echo "<script>alert('✅ Profile updated successfully!'); window.location.href='settings.php';</script>";
} else {
    echo "<script>alert('❌ Update failed.'); window.history.back();</script>";
}
