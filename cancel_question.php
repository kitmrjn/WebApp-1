<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'db_config.php';
require_once 'auth.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die(json_encode(['success' => false, 'message' => 'Not logged in']));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['success' => false, 'message' => 'Invalid request method']));
}

// Get JSON input
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data || !isset($data['question_id'])) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Invalid request data']));
}

$questionId = filter_var($data['question_id'], FILTER_VALIDATE_INT);

if (!$questionId) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Invalid question ID']));
}

try {
    // Verify the question belongs to the user
    $checkSql = "SELECT question_id FROM questions WHERE question_id = :question_id AND user_id = :user_id AND status = 'pending'";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bindValue(':question_id', $questionId, PDO::PARAM_INT);
    $checkStmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() === 0) {
        http_response_code(403);
        die(json_encode(['success' => false, 'message' => 'Question not found or not pending']));
    }
    
    // Delete the question
    $deleteSql = "DELETE FROM questions WHERE question_id = :question_id";
    $deleteStmt = $conn->prepare($deleteSql);
    $deleteStmt->bindValue(':question_id', $questionId, PDO::PARAM_INT);
    $deleteStmt->execute();
    
    echo json_encode(['success' => true, 'message' => 'Question canceled successfully']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}