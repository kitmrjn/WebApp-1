<?php
session_start();
require_once 'db_config.php'; // includes $conn (PDO object)

// reCAPTCHA Secret Key
$recaptchaSecretKey = '6LeJXvEqAAAAAKm0-NmvD-iraCVhy4h7IYO8kDxi'; // Replace with your Secret Key

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify reCAPTCHA
    $recaptchaResponse = $_POST['g-recaptcha-response'];
    $verifyUrl = "https://www.google.com/recaptcha/api/siteverify?secret=$recaptchaSecretKey&response=$recaptchaResponse";
    $response = file_get_contents($verifyUrl);
    $responseData = json_decode($response);

    if (!$responseData->success) {
        $error = "reCAPTCHA verification failed. Please try again.";
    } else {
        $username = trim($_POST['username']);
        $email    = trim($_POST['email']);
        $password = trim($_POST['password']);
        $confirmPassword = trim($_POST['confirmPassword']);
        $course   = trim($_POST['course']);

        // Validate username
        if (empty($username)) {
            $error = "Username is required.";
        } elseif (strlen($username) < 5 || strlen($username) > 20) {
            $error = "Username must be between 5 and 20 characters.";
        } elseif (preg_match('/^[0-9]+$/', $username)) {
            $error = "Username cannot be only numbers.";
        } elseif (!preg_match('/^[a-zA-Z0-9-]+$/', $username)) {
            $error = "Username can only contain letters, numbers, and hyphens (-).";
        }

        // Validate password
        elseif (empty($password)) {
            $error = "Password is required.";
        } elseif (strlen($password) < 8 || strlen($password) > 20) {
            $error = "Password must be between 8 and 20 characters.";
        }

        // Validate confirm password
        elseif ($password !== $confirmPassword) {
            $error = "Passwords do not match.";
        }

        // Validate email
        elseif (empty($email)) {
            $error = "Email is required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format.";
        }

        // Validate course
        elseif (empty($course)) {
            $error = "Course/Strand is required.";
        }

        // If no errors, proceed with registration
        else {
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
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" href="CSS/global.css">
    <link rel="stylesheet" href="CSS/forms.css">
    <link rel="stylesheet" href="CSS/form-links.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script src="JS/register.js" defer></script>
</head>
<body>

<section class="form-section">
    <h2>Register</h2>
    <?php if (!empty($error)): ?>
        <div class="error-message"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="POST" action="">
        <label>Username</label>
        <input type="text" name="username" required minlength="5" maxlength="20" pattern="[a-zA-Z0-9-]+" title="Username can only contain letters, numbers, and hyphens (-).">

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required minlength="8" maxlength="20">

        <label>Confirm Password</label>
        <input type="password" name="confirmPassword" required minlength="8" maxlength="20">

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

        <!-- Centered reCAPTCHA Widget -->
        <div class="g-recaptcha" data-sitekey="6LeJXvEqAAAAAARS60cGZML2OOi0US8HT1qEtGhJ"></div>

        <button type="submit">Register</button>
    </form>
    <p>Already have an account? <a href="login.php">Login here</a></p>
</section>

</body>
</html>