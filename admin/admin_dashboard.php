<?php
require_once '../includes/db_config.php'; 
require_once '../includes/auth.php'; 
require_once '../includes/functions.php'; 

$sortPending = isset($_GET['sort-pending']) ? $_GET['sort-pending'] : 'newest';
$sortReported = isset($_GET['sort-reported']) ? $_GET['sort-reported'] : 'newest';

$filterType = isset($_GET['filter-type']) ? $_GET['filter-type'] : 'all'; 

$pagePending = isset($_GET['page-pending']) ? (int)$_GET['page-pending'] : 1;
$pageReported = isset($_GET['page-reported']) ? (int)$_GET['page-reported'] : 1;

$perPage = 10;

$pending_posts = fetchPendingPosts($conn, $sortPending, $pagePending, $perPage);
$totalPending = countPendingPosts($conn); 

$reported_posts = fetchReportedPosts($conn, $sortReported, $filterType, $pageReported, $perPage);
$totalReported = countReportedPosts($conn, $filterType); 

// Determine active tab from URL
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'pending';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/CSS/admin.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=News+Cycle:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <meta name="theme-color" content="#4CAF50">
    <link rel="apple-touch-icon" sizes="57x57" href="../assets/images/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="../assets/images/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="../assets/images/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="../assets/images/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="../assets/images/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="../assets/images/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="../assets/images/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="../assets/images/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="../assets/images/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192"  href="../assets/images/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="../assets/images/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/images/favicon-16x16.png">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">
</head>
<body>
    <header>
        <div class="logo">
            <img src="../assets/images/svcc.jpg" alt="SVCC Logo" class="logo-img">
            <span class="site-name">Vincenthinks</span>
        </div>
        <div class="hamburger" onclick="toggleMenu()">
            <i class="fas fa-bars"></i>
        </div>
        <nav id="nav-menu">
            <a href="../index">Home</a>
            <a href="../user/profile">Profile</a>
            <a href="../logout">Logout</a>
        </nav>
    </header>
    <div class="admin-dashboard">
        <h1>Admin Dashboard</h1>
        
        <div class="admin-tabs">
            <button class="tab-button <?php echo $activeTab === 'pending' ? 'active' : ''; ?>" data-tab="pending">Pending Posts</button>
            <button class="tab-button <?php echo $activeTab === 'reported' ? 'active' : ''; ?>" data-tab="reported">Reported Content</button>
        </div>
        
        <div class="admin-sections">
            <div class="pending-posts tab-content <?php echo $activeTab === 'pending' ? 'active' : ''; ?>" id="pending-tab">
               <div class="admin-header-pending-posts">
               <h2>Pending Posts</h2>
                <div class="sort-group">
                    <label for="sort-pending">Sort:</label>
                    <select id="sort-pending" onchange="updatePendingSort(this.value)">
                        <option value="newest" <?php echo ($sortPending === 'newest') ? 'selected' : ''; ?>>Newest First</option>
                        <option value="oldest" <?php echo ($sortPending === 'oldest') ? 'selected' : ''; ?>>Oldest First</option>
                    </select>
                </div>
               </div> 
                <?php if (!empty($pending_posts)): ?>
                    <?php foreach ($pending_posts as $post): ?>
                        <div class="post">
                            <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                            <p><?php echo htmlspecialchars($post['content']); ?></p>
                            <p class="timestamp">
                                <span class="username"><?php echo htmlspecialchars($post['username']); ?></span> • 
                                <span class="time-ago"><?php echo time_ago($post['created_at']); ?></span>
                            </p>
                            <?php if (!empty($post['photos'])): ?>
                                <div class="question-photos">
                                    <?php foreach ($post['photos'] as $photo): ?>
                                        <img src="/webapp/uploads/<?php echo htmlspecialchars($photo['photo_path']); ?>" alt="Question Photo" class="question-photo">
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
                <div class="pagination">
                    <?php if ($pagePending > 1): ?>
                        <a href="admin_dashboard?tab=pending&sort-pending=<?php echo $sortPending; ?>&page-pending=<?php echo $pagePending - 1; ?>">Previous</a>
                    <?php endif; ?>
                    <span>Page <?php echo $pagePending; ?> of <?php echo ceil($totalPending / $perPage); ?></span>
                    <?php if ($pagePending < ceil($totalPending / $perPage)): ?>
                        <a href="admin_dashboard?tab=pending&sort-pending=<?php echo $sortPending; ?>&page-pending=<?php echo $pagePending + 1; ?>">Next</a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="reported-posts tab-content <?php echo $activeTab === 'reported' ? 'active' : ''; ?>" id="reported-tab">
                <div class="admin-header">
                    <h2>Reported Posts & Answers</h2>
                    <div class="sort-group">
                        <label for="sort-reported">Sort:</label>
                        <select id="sort-reported" onchange="updateReportedSort(this.value)">
                            <option value="newest" <?php echo ($sortReported === 'newest') ? 'selected' : ''; ?>>Newest First</option>
                            <option value="oldest" <?php echo ($sortReported === 'oldest') ? 'selected' : ''; ?>>Oldest First</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="filter-type">Filter:</label>
                        <select id="filter-type" onchange="updateReportedFilter(this.value)">
                            <option value="all" <?php echo ($filterType === 'all') ? 'selected' : ''; ?>>All</option>
                            <option value="question" <?php echo ($filterType === 'question') ? 'selected' : ''; ?>>Questions Only</option>
                            <option value="answer" <?php echo ($filterType === 'answer') ? 'selected' : ''; ?>>Answers Only</option>
                        </select>
                    </div>
                </div>
                <?php if (!empty($reported_posts)): ?>
                    <?php foreach ($reported_posts as $report): ?>
                        <div class="post" data-question-id="<?php echo $report['question_id'] ?? ''; ?>" data-answer-id="<?php echo $report['answer_id'] ?? ''; ?>">
                            <?php if (isset($report['question_id'])): ?>
                                <h3><?php echo htmlspecialchars($report['title']); ?></h3>
                                <p><?php echo htmlspecialchars($report['content']); ?></p>
                            <?php else: ?>
                                <h3>Answer to: <?php echo htmlspecialchars($report['question_title']); ?></h3>
                                <p><?php echo htmlspecialchars($report['content']); ?></p>
                            <?php endif; ?>
                            <p class="timestamp">
                                <span class="username"><?php echo htmlspecialchars($report['username']); ?></span> • 
                                <span class="time-ago"><?php echo time_ago($report['created_at']); ?></span>
                            </p>
                            <?php if (!empty($report['photos'])): ?>
                                <div class="question-photos">
                                    <?php foreach ($report['photos'] as $photo): ?>
                                        <img src="/webapp/uploads/<?php echo htmlspecialchars($photo['photo_path']); ?>" alt="Question Photo" class="question-photo">
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
                <div class="pagination">
                    <?php if ($pageReported > 1): ?>
                        <a href="admin_dashboard?tab=reported&sort-reported=<?php echo $sortReported; ?>&filter-type=<?php echo $filterType; ?>&page-reported=<?php echo $pageReported - 1; ?>">Previous</a>
                    <?php endif; ?>
                    <span>Page <?php echo $pageReported; ?> of <?php echo ceil($totalReported / $perPage); ?></span>
                    <?php if ($pageReported < ceil($totalReported / $perPage)): ?>
                        <a href="admin_dashboard?tab=reported&sort-reported=<?php echo $sortReported; ?>&filter-type=<?php echo $filterType; ?>&page-reported=<?php echo $pageReported + 1; ?>">Next</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

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

    <div id="confirmationModal" class="modal">
        <div class="modal-content">
            <p>Are you sure you want to perform this action?</p>
            <button id="confirmAction">Yes</button>
            <button id="cancelAction">No</button>
        </div>
    </div>

    <script src="../assets/JS/admin.js"></script>
</body>
</html>