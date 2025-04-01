<?php
// Get counts
$questions_count = $conn->query("SELECT COUNT(*) FROM questions WHERE user_id = {$_SESSION['user_id']} AND status = 'approved'")->fetchColumn();
$pending_count = $conn->query("SELECT COUNT(*) FROM questions WHERE user_id = {$_SESSION['user_id']} AND status = 'pending'")->fetchColumn();
$answers_count = $conn->query("SELECT COUNT(*) FROM answers WHERE user_id = {$_SESSION['user_id']}")->fetchColumn();

// Check if answer_votes table exists before querying
$reputation = 0;
try {
    $reputation = $conn->query("SELECT IFNULL(SUM(vote_value), 0) FROM answer_votes WHERE answer_id IN (SELECT answer_id FROM answers WHERE user_id = {$_SESSION['user_id']})")->fetchColumn();
} catch (PDOException $e) {
    // Table doesn't exist or other error - default to 0
    $reputation = 0;
}
?>
<section class="content-section">
    <h2><i class="fas fa-chart-bar"></i> Your Activity Stats</h2>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #4CAF50;">
                <i class="fas fa-question"></i>
            </div>
            <div class="stat-info">
                <div class="stat-number"><?php echo $questions_count; ?></div>
                <div class="stat-label">Approved Questions</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #FF9800;">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-info">
                <div class="stat-number"><?php echo $pending_count; ?></div>
                <div class="stat-label">Pending Questions</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #2196F3;">
                <i class="fas fa-reply"></i>
            </div>
            <div class="stat-info">
                <div class="stat-number"><?php echo $answers_count; ?></div>
                <div class="stat-label">Answers Posted</div>
            </div>
        </div>
    </div>
    
    </div>
</section>