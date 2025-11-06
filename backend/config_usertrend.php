<?php
// trends.php
session_start();
require_once 'database.php'; // Your database connection

// Load environment variables from .env file (no Composer needed)
function loadEnv($path) {
    if (!file_exists($path)) {
        return;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue; // Skip comments
        }
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        // Remove quotes if present
        if (preg_match('/^(["\'])(.*)\\1$/', $value, $matches)) {
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

// Get selected week from query parameter or default to current week
$selected_week = isset($_GET['week']) ? $_GET['week'] : 'current';

// Calculate week date ranges
function getWeekRange($week_offset = 0) {
    $today = new DateTime();
    $today->modify("-$week_offset week");
    $start = clone $today;
    $start->modify('monday this week');
    $end = clone $start;
    $end->modify('+6 days');
    
    return [
        'start' => $start->format('Y-m-d'),
        'end' => $end->format('Y-m-d'),
        'label' => $start->format('M d') . ' - ' . $end->format('M d, Y')
    ];
}

// Get week ranges for dropdown
$weeks = [];
for ($i = 0; $i < 8; $i++) {
    $weeks[$i] = getWeekRange($i);
}

// Determine current and previous week based on selection
$week_index = $selected_week === 'current' ? 0 : (int)filter_var($selected_week, FILTER_SANITIZE_NUMBER_INT);
$current_week = $weeks[$week_index];
$previous_week = $weeks[$week_index + 1];

// ============================================
// FETCH DATA FUNCTIONS
// ============================================

function getPhqScores($pdo, $user_id, $start_date, $end_date) {
    $stmt = $pdo->prepare("
        SELECT total_score as score, created_at 
        FROM phq9_responses 
        WHERE user_id = ? AND DATE(created_at) BETWEEN ? AND ?
        ORDER BY created_at DESC
        LIMIT 1
    ");
    $stmt->execute([$user_id, $start_date, $end_date]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getGadScores($pdo, $user_id, $start_date, $end_date) {
    $stmt = $pdo->prepare("
        SELECT total_score as score, created_at 
        FROM gad7_responses 
        WHERE user_id = ? AND DATE(created_at) BETWEEN ? AND ?
        ORDER BY created_at DESC
        LIMIT 1
    ");
    $stmt->execute([$user_id, $start_date, $end_date]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getJournals($pdo, $user_id, $start_date, $end_date) {
    $stmt = $pdo->prepare("
        SELECT journal_id as id, journal_title as title, diary_text as content, emotion as mood, created_date as created_at 
        FROM journals 
        WHERE user_id = ? AND DATE(created_date) BETWEEN ? AND ?
        ORDER BY created_date DESC
    ");
    $stmt->execute([$user_id, $start_date, $end_date]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getChatMessages($pdo, $user_id, $start_date, $end_date) {
    $stmt = $pdo->prepare("
        SELECT message_content as message, message_type as sender, created_at 
        FROM chat_messages 
        WHERE user_id = ? AND DATE(created_at) BETWEEN ? AND ?
        ORDER BY created_at ASC
    ");
    $stmt->execute([$user_id, $start_date, $end_date]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

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
        
        // Count journals
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM journals WHERE user_id = ? AND DATE(created_date) BETWEEN ? AND ?");
        $stmt->execute([$user_id, $week['start'], $week['end']]);
        $journal_counts[] = (int)$stmt->fetchColumn();
        
        // Count chat sessions (unique days with messages)
        $stmt = $pdo->prepare("SELECT COUNT(DISTINCT DATE(created_at)) FROM chat_messages WHERE user_id = ? AND DATE(created_at) BETWEEN ? AND ?");
        $stmt->execute([$user_id, $week['start'], $week['end']]);
        $chat_counts[] = (int)$stmt->fetchColumn();
        
        // Count assessments
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

// ============================================
// FETCH CURRENT AND PREVIOUS WEEK DATA
// ============================================

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

// ============================================
// CLAUDE API ANALYSIS
// ============================================

function analyzeWithClaude($current_data, $previous_data) {
    $api_key = $_ENV['UT_API_KEY'];
    
    // Load instructions from file
    $instruction_file = __DIR__ . '/../instruction_usertrend.txt';
    if (!file_exists($instruction_file)) {
        error_log("Warning: instruction_trend.txt not found. Using default instructions.");
        $instructions = "You are a supportive mental wellness analyst. Analyze this user's data and provide insights.
        
**Instructions:**
1. Identify positive trends and improvements
2. Note any concerning patterns (gently and constructively)
3. Analyze journal sentiment and themes
4. Review chat interaction patterns for coping strategies
5. Provide personalized, actionable suggestions
6. Be encouraging and non-judgmental
7. Never diagnose or provide medical advice

**Format your response as:**
POSITIVE_PROGRESS: [Your observations about improvements]
KEY_OBSERVATIONS: [List 2-3 key patterns you notice]
SUGGESTIONS: [List 2-3 personalized suggestions]
REMINDERS: [Any gentle reminders about seeking professional help if needed]";
    } else {
        $instructions = file_get_contents($instruction_file);
    }
    
    // Prepare data summary for Claude
    $data_summary = [
        'current_week' => [
            'phq9_score' => $current_data['phq'] ? $current_data['phq']['score'] : 'Not taken',
            'gad7_score' => $current_data['gad'] ? $current_data['gad']['score'] : 'Not taken',
            'journal_count' => count($current_data['journals']),
            'journal_entries' => array_map(function($j) { 
                return [
                    'title' => $j['title'],
                    'emotion' => $j['mood'],
                    'excerpt' => substr($j['content'], 0, 200) . '...'
                ];
            }, array_slice($current_data['journals'], 0, 5)),
            'chat_interaction_count' => count($current_data['chats']),
            'chat_sample' => array_map(function($c) { 
                return [
                    'sender' => $c['sender'],
                    'message' => substr($c['message'], 0, 150)
                ];
            }, array_slice($current_data['chats'], 0, 10))
        ],
        'previous_week' => [
            'phq9_score' => $previous_data['phq'] ? $previous_data['phq']['score'] : 'Not taken',
            'gad7_score' => $previous_data['gad'] ? $previous_data['gad']['score'] : 'Not taken',
            'journal_count' => count($previous_data['journals'])
        ],
        'changes' => [
            'phq9_change' => $current_data['phq_change'],
            'gad7_change' => $current_data['gad_change'],
            'journal_change' => $current_data['journal_change']
        ]
    ];
    
    $prompt = $instructions . "\n\n**Data Summary:**\n" . json_encode($data_summary, JSON_PRETTY_PRINT);
    
    $data = [
        'model' => 'claude-sonnet-4-20250514',
        'max_tokens' => 1024,
        'messages' => [
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ]
    ];
    
    $ch = curl_init('https://api.anthropic.com/v1/messages');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'x-api-key: ' . $api_key,
        'anthropic-version: 2023-06-01'
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $result = json_decode($response, true);
    
    if (isset($result['content'][0]['text'])) {
        return parseClaudeResponse($result['content'][0]['text']);
    }
    
    return [
        'positive' => 'Unable to generate analysis at this time.',
        'observations' => [],
        'suggestions' => [],
        'reminders' => ''
    ];
}

function parseClaudeResponse($text) {
    $sections = [
        'positive' => '',
        'observations' => [],
        'suggestions' => [],
        'reminders' => ''
    ];
    
    // Simple parsing logic
    if (preg_match('/POSITIVE_PROGRESS:\s*(.+?)(?=KEY_OBSERVATIONS:|$)/s', $text, $matches)) {
        $sections['positive'] = trim($matches[1]);
    }
    
    if (preg_match('/KEY_OBSERVATIONS:\s*(.+?)(?=SUGGESTIONS:|$)/s', $text, $matches)) {
        $obs_text = trim($matches[1]);
        $sections['observations'] = array_filter(array_map('trim', explode("\n", $obs_text)));
    }
    
    if (preg_match('/SUGGESTIONS:\s*(.+?)(?=REMINDERS:|$)/s', $text, $matches)) {
        $sug_text = trim($matches[1]);
        $sections['suggestions'] = array_filter(array_map('trim', explode("\n", $sug_text)));
    }
    
    if (preg_match('/REMINDERS:\s*(.+?)$/s', $text, $matches)) {
        $sections['reminders'] = trim($matches[1]);
    }
    
    return $sections;
}

// Check if we need to generate analysis (only if requested or not cached)
$analysis = null;
if (isset($_GET['analyze']) || !isset($_SESSION['cached_analysis_' . $week_index])) {
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
    $_SESSION['cached_analysis_' . $week_index] = $analysis;
} else {
    $analysis = $_SESSION['cached_analysis_' . $week_index];
}

// ============================================
// HELPER FUNCTIONS FOR DISPLAY
// ============================================

function getScoreCategory($score, $type = 'phq') {
    if (!$score) return ['category' => 'N/A', 'emoji' => '📊'];
    
    $score = (int)$score;
    
    if ($type === 'phq') {
        if ($score <= 4) return ['category' => 'Minimal', 'emoji' => '😊'];
        if ($score <= 9) return ['category' => 'Mild', 'emoji' => '🙂'];
        if ($score <= 14) return ['category' => 'Moderate', 'emoji' => '😐'];
        if ($score <= 19) return ['category' => 'Moderately Severe', 'emoji' => '😟'];
        return ['category' => 'Severe', 'emoji' => '😢'];
    } else { // gad7
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
    // For PHQ and GAD, lower is better (positive change means score decreased)
    if ($change > 0) return 'text-green-600';
    if ($change < 0) return 'text-red-600';
    return 'text-gray-600';
}

$phq_info = getScoreCategory($current_phq ? $current_phq['score'] : null, 'phq');
$gad_info = getScoreCategory($current_gad ? $current_gad['score'] : null, 'gad');

?>