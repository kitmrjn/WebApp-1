<?php
require_once 'db_config.php';
require_once 'auth.php';
require_once 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ensure email exists in session
if (!isset($_SESSION['email'])) {
    $user_sql = "SELECT username, email FROM users WHERE user_id = :user_id";
    $user_stmt = $conn->prepare($user_sql);
    $user_stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $user_stmt->execute();
    $user = $user_stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
    } else {
        // Handle case where user doesn't exist
        session_destroy();
        header("Location: login.php");
        exit();
    }
}

$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'questions';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vincenthinks - My Profile</title>
    <link rel="stylesheet" href="CSS/global.css">
    <link rel="stylesheet" href="CSS/header.css">
    <link rel="stylesheet" href="CSS/profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
        <!-- Your existing header content -->
    </header>

    <main class="profile-container">
        <div class="profile-sidebar">
            <div class="profile-summary">
                <?php 
                $user = get_user_data($conn, $_SESSION['user_id']);
                if (!empty($user['profile_picture'])): ?>
                    <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" 
                        alt="Profile Picture" 
                        class="profile-avatar-img">
                <?php else: ?>
                    <img src="images/userAvatar.jpg" class="avatar">
                <?php endif; ?>
                <h2><?php echo htmlspecialchars($user['username']); ?></h2>
                <p class="profile-email"><?php echo htmlspecialchars($user['email']); ?></p>
            </div>

            <nav class="profile-menu">
                <a href="profile.php?tab=profile" class="<?php echo $active_tab == 'profile' ? 'active' : ''; ?>">
                    <i class="fas fa-user"></i> Profile Info
                </a>
                <a href="profile.php?tab=questions" class="<?php echo $active_tab == 'questions' ? 'active' : ''; ?>">
                    <i class="fas fa-question-circle"></i> Your Questions
                </a>
                <a href="profile.php?tab=pending" class="<?php echo $active_tab == 'pending' ? 'active' : ''; ?>">
                    <i class="fas fa-clock"></i> Pending Questions
                </a>
                <a href="profile.php?tab=answers" class="<?php echo $active_tab == 'answers' ? 'active' : ''; ?>">
                    <i class="fas fa-reply"></i> Your Answers
                </a>
                <a href="profile.php?tab=stats" class="<?php echo $active_tab == 'stats' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-bar"></i> Your Stats
                </a>
            </nav>
        </div>

        <div class="profile-content">
            <?php
            // Include the appropriate content based on active tab
            switch($active_tab) {
                case 'profile':
                    include 'profile_sections/profile_info.php';
                    break;
                case 'questions':
                    include 'profile_sections/your_questions.php';
                    break;
                case 'pending':
                    include 'profile_sections/pending_questions.php';
                    break;
                case 'answers':
                    include 'profile_sections/your_answers.php';
                    break;
                case 'stats':
                    include 'profile_sections/your_stats.php';
                    break;
                default:
                    include 'profile_sections/your_questions.php';
            }
            ?>
        </div>
    </main>

    <footer>
        <!-- Your existing footer content -->
    </footer>
    <script src="JS/profile.js"></script> 
</body>
</html>