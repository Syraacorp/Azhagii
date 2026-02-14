<?php
$pageTitle = 'Course Assignments';
$currentPage = 'courseAssignmentsSSR';
require 'includes/auth.php';
requirePageRole(['superAdmin', 'adminAzhagii']);

// Fetch Courses for Dropdown
$q = "SELECT id, title, courseCode FROM courses ORDER BY title ASC";
$courses = [];
$r = mysqli_query($conn, $q);
while ($r && $row = mysqli_fetch_assoc($r)) {
    $courses[] = $row;
}
// Assigments will be loaded via AJAX on select (loadAssignments() in script.js)
require 'includes/header.php';
require 'includes/sidebar.php';
?>

<div class="section-toolbar">
    <div class="filter-bar">
        <select id="assignCourseSelect" class="form-input form-input-sm" onchange="loadAssignments()">
            <option value="">Select a course</option>
            <?php foreach ($courses as $c): ?>
                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['title']) ?>
                    <?= $c['courseCode'] ? '(' . htmlspecialchars($c['courseCode']) . ')' : '' ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button class="btn btn-primary" onclick="showAssignModal()"><i class="fas fa-plus"></i> Assign to College</button>
</div>
<div class="table-responsive">
    <table class="table" id="assignmentsTable">
        <thead>
            <tr>
                <th>#</th>
                <th>College</th>
                <th>Code</th>
                <th>Assigned By</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="assignmentsBody">
            <tr>
                <td colspan="6" class="empty-state">
                    <p>Select a course above</p>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<script>
    $(document).ready(function () {
        // Initial check if a course is selected (e.g. from reload)
        loadAssignments();
    });

    function loadAssignments() {
        const courseId = $('#assignCourseSelect').val();
        const tbody = $('#assignmentsBody');

        if (!courseId) {
            tbody.html('<tr><td colspan="6" class="empty-state"><p>Select a course above</p></td></tr>');
            return;
        }

        tbody.html('<tr><td colspan="6" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>');

        $.post('backend.php', { get_course_assignments: 1, courseId: courseId }, function (res) {
            if (res.status === 200) {
                let html = '';
                if (res.data.length === 0) {
                    html = '<tr><td colspan="6" class="empty-state"><i class="fas fa-clipboard-list"></i><p>No assignments found for this course</p></td></tr>';
                } else {
                    res.data.forEach((a, i) => {
                        html += `<tr>
                            <td>${i + 1}</td>
                            <td>${escapeHtml(a.college_name)}</td>
                            <td>${escapeHtml(a.college_code)}</td>
                            <td>${escapeHtml(a.assigned_by_name || '-')}</td>
                            <td>${new Date(a.assignedAt).toLocaleDateString()}</td>
                            <td>
                                <button class="btn btn-sm btn-danger" onclick="deleteAssignment(${a.id})"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>`;
                    });
                }
                tbody.html(html);
            } else {
                tbody.html(`<tr><td colspan="6" class="text-center text-danger">Error: ${res.message}</td></tr>`);
            }
        }, 'json').fail(function () {
            tbody.html('<tr><td colspan="6" class="text-center text-danger">Connection failed</td></tr>');
        });
    }

    function showAssignModal() {
        const courseId = $('#assignCourseSelect').val();
        if (!courseId) {
            Swal.fire('Select Course', 'Please select a course first', 'warning');
            return;
        }

        // Fetch colleges
        $.post('backend.php', { get_colleges: 1 }, function (res) {
            if (res.status !== 200) {
                Swal.fire('Error', 'Failed to load colleges', 'error');
                return;
            }

            let collegeOpts = '<option value="">Select College</option>';
            res.data.forEach(c => {
                collegeOpts += `<option value="${c.id}">${escapeHtml(c.name)} (${escapeHtml(c.code)})</option>`;
            });

            Swal.fire({
                title: 'Assign Course to College',
                html: `
                    <div class="form-group text-left">
                        <label>Select College</label>
                        <select id="swal-assignCollege" class="form-input">${collegeOpts}</select>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Assign',
                preConfirm: () => {
                    const colId = document.getElementById('swal-assignCollege').value;
                    if (!colId) { Swal.showValidationMessage('Please select a college'); return false; }
                    return colId;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('backend.php', {
                        assign_course: 1,
                        courseId: courseId,
                        collegeId: result.value
                    }, function (res) {
                        if (res.status === 200) {
                            Swal.fire('Assigned!', res.message, 'success');
                            loadAssignments();
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                    }, 'json');
                }
            });

        }, 'json');
    }

    function deleteAssignment(id) {
        Swal.fire({
            title: 'Remove Assignment?',
            text: "Access for this college will be revoked.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, remove it'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('backend.php', { delete_assignment: 1, id: id }, function (res) {
                    if (res.status === 200) {
                        Swal.fire('Removed!', res.message, 'success');
                        loadAssignments();
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
<?php require 'includes/footer.php'; ?>