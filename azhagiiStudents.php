<?php
$pageTitle = 'Azhagii Students';
$currentPage = 'azhagiiStudentsSSR';
require 'includes/auth.php';
requirePageRole('superAdmin');

// Fetch Colleges for filter
$colleges = [];
$cr = mysqli_query($conn, "SELECT id, name FROM colleges ORDER BY name ASC");
while ($row = mysqli_fetch_assoc($cr)) {
    $colleges[] = $row;
}

// Fetch Students (role=azhagiiStudents)
// Note: This matches the default load of loadAzhagiiStudents()
$students = [];
$q = "SELECT u.*, c.name as college_name 
      FROM users u 
      LEFT JOIN colleges c ON u.collegeId=c.id 
      WHERE u.role='azhagiiStudents' 
      ORDER BY u.name ASC";
$r = mysqli_query($conn, $q);
while ($r && $row = mysqli_fetch_assoc($r)) {
    $students[] = $row;
}

require 'includes/header.php';
require 'includes/sidebar.php';
?>

<div class="section-toolbar">
    <div class="filter-bar">
        <select id="studentCollegeFilter" class="form-input form-input-sm" onchange="filterAzhagiiStudentsSSR()">
            <option value="">All Colleges</option>
            <?php foreach ($colleges as $c): ?>
                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>
<div class="table-responsive">
    <table class="table" id="azhagiiStudentsTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Photo</th>
                <th>Name</th>
                <th>Username</th>
                <th>Azhagii ID</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Gender</th>
                <th>DOB</th>
                <th>College</th>
                <th>Department</th>
                <th>Year</th>
                <th>Roll Number</th>
                <th>Bio</th>
                <th>Address</th>
                <th>Status</th>
                <th>Locked</th>
                <th>Joined</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="azhagiiStudentsBody">
            <?php if (empty($students)): ?>
                <tr>
                    <td colspan="19" class="empty-state"><i class="fas fa-user-graduate"></i>
                        <p>No students found</p>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($students as $i => $u):
                    $photoHtml = !empty($u['profilePhoto'])
                        ? '<img src="' . htmlspecialchars($u['profilePhoto']) . '" alt="Photo" style="width:36px;height:36px;border-radius:50%;object-fit:cover;">'
                        : '<div class="avatar-circle" style="width:36px;height:36px;font-size:0.85rem;">' . strtoupper(substr($u['name'], 0, 1)) . '</div>';
                    $isLocked = ($u['isLocked'] == 1);
                    ?>
                    <tr data-college-id="<?= $u['collegeId'] ?>">
                        <td><?= $i + 1 ?></td>
                        <td><?= $photoHtml ?></td>
                        <td><?= htmlspecialchars($u['name']) ?></td>
                        <td><?= htmlspecialchars($u['username']) ?></td>
                        <td><?= htmlspecialchars($u['azhagiiID'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= htmlspecialchars($u['phone'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($u['gender'] ?? '-') ?></td>
                        <td><?= $u['dob'] ? date('M d, Y', strtotime($u['dob'])) : '-' ?></td>
                        <td><?= htmlspecialchars($u['college_name'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($u['department'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($u['year'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($u['rollNumber'] ?? '-') ?></td>
                        <td class="cell-wrap"><?= htmlspecialchars($u['bio'] ?? '-') ?></td>
                        <td class="cell-wrap"><?= htmlspecialchars($u['address'] ?? '-') ?></td>
                        <td><span
                                class="badge badge-<?= ($u['status'] == 'active' ? 'active' : 'inactive') ?>"><?= htmlspecialchars($u['status']) ?></span>
                        </td>
                        <td>
                            <?php if ($isLocked): ?>
                                <span class="badge badge-inactive">Locked</span>
                            <?php else: ?>
                                <span class="badge badge-active">No</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('M d, Y', strtotime($u['createdAt'])) ?></td>
                        <td class="actions">
                            <button class="btn btn-outline btn-sm" onclick="editAzhagiiStudent(<?= $u['id'] ?>)"><i
                                    class="fas fa-edit"></i></button>
                            <button class="btn btn-danger btn-sm" onclick="deleteAzhagiiStudent(<?= $u['id'] ?>)"><i
                                    class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>


<script>
    let table;

    // Custom filtering function which searches data in column "CollegeId" (hidden or via attribute)
    $.fn.dataTable.ext.search.push(
        function (settings, data, dataIndex) {
            var filterId = $('#studentCollegeFilter').val();
            if (!filterId) return true; // No filter selected

            // We use the data-college-id attribute from the TR
            var rowCollegeId = $(settings.aoData[dataIndex].nTr).attr('data-college-id');
            return rowCollegeId == filterId;
        }
    );

    $(document).ready(function () {
        table = $('#azhagiiStudentsTable').DataTable({
            // Columns: 0=#, 1=Photo, 9=College Name. 
            // We need to ensure we don't break column indices.
            columnDefs: [
                { orderable: false, targets: [1, 18] },
                // Hiding many columns by default to keep view clean
                { visible: false, targets: [4, 5, 6, 7, 8, 13, 14, 15, 16, 17] }
            ],
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print', 'colvis'
            ]
        });
    });

    function filterAzhagiiStudentsSSR() {
        table.draw();
    }

    function editAzhagiiStudent(id) {
        $.post('backend.php', { get_user_detail: 1, userId: id }, function (res) {
            if (res.status !== 200) { Swal.fire('Error', 'Could not fetch student', 'error'); return; }
            const u = res.data;

            // College options for select
            let collegeOpts = '<option value="">Select College</option>';
            // We can use the global PHP colleges array if we json_encode it, 
            // but here I'll just use the current college name as display or fetch strictly if needed.
            // Better: fetch colleges list again or print it in PHP.
            // I'll grab it from the dropdown on the page!
            $('#studentCollegeFilter option').each(function () {
                if ($(this).val()) {
                    collegeOpts += `<option value="${$(this).val()}" ${u.collegeId == $(this).val() ? 'selected' : ''}>${$(this).text()}</option>`;
                }
            });

            Swal.fire({
                title: 'Edit Student',
                html: `
                    <div class="swal-form text-left" style="height:400px;overflow-y:auto;">
                        <input type="hidden" id="sw-stId" value="${u.id}">
                        <div class="form-group"><label>Full Name</label><input id="sw-stName" class="form-input" value="${escapeHtml(u.name)}"></div>
                        <div class="form-group"><label>Email</label><input id="sw-stEmail" class="form-input" value="${escapeHtml(u.email)}"></div>
                        <div class="form-group"><label>College</label><select id="sw-stCollege" class="form-input">${collegeOpts}</select></div>
                        <div class="form-group"><label>Department</label><input id="sw-stDept" class="form-input" value="${escapeHtml(u.department || '')}"></div>
                        <div class="form-group"><label>Year</label><input id="sw-stYear" class="form-input" value="${escapeHtml(u.year || '')}"></div>
                        <div class="form-group"><label>Roll Number</label><input id="sw-stRoll" class="form-input" value="${escapeHtml(u.rollNumber || '')}"></div>
                        <div class="form-group"><label>Phone</label><input id="sw-stPhone" class="form-input" value="${escapeHtml(u.phone || '')}"></div>
                        <div class="form-group"><label>Status</label>
                            <select id="sw-stStatus" class="form-input">
                                <option value="active" ${u.status === 'active' ? 'selected' : ''}>Active</option>
                                <option value="inactive" ${u.status === 'inactive' ? 'selected' : ''}>Inactive</option>
                            </select>
                        </div>
                    </div>
                `,
                width: '600px',
                showCancelButton: true,
                confirmButtonText: 'Update',
                preConfirm: () => {
                    return {
                        id: $('#sw-stId').val(),
                        name: $('#sw-stName').val(),
                        email: $('#sw-stEmail').val(),
                        collegeId: $('#sw-stCollege').val(),
                        department: $('#sw-stDept').val(),
                        year: $('#sw-stYear').val(),
                        rollNumber: $('#sw-stRoll').val(),
                        phone: $('#sw-stPhone').val(),
                        status: $('#sw-stStatus').val()
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const d = result.value;
                    // We use update_user from manageUsers logic (backend support)
                    // But we have extra fields (dept, year, roll). update_user in backend.php supports them!
                    $.post('backend.php', {
                        update_user: 1,
                        id: d.id,
                        name: d.name,
                        email: d.email,
                        role: 'azhagiiStudents', // Force role or keep existing
                        collegeId: d.collegeId,
                        department: d.department,
                        year: d.year,
                        rollNumber: d.rollNumber,
                        phone: d.phone,
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

    function deleteAzhagiiStudent(id) {
        Swal.fire({
            title: 'Delete Student?',
            text: "This cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('backend.php', { delete_user: 1, id: id }, function (res) {
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

<?php require 'includes/footer.php'; ?>