<?php
session_start();
require_once 'db_config.php'; // includes $conn (PDO)

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$question_id = intval($_GET['id']);

// 1. Fetch question info
$qSql = "SELECT q.*, u.username
         FROM questions q
         JOIN users u ON q.user_id = u.user_id
         WHERE q.question_id = :qid";
$qStmt = $conn->prepare($qSql);
$qStmt->bindValue(':qid', $question_id, PDO::PARAM_INT);
$qStmt->execute();
$question = $qStmt->fetch(PDO::FETCH_ASSOC);

// 2. Handle new answer submission
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

// 3. Fetch existing answers
$aSql = "SELECT a.*, u.username
         FROM answers a
         JOIN users u ON a.user_id = u.user_id
         WHERE a.question_id = :qid
         ORDER BY a.created_at ASC";
$aStmt = $conn->prepare($aSql);
$aStmt->bindValue(':qid', $question_id, PDO::PARAM_INT);
$aStmt->execute();
$answers = $aStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Question Details</title>
    <link rel="stylesheet" href="CSS/global.css">
    <link rel="stylesheet" href="CSS/header.css">
    <link rel="stylesheet" href="CSS/question-detail.css">
    <link rel="stylesheet" href="CSS/forms.css">
</head>
<body>
    <header>
        <h1>Vincenthinks</h1>
        <nav>
            <a href="index.php">Home</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="logout.php">Logout (<?php echo $_SESSION['username']; ?>)</a>
            <?php else: ?>
                <a href="login.php">Login</a>
            <?php endif; ?>
        </nav>
    </header>

    <main class="question-detail-container">
        <?php if ($question)): ?>
            <div class="question-box">
                <h2><?php echo htmlspecialchars($question['title']); ?></h2> <!-- Title is now the category -->
                <p class="question-meta">
                    Asked by: <?php echo htmlspecialchars($question['username']); ?> on <?php echo $question['created_at']; ?>
                </p>
                <div class="question-content">
                    <p><?php echo nl2br(htmlspecialchars($question['content'])); ?></p>
                    <?php if (!empty($question['photo_path'])): ?>
                        <div class="question-photo">
                            <img src="<?php echo htmlspecialchars($question['photo_path']); ?>" alt="Question Photo" class="question-photo-full">
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
                                <strong><?php echo htmlspecialchars($answer['username']); ?>:</strong>
                                <em>Posted on: <?php echo $answer['created_at']; ?></em>
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
                <form method="POST" action="" class="answer-form">
                    <label>Your Answer:</label>
                    <textarea name="answer" rows="4" required></textarea>
                    <button type="submit">Submit Answer</button>
                </form>
            <?php else: ?>
                <p class="login-prompt"><a href="login.php">Login</a> to answer this question.</p>
            <?php endif; ?>
        <?php else: ?>
            <p>Question not found.</p>
        <?php endif; ?>
    </main>
</body>
</html>