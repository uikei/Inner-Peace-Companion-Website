<?php
session_start();

$username = $_SESSION['user_username'] ?? null;

?>
<!DOCTYPE html>
<html>

<head>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet" />

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

        .report {
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

        <button class="btn edit" onclick="window.location.href='Edit_Profile.php'">Edit Profile</button>
        <button class="btn report" onclick="window.location.href='report.php'">Generate Report</button>
        <button class="btn logout" onclick="window.location.href='Logout.php'">Logout</button>
    </div>

</body>

</html>