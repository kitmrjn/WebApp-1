<?php
session_start();
require_once '../includes/db_config.php'; 

if (!isset($_GET['question_id'])) {
    die("Question ID not provided.");
}

$question_id = intval($_GET['question_id']);

$pSql = "SELECT photo_path FROM question_photos WHERE question_id = :qid";
$pStmt = $conn->prepare($pSql);
$pStmt->bindValue(':qid', $question_id, PDO::PARAM_INT);
$pStmt->execute();
$photos = $pStmt->fetchAll(PDO::FETCH_ASSOC);

$html = '';
foreach ($photos as $photo) {
    $html .= '<img src="' . htmlspecialchars($photo['photo_path']) . '" alt="Question Photo" class="question-photo">';
}

echo $html;
?>