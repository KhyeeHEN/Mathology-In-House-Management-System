<!-- Aside Navigation -->
<aside class="sidebar">
    <div class="logo-container">
        <h2>Mathology</h2>
    </div>
    <nav class="side-nav">
        <a href="../instructors/dashboardInstructors.php"
            class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboardInstructors.php' ? 'active' : ''; ?>">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
                <a href="../instructors/instruct_days.php"
            class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'instruct_days.php' ? 'active' : ''; ?>">
            <i class="fas fa-home"></i>
            <span>My details</span>
        </a>
    </nav>
</aside>