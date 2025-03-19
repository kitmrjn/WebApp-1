<?php
require_once 'db_config.php'; // includes $conn (PDO)
require_once 'auth.php'; // Ensure only admins can access this page
require_once 'functions.php'; // Include functions.php to access fetchPendingPosts() and fetchReportedPosts()

// Fetch sorting options for pending and reported posts
$sortPending = isset($_GET['sort-pending']) ? $_GET['sort-pending'] : 'newest';
$sortReported = isset($_GET['sort-reported']) ? $_GET['sort-reported'] : 'newest';

// Fetch filter option for reported posts
$filterType = isset($_GET['filter-type']) ? $_GET['filter-type'] : 'all'; // 'all', 'question', or 'answer'

// Fetch page numbers for pagination
$pagePending = isset($_GET['page-pending']) ? (int)$_GET['page-pending'] : 1;
$pageReported = isset($_GET['page-reported']) ? (int)$_GET['page-reported'] : 1;

// Number of posts per page
$perPage = 10;

// Fetch pending posts with sorting and pagination
$pending_posts = fetchPendingPosts($conn, $sortPending, $pagePending, $perPage);
$totalPending = countPendingPosts($conn); // Total number of pending posts

// Fetch reported posts with sorting, filtering, and pagination
$reported_posts = fetchReportedPosts($conn, $sortReported, $filterType, $pageReported, $perPage);
$totalReported = countReportedPosts($conn, $filterType); // Total number of reported posts (filtered)
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="CSS/admin.css">
    <!-- Add Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="admin-dashboard">
        <h1>Admin Dashboard</h1>
        <div class="admin-sections">
            <!-- Pending Posts Section -->
            <div class="pending-posts">
                <h2>Pending Posts</h2>
                <!-- Sort Pending Posts -->
                <div class="sort-group">
                    <label for="sort-pending">Sort:</label>
                    <select id="sort-pending" onchange="window.location.href = 'admin_dashboard.php?sort-pending=' + this.value + '&page-pending=<?php echo $pagePending; ?>';">
                        <option value="newest" <?php echo ($sortPending === 'newest') ? 'selected' : ''; ?>>Newest First</option>
                        <option value="oldest" <?php echo ($sortPending === 'oldest') ? 'selected' : ''; ?>>Oldest First</option>
                    </select>
                </div>
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
                            <button onclick="approvePost(<?php echo $post['question_id']; ?>)">
                                <i class="fas fa-check"></i> Approve
                            </button>
                            <button onclick="rejectPost(<?php echo $post['question_id']; ?>)">
                                <i class="fas fa-times"></i> Reject
                            </button>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No pending posts.</p>
                <?php endif; ?>
                <!-- Pagination for Pending Posts -->
                <div class="pagination">
                    <?php if ($pagePending > 1): ?>
                        <a href="admin_dashboard.php?sort-pending=<?php echo $sortPending; ?>&page-pending=<?php echo $pagePending - 1; ?>">Previous</a>
                    <?php endif; ?>
                    <span>Page <?php echo $pagePending; ?> of <?php echo ceil($totalPending / $perPage); ?></span>
                    <?php if ($pagePending < ceil($totalPending / $perPage)): ?>
                        <a href="admin_dashboard.php?sort-pending=<?php echo $sortPending; ?>&page-pending=<?php echo $pagePending + 1; ?>">Next</a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Reported Posts Section -->
            <div class="reported-posts">
                <h2>Reported Posts & Answers</h2>
                <!-- Sort Reported Posts -->
                <div class="sort-group">
                    <label for="sort-reported">Sort:</label>
                    <select id="sort-reported" onchange="window.location.href = 'admin_dashboard.php?sort-reported=' + this.value + '&filter-type=<?php echo $filterType; ?>&page-reported=<?php echo $pageReported; ?>';">
                        <option value="newest" <?php echo ($sortReported === 'newest') ? 'selected' : ''; ?>>Newest First</option>
                        <option value="oldest" <?php echo ($sortReported === 'oldest') ? 'selected' : ''; ?>>Oldest First</option>
                    </select>
                </div>
                <!-- Filter Reported Posts by Type -->
                <div class="filter-group">
                    <label for="filter-type">Filter:</label>
                    <select id="filter-type" onchange="window.location.href = 'admin_dashboard.php?sort-reported=<?php echo $sortReported; ?>&filter-type=' + this.value + '&page-reported=<?php echo $pageReported; ?>';">
                        <option value="all" <?php echo ($filterType === 'all') ? 'selected' : ''; ?>>All</option>
                        <option value="question" <?php echo ($filterType === 'question') ? 'selected' : ''; ?>>Questions Only</option>
                        <option value="answer" <?php echo ($filterType === 'answer') ? 'selected' : ''; ?>>Answers Only</option>
                    </select>
                </div>
                <?php if (!empty($reported_posts)): ?>
                    <?php foreach ($reported_posts as $report): ?>
                        <div class="post" data-question-id="<?php echo $report['question_id'] ?? ''; ?>" data-answer-id="<?php echo $report['answer_id'] ?? ''; ?>">
                            <?php if (isset($report['question_id'])): ?>
                                <!-- Reported Question -->
                                <h3><?php echo htmlspecialchars($report['title']); ?></h3>
                                <p><?php echo htmlspecialchars($report['content']); ?></p>
                            <?php else: ?>
                                <!-- Reported Answer -->
                                <h3>Answer to: <?php echo htmlspecialchars($report['question_title']); ?></h3>
                                <p><?php echo htmlspecialchars($report['content']); ?></p>
                            <?php endif; ?>
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
                            <?php if (isset($report['question_id'])): ?>
                                <button onclick="deletePost(<?php echo $report['question_id']; ?>)">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            <?php else: ?>
                                <button onclick="deleteAnswer(<?php echo $report['answer_id']; ?>)">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            <?php endif; ?>
                            <button onclick="ignoreReport(<?php echo $report['id']; ?>)">
                                <i class="fas fa-ban"></i> Ignore
                            </button>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No reported posts.</p>
                <?php endif; ?>
                <!-- Pagination for Reported Posts -->
                <div class="pagination">
                    <?php if ($pageReported > 1): ?>
                        <a href="admin_dashboard.php?sort-reported=<?php echo $sortReported; ?>&filter-type=<?php echo $filterType; ?>&page-reported=<?php echo $pageReported - 1; ?>">Previous</a>
                    <?php endif; ?>
                    <span>Page <?php echo $pageReported; ?> of <?php echo ceil($totalReported / $perPage); ?></span>
                    <?php if ($pageReported < ceil($totalReported / $perPage)): ?>
                        <a href="admin_dashboard.php?sort-reported=<?php echo $sortReported; ?>&filter-type=<?php echo $filterType; ?>&page-reported=<?php echo $pageReported + 1; ?>">Next</a>
                    <?php endif; ?>
                </div>
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

    <!-- Confirmation Modal -->
    <div id="confirmationModal" class="modal">
        <div class="modal-content">
            <p>Are you sure you want to perform this action?</p>
            <button id="confirmAction">Yes</button>
            <button id="cancelAction">No</button>
        </div>
    </div>

    <script src="JS/admin.js"></script>
</body>
</html>