<?php
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet" />

    <link rel="stylesheet" href="RelaxMode.css" />
</head>

<body>
    <?php include 'header.php'; ?>
    <?php include 'sidebar.php'; ?>
    <?php require 'chatbot.php'; ?>


    <div class="page-layout">
        <main class="relax-main">
            <!-- Banner Section -->
            <div class="banner-container">
                <img src="../src/banner/relax.gif" alt="banner" class="banner-gif">
            </div>

            <div class="content-container">
                <div class="headphone-container">
                    <img src="../src/emoji/headphones.png" alt="headphone" class="headphone-image">
                </div>
                <h1 class="title">Relax Mode</h1>
                <p class="description">Come chill with us</p>
            </div>

            <!-- Track Section (cards act as category selectors) -->
            <section class="tracks-section">
                <div class="track-card" data-category="mediate">
                    <img src="../src/image/cover/mediate.jpeg" alt="Meditate" class="track-bg">
                    <span class="track-title">Mediate</span>
                    <button class="track-btn" aria-label="Play Meditate"><img src="../src/ui/playBtn.png"
                            alt="Play"></button>
                </div>

                <div class="track-card" data-category="sleep">
                    <img src="../src/image/cover/sleep.jpeg" alt="Sleep" class="track-bg">
                    <span class="track-title">Sleep</span>
                    <button class="track-btn" aria-label="Play Sleep"><img src="../src/ui/playBtn.png"
                            alt="Play"></button>
                </div>

                <div class="track-card playing" data-category="calm">
                    <img src="../src/image/cover/calm.jpeg" alt="Calm" class="track-bg">
                    <span class="track-title">Calm</span>
                    <button class="track-btn" aria-label="Play Calm"><img src="../src/ui/playBtn.png"
                            alt="Pause"></button>
                </div>
            </section>
        </main>
        <!-- Mini Player - hidden until a category is clicked -->
        <footer class="player-bar" class="player-bar hidden">
            <div class="player">
                <img src="../src/image/cover/calm.jpeg" alt="Cover" class="player-cover">
                <div class="player-meta">
                    <div class="playerTitle" class="player-title">Title</div>
                    <div class="playerArtist" class="player-artist"></div>
                </div>
            </div>

            <div class="player-controls">
                <button id="prevBtn" class="control-btn" aria-label="Previous">
                    <img src="../src/ui/skipToStart.png" alt="prev">
                </button>
                <button id="playPauseBtn" class="control-btn" aria-label="Play/Pause">
                    <img id="playPauseIcon" src="../src/ui/playBtn.png" alt="play/pause">
                </button>
                <button id="nextBtn" class="control-btn" aria-label="Next">
                    <img src="../src/ui/endBtn.png" alt="next">
                </button>
            </div>

            <div class="player-time">
                <span id="currentTime">0:00</span>
                <input id="progressBar" type="range" min="0" max="100" value="0" step="1">
                <span>0:00</span>
            </div>
        </footer>
        <!-- hidden audio element controlled by JS -->
        <audio id="audioPlayer" preload="metadata"></audio>
    </div>
    <script src="Relax.js"></script>
</body>

</html>