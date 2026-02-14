<?php
$pageTitle   = 'Manage Topics';
$currentPage = 'manageTopics';
require 'includes/auth.php';
requirePageRole('azhagiiCoordinator');
require 'includes/header.php';
require 'includes/sidebar.php';
?>

<div class="section-toolbar">
    <div class="filter-bar">
        <select id="topicCourseSelect" class="form-input form-input-sm" onchange="loadTopicSubjects()">
            <option value="">Select a course</option>
        </select>
        <select id="topicSubjectSelect" class="form-input form-input-sm" onchange="loadTopics()">
            <option value="">Select a subject/unit</option>
        </select>
    </div>
    <button class="btn btn-primary" onclick="showTopicModal()"><i class="fas fa-plus"></i> Add Topic</button>
</div>

<div class="table-responsive">
    <table class="table" id="topicsTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Topic Title</th>
                <th>Description</th>
                <th>Added By</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="topicsBody">
            <tr>
                <td colspan="6" class="empty-state">
                    <i class="fas fa-tags"></i>
                    <p>Select a course and subject above</p>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<?php require 'includes/footer.php'; ?>
