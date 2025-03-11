<?php
session_start();
require_once 'db_config.php'; // includes $conn (PDO object)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
            header("Location: index.php");
            exit();
        } else {
            $error = "Invalid username/email or password.";
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
</head>
<body>

<section class="form-section">
<h2>Login</h2>
<?php if (!empty($error)): ?>
    <div class="error-message"><?php echo $error; ?></div>
<?php endif; ?>
<form method="POST" action="">
    <label>Username or Email</label>
    <input type="text" name="usernameOrEmail" required>

    <label>Password</label>
    <input type="password" name="password" required>

    <button type="submit">Login</button>
</form>
<p>Don't have an account? <a href="register.php">Register here</a></p>
</section>

</body>
</html>
