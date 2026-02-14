@php
    $currentPage = $currentPage ?? 'dashboard';
    $role = auth()->user()->role;
    $dashboardLink = auth()->user()->dashboard_route;
@endphp
<!-- ═══ SIDEBAR ═══ -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <a href="{{ $dashboardLink }}" class="logo" style="text-decoration:none;">
            <span class="sparkle-icon"></span> Azhagii
        </a>
    </div>
    <nav class="sidebar-menu">

        <!-- All Roles: Dashboard -->
        <a href="{{ $dashboardLink }}" class="nav-item {{ $currentPage === 'dashboard' ? 'active' : '' }}">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>

        @if($role === 'superAdmin')
            <!-- superAdmin -->
            <a href="{{ route('colleges') }}" class="nav-item {{ $currentPage === 'manageCollegesSSR' ? 'active' : '' }}">
                <i class="fas fa-university"></i> Colleges
            </a>
            <a href="{{ route('azhagii-students') }}" class="nav-item {{ $currentPage === 'azhagiiStudentsSSR' ? 'active' : '' }}">
                <i class="fas fa-user-graduate"></i> Students
            </a>
        @endif

        @if(in_array($role, ['superAdmin', 'adminAzhagii']))
            <!-- superAdmin + adminAzhagii -->
            <a href="{{ route('users') }}" class="nav-item {{ $currentPage === 'manageUsersSSR' ? 'active' : '' }}">
                <i class="fas fa-users"></i> Users
            </a>
            <a href="{{ route('profile-requests') }}" class="nav-item {{ $currentPage === 'profileRequestSSR' ? 'active' : '' }}">
                <i class="fas fa-user-clock"></i> Profile Requests
            </a>
            <a href="{{ route('courses') }}" class="nav-item {{ $currentPage === 'manageCoursesSSR' ? 'active' : '' }}">
                <i class="fas fa-book"></i> Courses
            </a>
            <a href="{{ route('course-approvals') }}" class="nav-item {{ $currentPage === 'courseApprovalsSSR' ? 'active' : '' }}">
                <i class="fas fa-check-double"></i> Approvals
            </a>
            <a href="{{ route('subjects') }}" class="nav-item {{ $currentPage === 'manageSubjectsSSR' ? 'active' : '' }}">
                <i class="fas fa-layer-group"></i> Subjects
            </a>
            <a href="{{ route('course-assignments') }}" class="nav-item {{ $currentPage === 'courseAssignmentsSSR' ? 'active' : '' }}">
                <i class="fas fa-link"></i> Assignments
            </a>
        @endif

        @if(in_array($role, ['azhagiiCoordinator', 'superAdmin']))
            <!-- Coordinator / SuperAdmin Features -->
            <a href="{{ route('my-courses') }}" class="nav-item {{ in_array($currentPage, ['myCourses', 'myCoursesSSR']) ? 'active' : '' }}">
                <i class="fas fa-book-open"></i> My Courses
            </a>
            <a href="{{ route('create-course') }}" class="nav-item {{ $currentPage === 'coordinatorCourseCreateSSR' ? 'active' : '' }}">
                <i class="fas fa-plus-circle"></i> Create Course
            </a>
            <a href="{{ route('content') }}" class="nav-item {{ $currentPage === 'manageContentSSR' ? 'active' : '' }}">
                <i class="fas fa-file-alt"></i> Content
            </a>
            <a href="{{ route('topics') }}" class="nav-item {{ $currentPage === 'manageTopicsSSR' ? 'active' : '' }}">
                <i class="fas fa-tags"></i> Topics
            </a>
            @if($role === 'azhagiiCoordinator')
                <a href="{{ route('my-students') }}" class="nav-item {{ $currentPage === 'myStudentsSSR' ? 'active' : '' }}">
                    <i class="fas fa-user-graduate"></i> Students
                </a>
            @endif
        @endif

        @if($role === 'azhagiiStudents')
            <!-- Student -->
            <a href="{{ route('browse-courses') }}" class="nav-item {{ $currentPage === 'browseCourses' ? 'active' : '' }}">
                <i class="fas fa-compass"></i> Browse Courses
            </a>
            <a href="{{ route('my-learning') }}" class="nav-item {{ $currentPage === 'myLearning' ? 'active' : '' }}">
                <i class="fas fa-graduation-cap"></i> My Learning
            </a>
        @endif

    </nav>
</aside>
