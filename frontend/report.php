<?php
session_start();
require_once '../backend/database.php';

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

// Get report parameters
$report_type = isset($_POST['report_type']) ? $_POST['report_type'] : 'monthly';
$generated_report = null;

// Helper functions
function getDateRange($type) {
    $end = new DateTime();
    $start = clone $end;
    
    switch($type) {
        case 'weekly':
            $start->modify('-7 days');
            break;
        case 'biweekly':
            $start->modify('-14 days');
            break;
        case 'monthly':
            $start->modify('-30 days');
            break;
        case 'quarterly':
            $start->modify('-90 days');
            break;
        default:
            $start->modify('-30 days');
    }
    
    return [
        'start' => $start->format('Y-m-d'),
        'end' => $end->format('Y-m-d'),
        'label' => $start->format('M d, Y') . ' - ' . $end->format('M d, Y')
    ];
}

function getUserData($pdo, $user_id, $start_date, $end_date) {
    // Get PHQ-9 scores
    $stmt = $pdo->prepare("
        SELECT total_score, severity_level, created_at 
        FROM phq9_responses 
        WHERE user_id = ? AND DATE(created_at) BETWEEN ? AND ?
        ORDER BY created_at ASC
    ");
    $stmt->execute([$user_id, $start_date, $end_date]);
    $phq_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get GAD-7 scores
    $stmt = $pdo->prepare("
        SELECT total_score, severity_level, created_at 
        FROM gad7_responses 
        WHERE user_id = ? AND DATE(created_at) BETWEEN ? AND ?
        ORDER BY created_at ASC
    ");
    $stmt->execute([$user_id, $start_date, $end_date]);
    $gad_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get journals
    $stmt = $pdo->prepare("
        SELECT journal_title, diary_text, emotion, created_date 
        FROM journals 
        WHERE user_id = ? AND DATE(created_date) BETWEEN ? AND ?
        ORDER BY created_date ASC
    ");
    $stmt->execute([$user_id, $start_date, $end_date]);
    $journals = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get chat messages
    $stmt = $pdo->prepare("
        SELECT message_content, message_type, created_at 
        FROM chat_messages 
        WHERE user_id = ? AND DATE(created_at) BETWEEN ? AND ?
        ORDER BY created_at ASC
    ");
    $stmt->execute([$user_id, $start_date, $end_date]);
    $chats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get user info
    $stmt = $pdo->prepare("SELECT user_username FROM signup_web WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user_info = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return [
        'phq9' => $phq_data,
        'gad7' => $gad_data,
        'journals' => $journals,
        'chats' => $chats,
        'username' => $user_info['user_username'] ?? 'User'
    ];
}

function generateReportWithClaude($user_data, $date_range, $report_type) {
    $api_key = $_ENV['GR_API_KEY'];
    
    // Load instructions from file
    $instruction_file = __DIR__ . '/../instruction_report.txt';
    if (!file_exists($instruction_file)) {
        error_log("Warning: instruction_report.txt not found. Using default instructions.");
        $instructions = "You are a compassionate mental health report writer. Generate a comprehensive, professional mental wellness report.

Write a comprehensive essay-style report (1000-1500 words) analyzing the user's mental health data. Include:
1. Executive Summary
2. Mental Health Assessment Analysis
3. Emotional and Behavioral Patterns
4. Support System Engagement
5. Key Insights and Observations
6. Recommendations and Next Steps

Be empathetic, professional, avoid diagnosis, and recommend professional help when appropriate.";
    } else {
        $instructions = file_get_contents($instruction_file);
    }
    
    // Prepare comprehensive data summary
    $phq_summary = !empty($user_data['phq9']) ? [
        'count' => count($user_data['phq9']),
        'average' => round(array_sum(array_column($user_data['phq9'], 'total_score')) / count($user_data['phq9']), 1),
        'trend' => analyzeTrend(array_column($user_data['phq9'], 'total_score')),
        'scores' => array_map(function($item) {
            return ['score' => $item['total_score'], 'severity' => $item['severity_level'], 'date' => $item['created_at']];
        }, $user_data['phq9'])
    ] : ['count' => 0, 'average' => 'N/A', 'trend' => 'No data'];
    
    $gad_summary = !empty($user_data['gad7']) ? [
        'count' => count($user_data['gad7']),
        'average' => round(array_sum(array_column($user_data['gad7'], 'total_score')) / count($user_data['gad7']), 1),
        'trend' => analyzeTrend(array_column($user_data['gad7'], 'total_score')),
        'scores' => array_map(function($item) {
            return ['score' => $item['total_score'], 'severity' => $item['severity_level'], 'date' => $item['created_at']];
        }, $user_data['gad7'])
    ] : ['count' => 0, 'average' => 'N/A', 'trend' => 'No data'];
    
    // Journal analysis
    $journal_summary = [
        'count' => count($user_data['journals']),
        'emotions' => array_count_values(array_column($user_data['journals'], 'emotion')),
        'sample_entries' => array_slice(array_map(function($j) {
            return [
                'title' => $j['journal_title'],
                'emotion' => $j['emotion'],
                'excerpt' => substr($j['diary_text'], 0, 300),
                'date' => $j['created_date']
            ];
        }, $user_data['journals']), 0, 10)
    ];
    
    // Chat analysis
    $chat_summary = [
        'total_messages' => count($user_data['chats']),
        'user_messages' => count(array_filter($user_data['chats'], function($c) { return $c['message_type'] === 'user'; })),
        'sample_conversations' => array_slice(array_map(function($c) {
            return [
                'sender' => $c['message_type'],
                'message' => substr($c['message_content'], 0, 200),
                'date' => $c['created_at']
            ];
        }, $user_data['chats']), 0, 20)
    ];
    
    $prompt = $instructions . "

REPORT DETAILS:
- Username: {$user_data['username']}
- Report Period: {$date_range['label']}
- Report Type: " . ucfirst($report_type) . " Report

DATA SUMMARY:

Depression Assessment (PHQ-9):
- Total Assessments: {$phq_summary['count']}
- Average Score: {$phq_summary['average']}
- Trend: {$phq_summary['trend']}
- Detailed Scores: " . json_encode($phq_summary['scores']) . "

Anxiety Assessment (GAD-7):
- Total Assessments: {$gad_summary['count']}
- Average Score: {$gad_summary['average']}
- Trend: {$gad_summary['trend']}
- Detailed Scores: " . json_encode($gad_summary['scores']) . "

Journal Activity:
- Total Entries: {$journal_summary['count']}
- Emotion Distribution: " . json_encode($journal_summary['emotions']) . "
- Sample Entries: " . json_encode($journal_summary['sample_entries']) . "

Chatbot Interactions:
- Total Messages: {$chat_summary['total_messages']}
- User Initiated: {$chat_summary['user_messages']}
- Sample Conversations: " . json_encode($chat_summary['sample_conversations']) . "

Generate the comprehensive wellness report now:";

    $data = [
        'model' => 'claude-sonnet-4-20250514',
        'max_tokens' => 4096,
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
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code !== 200) {
        error_log("Claude API Error: " . $response);
        return "Unable to generate report at this time. Please try again later.\n\nError: " . $response;
    }
    
    $result = json_decode($response, true);
    
    if (isset($result['content'][0]['text'])) {
        return $result['content'][0]['text'];
    }
    
    return "Unable to generate report. Please check your API configuration.";
}

function analyzeTrend($scores) {
    if (count($scores) < 2) return 'Insufficient data';
    
    $first_half = array_slice($scores, 0, ceil(count($scores) / 2));
    $second_half = array_slice($scores, ceil(count($scores) / 2));
    
    $avg_first = array_sum($first_half) / count($first_half);
    $avg_second = array_sum($second_half) / count($second_half);
    
    $diff = $avg_first - $avg_second;
    
    if ($diff > 2) return 'Improving (scores decreasing)';
    if ($diff < -2) return 'Worsening (scores increasing)';
    return 'Stable';
}

function formatMarkdownToHTML($text) {
    // Convert markdown headers to HTML with proper styling
    // ### Header (h3) - largest sub-heading
    $text = preg_replace('/^### (.+)$/m', '<h3 style="font-size: 1.5em; font-weight: bold; margin-top: 1.5em; margin-bottom: 0.5em; color: #40350A;">$1</h3>', $text);
    
    // ## Header (h2) - larger heading
    $text = preg_replace('/^## (.+)$/m', '<h2 style="font-size: 1.75em; font-weight: bold; margin-top: 1.5em; margin-bottom: 0.5em; color: #40350A;">$1</h2>', $text);
    
    // # Header (h1) - main heading
    $text = preg_replace('/^# (.+)$/m', '<h1 style="font-size: 2em; font-weight: bold; margin-top: 1.5em; margin-bottom: 0.5em; color: #40350A;">$1</h1>', $text);
    
    // Convert **bold** to <strong>
    $text = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $text);
    
    // Convert *italic* to <em>
    $text = preg_replace('/\*(.+?)\*/s', '<em>$1</em>', $text);
    
    return $text;
}

// Generate report if requested
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_report'])) {
    $date_range = getDateRange($report_type);
    $user_data = getUserData($pdo, $user_id, $date_range['start'], $date_range['end']);
    
    $generated_report = [
        'content' => generateReportWithClaude($user_data, $date_range, $report_type),
        'date_range' => $date_range,
        'report_type' => $report_type,
        'generated_at' => date('F d, Y h:i A'),
        'data_summary' => [
            'phq_count' => count($user_data['phq9']),
            'gad_count' => count($user_data['gad7']),
            'journal_count' => count($user_data['journals']),
            'chat_count' => count($user_data['chats'])
        ]
    ];
    
    // Store in session for download
    $_SESSION['latest_report'] = $generated_report;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Wellness Report</title>
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
        @media print {
            .no-print { display: none; }
            .main-content { margin: 0; padding: 20px; }
        }
    </style>
</head>
<body class="bg-[#EAEEEB] min-h-screen">
    <?php require 'header.php'; ?>
    <?php require 'sidebar.php'; ?>
    <?php require 'chatbot.php'; ?>
    
    <div class="main-content">
        <div class="container mx-auto px-4 py-8 max-w-5xl">
            <!-- Header -->
            <div class="mb-8 no-print">
                <h1 class="text-4xl font-bold text-[#40350A] mb-2">Wellness Report Generator</h1>
                <p class="text-[#706F4E]">Generate a comprehensive analysis of your mental wellness journey using Claude AI</p>
            </div>

            <!-- Report Generation Form -->
            <?php if (!$generated_report): ?>
            <div class="bg-white rounded-xl shadow-md p-8 mb-6">
                <h2 class="text-2xl font-semibold text-[#40350A] mb-6">Select Report Type</h2>
                <form method="POST" action="" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-[#40350A] mb-3">Report Period</label>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <label class="relative flex items-center justify-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-[#778970] transition">
                                <input type="radio" name="report_type" value="weekly" class="sr-only peer" checked>
                                <div class="text-center peer-checked:text-[#778970]">
                                    <svg class="w-8 h-8 mx-auto mb-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"/>
                                    </svg>
                                    <span class="font-medium">Weekly</span>
                                    <p class="text-xs text-gray-500 mt-1">Last 7 days</p>
                                </div>
                                <div class="absolute inset-0 border-2 border-[#778970] rounded-lg hidden peer-checked:block"></div>
                            </label>

                            <label class="relative flex items-center justify-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-[#778970] transition">
                                <input type="radio" name="report_type" value="biweekly" class="sr-only peer">
                                <div class="text-center peer-checked:text-[#778970]">
                                    <svg class="w-8 h-8 mx-auto mb-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"/>
                                    </svg>
                                    <span class="font-medium">Bi-weekly</span>
                                    <p class="text-xs text-gray-500 mt-1">Last 14 days</p>
                                </div>
                                <div class="absolute inset-0 border-2 border-[#778970] rounded-lg hidden peer-checked:block"></div>
                            </label>

                            <label class="relative flex items-center justify-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-[#778970] transition">
                                <input type="radio" name="report_type" value="monthly" class="sr-only peer">
                                <div class="text-center peer-checked:text-[#778970]">
                                    <svg class="w-8 h-8 mx-auto mb-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"/>
                                    </svg>
                                    <span class="font-medium">Monthly</span>
                                    <p class="text-xs text-gray-500 mt-1">Last 30 days</p>
                                </div>
                                <div class="absolute inset-0 border-2 border-[#778970] rounded-lg hidden peer-checked:block"></div>
                            </label>

                            <label class="relative flex items-center justify-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-[#778970] transition">
                                <input type="radio" name="report_type" value="quarterly" class="sr-only peer">
                                <div class="text-center peer-checked:text-[#778970]">
                                    <svg class="w-8 h-8 mx-auto mb-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"/>
                                    </svg>
                                    <span class="font-medium">Quarterly</span>
                                    <p class="text-xs text-gray-500 mt-1">Last 90 days</p>
                                </div>
                                <div class="absolute inset-0 border-2 border-[#778970] rounded-lg hidden peer-checked:block"></div>
                            </label>
                        </div>
                    </div>

                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4">
                        <div class="flex">
                            <svg class="w-5 h-5 text-blue-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            <div>
                                <h3 class="text-sm font-medium text-blue-800 mb-1">What's included in your report:</h3>
                                <ul class="text-sm text-blue-700 space-y-1">
                                    <li>• Comprehensive analysis of mental health assessments (PHQ-9 & GAD-7)</li>
                                    <li>• Journal entry patterns and emotional themes</li>
                                    <li>• Chatbot interaction insights and coping strategies</li>
                                    <li>• Personalized recommendations and next steps</li>
                                    <li>• AI-powered insights using Claude Sonnet 4</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="generate_report" value="1">
                    <button type="submit" id="generateReportBtn" class="w-full bg-[#778970] text-white py-4 rounded-lg font-semibold hover:bg-[#5D6A58] transition transform hover:-translate-y-0.5 hover:shadow-lg flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
                        <svg class="w-5 h-5 mr-2" id="reportIcon" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd"/>
                        </svg>
                        <div class="hidden animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2" id="reportSpinner"></div>
                        <span id="reportBtnText">Generate My Wellness Report</span>
                    </button>
                </form>
            </div>
            <?php endif; ?>

            <!-- Generated Report Display -->
            <?php if ($generated_report): ?>
            <div class="space-y-6">
                <!-- Report Header -->
                <div class="bg-white rounded-xl shadow-md p-8 no-print">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-2xl font-bold text-[#40350A]"><?php echo ucfirst($generated_report['report_type']); ?> Wellness Report</h2>
                            <p class="text-[#706F4E] mt-1"><?php echo $generated_report['date_range']['label']; ?></p>
                            <p class="text-sm text-gray-500 mt-1">Generated on <?php echo $generated_report['generated_at']; ?></p>
                        </div>
                        <div class="flex gap-3">
                            <a href="../backend/download_report_pdf.php" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                                Download PDF
                            </a>
                            <a href="report.php" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                                Generate New Report
                            </a>
                        </div>
                    </div>
                    
                    <!-- Data Summary -->
                    <div class="grid grid-cols-4 gap-4 mt-6">
                        <div class="text-center p-4 bg-blue-50 rounded-lg">
                            <p class="text-3xl font-bold text-blue-600"><?php echo $generated_report['data_summary']['phq_count']; ?></p>
                            <p class="text-sm text-gray-600">PHQ-9 Assessments</p>
                        </div>
                        <div class="text-center p-4 bg-purple-50 rounded-lg">
                            <p class="text-3xl font-bold text-purple-600"><?php echo $generated_report['data_summary']['gad_count']; ?></p>
                            <p class="text-sm text-gray-600">GAD-7 Assessments</p>
                        </div>
                        <div class="text-center p-4 bg-green-50 rounded-lg">
                            <p class="text-3xl font-bold text-green-600"><?php echo $generated_report['data_summary']['journal_count']; ?></p>
                            <p class="text-sm text-gray-600">Journal Entries</p>
                        </div>
                        <div class="text-center p-4 bg-indigo-50 rounded-lg">
                            <p class="text-3xl font-bold text-indigo-600"><?php echo $generated_report['data_summary']['chat_count']; ?></p>
                            <p class="text-sm text-gray-600">Chat Messages</p>
                        </div>
                    </div>
                </div>

                <!-- Report Content -->
                <div class="bg-white rounded-xl shadow-md p-12">
                    <div class="prose prose-lg max-w-none">
                        <?php 
                            $formatted_content = formatMarkdownToHTML($generated_report['content']);
                            echo nl2br(htmlspecialchars_decode($formatted_content)); 
                        ?>
                    </div>
                </div>

                <!-- Footer -->
                <div class="bg-gray-50 rounded-xl p-6 text-center no-print">
                    <p class="text-sm text-gray-600">
                        This report is for informational purposes only and is not a substitute for professional medical advice, diagnosis, or treatment.
                        If you're experiencing severe symptoms, please consult with a qualified mental health professional.
                    </p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <script>
        // Report generation loading state
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('#generateReportBtn')?.closest('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const btn = document.getElementById('generateReportBtn');
                    const icon = document.getElementById('reportIcon');
                    const spinner = document.getElementById('reportSpinner');
                    const btnText = document.getElementById('reportBtnText');
                    
                    if (btn && icon && spinner && btnText) {
                        // Disable button and show loading state
                        btn.disabled = true;
                        icon.classList.add('hidden');
                        spinner.classList.remove('hidden');
                        btnText.textContent = 'Generating Report...';
                    }
                });
            }
        });
    </script>
</body>
</html>