<?php
session_start();

$username = $_SESSION['signup_username'] ?? null;

?>
<!DOCTYPE html>
<html>

<head>
    <title>Settings</title>
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
            text-align: center;
        }

        .btn {
            display: block;
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: none;
            cursor: pointer;
            font-size: 16px;
            border-radius: 8px;
        }

        .edit {
            background: #889B7E;
            color: white;
        }

        .logout {
            background: #B14E4E;
            color: white;
        }
    </style>
</head>

<body>

    <div class="banner">Account Settings</div>

    <div class="container">
        <h2>Hello, <?php echo $username; ?></h2>

        <button class="btn edit" onclick="window.location.href='edit_profile.php'">Edit Profile</button>
        <button class="btn logout" onclick="window.location.href='logout.php'">Logout</button>
    </div>

</body>

</html>