<!-- Aside Navigation -->
<aside class="sidebar">
    <div class="logo-container">
        <h2>Mathology</h2>
    </div>
    <nav class="side-nav">
        <a href="../client/dashboardclient.php"
            class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboardAdmin.php' ? 'active' : ''; ?>">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="../client/attendanceclient.php"
            class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'attendance.php' ? 'active' : ''; ?>">
            <i class="fas fa-user-check"></i>
            <span>Attendance</span>
        </a>
        <a href="../client/learninghours.php"
            class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'timetable.php' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-alt"></i>
            <span>Timetable</span>
        </a>
        <a href="../client/student_timetable.php"
            class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i>
            <span>Timetable</span>
        </a>
        <a href="../client/student_reschedule.php"
            class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'payment.php' ? 'active' : ''; ?>">
            <i class="fas fa-credit-card"></i>
            <span>Reschedule</span>
        </a>
        <a href="../client/leave.php"
            class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'payment.php' ? 'active' : ''; ?>">
            <i class="fas fa-check"></i>
            <span>Apply Leave</span>
        </a>
    </nav>
</aside>