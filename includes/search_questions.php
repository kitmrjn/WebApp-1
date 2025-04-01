<?php
session_start();
require_once 'db_config.php';
require_once 'functions.php';

// Get search term from POST data
$searchTerm = isset($_POST['searchTerm']) ? trim($_POST['searchTerm']) : '';

if (empty($searchTerm)) {
    die('<p>Please enter a search term</p>');
}

// Search in both title and content
$sql = "SELECT q.*, u.username
        FROM questions q
        JOIN users u ON q.user_id = u.user_id
        WHERE q.status = 'approved'
        AND (q.title LIKE :searchTerm OR q.content LIKE :searchTerm)
        ORDER BY q.created_at DESC";

$stmt = $conn->prepare($sql);
$searchParam = "%$searchTerm%";
$stmt->bindValue(':searchTerm', $searchParam);
$stmt->execute();
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch photos for each question
foreach ($questions as &$question) {
    $photo_sql = "SELECT photo_path FROM question_photos WHERE question_id = :qid LIMIT 2";
    $photo_stmt = $conn->prepare($photo_sql);
    $photo_stmt->bindValue(':qid', $question['question_id'], PDO::PARAM_INT);
    $photo_stmt->execute();
    $question['photos'] = $photo_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch the total number of photos
    $count_sql = "SELECT COUNT(*) AS total_photos FROM question_photos WHERE question_id = :qid";
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->bindValue(':qid', $question['question_id'], PDO::PARAM_INT);
    $count_stmt->execute();
    $question['total_photos'] = $count_stmt->fetch(PDO::FETCH_ASSOC)['total_photos'];
}

// Output the results
if (!empty($questions)) {
    foreach($questions as $row): ?>
        <div class="question" data-question-id="<?php echo htmlspecialchars($row['question_id']); ?>">
            <div class="question-header">
            <?php 
                $asker = get_user_data($conn, $row['user_id']);
                if (!empty($asker['profile_picture'])): ?>
                    <img src="/webapp/<?php echo htmlspecialchars($asker['profile_picture']); ?>" 
                        alt="User Avatar" 
                        class="avatar">
                <?php else: ?>
                    <img src="/webapp/assets/images/userAvatar.jpg" alt="User Avatar" class="avatar">
                <?php endif; ?>
                <div class="question-info">
                    <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                    <p class="timestamp">
                        <span class="username"><?php echo htmlspecialchars($row['username']); ?></span>
                        <span class="time-ago"><?php echo time_ago($row['created_at']); ?></span>
                    </p>
                </div>
            </div>
            <div class="question-content">
                <p class="answer-preview">
                    <?php echo mb_strimwidth(htmlspecialchars($row['content']), 0, 100, "..."); ?>
                </p>
                <div class="answer-full" style="display: none;">
                    <?php echo htmlspecialchars($row['content']); ?>
                </div>
                <?php if (!empty($row['photos'])): ?>
                    <div class="question-photos">
                        <?php foreach ($row['photos'] as $index => $photo): ?>
                            <div class="photo-container <?php echo (count($row['photos']) === 1 ? 'single-photo' : 'multiple-photos'); ?>">
                                <img src="/webapp/<?php echo htmlspecialchars($photo['photo_path']); ?>" alt="Question Photo" class="question-photo-thumbnail">
                                <?php if ($index === 1 && $row['total_photos'] > 2): ?>
                                    <div class="photo-count-overlay">
                                        +<?php echo $row['total_photos'] - 2; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <!-- Buttons at the bottom -->
            <div class="question-actions">
                <!-- Answers button on the left -->
                <a href="/webapp/questions/question?id=<?php echo htmlspecialchars($row['question_id']); ?>" class="answer-button">
                    <i class="bi bi-chat-left-text"></i> Answers
                </a>
                <!-- Report button for questions -->
                <button class="report-button" data-question-id="<?php echo $row['question_id']; ?>" onclick="reportPost(<?php echo $row['question_id']; ?>)">
                    <i class="bi bi-flag"></i> Report
                </button>
            </div>
        </div>
    <?php endforeach;
} else {
    echo '<p>No questions found matching your search.</p>';
}
?>