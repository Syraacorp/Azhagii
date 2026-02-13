<?php
$pageTitle   = 'Manage Colleges';
$currentPage = 'manageColleges';
require 'includes/auth.php';
requirePageRole('superAdmin');
require 'includes/header.php';
require 'includes/sidebar.php';
?>

<div class="section-toolbar">
    <div></div>
    <button class="btn btn-primary" onclick="showCollegeModal()"><i class="fas fa-plus"></i> Add College</button>
</div>
<div class="table-responsive">
    <table class="table">
        <thead><tr><th>#</th><th>Name</th><th>Code</th><th>City</th><th>Users</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody id="collegesBody"></tbody>
    </table>
</div>

<?php require 'includes/footer.php'; ?>
