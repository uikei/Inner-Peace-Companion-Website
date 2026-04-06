<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

require_once __DIR__ . '/../backend/env.php';

// NEW: Include the AWS SDK Autoloader
// (This requires running `composer require aws/aws-sdk-php` in the /var/www/html folder first)
require_once __DIR__ . '/../vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

header('Content-Type: application/json');

try {
    $pdo = new PDO(
        "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_DATABASE']};charset=utf8mb4",
        $_ENV['DB_USERNAME'],
        $_ENV['DB_PASSWORD']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $user_id = $_SESSION['user_id'];
    
    if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] === UPLOAD_ERR_NO_FILE) {
        echo json_encode(['success' => false, 'message' => 'No file uploaded']);
        exit();
    }
    if ($_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'File upload error']);
        exit();
    }
    
    $file = $_FILES['profile_picture'];
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowed_types)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file type.']);
        exit();
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'File size must be less than 5MB.']);
        exit();
    }
    
    // --- AWS S3 UPLOAD LOGIC BEGINS HERE ---
    
    // Set your precise bucket name and region here!
    $bucketName = 'innerpeace-upload'; // <<< CHANGE THIS
    $region = 'us-east-1'; // <<< CHANGE IF DIFFERENT

    // Instantiate the S3 client using the IAM Role credentials automatically
    $s3Client = new S3Client([
        'region'  => $region,
        'version' => 'latest'
    ]);

    // Generate unique filename structure inside the bucket
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'profile_pictures/profile_' . $user_id . '_' . time() . '.' . $file_extension;
    $temp_file_path = $file['tmp_name'];

    try {
        // Push the file directly to S3
        $result = $s3Client->putObject([
            'Bucket'     => $bucketName,
            'Key'        => $filename,
            'SourceFile' => $temp_file_path
            // Note: No 'ACL' here, because the Bucket Policy handles public viewing!
        ]);

        // Get the official public S3 Image URL from AWS
        $s3_image_url = $result->get('ObjectURL');

    } catch (AwsException $e) {
        error_log($e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error uploading to AWS S3.']);
        exit();
    }
    // --- AWS S3 UPLOAD LOGIC ENDS HERE ---

    
    // Update database with the new S3 URL instead of local path
    $stmt = $pdo->prepare("UPDATE signup_web SET profile_picture = ? WHERE user_id = ?");
    $stmt->execute([$s3_image_url, $user_id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Profile picture updated securely in S3!',
        'profile_picture' => $s3_image_url
    ]);
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error. Please try again.']);
    exit();
}
?>
