<?php
// Your PHP code here (database connections, etc.)
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet" />
    <title>Forgot Username</title>
    <link rel="stylesheet" href="Login&SignUp.css" />
</head>

<body>
    <header class="navbar">
        <div class="logo">
            <a href="../frontend/LandingPage.html">
                <img class="logo" src="../src/image/logo/logo.png" alt="Logo" />
            </a>
        </div>
    </header>

    <main class="container">
        <div class="form-box">
            <h2>Forgot Username</h2>

            <form action="../backend/ForgotUsernameProcess.php" method="POST">
                <label>Enter your registered email</label>
                <input type="text" name="email" placeholder="Enter your email..." required />

                <label>New Username</label>
                <input type="text" name="new_username" placeholder="Enter new username..." required />

                <label>Confirm Username</label>
                <input type="text" name="confirm_username" placeholder="Confirm new username..." required />

                <button type="submit" class="btn">Update Username</button>

                <p class="switch-page">
                    Remember your username?
                    <a href="../frontend/Login.html">Login</a>
                </p>
            </form>
        </div>
    </main>
</body>

</html>