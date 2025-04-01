<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
header('Content-Type: application/json');

session_start();
require_once '../../includes/db_config.php';
require_once '../../includes/auth.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        throw new Exception('Invalid input data');
    }

    if (empty($input['current_password']) || empty($input['new_password']) || empty($input['confirm_password'])) {
        throw new Exception('All fields are required');
    }

    if ($input['new_password'] !== $input['confirm_password']) {
        throw new Exception('Passwords do not match');
    }

    if (strlen($input['new_password']) < 8) {
        throw new Exception('Password must be at least 8 characters');
    }

    //check if current password match
    $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($input['current_password'], $user['password'])) {
        throw new Exception('Current password is incorrect');
    }

    $newHash = password_hash($input['new_password'], PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
    $stmt->execute([$newHash, $_SESSION['user_id']]);

    echo json_encode([
        'success' => true,
        'message' => 'Password updated successfully'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}