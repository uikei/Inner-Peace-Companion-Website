<?php
require_once 'config_diary.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle GET request (view journal)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get') {
    if (!isset($_GET['id'])) {
        echo json_encode(['success' => false, 'message' => 'Journal ID required']);
        exit;
    }

    $journal_id = $_GET['id'];
    
    try {
        // Check that journal belongs to current user
        $stmt = $pdo->prepare("SELECT * FROM journals WHERE journal_id = ? AND user_id = ?");
        $stmt->execute([$journal_id, $user_id]);
        $journal = $stmt->fetch();

        if ($journal) {
            echo json_encode(['success' => true, 'journal' => $journal]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Journal not found']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// Handle POST request (create journal)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $title = trim($_POST['title'] ?? '');
    $emotion = trim($_POST['emotion'] ?? '');
    $text = trim($_POST['text'] ?? '');

    // Validation
    if (empty($title) || empty($emotion) || empty($text)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }

    // Validate emotion
    $validEmotions = ['happy', 'sad', 'angry', 'anxious'];
    if (!in_array($emotion, $validEmotions)) {
        echo json_encode(['success' => false, 'message' => 'Invalid emotion']);
        exit;
    }

    try {
        // Check if user already has a journal today (using transaction for safety)
        $pdo->beginTransaction();
        
        $today = date('Y-m-d H:i:s', strtotime('today'));
        $tomorrow = date('Y-m-d H:i:s', strtotime('tomorrow'));
        
        $checkStmt = $pdo->prepare("SELECT journal_id FROM journals WHERE user_id = ? AND created_date >= ? AND created_date < ? LIMIT 1");
        $checkStmt->execute([$user_id, $today, $tomorrow]);
        
        if ($checkStmt->fetch()) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'You have already written a journal today!']);
            exit;
        }

        // Insert new journal
        $stmt = $pdo->prepare("INSERT INTO journals (user_id, journal_title, emotion, diary_text, created_date) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$user_id, $title, $emotion, $text]);

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Journal saved successfully', 'journal_id' => $pdo->lastInsertId()]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);
?>