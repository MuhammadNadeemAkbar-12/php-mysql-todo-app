<?php
// $activePage should be set before including this file
$activePage = $activePage ?? 'dashboard';
?>

<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-brand">
        <i class="fas fa-tasks"></i> TaskManager
    </div>
    <ul class="sidebar-menu">
        <li><a href="admin-dashboard.php" class="<?php echo $activePage === 'dashboard' ? 'active' : ''; ?>"><i class="fas fa-home"></i> Dashboard</a></li>
        <li><a href="all-users.php" class="<?php echo $activePage === 'users' ? 'active' : ''; ?>"><i class="fas fa-users"></i> All Users</a></li>
        <li><a href="all-tasks.php" class="<?php echo $activePage === 'tasks' ? 'active' : ''; ?>"><i class="fas fa-list"></i> All Tasks</a></li>
        <li><a href="pending-tasks.php" class="<?php echo $activePage === 'pending' ? 'active' : ''; ?>"><i class="fas fa-clock"></i> Pending Tasks</a></li>
        <li><a href="approved-tasks.php" class="<?php echo $activePage === 'approved' ? 'active' : ''; ?>"><i class="fas fa-check-circle"></i> Approved Tasks</a></li>
        <li><a href="rejected-tasks.php" class="<?php echo $activePage === 'rejected' ? 'active' : ''; ?>"><i class="fas fa-times-circle"></i> Rejected Tasks</a></li>
    </ul>
</div>
