<?php
require_once 'auth.php';    // ensures user is logged in
require_once 'db_config.php'; // includes $conn (PDO)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title   = trim($_POST['title']);
    $content = trim($_POST['content']);
    $user_id = $_SESSION['user_id'];

    if (!empty($title) && !empty($content)) {
        $sql = "INSERT INTO questions (user_id, title, content) VALUES (:uid, :title, :content)";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':uid', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':title', $title);
        $stmt->bindValue(':content', $content);

        if ($stmt->execute()) {
            // Redirect to home
            header("Location: index.php");
            exit();
        } else {
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
    <link rel="stylesheet" href="CSS/style.css">
</head>
<body>

<section class="form-section">
<h2>Post a Question</h2>
<?php if (!empty($error)): ?>
    <div class="error-message"><?php echo $error; ?></div>
<?php endif; ?>
<form method="POST" action="">
    <label>Title</label>
    <input type="text" name="title" required>

    <label>Question Content</label>
    <textarea name="content" rows="5" required></textarea>

    <button type="submit">Post</button>
</form>


<p style="text-align:center; margin-top:10px;">
    <a href="index.php" style="color:#800000; text-decoration:underline;">&larr; Back to Home</a>
</p>
</section>

</body>
</html>
