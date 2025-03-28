<?php
// Get additional user info
$user_sql = "SELECT created_at FROM users WHERE user_id = :user_id";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
$user_stmt->execute();
$user = $user_stmt->fetch(PDO::FETCH_ASSOC);

// Set default last_login if column doesn't exist
$last_login = isset($user['last_login']) ? $user['last_login'] : 'Never';
?>
<section class="profile-info-section">
    <h2><i class="fas fa-user-shield"></i> Account Information</h2>
    <div class="info-grid">
        <div class="info-item">
            <label>Username:</label>
            <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
        </div>
        <div class="info-item">
            <label>Email:</label>
            <span><?php echo htmlspecialchars($_SESSION['email']); ?></span>
        </div>
        <div class="info-item">
            <label>Member Since:</label>
            <span><?php echo date('F j, Y', strtotime($user['created_at'])); ?></span>
        </div>
    </div>
    <h2><i class="fas fa-cog"></i> Account Settings</h2>
    <div class="settings-actions">
        <button class="btn-change-email"><i class="fas fa-envelope"></i> Change Email</button>
        <button class="btn-change-password"><i class="fas fa-lock"></i> Change Password</button>
    </div>
        <h2><i class="fas fa-cog"></i> Account Settings</h2>
        <div class="info-item">
        <div class="profile-picture-container">
        <?php 
        $user = get_user_data($conn, $_SESSION['user_id']);
        if (!empty($user['profile_picture'])): ?>
            <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" 
                alt="Profile Picture" 
                class="profile-avatar-img">
        <?php else: ?>
            <img src="images/userAvatar.jpg" class="avatar">
        <?php endif; ?>
            <form id="profilePictureForm" enctype="multipart/form-data">
                <input type="file" id="profilePictureUpload" name="profile_picture" accept="image/*" style="display: none;">
                <button type="button" class="btn-upload" onclick="document.getElementById('profilePictureUpload').click()">
                    <i class="fas fa-camera"></i> Change Picture
                </button>
                <div id="profilePictureMessage" class="message"></div>
            </form>
        </div>
    </div>
</section>