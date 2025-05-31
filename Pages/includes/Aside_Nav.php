<!-- Aside Navigation -->
<aside class="sidebar">
    <div class="logo-container">
        <h2>Mathology</h2>
    </div>
    <nav class="side-nav">
        <a href="../admin/dashboardAdmin.php"
            class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboardAdmin.php' ? 'active' : ''; ?>">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="../admin/attendance.php"
            class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'attendance.php' ? 'active' : ''; ?>">
            <i class="fas fa-user-check"></i>
            <span>Attendance</span>
        </a>
        <a href="../admin/timetable_approve.php"
            class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'timetable_approve.php' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-alt"></i>
            <span>Student Timetable</span>
        </a>
        <a href="../admin/users.php"
            class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i>
            <span>Users</span>
        </a>
        <a href="../admin/payment.php"
            class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'payment.php' ? 'active' : ''; ?>">
            <i class="fas fa-credit-card"></i>
            <span>Payments</span>
        </a>
    </nav>
</aside>