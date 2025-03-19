<?php
// functions.php
date_default_timezone_set('Asia/Manila');

// Function to calculate relative time
function time_ago($datetime) {
    $now = new DateTime;
    $created_at = new DateTime($datetime);
    $interval = $now->diff($created_at);

    if ($interval->y > 0) {
        return $interval->y == 1 ? '1 year ago' : $interval->y . ' years ago';
    } elseif ($interval->m > 0) {
        return $interval->m == 1 ? '1 month ago' : $interval->m . ' months ago';
    } elseif ($interval->d > 0) {
        return $interval->d == 1 ? '1 day ago' : $interval->d . ' days ago';
    } elseif ($interval->h > 0) {
        return $interval->h == 1 ? '1 hr ago' : $interval->h . ' hrs ago';
    } elseif ($interval->i > 0) {
        return $interval->i == 1 ? '1 min ago' : $interval->i . ' mins ago';
    } else {
        return 'Just now';
    }
}

// Function to fetch pending posts with sorting
function fetchPendingPosts($conn, $sort = 'newest') {
    $order = ($sort === 'oldest') ? 'ASC' : 'DESC';
    $sql = "SELECT q.*, u.username
            FROM questions q
            JOIN users u ON q.user_id = u.user_id
            WHERE q.status = 'pending'
            ORDER BY q.created_at $order";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch photos for each post
    foreach ($posts as &$post) {
        $photo_sql = "SELECT photo_path FROM question_photos WHERE question_id = :qid";
        $photo_stmt = $conn->prepare($photo_sql);
        $photo_stmt->bindValue(':qid', $post['question_id'], PDO::PARAM_INT);
        $photo_stmt->execute();
        $post['photos'] = $photo_stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    return $posts;
}

// Function to fetch reported posts and answers
function fetchReportedPosts($conn) {
    // Fetch reported questions
    $questionSql = "SELECT r.*, q.title, q.content, u.username
                    FROM reports r 
                    JOIN questions q ON r.question_id = q.question_id
                    JOIN users u ON q.user_id = u.user_id";
    $questionStmt = $conn->prepare($questionSql);
    $questionStmt->execute();
    $reportedQuestions = $questionStmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch reported answers
    $answerSql = "SELECT ar.*, a.content, u.username, q.title AS question_title
                  FROM answer_reports ar
                  JOIN answers a ON ar.answer_id = a.answer_id
                  JOIN users u ON a.user_id = u.user_id
                  JOIN questions q ON a.question_id = q.question_id";
    $answerStmt = $conn->prepare($answerSql);
    $answerStmt->execute();
    $reportedAnswers = $answerStmt->fetchAll(PDO::FETCH_ASSOC);

    // Combine reported questions and answers
    $reportedPosts = array_merge($reportedQuestions, $reportedAnswers);

    // Fetch photos for reported questions
    foreach ($reportedPosts as &$post) {
        if (isset($post['question_id'])) {
            $photoSql = "SELECT photo_path FROM question_photos WHERE question_id = :qid";
            $photoStmt = $conn->prepare($photoSql);
            $photoStmt->bindValue(':qid', $post['question_id'], PDO::PARAM_INT);
            $photoStmt->execute();
            $post['photos'] = $photoStmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $post['photos'] = [];
        }
    }

    return $reportedPosts;
}