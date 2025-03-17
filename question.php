<?php
session_start();
require_once 'db_config.php'; // includes $conn (PDO)
require_once 'functions.php'; // include the time_ago function

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$question_id = intval($_GET['id']);

// Fetch question info
$qSql = "SELECT q.*, u.username
         FROM questions q
         JOIN users u ON q.user_id = u.user_id
         WHERE q.question_id = :qid";
$qStmt = $conn->prepare($qSql);
$qStmt->bindValue(':qid', $question_id, PDO::PARAM_INT);
$qStmt->execute();
$question = $qStmt->fetch(PDO::FETCH_ASSOC);

// Fetch photos for the question
$pSql = "SELECT photo_path FROM question_photos WHERE question_id = :qid";
$pStmt = $conn->prepare($pSql);
$pStmt->bindValue(':qid', $question_id, PDO::PARAM_INT);
$pStmt->execute();
$photos = $pStmt->fetchAll(PDO::FETCH_ASSOC);

// Handle new answer submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $answerContent = trim($_POST['answer']);
    $user_id       = $_SESSION['user_id'];

    if (!empty($answerContent)) {
        $aSql = "INSERT INTO answers (question_id, user_id, content) VALUES (:qid, :uid, :content)";
        $aStmt = $conn->prepare($aSql);
        $aStmt->bindValue(':qid', $question_id, PDO::PARAM_INT);
        $aStmt->bindValue(':uid', $user_id, PDO::PARAM_INT);
        $aStmt->bindValue(':content', $answerContent);
        $aStmt->execute();

        // Reload to show the new answer
        header("Location: question.php?id=$question_id");
        exit();
    }
}

// Fetch existing answers
$aSql = "SELECT a.*, u.username
         FROM answers a
         JOIN users u ON a.user_id = u.user_id
         WHERE a.question_id = :qid
         ORDER BY a.created_at DESC";
$aStmt = $conn->prepare($aSql);
$aStmt->bindValue(':qid', $question_id, PDO::PARAM_INT);
$aStmt->execute();
$answers = $aStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vincenthinks - Question</title>
    <link rel="stylesheet" href="CSS/global.css">
    <link rel="stylesheet" href="CSS/header.css">
    <link rel="stylesheet" href="CSS/search-bar.css">
    <link rel="stylesheet" href="CSS/footer.css">
    <link rel="stylesheet" href="CSS/questions.css">
    <link rel="stylesheet" href="CSS/modals.css">
    <link rel="stylesheet" href="CSS/question-detail.css">
    <link rel="stylesheet" href="CSS/forms.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="page-container">
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
                            <i class="fas fa-user-circle profile-icon"></i>
                            <div class="dropdown-content">
                                <a href="#">My Profile</a>
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
                        <input type="hidden" name="question_id" id="modalQuestionId" value="<?php echo $question_id; ?>">
                        <textarea name="answer" rows="4" required placeholder="Write your answer..."></textarea>
                        <button type="submit" class="answer-submit-btn">Submit Answer</button>
                    </form>
                </div>
            </div>

            <main class="question-detail-container">
                <?php if (($question)): ?>
                    <div class="recent-questions-wrapper">
                        <div class="recent-questions">
                            <div class="question">
                                <div class="question-header">
                                    <img src="images/userAvatar.jpg" alt="User Avatar" class="avatar">
                                    <div class="question-info">
                                        <h3><?php echo htmlspecialchars($question['title']); ?></h3>
                                        <p class="timestamp">
                                            <?php echo htmlspecialchars($question['username']); ?> • <?php echo time_ago($question['created_at']); ?>
                                        </p>
                                    </div>
                                </div>
                                <div class="question-content">
                                    <p><?php echo nl2br(htmlspecialchars($question['content'])); ?></p>
                                    <?php if (!empty($photos)): ?>
                                        <div class="question-photos">
                                            <?php foreach ($photos as $photo): ?>
                                                <img src="<?php echo htmlspecialchars($photo['photo_path']); ?>" alt="Question Photo" class="question-photo">
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <hr>

                            <div class="answers-container">
                                <h3>Answers:</h3>
                                <?php if (!empty($answers)): ?>
                                    <?php foreach ($answers as $answer): ?>
                                        <div class="answer-box">
                                            <p class="answer-meta">
                                                <strong><?php echo htmlspecialchars($answer['username']); ?></strong> • <?php echo time_ago($answer['created_at']); ?>
                                            </p>
                                            <div class="answer-content">
                                                <p><?php echo nl2br(htmlspecialchars($answer['content'])); ?></p>
                                            </div>
                                            <hr>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p>No answers yet.</p>
                                <?php endif; ?>
                            </div>

                            <?php if (isset($_SESSION['user_id'])): ?>
                                <button onclick="openAnswerModal()" class="answer-button">Post Your Answer</button>
                            <?php else: ?>
                                <p class="login-prompt"><a href="login.php">Login</a> to answer this question.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <p>Question not found.</p>
                <?php endif; ?>
            </main>
        </div>
        <footer>
            <p>&copy; <?php echo date('Y'); ?> VincenThinks. All rights reserved.</p>
        </footer>
    </div>
    <script src="JS/index.js"></script>
</body>
</html>