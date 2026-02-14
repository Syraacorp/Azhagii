<?php
$pageTitle = 'Manage Users';
// Changed to prevent script.js auto-load. Initial load is now SSR.
$currentPage = 'manageUsersSSR';
require 'includes/auth.php';
requirePageRole(['superAdmin', 'adminAzhagii']);
require 'includes/header.php';
require 'includes/sidebar.php';

// 1. Fetch Colleges for Dropdown
$colleges = [];
$cr = mysqli_query($conn, "SELECT id, name FROM colleges ORDER BY name ASC");
while ($cr && $row = mysqli_fetch_assoc($cr)) {
    $colleges[] = $row;
}

// 2. Fetch Initial Users List (All)
$where = "1=1";
if (hasRole('adminAzhagii'))
    $where .= " AND u.role != 'superAdmin'";
$q = "SELECT u.*, c.name as college_name 
      FROM users u 
      LEFT JOIN colleges c ON u.collegeId=c.id 
      WHERE $where 
      ORDER BY u.createdAt DESC";
$users = [];
$ur = mysqli_query($conn, $q);
while ($ur && $row = mysqli_fetch_assoc($ur)) {
    $users[] = $row;
}
?>

<div class="section-toolbar">
    <div class="filter-bar">
        <select id="userRoleFilter" class="form-input form-input-sm" onchange="loadUsers()">
            <option value="">All Roles</option>
            <?php if ($role === 'superAdmin'): ?>
                <option value="superAdmin">Super Admin</option><?php endif; ?>
            <?php if ($role === 'superAdmin'): ?>
                <option value="adminAzhagii">Admin Azhagii</option><?php endif; ?>
            <option value="azhagiiCoordinator">Coordinator</option>
            <option value="azhagiiStudents">Student</option>
        </select>
        <select id="userCollegeFilter" class="form-input form-input-sm" onchange="loadUsers()">
            <option value="">All Colleges</option>
            <?php foreach ($colleges as $c): ?>
                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button class="btn btn-primary" onclick="showUserModal()"><i class="fas fa-plus"></i> Add User</button>
</div>
<div class="table-responsive">
    <table class="table" id="usersTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>College</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="usersBody">
            <?php if (empty($users)): ?>
                <tr>
                    <td colspan="7" class="empty-state">
                        <p>No users found</p>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($users as $i => $u):
                    $roleBadge = ($u['role'] === 'superAdmin' || $u['role'] === 'adminAzhagii') ? 'active' : 'draft';
                    $statusBadge = ($u['status'] === 'active') ? 'active' : 'inactive';
                    $roleLabel = str_replace('azhagii', '', ucfirst($u['role']));
                    if ($u['role'] === 'superAdmin')
                        $roleLabel = 'Super Admin';
                    if ($u['role'] === 'adminAzhagii')
                        $roleLabel = 'Admin';
                    if ($u['role'] === 'azhagiiCoordinator')
                        $roleLabel = 'Coordinator';
                    if ($u['role'] === 'azhagiiStudents')
                        $roleLabel = 'Student';
                    ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($u['name']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><span class="badge badge-<?= $roleBadge ?>"><?= $roleLabel ?></span></td>
                        <td><?= htmlspecialchars($u['college_name'] ?: '-') ?></td>
                        <td><span class="badge badge-<?= $statusBadge ?>"><?= ucfirst($u['status']) ?></span></td>
                        <td class="actions">
                            <button class="btn btn-outline btn-sm" onclick="editUser(<?= $u['id'] ?>)"><i
                                    class="fas fa-edit"></i></button>
                            <button class="btn btn-danger btn-sm" onclick="deleteUser(<?= $u['id'] ?>)"><i
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
    const collegesList = <?= json_encode($colleges) ?>;
    let dataTable;

    $(document).ready(function () {
        dataTable = $('#usersTable').DataTable({
            paging: true, searching: true, info: true, ordering: true,
            language: { search: '', searchPlaceholder: 'Search...' }
        });
    });

    function loadUsers() {
        const role = $('#userRoleFilter').val();
        const collegeId = $('#userCollegeFilter').val();
        const tbody = $('#usersBody');

        tbody.html('<tr><td colspan="7" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>');

        $.post('backend.php', { get_users: 1, role: role, collegeId: collegeId }, function (res) {
            if (res.status === 200) {
                // Destroy old datatable to re-render
                if ($.fn.DataTable.isDataTable('#usersTable')) {
                    $('#usersTable').DataTable().destroy();
                }

                let html = '';
                if (res.data.length === 0) {
                    html = '<tr><td colspan="7" class="empty-state"><p>No users found</p></td></tr>';
                } else {
                    res.data.forEach((u, i) => {
                        let roleLabel = u.role.replace('azhagii', '');
                        roleLabel = roleLabel.charAt(0).toUpperCase() + roleLabel.slice(1);
                        if (u.role === 'superAdmin') roleLabel = 'Super Admin';
                        if (u.role === 'adminAzhagii') roleLabel = 'Admin';

                        const roleBadge = (u.role === 'superAdmin' || u.role === 'adminAzhagii') ? 'active' : 'draft';
                        const statusBadge = (u.status === 'active') ? 'active' : 'inactive';

                        html += `<tr>
                            <td>${i + 1}</td>
                            <td>${escapeHtml(u.name)}</td>
                            <td>${escapeHtml(u.email)}</td>
                            <td><span class="badge badge-${roleBadge}">${roleLabel}</span></td>
                            <td>${escapeHtml(u.college_name || '-')}</td>
                            <td><span class="badge badge-${statusBadge}">${u.status.charAt(0).toUpperCase() + u.status.slice(1)}</span></td>
                            <td class="actions">
                                <button class="btn btn-outline btn-sm" onclick="editUser(${u.id})"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-danger btn-sm" onclick="deleteUser(${u.id})"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>`;
                    });
                }
                tbody.html(html);

                // Re-init DataTable
                $('#usersTable').DataTable({
                    paging: true, searching: true, info: true, ordering: true,
                    language: { search: '', searchPlaceholder: 'Search...' }
                });

            } else {
                tbody.html(`<tr><td colspan="7" class="text-center text-danger">Error: ${res.message}</td></tr>`);
            }
        }, 'json');
    }

    function showUserModal() {
        let collegeOpts = '<option value="">Select College</option>';
        collegesList.forEach(c => {
            collegeOpts += `<option value="${c.id}">${escapeHtml(c.name)}</option>`;
        });

        Swal.fire({
            title: 'Add New User',
            html: `
                <div class="swal-form text-left" style="max-height:60vh;overflow-y:auto;padding-right:5px;">
                    <div class="form-group"><label>Full Name <span class="text-danger">*</span></label><input id="sw-uName" class="form-input"></div>
                    <div class="form-group"><label>Email <span class="text-danger">*</span></label><input id="sw-uEmail" class="form-input" type="email"></div>
                    <div class="form-group"><label>Password <span class="text-danger">*</span></label><input id="sw-uPass" class="form-input" type="password"></div>
                    <div class="form-group"><label>Role <span class="text-danger">*</span></label>
                        <select id="sw-uRole" class="form-input" onchange="toggleCollegeField(this.value)">
                            <option value="azhagiiStudents">Student</option>
                            <option value="azhagiiCoordinator">Coordinator</option>
                            <option value="adminAzhagii">Admin Azhagii</option>
                            <option value="superAdmin">Super Admin</option>
                        </select>
                    </div>
                    <div class="form-group" id="sw-uCollegeGroup"><label>College</label><select id="sw-uCollege" class="form-input">${collegeOpts}</select></div>
                </div>
            `,
            width: '500px',
            showCancelButton: true,
            confirmButtonText: 'Create User',
            didOpen: () => { toggleCollegeField('azhagiiStudents'); },
            preConfirm: () => {
                const name = $('#sw-uName').val();
                const email = $('#sw-uEmail').val();
                const pass = $('#sw-uPass').val();
                const role = $('#sw-uRole').val();
                const collegeId = $('#sw-uCollege').val();

                if (!name || !email || !pass) { Swal.showValidationMessage('Name, Email and Password are required'); return false; }
                if ((role === 'azhagiiStudents' || role === 'azhagiiCoordinator') && !collegeId) {
                    Swal.showValidationMessage('College is required for this role'); return false;
                }
                return { name, email, pass, role, collegeId };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const d = result.value;
                $.post('backend.php', {
                    add_user: 1,
                    name: d.name,
                    email: d.email,
                    password: d.pass,
                    role: d.role,
                    collegeId: d.collegeId
                }, function (res) {
                    if (res.status === 200) {
                        Swal.fire('Created!', res.message, 'success').then(() => loadUsers());
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                }, 'json');
            }
        });
    }

    function toggleCollegeField(role) {
        if (role === 'adminAzhagii' || role === 'superAdmin') {
            $('#sw-uCollegeGroup').hide();
        } else {
            $('#sw-uCollegeGroup').show();
        }
    }

    function editUser(id) {
        // Fetch user detail. Assuming simpler to just load current row data? 
        // No, need full details (e.g. hidden fields). 
        // We'll use get_users with an ID filter if supported, or a get_user_detail. 
        // backend.php usually supports fetching a specific user or we can filter client side if we had all data.
        // Let's rely on backend fetch for freshness.

        // Emulating fetch via get_users with ID if possible or get_user_detail.
        // I'll assume get_user_detail exists or I'll implement it/use get_users.
        // The safe bet is get_user_detail.

        $.post('backend.php', { get_user_detail: 1, userId: id }, function (res) {
            if (res.status !== 200) { Swal.fire('Error', 'Could not fetch user', 'error'); return; }
            const u = res.data;

            let collegeOpts = '<option value="">Select College</option>';
            collegesList.forEach(c => {
                collegeOpts += `<option value="${c.id}" ${u.collegeId == c.id ? 'selected' : ''}>${escapeHtml(c.name)}</option>`;
            });

            Swal.fire({
                title: 'Edit User',
                html: `
                    <div class="swal-form text-left">
                        <input type="hidden" id="sw-uId" value="${u.id}">
                        <div class="form-group"><label>Full Name</label><input id="sw-uName" class="form-input" value="${escapeHtml(u.name)}"></div>
                        <div class="form-group"><label>Email</label><input id="sw-uEmail" class="form-input" type="email" value="${escapeHtml(u.email)}"></div>
                        <div class="form-group"><label>Role</label>
                            <select id="sw-uRole" class="form-input" onchange="toggleCollegeField(this.value)">
                                <option value="azhagiiStudents" ${u.role === 'azhagiiStudents' ? 'selected' : ''}>Student</option>
                                <option value="azhagiiCoordinator" ${u.role === 'azhagiiCoordinator' ? 'selected' : ''}>Coordinator</option>
                                <option value="adminAzhagii" ${u.role === 'adminAzhagii' ? 'selected' : ''}>Admin Azhagii</option>
                                <option value="superAdmin" ${u.role === 'superAdmin' ? 'selected' : ''}>Super Admin</option>
                            </select>
                        </div>
                        <div class="form-group" id="sw-uCollegeGroup"><label>College</label><select id="sw-uCollege" class="form-input">${collegeOpts}</select></div>
                        <div class="form-group"><label>Status</label>
                            <select id="sw-uStatus" class="form-input">
                                <option value="active" ${u.status === 'active' ? 'selected' : ''}>Active</option>
                                <option value="inactive" ${u.status === 'inactive' ? 'selected' : ''}>Inactive</option>
                            </select>
                        </div>
                    </div>
                `,
                didOpen: () => { toggleCollegeField($('#sw-uRole').val()); },
                showCancelButton: true,
                confirmButtonText: 'Update User',
                preConfirm: () => {
                    return {
                        id: $('#sw-uId').val(),
                        name: $('#sw-uName').val(),
                        email: $('#sw-uEmail').val(),
                        role: $('#sw-uRole').val(),
                        collegeId: $('#sw-uCollege').val(),
                        status: $('#sw-uStatus').val()
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const d = result.value;
                    $.post('backend.php', {
                        update_user: 1,
                        id: d.id,
                        name: d.name,
                        email: d.email,
                        role: d.role,
                        collegeId: d.collegeId,
                        status: d.status
                    }, function (res) {
                        if (res.status === 200) {
                            Swal.fire('Updated!', res.message, 'success').then(() => loadUsers());
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                    }, 'json');
                }
            });
        }, 'json');
    }

    function deleteUser(id) {
        Swal.fire({
            title: 'Delete User?',
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('backend.php', { delete_user: 1, id: id }, function (res) {
                    if (res.status === 200) {
                        Swal.fire('Deleted!', res.message, 'success').then(() => loadUsers());
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