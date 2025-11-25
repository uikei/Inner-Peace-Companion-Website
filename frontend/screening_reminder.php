<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['needs_screening' => false]);
    exit();
}

$user_id = $_SESSION['user_id'];
$host = 'localhost';
$dbname = 'innerpeacecomp_web';
$db_username = 'root';
$db_password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $db_username, $db_password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if user has completed screening in the last week
    $stmt = $pdo->prepare("
        SELECT MAX(created_at) as last_screening 
        FROM (
            SELECT created_at FROM phq9_responses WHERE user_id = ?
            UNION ALL
            SELECT created_at FROM gad7_responses WHERE user_id = ?
        ) as screenings
    ");
    $stmt->execute([$user_id, $user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $last_screening = $result['last_screening'];
    $needs_screening = true;
    $days_passed = 0;
    
    if ($last_screening) {
        $last_date = new DateTime($last_screening);
        $today = new DateTime();
        $interval = $today->diff($last_date);
        $days_passed = $interval->days;
        
        // User must wait 7 days (1 week) between screenings
        if ($days_passed < 7) {
            $needs_screening = false;
        }
    }
    
    echo json_encode([
        'needs_screening' => $needs_screening,
        'days_passed' => $days_passed,
        'last_screening' => $last_screening
    ]);
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['needs_screening' => false, 'error' => true]);
}
?>
