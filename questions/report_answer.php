<?php

require_once '../includes/db_config.php'; 
require_once '../includes/auth.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $answer_id = intval($data['answer_id']);
    $reason = trim($data['reason']);
    $user_id = $_SESSION['user_id'];

    $checkSql = "SELECT * FROM answer_reports WHERE answer_id = :answer_id AND user_id = :user_id";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bindValue(':answer_id', $answer_id, PDO::PARAM_INT);
    $checkStmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $checkStmt->execute();

    if ($checkStmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'You have already reported this answer.']);
    } else {
        $sql = "INSERT INTO answer_reports (answer_id, user_id, reason) VALUES (:answer_id, :user_id, :reason)";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':answer_id', $answer_id, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':reason', $reason);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to report answer.']);
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>