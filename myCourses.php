<?php
$pageTitle = 'My Courses';
$currentPage = 'myCourses';
require 'includes/auth.php';
requirePageRole(['azhagiiCoordinator', 'superAdmin']);

$cid = intval($_SESSION['collegeId'] ?? 0);
$uid = $_SESSION['userId'];

// Fetch Courses (Assigned to College OR Created by Coordinator)
$where = "1=0"; // Default to none
if (hasRole('superAdmin')) {
    $where = "1=1"; // SuperAdmin sees all
} elseif (hasRole('azhagiiCoordinator')) {
    $where = "(c.id IN (SELECT courseId FROM coursecolleges WHERE collegeId=$cid) OR c.createdBy=$uid)";
} elseif (hasRole('adminAzhagii')) {
    $where = "1=1"; // Admin also sees all? Assuming similar to SuperAdmin for "My Courses" view if they access it.
} else {
    // Fallback for others (students shouldn't be here, they have browseCourses)
    $where = "c.createdBy=$uid";
}

$q = "SELECT c.*, u.name as creator_name,
      (SELECT COUNT(*) FROM coursecontent WHERE courseId=c.id AND status='active') as content_count 
      FROM courses c 
      LEFT JOIN users u ON c.createdBy=u.id
      WHERE $where
      ORDER BY c.createdAt DESC";

$courses = [];
$r = mysqli_query($conn, $q);
while ($r && $row = mysqli_fetch_assoc($r)) {
    $courses[] = $row;
}

require 'includes/header.php';
require 'includes/sidebar.php';
?>

<div class="cards-grid" id="coord-courses-ssr">
    <?php if (empty($courses)): ?>
        <div class="empty-state">
            <i class="fas fa-book-open"></i>
            <p>No courses assigned to your college yet.</p>
        </div>
    <?php else: ?>
        <?php foreach ($courses as $c):
            $desc = htmlspecialchars($c['description'] ?? '');
            if (strlen($desc) > 100)
                $desc = mb_strimwidth($desc, 0, 100, '...');
            $isMine = ($c['createdBy'] == $uid);
            $badge = $isMine ? '<span class="badge badge-draft">Created by Me</span>' : '<span class="badge badge-active">Assigned</span>';
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
                    <div style="margin-top:0.5rem;display:flex;justify-content:space-between;align-items:center;">
                        <span class="badge badge-role"><?= htmlspecialchars($c['category'] ?: 'General') ?></span>
                        <?= $badge ?>
                    </div>
                </div>
                <div class="course-card-footer">
                    <span style="font-size:0.85rem;color:var(--text-muted);"><?= intval($c['content_count']) ?> lessons</span>
                    <button class="btn btn-outline btn-sm" onclick="viewCourseDetail(<?= $c['id'] ?>)">
                        <i class="fas fa-eye"></i> Details
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>


<?php require 'includes/footer.php'; ?>

<script>
    function viewCourseDetail(id) {
        $.post('backend.php', { get_course_detail: 1, courseId: id }, function (res) {
            if (res.status !== 200) { Swal.fire('Error', 'Could not fetch details', 'error'); return; }
            const c = res.data;
            const syllabusHtml = c.syllabus ? `<p><a href="${c.syllabus}" target="_blank" class="btn btn-sm btn-outline"><i class="fas fa-file-pdf"></i> View Syllabus</a></p>` : '';

            Swal.fire({
                title: c.title,
                html: `
                    <div class="text-left">
                        <p><strong>Code:</strong> ${escapeHtml(c.courseCode || '-')}</p>
                        <p><strong>Category:</strong> ${escapeHtml(c.category || '-')}</p>
                        <p><strong>Semester:</strong> ${c.semester || '-'}</p>
                        <p><strong>Description:</strong><br>${escapeHtml(c.description || '-')}</p>
                        ${syllabusHtml}
                    </div>
                `,
                width: '600px',
                showCloseButton: true
            });
        }, 'json');
    }

    function escapeHtml(text) {
        if (!text) return '';
        return $('<div>').text(text).html();
    }
</script>