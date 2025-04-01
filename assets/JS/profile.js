// Global modal reference
let activeModal = null;

// Close modal function
function closeModal() {
    if (activeModal) {
        activeModal.remove();
        activeModal = null;
    }
}

async function handleFormSubmit(url, data, successCallback) {
    const loadingIndicator = showLoading();
    
    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });

        // First check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            throw new Error(`Server returned unexpected format: ${text.substring(0, 100)}`);
        }

        const result = await response.json();
        
        if (!response.ok) {
            throw new Error(result.message || `HTTP error! status: ${response.status}`);
        }
        
        if (result.success) {
            if (successCallback) successCallback(result);
            return true;
        } else {
            throw new Error(result.message || 'Action failed');
        }
    } catch (error) {
        console.error('Error:', error);
        showModalMessage(
            error.message.includes('unexpected format') 
                ? 'Server error occurred' 
                : error.message,
            false
        );
        return false;
    } finally {
        loadingIndicator.remove();
    }
}

function showLoading() {
    const loader = document.createElement('div');
    loader.className = 'loading-indicator';
    loader.innerHTML = 'Processing...';
    activeModal.querySelector('.modal-content').appendChild(loader);
    return loader;
}

// Show message in modal
function showModalMessage(message, isSuccess) {
    if (!activeModal) return;
    
    // Remove existing messages
    const existingMsg = activeModal.querySelector('.modal-message');
    if (existingMsg) existingMsg.remove();
    
    // Create new message element
    const msgElement = document.createElement('div');
    msgElement.className = `modal-message ${isSuccess ? 'success' : 'error'}`;
    msgElement.textContent = message;
    
    // Insert after form
    const form = activeModal.querySelector('form');
    form.insertAdjacentElement('afterend', msgElement);
}

// Email Change Handler
document.addEventListener('click', function(e) {
    if (e.target.closest('.btn-change-email')) {
        closeModal();
        
        activeModal = document.createElement('div');
        activeModal.className = 'modal';
        activeModal.innerHTML = `
            <div class="modal-content">
                <span class="close-modal">&times;</span>
                <h2><i class="fas fa-envelope"></i> Change Email</h2>
                <form id="changeEmailForm">
                    <div class="form-group">
                        <label for="currentPasswordEmail">Current Password:</label>
                        <input type="password" id="currentPasswordEmail" required>
                    </div>
                    <div class="form-group">
                        <label for="newEmail">New Email:</label>
                        <input type="email" id="newEmail" required>
                    </div>
                    <button type="submit" class="btn-submit">Update Email</button>
                </form>
            </div>
        `;
        
        document.body.appendChild(activeModal);
        
        // Event listeners
        activeModal.querySelector('.close-modal').addEventListener('click', closeModal);
        activeModal.addEventListener('click', (e) => e.target === activeModal && closeModal());
        
        document.getElementById('changeEmailForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const result = await handleFormSubmit(
                'change_email.php',
                {
                    current_password: document.getElementById('currentPasswordEmail').value,
                    new_email: document.getElementById('newEmail').value
                },
                (data) => {
                    // Success callback
                    showModalMessage('Email updated successfully!', true);
                    // Update displayed email if on profile page
                    const emailDisplay = document.querySelector('.profile-email');
                    if (emailDisplay) emailDisplay.textContent = data.new_email;
                    // Clear form
                    this.reset();
                }
            );
        });
    }
});

// Password Change Handler (similar structure to email)
document.addEventListener('click', function(e) {
    if (e.target.closest('.btn-change-password')) {
        closeModal();
        
        activeModal = document.createElement('div');
        activeModal.className = 'modal';
        activeModal.innerHTML = `
            <div class="modal-content">
                <span class="close-modal">&times;</span>
                <h2><i class="fas fa-lock"></i> Change Password</h2>
                <form id="changePasswordForm">
                    <div class="form-group">
                        <label for="currentPassword">Current Password:</label>
                        <input type="password" id="currentPassword" required>
                    </div>
                    <div class="form-group">
                        <label for="newPassword">New Password:</label>
                        <input type="password" id="newPassword" required minlength="8">
                    </div>
                    <div class="form-group">
                        <label for="confirmPassword">Confirm New Password:</label>
                        <input type="password" id="confirmPassword" required minlength="8">
                    </div>
                    <button type="submit" class="btn-submit">Update Password</button>
                </form>
            </div>
        `;
        
        document.body.appendChild(activeModal);
        
        // Event listeners
        activeModal.querySelector('.close-modal').addEventListener('click', closeModal);
        activeModal.addEventListener('click', (e) => e.target === activeModal && closeModal());
        
        document.getElementById('changePasswordForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (newPassword !== confirmPassword) {
                showModalMessage('Passwords do not match!', false);
                return;
            }
            
            const result = await handleFormSubmit(
                'change_password.php',
                {
                    current_password: document.getElementById('currentPassword').value,
                    new_password: newPassword,
                    confirm_password: confirmPassword
                },
                () => {
                    // Success callback
                    showModalMessage('Password updated successfully!', true);
                    this.reset();
                }
            );
        });
    }
});

// Cancel Question Handler
document.addEventListener('click', function(e) {
    if (e.target.closest('.btn-cancel')) {
        e.preventDefault();
        const button = e.target.closest('.btn-cancel');
        const questionId = button.getAttribute('data-id');
        
        // Confirm before canceling
        if (!confirm('Are you sure you want to cancel this question?')) {
            return;
        }
        
        // Show loading state on the button
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Canceling...';
        button.disabled = true;
        
        fetch('/webapp/user/cancel_question.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ question_id: questionId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove the question from the DOM
                button.closest('.question-item').remove();
                
                // Update the pending questions count if exists
                const pendingCount = document.querySelector('.content-section h2 i.fa-clock')?.parentNode;
                if (pendingCount) {
                    const currentText = pendingCount.textContent;
                    const newCount = parseInt(currentText.match(/\((\d+)\)/)[1]) - 1;
                    pendingCount.textContent = currentText.replace(/\(\d+\)/, `(${newCount})`);
                }
            } else {
                throw new Error(data.message || 'Failed to cancel question');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert(error.message);
        })
        .finally(() => {
            button.innerHTML = originalText;
            button.disabled = false;
        });
    }
});

document.getElementById('profilePictureUpload').addEventListener('change', function(e) {
    if (!this.files || !this.files[0]) return;
    
    const formData = new FormData();
    formData.append('profile_picture', this.files[0]);
    
    const messageEl = document.getElementById('profilePictureMessage');
    const btn = document.querySelector('.btn-upload');
    const originalBtnText = btn.innerHTML;
    
    // Show loading state
    messageEl.textContent = 'Uploading...';
    messageEl.style.color = '#800000';
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading';
    btn.disabled = true;
    
    fetch('upload_profile_picture.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        // First check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            return response.text().then(text => {
                throw new Error(text || 'Invalid server response');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            
            const profilePic = document.querySelector('.profile-picture-container .profile-avatar-img, .profile-picture-container .fa-user-circle');
            if (profilePic) {
                if (profilePic.tagName === 'IMG') {
                    profilePic.src = data.imageUrl;
                } else {
                    // Replace icon with image
                    const newImg = document.createElement('img');
                    newImg.src = data.imageUrl;
                    newImg.className = 'profile-avatar-img';
                    newImg.alt = 'Profile Picture';
                    profilePic.replaceWith(newImg);
                }
            }
            
            // Update avatar in sidebar if exists
            const sidebarAvatar = document.querySelector('.profile-avatar img, .profile-avatar i');
            if (sidebarAvatar) {
                if (sidebarAvatar.tagName === 'IMG') {
                    sidebarAvatar.src = data.imageUrl;
                } else {
                    const newImg = document.createElement('img');
                    newImg.src = data.imageUrl;
                    newImg.className = 'profile-avatar-img';
                    newImg.alt = 'Profile Picture';
                    sidebarAvatar.replaceWith(newImg);
                }
            }
            
            messageEl.textContent = data.message;
            messageEl.style.color = 'green';
        } else {
            throw new Error(data.message);
        }
    })
    .catch(error => {
        console.error('Upload error:', error);
        messageEl.textContent = error.message.includes('<') ? 
            'Server error occurred' : error.message;
        messageEl.style.color = 'red';
    })
    .finally(() => {
        btn.innerHTML = originalBtnText;
        btn.disabled = false;
        this.value = ''; // Reset input
    });
});