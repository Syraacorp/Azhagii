<?php
$pageTitle   = 'Content Management';
$currentPage = 'manageContent';
require 'includes/auth.php';
requirePageRole('ziyaaCoordinator');
require 'includes/header.php';
require 'includes/sidebar.php';
?>

<div class="section-toolbar">
    <div class="filter-bar">
        <select id="contentCourseSelect" class="form-input form-input-sm" onchange="loadContent()">
            <option value="">Select a course</option>
        </select>
    </div>
    <button class="btn btn-primary" onclick="showContentModal()"><i class="fas fa-plus"></i> Add Content</button>
</div>
<div id="contentList" class="content-list"></div>

<?php require 'includes/footer.php'; ?>
