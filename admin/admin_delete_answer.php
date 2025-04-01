<?php
require_once '../includes/db_config.php'; 
require_once '../includes/auth.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $answer_id = intval($data['answer_id']);

    try {
        $conn->beginTransaction();

        $deleteReportsSql = "DELETE FROM answer_reports WHERE answer_id = :answer_id";
        $deleteReportsStmt = $conn->prepare($deleteReportsSql);
        $deleteReportsStmt->bindValue(':answer_id', $answer_id, PDO::PARAM_INT);
        $deleteReportsStmt->execute();

        $deleteAnswerSql = "DELETE FROM answers WHERE answer_id = :answer_id";
        $deleteAnswerStmt = $conn->prepare($deleteAnswerSql);
        $deleteAnswerStmt->bindValue(':answer_id', $answer_id, PDO::PARAM_INT);
        $deleteAnswerStmt->execute();

        $conn->commit();

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode(['success' => false, 'message' => 'Failed to delete answer.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>