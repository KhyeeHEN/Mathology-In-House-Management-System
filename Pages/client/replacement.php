<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance</title>
    <link rel="stylesheet" href="../../styles/common.css">
    <link rel="stylesheet" href="../../styles/attendence.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo-container">
                <h2>Mathology</h2>
            </div>
            <nav class="side-nav">
                <a href="dashboardclient.php" class="nav-item">
                    <i class="fas fa-home"></i>
                    <span>Home</span>
                </a>
                <a href="attendanceclient.php" class="nav-item">
                    <i class="fas fa-user-check"></i>
                    <span>Attendance</span>
                </a>
                <a href="replacement.php" class="nav-item active">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Schedule Replacement</span>
                </a>
                <a href="learninghours.php" class="nav-item">
                    <i class="fas fa-clock"></i>
                    <span>Learning Hours</span>
                </a>
                <a href="leave.php" class="nav-item">
                    <i class="fas fa-check"></i>
                    <span>Apply Leave</span>
                </a>
                <a href="paymentclient.php" class="nav-item">
                    <i class="fas fa-credit-card"></i>
                    <span>Payments</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Navigation -->
            <nav class="top-nav">
                <div class="nav-left">
                    <button id="menu-toggle" class="menu-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1>Schedule Replacement</h1>
                </div>
                <div class="nav-right">
                    <div class="nav-links">
                        <a href="dashboard.html" class="nav-link">Home</a>
                        <a href="#" class="nav-link">Courses</a>
                        <a href="#" class="nav-link">Resources</a>
                        <a href="#" class="nav-link">Help</a>
                    </div>
                    <div class="user-profile">
                        <img src="https://ui-avatars.com/api/?name=Darshann" alt="Profile" class="profile-img">
                        <div class="profile-dropdown">
                            <span class="user-name">Darrshan</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="dropdown-menu">
                            <a href="profile.html" class="dropdown-item">
                                <i class="fas fa-user"></i>
                                <span>View Profile</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Logout</span>
                            </a>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Do your content here -->

        </main>
    </div>
    <script type="module" src="Scripts/common.js"></script>
</body>
</html>
