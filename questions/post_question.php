<?php
require_once '../includes/auth.php';  
require_once '../includes/db_config.php'; 

$error = "";

function resizeImage($file, $max_width, $max_height) {
    if (!file_exists($file)) {
        throw new Exception("File does not exist: $file");
    }

    list($width, $height, $type) = getimagesize($file);

    $ratio = $width / $height;
    if ($max_width / $max_height > $ratio) {
        $max_width = $max_height * $ratio;
    } else {
        $max_height = $max_width / $ratio;
    }

    $src = imagecreatefromstring(file_get_contents($file));
    if ($src === false) {
        throw new Exception("Failed to create image from string.");
    }

    $dst = imagecreatetruecolor($max_width, $max_height);
    if ($dst === false) {
        imagedestroy($src);
        throw new Exception("Failed to create true color image.");
    }

    if (!imagecopyresampled($dst, $src, 0, 0, 0, 0, $max_width, $max_height, $width, $height)) {
        imagedestroy($src);
        imagedestroy($dst);
        throw new Exception("Failed to resize the image.");
    }

    $resized_file = tempnam(sys_get_temp_dir(), 'resized');
    switch ($type) {
        case IMAGETYPE_JPEG:
            if (!imagejpeg($dst, $resized_file, 90)) { 
                imagedestroy($src);
                imagedestroy($dst);
                throw new Exception("Failed to save JPEG image.");
            }
            break;
        case IMAGETYPE_PNG:
            if (!imagepng($dst, $resized_file, 9)) { 
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

    imagedestroy($src);
    imagedestroy($dst);

    return $resized_file;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content  = trim($_POST['content']);
    $category = trim($_POST['category']); 
    $user_id  = $_SESSION['user_id'];

    if (!empty($content) && !empty($category)) {
        $sql = "INSERT INTO questions (user_id, title, content, category, status) VALUES (:uid, :title, :content, :category, 'pending')";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':uid', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':title', $category, PDO::PARAM_STR); 
        $stmt->bindParam(':content', $content, PDO::PARAM_STR);
        $stmt->bindParam(':category', $category, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $question_id = $conn->lastInsertId(); 

            if (!empty($_FILES['photos']['name'][0])) {
                $upload_dir = '../uploads/'; 
                if (!is_dir($upload_dir)) {
                    if (!mkdir($upload_dir, 0755, true)) {
                        $error = "Failed to create upload directory.";
                    }
                }

                foreach ($_FILES['photos']['tmp_name'] as $key => $tmp_name) {
                    $file_name = basename($_FILES['photos']['name'][$key]);
                    $file_tmp = $_FILES['photos']['tmp_name'][$key];
                    $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

                    $allowed_types = ['jpg', 'jpeg', 'png'];
                    if (!in_array($file_type, $allowed_types)) {
                        $error = "Only JPG, JPEG, and PNG files are allowed.";
                        break;
                    }

                    try {
                        $resized_file = resizeImage($file_tmp, 400, 400);

                        $unique_name = uniqid() . '.' . $file_type;
                        $photo_path = $upload_dir . $unique_name;

                        if (!rename($resized_file, $photo_path)) {
                            $error = "Failed to upload the file.";
                            break;
                        }

                        $photo_sql = "INSERT INTO question_photos (question_id, photo_path) VALUES (:qid, :photo_path)";
                        $photo_stmt = $conn->prepare($photo_sql);
                        $photo_stmt->bindParam(':qid', $question_id, PDO::PARAM_INT);
                        $photo_stmt->bindParam(':photo_path', $photo_path, PDO::PARAM_STR);
                        $photo_stmt->execute();
                    } catch (Exception $e) {
                        $error = "Error processing image: " . $e->getMessage();
                        break;
                    }
                }
            }

            if (empty($error)) {
                header("Location: ../index");
                exit();
            }
        } else {
            error_log("Error posting question: " . print_r($stmt->errorInfo(), true));
            $error = "Error posting question.";
        }
    } else {
        $error = "All fields are required.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Post a Question</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <link rel="stylesheet" href="../assets/CSS/global.css">
    <link rel="stylesheet" href="../assets/CSS/header.css">
    <link rel="stylesheet" href="../assets/CSS/forms.css">
    <link rel="stylesheet" href="../assets/CSS/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta name="theme-color" content="#4CAF50">
    <link rel="apple-touch-icon" sizes="57x57" href="../assets/images/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="../assets/images/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="../assets/images/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="../assets/images/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="../assets/images/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="../assets/images/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="../assets/images/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="../assets/images/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="../assets/images/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192"  href="../assets/images/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="../assets/images/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/images/favicon-16x16.png">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">
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
    </header>

    <main>
        <div class="post-question-container"> 
            <!-- Wrap the form in a container -->
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
                        <option value="JHS">JHS - Junior High School</option>
                        <option value="FACULTY">FACULTY - Faculty</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="content">Question Content</label>
                    <textarea id="content" name="content" rows="5" required></textarea>
                </div>

                <div class="form-group">
                    <label for="photos">Upload Photos (Optional)</label>
                    <input type="file" id="photos" name="photos[]" accept="image/jpeg, image/png" multiple>
                </div>

                <div class="form-actions">
                    <button type="submit">Post</button>
                    <a href="../index" class="back-to-home">
                        <i class="fas fa-arrow-left"></i> Back to Home
                    </a>
                </div>
            </form>
        </div>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> VincenThinks. All rights reserved.</p>
    </footer>
</body>
</html>