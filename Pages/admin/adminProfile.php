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
                            <img src="https://ui-avatars.com/api/?name=Darshann" alt="Profile" class="profile-image">
                            <label for="profile-upload" class="image-upload-label">
                                <i class="fas fa-camera"></i>
                                <input type="file" id="profile-upload" accept="image/*" class="hidden">
                            </label>
                        </div>
                    </div>

                    <form class="profile-form">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" value="Darrshan" class="form-input">
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" value="darrshan@example.com" class="form-input">
                        </div>

                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" value="+1234567890" class="form-input">
                        </div>

                        <div class="form-group">
                            <label for="bio">Bio</label>
                            <textarea id="bio" name="bio" rows="4" class="form-textarea">Mathematics enthusiast and lifelong learner</textarea>
                        </div>

                        <button type="submit" class="save-button">
                            <i class="fas fa-save"></i>
                            Save Changes
                        </button>
                    </form>
                </div>
            </div>
        </main>
    </div>
    <script type="module" src="/Scripts/common.js"></script>
</body>
</html>