<?php
session_start();
require_once 'includes/db_config.php'; // includes $conn (PDO object)

// reCAPTCHA Secret Key
$recaptchaSecretKey = '6LeJXvEqAAAAAKm0-NmvD-iraCVhy4h7IYO8kDxi'; // Replace with your Secret Key

// Initialize variables to hold form data
$username = $student_number = $email = $course = '';

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
        $student_number = trim($_POST['student_number']);
        $email    = trim($_POST['email']);
        $password = trim($_POST['password']);
        $confirmPassword = trim($_POST['confirmPassword']);
        $course   = trim($_POST['course']);

        // Validate full name
        if (empty($username)) {
            $error = "Full name is required.";
        } elseif (strlen($username) < 5 || strlen($username) > 50) {
            $error = "Full name must be between 5 and 50 characters.";
        } elseif (!preg_match('/^[a-zA-Z\s\-]+$/', $username)) {
            $error = "Full name can only contain letters, spaces, and hyphens.";
        }

       // Validate student number
        elseif (empty($student_number)) {
            $error = "Student number is required.";
        } elseif (!preg_match('/^AY[a-zA-Z0-9\-]+$/i', $student_number)) {
            $error = "Student number must start with 'AY' and can only contain letters, numbers, and hyphens.";
        } elseif (strlen($student_number) < 11 || strlen($student_number) > 20) {  // Changed from 5 to 11
            $error = "Student number must be between 11 and 20 characters.";
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
            // Check if student number or email already exists
            $checkSql = "SELECT * FROM users WHERE student_number = :student_number OR email = :email LIMIT 1";
            $stmt = $conn->prepare($checkSql);
            $stmt->bindValue(':student_number', $student_number);
            $stmt->bindValue(':email', $email);
            $stmt->execute();
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                $error = "Student number or Email already taken.";
            } else {
                // Hash the password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Insert new user
                $sql = "INSERT INTO users (username, student_number, email, password, course) VALUES (:username, :student_number, :email, :password, :course)";
                $stmt = $conn->prepare($sql);
                $stmt->bindValue(':username', $username);
                $stmt->bindValue(':student_number', $student_number);
                $stmt->bindValue(':email', $email);
                $stmt->bindValue(':password', $hashedPassword);
                $stmt->bindValue(':course', $course);

                if ($stmt->execute()) {
                    // Success: redirect to login
                    header("Location: login");
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
            <h2>Register</h2>
            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <label>Full Name</label>
                <input type="text" name="username" required minlength="5" maxlength="50" pattern="[a-zA-Z\s\-]+" title="Full name can only contain letters, spaces, and hyphens." value="<?php echo htmlspecialchars($username); ?>">

                <label>Student Number (must start with AY)</label>
                <input type="text" name="student_number" required minlength="11" maxlength="20" pattern="AY[a-zA-Z0-9\-]+" title="Student number must start with 'AY', be 11-20 characters long, and can only contain letters, numbers, and hyphens" value="<?php echo htmlspecialchars($student_number); ?>">
                
                <label>Email</label>
                <input type="email" name="email" required value="<?php echo htmlspecialchars($email); ?>">

                <label>Password</label>
                <input type="password" name="password" required minlength="8" maxlength="20">

                <label>Confirm Password</label>
                <input type="password" name="confirmPassword" required minlength="8" maxlength="20">

                <label>Course/Strand</label>
                <select name="course" required>
                    <option value="" disabled selected>Select your course/strand</option>
                    <optgroup label="College Courses">
                        <option value="BSIT" <?php if ($course === 'BSIT') echo 'selected'; ?>>BSIT - Bachelor of Science in Information Technology</option>
                        <option value="BSHM" <?php if ($course === 'BSHM') echo 'selected'; ?>>BSHM - Bachelor of Science in Hospitality Management</option>
                        <option value="BSTM" <?php if ($course === 'BSTM') echo 'selected'; ?>>BSTM - Bachelor of Science in Tourism Management</option>
                        <option value="BSBA" <?php if ($course === 'BSBA') echo 'selected'; ?>>BSBA - Bachelor of Science in Business Administration</option>
                        <option value="BSA" <?php if ($course === 'BSA') echo 'selected'; ?>>BSA - Bachelor of Science in Accountancy</option>
                        <option value="BSCRIM" <?php if ($course === 'BSCRIM') echo 'selected'; ?>>BSCRIM - Bachelor of Science in Criminology</option>
                        <option value="BSED" <?php if ($course === 'BSED') echo 'selected'; ?>>BSED - Bachelor of Secondary Education</option>
                        <option value="BEED" <?php if ($course === 'BEED') echo 'selected'; ?>>BEED - Bachelor of Elementary Education</option>
                        <option value="BSPSY" <?php if ($course === 'BSPSY') echo 'selected'; ?>>BSPSY - Bachelor of Science in Psychology</option>
                        <option value="BPE" <?php if ($course === 'BPE') echo 'selected'; ?>>BPE - Bachelor of Physical Education</option>
                        <option value="BSECE" <?php if ($course === 'BSECE') echo 'selected'; ?>>BSECE - Bachelor of Science in Early Childhood Education</option>
                    </optgroup>
                    <optgroup label="Senior High School Strands">
                        <option value="STEM" <?php if ($course === 'STEM') echo 'selected'; ?>>STEM - Science, Technology, Engineering, and Mathematics</option>
                        <option value="ABM" <?php if ($course === 'ABM') echo 'selected'; ?>>ABM - Accountancy, Business, and Management</option>
                        <option value="GAS" <?php if ($course === 'GAS') echo 'selected'; ?>>GAS - General Academic Strand</option>
                        <option value="HE" <?php if ($course === 'HE') echo 'selected'; ?>>HE - Home Economics</option>
                        <option value="ICT" <?php if ($course === 'ICT') echo 'selected'; ?>>ICT - Information and Communications Technology</option>
                    </optgroup>
                    <optgroup label="Faculty">
                        <option value="FACULTY" <?php if ($course === 'FACULTY') echo 'selected'; ?>>FACULTY - Faculty</option>
                    </optgroup>
                    <optgroup label="Junior High School">
                        <option value="JHS" <?php if ($course === 'JHS') echo 'selected'; ?>>JHS - Junior High School</option>
                    </optgroup>
                </select>

                <!-- Centered reCAPTCHA Widget -->
                <div class="g-recaptcha" data-sitekey="6LeJXvEqAAAAAARS60cGZML2OOi0US8HT1qEtGhJ"></div>

                <button type="submit">Register</button>
            </form>
            <p>Already have an account? <a href="login">Login here</a></p>
        </section>
    </main>
</body>
</html>