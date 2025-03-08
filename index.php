<?php
session_start();
require_once 'db_config.php'; // includes $conn (PDO)

// Fetch the latest questions
$sql = "SELECT q.*, u.username
        FROM questions q
        JOIN users u ON q.user_id = u.user_id
        ORDER BY q.created_at DESC";
$stmt = $conn->query($sql);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Vincenthinks - Home</title>
    <link rel="stylesheet" href="CSS/style.css">
</head>
<body>
<header>
    <h1>Vincenthinks</h1>
    <nav>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="post_question.php">Post a Question</a>
            <a href="logout.php">Logout (<?php echo $_SESSION['username']; ?>)</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </nav>
</header>

<h2>Recent Questions</h2>
<?php if (!empty($questions)): ?>
    <?php foreach($questions as $row): ?>
        <div class="question-box">
            <h3>
              <a href="question.php?id=<?php echo $row['question_id']; ?>">
                <?php echo htmlspecialchars($row['title']); ?>
              </a>
            </h3>
            <p>Posted by: <?php echo htmlspecialchars($row['username']); ?></p>
            <p>On: <?php echo $row['created_at']; ?></p>
            <p>
              <?php 
                // Show first 100 characters, if you want
                echo mb_strimwidth(htmlspecialchars($row['content']), 0, 100, "...");
              ?>
            </p>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>No questions found.</p>
<?php endif; ?>
</body>
</html>
