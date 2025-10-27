<?php

$servername = 'localhost';
$username = 'root';
$password = '';     //Change this to your actual database password
$dbname = 'innerpeacecomp_web';  //change it based on your database name

//Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

//Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Check if passwords match
    if ($password !== $confirm_password) {
        echo "<script>alert('❌ Passwords do not match!'); window.history.back();</script>";
        exit();
    }


    // Check if email already exists
    $check = $conn->prepare("SELECT signup_email FROM signup_web WHERE signup_email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo "<script>alert('⚠️ Email already registered! Please use another.'); window.history.back();</script>";
        exit();
    }

    $check->close();

    // Hash passwords before storing
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO signup_web (signup_email, signup_username, signup_pass) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $email, $username, $hashed_password);


    if ($stmt->execute()) {
        echo "<script>alert('✅ Registration successful! You can now login.'); 
        window.location.href='../frontend/RegisterSuccess.php';</script>";
        exit();

    } else {
        echo "<script>alert('❌ Error: Unable to register.'); window.history.back();</script>";
    }

    $stmt->close();
}
$conn->close();