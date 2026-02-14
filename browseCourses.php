    <?php
$pageTitle = 'Browse Courses';
$currentPage = 'browseCourses';
require 'includes/auth.php';
requirePageRole('azhagiiStudents');

$cid = $_SESSION['collegeId'];
$uid = $_SESSION['userId'];

// 1. Get Enrolled Course IDs
$enrolledIds = [];
$r = mysqli_query($conn, "SELECT courseId FROM enrollments WHERE studentId=$uid");
while ($row = mysqli_fetch_assoc($r)) {
    $enrolledIds[] = intval($row['courseId']);
}

// 2. Fetch Available Courses
// Logic: Status=active AND (Assigned to Student's College OR (Maybe Public? Script just said collegeId check))
// Backend logic strictly checks coursecolleges.
$q = "SELECT c.*, 
      (SELECT COUNT(*) FROM coursecontent WHERE courseId=c.id AND status='active') as content_count 
      FROM courses c 
      WHERE c.status='active' 
      AND c.id IN (SELECT courseId FROM coursecolleges WHERE collegeId=$cid)
      ORDER BY c.createdAt DESC";
$courses = [];
$r = mysqli_query($conn, $q);
while ($r && $row = mysqli_fetch_assoc($r)) {
    $courses[] = $row;
}
require 'includes/header.php';
require 'includes/sidebar.php';
?>

<div class="cards-grid" id="browse-courses-ssr">
    <?php if (empty($courses)): ?>
        <div class="empty-state">
            <i class="fas fa-compass"></i>
            <p>No courses available for your college yet</p>
        </div>
    <?php else: ?>
        <?php foreach ($courses as $c):
            $isEnrolled = in_array(intval($c['id']), $enrolledIds);
            $desc = htmlspecialchars($c['description'] ?? '');
            if (strlen($desc) > 100)
                $desc = mb_strimwidth($desc, 0, 100, '...');
            ?>
            <div class="course-card">
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
                    <div style="margin-top:0.5rem;">
                        <span class="badge badge-role"><?= htmlspecialchars($c['category'] ?: 'General') ?></span>
                    </div>
                </div>
                <div class="course-card-footer">
                    <span style="font-size:0.85rem;color:var(--text-muted);"><?= intval($c['content_count']) ?> lessons</span>
                    <?php if ($isEnrolled): ?>
                        <span class="badge badge-active">Enrolled</span>
                    <?php else: ?>
                        <button class="btn btn-primary btn-sm" onclick="enrollCourse(<?= $c['id'] ?>)">
                            <i class="fas fa-plus"></i> Enroll
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>


<?php require 'includes/footer.php'; ?>

<script>
    function enrollCourse(id) {
        Swal.fire({
            title: 'Enroll in Course?',
            text: "You are about to enroll in this course.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Enroll Me',
            confirmButtonColor: '#10b981'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('backend.php', { enroll_course: 1, courseId: id }, function (res) {
                    if (res.status === 200) {
                        Swal.fire('Enrolled!', 'You have successfully enrolled.', 'success').then(() => {
                            window.location.href = 'myLearning.php';
                        });
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                }, 'json');
            }
        });
    }
</script>