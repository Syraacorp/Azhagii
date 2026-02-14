<?php
$pageTitle   = 'Super Admin Dashboard';
$currentPage = 'dashboard';
require 'includes/auth.php';
requirePageRole('superAdmin');

// ── Fetch Super Admin Stats (Server-Side) ──
$stats = [
    'colleges' => 0,
    'users' => 0,
    'courses' => 0,
    'pending_courses' => 0,
    'enrollments' => 0
];

// 1. Stats
$r = mysqli_query($conn, "SELECT COUNT(*) as c FROM colleges");
if ($row = mysqli_fetch_assoc($r)) $stats['colleges'] = intval($row['c']);

$r = mysqli_query($conn, "SELECT COUNT(*) as c FROM users");
if ($row = mysqli_fetch_assoc($r)) $stats['users'] = intval($row['c']);

$r = mysqli_query($conn, "SELECT COUNT(*) as c FROM courses");
if ($row = mysqli_fetch_assoc($r)) $stats['courses'] = intval($row['c']);

$r = mysqli_query($conn, "SELECT COUNT(*) as c FROM courses WHERE status='pending'");
if ($row = mysqli_fetch_assoc($r)) $stats['pending_courses'] = intval($row['c']);

$r = mysqli_query($conn, "SELECT COUNT(*) as c FROM enrollments");
if ($row = mysqli_fetch_assoc($r)) $stats['enrollments'] = intval($row['c']);

// 2. Recent Users
$recentUsers = [];
$r = mysqli_query($conn, "SELECT u.name, u.email, u.role, u.createdAt, c.name as college_name 
                          FROM users u 
                          LEFT JOIN colleges c ON u.collegeId=c.id 
                          ORDER BY u.createdAt DESC LIMIT 5");
while ($row = mysqli_fetch_assoc($r)) {
    $recentUsers[] = $row;
}

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
<div class="stats-grid" id="super-stats-container">
    <div class="stat-card">
        <div class="stat-icon" style="background:#4285f415;color:#4285f4;"><i class="fas fa-university"></i></div>
        <div><div class="stat-value"><?= $stats['colleges'] ?></div><div class="stat-label">Colleges</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#9b72cb15;color:#9b72cb;"><i class="fas fa-users"></i></div>
        <div><div class="stat-value"><?= $stats['users'] ?></div><div class="stat-label">Total Users</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#34d39915;color:#34d399;"><i class="fas fa-book"></i></div>
        <div><div class="stat-value"><?= $stats['courses'] ?></div><div class="stat-label">Total Courses</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#fbbf2415;color:#fbbf24;"><i class="fas fa-clipboard-check"></i></div>
        <div><div class="stat-value"><?= $stats['enrollments'] ?></div><div class="stat-label">Enrollments</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#f8717115;color:#f87171;"><i class="fas fa-clock"></i></div>
        <div><div class="stat-value"><?= $stats['pending_courses'] ?></div><div class="stat-label">Pending Approval</div></div>
    </div>
</div>

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
    <table class="table" id="recentUsersTable">
        <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>College</th><th>Joined</th></tr></thead>
        <tbody>
            <?php if (empty($recentUsers)): ?>
                <tr><td colspan="5" class="empty-state">No users registered yet.</td></tr>
            <?php else: ?>
                <?php foreach ($recentUsers as $u): ?>
                <tr>
                    <td data-label="Name"><?= htmlspecialchars($u['name']) ?></td>
                    <td data-label="Email"><?= htmlspecialchars($u['email']) ?></td>
                    <td data-label="Role"><span class="badge badge-draft"><?= htmlspecialchars($u['role']) ?></span></td>
                    <td data-label="College"><?= htmlspecialchars($u['college_name'] ?: 'N/A') ?></td>
                    <td data-label="Joined"><?= date('M d, Y', strtotime($u['createdAt'])) ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
    $(document).ready(function() {
        $('#recentUsersTable').DataTable({
            paging: false, searching: false, info: false, ordering: false
        });
    });
</script>

<?php require 'includes/footer.php'; ?>
