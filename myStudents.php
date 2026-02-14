<?php
$pageTitle = 'My Students';
$currentPage = 'myStudentsSSR';
require 'includes/auth.php';
requirePageRole('azhagiiCoordinator');

$cid = $_SESSION['collegeId'];
$uid = $_SESSION['userId'];

// Fetch Courses for Dropdown (Assigned or Created)
$q = "SELECT id, title, courseCode 
      FROM courses 
      WHERE (id IN (SELECT courseId FROM coursecolleges WHERE collegeId=?) OR createdBy=?) 
      ORDER BY title ASC";
$stmt = $conn->prepare($q);
$stmt->bind_param("ii", $cid, $uid);
$stmt->execute();
$r = $stmt->get_result();
$courses = [];
while ($r && $row = $r->fetch_assoc()) {
    $courses[] = $row;
}
$stmt->close();

require 'includes/header.php';
require 'includes/sidebar.php';
?>

<div class="section-toolbar">
    <div class="filter-bar">
        <select id="studentCourseSelect" class="form-input form-input-sm" onchange="loadCourseStudents()">
            <option value="">Select a course</option>
            <?php foreach ($courses as $c): ?>
                <option value="<?= $c['id'] ?>">
                    <?= htmlspecialchars($c['title']) ?>
                    <?= $c['courseCode'] ? '(' . htmlspecialchars($c['courseCode']) . ')' : '' ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>
<div class="table-responsive">
    <table class="table" id="courseStudentsTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Student</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Progress</th>
                <th>Status</th>
                <th>Enrolled</th>
            </tr>
        </thead>
        <tbody id="courseStudentsBody">
            <tr>
                <td colspan="7" class="empty-state">
                    <p>Select a course above</p>
                </td>
            </tr>
        </tbody>
    </table>
</div>


<?php require 'includes/footer.php'; ?>

<script>
    function loadCourseStudents() {
        const courseId = $('#studentCourseSelect').val();
        const tbody = $('#courseStudentsBody');

        if (!courseId) {
            tbody.html('<tr><td colspan="7" class="empty-state"><p>Select a course above</p></td></tr>');
            return;
        }

        tbody.html('<tr><td colspan="7" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>');

        $.post('backend.php', { get_course_students: 1, courseId: courseId }, function (res) {
            if (res.status === 200) {
                let html = '';
                if (res.data.length === 0) {
                    html = '<tr><td colspan="7" class="empty-state"><i class="fas fa-user-graduate"></i><p>No students enrolled yet</p></td></tr>';
                } else {
                    res.data.forEach((s, i) => {
                        const progress = s.progress || 0;
                        const statusClass = s.status === 'completed' ? 'active' : 'pending';

                        html += `<tr>
                            <td>${i + 1}</td>
                            <td>
                                <div><strong>${escapeHtml(s.student_name)}</strong></div>
                                <div class="text-muted small">${escapeHtml(s.rollNumber || '')}</div>
                            </td>
                            <td>${escapeHtml(s.student_email)}</td>
                            <td>${escapeHtml(s.phone || '-')}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="progress-bar-wrap mr-2" style="width:100px;margin-right:0.5rem;">
                                        <div class="progress-bar-fill" style="width:${progress}%"></div>
                                    </div>
                                    <span>${progress}%</span>
                                </div>
                            </td>
                            <td><span class="badge badge-${statusClass}">${s.status}</span></td>
                            <td>${new Date(s.enrolledAt).toLocaleDateString()}</td>
                        </tr>`;
                    });
                }
                tbody.html(html);
            } else {
                tbody.html(`<tr><td colspan="7" class="text-center text-danger">Error: ${res.message}</td></tr>`);
            }
        }, 'json').fail(function () {
            tbody.html('<tr><td colspan="7" class="text-center text-danger">Connection failed</td></tr>');
        });
    }

    function escapeHtml(text) {
        if (!text) return '';
        return $('<div>').text(text).html();
    }
</script>