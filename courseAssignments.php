<?php
$pageTitle   = 'Course Assignments';
$currentPage = 'courseAssignments';
require 'includes/auth.php';
requirePageRole(['superAdmin', 'adminAzhagii']);
require 'includes/header.php';
require 'includes/sidebar.php';
?>

<div class="section-toolbar">
    <div class="filter-bar">
        <select id="assignCourseSelect" class="form-input form-input-sm" onchange="loadAssignments()">
            <option value="">Select a course</option>
        </select>
    </div>
    <button class="btn btn-primary" onclick="showAssignModal()"><i class="fas fa-plus"></i> Assign to College</button>
</div>
<div class="table-responsive">
    <table class="table" id="assignmentsTable">
        <thead><tr><th>#</th><th>College</th><th>Code</th><th>Assigned By</th><th>Date</th><th>Actions</th></tr></thead>
        <tbody id="assignmentsBody"></tbody>
    </table>
</div>

<?php require 'includes/footer.php'; ?>
