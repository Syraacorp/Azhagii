<?php
$pageTitle   = 'Super Admin Dashboard';
$currentPage = 'dashboard';
require 'includes/auth.php';
requirePageRole('superAdmin');
require 'includes/header.php';
require 'includes/sidebar.php';
?>

<!-- Welcome Banner -->
<div class="dashboard-welcome">
    <div class="welcome-text">
        <h2>Welcome back, <?= htmlspecialchars($userName) ?></h2>
        <p>Here's your platform overview — manage colleges, users, courses, and enrollments from one place.</p>
    </div>
    <div class="welcome-icon"><i class="fas fa-shield-alt"></i></div>
</div>

<!-- Stats Grid -->
<div class="stats-grid" id="stats-container"></div>

<!-- Quick Actions -->
<div class="dashboard-section-title"><i class="fas fa-bolt"></i> Quick Actions</div>
<div class="quick-actions-grid">
    <a href="manageColleges.php" class="quick-action-card">
        <div class="quick-action-icon" style="background:rgba(66,133,244,0.1);color:#4285f4;"><i class="fas fa-university"></i></div>
        <div>
            <h4>Manage Colleges</h4>
            <p>Add, edit or deactivate institutions</p>
        </div>
    </a>
    <a href="manageUsers.php" class="quick-action-card">
        <div class="quick-action-icon" style="background:rgba(155,114,203,0.1);color:#9b72cb;"><i class="fas fa-users-cog"></i></div>
        <div>
            <h4>Manage Users</h4>
            <p>Create admins, coordinators & students</p>
        </div>
    </a>
    <a href="manageCourses.php" class="quick-action-card">
        <div class="quick-action-icon" style="background:rgba(52,211,153,0.1);color:#34d399;"><i class="fas fa-book"></i></div>
        <div>
            <h4>Manage Courses</h4>
            <p>Create and publish courses</p>
        </div>
    </a>
    <a href="courseAssignments.php" class="quick-action-card">
        <div class="quick-action-icon" style="background:rgba(251,191,36,0.1);color:#fbbf24;"><i class="fas fa-link"></i></div>
        <div>
            <h4>Course Assignments</h4>
            <p>Assign courses to colleges</p>
        </div>
    </a>
</div>

<!-- Recent Users -->
<div class="dashboard-section-title"><i class="fas fa-clock"></i> Recent Users</div>
<div class="table-responsive">
    <table class="table">
        <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>College</th><th>Joined</th></tr></thead>
        <tbody id="recentUsersBody"></tbody>
    </table>
</div>

<?php require 'includes/footer.php'; ?>
