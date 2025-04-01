<?php
require_once '../includes/db_config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

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
        session_destroy();
        header("Location: ../login.php");
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
    <link rel="stylesheet" href="../assets/CSS/global.css">
    <link rel="stylesheet" href="../assets/CSS/header.css">
    <link rel="stylesheet" href="../assets/CSS/profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
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
                <a href="../logout">Logout (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a>
            <?php else: ?>
                <a href="../login">Login</a>
                <a href="../register">Register</a>
            <?php endif; ?>
        </nav>
        <button class="menu-toggle" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
    </header>

    <div class="overlay" id="overlay"></div>

    <main class="profile-container">
        <div class="profile-sidebar" id="profileSidebar">
            <div class="profile-summary">
                <?php 
                $user = get_user_data($conn, $_SESSION['user_id']);
                if (!empty($user['profile_picture'])): ?>
                    <img src="../<?php echo htmlspecialchars($user['profile_picture']); ?>" 
                        alt="Profile Picture" 
                        class="profile-avatar-img">
                <?php else: ?>
                    <img src="../assets/images/userAvatar.jpg" class="avatar">
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
                <a href="../index.php?tab=home" class="<?php echo $active_tab == 'home' ? 'active' : ''; ?>">
                    <i class="fas fa-arrow-left"></i> Back to Home
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
            <p>&copy; <?php echo date('Y'); ?> VincenThinks. All rights reserved.</p>
        </footer>
    <script src="../assets/JS/profile.js"></script>
    <script>
        // Sidebar toggle functionality
        const sidebarToggle = document.getElementById('sidebarToggle');
        const profileSidebar = document.getElementById('profileSidebar');
        const overlay = document.getElementById('overlay');

        sidebarToggle.addEventListener('click', () => {
            profileSidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        });

        overlay.addEventListener('click', () => {
            profileSidebar.classList.remove('active');
            overlay.classList.remove('active');
        });

        // Close sidebar when clicking on a menu item (for mobile)
        const menuLinks = document.querySelectorAll('.profile-menu a');
        menuLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                    profileSidebar.classList.remove('active');
                    overlay.classList.remove('active');
                }
            });
        });
    </script>
</body>
</html>