<?php
session_start();

$servername = 'localhost';
$username = 'root';
$password = 'root';  // your database password
$dbname = 'innerpeacecomp_web';  // same as your signup database

// Create database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['Username']);
    $password = trim($_POST['Password']);

    // Check if the email exists
    $stmt = $conn->prepare("SELECT user_id, user_username, user_pass FROM signup_web WHERE user_username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    // If user found
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($db_id, $db_username, $hashed_password);
        $stmt->fetch();

        // Verify the entered password against hashed password
        if (password_verify($password, $hashed_password)) {
            // Login success
            $_SESSION['user_id'] = $db_id;
            $_SESSION['user_username'] = $db_username;

            echo "<script>
                    alert('✅ Login successful!');
                    window.location.href = '../frontend/home.php';
                  </script>";
        } else {
            // Wrong password
            echo "<script>
                    alert('❌ Incorrect password. Please try again.');
                    window.history.back();
                  </script>";
        }
    } else {
        // Username not found
        echo "<script>
                alert('⚠️ Username not found. Please sign up first.');
                window.location.href = '../frontend/SignUp.html';
              </script>";
    }

    $stmt->close();
}

$conn->close();
