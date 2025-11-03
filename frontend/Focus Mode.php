<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Focus Mode Timer</title>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet" />

    <style>
        :root {
            --bg: #EAEEEB;
            --player-bg: #dbe9d9;
            --text-dark: #3d3d2f;
            --accent-color: #40350A;
            --muted-text: rgba(75, 85, 99, 0.6);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Manrope", system-ui, sans-serif;
        }

        body {
            background: var(--bg);
            color: #222;
            overflow-x: hidden;
        }

        .dashboard-main {
            margin-top: 70px;
            /* header height */
            margin-left: 250px;
            /* sidebar width */
            padding: 0;
            width: calc(100% - 250px);
        }

        .banner-container {
            position: relative;
            width: 120%;
            height: 220px;
            background: url('../src/banner/focus.gif') center/cover no-repeat;
            border-bottom: 2px solid rgba(0, 0, 0, 0.08);
            margin: 20px 0 0 0;
            left: -50px;
            padding-top: 30px;
            bottom: -50px;
            top: -5px;
        }

        .banner-gif {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .focus-header {
            position: relative;
            margin-top: -120px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }

        .focus-header h1 {
            font-size: 32px;
            color: #3b3b2e;
            font-weight: 700;
            margin: 12px 0 0;
        }

        .subtitle {
            margin-top: 3px;
            font-size: 14px;
            opacity: 0.7;
        }

        .focus-content {
            padding: 35px;
            text-align: center;
        }

        .icon-container {
            width: 100%;
            display: flex;
            justify-content: center;
            margin-top: -40px;
            /* pulls the icon closer to the gif */
            margin-bottom: 10px;
        }

        .hourglass-image {
            width: 50px;
            height: 75px;
            margin-bottom: 10px;
            position: relative;
            z-index: 10;
            bottom: -30px;
        }

        .timer-container {
            background-color: #B9C5B4;
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            width: 90%;
            max-width: 700px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            text-align: center;
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            margin: 0 auto;
            bottom: -10px;
        }

        .timer-controls {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 30px;
        }

        .timer-btn {
            padding: 12px 25px;
            border: none;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            min-width: 120px;
        }

        .timer-btn:active {
            transform: translateY(2px);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        #start-btn {
            background-color: #778970;
            color: white;
        }

        #pause-btn {
            background-color: #788E69;
            color: white;
        }

        #reset-btn {
            background-color: #788E69;
            color: white;
        }

        .timer-btn:hover {
            opacity: 0.9;
        }

        .timer-btn:disabled {
            background-color: #cccccc;
            color: #666666;
            cursor: not-allowed;
        }

        .timer-setup {
            background-color: rgba(255, 255, 255, 0.15);
            border-radius: 15px;
            padding: 20px;
            margin-top: 20px;
        }

        .timer-setup h3 {
            margin-bottom: 15px;
            font-size: 18px;
        }

        .time-inputs {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 15px;
        }

        .time-input {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .time-input label {
            font-size: 14px;
            margin-bottom: 5px;
            opacity: 0.8;
        }

        .time-input input {
            width: 70px;
            padding: 10px;
            border: none;
            border-radius: 10px;
            text-align: center;
            font-size: 18px;
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .time-input input:focus {
            outline: 2px solid rgba(255, 255, 255, 0.5);
        }

        .progress-ring {
            margin: 0 auto 30px;
            position: relative;
        }

        .progress-ring-circle {
            width: 100%;
            height: 100%;
            fill: none;
            stroke: rgba(255, 255, 255, 0.2);
            stroke-width: 8;
            stroke-linecap: round;
            transform: rotate(-90deg);
            transform-origin: 50% 50%;
        }

        .progress-ring-progress {
            stroke: #788E69;
            transition: stroke-dashoffset 1s linear;
        }

        .timer-display {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 80px;
            font-weight: bold;
            margin: 30px 0;
            text-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            font-variant-numeric: tabular-nums;
            margin-top: -10px;
        }

        @media (max-width: 500px) {
            .timer-container {
                padding: 25px;
            }

            .timer-display {
                font-size: 60px;
            }

            .progress-ring {
                width: 200px;
                height: 200px;
            }

            .timer-controls {
                flex-direction: column;
                align-items: center;
            }

            .timer-btn {
                padding: 12px 25px;
                border: none;
                border-radius: 50px;
                font-size: 16px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s ease;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
                width: 100%;
                max-width: 200px;
            }
        }

        #audioPlayer,
        #timer-sound {
            visibility: hidden;
            height: 0;
            width: 0;
        }

        /* === Popout Notification === */
        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: #ffffff;
            padding: 30px 40px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            animation: fadeIn 0.3s ease;
        }

        .modal-content h2 {
            color: #3d3d2f;
            margin-bottom: 20px;
        }

        .modal-content button {
            background-color: #788E69;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            transition: 0.3s;
        }

        .modal-content button:hover {
            background-color: #657e5b;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }
    </style>

</head>

<body>
    <?php include 'header.php'; ?>
    <?php include 'sidebar.php'; ?>
    <?php require 'chatbot.php'; ?>

    <main class="dashboard-main">

        <!-- Banner (Medium Height) -->
        <div class="banner-container"></div>
        <section class="focus-content">
            <div class="focus-header">
                <img src="../src/emoji/hourglass.png" class="hourglass-image" alt="Hourglass">
                <h1>Focus Mode</h1>
                <p class="subtitle">Time to focus !!!</p>
            </div>

            <!-- Timer Ring -->
            <div class="timer-container">
                <div class="progress-ring">
                    <svg class="progress-ring-circle" viewBox="0 0 100 100">
                        <circle class="progress-ring-background" cx="50" cy="50" r="45"></circle>
                        <circle class="progress-ring-progress" cx="50" cy="50" r="45" stroke-dasharray="283"
                            stroke-dashoffset="283"></circle>
                    </svg>
                    <div class="timer-display" id="timer-display">00:00</div>
                </div>

                <!-- Buttons -->
                <div class="timer-controls">
                    <button class="timer-btn" id="start-btn">Start</button>
                    <button class="timer-btn" id="reset-btn">Reset</button>
                </div>

                <!-- Duration Setup -->
                <div class="timer-setup">
                    <h3>Set Timer Duration</h3>
                    <div class="time-inputs">
                        <div class="time-input">
                            <label for="hours">Hours</label>
                            <input type="number" id="hours" min="0" max="23" value="0">
                        </div>
                        <div class="time-input">
                            <label for="minutes">Minutes</label>
                            <input type="number" id="minutes" min="0" max="59" value="50">
                        </div>
                        <div class="time-input">
                            <label for="seconds">Seconds</label>
                            <input type="number" id="seconds" min="0" max="59" value="0">
                        </div>
                    </div>
                    <button class="timer-btn" id="set-time-btn" style="background-color: #778970;">Set</button>
                </div>
            </div>
        </section>
        <audio id="timer-sound" src="../src/music/sound/Jingle Bells.mp3" preload="auto"></audio>
    </main>
    <!-- Break Popup Modal -->
    <div id="breakModal" class="modal">
        <div class="modal-content">
            <h2>Need some break?! ☕</h2>
            <button id="closeModal">OK</button>
        </div>
    </div>
    <script src="Focus Mode.js"></script>
</body>

</html>