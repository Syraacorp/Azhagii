<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Dashboard' ?> - Ziyaa LMS</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Google+Sans:wght@400;500;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<div class="dashboard-layout">
    <div class="sidebar-overlay"></div>

    <!-- ═══ TOP BAR ═══ -->
    <header class="top-bar">
        <div style="display:flex;align-items:center;gap:1rem;">
            <button class="hamburger" id="menu-toggle"><i class="fas fa-bars"></i></button>
            <span class="page-title" id="pageTitle"><?= $pageTitle ?? 'Dashboard' ?></span>
        </div>
        <div style="display:flex;align-items:center;gap:1rem;">
            <button class="theme-toggle" id="themeToggle"><i class="fas fa-sun"></i></button>

            <!-- User Dropdown -->
            <div class="user-dropdown-wrapper">
                <button class="user-dropdown-toggle" id="userDropdownToggle">
                    <div class="avatar-circle"><?= $avatarInitial ?></div>
                    <span class="user-dropdown-name"><?= htmlspecialchars($userName) ?></span>
                    <i class="fas fa-chevron-down user-dropdown-arrow"></i>
                </button>
                <div class="user-dropdown-menu" id="userDropdownMenu">
                    <div class="user-dropdown-header">
                        <div class="avatar-circle" style="width:42px;height:42px;font-size:1.1rem;"><?= $avatarInitial ?></div>
                        <div>
                            <div class="user-dropdown-fullname"><?= htmlspecialchars($userName) ?></div>
                            <div class="user-dropdown-email"><?= htmlspecialchars($userEmail) ?></div>
                        </div>
                    </div>
                    <div class="user-dropdown-divider"></div>
                    <a href="<?= $dashboardLink ?>" class="user-dropdown-item"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                    <a href="profile.php" class="user-dropdown-item"><i class="fas fa-user-circle"></i> My Profile</a>
                    <div class="user-dropdown-divider"></div>
                    <a href="logout.php" class="user-dropdown-item user-dropdown-item-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        </div>
    </header>
