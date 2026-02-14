<?php
$pageTitle = 'Manage Courses';
// Prevent JS auto-load
$currentPage = 'manageCoursesSSR';
require 'includes/auth.php';
requirePageRole(['superAdmin', 'adminAzhagii']);

// Fetch Courses
$q = "SELECT c.*, u.name as creator_name, ap.name as approver_name,
      (SELECT COUNT(*) FROM coursecolleges WHERE courseId=c.id) as college_count,
      (SELECT COUNT(*) FROM enrollments WHERE courseId=c.id) as enrollment_count,
      (SELECT COUNT(*) FROM coursecontent WHERE courseId=c.id) as content_count,
      (SELECT COUNT(*) FROM subjects WHERE courseId=c.id) as subject_count
      FROM courses c 
      LEFT JOIN users u ON c.createdBy=u.id
      LEFT JOIN users ap ON c.approvedBy=ap.id
      ORDER BY c.createdAt DESC";
$courses = [];
$r = mysqli_query($conn, $q);
while ($r && $row = mysqli_fetch_assoc($r)) {
    $courses[] = $row;
}
require 'includes/header.php';
require 'includes/sidebar.php';
?>

<div class="section-toolbar">
    <div></div>
    <button class="btn btn-primary" onclick="showCourseModal()"><i class="fas fa-plus"></i> Add Course</button>
</div>
<div class="table-responsive">
    <table class="table" id="coursesTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Title</th>
                <th>Category</th>
                <th>Semester</th>
                <th>Colleges</th>
                <th>Enrollments</th>
                <th>Content</th>
                <th>Status</th>
                <th>Syllabus</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="coursesBody">
            <?php if (empty($courses)): ?>
                <tr>
                    <td colspan="10" class="empty-state">
                        <p>No courses found</p>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($courses as $i => $c):
                    $badge = ($c['status'] === 'active') ? 'active' : ($c['status'] === 'pending' ? 'pending' : ($c['status'] === 'rejected' ? 'rejected' : 'inactive'));
                    ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($c['title']) ?></td>
                        <td><span class="badge badge-draft"><?= htmlspecialchars($c['category'] ?: '-') ?></span></td>
                        <td><?= htmlspecialchars($c['semester'] ?: '-') ?></td>
                        <td><?= intval($c['college_count']) ?></td>
                        <td><?= intval($c['enrollment_count']) ?></td>
                        <td><?= intval($c['content_count']) ?></td>
                        <td><span class="badge badge-<?= $badge ?>"><?= htmlspecialchars($c['status']) ?></span></td>
                        <td>
                            <?php if (!empty($c['syllabus'])): ?>
                                <a href="<?= htmlspecialchars($c['syllabus']) ?>" target="_blank" class="btn btn-outline btn-sm"><i
                                        class="fas fa-file-pdf"></i> PDF</a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td class="actions">
                            <button class="btn btn-outline btn-sm" onclick="editCourse(<?= $c['id'] ?>)"><i
                                    class="fas fa-edit"></i></button>
                            <button class="btn btn-danger btn-sm" onclick="deleteCourse(<?= $c['id'] ?>)"><i
                                    class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>


<?php require 'includes/footer.php'; ?>

<script>
    $(document).ready(function () {
        $('#coursesTable').DataTable({
            paging: true, searching: true, info: true, ordering: true,
            language: { search: '', searchPlaceholder: 'Search...' }
        });
    });

    function showCourseModal() {
        Swal.fire({
            title: 'Add New Course',
            html: `
                <div class="swal-form text-left" style="max-height:60vh;overflow-y:auto;padding-right:5px;">
                    <div class="form-group"><label>Title <span class="text-danger">*</span></label><input id="sw-cTitle" class="form-input"></div>
                    <div class="form-group"><label>Course Code</label><input id="sw-cCode" class="form-input"></div>
                    <div class="form-group"><label>Description</label><textarea id="sw-cDesc" class="form-input" rows="3"></textarea></div>
                    <div class="form-group"><label>Category</label><input id="sw-cCat" class="form-input" placeholder="e.g. Engineering"></div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Semester</label>
                            <select id="sw-cSem" class="form-input">
                                <option value="">Select</option>
                                ${[1, 2, 3, 4, 5, 6, 7, 8].map(i => `<option value="${i}">Sem ${i}</option>`).join('')}
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Status</label>
                            <select id="sw-cStatus" class="form-input">
                                <option value="active">Active</option>
                                <option value="draft">Draft</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
            `,
            width: '600px',
            showCancelButton: true,
            confirmButtonText: 'Create Course',
            preConfirm: () => {
                const title = $('#sw-cTitle').val();
                if (!title) { Swal.showValidationMessage('Title is required'); return false; }
                return {
                    title: title,
                    code: $('#sw-cCode').val(),
                    desc: $('#sw-cDesc').val(),
                    cat: $('#sw-cCat').val(),
                    sem: $('#sw-cSem').val(),
                    status: $('#sw-cStatus').val()
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const d = result.value;
                $.post('backend.php', {
                    add_course: 1,
                    title: d.title,
                    courseCode: d.code,
                    description: d.desc,
                    category: d.cat,
                    semester: d.sem,
                    status: d.status
                }, function (res) {
                    if (res.status === 200) {
                        Swal.fire('Created!', res.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                }, 'json');
            }
        });
    }

    function editCourse(id) {
        // Fetch course details first
        $.post('backend.php', { get_course_detail: 1, courseId: id }, function (res) {
            if (res.status !== 200) { Swal.fire('Error', 'Could not fetch course details', 'error'); return; }
            const c = res.data;

            Swal.fire({
                title: 'Edit Course',
                html: `
                    <div class="swal-form text-left" style="max-height:60vh;overflow-y:auto;padding-right:5px;">
                        <input type="hidden" id="sw-cId" value="${c.id}">
                        <div class="form-group"><label>Title <span class="text-danger">*</span></label><input id="sw-cTitle" class="form-input" value="${escapeHtml(c.title)}"></div>
                        <div class="form-group"><label>Course Code</label><input id="sw-cCode" class="form-input" value="${escapeHtml(c.courseCode)}"></div>
                        <div class="form-group"><label>Description</label><textarea id="sw-cDesc" class="form-input" rows="3">${escapeHtml(c.description)}</textarea></div>
                        <div class="form-group"><label>Category</label><input id="sw-cCat" class="form-input" value="${escapeHtml(c.category)}"></div>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Semester</label>
                                <select id="sw-cSem" class="form-input">
                                    <option value="">Select</option>
                                    ${[1, 2, 3, 4, 5, 6, 7, 8].map(i => `<option value="${i}" ${c.semester == i ? 'selected' : ''}>Sem ${i}</option>`).join('')}
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Status</label>
                                <select id="sw-cStatus" class="form-input">
                                    <option value="active" ${c.status === 'active' ? 'selected' : ''}>Active</option>
                                    <option value="draft" ${c.status === 'draft' ? 'selected' : ''}>Draft</option>
                                    <option value="inactive" ${c.status === 'inactive' ? 'selected' : ''}>Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                `,
                width: '600px',
                showCancelButton: true,
                confirmButtonText: 'Update Course',
                preConfirm: () => {
                    const title = $('#sw-cTitle').val();
                    if (!title) { Swal.showValidationMessage('Title is required'); return false; }
                    return {
                        id: $('#sw-cId').val(),
                        title: title,
                        code: $('#sw-cCode').val(),
                        desc: $('#sw-cDesc').val(),
                        cat: $('#sw-cCat').val(),
                        sem: $('#sw-cSem').val(),
                        status: $('#sw-cStatus').val()
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const d = result.value;
                    $.post('backend.php', {
                        update_course: 1,
                        id: d.id,
                        title: d.title,
                        courseCode: d.code,
                        description: d.desc,
                        category: d.cat,
                        semester: d.sem,
                        status: d.status
                    }, function (res) {
                        if (res.status === 200) {
                            Swal.fire('Updated!', res.message, 'success').then(() => location.reload());
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                    }, 'json');
                }
            });
        }, 'json');
    }

    function deleteCourse(id) {
        Swal.fire({
            title: 'Delete Course?',
            text: "This will delete the course and all associated content permanently!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('backend.php', { delete_course: 1, id: id }, function (res) {
                    if (res.status === 200) {
                        Swal.fire('Deleted!', res.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                }, 'json');
            }
        });
    }

    function escapeHtml(text) {
        if (!text) return '';
        return $('<div>').text(text).html();
    }
</script>