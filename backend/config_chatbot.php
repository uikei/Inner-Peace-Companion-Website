<?php
// Load environment variables from .env file
function loadEnv($path) {
    if (!file_exists($path)) {
        die('Error: .env file not found');
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        if (!array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
        }
    }
}

loadEnv(__DIR__ . '/../.env');

define('DB_HOST', 'localhost');
define('DB_PORT', '3306'); 
define('DB_NAME', 'innerpeacecomp_web');
define('DB_USER', 'root'); // remove root **yukie**
define('DB_PASS', 'root'); // same

// Claude API configuration
define('CLAUDE_API_KEY', getenv('API_KEY'));
define('CLAUDE_API_URL', 'https://api.anthropic.com/v1/messages');
define('CLAUDE_MODEL', 'claude-3-7-sonnet-20250219');

// Load therapy instructions
$instructionsFile = __DIR__ . '/../instruction_chatbot.txt';
if (file_exists($instructionsFile)) {
    define('THERAPY_INSTRUCTIONS', file_get_contents($instructionsFile));
} else {
    define('THERAPY_INSTRUCTIONS', 'You are a compassionate and professional AI therapy assistant. Listen actively, provide empathetic responses, and offer supportive guidance.');
}

// Session configuration
session_start();

// Database connection 
function getDBConnection() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        return null;
    }
}

// Generate or retrieve session ID
function getSessionId() {
    if (!isset($_SESSION['chatbot_session_id'])) {
        $_SESSION['chatbot_session_id'] = bin2hex(random_bytes(16));
        
        // Create session in database
        $pdo = getDBConnection();
        if ($pdo) {
            $user_id = $_SESSION['user_id'] ?? null;
            $stmt = $pdo->prepare("
                INSERT INTO chat_sessions (session_id, user_id, user_ip, user_agent) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $_SESSION['chatbot_session_id'],
                $user_id,
                $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ]);
        }
    }
    return $_SESSION['chatbot_session_id'];
}
?>

