<?php
session_start();
?>

<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Register Successful Page</title>

  <!-- Google Font -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;600;700&display=swap" rel="stylesheet" />

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: "Manrope", sans-serif;
    }

    body {
      background-color: #eaeeeb;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    /* --- Navbar --- */
    .navbar {
      background-color: #889b7e;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 12px 60px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .logo img {
      height: 90px;
      width: 90px;
      object-fit: contain;
      object-fit: cover;
      transition: transform 0.4s ease, filter 0.3s;
      animation: floatLogo 5s ease-in-out infinite;
    }

    @keyframes floatLogo {
      0% {
        transform: translateY(0);
      }

      50% {
        transform: translateY(-5px);
      }
    }

    .logo:hover img {
      transform: scale(1.05);
    }

    /* --- Main content --- */
    main {
      flex: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
      padding: 30px;
      opacity: 0;
      /* Initially hidden */
      transform: translateY(20px);
      /* Initial position */
      animation: fadeIn 0.8s ease-out forwards 0.3s;
      /* Fade-in animation */
    }

    @keyframes fadeIn {
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .checkmark {
      font-size: 140px;
      color: white;
      background-color: #4caf50;
      border-radius: 50%;
      width: 160px;
      height: 160px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 25px;
      overflow: hidden;
    }

    .checkmark img {
      width: 70%;
      height: auto;
      animation: pulse 1.5s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }

    @keyframes pulse {
      0% {
        transform: scale(1);
      }

      50% {
        transform: scale(1.1);
      }

      100% {
        transform: scale(1);
      }
    }

    h2 {
      font-size: 2rem;
      font-weight: 700;
      color: #222;
      margin-bottom: 15px;
      animation: slideInDown 0.6s ease-out;
      /* Slide-in-animation */
    }

    @keyframes slideInDown {
      from {
        transform: translateY(-50px);
        opacity: 0;
      }

      to {
        transform: translateY(0);
        opacity: 1;
      }
    }

    p {
      font-size: 1rem;
      color: #333;
    }

    @keyframes fadeInUp {
      from {
        transform: translateY(30px);
        opacity: 0;
      }

      to {
        transform: translateY(0);
        opacity: 1;
      }
    }

    a.login-link {
      color: #4a74e3;
      text-decoration: none;
      font-weight: 600;
      margin-left: 5px;
      transition: color 0.3s, text-shadow 0.3s;
    }

    a.login-link:hover {
      text-decoration: underline;
      color: #3b5b8c;
      text-shadow: 0 0 5px rgba(59, 91, 140, 0.5);
    }
  </style>
</head>

<body>
  <header class="navbar">
    <div class="logo">
      <a href="../frontend/LandingPage.html">
        <img src="../src/image/logo/logo.png" alt="Logo" />
      </a>
    </div>
  </header>

  <main>
    <div class="checkmark">
      <img src="../src/ui/checkMark.png" alt="checkMark" />
    </div>
    <h2>Register successful!!</h2>
    <p>
      Click here to navigate back to
      <a href="Login.html" class="login-link">Login</a>
    </p>
  </main>
</body>

</html>