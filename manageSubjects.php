<?php
$pageTitle = 'Manage Subjects';
$currentPage = 'manageSubjectsSSR';
require 'includes/auth.php';
requirePageRole(['superAdmin', 'adminAzhagii']);

// Fetch Courses for Dropdown
$q = "SELECT id, title, courseCode FROM courses ORDER BY title ASC";
$courses = [];
$r = mysqli_query($conn, $q);
while ($r && $row = mysqli_fetch_assoc($r)) {
    $courses[] = $row;
}

require 'includes/header.php';
require 'includes/sidebar.php';
?>

<div class="section-toolbar">
    <div class="filter-bar">
        <select id="subjectCourseSelect" class="form-input form-input-sm" onchange="loadSubjects()">
            <option value="">Select a course</option>
            <?php foreach ($courses as $c): ?>
                <option value="<?= $c['id'] ?>">
                    <?= htmlspecialchars($c['title']) ?>
                    <?= $c['courseCode'] ? '(' . htmlspecialchars($c['courseCode']) . ')' : '' ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <button class="btn btn-primary" onclick="showSubjectModal()"><i class="fas fa-plus"></i> Add Subject</button>
</div>

<div class="table-responsive">
    <table class="table" id="subjectsTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Title</th>
                <th>Code</th>
                <th>Description</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="subjectsBody">
            <tr>
                <td colspan="6" class="empty-state">
                    <p>Select a course above</p>
                </td>
            </tr>
        </tbody>
    </table>
</div>


<?php require 'includes/footer.php'; ?>

<script>
    function loadSubjects() {
        const courseId = $('#subjectCourseSelect').val();
        const tbody = $('#subjectsBody');

        if (!courseId) {
            tbody.html('<tr><td colspan="6" class="empty-state"><p>Select a course above</p></td></tr>');
            return;
        }

        tbody.html('<tr><td colspan="6" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>');

        $.post('backend.php', { get_subjects: 1, courseId: courseId }, function (res) {
            if (res.status === 200) {
                let html = '';
                if (res.data.length === 0) {
                    html = '<tr><td colspan="6" class="empty-state"><i class="fas fa-book-open"></i><p>No subjects found for this course</p></td></tr>';
                } else {
                    res.data.forEach((s, i) => {
                        html += `<tr>
                            <td>${i + 1}</td>
                            <td>${escapeHtml(s.title)}</td>
                            <td>${escapeHtml(s.code)}</td>
                            <td>${escapeHtml(s.description || '-')}</td>
                            <td><span class="badge badge-active">Active</span></td>
                            <td>
                                <button class="btn btn-sm btn-danger" onclick="deleteSubject(${s.id})"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>`;
                    });
                }
                tbody.html(html);
            } else {
                tbody.html(`<tr><td colspan="6" class="text-center text-danger">Error: ${res.message}</td></tr>`);
            }
        }, 'json');
    }

    function showSubjectModal() {
        const courseId = $('#subjectCourseSelect').val();
        if (!courseId) {
            Swal.fire('Select Course', 'Please select a course first', 'warning');
            return;
        }

        Swal.fire({
            title: 'Add New Subject',
            html: `
                <div class="swal-form text-left">
                    <div class="form-group"><label>Title <span class="text-danger">*</span></label><input id="sw-sTitle" class="form-input"></div>
                    <div class="form-group"><label>Code</label><input id="sw-sCode" class="form-input"></div>
                    <div class="form-group"><label>Description</label><textarea id="sw-sDesc" class="form-input" rows="2"></textarea></div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Add Subject',
            preConfirm: () => {
                const title = $('#sw-sTitle').val();
                if (!title) { Swal.showValidationMessage('Title is required'); return false; }
                return {
                    title: title,
                    code: $('#sw-sCode').val(),
                    desc: $('#sw-sDesc').val()
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const d = result.value;
                $.post('backend.php', {
                    add_subject: 1,
                    courseId: courseId,
                    title: d.title,
                    code: d.code,
                    description: d.desc
                }, function (res) {
                    if (res.status === 200) {
                        Swal.fire('Added!', res.message, 'success');
                        loadSubjects();
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                }, 'json');
            }
        });
    }

    function deleteSubject(id) {
        Swal.fire({
            title: 'Delete Subject?',
            text: "This will remove the subject and all topics under it.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('backend.php', { delete_subject: 1, id: id }, function (res) {
                    if (res.status === 200) {
                        Swal.fire('Deleted!', res.message, 'success');
                        loadSubjects();
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