<?php
$pageTitle   = 'Manage Users';
$currentPage = 'manageUsers';
require 'includes/auth.php';
requirePageRole(['superAdmin', 'adminAzhagii']);
require 'includes/header.php';
require 'includes/sidebar.php';
?>

<div class="section-toolbar">
    <div class="filter-bar">
        <select id="userRoleFilter" class="form-input form-input-sm" onchange="loadUsers()">
            <option value="">All Roles</option>
            <?php if ($role === 'superAdmin'): ?><option value="superAdmin">Super Admin</option><?php endif; ?>
            <?php if ($role === 'superAdmin'): ?><option value="adminAzhagii">Admin Azhagii</option><?php endif; ?>
            <option value="azhagiiCoordinator">Coordinator</option>
            <option value="azhagiiStudents">Student</option>
        </select>
        <select id="userCollegeFilter" class="form-input form-input-sm" onchange="loadUsers()">
            <option value="">All Colleges</option>
        </select>
    </div>
    <button class="btn btn-primary" onclick="showUserModal()"><i class="fas fa-plus"></i> Add User</button>
</div>
<div class="table-responsive">
    <table class="table">
        <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Role</th><th>College</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody id="usersBody"></tbody>
    </table>
</div>

<?php require 'includes/footer.php'; ?>
