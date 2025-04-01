<?php
session_start();
require_once 'includes/db_config.php'; // includes $conn (PDO object)

// reCAPTCHA Secret Key
$recaptchaSecretKey = '6LeJXvEqAAAAAKm0-NmvD-iraCVhy4h7IYO8kDxi'; // Replace with your Secret Key

// Initialize variables to hold form data
$usernameOrEmail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify reCAPTCHA
    $recaptchaResponse = $_POST['g-recaptcha-response'];
    $verifyUrl = "https://www.google.com/recaptcha/api/siteverify?secret=$recaptchaSecretKey&response=$recaptchaResponse";
    $response = file_get_contents($verifyUrl);
    $responseData = json_decode($response);

    if (!$responseData->success) {
        $error = "reCAPTCHA verification failed. Please try again.";
    } else {
        $usernameOrEmail = trim($_POST['usernameOrEmail']);
        $password        = trim($_POST['password']);

        if (empty($usernameOrEmail) || empty($password)) {
            $error = "All fields are required.";
        } else {
            // Fetch user by username or email
            $sql = "SELECT * FROM users WHERE username = :userOrEmail OR email = :userOrEmail LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':userOrEmail', $usernameOrEmail);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                // Valid login
                $_SESSION['user_id']  = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role']     = $user['role']; // Store user role in session

                // Redirect admins to the admin dashboard
                if ($user['role'] === 'admin') {
                    header("Location: admin/admin_dashboard");
                } else {
                    header("Location: index");
                }
                exit();
            } else {
                $error = "Invalid username/email or password.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Ensure proper scaling -->
    <link rel="stylesheet" href="assets/CSS/global.css">
    <link rel="stylesheet" href="assets/CSS/forms.css">
    <link rel="stylesheet" href="assets/CSS/form-links.css">
    <meta name="theme-color" content="#4CAF50">
    <link rel="apple-touch-icon" sizes="57x57" href="assets/images/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="assets/images/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="assets/images/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="assets/images/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="assets/images/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="assets/images/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="assets/images/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="assets/images/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/images/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192"  href="assets/images/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="assets/images/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/images/favicon-16x16.png">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script src="assets/JS/login.js" defer></script>
</head>
<body class="auth-page">
    <!-- Non-clickable header -->
    <header class="auth-header">
        <div class="logo">
            <img src="assets/images/svcc.jpg" alt="VincentThinks Logo" class="nav-logo">
            <h1>VincenThinks</h1>
        </div>
    </header>

    <main>
        <section class="form-section">
            <h2>Login</h2>
            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <label>Username or Email</label>
                <input type="text" name="usernameOrEmail" required value="<?php echo htmlspecialchars($usernameOrEmail); ?>">

                <label>Password</label>
                <input type="password" name="password" required>

                <!-- Centered reCAPTCHA Widget -->
                <div class="g-recaptcha" data-sitekey="6LeJXvEqAAAAAARS60cGZML2OOi0US8HT1qEtGhJ"></div>

                <button type="submit">Login</button>
            </form>
            <p>Don't have an account? <a href="register">Register here</a></p>
        </section>
    </main>
</body>
</html>