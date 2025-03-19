<?php
require_once 'db_config.php'; // includes $conn (PDO)
require_once 'auth.php'; // Ensure only admins can access this page

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $answer_id = intval($data['answer_id']);

    try {
        // Start a transaction
        $conn->beginTransaction();

        // Delete reports associated with the answer
        $deleteReportsSql = "DELETE FROM answer_reports WHERE answer_id = :answer_id";
        $deleteReportsStmt = $conn->prepare($deleteReportsSql);
        $deleteReportsStmt->bindValue(':answer_id', $answer_id, PDO::PARAM_INT);
        $deleteReportsStmt->execute();

        // Delete the answer itself
        $deleteAnswerSql = "DELETE FROM answers WHERE answer_id = :answer_id";
        $deleteAnswerStmt = $conn->prepare($deleteAnswerSql);
        $deleteAnswerStmt->bindValue(':answer_id', $answer_id, PDO::PARAM_INT);
        $deleteAnswerStmt->execute();

        // Commit the transaction
        $conn->commit();

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        // Rollback the transaction on error
        $conn->rollBack();
        echo json_encode(['success' => false, 'message' => 'Failed to delete answer.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>