<?php
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Relax Mode</title>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet" />

    <link rel="stylesheet" href="RelaxMode.css" />
</head>

<body>
    <?php include 'header.php'; ?>
    <?php include 'sidebar.php'; ?>
    <?php require 'chatbot.php'; ?>

    <!------------------------------------------- Dashboard ------------------------------->
    <div class="page-layout">
        <!-- Banner Section -->
        <div class="banner-container"></div>
        <section class="relax-content">
            <div class="relax-header">
                <img src="../src/emoji/headphones.png" alt="Relax Icon" class="banner-image">
                <h1>Relax Mode</h1>
                <p class="subtitle">Come chill with us -_-</p>
            </div>

            <!-- Track Section (cards act as category selectors) -->

            <section class="tracks-section">
                <div class="track-card" data-category="meditate">
                    <img src="../src/image/cover/meditate.jpeg" alt="Meditate" class="track-bg">
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
            <footer id="player-bar" class="player-bar">
                <div class="player">
                    <img id="playerCover" src="../src/image/cover/calm.jpeg" alt="Cover" class="player-cover">
                    <div class="player-meta">
                        <div id="playerTitle" class="player-title">Title</div>
                        <div id="playerArtist" class="player-artist"></div>
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
                    <span id="duration">0:00</span>
                </div>
            </footer>
        </section>

        <!-- hidden audio element controlled by JS -->
        <audio id="audioPlayer" preload="metadata"></audio>
    </div>
    <script src="Relax.js"></script>
</body>

</html>