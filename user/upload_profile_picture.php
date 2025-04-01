<?php
error_reporting(E_ALL);
ini_set('display_errors', 0); 
header('Content-Type: application/json');

session_start();
require_once '../incldues/db_config.php';
require_once '../incldues/auth.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die(json_encode(['success' => false, 'message' => 'Not authenticated']));
}

try {
    if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('No file uploaded or upload error');
    }

    $file = $_FILES['profile_picture'];
    
    $maxSize = 2 * 1024 * 1024;
    if ($file['size'] > $maxSize) {
        throw new Exception('File too large (max 2MB allowed)');
    }
    
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    
    if (!in_array($mime, $allowedTypes)) {
        throw new Exception('Only JPG, PNG, and GIF images are allowed');
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'user_' . $_SESSION['user_id'] . '_' . uniqid() . '.' . $ext;
    $uploadPath = 'uploads/profile_pictures/' . $filename;
    
    if (!file_exists('uploads/profile_pictures')) {
        mkdir('uploads/profile_pictures', 0755, true);
    }

    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        throw new Exception('Failed to save file');
    }

    if (!empty($_SESSION['profile_picture']) && file_exists($_SESSION['profile_picture'])) {
        unlink($_SESSION['profile_picture']);
    }

    $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE user_id = ?");
    $stmt->execute([$uploadPath, $_SESSION['user_id']]);
    
    $_SESSION['profile_picture'] = $uploadPath;
    
    echo json_encode([
        'success' => true,
        'message' => 'Profile picture updated!',
        'imageUrl' => $uploadPath
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}