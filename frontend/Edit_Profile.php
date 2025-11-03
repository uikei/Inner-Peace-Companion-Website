<?php
session_start();

$servername = 'localhost';
$username = 'root';
$password = '';
$dbname = 'innerpeacecomp_web';

$conn = new mysqli($servername, $username, $password, $dbname);

$user_id = $_SESSION['user_id'];

// SQL with prepared statement 
$stmt = $conn->prepare(query: "SELECT user_username FROM signup_web WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    echo "<script>alert('User not found. Please log in again.'); window.location.href='Login.html';</script>";
    exit();
}

$current_username = $row['user_username'];

?>
<!DOCTYPE html>
<html>

<head>
    <title>Edit Profile</title>
    <style>
        body {
            background: #EAEEEB;
            font-family: Manrope, sans-serif;
            margin: 0;
        }

        .banner {
            background: #889B7E;
            padding: 20px;
            color: white;
            text-align: center;
            font-size: 24px;
        }

        .container {
            width: 400px;
            margin: 50px auto;
        }

        input {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border-radius: 6px;
            border: 1px solid gray;
        }

        .btn {
            width: 100%;
            padding: 12px;
            margin-top: 10px;
            background: #889B7E;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 8px;
        }
    </style>
</head>

<body>

    <div class="banner">Edit Profile</div>

    <div class="container">
        <form action="Update_Profile.php" method="post">
            <label>Username</label>
            <input type="text" name="new_username" value="<?php echo $current_username; ?>" required>

            <label>New Password</label>
            <input type="password" name="new_password" required>

            <label>Confirm Password</label>
            <input type="password" name="confirm_password" required>

            <button class="btn" type="submit">Save Changes</button>
        </form>
    </div>

</body>

</html>