<?php
$pageTitle = 'Course Approvals';
$currentPage = 'courseApprovalsSSR';
require 'includes/auth.php';
requirePageRole(['superAdmin', 'adminAzhagii']);

// 1. Fetch Stats
$stats = ['pending' => 0, 'active' => 0, 'rejected' => 0, 'enrollments' => 0];

// Count by status
$sq = "SELECT status, COUNT(*) as c FROM courses GROUP BY status";
$sr = mysqli_query($conn, $sq);
while ($sr && $row = mysqli_fetch_assoc($sr)) {
    if ($row['status'] == 'pending')
        $stats['pending'] = intval($row['c']);
    if ($row['status'] == 'active')
        $stats['active'] = intval($row['c']);
    if ($row['status'] == 'rejected')
        $stats['rejected'] = intval($row['c']);
}
$stats['courses'] = $stats['pending'] + $stats['active'] + $stats['rejected']; // Rough total or query DB

// Count enrollments
$er = mysqli_query($conn, "SELECT COUNT(*) as c FROM enrollments");
if ($row = mysqli_fetch_assoc($er))
    $stats['enrollments'] = intval($row['c']);

// 2. Fetch Pending Courses
$q = "SELECT c.*, u.name as creator_name, col.name as creator_college,
      (SELECT COUNT(*) FROM subjects WHERE courseId=c.id) as subject_count
      FROM courses c 
      LEFT JOIN users u ON c.createdBy=u.id
      LEFT JOIN colleges col ON u.collegeId=col.id
      WHERE c.status='pending'
      ORDER BY c.createdAt DESC";
$pendingCourses = [];
$pr = mysqli_query($conn, $q);
while ($pr && $row = mysqli_fetch_assoc($pr)) {
    $pendingCourses[] = $row;
}
require 'includes/header.php';
require 'includes/sidebar.php';

// Helper for Stats Card
function renderStatCard($icon, $title, $count, $color)
{
    return '
    <div class="dashboard-card" style="display:flex;align-items:center;gap:1rem;padding:1.25rem;">
        <div class="stat-icon" style="background:' . $color . '15;color:' . $color . ';width:50px;height:50px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;">
            <i class="fas ' . $icon . '"></i>
        </div>
        <div>
            <h3 style="margin:0;font-size:1.5rem;font-weight:700;">' . number_format($count) . '</h3>
            <p style="margin:0;font-size:0.85rem;color:var(--text-muted);">' . $title . '</p>
        </div>
    </div>';
}
?>

<!-- Approval Stats -->
<div class="stats-grid" id="approval-stats">
    <?= renderStatCard('fa-clock', 'Pending', $stats['pending'], '#fbbf24') ?>
    <?= renderStatCard('fa-book', 'Total Courses', $stats['courses'], '#4285f4') ?>
    <?= renderStatCard('fa-times-circle', 'Rejected', $stats['rejected'], '#f87171') ?>
    <?= renderStatCard('fa-clipboard-list', 'Enrollments', $stats['enrollments'], '#34d399') ?>
</div>

<!-- Tabs for Pending / All -->
<div class="approval-tabs" style="margin-bottom:1.5rem;">
    <button class="btn btn-primary btn-sm approval-tab active" data-tab="pending"
        onclick="switchApprovalTab('pending')"><i class="fas fa-clock"></i> Pending Approval</button>
    <button class="btn btn-outline btn-sm approval-tab" data-tab="all" onclick="switchApprovalTab('all')"><i
            class="fas fa-list"></i> All Courses</button>
    <button class="btn btn-outline btn-sm approval-tab" data-tab="rejected" onclick="switchApprovalTab('rejected')"><i
            class="fas fa-times-circle"></i> Rejected</button>
</div>

<!-- Pending Courses Table -->
<div id="approvalPendingTab">
    <div class="table-responsive">
        <table class="table" id="pendingCoursesTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Course</th>
                    <th>Code</th>
                    <th>Submitted By</th>
                    <th>College</th>
                    <th>Semester</th>
                    <th>Subjects</th>
                    <th>Syllabus</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="pendingCoursesBody">
                <?php if (empty($pendingCourses)): ?>
                    <!-- DataTables will handle empty state -->
                <?php else: ?>
                    <?php foreach ($pendingCourses as $i => $c):
                        ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= htmlspecialchars($c['title']) ?></td>
                            <td><?= htmlspecialchars($c['courseCode'] ?: '-') ?></td>
                            <td><?= htmlspecialchars($c['creator_name'] ?: '-') ?></td>
                            <td><?= htmlspecialchars($c['creator_college'] ?: '-') ?></td>
                            <td><?= htmlspecialchars($c['semester'] ?: '-') ?></td>
                            <td><?= intval($c['subject_count']) ?></td>
                            <td>
                                <?php if (!empty($c['syllabus'])): ?>
                                    <a href="<?= htmlspecialchars($c['syllabus']) ?>" target="_blank"
                                        class="btn btn-outline btn-sm"><i class="fas fa-file-pdf"></i></a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td><?= date('M d, Y', strtotime($c['createdAt'])) ?></td>
                            <td class="actions" style="white-space:nowrap;">
                                <button class="btn btn-outline btn-sm" onclick="viewCourseDetail(<?= $c['id'] ?>)"
                                    title="View Details"><i class="fas fa-eye"></i></button>
                                <button class="btn btn-success btn-sm" onclick="approveCourse(<?= $c['id'] ?>)"
                                    title="Approve"><i class="fas fa-check"></i></button>
                                <button class="btn btn-danger btn-sm" onclick="rejectCourse(<?= $c['id'] ?>)" title="Reject"><i
                                        class="fas fa-times"></i></button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- All Courses Table -->
<div id="approvalAllTab" style="display:none;">
    <div class="section-toolbar">
        <div class="filter-bar">
            <select id="approvalStatusFilter" class="form-input form-input-sm" onchange="loadAllCoursesForApproval()">
                <option value="">All Statuses</option>
                <option value="active">Approved/Active</option>
                <option value="pending">Pending</option>
                <option value="rejected">Rejected</option>
                <option value="draft">Draft</option>
                <option value="archived">Archived</option>
            </select>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table" id="allCoursesApprovalTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Course</th>
                    <th>Code</th>
                    <th>Created By</th>
                    <th>Status</th>
                    <th>Semester</th>
                    <th>Syllabus</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="allCoursesApprovalBody"></tbody>
        </table>
    </div>
</div>

<!-- Rejected Courses Table -->
<div id="approvalRejectedTab" style="display:none;">
    <div class="table-responsive">
        <table class="table" id="rejectedCoursesTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Course</th>
                    <th>Code</th>
                    <th>Submitted By</th>
                    <th>Rejection Reason</th>
                    <th>Rejected By</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody id="rejectedCoursesBody"></tbody>
        </table>
    </div>
</div>


<?php require 'includes/footer.php'; ?>

<script>
    $(document).ready(function () {
        $('#pendingCoursesTable').DataTable({
            paging: true, searching: true, info: true, ordering: true,
            language: { search: '', searchPlaceholder: 'Search...' }
        });
    });

    function switchApprovalTab(tabName) {
        $('.approval-tab').removeClass('active').addClass('btn-outline').removeClass('btn-primary');
        $(`.approval-tab[data-tab="${tabName}"]`).addClass('active').removeClass('btn-outline').addClass('btn-primary');

        $('#approvalPendingTab, #approvalAllTab, #approvalRejectedTab').hide();

        if (tabName === 'pending') {
            $('#approvalPendingTab').fadeIn();
        } else if (tabName === 'all') {
            $('#approvalAllTab').fadeIn();
            loadAllCoursesForApproval();
        } else if (tabName === 'rejected') {
            $('#approvalRejectedTab').fadeIn();
            loadRejectedCourses();
        }
    }

    function loadAllCoursesForApproval() {
        const filter = $('#approvalStatusFilter').val();
        const tbody = $('#allCoursesApprovalBody');
        tbody.html('<tr><td colspan="9" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>');

        $.post('backend.php', { get_all_courses_approval: 1, filter: filter }, function (res) {
            if (res.status === 200) {
                if ($.fn.DataTable.isDataTable('#allCoursesApprovalTable')) {
                    $('#allCoursesApprovalTable').DataTable().destroy();
                }

                let html = '';
                if (res.data.length === 0) {
                    html = '<tr><td colspan="9" class="empty-state">No courses found</td></tr>';
                } else {
                    res.data.forEach((c, i) => {
                        let syllabusBtn = c.syllabus ? `<a href="${c.syllabus}" target="_blank" class="btn btn-outline btn-sm"><i class="fas fa-file-pdf"></i></a>` : '-';
                        html += `<tr>
                            <td>${i + 1}</td>
                            <td>${escapeHtml(c.title)}</td>
                            <td>${escapeHtml(c.courseCode || '-')}</td>
                            <td>${escapeHtml(c.creator_name || 'System')}</td>
                            <td><span class="badge badge-${c.status === 'active' ? 'active' : (c.status === 'rejected' ? 'rejected' : 'draft')}">${c.status}</span></td>
                            <td>${escapeHtml(c.semester || '-')}</td>
                            <td>${syllabusBtn}</td>
                            <td>${new Date(c.createdAt).toLocaleDateString()}</td>
                            <td class="actions">
                                <button class="btn btn-outline btn-sm" onclick="viewCourseDetail(${c.id})"><i class="fas fa-eye"></i></button>
                                ${c.status === 'pending' ? `<button class="btn btn-success btn-sm" onclick="approveCourse(${c.id})"><i class="fas fa-check"></i></button>
                                <button class="btn btn-danger btn-sm" onclick="rejectCourse(${c.id})"><i class="fas fa-times"></i></button>` : ''}
                            </td>
                        </tr>`;
                    });
                }
                tbody.html(html);

                $('#allCoursesApprovalTable').DataTable({
                    paging: true, searching: true, info: true, ordering: true,
                    language: { search: '', searchPlaceholder: 'Search...' }
                });
            } else {
                tbody.html(`<tr><td colspan="9" class="text-center text-danger">Error: ${res.message}</td></tr>`);
            }
        }, 'json');
    }

    function loadRejectedCourses() {
        const tbody = $('#rejectedCoursesBody');
        tbody.html('<tr><td colspan="7" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>');

        $.post('backend.php', { get_rejected_courses: 1 }, function (res) {
            if (res.status === 200) {
                if ($.fn.DataTable.isDataTable('#rejectedCoursesTable')) {
                    $('#rejectedCoursesTable').DataTable().destroy();
                }

                let html = '';
                if (res.data.length === 0) {
                    html = '<tr><td colspan="7" class="empty-state">No rejected courses</td></tr>';
                } else {
                    res.data.forEach((c, i) => {
                        html += `<tr>
                            <td>${i + 1}</td>
                            <td>${escapeHtml(c.title)}</td>
                            <td>${escapeHtml(c.courseCode || '-')}</td>
                            <td>${escapeHtml(c.creator_name || '-')}</td>
                            <td class="text-danger">${escapeHtml(c.rejectionReason || 'No reason')}</td>
                            <td>${escapeHtml(c.rejector_name || '-')}</td>
                            <td>${new Date(c.updatedAt).toLocaleDateString()}</td>
                        </tr>`;
                    });
                }
                tbody.html(html);

                $('#rejectedCoursesTable').DataTable({
                    paging: true, searching: true, info: true, ordering: true,
                    language: { search: '', searchPlaceholder: 'Search...' }
                });
            }
        }, 'json');
    }

    function viewCourseDetail(id) {
        $.post('backend.php', { get_course_detail: 1, courseId: id }, function (res) {
            if (res.status !== 200) { Swal.fire('Error', 'Could not fetch details', 'error'); return; }
            const c = res.data;
            Swal.fire({
                title: c.title,
                html: `
                    <div class="text-left">
                        <p><strong>Code:</strong> ${escapeHtml(c.courseCode)}</p>
                        <p><strong>Category:</strong> ${escapeHtml(c.category)}</p>
                        <p><strong>Semester:</strong> ${c.semester}</p>
                        <p><strong>Description:</strong><br>${escapeHtml(c.description)}</p>
                        ${c.syllabus ? `<p><a href="${c.syllabus}" target="_blank" class="btn btn-sm btn-outline"><i class="fas fa-file-pdf"></i> View Syllabus</a></p>` : ''}
                    </div>
                `,
                width: '600px',
                showCloseButton: true
            });
        }, 'json');
    }

    function approveCourse(id) {
        Swal.fire({
            title: 'Approve Course?',
            text: "This course will become active and visible to colleges.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            confirmButtonText: 'Yes, Approve'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('backend.php', { approve_course: 1, courseId: id }, function (res) {
                    if (res.status === 200) {
                        Swal.fire('Approved!', res.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                }, 'json');
            }
        });
    }

    function rejectCourse(id) {
        Swal.fire({
            title: 'Reject Course',
            input: 'textarea',
            inputLabel: 'Reason for rejection',
            inputPlaceholder: 'Enter reason...',
            inputAttributes: { 'aria-label': 'Reason for rejection' },
            showCancelButton: true,
            confirmButtonText: 'Reject',
            confirmButtonColor: '#ef4444',
            preConfirm: (reason) => {
                if (!reason) { Swal.showValidationMessage('Reason is required'); }
                return reason;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('backend.php', { reject_course: 1, courseId: id, reason: result.value }, function (res) {
                    if (res.status === 200) {
                        Swal.fire('Rejected!', res.message, 'success').then(() => location.reload());
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