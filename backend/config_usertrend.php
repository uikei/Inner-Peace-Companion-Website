<?php

session_start();
require_once 'database.php';
require_once 'config_usertrend_functions.php';

// Load environment variables
function loadEnv($path) {
    if (!file_exists($path)) return;
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if (preg_match('/^(["\']).* \1$/', $value, $matches)) {
            $value = $matches[2];
        }
        if (!array_key_exists($name, $_ENV)) {
            $_ENV[$name] = $value;
            putenv("$name=$value");
        }
    }
}

loadEnv(__DIR__ . '/../.env');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$selected_week = isset($_GET['week']) ? $_GET['week'] : 'current';

// Get week ranges
$weeks = [];
for ($i = 0; $i < 8; $i++) {
    $weeks[$i] = getWeekRange($i);
}

$week_index = $selected_week === 'current' ? 0 : (int)filter_var($selected_week, FILTER_SANITIZE_NUMBER_INT);
$current_week = $weeks[$week_index];
$previous_week = $weeks[$week_index + 1];

// Helper functions for charts
function getHistoricalScores($pdo, $user_id, $weeks_back = 4) {
    $phq_scores = [];
    $gad_scores = [];
    $labels = [];
    
    for ($i = $weeks_back - 1; $i >= 0; $i--) {
        $week = getWeekRange($i);
        $labels[] = "Week " . ($weeks_back - $i);
        
        $phq = getPhqScores($pdo, $user_id, $week['start'], $week['end']);
        $gad = getGadScores($pdo, $user_id, $week['start'], $week['end']);
        
        $phq_scores[] = $phq ? (int)$phq['score'] : null;
        $gad_scores[] = $gad ? (int)$gad['score'] : null;
    }
    
    return [
        'labels' => $labels,
        'phq' => $phq_scores,
        'gad' => $gad_scores
    ];
}

function getActivityData($pdo, $user_id, $weeks_back = 4) {
    $journal_counts = [];
    $chat_counts = [];
    $assessment_counts = [];
    
    for ($i = $weeks_back - 1; $i >= 0; $i--) {
        $week = getWeekRange($i);
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM journals WHERE user_id = ? AND DATE(created_date) BETWEEN ? AND ?");
        $stmt->execute([$user_id, $week['start'], $week['end']]);
        $journal_counts[] = (int)$stmt->fetchColumn();
        
        $stmt = $pdo->prepare("SELECT COUNT(DISTINCT DATE(created_at)) FROM chat_messages WHERE user_id = ? AND DATE(created_at) BETWEEN ? AND ?");
        $stmt->execute([$user_id, $week['start'], $week['end']]);
        $chat_counts[] = (int)$stmt->fetchColumn();
        
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM (
                SELECT created_at FROM phq9_responses WHERE user_id = ? AND DATE(created_at) BETWEEN ? AND ?
                UNION ALL
                SELECT created_at FROM gad7_responses WHERE user_id = ? AND DATE(created_at) BETWEEN ? AND ?
            ) as assessments
        ");
        $stmt->execute([$user_id, $week['start'], $week['end'], $user_id, $week['start'], $week['end']]);
        $assessment_counts[] = (int)$stmt->fetchColumn();
    }
    
    return [
        'journals' => $journal_counts,
        'chats' => $chat_counts,
        'assessments' => $assessment_counts
    ];
}

// Fetch data
$current_phq = getPhqScores($pdo, $user_id, $current_week['start'], $current_week['end']);
$current_gad = getGadScores($pdo, $user_id, $current_week['start'], $current_week['end']);
$current_journals = getJournals($pdo, $user_id, $current_week['start'], $current_week['end']);
$current_chats = getChatMessages($pdo, $user_id, $current_week['start'], $current_week['end']);

$previous_phq = getPhqScores($pdo, $user_id, $previous_week['start'], $previous_week['end']);
$previous_gad = getGadScores($pdo, $user_id, $previous_week['start'], $previous_week['end']);
$previous_journals = getJournals($pdo, $user_id, $previous_week['start'], $previous_week['end']);

// Calculate changes
$phq_change = ($current_phq && $previous_phq) ? ((int)$previous_phq['score'] - (int)$current_phq['score']) : 0;
$gad_change = ($current_gad && $previous_gad) ? ((int)$previous_gad['score'] - (int)$current_gad['score']) : 0;
$journal_change = count($current_journals) - count($previous_journals);

// Get chart data
$historical_scores = getHistoricalScores($pdo, $user_id, 4);
$activity_data = getActivityData($pdo, $user_id, 4);

// Display helper functions
function getScoreCategory($score, $type = 'phq') {
    if (!$score) return ['category' => 'N/A', 'emoji' => '📊'];
    
    $score = (int)$score;
    
    if ($type === 'phq') {
        if ($score <= 4) return ['category' => 'Minimal', 'emoji' => '😊'];
        if ($score <= 9) return ['category' => 'Mild', 'emoji' => '🙂'];
        if ($score <= 14) return ['category' => 'Moderate', 'emoji' => '😐'];
        if ($score <= 19) return ['category' => 'Moderately Severe', 'emoji' => '😟'];
        return ['category' => 'Severe', 'emoji' => '😢'];
    } else {
        if ($score <= 4) return ['category' => 'Minimal', 'emoji' => '😊'];
        if ($score <= 9) return ['category' => 'Mild', 'emoji' => '🙂'];
        if ($score <= 14) return ['category' => 'Moderate', 'emoji' => '😐'];
        return ['category' => 'Severe', 'emoji' => '😟'];
    }
}

function getTrendIcon($change) {
    if ($change > 0) {
        return '<svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>';
    } elseif ($change < 0) {
        return '<svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>';
    }
    return '<span class="mr-1">→</span>';
}

function getTrendColor($change) {
    if ($change > 0) return 'text-green-600';
    if ($change < 0) return 'text-red-600';
    return 'text-gray-600';
}

$phq_info = getScoreCategory($current_phq ? $current_phq['score'] : null, 'phq');
$gad_info = getScoreCategory($current_gad ? $current_gad['score'] : null, 'gad');

?>