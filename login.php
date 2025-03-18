<?php
session_start();
require_once 'db_config.php'; // includes $conn (PDO object)

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
                    header("Location: admin_dashboard.php");
                } else {
                    header("Location: index.php");
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
    <link rel="stylesheet" href="CSS/global.css">
    <link rel="stylesheet" href="CSS/forms.css">
    <link rel="stylesheet" href="CSS/form-links.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script src="JS/login.js" defer></script>
</head>
<body class="auth-page">

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
    <p>Don't have an account? <a href="register.php">Register here</a></p>
</section>

</body>
</html>