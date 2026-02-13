<?php
$pageTitle   = 'Student Dashboard';
$currentPage = 'dashboard';
require 'includes/auth.php';
requirePageRole('ziyaaStudents');
require 'includes/header.php';
require 'includes/sidebar.php';
?>

<!-- Welcome Banner -->
<div class="dashboard-welcome">
    <div class="welcome-text">
        <h2>Welcome back, <?= htmlspecialchars($userName) ?></h2>
        <p>Continue your learning journey at <strong><?= htmlspecialchars($collegeName ?: 'your college') ?></strong>. Pick up where you left off.</p>
    </div>
    <div class="welcome-icon"><i class="fas fa-graduation-cap"></i></div>
</div>

<!-- Progress Overview -->
<div class="progress-overview-grid" id="progressOverview">
    <div class="progress-card" id="profileProgressCard">
        <div class="progress-card-header">
            <div class="progress-card-icon" style="background:rgba(155,114,203,0.1);color:#9b72cb;"><i class="fas fa-user-circle"></i></div>
            <div>
                <h4>Profile Completion</h4>
                <p class="progress-card-subtitle">Complete your profile for a better experience</p>
            </div>
        </div>
        <div class="progress-card-bar">
            <div class="progress-card-bar-track">
                <div class="progress-card-bar-fill" id="profileBarFill" style="width:0%;"></div>
            </div>
            <span class="progress-card-pct" id="profilePct">0%</span>
        </div>
        <p class="progress-card-detail" id="profileDetail">Loading...</p>
    </div>
    <div class="progress-card" id="courseProgressCard">
        <div class="progress-card-header">
            <div class="progress-card-icon" style="background:rgba(66,133,244,0.1);color:#4285f4;"><i class="fas fa-book-reader"></i></div>
            <div>
                <h4>Course Progress</h4>
                <p class="progress-card-subtitle">Your overall learning progress</p>
            </div>
        </div>
        <div class="progress-card-bar">
            <div class="progress-card-bar-track">
                <div class="progress-card-bar-fill" id="courseBarFill" style="width:0%;"></div>
            </div>
            <span class="progress-card-pct" id="coursePct">0%</span>
        </div>
        <div id="courseProgressList" class="course-progress-list"></div>
    </div>
</div>

<!-- Stats Grid -->
<div class="stats-grid" id="stats-container"></div>

<!-- Quick Actions -->
<div class="dashboard-section-title"><i class="fas fa-bolt"></i> Quick Actions</div>
<div class="quick-actions-grid">
    <a href="browseCourses.php" class="quick-action-card">
        <div class="quick-action-icon" style="background:rgba(66,133,244,0.1);color:#4285f4;"><i class="fas fa-compass"></i></div>
        <div>
            <h4>Browse Courses</h4>
            <p>Discover and enroll in new courses</p>
        </div>
    </a>
    <a href="myLearning.php" class="quick-action-card">
        <div class="quick-action-icon" style="background:rgba(52,211,153,0.1);color:#34d399;"><i class="fas fa-graduation-cap"></i></div>
        <div>
            <h4>My Learning</h4>
            <p>Continue your enrolled courses</p>
        </div>
    </a>
    <a href="profile.php" class="quick-action-card">
        <div class="quick-action-icon" style="background:rgba(155,114,203,0.1);color:#9b72cb;"><i class="fas fa-user-circle"></i></div>
        <div>
            <h4>My Profile</h4>
            <p>Update your account details</p>
        </div>
    </a>
</div>

<!-- Continue Learning -->
<div class="dashboard-section-title"><i class="fas fa-play-circle"></i> Continue Learning</div>
<div class="cards-grid" id="continueLearningGrid"></div>

<?php require 'includes/footer.php'; ?>
