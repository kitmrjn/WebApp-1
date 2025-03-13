<?php
session_start();
require_once 'db_config.php'; // includes $conn (PDO)

// Function to calculate relative time
function time_ago($datetime) {
    $now = new DateTime;
    $created_at = new DateTime($datetime);
    $interval = $now->diff($created_at);

    if ($interval->y > 0) {
        return $interval->y == 1 ? '1 year ago' : $interval->y . ' years ago';
    } elseif ($interval->m > 0) {
        return $interval->m == 1 ? '1 month ago' : $interval->m . ' months ago';
    } elseif ($interval->d > 0) {
        return $interval->d == 1 ? '1 day ago' : $interval->d . ' days ago';
    } elseif ($interval->h > 0) {
        return $interval->h == 1 ? '1 hr ago' : $interval->h . ' hrs ago';
    } elseif ($interval->i > 0) {
        return $interval->i == 1 ? '1 min ago' : $interval->i . ' mins ago';
    } else {
        return 'Just now';
    }
}

// Fetch the latest questions
$sql = "SELECT q.*, u.username
        FROM questions q
        JOIN users u ON q.user_id = u.user_id
        ORDER BY q.created_at DESC";
$stmt = $conn->query($sql);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle new answer submission from modal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id']) && isset($_POST['question_id'])) {
    $answerContent = trim($_POST['answer']);
    $question_id   = intval($_POST['question_id']);
    $user_id       = $_SESSION['user_id'];

    if (!empty($answerContent)) {
        $aSql = "INSERT INTO answers (question_id, user_id, content) VALUES (:qid, :uid, :content)";
        $aStmt = $conn->prepare($aSql);
        $aStmt->bindValue(':qid', $question_id, PDO::PARAM_INT);
        $aStmt->bindValue(':uid', $user_id, PDO::PARAM_INT);
        $aStmt->bindValue(':content', $answerContent);
        $aStmt->execute();

        // Reload to show the new answer
        header("Location: index.php"); // Redirect back to index
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vincenthinks - Home</title>
    <link rel="stylesheet" href="CSS/global.css">
    <link rel="stylesheet" href="CSS/header.css">
    <link rel="stylesheet" href="CSS/search-bar.css">
    <link rel="stylesheet" href="CSS/footer.css">
    <link rel="stylesheet" href="CSS/questions.css">
    <link rel="stylesheet" href="CSS/modals.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="page-container">
        <!-- Flex layout for main content -->
        <div class="content-wrap">
            <header>
                <a href="index.php" class="logo-link">
                    <div class="logo">
                        <img src="images/svcc.jpg" alt="VincentThinks Logo" class="nav-logo">
                        <h1>VincenThinks</h1>
                    </div>
                </a>
                <div class="search-bar">
                    <input type="text" id="searchInput" placeholder="Search questions..." onkeyup="searchQuestions()">
                    <i class="fas fa-search search-icon"></i>
                </div>
                <nav>
                    <a href="index.php">Home</a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="post_question.php">Ask a Question</a>
                        <div class="profile-dropdown">
                            <i class="fas fa-user-circle profile-icon"></i> <!-- Profile icon -->
                            <div class="dropdown-content">
                                <a href="#">My Profile</a> <!-- Add link to profile page -->
                                <a href="logout.php">Logout</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="login.php">Login</a>
                        <a href="register.php">Register</a>
                    <?php endif; ?>
                </nav>
            </header>
            <!-- Answer Modal -->
            <div id="answerModal" class="modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <h2>Post Your Answer</h2>
                    <form method="POST" action="">
                        <input type="hidden" name="question_id" id="modalQuestionId" value="">
                        <textarea name="answer" rows="4" required placeholder="Write your answer..."></textarea>
                        <button type="submit" class="answer-submit-btn">Submit Answer</button>
                    </form>
                </div>
            </div>

            <!-- Recent Questions Section -->
            <div class="recent-questions-wrapper">
                <section class="recent-questions">
                    <h2>Recent Questions</h2>
                    <div id="questionsContainer">
                        <?php if (!empty($questions)): ?>
                            <?php foreach($questions as $row): ?>
                                <div class="question" data-question-id="<?php echo htmlspecialchars($row['question_id']); ?>">
                                    <div class="question-header">
                                        <img src="images/userAvatar.jpg" alt="User Avatar" class="avatar">
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
                                        <?php if (!empty($row['photo_path'])): ?>
                                            <div class="question-photo">
                                                <img src="<?php echo htmlspecialchars($row['photo_path']); ?>" alt="Question Photo" class="question-photo-thumbnail">
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (isset($_SESSION['user_id'])): ?>
                                        <button onclick="openAnswerModal(<?php echo htmlspecialchars($row['question_id']); ?>)" class="answer-button">Answer</button>
                                    <?php else: ?>
                                        <a href="login.php" class="answer-button">Login to Answer</a>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No questions found.</p>
                        <?php endif; ?>
                    </div>
                </section>
            </div>
        </div>
        <footer>
            <p>&copy; <?php echo date('Y'); ?> VincenThinks. All rights reserved.</p>
        </footer>
    </div>
    <script src="JS/index.js"></script>
</body>
</html>