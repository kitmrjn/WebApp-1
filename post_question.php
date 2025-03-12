<?php
require_once 'auth.php';    // Ensures user is logged in
require_once 'db_config.php'; // Includes $conn (PDO)

// Initialize error variable
$error = "";

// Function to resize image
function resizeImage($file, $max_width, $max_height) {
    // Check if the file exists
    if (!file_exists($file)) {
        throw new Exception("File does not exist: $file");
    }

    // Get image dimensions and type
    list($width, $height, $type) = getimagesize($file);

    // Calculate aspect ratio
    $ratio = $width / $height;
    if ($max_width / $max_height > $ratio) {
        $max_width = $max_height * $ratio;
    } else {
        $max_height = $max_width / $ratio;
    }

    // Create a new image resource
    $src = imagecreatefromstring(file_get_contents($file));
    if ($src === false) {
        throw new Exception("Failed to create image from string.");
    }

    $dst = imagecreatetruecolor($max_width, $max_height);
    if ($dst === false) {
        imagedestroy($src);
        throw new Exception("Failed to create true color image.");
    }

    // Resize the image
    if (!imagecopyresampled($dst, $src, 0, 0, 0, 0, $max_width, $max_height, $width, $height)) {
        imagedestroy($src);
        imagedestroy($dst);
        throw new Exception("Failed to resize the image.");
    }

    // Save the resized image
    $resized_file = tempnam(sys_get_temp_dir(), 'resized');
    switch ($type) {
        case IMAGETYPE_JPEG:
            if (!imagejpeg($dst, $resized_file, 90)) { // 90% quality
                imagedestroy($src);
                imagedestroy($dst);
                throw new Exception("Failed to save JPEG image.");
            }
            break;
        case IMAGETYPE_PNG:
            if (!imagepng($dst, $resized_file, 9)) { // 9 = compression level
                imagedestroy($src);
                imagedestroy($dst);
                throw new Exception("Failed to save PNG image.");
            }
            break;
        default:
            imagedestroy($src);
            imagedestroy($dst);
            throw new Exception("Unsupported image type.");
    }

    // Free up memory
    imagedestroy($src);
    imagedestroy($dst);

    return $resized_file;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content  = trim($_POST['content']);
    $category = trim($_POST['category']); // Get category (this will be the title)
    $user_id  = $_SESSION['user_id'];

    // Handle file upload
    $photo_path = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/'; // Directory to store uploaded files
        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0755, true)) {
                $error = "Failed to create upload directory.";
            }
        }

        $file_name = basename($_FILES['photo']['name']);
        $file_tmp = $_FILES['photo']['tmp_name'];
        $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Validate file type (only allow JPEG and PNG)
        $allowed_types = ['jpg', 'jpeg', 'png'];
        if (!in_array($file_type, $allowed_types)) {
            $error = "Only JPG, JPEG, and PNG files are allowed.";
        } else {
            try {
                // Resize the image to a maximum width of 400px
                $resized_file = resizeImage($file_tmp, 400, 400);

                // Generate a unique file name to avoid conflicts
                $unique_name = uniqid() . '.' . $file_type;
                $photo_path = $upload_dir . $unique_name;

                // Move the resized file to the uploads directory
                if (!rename($resized_file, $photo_path)) {
                    $error = "Failed to upload the file.";
                }
            } catch (Exception $e) {
                $error = "Error processing image: " . $e->getMessage();
            }
        }
    }

    if (!empty($content) && !empty($category) && empty($error)) {
        // Use prepared statement to prevent SQL injection
        $sql = "INSERT INTO questions (user_id, title, content, category, photo_path) VALUES (:uid, :title, :content, :category, :photo_path)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':uid', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':title', $category, PDO::PARAM_STR); // Set title to the selected category
        $stmt->bindParam(':content', $content, PDO::PARAM_STR);
        $stmt->bindParam(':category', $category, PDO::PARAM_STR);
        $stmt->bindParam(':photo_path', $photo_path, PDO::PARAM_STR);

        if ($stmt->execute()) {
            // Redirect to home
            header("Location: index.php");
            exit();
        } else {
            // Log the error
            error_log("Error posting question: " . print_r($stmt->errorInfo(), true));
            $error = "Error posting question.";
        }
    } elseif (empty($error)) {
        $error = "All fields are required.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Post a Question</title>
    <link rel="stylesheet" href="CSS/global.css">
    <link rel="stylesheet" href="CSS/header.css">
    <link rel="stylesheet" href="CSS/forms.css">
    <link rel="stylesheet" href="CSS/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
        <a href="index.php" class="logo-link">
            <div class="logo">
                <img src="images/svcc.jpg" alt="VincentThinks Logo" class="nav-logo">
                <h1>VincenThinks</h1>
            </div>
        </a>
        <nav>
            <a href="index.php">Home</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
        </nav>
    </header>

    <main class="post-question-container">
        <h2>Post a Question</h2>
        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="form-group">
                <label for="category">Category</label>
                <select id="category" name="category" required>
                    <option value="">Select Category</option>
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
                    <option value="STEM">STEM - Science, Technology, Engineering, and Mathematics</option>
                    <option value="ABM">ABM - Accountancy, Business, and Management</option>
                    <option value="GAS">GAS - General Academic Strand</option>
                    <option value="HE">HE - Home Economics</option>
                    <option value="ICT">ICT - Information and Communications Technology</option>
                </select>
            </div>

            <div class="form-group">
                <label for="content">Question Content</label>
                <textarea id="content" name="content" rows="5" required></textarea>
            </div>

            <div class="form-group">
                <label for="photo">Upload Photo (Optional)</label>
                <input type="file" id="photo" name="photo" accept="image/jpeg, image/png">
            </div>

            <div class="form-actions">
                <button type="submit">Post</button>
                <a href="index.php" class="back-to-home">
                    <i class="fas fa-arrow-left"></i> Back to Home
                </a>
            </div>
        </form>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> VincenThinks. All rights reserved.</p>
    </footer>
</body>
</html>