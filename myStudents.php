<?php
$pageTitle   = 'My Students';
$currentPage = 'myStudents';
require 'includes/auth.php';
requirePageRole('ziyaaCoordinator');
require 'includes/header.php';
require 'includes/sidebar.php';
?>

<div class="section-toolbar">
    <div class="filter-bar">
        <select id="studentCourseSelect" class="form-input form-input-sm" onchange="loadCourseStudents()">
            <option value="">Select a course</option>
        </select>
    </div>
</div>
<div class="table-responsive">
    <table class="table">
        <thead><tr><th>#</th><th>Student</th><th>Email</th><th>Phone</th><th>Progress</th><th>Status</th><th>Enrolled</th></tr></thead>
        <tbody id="courseStudentsBody"></tbody>
    </table>
</div>

<?php require 'includes/footer.php'; ?>
