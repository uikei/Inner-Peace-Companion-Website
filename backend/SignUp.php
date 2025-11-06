<?php

$servername = 'localhost';
$username = 'root';
$password = 'root';     //Change this to your actual database password
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
    $check = $conn->prepare("SELECT user_email FROM signup_web WHERE user_email = ?");
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
    // user_compass is set to the same as user_pass (hashed password)
    $stmt = $conn->prepare("INSERT INTO signup_web (user_email, user_username, user_pass, user_compass) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $email, $username, $hashed_password, $hashed_password);


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