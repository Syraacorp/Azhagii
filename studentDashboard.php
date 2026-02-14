<?php
$pageTitle   = 'Student Dashboard';
$currentPage = 'dashboard';
require 'includes/auth.php';
requirePageRole('azhagiiStudents');

// ── Fetch Student Data (Server-Side) ──
$sid = intval($_SESSION['userId']);
$cid = intval($_SESSION['collegeId']);

// 1. Stats
$stats = [
    'enrolled' => 0,
    'completed' => 0,
    'available' => 0,
    'avg_progress' => 0
];

$r = mysqli_query($conn, "SELECT COUNT(*) as c FROM enrollments WHERE studentId=$sid");
if ($row = mysqli_fetch_assoc($r)) $stats['enrolled'] = intval($row['c']);

$r = mysqli_query($conn, "SELECT COUNT(*) as c FROM enrollments WHERE studentId=$sid AND status='completed'");
if ($row = mysqli_fetch_assoc($r)) $stats['completed'] = intval($row['c']);

$r = mysqli_query($conn, "SELECT COUNT(*) as c FROM coursecolleges WHERE collegeId=$cid");
if ($row = mysqli_fetch_assoc($r)) $stats['available'] = intval($row['c']);

$r = mysqli_query($conn, "SELECT AVG(progress) as p FROM enrollments WHERE studentId=$sid");
if ($row = mysqli_fetch_assoc($r)) $stats['avg_progress'] = round(floatval($row['p'] ?? 0));

// 2. Profile Completion (Re-calculation for display)
// Note: auth.php enforces 100% to view this page, so this will likely be 100%.
$r = mysqli_query($conn, "SELECT * FROM users WHERE id=$sid");
$u = ($r) ? mysqli_fetch_assoc($r) : [];
$profileFields = ['name', 'email', 'username', 'role', 'phone', 'address', 'bio', 'dob', 'gender', 'collegeId', 'department', 'year', 'rollNumber', 'profilePhoto'];
$filled = 0;
foreach ($profileFields as $f) {
    if (!empty($u[$f])) $filled++;
}
$profilePct = round(($filled / count($profileFields)) * 100);

// 3. Course Progress List (Enrolled Courses)
$courseProgress = [];
$r = mysqli_query($conn, "SELECT c.title, e.progress, e.status as enroll_status 
                          FROM enrollments e 
                          JOIN courses c ON e.courseId=c.id 
                          WHERE e.studentId=$sid 
                          ORDER BY e.enrolledAt DESC");
while ($row = mysqli_fetch_assoc($r)) {
    $courseProgress[] = $row;
}

// 4. Continue Learning (In-Progress Courses)
$continueLearning = [];
$r = mysqli_query($conn, "SELECT c.*, e.id as enrollment_id, e.progress, e.status as enroll_status, e.enrolledAt,
                          (SELECT COUNT(*) FROM coursecontent WHERE courseId=c.id AND collegeId=$cid AND status='active') as content_count
                          FROM enrollments e 
                          JOIN courses c ON e.courseId=c.id 
                          WHERE e.studentId=$sid AND e.status != 'completed'
                          ORDER BY e.enrolledAt DESC LIMIT 4");
while ($row = mysqli_fetch_assoc($r)) {
    $continueLearning[] = $row;
}

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
<div class="progress-overview-grid" id="student-progress-overview">
    <!-- Profile Card -->
    <div class="progress-card">
        <div class="progress-card-header">
            <div class="progress-card-icon" style="background:rgba(155,114,203,0.1);color:#9b72cb;"><i class="fas fa-user-circle"></i></div>
            <div>
                <h4>Profile Completion</h4>
                <p class="progress-card-subtitle">Complete your profile for a better experience</p>
            </div>
        </div>
        <div class="progress-card-bar">
            <div class="progress-card-bar-track">
                <div class="progress-card-bar-fill" style="width:<?= $profilePct ?>%; background:<?= $profilePct >= 100 ? 'linear-gradient(90deg, #22c55e, #34d399)' : 'linear-gradient(90deg, #f59e0b, #fbbf24)' ?>;"></div>
            </div>
            <span class="progress-card-pct"><?= $profilePct ?>%</span>
        </div>
        <p class="progress-card-detail">
            <?php if ($profilePct >= 100): ?>
                <span style="color:#22c55e;"><i class="fas fa-check-circle"></i> Profile complete!</span>
            <?php else: ?>
                <?= $filled ?> of <?= count($profileFields) ?> fields filled — <a href="profile.php">Complete now</a>
            <?php endif; ?>
        </p>
    </div>

    <!-- Course Progress Card -->
    <div class="progress-card">
        <div class="progress-card-header">
            <div class="progress-card-icon" style="background:rgba(66,133,244,0.1);color:#4285f4;"><i class="fas fa-book-reader"></i></div>
            <div>
                <h4>Course Progress</h4>
                <p class="progress-card-subtitle">Your overall learning progress</p>
            </div>
        </div>
        <div class="progress-card-bar">
            <div class="progress-card-bar-track">
                <div class="progress-card-bar-fill" style="width:<?= $stats['avg_progress'] ?>%; background:<?= $stats['avg_progress'] >= 100 ? 'linear-gradient(90deg, #22c55e, #34d399)' : '' ?>;"></div>
            </div>
            <span class="progress-card-pct"><?= $stats['avg_progress'] ?>%</span>
        </div>
        <div class="course-progress-list">
            <?php if (empty($courseProgress)): ?>
                <p style="font-size:0.82rem;color:var(--text-muted);margin-top:0.5rem;">No enrolled courses yet. <a href="browseCourses.php">Browse courses</a></p>
            <?php else: ?>
                <?php foreach (array_slice($courseProgress, 0, 5) as $cp): 
                    $p = intval($cp['progress']);
                    $color = ($cp['enroll_status'] === 'completed') ? '#22c55e' : (($p >= 50) ? '#4285f4' : '#f59e0b');
                ?>
                <div class="cp-item">
                    <div class="cp-item-info">
                        <span class="cp-item-title"><?= htmlspecialchars($cp['title']) ?></span>
                        <span class="cp-item-pct" style="color:<?= $color ?>;"><?= $p ?>%</span>
                    </div>
                    <div class="cp-item-bar">
                        <div class="cp-item-bar-fill" style="width:<?= $p ?>%;background:<?= $color ?>;"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Stats Grid -->
<div class="stats-grid" id="student-stats-grid">
    <!-- Enrolled -->
    <div class="stat-card">
        <div class="stat-icon" style="background:#4285f415;color:#4285f4;"><i class="fas fa-book-reader"></i></div>
        <div>
            <div class="stat-value"><?= $stats['enrolled'] ?></div>
            <div class="stat-label">Enrolled Courses</div>
        </div>
    </div>
    <!-- Completed -->
    <div class="stat-card">
        <div class="stat-icon" style="background:#34d39915;color:#34d399;"><i class="fas fa-check-circle"></i></div>
        <div>
            <div class="stat-value"><?= $stats['completed'] ?></div>
            <div class="stat-label">Completed</div>
        </div>
    </div>
    <!-- Available -->
    <div class="stat-card">
        <div class="stat-icon" style="background:#9b72cb15;color:#9b72cb;"><i class="fas fa-compass"></i></div>
        <div>
            <div class="stat-value"><?= $stats['available'] ?></div>
            <div class="stat-label">Available Courses</div>
        </div>
    </div>
    <!-- Avg Progress -->
    <div class="stat-card">
        <div class="stat-icon" style="background:#fbbf2415;color:#fbbf24;"><i class="fas fa-chart-line"></i></div>
        <div>
            <div class="stat-value"><?= $stats['avg_progress'] ?>%</div>
            <div class="stat-label">Avg Progress</div>
        </div>
    </div>
</div>

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
<div class="cards-grid" id="student-learning-grid">
    <?php if (empty($continueLearning)): ?>
        <div class="empty-state">
            <i class="fas fa-graduation-cap"></i>
            <p>No courses in progress. 
                <a href="browseCourses.php">Browse courses</a> to get started!</p>
        </div>
    <?php else: ?>
        <?php foreach ($continueLearning as $c): ?>
        <div class="course-card" style="cursor:pointer;" onclick="window.location.href='courseViewer.php?courseId=<?= $c['id'] ?>&enrollmentId=<?= $c['enrollment_id'] ?>'">
            <div class="course-card-thumb">
                <?php if (!empty($c['thumbnail'])): ?>
                    <img src="<?= htmlspecialchars($c['thumbnail']) ?>" alt="Thumbnail">
                <?php else: ?>
                    <i class="fas fa-book"></i>
                <?php endif; ?>
            </div>
            <div class="course-card-body">
                <h3><?= htmlspecialchars($c['title']) ?></h3>
                <p><?= htmlspecialchars(mb_strimwidth($c['description'], 0, 80, '...')) ?></p>
                <div style="margin-top:0.75rem;">
                    <div class="progress-bar-wrap">
                        <div class="progress-bar-fill" style="width:<?= intval($c['progress']) ?>%;"></div>
                    </div>
                    <div style="display:flex;justify-content:space-between;margin-top:0.3rem;font-size:0.8rem;color:var(--text-muted);">
                        <span><?= intval($c['progress']) ?>% complete</span>
                        <span><?= intval($c['content_count']) ?> lessons</span>
                    </div>
                </div>
            </div>
            <div class="course-card-footer">
                <span class="badge badge-draft"><?= htmlspecialchars($c['enroll_status']) ?></span>
                <span style="font-size:0.8rem;color:var(--text-muted);">Enrolled <?= date('M d, Y', strtotime($c['enrolledAt'])) ?></span>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require 'includes/footer.php'; ?>
