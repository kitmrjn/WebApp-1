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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vincenthinks - Home</title>
    <link rel="stylesheet" href="CSS/style.css">
</head>
<body>
    <header>
        <a href="index.php" class="logo-link">
            <div class="logo">
                <img src="images/svcc.jpg" alt="VincentThinks Logo" class="nav-logo">
                <h1>VincenThinks</h1>
            </div>
        </a>
        <div class="search-bar">
            <input type="text" id="searchInput" placeholder="Search questions..." onkeyup="searchQuestions()">
        </div>
        <nav>
            <a href="index.php">Home</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="post_question.php">Ask a Question</a>
                <a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
        </nav>
    </header>

    <!-- Recent Questions Section -->
    <section class="recent-questions">
        <h2>Recent Questions</h2>
        <div id="questionsContainer">
            <?php if (!empty($questions)): ?>
                <?php foreach($questions as $row): ?>
                    <div class="question">
                        <div class="question-header">
                            <img src="images/userAvatar.jpg" alt="User Avatar" class="avatar">
                            <div class="question-info">
                                <h3>
                                    <a href="question.php?id=<?php echo $row['question_id']; ?>">
                                        <?php echo htmlspecialchars($row['title']); ?>
                                    </a>
                                </h3>
                                <p class="timestamp">Asked by <?php echo htmlspecialchars($row['username']); ?> on <?php echo $row['created_at']; ?></p>
                            </div>
                        </div>
                        <p class="answer-preview">
                            <?php echo mb_strimwidth(htmlspecialchars($row['content']), 0, 100, "..."); ?>
                        </p>
                        <a href="question.php?id=<?php echo $row['question_id']; ?>" class="answer-button">View Answers</a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No questions found.</p>
            <?php endif; ?>
        </div>
    </section>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> VincenThinks. All rights reserved.</p>
    </footer>

    <script>
        function searchQuestions() {
            let input = document.getElementById("searchInput").value.toLowerCase();
            let questions = document.getElementsByClassName("question");
            
            for (let i = 0; i < questions.length; i++) {
                let title = questions[i].querySelector("h3 a").innerText.toLowerCase();
                let content = questions[i].querySelector(".answer-preview").innerText.toLowerCase();
                
                if (title.includes(input) || content.includes(input)) {
                    questions[i].style.display = "block";
                } else {
                    questions[i].style.display = "none";
                }
            }
        }
    </script>
</body>
</html>
