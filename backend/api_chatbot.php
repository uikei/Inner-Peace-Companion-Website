<?php
require_once 'config_chatbot.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

switch ($action) {
    case 'send_message':
        handleSendMessage($input);
        break;
    case 'get_history':
        handleGetHistory();
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
}

function handleSendMessage($input) {
    $message = trim($input['message'] ?? '');
    
    if (empty($message)) {
        http_response_code(400);
        echo json_encode(['error' => 'Message is required']);
        return;
    }
    
    $sessionId = getSessionId();
    $pdo = getDBConnection();
    
    if (!$pdo) {
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed']);
        return;
    }
    
    // Save user message
    $stmt = $pdo->prepare("
        INSERT INTO chat_messages (session_id, message_type, message_content) 
        VALUES (?, 'user', ?)
    ");
    $stmt->execute([$sessionId, $message]);
    
    // Get conversation history for context
    $history = getConversationHistory($pdo, $sessionId, 20);
    
    // Call Claude API with therapy instructions
    $botResponse = callClaudeAPI($message, $history);
    
    if ($botResponse['success']) {
        // Save bot response
        $stmt = $pdo->prepare("
            INSERT INTO chat_messages (session_id, message_type, message_content) 
            VALUES (?, 'bot', ?)
        ");
        $stmt->execute([$sessionId, $botResponse['message']]);
        
        echo json_encode([
            'success' => true,
            'message' => $botResponse['message']
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $botResponse['error']
        ]);
    }
}

function handleGetHistory() {
    $sessionId = getSessionId();
    $pdo = getDBConnection();
    
    if (!$pdo) {
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed']);
        return;
    }
    
    $history = getConversationHistory($pdo, $sessionId, 50);
    echo json_encode(['success' => true, 'history' => $history]);
}

function getConversationHistory($pdo, $sessionId, $limit = 10) {
    $stmt = $pdo->prepare("
        SELECT message_type, message_content, created_at 
        FROM chat_messages 
        WHERE session_id = ? 
        ORDER BY created_at DESC 
        LIMIT ?
    ");
    $stmt->execute([$sessionId, $limit]);
    return array_reverse($stmt->fetchAll());
}

function callClaudeAPI($message, $history = []) {
    // Build messages array for Claude with therapy context
    $messages = [];
    
    // Add conversation history
    foreach ($history as $msg) {
        $messages[] = [
            'role' => $msg['message_type'] === 'user' ? 'user' : 'assistant',
            'content' => $msg['message_content']
        ];
    }
    
    // Add current message if not already in history
    if (empty($messages) || end($messages)['content'] !== $message) {
        $messages[] = [
            'role' => 'user',
            'content' => $message
        ];
    }
    
    // Prepare API request with system instructions
    $data = [
        'model' => CLAUDE_MODEL,
        'max_tokens' => 1024,
        'system' => THERAPY_INSTRUCTIONS, 
        'messages' => $messages
    ];
    
    $ch = curl_init(CLAUDE_API_URL);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'x-api-key: ' . CLAUDE_API_KEY,
            'anthropic-version: 2023-06-01'
        ],
        CURLOPT_TIMEOUT => 45
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        error_log("Claude API Error: " . $error);
        return [
            'success' => false,
            'error' => 'Failed to connect to AI service'
        ];
    }
    
    $result = json_decode($response, true);
    
    if ($httpCode === 200 && isset($result['content'][0]['text'])) {
        return [
            'success' => true,
            'message' => $result['content'][0]['text']
        ];
    } else {
        error_log("Claude API Response Error: " . $response);
        return [
            'success' => false,
            'error' => $result['error']['message'] ?? 'AI service error'
        ];
    }
}
?>