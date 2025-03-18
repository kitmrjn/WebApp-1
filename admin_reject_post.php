<?php
require_once 'db_config.php'; // includes $conn (PDO)
require_once 'auth.php'; // Ensure only admins can access this page

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $question_id = intval($data['question_id']);

    // Update the post status to 'rejected'
    $sql = "UPDATE questions SET status = 'rejected' WHERE question_id = :question_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':question_id', $question_id, PDO::PARAM_INT);
    $stmt->execute();

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>