<?php
require_once 'db_config.php'; // includes $conn (PDO)
require_once 'auth.php'; // Ensure user is logged in

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $answer_id = intval($data['answer_id']);
    $reason = trim($data['reason']);
    $user_id = $_SESSION['user_id'];

    // Check if the user has already reported this answer
    $checkSql = "SELECT * FROM answer_reports WHERE answer_id = :answer_id AND user_id = :user_id";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bindValue(':answer_id', $answer_id, PDO::PARAM_INT);
    $checkStmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $checkStmt->execute();

    if ($checkStmt->fetch()) {
        // User has already reported this answer
        echo json_encode(['success' => false, 'message' => 'You have already reported this answer.']);
    } else {
        // Insert the report into the answer_reports table
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