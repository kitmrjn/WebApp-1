<?php
session_start();
require_once '../includes/db_config.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $answer_id = intval($data['answer_id']);
    $is_helpful = intval($data['is_helpful']);
    $user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

    if ($user_id > 0) {
        $check_sql = "SELECT * FROM answer_ratings WHERE answer_id = :answer_id AND user_id = :user_id";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bindValue(':answer_id', $answer_id, PDO::PARAM_INT);
        $check_stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $check_stmt->execute();
        $existing_rating = $check_stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing_rating) {
            $update_sql = "UPDATE answer_ratings SET is_helpful = :is_helpful WHERE id = :id";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bindValue(':is_helpful', $is_helpful, PDO::PARAM_INT);
            $update_stmt->bindValue(':id', $existing_rating['id'], PDO::PARAM_INT);
            $update_stmt->execute();

            $count_change = $is_helpful ? 1 : -1;
            $update_count_sql = "UPDATE answers SET helpful_count = helpful_count + :count_change WHERE answer_id = :answer_id";
            $update_count_stmt = $conn->prepare($update_count_sql);
            $update_count_stmt->bindValue(':count_change', $count_change, PDO::PARAM_INT);
            $update_count_stmt->bindValue(':answer_id', $answer_id, PDO::PARAM_INT);
            $update_count_stmt->execute();
        } else {
            $insert_sql = "INSERT INTO answer_ratings (answer_id, user_id, is_helpful) VALUES (:answer_id, :user_id, :is_helpful)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bindValue(':answer_id', $answer_id, PDO::PARAM_INT);
            $insert_stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $insert_stmt->bindValue(':is_helpful', $is_helpful, PDO::PARAM_INT);
            $insert_stmt->execute();

            if ($is_helpful) {
                $update_count_sql = "UPDATE answers SET helpful_count = helpful_count + 1 WHERE answer_id = :answer_id";
                $update_count_stmt = $conn->prepare($update_count_sql);
                $update_count_stmt->bindValue(':answer_id', $answer_id, PDO::PARAM_INT);
                $update_count_stmt->execute();
            }
        }

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}