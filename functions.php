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

// Function to fetch reported posts with photos
function fetchReportedPosts($conn) {
    $sql = "SELECT r.*, q.title, q.content 
            FROM reports r 
            JOIN questions q ON r.question_id = q.question_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch photos for each reported post
    foreach ($reports as &$report) {
        $photo_sql = "SELECT photo_path FROM question_photos WHERE question_id = :qid";
        $photo_stmt = $conn->prepare($photo_sql);
        $photo_stmt->bindValue(':qid', $report['question_id'], PDO::PARAM_INT);
        $photo_stmt->execute();
        $report['photos'] = $photo_stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    return $reports;
}