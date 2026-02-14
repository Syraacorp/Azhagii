<?php
$pageTitle = 'Create Course';
$currentPage = 'coordinatorCourseCreateSSR';
require 'includes/auth.php';
requirePageRole(['azhagiiCoordinator', 'superAdmin']);

$uid = $_SESSION['userId'];

// Fetch "My Submitted Courses"
$myCourses = [];
$stmt = $conn->prepare("SELECT * FROM courses WHERE createdBy = ? ORDER BY createdAt DESC");
$stmt->bind_param("i", $uid);
$stmt->execute();
$r = $stmt->get_result();
while ($r && $row = $r->fetch_assoc()) {
    $myCourses[] = $row;
}
$stmt->close();

require 'includes/header.php';
require 'includes/sidebar.php';
?>

<div class="card" style="max-width:800px;">
    <h3 style="margin-bottom:0.5rem;">Submit a New Course</h3>
    <p style="color:var(--text-muted);margin-bottom:1.5rem;">Fill in the course details below. Your course will be
        submitted for admin approval.</p>

    <form id="coordCourseForm" enctype="multipart/form-data">
        <div class="responsive-grid-2">
            <div class="form-group">
                <label class="form-label">Course Title *</label>
                <input type="text" name="title" class="form-input" required placeholder="e.g. Data Structures">
            </div>
            <div class="form-group">
                <label class="form-label">Course Code</label>
                <input type="text" name="courseCode" class="form-input" placeholder="e.g. CS201">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-input" rows="3"
                placeholder="Brief description of the course"></textarea>
        </div>

        <div class="responsive-grid-3">
            <div class="form-group">
                <label class="form-label">Category</label>
                <input type="text" name="category" class="form-input" placeholder="e.g. Computer Science">
            </div>
            <div class="form-group">
                <label class="form-label">Course Type</label>
                <select name="courseType" class="form-input">
                    <option value="theory">Theory</option>
                    <option value="lab">Lab / Practical</option>
                    <option value="elective">Elective</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Semester</label>
                <select name="semester" class="form-input">
                    <option value="">Select</option>
                    <option value="1">Semester 1</option>
                    <option value="2">Semester 2</option>
                    <option value="3">Semester 3</option>
                    <option value="4">Semester 4</option>
                    <option value="5">Semester 5</option>
                    <option value="6">Semester 6</option>
                    <option value="7">Semester 7</option>
                    <option value="8">Semester 8</option>
                </select>
            </div>
        </div>

        <div class="responsive-grid-2">
            <div class="form-group">
                <label class="form-label">Regulation</label>
                <input type="text" name="regulation" class="form-input" placeholder="e.g. R2021">
            </div>
            <div class="form-group">
                <label class="form-label">Academic Year</label>
                <input type="text" name="academicYear" class="form-input" placeholder="e.g. 2024-2025">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Syllabus PDF (max 2MB)</label>
            <input type="file" name="syllabus" class="form-input" accept=".pdf">
        </div>

        <!-- Units / Subjects Section -->
        <div style="margin-top:1.5rem;margin-bottom:1rem;">
            <h4 style="margin-bottom:0.75rem;"><i class="fas fa-layer-group"
                    style="color:var(--accent-blue);margin-right:0.5rem;"></i>Units / Subjects</h4>
            <p style="font-size:0.85rem;color:var(--text-muted);margin-bottom:1rem;">Add up to 5 units. You can add
                topics to each unit after the course is created.</p>
        </div>
        <div id="unitsContainer">
            <div class="unit-entry" style="display:flex;gap:0.75rem;margin-bottom:0.75rem;align-items:center;">
                <span style="color:var(--text-muted);font-weight:600;min-width:24px;">1.</span>
                <input type="text" name="unit_1" class="form-input" placeholder="Unit 1 title" style="flex:1;">
            </div>
            <div class="unit-entry" style="display:flex;gap:0.75rem;margin-bottom:0.75rem;align-items:center;">
                <span style="color:var(--text-muted);font-weight:600;min-width:24px;">2.</span>
                <input type="text" name="unit_2" class="form-input" placeholder="Unit 2 title" style="flex:1;">
            </div>
            <div class="unit-entry" style="display:flex;gap:0.75rem;margin-bottom:0.75rem;align-items:center;">
                <span style="color:var(--text-muted);font-weight:600;min-width:24px;">3.</span>
                <input type="text" name="unit_3" class="form-input" placeholder="Unit 3 title" style="flex:1;">
            </div>
            <div class="unit-entry" style="display:flex;gap:0.75rem;margin-bottom:0.75rem;align-items:center;">
                <span style="color:var(--text-muted);font-weight:600;min-width:24px;">4.</span>
                <input type="text" name="unit_4" class="form-input" placeholder="Unit 4 title" style="flex:1;">
            </div>
            <div class="unit-entry" style="display:flex;gap:0.75rem;margin-bottom:0.75rem;align-items:center;">
                <span style="color:var(--text-muted);font-weight:600;min-width:24px;">5.</span>
                <input type="text" name="unit_5" class="form-input" placeholder="Unit 5 title" style="flex:1;">
            </div>
        </div>

        <div class="responsive-btn-row" style="margin-top:1.5rem;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Submit for
                Approval</button>
            <a href="myCourses.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Cancel</a>
        </div>
    </form>
</div>

<!-- My Submitted Courses -->
<div class="dashboard-section-title" style="margin-top:2rem;"><i class="fas fa-history"></i> My Submitted Courses</div>
<div class="table-responsive">
    <table class="table" id="mySubmittedCoursesTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Title</th>
                <th>Code</th>
                <th>Semester</th>
                <th>Status</th>
                <th>Reason</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="mySubmittedCoursesBody">
            <?php if (empty($myCourses)): ?>
                <tr>
                    <td colspan="8" class="empty-state"><i class="fas fa-book"></i>
                        <p>No courses submitted yet</p>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($myCourses as $i => $c):
                    $statusBadge = $c['status'] === 'active' ? 'active' : ($c['status'] === 'pending' ? 'pending' : ($c['status'] === 'rejected' ? 'rejected' : 'draft'));
                    ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($c['title']) ?></td>
                        <td><?= htmlspecialchars($c['courseCode'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($c['semester'] ?? '-') ?></td>
                        <td><span class="badge badge-<?= $statusBadge ?>"><?= $c['status'] ?></span></td>
                        <td><?= htmlspecialchars($c['rejectionReason'] ?? '-') ?></td>
                        <td><?= date('M d, Y', strtotime($c['createdAt'])) ?></td>
                        <td class="actions">
                            <?php if ($c['status'] === 'pending' || $c['status'] === 'rejected'): ?>
                                <button class="btn btn-outline btn-sm" onclick="editMySubmittedCourse(<?= $c['id'] ?>)"><i
                                        class="fas fa-edit"></i></button>
                                <button class="btn btn-danger btn-sm" onclick="deleteMySubmittedCourse(<?= $c['id'] ?>)"><i
                                        class="fas fa-trash"></i></button>
                            <?php else: ?>
                                <button class="btn btn-outline btn-sm" onclick="viewCourseDetail(<?= $c['id'] ?>)"><i
                                        class="fas fa-eye"></i></button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
    $(document).ready(function () {
        $('#mySubmittedCoursesTable').DataTable({
            paging: true, searching: true, info: true, ordering: true,
            language: { search: '', searchPlaceholder: 'Search...' }
        });

        // Form Submission
        $('#coordCourseForm').submit(function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('add_course', 1);
            // formData.append('status', 'pending'); // Handled in backend for coordinators

            const btn = $(this).find('button[type=submit]');
            const oldHtml = btn.html();
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Submitting...');

            $.ajax({
                url: 'backend.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function (res) {
                    if (res.status === 200) {
                        Swal.fire('Success', 'Course submitted for approval!', 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', res.message, 'error');
                        btn.prop('disabled', false).html(oldHtml);
                    }
                },
                error: function () {
                    Swal.fire('Error', 'Connection failed', 'error');
                    btn.prop('disabled', false).html(oldHtml);
                }
            });
        });
    });

    function editMySubmittedCourse(id) {
        // Fetch course details
        $.post('backend.php', { get_course_detail: 1, courseId: id }, function (res) {
            if (res.status === 200) {
                const c = res.data;
                Swal.fire({
                    title: 'Edit Course Reference',
                    html: `
                        <div class="swal-form text-left">
                            <input type="hidden" id="sw-cId" value="${c.id}">
                            <div class="form-group"><label>Title</label><input id="sw-cTitle" class="form-input" value="${escapeHtml(c.title)}"></div>
                            <div class="form-group"><label>Code</label><input id="sw-cCode" class="form-input" value="${escapeHtml(c.courseCode || '')}"></div>
                            <div class="form-group"><label>Type</label>
                                <select id="sw-cType" class="form-input">
                                    <option value="theory" ${c.courseType == 'theory' ? 'selected' : ''}>Theory</option>
                                    <option value="lab" ${c.courseType == 'lab' ? 'selected' : ''}>Lab</option>
                                    <option value="elective" ${c.courseType == 'elective' ? 'selected' : ''}>Elective</option>
                                </select>
                            </div>
                            <div class="form-group"><label>Semester</label><input id="sw-cSem" class="form-input" value="${escapeHtml(c.semester || '')}"></div>
                            <div class="form-group"><label>Description</label><textarea id="sw-cDesc" class="form-input" rows="2">${escapeHtml(c.description || '')}</textarea></div>
                            <p class="text-muted" style="font-size:0.8rem;">Note: Editing will re-submit the course for approval.</p>
                        </div>
                    `,
                    width: '600px',
                    showCancelButton: true,
                    confirmButtonText: 'Update & Resubmit',
                    preConfirm: () => {
                        return {
                            id: $('#sw-cId').val(),
                            title: $('#sw-cTitle').val(),
                            courseCode: $('#sw-cCode').val(),
                            courseType: $('#sw-cType').val(),
                            semester: $('#sw-cSem').val(),
                            description: $('#sw-cDesc').val()
                        };
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const d = result.value;
                        const fd = new FormData();
                        fd.append('update_course', 1);
                        fd.append('id', d.id);
                        fd.append('title', d.title);
                        fd.append('courseCode', d.courseCode);
                        fd.append('courseType', d.courseType);
                        fd.append('semester', d.semester);
                        fd.append('description', d.description);

                        $.ajax({
                            url: 'backend.php', type: 'POST', data: fd, processData: false, contentType: false, dataType: 'json',
                            success: function (r) {
                                if (r.status === 200) Swal.fire('Updated', 'Course re-submitted for approval', 'success').then(() => location.reload());
                                else Swal.fire('Error', r.message, 'error');
                            }
                        });
                    }
                });
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        }, 'json');
    }

    function deleteMySubmittedCourse(id) {
        Swal.fire({
            title: 'Delete Course?',
            text: "This cannot be undone.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('backend.php', { delete_course: 1, id: id }, function (res) {
                    if (res.status === 200) Swal.fire('Deleted', 'Course deleted', 'success').then(() => location.reload());
                    else Swal.fire('Error', res.message, 'error');
                }, 'json');
            }
        });
    }

    function viewCourseDetail(id) {
        $.post('backend.php', { get_course_detail: 1, courseId: id }, function (res) {
            if (res.status === 200) {
                const c = res.data;
                const subjects = c.subjects || [];
                let subHtml = '';
                if (subjects.length > 0) {
                    subHtml = '<ul style="text-align:left;margin-top:0.5rem;padding-left:1.2rem;">';
                    subjects.forEach(s => subHtml += `<li>${escapeHtml(s.title)}</li>`);
                    subHtml += '</ul>';
                } else {
                    subHtml = '<p class="text-muted">No units added yet.</p>';
                }

                Swal.fire({
                    title: escapeHtml(c.title),
                    html: `
                        <div style="text-align:left;">
                            <p><strong>Code:</strong> ${escapeHtml(c.courseCode || '-')}</p>
                            <p><strong>Type:</strong> ${escapeHtml(c.courseType)}</p>
                            <p><strong>Status:</strong> <span class="badge badge-${c.status == 'active' ? 'active' : (c.status == 'pending' ? 'pending' : 'draft')}">${c.status}</span></p>
                            <hr>
                            <strong>Description:</strong>
                            <p>${escapeHtml(c.description || 'None')}</p>
                            <hr>
                            <strong>Units:</strong>
                            ${subHtml}
                        </div>
                    `,
                    width: '600px'
                });
            }
        }, 'json');
    }

    function escapeHtml(text) {
        if (!text) return '';
        return $('<div>').text(text).html();
    }
</script>

<?php require 'includes/footer.php'; ?>