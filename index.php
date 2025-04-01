<?php
session_start();
date_default_timezone_set('Asia/Manila');
require_once 'includes/db_config.php'; 
require_once 'includes/functions.php'; 

// Pagination logic
$questions_per_page = 10; // Number of questions per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Current page number
$offset = ($page - 1) * $questions_per_page; // Calculate offset

// Fetch questions for the current page
$sql = "SELECT q.*, u.username
        FROM questions q
        JOIN users u ON q.user_id = u.user_id
        WHERE q.status = 'approved'
        ORDER BY q.created_at DESC
        LIMIT :limit OFFSET :offset";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':limit', $questions_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch the first 2 photos for each question
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

// Fetch total number of approved questions
$total_questions_sql = "SELECT COUNT(*) as total FROM questions WHERE status = 'approved'";
$total_questions_stmt = $conn->query($total_questions_sql);
$total_questions = $total_questions_stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Calculate total pages
$total_pages = ceil($total_questions / $questions_per_page);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="theme-color" content="#4CAF50">
    <link rel="apple-touch-icon" sizes="57x57" href="assets/images/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="assets/images/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="assets/images/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="assets/images/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="assets/images/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="assets/images/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="assets/images/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="assets/images/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/images/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192"  href="assets/images/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="assets/images/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/images/favicon-16x16.png">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Vincenthinks - Home</title>
    <link rel="stylesheet" href="assets/CSS/global.css">
    <link rel="stylesheet" href="assets/CSS/header.css">
    <link rel="stylesheet" href="assets/CSS/footer.css">
    <link rel="stylesheet" href="assets/CSS/questions.css">
    <link rel="stylesheet" href="assets/CSS/modals.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=News+Cycle:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
            <header>
                <a href="index" class="logo-link desktop-only">
                    <div class="logo">
                        <img src="assets/images/svcc.jpg" alt="VincentThinks Logo" class="nav-logo">
                        <h1>VincenThinks</h1>
                    </div>
                </a>

                <a href="questions/post_question" class="ask-question-link mobile-only" id="ask-text">
                    Ask a Question
                </a>

                <!-- Search Bar -->
                <div class="search-bar">
                    <input type="text" id="searchInput" placeholder="Search questions..." onkeyup="searchQuestions()">
                    <i class="fas fa-search search-icon"></i>
                </div>

                <!-- Hamburger Menu Icon -->
                <div class="hamburger" onclick="toggleMenu()">
                    <i class="fas fa-bars"></i>
                </div>

                <!-- Navigation Links -->
                <nav id="nav-menu">
                    <a href="index">Home</a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- Ask a Question Link for Larger Screens -->
                        <a href="questions/post_question" class="desktop-only">Ask a Question</a>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <a href="admin/admin_dashboard">Admin Dashboard</a>
                        <?php endif; ?>
                        <!-- Profile Dropdown for Larger Screens -->
                            <?php if (isset($_SESSION['user_id'])): 
                                $user = get_user_data($conn, $_SESSION['user_id']);
                            ?>
                                <div class="profile-dropdown desktop-only">
                                    <?php if (!empty($user['profile_picture'])): ?>
                                        <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" 
                                            alt="Profile Picture" 
                                            class="profile-icon">
                                    <?php else: ?>
                                        <img src="assets/images/userAvatar.jpg" class="avatar">
                                    <?php endif; ?>
                                    <div class="dropdown-content">
                                        <a href="user/profile.php">My Profile</a>
                                        <a href="logout">Logout</a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <!-- Profile Links for Smaller Screens -->
                        <div class="profile-links mobile-only">
                            <a href="user/profile.php">My Profile</a>
                            <a href="logout">Logout</a>
                        </div>
                    <?php else: ?>
                        <a href="login">Login</a>
                        <a href="register">Register</a>
                    <?php endif; ?>
                </nav>
            </header>

            <!-- Recent Questions Section -->
            <div class="recent-questions-wrapper">
                <section class="recent-questions">
                    <h2>Recent Questions</h2>
                    <div id="questionsContainer">
                        <?php if (!empty($questions)): ?>
                            <?php foreach($questions as $row): ?>
                                <div class="question" data-question-id="<?php echo htmlspecialchars($row['question_id']); ?>">
                                    <div class="question-header">
                                    <?php 
                                        $asker = get_user_data($conn, $row['user_id']);
                                        if (!empty($asker['profile_picture'])): ?>
                                            <img src="<?php echo htmlspecialchars($asker['profile_picture']); ?>" 
                                                alt="User Avatar" 
                                                class="avatar">
                                        <?php else: ?>
                                            <img src="assets/images/userAvatar.jpg" alt="User Avatar" class="avatar">
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
                                                        <img src="<?php echo htmlspecialchars($photo['photo_path']); ?>" alt="Question Photo" class="question-photo-thumbnail">
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
                                        <a href="questions/question?id=<?php echo htmlspecialchars($row['question_id']); ?>" class="answer-button">
                                            <i class="bi bi-chat-left-text"></i> Answers
                                        </a>
                                        <!-- Report button for questions -->
                                        <button class="report-button" data-question-id="<?php echo $row['question_id']; ?>" onclick="reportPost(<?php echo $row['question_id']; ?>)">
                                            <i class="bi bi-flag"></i> Report
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No questions found.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Pagination Links -->
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="index?page=<?php echo ($page - 1); ?>">Previous</a>
                        <?php endif; ?>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="index?page=<?php echo $i; ?>" <?php echo ($i == $page ? 'class="active"' : ''); ?>><?php echo $i; ?></a>
                        <?php endfor; ?>
                        <?php if ($page < $total_pages): ?>
                            <a href="index?page=<?php echo ($page + 1); ?>">Next</a>
                        <?php endif; ?>
                    </div>
                </section>
            </div>
        <footer>
            <p>&copy; <?php echo date('Y'); ?> VincenThinks. All rights reserved.</p>
        </footer>
    

    <!-- Full Question Modal -->
    <div id="fullQuestionModal" class="modal">
        <div class="modal-content">
            <div class="modal-sticky">
            <span class="close">&times;</span>
            <h2 id="modalQuestionTitle"></h2>
            <p class="modal-question-meta">
                <span id="modalQuestionUsername"></span> • <span id="modalQuestionTime"></span>
            </p>
            </div>
            <div class="modal-question-content">
                <p id="modalQuestionContent"></p>
                <div id="modalQuestionPhoto"></div>
            </div>
        </div>
    </div>


    <script src="assets/JS/index.js"></script>
</body>
</html>