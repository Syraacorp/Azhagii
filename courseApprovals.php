<?php
$pageTitle   = 'Course Approvals';
$currentPage = 'courseApprovals';
require 'includes/auth.php';
requirePageRole(['superAdmin', 'adminZiyaa']);
require 'includes/header.php';
require 'includes/sidebar.php';
?>

<!-- Approval Stats -->
<div class="stats-grid" id="approval-stats"></div>

<!-- Tabs for Pending / All -->
<div class="approval-tabs" style="margin-bottom:1.5rem;">
    <button class="btn btn-primary btn-sm approval-tab active" data-tab="pending" onclick="switchApprovalTab('pending')"><i class="fas fa-clock"></i> Pending Approval</button>
    <button class="btn btn-outline btn-sm approval-tab" data-tab="all" onclick="switchApprovalTab('all')"><i class="fas fa-list"></i> All Courses</button>
    <button class="btn btn-outline btn-sm approval-tab" data-tab="rejected" onclick="switchApprovalTab('rejected')"><i class="fas fa-times-circle"></i> Rejected</button>
</div>

<!-- Pending Courses Table -->
<div id="approvalPendingTab">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Course</th>
                    <th>Code</th>
                    <th>Submitted By</th>
                    <th>College</th>
                    <th>Semester</th>
                    <th>Subjects</th>
                    <th>Syllabus</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="pendingCoursesBody"></tbody>
        </table>
    </div>
</div>

<!-- All Courses Table -->
<div id="approvalAllTab" style="display:none;">
    <div class="section-toolbar">
        <div class="filter-bar">
            <select id="approvalStatusFilter" class="form-input form-input-sm" onchange="loadAllCoursesForApproval()">
                <option value="">All Statuses</option>
                <option value="active">Approved/Active</option>
                <option value="pending">Pending</option>
                <option value="rejected">Rejected</option>
                <option value="draft">Draft</option>
                <option value="archived">Archived</option>
            </select>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Course</th>
                    <th>Code</th>
                    <th>Created By</th>
                    <th>Status</th>
                    <th>Semester</th>
                    <th>Syllabus</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="allCoursesApprovalBody"></tbody>
        </table>
    </div>
</div>

<!-- Rejected Courses Table -->
<div id="approvalRejectedTab" style="display:none;">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Course</th>
                    <th>Code</th>
                    <th>Submitted By</th>
                    <th>Rejection Reason</th>
                    <th>Rejected By</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody id="rejectedCoursesBody"></tbody>
        </table>
    </div>
</div>

<!-- Course Detail Modal container -->
<div id="courseDetailModal"></div>

<?php require 'includes/footer.php'; ?>
