<?php
$pending_sql = "SELECT question_id, title, content, created_at 
               FROM questions 
               WHERE user_id = :user_id AND status = 'pending'
               ORDER BY created_at DESC";
$pending_stmt = $conn->prepare($pending_sql);
$pending_stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
$pending_stmt->execute();
$pending_questions = $pending_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<section class="content-section">
    <h2><i class="fas fa-clock"></i> Pending Questions (<?php echo count($pending_questions); ?>)</h2>
    
    <?php if (!empty($pending_questions)): ?>
        <div class="question-list">
            <?php foreach ($pending_questions as $question): ?>
                <div class="question-item pending">
                    <div class="question-main">
                        <h3><?php echo htmlspecialchars($question['title']); ?></h3>
                        <p class="question-excerpt">
                            <?php echo mb_strimwidth(htmlspecialchars($question['content']), 0, 150, "..."); ?>
                        </p>
                        <div class="question-meta">
                            <span class="meta-item">
                                <i class="fas fa-calendar-alt"></i> 
                                <?php echo date('M j, Y', strtotime($question['created_at'])); ?>
                            </span>
                            <span class="meta-item status-pending">
                                <i class="fas fa-clock"></i> Pending Approval
                            </span>
                        </div>
                    </div>
                    <div class="question-actions">
                        <button class="btn-cancel" data-id="<?php echo $question['question_id']; ?>">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-check-circle empty-icon"></i>
            <p>No questions pending approval.</p>
        </div>
    <?php endif; ?>
</section>