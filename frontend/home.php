<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #EAEEEB;
            font-family: 'Manrope', sans-serif;
        }

        /* Main content area (pushes content to account for sidebar) */
        .main-content {
            margin-left: 200px; /* Match sidebar width */
            /* margin-top: 0; */
            padding: 40px;
            min-height: calc(100vh - 95px);
        }
    </style>
</head>
<body>
    <?php require 'header.php'; ?>
    <?php require 'sidebar.php'; ?>
    <?php require 'chatbot.php'; ?>
    <!-- Main Dashboard Content -->
    <div class="main-content">
        <div class="title-user">

        </div>
    </div>
</body>
</html>