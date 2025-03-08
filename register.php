<?php
session_start();
require_once 'db_config.php'; // includes $conn (PDO object)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Basic validation
    if (empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } else {
        // Check if username or email already exists
        $checkSql = "SELECT * FROM users WHERE username = :username OR email = :email LIMIT 1";
        $stmt = $conn->prepare($checkSql);
        $stmt->bindValue(':username', $username);
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $error = "Username or Email already taken.";
        } else {
            // Hash the password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user
            $sql = "INSERT INTO users (username, email, password) VALUES (:username, :email, :password)";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':username', $username);
            $stmt->bindValue(':email', $email);
            $stmt->bindValue(':password', $hashedPassword);

            if ($stmt->execute()) {
                // Success: redirect to login
                header("Location: login.php");
                exit();
            } else {
                $error = "Error creating account.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" href="CSS/style.css">
</head>
<body>
<h2>Register</h2>
<?php if (!empty($error)): ?>
    <div class="error-message"><?php echo $error; ?></div>
<?php endif; ?>
<form method="POST" action="">
    <label>Username</label>
    <input type="text" name="username" required>

    <label>Email</label>
    <input type="text" name="email" required>

    <label>Password</label>
    <input type="password" name="password" required>

    <button type="submit">Register</button>
</form>
<p>Already have an account? <a href="login.php">Login here</a></p>
</body>
</html>
