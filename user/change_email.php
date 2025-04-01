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

    if (empty($input['current_password']) || empty($input['new_email'])) {
        throw new Exception('All fields are required');
    }

    // Check if credentials exist
    $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($input['current_password'], $user['password'])) {
        throw new Exception('Current password is incorrect');
    }

    if (!filter_var($input['new_email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
    $stmt->execute([$input['new_email'], $_SESSION['user_id']]);
    if ($stmt->rowCount() > 0) {
        throw new Exception('Email already in use');
    }

    $stmt = $conn->prepare("UPDATE users SET email = ? WHERE user_id = ?");
    $stmt->execute([$input['new_email'], $_SESSION['user_id']]);

    $_SESSION['email'] = $input['new_email'];

    echo json_encode([
        'success' => true,
        'message' => 'Email updated successfully',
        'new_email' => $input['new_email']
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}