<?php
// $currentPage should be set before including this file
$currentPage = $currentPage ?? 'dashboard';
?>
<!-- ═══ SIDEBAR ═══ -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <a href="<?= $dashboardLink ?>" class="logo" style="text-decoration:none;">
            <span class="sparkle-icon"></span> Ziyaa
        </a>
    </div>
    <nav class="sidebar-menu">

        <!-- All Roles: Dashboard -->
        <a href="<?= $dashboardLink ?>" class="nav-item <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>

        <?php if ($role === 'superAdmin'): ?>
            <!-- superAdmin -->
            <a href="manageColleges.php" class="nav-item <?= $currentPage === 'manageColleges' ? 'active' : '' ?>">
                <i class="fas fa-university"></i> Colleges
            </a>
        <?php endif; ?>

        <?php if (in_array($role, ['superAdmin', 'adminZiyaa'])): ?>
            <!-- superAdmin + adminZiyaa -->
            <a href="manageUsers.php" class="nav-item <?= $currentPage === 'manageUsers' ? 'active' : '' ?>">
                <i class="fas fa-users"></i> Users
            </a>
            <a href="profileRequest.php" class="nav-item <?= $currentPage === 'profileRequest' ? 'active' : '' ?>">
                <i class="fas fa-user-clock"></i> Profile Requests
            </a>
            <a href="manageCourses.php" class="nav-item <?= $currentPage === 'manageCourses' ? 'active' : '' ?>">
                <i class="fas fa-book"></i> Courses
            </a>
            <a href="courseApprovals.php" class="nav-item <?= $currentPage === 'courseApprovals' ? 'active' : '' ?>">
                <i class="fas fa-check-double"></i> Approvals
            </a>
            <a href="manageSubjects.php" class="nav-item <?= $currentPage === 'manageSubjects' ? 'active' : '' ?>">
                <i class="fas fa-layer-group"></i> Subjects
            </a>
            <a href="courseAssignments.php" class="nav-item <?= $currentPage === 'courseAssignments' ? 'active' : '' ?>">
                <i class="fas fa-link"></i> Assignments
            </a>
        <?php endif; ?>

        <?php if ($role === 'ziyaaCoordinator'): ?>
            <!-- Coordinator -->
            <a href="myCourses.php" class="nav-item <?= $currentPage === 'myCourses' ? 'active' : '' ?>">
                <i class="fas fa-book-open"></i> My Courses
            </a>
            <a href="coordinatorCourseCreate.php"
                class="nav-item <?= $currentPage === 'coordinatorCourseCreate' ? 'active' : '' ?>">
                <i class="fas fa-plus-circle"></i> Create Course
            </a>
            <a href="manageContent.php" class="nav-item <?= $currentPage === 'manageContent' ? 'active' : '' ?>">
                <i class="fas fa-file-alt"></i> Content
            </a>
            <a href="manageTopics.php" class="nav-item <?= $currentPage === 'manageTopics' ? 'active' : '' ?>">
                <i class="fas fa-tags"></i> Topics
            </a>
            <a href="myStudents.php" class="nav-item <?= $currentPage === 'myStudents' ? 'active' : '' ?>">
                <i class="fas fa-user-graduate"></i> Students
            </a>
        <?php endif; ?>

        <?php if ($role === 'ziyaaStudents'): ?>
            <!-- Student -->
            <a href="browseCourses.php" class="nav-item <?= $currentPage === 'browseCourses' ? 'active' : '' ?>">
                <i class="fas fa-compass"></i> Browse Courses
            </a>
            <a href="myLearning.php" class="nav-item <?= $currentPage === 'myLearning' ? 'active' : '' ?>">
                <i class="fas fa-graduation-cap"></i> My Learning
            </a>
        <?php endif; ?>

    </nav>
</aside>

<!-- ═══ MAIN CONTENT WRAPPER ═══ -->
<main class="main-content">
    <div class="content-wrapper">