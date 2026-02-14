<?php
$pageTitle   = 'Manage Courses';
$currentPage = 'manageCourses';
require 'includes/auth.php';
requirePageRole(['superAdmin', 'adminAzhagii']);
require 'includes/header.php';
require 'includes/sidebar.php';
?>

<div class="section-toolbar">
    <div></div>
    <button class="btn btn-primary" onclick="showCourseModal()"><i class="fas fa-plus"></i> Add Course</button>
</div>
<div class="table-responsive">
    <table class="table">
        <thead><tr><th>#</th><th>Title</th><th>Category</th><th>Semester</th><th>Colleges</th><th>Enrollments</th><th>Content</th><th>Status</th><th>Syllabus</th><th>Actions</th></tr></thead>
        <tbody id="coursesBody"></tbody>
    </table>
</div>

<?php require 'includes/footer.php'; ?>
