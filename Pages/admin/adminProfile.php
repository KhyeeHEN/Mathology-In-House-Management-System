<?php
session_start();
require_once '../setting.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Get admin data
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT email, profile_pic FROM users WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

// Set default values
$email = $admin['email'] ?? '';
$profilePic = $admin['profile_pic'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($email);
$nameFromEmail = explode('@', $email)[0]; // Extract name part from email for default avatar
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings</title>
    <link rel="stylesheet" href="/Styles/common.css">
    <link rel="stylesheet" href="/Styles/adminProfile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <?php require("../includes/Aside_Nav.php"); ?>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Navigation -->
            <?php require("../includes/Top_Nav_Bar_Admin.php"); ?>

            <!-- Profile Content -->
            <div class="profile-container">
                <div class="profile-header">
                    <h1>Profile Settings</h1>
                    <p>Manage your personal information and account settings</p>
                </div>

                <div class="profile-content">
                    <div class="profile-image-section">
                        <div class="image-container">
                            <img src="<?php echo htmlspecialchars($profilePic); ?>" alt="Profile" class="profile-image" id="profile-image-preview">
                            <div class="image-actions">
                                <label for="profile-upload" class="image-upload-label">
                                    <i class="fas fa-camera"></i> Change
                                    <input type="file" id="profile-upload" accept="image/*" class="hidden">
                                </label>
                                <?php if ($admin['profile_pic']): ?>
                                <button class="image-delete-btn" id="delete-profile-pic">
                                    <i class="fas fa-trash"></i> Remove
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <form class="profile-form" id="profile-form">
                        <div class="form-group">
                            <label>Role</label>
                            <input type="text" value="Administrator" class="form-input" disabled>
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" class="form-input" required>
                        </div>

                        <div class="form-group">
                            <label for="current_password">Current Password (leave blank to keep unchanged)</label>
                            <input type="password" id="current_password" name="current_password" class="form-input">
                            <small>Enter current password to make changes</small>
                        </div>

                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" class="form-input">
                            <small>Leave blank if you don't want to change</small>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-input">
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="save-button">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                            <div id="form-message" class="form-message"></div>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const profileForm = document.getElementById('profile-form');
        const profileUpload = document.getElementById('profile-upload');
        const deleteProfilePic = document.getElementById('delete-profile-pic');
        const profileImagePreview = document.getElementById('profile-image-preview');
        const formMessage = document.getElementById('form-message');
        const defaultAvatar = 'https://ui-avatars.com/api/?name=<?php echo urlencode($nameFromEmail); ?>';

        // Handle profile picture upload
        profileUpload.addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                const file = e.target.files[0];
                const formData = new FormData();
                formData.append('profile_pic', file);
                formData.append('action', 'upload');

                fetch('../upload.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        profileImagePreview.src = data.filePath;
                        formMessage.textContent = 'Profile picture updated successfully!';
                        formMessage.className = 'form-message success';
                        
                        // Show delete button if it was hidden
                        if (!deleteProfilePic) {
                            location.reload(); // Reload to show delete button
                        }
                    } else {
                        formMessage.textContent = data.message || 'Error uploading image';
                        formMessage.className = 'form-message error';
                    }
                })
                .catch(error => {
                    formMessage.textContent = 'Error uploading image';
                    formMessage.className = 'form-message error';
                });
            }
        });

        // Handle profile picture deletion
        if (deleteProfilePic) {
            deleteProfilePic.addEventListener('click', function(e) {
                e.preventDefault();
                
                if (confirm('Are you sure you want to remove your profile picture?')) {
                    fetch('../upload.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            action: 'delete'
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            profileImagePreview.src = defaultAvatar;
                            deleteProfilePic.remove();
                            formMessage.textContent = 'Profile picture removed successfully!';
                            formMessage.className = 'form-message success';
                        } else {
                            formMessage.textContent = data.message || 'Error removing image';
                            formMessage.className = 'form-message error';
                        }
                    })
                    .catch(error => {
                        formMessage.textContent = 'Error removing image';
                        formMessage.className = 'form-message error';
                    });
                }
            });
        }

        // Handle form submission for email/password changes
        profileForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(profileForm);
            formData.append('action', 'update_profile');
            
            // Validate password fields if any is filled
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (newPassword !== confirmPassword) {
                formMessage.textContent = 'New passwords do not match!';
                formMessage.className = 'form-message error';
                return;
            }
            
            if (newPassword && !document.getElementById('current_password').value) {
                formMessage.textContent = 'Please enter your current password to change it';
                formMessage.className = 'form-message error';
                return;
            }

            fetch('../upload.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    formMessage.textContent = 'Profile updated successfully!';
                    formMessage.className = 'form-message success';
                    
                    // If email was changed, update the displayed email
                    if (data.newEmail) {
                        document.getElementById('email').value = data.newEmail;
                    }
                } else {
                    formMessage.textContent = data.message || 'Error updating profile';
                    formMessage.className = 'form-message error';
                }
            })
            .catch(error => {
                formMessage.textContent = 'Error updating profile';
                formMessage.className = 'form-message error';
            });
        });
    });
    </script>
    <script type="module" src="/Scripts/common.js"></script>
</body>
</html>