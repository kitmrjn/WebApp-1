<?php
$questions_sql = "SELECT q.question_id, q.title, q.content, q.created_at, q.status, 
                 COUNT(a.answer_id) as answer_count
                 FROM questions q
                 LEFT JOIN answers a ON q.question_id = a.question_id
                 WHERE q.user_id = :user_id AND q.status = 'approved'
                 GROUP BY q.question_id
                 ORDER BY q.created_at DESC";
$questions_stmt = $conn->prepare($questions_sql);
$questions_stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
$questions_stmt->execute();
$questions = $questions_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<section class="content-section">
    <h2><i class="fas fa-question-circle"></i> Your Questions (<?php echo count($questions); ?>)</h2>
    
    <?php if (!empty($questions)): ?>
        <div class="question-list">
            <?php foreach ($questions as $question): ?>
                <div class="question-item">
                    <div class="question-main">
                        <h3>
                            <a href="../../questionsquestion?id=<?php echo $question['question_id']; ?>">
                                <?php echo htmlspecialchars($question['title']); ?>
                            </a>
                        </h3>
                        <p class="question-excerpt">
                            <?php echo mb_strimwidth(htmlspecialchars($question['content']), 0, 150, "..."); ?>
                        </p>
                        <div class="question-meta">
                            <span class="meta-item">
                                <i class="fas fa-calendar-alt"></i> 
                                <?php echo date('M j, Y', strtotime($question['created_at'])); ?>
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-comments"></i> 
                                <?php echo $question['answer_count']; ?> answers
                            </span>
                        </div>
                    </div>
                    <div class="question-actions">
                        <a href="../../webapp/questions/question.php?id=<?php echo $question['question_id']; ?>" class="btn-view">
                            <i class="fas fa-eye"></i> View
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-question-circle empty-icon"></i>
            <p>You haven't asked any questions yet.</p>
            <a href="../../webapp/questions/post_question" class="btn-ask">Ask Your First Question</a>
        </div>
    <?php endif; ?>
</section>