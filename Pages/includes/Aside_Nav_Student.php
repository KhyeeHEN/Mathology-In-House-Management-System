<!-- Aside Navigation -->
<aside class="sidebar">
    <div class="logo-container">
        <h2>Mathology</h2>
    </div>
    <nav class="side-nav">
        <a href="../client/dashboardclient.php"
            class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboardclient.php' ? 'active' : ''; ?>">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="../client/learninghours.php"
            class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'learninghours.php' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-alt"></i>
            <span>Learning hours</span>
        </a>
        <a href="../client/student_timetable.php"
            class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'student_timetable.php' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-alt"></i>
            <span>Timetable</span>
        </a>
        <a href="../client/student_reschedule.php"
            class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'student_reschedule.php' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-alt"></i>
            <span>Reschedule</span>
        </a>

        <a href="../client/paymentclient.php"
            class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'paymentclient.php' ? 'active' : ''; ?>">
            <i class="fas fa-credit-card"></i>
            <span>Payment</span>
        </a>

        <a href="../client/leave.php"
            class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'leave.php' ? 'active' : ''; ?>">
            <i class="fas fa-check"></i>
            <span>Apply Leave</span>
        </a>
    </nav>
</aside>
