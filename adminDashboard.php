<?php
$pageTitle   = 'Admin Dashboard';
$currentPage = 'dashboard';
require 'includes/auth.php';
requirePageRole('adminZiyaa');
require 'includes/header.php';
require 'includes/sidebar.php';
?>

<!-- Welcome Banner -->
<div class="dashboard-welcome">
    <div class="welcome-text">
        <h2>Welcome back, <?= htmlspecialchars($userName) ?></h2>
        <p>Manage users, create courses and assign them to colleges across the platform.</p>
    </div>
    <div class="welcome-icon"><i class="fas fa-user-tie"></i></div>
</div>

<!-- Stats Grid -->
<div class="stats-grid" id="stats-container"></div>

<!-- Quick Actions -->
<div class="dashboard-section-title"><i class="fas fa-bolt"></i> Quick Actions</div>
<div class="quick-actions-grid">
    <a href="manageUsers.php" class="quick-action-card">
        <div class="quick-action-icon" style="background:rgba(66,133,244,0.1);color:#4285f4;"><i class="fas fa-users"></i></div>
        <div>
            <h4>Manage Users</h4>
            <p>Create coordinators & students</p>
        </div>
    </a>
    <a href="manageCourses.php" class="quick-action-card">
        <div class="quick-action-icon" style="background:rgba(155,114,203,0.1);color:#9b72cb;"><i class="fas fa-book"></i></div>
        <div>
            <h4>Manage Courses</h4>
            <p>Create and publish courses</p>
        </div>
    </a>
    <a href="courseApprovals.php" class="quick-action-card">
        <div class="quick-action-icon" style="background:rgba(251,191,36,0.1);color:#fbbf24;"><i class="fas fa-check-double"></i></div>
        <div>
            <h4>Course Approvals</h4>
            <p>Review and approve submitted courses</p>
        </div>
    </a>
    <a href="courseAssignments.php" class="quick-action-card">
        <div class="quick-action-icon" style="background:rgba(52,211,153,0.1);color:#34d399;"><i class="fas fa-link"></i></div>
        <div>
            <h4>Course Assignments</h4>
            <p>Assign courses to colleges</p>
        </div>
    </a>
</div>

<!-- Recent Activity -->
<div class="dashboard-section-title"><i class="fas fa-chart-bar"></i> Platform Overview</div>
<div class="admin-overview-grid">
    <div class="card">
        <h3 style="margin-bottom:1rem;font-size:1rem;"><i class="fas fa-users" style="color:var(--accent-blue);margin-right:0.5rem;"></i>User Breakdown</h3>
        <div id="adminUserBreakdown">
            <div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Loading...</p></div>
        </div>
    </div>
    <div class="card">
        <h3 style="margin-bottom:1rem;font-size:1rem;"><i class="fas fa-book" style="color:var(--accent-purple);margin-right:0.5rem;"></i>Recent Courses</h3>
        <div id="adminRecentCourses">
            <div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Loading...</p></div>
        </div>
    </div>
</div>

<?php require 'includes/footer.php'; ?>
