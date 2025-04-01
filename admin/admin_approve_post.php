<?php
require_once '../includes/db_config.php'; 
require_once '../includes/auth.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $question_id = intval($data['question_id']);

    $sql = "UPDATE questions SET status = 'approved' WHERE question_id = :question_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':question_id', $question_id, PDO::PARAM_INT);
    $stmt->execute();

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>