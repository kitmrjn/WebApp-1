<?php
require_once 'auth.php';    // ensures user is logged in
require_once 'db_config.php'; // includes $conn (PDO)

// Initialize error variable
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content  = trim($_POST['content']);
    $category = trim($_POST['category']); // Get category (this will be the title)
    $user_id  = $_SESSION['user_id'];

    if (!empty($content) && !empty($category)) {
        // Use prepared statement to prevent SQL injection
        $sql = "INSERT INTO questions (user_id, title, content, category) VALUES (:uid, :title, :content, :category)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':uid', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':title', $category, PDO::PARAM_STR); // Set title to the selected category
        $stmt->bindParam(':content', $content, PDO::PARAM_STR);
        $stmt->bindParam(':category', $category, PDO::PARAM_STR);

        if ($stmt->execute()) {
            // Redirect to home
            header("Location: index.php");
            exit();
        } else {
            // Log the error
            error_log("Error posting question: " . print_r($stmt->errorInfo(), true));
            $error = "Error posting question.";
        }
    } else {
        $error = "All fields are required.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Post a Question</title>
    <link rel="stylesheet" href="CSS/global.css">
    <link rel="stylesheet" href="CSS/header.css">
    <link rel="stylesheet" href="CSS/forms.css">
    <link rel="stylesheet" href="CSS/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<header>
    <a href="index.php" class="logo-link">
        <div class="logo">
            <img src="images/svcc.jpg" alt="VincentThinks Logo" class="nav-logo">
            <h1>VincenThinks</h1>
        </div>
    </a>
    <nav>
        <a href="index.php">Home</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </nav>
</header>

<section class="form-section">
    <h2>Post a Question</h2>
    <?php if (!empty($error)): ?>
        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="POST" action="">
        <!-- Add category selection -->
        <label for="category">Category</label>
        <select id="category" name="category" required>
            <option value="">Select Category</option>
            <option value="BSIT">BSIT - Bachelor of Science in Information Technology</option>
            <option value="BSHM">BSHM - Bachelor of Science in Hospitality Management</option>
            <option value="BSTM">BSTM - Bachelor of Science in Tourism Management</option>
            <option value="BSBA">BSBA - Bachelor of Science in Business Administration</option>
            <option value="BSA">BSA - Bachelor of Science in Accountancy</option>
            <option value="BSCRIM">BSCRIM - Bachelor of Science in Criminology</option>
            <option value="BSED">BSED - Bachelor of Secondary Education</option>
            <option value="BEED">BEED - Bachelor of Elementary Education</option>
            <option value="BSPSY">BSPSY - Bachelor of Science in Psychology</option>
            <option value="BPE">BPE - Bachelor of Physical Education</option>
            <option value="BSECE">BSECE - Bachelor of Science in Early Childhood Education</option>
            <option value="STEM">STEM - Science, Technology, Engineering, and Mathematics</option>
            <option value="ABM">ABM - Accountancy, Business, and Management</option>
            <option value="GAS">GAS - General Academic Strand</option>
            <option value="HE">HE - Home Economics</option>
            <option value="ICT">ICT - Information and Communications Technology</option>
        </select>

        <label for="content">Question Content</label>
        <textarea id="content" name="content" rows="5" required></textarea>

        <button type="submit">Post</button>
        <div class="back-to-home-container">
            <a href="index.php" class="back-to-home">
                <i class="fas fa-arrow-left"></i> Back to Home
            </a>
        </div>
    </form>
</section>

<footer>
    <p>&copy; <?php echo date('Y'); ?> VincenThinks. All rights reserved.</p>
</footer>

</body>
</html>