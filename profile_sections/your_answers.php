<?php
$answers_sql = "SELECT a.answer_id, a.content, a.created_at, 
               q.question_id, q.title as question_title
               FROM answers a
               JOIN questions q ON a.question_id = q.question_id
               WHERE a.user_id = :user_id
               ORDER BY a.created_at DESC";
$answers_stmt = $conn->prepare($answers_sql);
$answers_stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
$answers_stmt->execute();
$answers = $answers_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<section class="content-section">
    <h2><i class="fas fa-reply"></i> Your Answers (<?php echo count($answers); ?>)</h2>
    
    <?php if (!empty($answers)): ?>
        <div class="answer-list">
            <?php foreach ($answers as $answer): ?>
                <div class="answer-item">
                    <div class="answer-main">
                        <h3>
                            <a href="question?id=<?php echo $answer['question_id']; ?>#answer-<?php echo $answer['answer_id']; ?>">
                                Re: <?php echo htmlspecialchars($answer['question_title']); ?>
                            </a>
                        </h3>
                        <p class="answer-excerpt">
                            <?php echo mb_strimwidth(htmlspecialchars($answer['content']), 0, 150, "..."); ?>
                        </p>
                        <div class="answer-meta">
                            <span class="meta-item">
                                <i class="fas fa-calendar-alt"></i> 
                                <?php echo date('M j, Y', strtotime($answer['created_at'])); ?>
                            </span>
                        </div>
                    </div>
                    <div class="answer-actions">
                        <a href="question?id=<?php echo $answer['question_id']; ?>#answer-<?php echo $answer['answer_id']; ?>" class="btn-view">
                            <i class="fas fa-eye"></i> View
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-comment-slash empty-icon"></i>
            <p>You haven't answered any questions yet.</p>
        </div>
    <?php endif; ?>
</section>