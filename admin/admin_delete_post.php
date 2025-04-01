<?php
require_once '../includes/db_config.php'; 
require_once '../includes/auth.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $question_id = intval($data['question_id']);

    try {
        $conn->beginTransaction();

        $deleteReportsSql = "DELETE FROM reports WHERE question_id = :question_id";
        $deleteReportsStmt = $conn->prepare($deleteReportsSql);
        $deleteReportsStmt->bindValue(':question_id', $question_id, PDO::PARAM_INT);
        $deleteReportsStmt->execute();

        $deletePhotosSql = "DELETE FROM question_photos WHERE question_id = :question_id";
        $deletePhotosStmt = $conn->prepare($deletePhotosSql);
        $deletePhotosStmt->bindValue(':question_id', $question_id, PDO::PARAM_INT);
        $deletePhotosStmt->execute();

        $deleteQuestionSql = "DELETE FROM questions WHERE question_id = :question_id";
        $deleteQuestionStmt = $conn->prepare($deleteQuestionSql);
        $deleteQuestionStmt->bindValue(':question_id', $question_id, PDO::PARAM_INT);
        $deleteQuestionStmt->execute();

        $conn->commit();

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode(['success' => false, 'message' => 'Failed to delete post.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>