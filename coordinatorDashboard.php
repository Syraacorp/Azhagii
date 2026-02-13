<?php
$pageTitle   = 'Coordinator Dashboard';
$currentPage = 'dashboard';
require 'includes/auth.php';
requirePageRole('ziyaaCoordinator');
require 'includes/header.php';
require 'includes/sidebar.php';
?>

<!-- Welcome Banner -->
<div class="dashboard-welcome">
    <div class="welcome-text">
        <h2>Welcome back, <?= htmlspecialchars($userName) ?></h2>
        <p>Manage course content and track student progress for <strong><?= htmlspecialchars($collegeName ?: 'your college') ?></strong>.</p>
    </div>
    <div class="welcome-icon"><i class="fas fa-chalkboard-teacher"></i></div>
</div>

<!-- Stats Grid -->
<div class="stats-grid" id="stats-container"></div>

<!-- Quick Actions -->
<div class="dashboard-section-title"><i class="fas fa-bolt"></i> Quick Actions</div>
<div class="quick-actions-grid">
    <a href="myCourses.php" class="quick-action-card">
        <div class="quick-action-icon" style="background:rgba(66,133,244,0.1);color:#4285f4;"><i class="fas fa-book-open"></i></div>
        <div>
            <h4>My Courses</h4>
            <p>View courses assigned to your college</p>
        </div>
    </a>
    <a href="manageContent.php" class="quick-action-card">
        <div class="quick-action-icon" style="background:rgba(52,211,153,0.1);color:#34d399;"><i class="fas fa-file-alt"></i></div>
        <div>
            <h4>Manage Content</h4>
            <p>Upload videos, PDFs and lessons</p>
        </div>
    </a>
    <a href="myStudents.php" class="quick-action-card">
        <div class="quick-action-icon" style="background:rgba(155,114,203,0.1);color:#9b72cb;"><i class="fas fa-user-graduate"></i></div>
        <div>
            <h4>My Students</h4>
            <p>Track student enrollment & progress</p>
        </div>
    </a>
</div>

<!-- Recent Students -->
<div class="dashboard-section-title"><i class="fas fa-user-graduate"></i> Recent Student Activity</div>
<div class="table-responsive">
    <table class="table">
        <thead><tr><th>Student</th><th>Course</th><th>Progress</th><th>Status</th><th>Enrolled</th></tr></thead>
        <tbody id="coordRecentStudents"></tbody>
    </table>
</div>

<?php require 'includes/footer.php'; ?>
