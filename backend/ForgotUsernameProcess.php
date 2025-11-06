<?php
$servername = 'localhost';
$username = 'root';
$password = 'root';
$dbname = 'innerpeacecomp_web';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $new_username = trim($_POST['new_username']);
    $confirm_username = trim($_POST['confirm_username']);

    // Check match
    if ($new_username !== $confirm_username) {
        echo "<script>
                alert('⚠️ Username and confirmation do not match.');
                window.history.back();
              </script>";
        exit();
    }

    // Check if email exists
    $stmt = $conn->prepare("SELECT user_id FROM signup_web WHERE user_email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Update username
        $stmt->close();
        $stmt = $conn->prepare("UPDATE signup_web SET user_username = ? WHERE user_email = ?");
        $stmt->bind_param("ss", $new_username, $email);

        if ($stmt->execute()) {
            echo "<script>
                    alert('✅ Username updated successfully! Please log in with your new username.');
                    window.location.href = '../frontend/Login.html';
                  </script>";
        } else {
            echo "<script>
                    alert('❌ Error updating username. Please try again.');
                    window.history.back();
                  </script>";
        }
    } else {
        echo "<script>
                alert('⚠️ Email not found in our system.');
                window.history.back();
              </script>";
    }

    $stmt->close();
}

$conn->close();
?>