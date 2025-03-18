<?php
require_once 'db_config.php'; // includes $conn (PDO)
require_once 'auth.php'; // Ensure only admins can access this page
require_once 'functions.php'; // Include functions.php to access fetchPendingPosts() and fetchReportedPosts()

// Fetch sorting option (newest or oldest)
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Fetch pending posts with sorting
$pending_posts = fetchPendingPosts($conn, $sort);

// Fetch reported posts
$reported_posts = fetchReportedPosts($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="CSS/admin.css">
</head>
<body>
    <div class="admin-dashboard">
        <h1>Admin Dashboard</h1>
        <!-- Sorting Dropdown -->
        <div class="sorting-options">
            <label for="sort">Sort Pending Posts:</label>
            <select id="sort" onchange="window.location.href = 'admin_dashboard.php?sort=' + this.value;">
                <option value="newest" <?php echo ($sort === 'newest') ? 'selected' : ''; ?>>Newest First</option>
                <option value="oldest" <?php echo ($sort === 'oldest') ? 'selected' : ''; ?>>Oldest First</option>
            </select>
        </div>
        <div class="admin-sections">
            <!-- Pending Posts Section -->
            <div class="pending-posts">
                <h2>Pending Posts</h2>
                <?php if (!empty($pending_posts)): ?>
                    <?php foreach ($pending_posts as $post): ?>
                        <div class="post">
                            <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                            <p><?php echo htmlspecialchars($post['content']); ?></p>
                            <!-- Add timestamp (username and time) -->
                            <p class="timestamp">
                                <span class="username"><?php echo htmlspecialchars($post['username']); ?></span> • 
                                <span class="time-ago"><?php echo time_ago($post['created_at']); ?></span>
                            </p>
                            <!-- Display Photos -->
                            <?php if (!empty($post['photos'])): ?>
                                <div class="question-photos">
                                    <?php foreach ($post['photos'] as $photo): ?>
                                        <img src="<?php echo htmlspecialchars($photo['photo_path']); ?>" alt="Question Photo" class="question-photo">
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <button onclick="approvePost(<?php echo $post['question_id']; ?>)">Approve</button>
                            <button onclick="rejectPost(<?php echo $post['question_id']; ?>)">Reject</button>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No pending posts.</p>
                <?php endif; ?>
            </div>

            <!-- Reported Posts Section -->
            <div class="reported-posts">
                <h2>Reported Posts</h2>
                <?php if (!empty($reported_posts)): ?>
                    <?php foreach ($reported_posts as $report): ?>
                        <div class="post">
                            <h3><?php echo htmlspecialchars($report['title']); ?></h3>
                            <p><?php echo htmlspecialchars($report['content']); ?></p>
                            <!-- Add timestamp (username and time) -->
                            <p class="timestamp">
                                <span class="username"><?php echo htmlspecialchars($report['username']); ?></span> • 
                                <span class="time-ago"><?php echo time_ago($report['created_at']); ?></span>
                            </p>
                            <!-- Display Photos -->
                            <?php if (!empty($report['photos'])): ?>
                                <div class="question-photos">
                                    <?php foreach ($report['photos'] as $photo): ?>
                                        <img src="<?php echo htmlspecialchars($photo['photo_path']); ?>" alt="Question Photo" class="question-photo">
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <p><strong>Reason:</strong> <?php echo htmlspecialchars($report['reason']); ?></p>
                            <button onclick="deletePost(<?php echo $report['question_id']; ?>)">Delete</button>
                            <button onclick="ignoreReport(<?php echo $report['id']; ?>)">Ignore</button>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No reported posts.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Full Question Modal -->
    <div id="fullQuestionModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 id="modalQuestionTitle"></h2>
            <p class="modal-question-meta">
                <span id="modalQuestionUsername"></span> • <span id="modalQuestionTime"></span>
            </p>
            <div class="modal-question-content">
                <p id="modalQuestionContent"></p>
                <div id="modalQuestionPhotos" class="modal-photos-container"></div>
            </div>
        </div>
    </div>
    <script src="JS/admin.js"></script>
</body>
</html>