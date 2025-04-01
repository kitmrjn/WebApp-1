<?php
date_default_timezone_set('Asia/Manila');
require_once 'db_config.php';

// Function to get complete user data
function get_user_data($conn, $user_id) {
    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching user data: " . $e->getMessage());
        return false;
    }
}

// Function to update profile picture in database
function update_profile_picture($conn, $user_id, $picture_path) {
    try {
        $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE user_id = ?");
        return $stmt->execute([$picture_path, $user_id]);
    } catch (PDOException $e) {
        error_log("Error updating profile picture: " . $e->getMessage());
        return false;
    }
}

// Answer and Post time
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

function countPendingPosts($conn) {
    try {
        $sql = "SELECT COUNT(*) as total FROM questions WHERE status = 'pending'";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    } catch (PDOException $e) {
        error_log("Error counting pending posts: " . $e->getMessage());
        return 0;
    }
}

function countReportedPosts($conn, $filterType = 'all') {
    try {
        $sql = "SELECT COUNT(*) as total FROM reports";
        if ($filterType === 'question') {
            $sql = "SELECT COUNT(*) as total FROM reports WHERE question_id IS NOT NULL";
        } elseif ($filterType === 'answer') {
            $sql = "SELECT COUNT(*) as total FROM answer_reports";
        }
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    } catch (PDOException $e) {
        error_log("Error counting reported posts: " . $e->getMessage());
        return 0;
    }
}

function fetchPendingPosts($conn, $sort = 'newest', $page = 1, $perPage = 10) {
    try {
        $order = ($sort === 'oldest') ? 'ASC' : 'DESC';
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT q.*, u.username
                FROM questions q
                JOIN users u ON q.user_id = u.user_id
                WHERE q.status = 'pending'
                ORDER BY q.created_at $order
                LIMIT :limit OFFSET :offset";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
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
    } catch (PDOException $e) {
        error_log("Error fetching pending posts: " . $e->getMessage());
        return [];
    }
}

function fetchReportedPosts($conn, $sort = 'newest', $filterType = 'all', $page = 1, $perPage = 10) {
    try {
        $order = ($sort === 'oldest') ? 'ASC' : 'DESC';
        $offset = ($page - 1) * $perPage;

        $questionSql = "SELECT r.*, q.title, q.content, u.username, q.created_at
                        FROM reports r 
                        JOIN questions q ON r.question_id = q.question_id
                        JOIN users u ON q.user_id = u.user_id
                        WHERE 1=1";
        if ($filterType === 'question') {
            $questionSql .= " AND r.question_id IS NOT NULL";
        } elseif ($filterType === 'answer') {
            $questionSql .= " AND 1=0";
        }
        $questionSql .= " ORDER BY q.created_at $order
                          LIMIT :limit OFFSET :offset";
        $questionStmt = $conn->prepare($questionSql);
        $questionStmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $questionStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $questionStmt->execute();
        $reportedQuestions = $questionStmt->fetchAll(PDO::FETCH_ASSOC);

        $answerSql = "SELECT ar.*, a.content, u.username, q.title AS question_title, a.created_at
                      FROM answer_reports ar
                      JOIN answers a ON ar.answer_id = a.answer_id
                      JOIN users u ON a.user_id = u.user_id
                      JOIN questions q ON a.question_id = q.question_id
                      WHERE 1=1";
        if ($filterType === 'answer') {
            $answerSql .= " AND ar.answer_id IS NOT NULL";
        } elseif ($filterType === 'question') {
            $answerSql .= " AND 1=0";
        }
        $answerSql .= " ORDER BY a.created_at $order
                        LIMIT :limit OFFSET :offset";
        $answerStmt = $conn->prepare($answerSql);
        $answerStmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $answerStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $answerStmt->execute();
        $reportedAnswers = $answerStmt->fetchAll(PDO::FETCH_ASSOC);

        // Combine and sort
        $reportedPosts = array_merge($reportedQuestions, $reportedAnswers);
        usort($reportedPosts, function($a, $b) use ($order) {
            $timeA = strtotime($a['created_at']);
            $timeB = strtotime($b['created_at']);
            return ($order === 'ASC') ? $timeA - $timeB : $timeB - $timeA;
        });

        foreach ($reportedPosts as &$post) {
            $post['photos'] = [];
            if (isset($post['question_id'])) {
                $photoSql = "SELECT photo_path FROM question_photos WHERE question_id = :qid";
                $photoStmt = $conn->prepare($photoSql);
                $photoStmt->bindValue(':qid', $post['question_id'], PDO::PARAM_INT);
                $photoStmt->execute();
                $post['photos'] = $photoStmt->fetchAll(PDO::FETCH_ASSOC);
            }
        }

        return $reportedPosts;
    } catch (PDOException $e) {
        error_log("Error fetching reported posts: " . $e->getMessage());
        return [];
    }
}

function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}