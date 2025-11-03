<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout Confirmation</title>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet" />

    <link href="https://cdn.tailwindcss.com" rel="stylesheet">

    <style>
        body {
            font-family: 'Manrope', sans-serif;
        }

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .modal-content {
            background: white;
            padding: 32px;
            border-radius: 16px;
            max-width: 380px;
            width: 90%;
            text-align: center;
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-title {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 14px;
            color: #111827;
        }

        .modal-message {
            font-size: 15px;
            margin-bottom: 26px;
            color: #374151;
        }

        .modal-buttons {
            display: flex;
            gap: 12px;
            justify-content: center;
        }

        .btn-yes {
            background: #b91c1c;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-yes:hover {
            background: #991b1b;
            transform: scale(1.05);
        }

        .btn-cancel {
            background: #6b7280;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-cancel:hover {
            background: #4b5563;
            transform: scale(1.05);
        }
    </style>
</head>

<body>
    <div class="modal-overlay">
        <div class="modal-content">
            <div class="modal-title">Logout?</div>
            <div class="modal-message">Are you sure you want to logout?</div>

            <div class="modal-buttons">
                <form method="POST">
                    <button type="submit" name="confirm_logout" class="btn-yes">Yes</button>
                </form>
                <button class="btn-cancel" onclick="window.history.back()">Cancel</button>
            </div>
        </div>
    </div>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_logout'])) {
        session_unset();
        session_destroy();
        echo "<script>window.location.href = '../frontend/LandingPage.html';</script>";
        exit();
    }
    ?>

</body>

</html>