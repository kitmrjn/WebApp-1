<?php
require_once 'db_config.php'; // includes $conn (PDO)
require_once 'auth.php'; // Ensure only admins can access this page

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $question_id = intval($data['question_id']);

    try {
        // Start a transaction
        $conn->beginTransaction();

        // Delete reports associated with the question
        $deleteReportsSql = "DELETE FROM reports WHERE question_id = :question_id";
        $deleteReportsStmt = $conn->prepare($deleteReportsSql);
        $deleteReportsStmt->bindValue(':question_id', $question_id, PDO::PARAM_INT);
        $deleteReportsStmt->execute();

        // Delete photos associated with the question
        $deletePhotosSql = "DELETE FROM question_photos WHERE question_id = :question_id";
        $deletePhotosStmt = $conn->prepare($deletePhotosSql);
        $deletePhotosStmt->bindValue(':question_id', $question_id, PDO::PARAM_INT);
        $deletePhotosStmt->execute();

        // Delete the question itself
        $deleteQuestionSql = "DELETE FROM questions WHERE question_id = :question_id";
        $deleteQuestionStmt = $conn->prepare($deleteQuestionSql);
        $deleteQuestionStmt->bindValue(':question_id', $question_id, PDO::PARAM_INT);
        $deleteQuestionStmt->execute();

        // Commit the transaction
        $conn->commit();

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        // Rollback the transaction on error
        $conn->rollBack();
        echo json_encode(['success' => false, 'message' => 'Failed to delete post.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>