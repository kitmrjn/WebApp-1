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

// Handle new answer submission from modal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id']) && isset($_POST['question_id'])) {
    $answerContent = trim($_POST['answer']);
    $question_id   = intval($_POST['question_id']);
    $user_id       = $_SESSION['user_id'];

    if (!empty($answerContent) && $question_id > 0) {
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
    <link rel="stylesheet" href="CSS/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        /* Add smooth transition for expanding questions */
        .question {
            transition: all 0.5s ease;
            overflow: hidden;
            max-height: 150px; /* Initial height */
        }

        .question.expanded {
            max-height: 1000px; /* Expanded height */
            overflow-y: auto; /* Make it scrollable */
        }
    </style>
</head>
<body>
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
                    <div class="question" data-question-id="<?php echo htmlspecialchars($row['question_id']); ?>">
                        <div class="question-header">
                            <img src="images/userAvatar.jpg" alt="User Avatar" class="avatar">
                            <div class="question-info">
                                <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                                <p class="timestamp">Asked by <?php echo htmlspecialchars($row['username']); ?> on <?php echo htmlspecialchars($row['created_at']); ?></p>
                            </div>
                        </div>
                        <p class="answer-preview">
                            <?php echo mb_strimwidth(htmlspecialchars($row['content']), 0, 100, "..."); ?>
                        </p>
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

    <footer>
        <p>&copy; <?php echo date('Y'); ?> VincenThinks. All rights reserved.</p>
    </footer>

    <script>
     // Function to open the reply modal
        function openReplyModal(parentId) {
            document.getElementById('modalParentId').value = parentId;
            document.getElementById('answerModal').style.display = 'block';
        }

        // Function to handle question click
        document.querySelectorAll('.question').forEach(question => {
            question.addEventListener('click', function() {
                const questionId = this.getAttribute('data-question-id');

                // Remove all other questions
                document.querySelectorAll('.question').forEach(q => {
                    if (q !== this) {
                        q.remove(); // Remove the question from the DOM
                    }
                });

                // Check if answers are already loaded
                const answersDiv = this.querySelector('.answers');
                if (answersDiv) {
                    // If answers are already loaded, just expand the question
                    this.classList.add('expanded');
                    return; // Exit the function
                }

                // Fetch and display answers
                fetch(`get_answers.php?question_id=${questionId}`)
                    .then(response => response.text())
                    .then(data => {
                        // Create a new div to display the answers
                        const answersDiv = document.createElement('div');
                        answersDiv.className = 'answers';
                        answersDiv.innerHTML = data;

                        // Append answers to the expanded question
                        this.appendChild(answersDiv);

                        // Expand the question smoothly
                        this.classList.add('expanded');
                    })
                    .catch(error => console.error('Error fetching answers:', error));
            });
        });

        // Search functionality
        function searchQuestions() {
            let input = document.getElementById("searchInput").value.toLowerCase();
            let questions = document.getElementsByClassName("question");
            
            for (let i = 0; i < questions.length; i++) {
                let title = questions[i].querySelector("h3").innerText.toLowerCase();
                let content = questions[i].querySelector(".answer-preview").innerText.toLowerCase();
                
                if (title.includes(input) || content.includes(input)) {
                    questions[i].style.display = "block";
                } else {
                    questions[i].style.display = "none";
                }
            }
        }

        // Modal handling
        function openAnswerModal(questionId) {
            document.getElementById('modalQuestionId').value = questionId;
            document.getElementById('answerModal').style.display = 'block';
        }

        document.querySelector('.close').addEventListener('click', function() {
            document.getElementById('answerModal').style.display = 'none';
        });

        window.onclick = function(event) {
            const modal = document.getElementById('answerModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>