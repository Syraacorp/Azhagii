<?php
$pageTitle = 'Manage Colleges';
// Prevent script.js auto-load
$currentPage = 'manageCollegesSSR';
require 'includes/auth.php';
requirePageRole('superAdmin');

// Fetch Colleges
$q = "SELECT c.*, (SELECT COUNT(*) FROM users WHERE collegeId=c.id) as user_count 
      FROM colleges c 
      ORDER BY c.name ASC";
$colleges = [];
$r = mysqli_query($conn, $q);
while ($r && $row = mysqli_fetch_assoc($r)) {
    $colleges[] = $row;
}
require 'includes/header.php';
require 'includes/sidebar.php';
?>

<div class="section-toolbar">
    <div></div>
    <button class="btn btn-primary" onclick="showCollegeModal()"><i class="fas fa-plus"></i> Add College</button>
</div>
<div class="table-responsive">
    <table class="table" id="collegesTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Code</th>
                <th>City</th>
                <th>Users</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="collegesBody">
            <?php if (empty($colleges)): ?>
                <tr>
                    <td colspan="7" class="empty-state">
                        <p>No colleges found</p>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($colleges as $i => $c):
                    $status = ucfirst($c['status']);
                    $badge = ($c['status'] === 'active') ? 'active' : 'inactive';
                    $user_count = intval($c['user_count']);
                    ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($c['name']) ?></td>
                        <td><span class="badge badge-draft"><?= htmlspecialchars($c['code']) ?></span></td>
                        <td><?= htmlspecialchars($c['city']) ?></td>
                        <td><?= $user_count ?></td>
                        <td><span class="badge badge-<?= $badge ?>"><?= $status ?></span></td>
                        <td class="actions">
                            <button class="btn btn-outline btn-sm" onclick="editCollege(<?= $c['id'] ?>)"><i
                                    class="fas fa-edit"></i></button>
                            <button class="btn btn-danger btn-sm" onclick="deleteCollege(<?= $c['id'] ?>)"><i
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
        $('#collegesTable').DataTable({
            paging: true, searching: true, info: true, ordering: true,
            language: { search: '', searchPlaceholder: 'Search...' }
        });
    });

    function showCollegeModal() {
        Swal.fire({
            title: 'Add New College',
            html: `
                <div class="swal-form text-left">
                    <div class="form-group"><label>College Name <span class="text-danger">*</span></label><input id="sw-cName" class="form-input"></div>
                    <div class="form-group"><label>Code <span class="text-danger">*</span></label><input id="sw-cCode" class="form-input"></div>
                    <div class="form-group"><label>City</label><input id="sw-cCity" class="form-input"></div>
                    <div class="form-group"><label>Address</label><textarea id="sw-cAddr" class="form-input" rows="2"></textarea></div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Add College',
            preConfirm: () => {
                const name = $('#sw-cName').val();
                const code = $('#sw-cCode').val();
                if (!name || !code) { Swal.showValidationMessage('Name and Code are required'); return false; }
                return {
                    name: name,
                    code: code,
                    city: $('#sw-cCity').val(),
                    address: $('#sw-cAddr').val()
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const d = result.value;
                $.post('backend.php', {
                    add_college: 1,
                    name: d.name,
                    code: d.code,
                    city: d.city,
                    address: d.address
                }, function (res) {
                    if (res.status === 200) {
                        Swal.fire('Added!', res.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                }, 'json');
            }
        });
    }

    function editCollege(id) {
        // Fetch college details first? Or just render with existing if passed? 
        // For simplicity and correctness, let's fetch or use row data. 
        // Since we are moving away from global object cache, let's fetch.
        // Or we can just use the data if we had it. But backend fetch is safer.

        // Actually, we don't have a 'get_college_detail' endpoint easily visible 
        // but we have 'get_colleges'. Let's assume we can fetch all or just iterate.
        // Better: let's try to get it from the table row or just make a quick backend call if endpoint exists.
        // We implemented 'get_colleges' in backend.php.

        // Let's implement a simple fetch from backend 'get_colleges' and find the ID.
        $.post('backend.php', { get_colleges: 1 }, function (res) {
            if (res.status === 200) {
                const college = res.data.find(c => c.id == id);
                if (!college) { Swal.fire('Error', 'College not found', 'error'); return; }

                Swal.fire({
                    title: 'Edit College',
                    html: `
                        <div class="swal-form text-left">
                            <div class="form-group"><label>College Name <span class="text-danger">*</span></label><input id="sw-cName" class="form-input" value="${escapeHtml(college.name)}"></div>
                            <div class="form-group"><label>Code <span class="text-danger">*</span></label><input id="sw-cCode" class="form-input" value="${escapeHtml(college.code)}"></div>
                            <div class="form-group"><label>City</label><input id="sw-cCity" class="form-input" value="${escapeHtml(college.city)}"></div>
                            <div class="form-group"><label>Address</label><textarea id="sw-cAddr" class="form-input" rows="2">${escapeHtml(college.address)}</textarea></div>
                            <div class="form-group"><label>Status</label>
                                <select id="sw-cStatus" class="form-input">
                                    <option value="active" ${college.status === 'active' ? 'selected' : ''}>Active</option>
                                    <option value="inactive" ${college.status === 'inactive' ? 'selected' : ''}>Inactive</option>
                                </select>
                            </div>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Update College',
                    preConfirm: () => {
                        const name = $('#sw-cName').val();
                        const code = $('#sw-cCode').val();
                        if (!name || !code) { Swal.showValidationMessage('Name and Code are required'); return false; }
                        return {
                            id: id,
                            name: name,
                            code: code,
                            city: $('#sw-cCity').val(),
                            address: $('#sw-cAddr').val(),
                            status: $('#sw-cStatus').val()
                        };
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const d = result.value;
                        $.post('backend.php', {
                            update_college: 1,
                            id: d.id,
                            name: d.name,
                            code: d.code,
                            city: d.city,
                            address: d.address,
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
            }
        }, 'json');
    }

    function deleteCollege(id) {
        Swal.fire({
            title: 'Delete College?',
            text: "This will remove the college and might affect associated users!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('backend.php', { delete_college: 1, id: id }, function (res) {
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