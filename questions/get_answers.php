<?php
session_start();
require_once '../includes/db_config.php'; 

if (!isset($_GET['question_id'])) {
    die("Invalid request.");
}

$question_id = intval($_GET['question_id']);

$sql = "SELECT a.id AS answer_id, a.content, a.created_at, u.username
        FROM answers a
        JOIN users u ON a.user_id = u.user_id
        WHERE a.question_id = :qid AND a.parent_id IS NULL
        ORDER BY a.created_at ASC";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':qid', $question_id, PDO::PARAM_INT);
$stmt->execute();
$answers = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!empty($answers)) {
    foreach ($answers as $answer) {
        if (!isset($answer['answer_id'])) {
            error_log("Missing 'answer_id' in answer: " . print_r($answer, true));
            continue; 
        }

        echo '<div class="answer-box" data-answer-id="' . htmlspecialchars($answer['answer_id']) . '">';
        echo '<p><strong>' . htmlspecialchars($answer['username']) . ':</strong></p>';
        echo '<p>' . nl2br(htmlspecialchars($answer['content'])) . '</p>';
        echo '<p><em>Posted on: ' . htmlspecialchars($answer['created_at']) . '</em></p>';

        if (isset($_SESSION['user_id'])) {
            echo '<button onclick="openReplyModal(' . htmlspecialchars($answer['answer_id']) . ')" class="reply-button">Reply</button>';
        } else {
            echo '<a href="../login.php" class="reply-button">Login to Reply</a>';
        }

        $replySql = "SELECT a.id AS answer_id, a.content, a.created_at, u.username
                     FROM answers a
                     JOIN users u ON a.user_id = u.user_id
                     WHERE a.parent_id = :parent_id
                     ORDER BY a.created_at ASC";
        $replyStmt = $conn->prepare($replySql);
        $replyStmt->bindValue(':parent_id', $answer['answer_id'], PDO::PARAM_INT);
        $replyStmt->execute();
        $replies = $replyStmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($replies)) {
            echo '<div class="replies">';
            foreach ($replies as $reply) {
                if (!isset($reply['answer_id'])) {
                    error_log("Missing 'answer_id' in reply: " . print_r($reply, true));
                    continue; 
                }

                echo '<div class="reply-box">';
                echo '<p><strong>' . htmlspecialchars($reply['username']) . ':</strong></p>';
                echo '<p>' . nl2br(htmlspecialchars($reply['content'])) . '</p>';
                echo '<p><em>Posted on: ' . htmlspecialchars($reply['created_at']) . '</em></p>';
                echo '</div>';
            }
            echo '</div>';
        }

        echo '</div>';
    }
} else {
    echo '<p>No answers yet.</p>';
}
?>