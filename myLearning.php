<?php
$pageTitle = 'My Learning';
$currentPage = 'myLearning';
require 'includes/auth.php';
requirePageRole('azhagiiStudents');

$uid = $_SESSION['userId'];

// 1. Fetch Enrolled Courses
$q = "SELECT c.*, e.id as enrollment_id, e.progress, e.status as enroll_status, e.enrolledAt, 
      (SELECT COUNT(*) FROM coursecontent WHERE courseId=c.id AND status='active') as content_count 
      FROM enrollments e 
      JOIN courses c ON e.courseId=c.id 
      WHERE e.studentId=$uid 
      ORDER BY e.enrolledAt DESC";
$myCourses = [];
$r = mysqli_query($conn, $q);
while ($r && $row = mysqli_fetch_assoc($r)) {
    $myCourses[] = $row;
}
require 'includes/header.php';
require 'includes/sidebar.php';
?>

<div class="cards-grid" id="my-learning-ssr">
    <?php if (empty($myCourses)): ?>
        <div class="empty-state">
            <i class="fas fa-graduation-cap"></i>
            <p>You haven't enrolled in any courses yet.<br><a href="browseCourses.php">Browse courses</a></p>
        </div>
    <?php else: ?>
        <?php foreach ($myCourses as $c):
            $desc = htmlspecialchars($c['description'] ?? '');
            if (strlen($desc) > 80)
                $desc = mb_strimwidth($desc, 0, 80, '...');
            $progress = intval($c['progress']);
            $statusBadge = ($c['enroll_status'] === 'completed') ? 'badge-active' : 'badge-draft';
            $statusText = ucfirst($c['enroll_status']);
            ?>
            <div class="course-card" style="cursor:pointer;" onclick="viewCourse(<?= $c['id'] ?>, <?= $c['enrollment_id'] ?>)">
                <div class="course-card-thumb">
                    <?php if (!empty($c['thumbnail'])): ?>
                        <img src="<?= htmlspecialchars($c['thumbnail']) ?>" alt="Thumbnail">
                    <?php else: ?>
                        <i class="fas fa-book"></i>
                    <?php endif; ?>
                </div>
                <div class="course-card-body">
                    <h3><?= htmlspecialchars($c['title']) ?></h3>
                    <p><?= $desc ?></p>
                    <div style="margin-top:0.75rem;">
                        <div class="progress-bar-wrap">
                            <div class="progress-bar-fill" style="width:<?= $progress ?>%;"></div>
                        </div>
                        <div
                            style="display:flex;justify-content:space-between;margin-top:0.3rem;font-size:0.8rem;color:var(--text-muted);">
                            <span><?= $progress ?>% complete</span>
                            <span><?= intval($c['content_count']) ?> lessons</span>
                        </div>
                    </div>
                </div>
                <div class="course-card-footer">
                    <span class="badge <?= $statusBadge ?>"><?= $statusText ?></span>
                    <span style="font-size:0.8rem;color:var(--text-muted);">Enrolled
                        <?= date('M d, Y', strtotime($c['enrolledAt'])) ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>


<?php require 'includes/footer.php'; ?>

<script>
    function viewCourse(courseId, enrollmentId) {
        window.location.href = `courseViewer.php?courseId=${courseId}&enrollmentId=${enrollmentId}`;
    }
</script>