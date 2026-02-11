<?php
// Ensure DB config is loaded for BASE_URL
require_once __DIR__ . '/../config/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['role'] ?? 'user';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - EventManager</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body class="dashboard-body">
    <div class="dashboard-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <a href="<?php echo BASE_URL; ?>/" class="logo">Event<span class="highlight">Manager</span></a>
            </div>
            <ul class="sidebar-menu">
                <?php if ($role === 'admin'): ?>
                    <li><a href="<?php echo BASE_URL; ?>/admin/index.php"
                            class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>"><i class="fas fa-home"></i>
                            Dashboard</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin/create_event.php"
                            class="<?php echo $current_page == 'create_event.php' ? 'active' : ''; ?>"><i
                                class="fas fa-plus-circle"></i> Create Event</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin/users.php"
                            class="<?php echo $current_page == 'users.php' ? 'active' : ''; ?>"><i class="fas fa-users"></i>
                            Users</a></li>
                <?php else: ?>
                    <li><a href="<?php echo BASE_URL; ?>/user/index.php"
                            class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>"><i class="fas fa-home"></i>
                            Dashboard</a></li>
                <?php endif; ?>
                <li><a href="<?php echo BASE_URL; ?>/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <div class="main-content-wrapper">
            <!-- Top Header -->
            <header class="top-header">
                <div class="header-left">
                    <i class="fas fa-bars menu-toggle" id="menu-toggle"></i>
                    <h2 class="page-title">
                        <?php
                        if ($current_page == 'index.php')
                            echo 'Dashboard';
                        elseif ($current_page == 'create_event.php')
                            echo 'Create New Event';
                        elseif ($current_page == 'users.php')
                            echo 'User Management';
                        elseif ($current_page == 'event_details.php')
                            echo 'Event Details';
                        else
                            echo 'Overview';
                        ?>
                    </h2>
                </div>
                <div class="header-right">
                    <div class="user-profile" id="user-dropdown-trigger">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)); ?>
                        </div>
                        <div class="user-info">
                            <span class="user-name">
                                <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>
                            </span>
                            <span class="user-role">
                                <?php echo ucfirst($role); ?>
                            </span>
                        </div>
                        <i class="fas fa-chevron-down" style="font-size: 0.8rem; color: var(--text-light);"></i>
                    </div>
                    <div class="dropdown-menu" id="user-dropdown">
                        <div class="dropdown-item">
                            <i class="fas fa-user-circle"></i> Profile
                        </div>
                        <div class="dropdown-divider"></div>
                        <a href="<?php echo BASE_URL; ?>/logout.php" class="dropdown-item"
                            style="color: var(--accent);">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="dashboard-content">