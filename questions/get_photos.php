<?php
session_start();
require_once '../includes/db_config.php'; 

if (!isset($_GET['question_id'])) {
    die(json_encode(['error' => 'Question ID not provided']));
}

$question_id = intval($_GET['question_id']);

$pSql = "SELECT photo_path FROM question_photos WHERE question_id = :qid";
$pStmt = $conn->prepare($pSql);
$pStmt->bindValue(':qid', $question_id, PDO::PARAM_INT);
$pStmt->execute();
$photos = $pStmt->fetchAll(PDO::FETCH_ASSOC);

// Return as JSON
header('Content-Type: application/json');
echo json_encode(array_map(function($photo) {
    return '/webapp/uploads/' . htmlspecialchars($photo['photo_path']);
}, $photos));
?>