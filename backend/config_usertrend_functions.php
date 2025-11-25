<?php
// Shared functions for user trend analysis

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

function analyzeWithClaude($current_data, $previous_data) {
    $api_key = $_ENV['UT_API_KEY'];
    
    // Load instructions from file
    $instruction_file = __DIR__ . '/../instruction_usertrend.txt';
    if (!file_exists($instruction_file)) {
        $instructions = "You are a supportive mental wellness analyst. Analyze this user's data and provide insights.";
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
?>
