<?php
require_once 'db_config.php'; // includes $conn (PDO)
require_once 'auth.php'; // Ensure user is logged in

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $question_id = intval($data['question_id']);
    $reason = trim($data['reason']);
    $user_id = $_SESSION['user_id'];

    // Insert the report into the reports table
    $sql = "INSERT INTO reports (question_id, user_id, reason) VALUES (:question_id, :user_id, :reason)";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':question_id', $question_id, PDO::PARAM_INT);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':reason', $reason);
    $stmt->execute();

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>