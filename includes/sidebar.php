<?php
// $currentPage should be set before including this file
$currentPage = $currentPage ?? 'dashboard';
?>
<!-- ═══ SIDEBAR ═══ -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <a href="<?= $dashboardLink ?>" class="logo" style="text-decoration:none;">
            <span class="sparkle-icon"></span> Azhagii
        </a>
    </div>
    <nav class="sidebar-menu">

        <!-- All Roles: Dashboard -->
        <a href="<?= $dashboardLink ?>" class="nav-item <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>

        <?php if ($role === 'superAdmin'): ?>
            <!-- superAdmin -->
            <a href="manageColleges.php" class="nav-item <?= $currentPage === 'manageCollegesSSR' ? 'active' : '' ?>">
                <i class="fas fa-university"></i> Colleges
            </a>
            <a href="azhagiiStudents.php" class="nav-item <?= $currentPage === 'azhagiiStudentsSSR' ? 'active' : '' ?>">
                <i class="fas fa-user-graduate"></i> Students
            </a>
        <?php endif; ?>

        <?php if (in_array($role, ['superAdmin', 'adminAzhagii'])): ?>
            <!-- superAdmin + adminAzhagii -->
            <a href="manageUsers.php" class="nav-item <?= $currentPage === 'manageUsersSSR' ? 'active' : '' ?>">
                <i class="fas fa-users"></i> Users
            </a>
            <a href="profileRequest.php" class="nav-item <?= $currentPage === 'profileRequestSSR' ? 'active' : '' ?>">
                <i class="fas fa-user-clock"></i> Profile Requests
            </a>
            <a href="manageCourses.php" class="nav-item <?= $currentPage === 'manageCoursesSSR' ? 'active' : '' ?>">
                <i class="fas fa-book"></i> Courses
            </a>
            <a href="courseApprovals.php" class="nav-item <?= $currentPage === 'courseApprovalsSSR' ? 'active' : '' ?>">
                <i class="fas fa-check-double"></i> Approvals
            </a>
            <a href="manageSubjects.php" class="nav-item <?= $currentPage === 'manageSubjectsSSR' ? 'active' : '' ?>">
                <i class="fas fa-layer-group"></i> Subjects
            </a>
            <a href="courseAssignments.php" class="nav-item <?= $currentPage === 'courseAssignmentsSSR' ? 'active' : '' ?>">
                <i class="fas fa-link"></i> Assignments
            </a>
        <?php endif; ?>

        <?php if (in_array($role, ['azhagiiCoordinator', 'superAdmin'])): ?>
            <!-- Coordinator / SuperAdmin Features -->
            <a href="myCourses.php"
                class="nav-item <?= ($currentPage === 'myCourses' || $currentPage === 'myCoursesSSR') ? 'active' : '' ?>">
                <i class="fas fa-book-open"></i> My Courses
            </a>
            <a href="coordinatorCourseCreate.php"
                class="nav-item <?= $currentPage === 'coordinatorCourseCreateSSR' ? 'active' : '' ?>">
                <i class="fas fa-plus-circle"></i> Create Course
            </a>
            <a href="manageContent.php" class="nav-item <?= $currentPage === 'manageContentSSR' ? 'active' : '' ?>">
                <i class="fas fa-file-alt"></i> Content
            </a>
            <a href="manageTopics.php" class="nav-item <?= $currentPage === 'manageTopicsSSR' ? 'active' : '' ?>">
                <i class="fas fa-tags"></i> Topics
            </a>
            <?php if ($role === 'azhagiiCoordinator'): // Only for actual coordinators ?>
                <a href="myStudents.php" class="nav-item <?= $currentPage === 'myStudentsSSR' ? 'active' : '' ?>">
                    <i class="fas fa-user-graduate"></i> Students
                </a>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($role === 'azhagiiStudents'): ?>
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