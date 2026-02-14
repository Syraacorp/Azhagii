<?php
$pageTitle = 'Admin Dashboard';
$currentPage = 'dashboard';
require 'includes/auth.php';
requirePageRole('adminAzhagii');

// ── Fetch Admin Stats (Server-Side) ──
$stats = [
    'users' => 0,
    'courses' => 0,
    'pending_courses' => 0,
    'enrollments' => 0
];

// 1. Stats
$r = mysqli_query($conn, "SELECT COUNT(*) as c FROM users WHERE role IN ('azhagiiCoordinator','azhagiiStudents')");
if ($row = mysqli_fetch_assoc($r))
    $stats['users'] = intval($row['c']);

$r = mysqli_query($conn, "SELECT COUNT(*) as c FROM courses");
if ($row = mysqli_fetch_assoc($r))
    $stats['courses'] = intval($row['c']);

$r = mysqli_query($conn, "SELECT COUNT(*) as c FROM courses WHERE status='pending'");
if ($row = mysqli_fetch_assoc($r))
    $stats['pending_courses'] = intval($row['c']);

$r = mysqli_query($conn, "SELECT COUNT(*) as c FROM enrollments");
if ($row = mysqli_fetch_assoc($r))
    $stats['enrollments'] = intval($row['c']);

// 2. User Breakdown
$userBreakdown = [
    'azhagiiCoordinator' => 0,
    'azhagiiStudents' => 0
];
$r = mysqli_query($conn, "SELECT role, COUNT(*) as c FROM users WHERE role IN ('azhagiiCoordinator','azhagiiStudents') GROUP BY role");
while ($row = mysqli_fetch_assoc($r)) {
    $userBreakdown[$row['role']] = intval($row['c']);
}

// 3. Recent Courses
$recentCourses = [];
$r = mysqli_query($conn, "SELECT c.*, (SELECT COUNT(*) FROM enrollments WHERE courseId=c.id) as enrollment_count 
                          FROM courses c ORDER BY c.createdAt DESC LIMIT 5");
while ($row = mysqli_fetch_assoc($r)) {
    $recentCourses[] = $row;
}

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
<div class="stats-grid" id="admin-stats-container">
    <div class="stat-card">
        <div class="stat-icon" style="background:#4285f415;color:#4285f4;"><i class="fas fa-users"></i></div>
        <div>
            <div class="stat-value"><?= $stats['users'] ?></div>
            <div class="stat-label">Users</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#9b72cb15;color:#9b72cb;"><i class="fas fa-book"></i></div>
        <div>
            <div class="stat-value"><?= $stats['courses'] ?></div>
            <div class="stat-label">Courses</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#f8717115;color:#f87171;"><i class="fas fa-clock"></i></div>
        <div>
            <div class="stat-value"><?= $stats['pending_courses'] ?></div>
            <div class="stat-label">Pending Approvals</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#fbbf2415;color:#fbbf24;"><i class="fas fa-clipboard-list"></i></div>
        <div>
            <div class="stat-value"><?= $stats['enrollments'] ?></div>
            <div class="stat-label">Enrollments</div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="dashboard-section-title"><i class="fas fa-bolt"></i> Quick Actions</div>
<div class="quick-actions-grid">
    <a href="manageUsers.php" class="quick-action-card">
        <div class="quick-action-icon" style="background:rgba(66,133,244,0.1);color:#4285f4;"><i
                class="fas fa-users"></i></div>
        <div>
            <h4>Manage Users</h4>
            <p>Create coordinators & students</p>
        </div>
    </a>
    <a href="manageCourses.php" class="quick-action-card">
        <div class="quick-action-icon" style="background:rgba(155,114,203,0.1);color:#9b72cb;"><i
                class="fas fa-book"></i></div>
        <div>
            <h4>Manage Courses</h4>
            <p>Create and publish courses</p>
        </div>
    </a>
    <a href="courseApprovals.php" class="quick-action-card">
        <div class="quick-action-icon" style="background:rgba(251,191,36,0.1);color:#fbbf24;"><i
                class="fas fa-check-double"></i></div>
        <div>
            <h4>Course Approvals</h4>
            <p>Review and approve submitted courses</p>
        </div>
    </a>
    <a href="courseAssignments.php" class="quick-action-card">
        <div class="quick-action-icon" style="background:rgba(52,211,153,0.1);color:#34d399;"><i
                class="fas fa-link"></i></div>
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
        <h3 style="margin-bottom:1rem;font-size:1rem;"><i class="fas fa-users"
                style="color:var(--accent-blue);margin-right:0.5rem;"></i>User Breakdown</h3>
        <div class="breakdown-list">
            <div class="breakdown-item">
                <span class="breakdown-dot" style="background:#9b72cb;"></span>
                <span>Coordinators</span>
                <strong><?= $userBreakdown['azhagiiCoordinator'] ?></strong>
            </div>
            <div class="breakdown-item">
                <span class="breakdown-dot" style="background:#4285f4;"></span>
                <span>Students</span>
                <strong><?= $userBreakdown['azhagiiStudents'] ?></strong>
            </div>
        </div>
    </div>
    <div class="card">
        <h3 style="margin-bottom:1rem;font-size:1rem;"><i class="fas fa-book"
                style="color:var(--accent-purple);margin-right:0.5rem;"></i>Recent Courses</h3>
        <div class="breakdown-list">
            <?php if (empty($recentCourses)): ?>
                <div class="empty-state" style="padding:1rem;">
                    <p>No courses yet</p>
                </div>
            <?php else: ?>
                <?php foreach ($recentCourses as $c):
                    $statusClass = $c['status'] === 'active' ? 'active' : ($c['status'] === 'draft' ? 'draft' : 'inactive');
                    ?>
                    <div class="breakdown-item">
                        <span class="badge badge-<?= $statusClass ?>"><?= htmlspecialchars($c['status']) ?></span>
                        <span><?= htmlspecialchars($c['title']) ?></span>
                        <small style="color:var(--text-muted);"><?= $c['enrollment_count'] ?> enrolled</small>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require 'includes/footer.php'; ?>