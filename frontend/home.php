<?php

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$host = 'localhost';
$dbname = 'innerpeacecomp_web';
$db_username = 'root';
$db_password = 'root';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $db_username, $db_password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get username
    $stmt = $pdo->prepare("SELECT user_username FROM signup_web WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $username = $user['user_username'] ?? 'User';
    
    // Get latest PHQ-9 score
    $stmt = $pdo->prepare("
        SELECT total_score, severity_level, created_at 
        FROM phq9_responses 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 1
    ");
    $stmt->execute([$user_id]);
    $phq_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get latest GAD-7 score
    $stmt = $pdo->prepare("
        SELECT total_score, severity_level, created_at 
        FROM gad7_responses 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 1
    ");
    $stmt->execute([$user_id]);
    $gad_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get today's journal emotion
    $today = date('Y-m-d');
    $stmt = $pdo->prepare("
        SELECT emotion 
        FROM journals 
        WHERE user_id = ? AND DATE(created_date) = ? 
        ORDER BY created_date DESC 
        LIMIT 1
    ");
    $stmt->execute([$user_id, $today]);
    $today_journal = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get last 7 days of journal emotions for calendar
    $stmt = $pdo->prepare("
        SELECT DATE(created_date) as journal_date, emotion 
        FROM journals 
        WHERE user_id = ? AND DATE(created_date) >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
        ORDER BY created_date DESC
    ");
    $stmt->execute([$user_id]);
    $week_journals = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Create emotion map for the week
    $emotion_map = [];
    foreach ($week_journals as $journal) {
        $emotion_map[$journal['journal_date']] = $journal['emotion'];
    }
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $phq_data = null;
    $gad_data = null;
    $today_journal = null;
    $emotion_map = [];
}

// Helper function to get emotion emoji
function getEmotionEmoji($emotion) {
    $emojis = [
        'happy' => '../src/journalAsset/emotions/happy.png',
        'sad' => '../src/journalAsset/emotions/cry.png',
        'angry' => '../src/journalAsset/emotions/angry.png',
        'anxious' => '../src/journalAsset/emotions/worried.png'
    ];
    return $emojis[$emotion] ?? '../src/journalAsset/emotions/happy.png';
}

// Helper function to get risk color
function getRiskColor($severity) {
    $colors = [
        'Minimal' => 'bg-green-100 text-green-800',
        'Mild' => 'bg-yellow-100 text-yellow-800',
        'Moderate' => 'bg-orange-100 text-orange-800',
        'Moderately Severe' => 'bg-red-100 text-red-800',
        'Severe' => 'bg-red-200 text-red-900'
    ];
    return $colors[$severity] ?? 'bg-gray-100 text-gray-800';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&family=Poppins:ital,wght@0,500;0,600;0,700;1,500&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Manrope', sans-serif;
        }
        .main-content {
            margin-left: 200px;
            margin-top: 40px;
            padding: 40px;
            min-height: calc(100vh - 95px);
        }
    </style>
</head>
<body class="bg-[#EAEEEB] min-h-screen">
    <?php require 'header.php'; ?>
    <?php require 'sidebar.php'; ?>
    <?php require 'chatbot.php'; ?>
    <?php require 'screening_modal.php'; ?>

    <div class="main-content">
        <div class="container mx-auto px-4 py-8 max-w-7xl">
            <!-- Welcome Header -->
            <div class="mb-8">
                <h1 class="text-4xl font-bold text-[#40350A]">Welcome <?php echo htmlspecialchars($username); ?>!</h1>
                <hr class="mt-4 border-t-2 border-[#B9C5B4]">
            </div>

            <!-- Top Section: Risk Assessment & Mood Calendar -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Mood Calendar -->
                <div class="bg-[#B9C5B4] rounded-2xl shadow-lg p-6 hover:shadow-xl transition-shadow">
                    <h3 class="text-xl font-semibold text-[#40350A] mb-4">My Week at a Glance</h3>
                    
                    <!-- Today's Mood Highlight -->
                    <div class="bg-white rounded-xl p-6 mb-4 text-center">
                        <p class="text-sm text-[#706F4E] mb-2">Today's Mood</p>
                        <div class="text-7xl mb-2">
                            <?php 
                            if ($today_journal) {
                                $emoji_path = getEmotionEmoji($today_journal['emotion']);
                                echo '<img src="' . htmlspecialchars($emoji_path) . '" alt="' . htmlspecialchars($today_journal['emotion']) . '" class="w-24 h-24 mx-auto">';
                            } else {
                                echo '<img src="../src/journalAsset/emotions/happy.png" alt="No entry" class="w-24 h-24 mx-auto opacity-50">';
                            }
                            ?>
                        </div>
                        <p class="text-lg font-semibold text-[#40350A]">
                            <?php echo $today_journal ? $today_journal['emotion'] : 'No entry yet'; ?>
                        </p>
                        <p class="text-sm text-[#706F4E] mt-1"><?php echo date('l, F d, Y'); ?></p>
                    </div>

                    <!-- Week Calendar -->
                    <div class="grid grid-cols-7 gap-2">
                        <?php
                        for ($i = 6; $i >= 0; $i--) {
                            $date = date('Y-m-d', strtotime("-$i days"));
                            $day_name = date('D', strtotime($date));
                            $day_num = date('j', strtotime($date));
                            $emotion = $emotion_map[$date] ?? null;
                            $is_today = $date === $today;
                            ?>
                            <div class="text-center">
                                <p class="text-xs text-[#706F4E] mb-1"><?php echo $day_name; ?></p>
                                <div class="bg-white rounded-lg p-2 <?php echo $is_today ? 'ring-2 ring-[#778970]' : ''; ?>">
                                    <p class="text-xs font-semibold text-[#40350A] mb-1"><?php echo $day_num; ?></p>
                                    <div class="text-2xl">
                                        <?php 
                                        if ($emotion) {
                                            $emoji_path = getEmotionEmoji($emotion);
                                            echo '<img src="' . htmlspecialchars($emoji_path) . '" alt="' . htmlspecialchars($emotion) . '" class="w-8 h-8 mx-auto">';
                                        } else {
                                            echo '—';
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                    
                    <div class="mt-4 text-center">
                        <a href="journal.php" class="text-sm text-[#40350A] hover:text-[#778970] font-medium">
                            View All Journal Entries →
                        </a>
                    </div>
                </div>
                <!-- Mental Health Risk Cards -->
                <div class="space-y-4 pt-10">
                    <!-- Anxiety Risk Card -->
                    <div class="bg-[#B9C5B4] rounded-2xl shadow-lg p-6 hover:shadow-xl transition-shadow">
                        <div class="flex items-center mb-3">
                            <img src="../src/ui/brain.png" alt="brain" class="w-[35px] h-[35px] mr-3">
                            <h3 class="text-lg font-semibold text-white">Anxiety Risk</h3>
                        </div>
                        <?php if ($gad_data): ?>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-5xl font-bold text-white mb-2"><?php echo $gad_data['total_score']; ?></p>
                                <span class="inline-block px-4 py-2 rounded-full text-sm font-semibold <?php echo getRiskColor($gad_data['severity_level']); ?>">
                                    <?php echo $gad_data['severity_level']; ?>
                                </span>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-white">Last assessed:</p>
                                <p class="text-sm font-medium text-white">
                                    <?php echo date('M d, Y', strtotime($gad_data['created_at'])); ?>
                                </p>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-6">
                            <p class="text-[#706F4E] mb-3">No assessment data available</p>
                            <a href="MHScreening.php" class="inline-block px-6 py-2 bg-[#778970] text-white rounded-lg hover:bg-[#5D6A58] transition">
                                Take Assessment
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Depression Risk Card -->
                    <div class="bg-[#B9C5B4] rounded-2xl shadow-lg p-6 hover:shadow-xl transition-shadow">
                        <div class="flex items-center mb-3">
                            <img src="../src/ui/brain.png" alt="brain" class="w-[35px] h-[35px] mr-3">
                            <h3 class="text-lg font-semibold text-white">Depression Risk</h3>
                        </div>
                        <?php if ($phq_data): ?>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-5xl font-bold text-white mb-2"><?php echo $phq_data['total_score']; ?></p>
                                <span class="inline-block px-4 py-2 rounded-full text-sm font-semibold <?php echo getRiskColor($phq_data['severity_level']); ?>">
                                    <?php echo $phq_data['severity_level']; ?>
                                </span>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-white">Last assessed:</p>
                                <p class="text-sm font-medium text-white">
                                    <?php echo date('M d, Y', strtotime($phq_data['created_at'])); ?>
                                </p>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-6">
                            <p class="text-[#706F4E] mb-3">No assessment data available</p>
                            <a href="MHScreening.php" class="inline-block px-6 py-2 bg-[#778970] text-white rounded-lg hover:bg-[#5D6A58] transition">
                                Take Assessment
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Quick Access Section -->
            <div class="mb-8">
                <h2 class="text-3xl font-bold text-[#40350A] mb-6">Quick Access</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Mindful Journal -->
                    <a href="journal.php" class="group block bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition-all transform hover:-translate-y-1">
                        <div class="h-48 bg-gradient-to-br from-green-200 to-green-400 flex items-center justify-center relative overflow-hidden">
                            <img src="../src/banner/journal.gif" alt="Journal" class="w-full h-full object-cover opacity-70">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
                        </div>
                        <div class="p-6 bg-[#C5D1BE]">
                            <h3 class="text-xl font-bold text-[#40350A] text-center group-hover:text-[#778970] transition">
                                Mindful Journal
                            </h3>
                        </div>
                    </a>

                    <!-- Focus Mode -->
                    <a href="focus_mode.php" class="group block bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition-all transform hover:-translate-y-1">
                        <div class="h-48 bg-gradient-to-br from-blue-200 to-blue-400 flex items-center justify-center relative overflow-hidden">
                            <img src="../src/banner/focus.gif" alt="Focus" class="w-full h-full object-cover opacity-70">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
                        </div>
                        <div class="p-6 bg-[#C5D1BE]">
                            <h3 class="text-xl font-bold text-[#40350A] text-center group-hover:text-[#778970] transition">
                                Focus Mode
                            </h3>
                        </div>
                    </a>

                    <!-- Wellness Trends -->
                    <a href="user_trend.php" class="group block bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition-all transform hover:-translate-y-1">
                        <div class="h-48 bg-gradient-to-br from-purple-200 to-purple-400 flex items-center justify-center relative overflow-hidden">
                            <img src="../src/banner/trends.gif" alt="Trends" class="w-full h-full object-cover opacity-70">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
                        </div>
                        <div class="p-6 bg-[#C5D1BE]">
                            <h3 class="text-xl font-bold text-[#40350A] text-center group-hover:text-[#778970] transition">
                                Wellness Trends
                            </h3>
                        </div>
                    </a>
                    <!-- Generate Report -->
                    <a href="report.php" class="group block bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition-all transform hover:-translate-y-1">
                        <div class="h-48 bg-gradient-to-br from-orange-200 to-orange-400 flex items-center justify-center relative overflow-hidden">
                            <img src="../src/banner/report.gif" alt="Report" class="w-full h-full object-cover opacity-70">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
                        </div>
                        <div class="p-6 bg-[#C5D1BE]">
                            <h3 class="text-xl font-bold text-[#40350A] text-center group-hover:text-[#778970] transition">
                                Generate Report
                            </h3>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>