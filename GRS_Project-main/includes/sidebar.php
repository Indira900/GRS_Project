<div class="sidebar">
    <div class="sidebar-header">
        <i class="fas fa-comments"></i>
        <h2>Grievance System</h2>
    </div>
    
    <div class="user-info">
        <div class="user-avatar">
            <?php echo substr($_SESSION['user_name'], 0, 1); ?>
        </div>
        <div class="user-details">
            <h3><?php echo $_SESSION['user_name']; ?></h3>
            <p><?php echo ucfirst($_SESSION['user_type']); ?></p>
        </div>
    </div>
    
    <div class="sidebar-menu">
        <a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
        <a href="my-grievances.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'my-grievances.php' ? 'active' : ''; ?>">
            <i class="fas fa-list-alt"></i>
            <span>My Grievances</span>
        </a>
        <a href="new-grievance.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'new-grievance.php' ? 'active' : ''; ?>">
            <i class="fas fa-plus-circle"></i>
            <span>New Grievance</span>
        </a>
        <a href="profile.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
            <i class="fas fa-user"></i>
            <span>My Profile</span>
        </a>
        <a href="../includes/process_logout.php">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</div>
