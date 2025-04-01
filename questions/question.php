<?php
session_start();
require_once '../includes/db_config.php'; 
require_once '../includes/functions.php'; 

if (!isset($_GET['id'])) {
    header("Location: ../index");
    exit();
}

$question_id = intval($_GET['id']);

$qSql = "SELECT q.*, u.username, u.profile_picture
         FROM questions q
         JOIN users u ON q.user_id = u.user_id
         WHERE q.question_id = :qid AND q.status = 'approved'";
$qStmt = $conn->prepare($qSql);
$qStmt->bindValue(':qid', $question_id, PDO::PARAM_INT);
$qStmt->execute();
$question = $qStmt->fetch(PDO::FETCH_ASSOC);

$pSql = "SELECT photo_path FROM question_photos WHERE question_id = :qid";
$pStmt = $conn->prepare($pSql);
$pStmt->bindValue(':qid', $question_id, PDO::PARAM_INT);
$pStmt->execute();
$photos = $pStmt->fetchAll(PDO::FETCH_ASSOC);

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

        header("Location: question?id=$question_id");
        exit();
    }
}

      //get user database information
$aSql = "SELECT a.*, u.username, u.profile_picture,
                IFNULL(r.is_helpful, 0) AS is_helpful
         FROM answers a
         JOIN users u ON a.user_id = u.user_id
         LEFT JOIN answer_ratings r ON a.answer_id = r.answer_id AND r.user_id = :user_id
         WHERE a.question_id = :qid
         ORDER BY a.created_at DESC";
$aStmt = $conn->prepare($aSql);
$aStmt->bindValue(':qid', $question_id, PDO::PARAM_INT);
$aStmt->bindValue(':user_id', isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0, PDO::PARAM_INT);
$aStmt->execute();
$answers = $aStmt->fetchAll(PDO::FETCH_ASSOC);

$currentUser = isset($_SESSION['user_id']) ? get_user_data($conn, $_SESSION['user_id']) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#4CAF50">
    <title>Vincenthinks - Question</title>
    <link rel="stylesheet" href="../assets/CSS/global.css">
    <link rel="stylesheet" href="../assets/CSS/header.css">
    <link rel="stylesheet" href="../assets/CSS/footer.css">
    <link rel="stylesheet" href="../assets/CSS/questions.css">
    <link rel="stylesheet" href="../assets/CSS/modals.css">
    <link rel="stylesheet" href="../assets/CSS/question-detail.css">
    <link rel="stylesheet" href="../assets/CSS/forms.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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
    <div class="page-container">
        <div class="content-wrap">
            <header>
                <a href="../index" class="logo-link">
                    <div class="logo">
                        <img src="../assets/images/svcc.jpg" alt="VincentThinks Logo" class="nav-logo">
                        <h1>VincenThinks</h1>
                    </div>
                </a>
                <nav>
                    <a href="../index">Home</a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <?php else: ?>
                        <a href="../login">Login</a>
                        <a href="../register">Register</a>
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
                                    <?php if (!empty($question['profile_picture'])): ?>
                                        <img src="../<?php echo htmlspecialchars($question['profile_picture']); ?>" alt="User Avatar" class="avatar">
                                    <?php else: ?>
                                        <i class="fas fa-user-circle avatar-icon"></i>
                                    <?php endif; ?>
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
                                        <div class="question-photos-answer">
                                            <?php foreach ($photos as $photo): ?>
                                                <img src="/webapp/uploads/<?php echo htmlspecialchars($photo['photo_path']); ?>" alt="Question Photo" class="question-photo">
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <hr>

                            <!-- Answers Section -->
                            <div class="answers-container">
                                <h3>Answers:</h3>
                                <?php if (!empty($answers)): ?>
                                    <?php foreach ($answers as $answer): ?>
                                        <div class="answer-box" data-answer-id="<?php echo $answer['answer_id']; ?>">
                                            <div class="answer-meta">
                                                <div class="answer-user">
                                                    <?php if (!empty($answer['profile_picture'])): ?>
                                                        <img src="../<?php echo htmlspecialchars($answer['profile_picture']); ?>" alt="User Avatar" class="avatar">
                                                    <?php else: ?>
                                                        <i class="fas fa-user-circle avatar-icon"></i>
                                                    <?php endif; ?>
                                                    <span>
                                                        <strong><?php echo htmlspecialchars($answer['username']); ?></strong> • <?php echo time_ago($answer['created_at']); ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="answer-content-wrapper">
                                                <div class="answer-content">
                                                    <p><?php echo nl2br(htmlspecialchars($answer['content'])); ?></p>
                                                </div>
                                                <div class="answer-icons">
                                                    <div class="answer-rating" data-tooltip="Helpful">
                                                        <i class="bi bi-star-fill <?php echo isset($answer['is_helpful']) && $answer['is_helpful'] ? 'selected' : ''; ?>" 
                                                        data-is-helpful="<?php echo isset($answer['is_helpful']) && $answer['is_helpful'] ? 'true' : 'false'; ?>">
                                                        </i>
                                                        <span class="rating-count"><?php echo isset($answer['helpful_count']) ? $answer['helpful_count'] : 0; ?></span>
                                                    </div>
                                                    <div class="answer-report" data-answer-id="<?php echo $answer['answer_id']; ?>" data-tooltip="Report">
                                                        <i class="bi bi-flag-fill" onclick="reportAnswer(<?php echo $answer['answer_id']; ?>)"></i>
                                                    </div>
                                                </div>
                                            </div>
                                            <hr>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p>No answers yet.</p>
                                <?php endif; ?>
                            </div>

                            <?php if (isset($_SESSION['user_id'])): ?>
                                <div class="form-actions">
                                    <div class="answer-prompt">
                                        <button onclick="openAnswerModal()" class="answer-button">Post Your Answer</button>
                                    </div>
                                    <a href="../index" class="back-to-home">
                                        <i class="fas fa-arrow-left"></i> Back to Home
                                    </a>
                                </div>
                            <?php else: ?>
                                <p class="login-prompt"><a href="../login">Login</a> to answer this question.</p>
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
    <script src="../assets/JS/index.js"></script>
</body>
</html>