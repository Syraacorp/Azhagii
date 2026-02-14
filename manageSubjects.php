<?php
$pageTitle = 'Manage Subjects';
$currentPage = 'manageSubjects';
require 'includes/auth.php';
requirePageRole(['superAdmin', 'adminAzhagii']);
require 'includes/header.php';
require 'includes/sidebar.php';
?>

<div class="section-toolbar">
    <div class="filter-bar">
        <select id="subjectCourseSelect" class="form-input form-input-sm" onchange="loadSubjects()">
            <option value="">Select a course</option>
        </select>
    </div>
    <button class="btn btn-primary" onclick="showSubjectModal()"><i class="fas fa-plus"></i> Add Subject</button>
</div>

<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Title</th>
                <th>Code</th>
                <th>Description</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="subjectsBody">
            <tr>
                <td colspan="6" class="empty-state">
                    <p>Select a course above</p>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<?php require 'includes/footer.php'; ?>