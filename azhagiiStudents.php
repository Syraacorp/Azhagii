<?php
$pageTitle   = 'Azhagii Students';
$currentPage = 'azhagiiStudents';
require 'includes/auth.php';
requirePageRole('superAdmin');
require 'includes/header.php';
require 'includes/sidebar.php';
?>

<div class="section-toolbar">
    <div class="filter-bar">
        <select id="studentCollegeFilter" class="form-input form-input-sm" onchange="loadAzhagiiStudents()">
            <option value="">All Colleges</option>
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
        <tbody id="azhagiiStudentsBody"></tbody>
    </table>
</div>

<?php require 'includes/footer.php'; ?>
