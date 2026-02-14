<?php
$pageTitle = 'Coordinator Dashboard';
$currentPage = 'dashboard';
require 'includes/auth.php';
requirePageRole('azhagiiCoordinator');

$cid = $_SESSION['collegeId'];
$uid = $_SESSION['userId'];

// ── Fetch Coordinator Stats (Server-Side) ──
$stats = [
    'assigned_courses' => 0,
    'my_students' => 0,
    'my_courses' => 0,
    'my_pending' => 0,
    'my_approved' => 0,
    'my_rejected' => 0
];

// 1. Stats
$r = mysqli_query($conn, "SELECT COUNT(*) as c FROM coursecolleges WHERE collegeId=$cid");
if ($row = mysqli_fetch_assoc($r))
    $stats['assigned_courses'] = intval($row['c']);

$r = mysqli_query($conn, "SELECT COUNT(*) as c FROM users WHERE collegeId=$cid AND role='azhagiiStudents'");
if ($row = mysqli_fetch_assoc($r))
    $stats['my_students'] = intval($row['c']);

$r = mysqli_query($conn, "SELECT COUNT(*) as c FROM courses WHERE createdBy=$uid");
if ($row = mysqli_fetch_assoc($r))
    $stats['my_courses'] = intval($row['c']);

$r = mysqli_query($conn, "SELECT COUNT(*) as c FROM courses WHERE createdBy=$uid AND status='pending'");
if ($row = mysqli_fetch_assoc($r))
    $stats['my_pending'] = intval($row['c']);

$r = mysqli_query($conn, "SELECT COUNT(*) as c FROM courses WHERE createdBy=$uid AND status='active'");
if ($row = mysqli_fetch_assoc($r))
    $stats['my_approved'] = intval($row['c']);

$r = mysqli_query($conn, "SELECT COUNT(*) as c FROM courses WHERE createdBy=$uid AND status='rejected'");
if ($row = mysqli_fetch_assoc($r))
    $stats['my_rejected'] = intval($row['c']);

// 2. Recent Student Activity (Enrollments)
$recentActivity = [];
$q = "SELECT e.*, u.name as student_name, c.title as course_title 
      FROM enrollments e
      JOIN users u ON e.studentId=u.id
      JOIN courses c ON e.courseId=c.id
      WHERE u.collegeId=$cid
      ORDER BY e.enrolledAt DESC LIMIT 8";
$r = mysqli_query($conn, $q);
while ($row = mysqli_fetch_assoc($r)) {
    $recentActivity[] = $row;
}

require 'includes/header.php';
require 'includes/sidebar.php';
?>

<!-- Welcome Banner -->
<div class="dashboard-welcome">
    <div class="welcome-text">
        <h2>Welcome back, <?= htmlspecialchars($userName) ?></h2>
        <p>Manage course content and track student progress for
            <strong><?= htmlspecialchars($collegeName ?: 'your college') ?></strong>.</p>
    </div>
    <div class="welcome-icon"><i class="fas fa-chalkboard-teacher"></i></div>
</div>

<!-- Stats Grid -->
<div class="stats-grid" id="coord-stats-container">
    <div class="stat-card">
        <div class="stat-icon" style="background:#4285f415;color:#4285f4;"><i class="fas fa-book-open"></i></div>
        <div>
            <div class="stat-value"><?= $stats['assigned_courses'] ?></div>
            <div class="stat-label">Assigned Courses</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#9b72cb15;color:#9b72cb;"><i class="fas fa-plus-circle"></i></div>
        <div>
            <div class="stat-value"><?= $stats['my_courses'] ?></div>
            <div class="stat-label">My Courses</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#fbbf2415;color:#fbbf24;"><i class="fas fa-clock"></i></div>
        <div>
            <div class="stat-value"><?= $stats['my_pending'] ?></div>
            <div class="stat-label">Pending</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#34d39915;color:#34d399;"><i class="fas fa-check-circle"></i></div>
        <div>
            <div class="stat-value"><?= $stats['my_approved'] ?></div>
            <div class="stat-label">Approved</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#f8717115;color:#f87171;"><i class="fas fa-times-circle"></i></div>
        <div>
            <div class="stat-value"><?= $stats['my_rejected'] ?></div>
            <div class="stat-label">Rejected</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#a78bfa15;color:#a78bfa;"><i class="fas fa-user-graduate"></i></div>
        <div>
            <div class="stat-value"><?= $stats['my_students'] ?></div>
            <div class="stat-label">My Students</div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="dashboard-section-title"><i class="fas fa-bolt"></i> Quick Actions</div>
<div class="quick-actions-grid">
    <a href="coordinatorCourseCreate.php" class="quick-action-card">
        <div class="quick-action-icon" style="background:rgba(251,191,36,0.1);color:#fbbf24;"><i
                class="fas fa-plus-circle"></i></div>
        <div>
            <h4>Create Course</h4>
            <p>Submit a new course for approval</p>
        </div>
    </a>
    <a href="myCourses.php" class="quick-action-card">
        <div class="quick-action-icon" style="background:rgba(66,133,244,0.1);color:#4285f4;"><i
                class="fas fa-book-open"></i></div>
        <div>
            <h4>My Courses</h4>
            <p>View courses assigned to your college</p>
        </div>
    </a>
    <a href="manageContent.php" class="quick-action-card">
        <div class="quick-action-icon" style="background:rgba(52,211,153,0.1);color:#34d399;"><i
                class="fas fa-file-alt"></i></div>
        <div>
            <h4>Manage Content</h4>
            <p>Upload videos, PDFs and lessons</p>
        </div>
    </a>
    <a href="manageTopics.php" class="quick-action-card">
        <div class="quick-action-icon" style="background:rgba(168,135,250,0.1);color:#a78bfa;"><i
                class="fas fa-tags"></i></div>
        <div>
            <h4>Manage Topics</h4>
            <p>Add important topics per subject</p>
        </div>
    </a>
    <a href="myStudents.php" class="quick-action-card">
        <div class="quick-action-icon" style="background:rgba(155,114,203,0.1);color:#9b72cb;"><i
                class="fas fa-user-graduate"></i></div>
        <div>
            <h4>My Students</h4>
            <p>Track student enrollment & progress</p>
        </div>
    </a>
</div>

<!-- Recent Students -->
<div class="dashboard-section-title"><i class="fas fa-user-graduate"></i> Recent Student Activity</div>
<div class="table-responsive">
    <table class="table" id="coordRecentStudentsTable">
        <thead>
            <tr>
                <th>Student</th>
                <th>Course</th>
                <th>Progress</th>
                <th>Status</th>
                <th>Enrolled</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($recentActivity)): ?>
                <tr>
                    <td colspan="5" class="empty-state"><i class="fas fa-user-graduate"></i>
                        <p>No enrollments yet</p>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($recentActivity as $e):
                    $statBadge = $e['status'] === 'completed' ? 'active' : ($e['status'] === 'active' ? 'draft' : 'inactive');
                    $prog = intval($e['progress']);
                    ?>
                    <tr>
                        <td data-label="Student"><?= htmlspecialchars($e['student_name']) ?></td>
                        <td data-label="Course"><?= htmlspecialchars($e['course_title']) ?></td>
                        <td data-label="Progress">
                            <div class="progress-bar-wrap" style="min-width:80px;">
                                <div class="progress-bar-fill" style="width:<?= $prog ?>%;"></div>
                            </div>
                            <span style="font-size:0.8rem;"><?= $prog ?>%</span>
                        </td>
                        <td data-label="Status"><span
                                class="badge badge-<?= $statBadge ?>"><?= htmlspecialchars($e['status']) ?></span></td>
                        <td data-label="Enrolled"><?= date('M d, Y', strtotime($e['enrolledAt'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
    $(document).ready(function () {
        $('#coordRecentStudentsTable').DataTable({
            paging: false, searching: false, info: false, ordering: false,
            // Simple display table
        });
    });
</script>

<?php require 'includes/footer.php'; ?>