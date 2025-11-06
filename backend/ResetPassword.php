<?php
$servername = 'localhost';
$username = 'root';
$password = 'root';
$dbname = 'innerpeacecomp_web';

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    if ($new_password !== $confirm_password) {
        echo "<script>alert('Passwords do not match!'); window.history.back();</script>";
        exit();
    }

    $check = $conn->prepare("SELECT user_email FROM signup_web WHERE user_email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows == 0) {
        echo "<script>alert('Email not found! Please use a registered account.'); window.history.back();</script>";
        exit();
    }

    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $update = $conn->prepare("UPDATE signup_web SET user_pass=?, user_compass=? WHERE user_email=?");
    $update->bind_param("sss", $hashed_password, $hashed_password, $email);

    if ($update->execute()) {
        echo "<script>
            alert('✅ Password reset successful! You can now log in.');
            window.location.href = '../frontend/Login.html';
          </script>";
    } else {
        echo "<script>
            alert('❌ Error resetting password. Try again later.');
            window.history.back();
          </script>";
    }

    $update->close();
}
$conn->close();

