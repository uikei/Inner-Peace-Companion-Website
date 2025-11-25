<?php
session_start();
require_once 'database.php';

// Load environment variables
function loadEnv($path) {
    if (!file_exists($path)) {
        return;
    }
    
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

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit();
}

$week_index = isset($_GET['week_index']) ? (int)$_GET['week_index'] : 0;

// Include the analysis functions from config_usertrend.php
require_once 'config_usertrend_functions.php';

// Get data
$user_id = $_SESSION['user_id'];
$weeks = [];
for ($i = 0; $i < 8; $i++) {
    $weeks[$i] = getWeekRange($i);
}

$current_week = $weeks[$week_index];
$previous_week = $weeks[$week_index + 1];

$current_phq = getPhqScores($pdo, $user_id, $current_week['start'], $current_week['end']);
$current_gad = getGadScores($pdo, $user_id, $current_week['start'], $current_week['end']);
$current_journals = getJournals($pdo, $user_id, $current_week['start'], $current_week['end']);
$current_chats = getChatMessages($pdo, $user_id, $current_week['start'], $current_week['end']);

$previous_phq = getPhqScores($pdo, $user_id, $previous_week['start'], $previous_week['end']);
$previous_gad = getGadScores($pdo, $user_id, $previous_week['start'], $previous_week['end']);
$previous_journals = getJournals($pdo, $user_id, $previous_week['start'], $previous_week['end']);

$phq_change = ($current_phq && $previous_phq) ? ((int)$previous_phq['score'] - (int)$current_phq['score']) : 0;
$gad_change = ($current_gad && $previous_gad) ? ((int)$previous_gad['score'] - (int)$current_gad['score']) : 0;
$journal_change = count($current_journals) - count($previous_journals);

// Generate analysis
$analysis_data = [
    'phq' => $current_phq,
    'gad' => $current_gad,
    'journals' => $current_journals,
    'chats' => $current_chats,
    'phq_change' => $phq_change,
    'gad_change' => $gad_change,
    'journal_change' => $journal_change
];

$previous_data = [
    'phq' => $previous_phq,
    'gad' => $previous_gad,
    'journals' => $previous_journals
];

$analysis = analyzeWithClaude($analysis_data, $previous_data);

echo json_encode([
    'success' => true,
    'analysis' => $analysis
]);
?>
