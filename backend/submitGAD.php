<?php
session_start();
header('Content-Type: application/json');
require_once 'config_mentalhealth.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

// Validate all required fields
$required = ['q1', 'q2', 'q3', 'q4', 'q5', 'q6', 'q7'];
foreach ($required as $field) {
    if (!isset($input[$field])) {
        echo json_encode(['success' => false, 'message' => "Missing field: $field"]);
        exit;
    }
}

// Get user_id from session
$user_id = $_SESSION['user_id'] ?? null;

// Calculate total score
$total = 0;
for ($i = 1; $i <= 7; $i++) {
    $total += (int)$input["q$i"];
}

// Determine severity level
if ($total <= 4) {
    $severity = 'Minimal';
} elseif ($total <= 9) {
    $severity = 'Mild';
} elseif ($total <= 14) {
    $severity = 'Moderate';
} else {
    $severity = 'Severe';
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO gad7_responses 
        (user_id, q1, q2, q3, q4, q5, q6, q7, total_score, severity_level) 
        VALUES 
        (:user_id, :q1, :q2, :q3, :q4, :q5, :q6, :q7, :total_score, :severity_level)
    ");
    
    $stmt->execute([
        ':user_id' => $user_id,
        ':q1' => $input['q1'],
        ':q2' => $input['q2'],
        ':q3' => $input['q3'],
        ':q4' => $input['q4'],
        ':q5' => $input['q5'],
        ':q6' => $input['q6'],
        ':q7' => $input['q7'],
        ':total_score' => $total,
        ':severity_level' => $severity
    ]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'GAD-7 saved successfully',
        'score' => $total,
        'severity' => $severity
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
