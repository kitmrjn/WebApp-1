<?php
session_start();
require_once 'db_config.php'; // includes $conn (PDO object)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirmPassword']); // New field for confirm password
    $course   = trim($_POST['course']); // Field for course/strand

    // Basic validation
    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword) || empty($course)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
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
            $sql = "INSERT INTO users (username, email, password, course) VALUES (:username, :email, :password, :course)";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':username', $username);
            $stmt->bindValue(':email', $email);
            $stmt->bindValue(':password', $hashedPassword);
            $stmt->bindValue(':course', $course);

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
    <link rel="stylesheet" href="CSS/global.css">
    <link rel="stylesheet" href="CSS/forms.css">
    <link rel="stylesheet" href="CSS/form-links.css">
</head>
<body>

<section class="form-section">
    <h2>Register</h2>
    <?php if (!empty($error)): ?>
        <div class="error-message"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="POST" action="">
        <label>Username</label>
        <input type="text" name="username" required>

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <label>Confirm Password</label>
        <input type="password" name="confirmPassword" required>

        <label>Course/Strand</label>
        <select name="course" required>
            <option value="" disabled selected>Select your course/strand</option>
            <optgroup label="College Courses">
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
            </optgroup>
            <optgroup label="Senior High School Strands">
                <option value="STEM">STEM - Science, Technology, Engineering, and Mathematics</option>
                <option value="ABM">ABM - Accountancy, Business, and Management</option>
                <option value="GAS">GAS - General Academic Strand</option>
                <option value="HE">HE - Home Economics</option>
                <option value="ICT">ICT - Information and Communications Technology</option>
            </optgroup>
            <optgroup label="Elementary">
                <option value="Elementary">Elementary</option>
            </optgroup>
        </select>

        <button type="submit">Register</button>
    </form>
    <p>Already have an account? <a href="login.php">Login here</a></p>
</section>

</body>
</html>