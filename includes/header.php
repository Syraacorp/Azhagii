<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Dashboard' ?> - Azhagii LMS</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Google+Sans:wght@400;500;700&family=Inter:wght@400;500;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="dashboard-layout">
        <div class="sidebar-overlay"></div>

        <!-- ═══ TOP BAR ═══ -->
        <header class="top-bar">
            <div class="top-bar-left">
                <button class="hamburger" id="menu-toggle"><i class="fas fa-bars"></i></button>
                <span class="page-title" id="pageTitle"><?= $pageTitle ?? 'Dashboard' ?></span>
            </div>
            <div class="top-bar-right">
                <button class="theme-toggle" id="themeToggle"><i class="fas fa-sun"></i></button>

                <!-- User Dropdown -->
                <div class="user-dropdown-wrapper">
                    <button class="user-dropdown-toggle" id="userDropdownToggle">
                        <div class="avatar-circle">
                            <?php if (!empty($profilePhoto)): ?>
                                <img src="<?= htmlspecialchars($profilePhoto) ?>" alt="Avatar"
                                    style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                            <?php else: ?>
                                <?= $avatarInitial ?>
                            <?php endif; ?>
                        </div>
                        <span class="user-dropdown-name"><?= htmlspecialchars($userName) ?></span>
                        <i class="fas fa-chevron-down user-dropdown-arrow"></i>
                    </button>
                    <div class="user-dropdown-menu" id="userDropdownMenu">
                        <div class="user-dropdown-header">
                            <div class="avatar-circle" style="width:42px;height:42px;font-size:1.1rem;">
                                <?php if (!empty($profilePhoto)): ?>
                                    <img src="<?= htmlspecialchars($profilePhoto) ?>" alt="Avatar"
                                        style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                                <?php else: ?>
                                    <?= $avatarInitial ?>
                                <?php endif; ?>
                            </div>
                            <div>
                                <div class="user-dropdown-fullname"><?= htmlspecialchars($userName) ?></div>
                                <div class="user-dropdown-email"><?= htmlspecialchars($userEmail) ?></div>
                            </div>
                        </div>
                        <div class="user-dropdown-divider"></div>
                        <a href="<?= $dashboardLink ?>" class="user-dropdown-item"><i class="fas fa-tachometer-alt"></i>
                            Dashboard</a>
                        <a href="profile.php" class="user-dropdown-item"><i class="fas fa-user-circle"></i> My
                            Profile</a>
                        <div class="user-dropdown-divider"></div>
                        <a href="logout.php" class="user-dropdown-item user-dropdown-item-danger"><i
                                class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Global Scroll Progress -->
        <div class="scroll-progress-container" id="globalScrollContainer">
            <div class="scroll-progress-bar" id="globalProgressBar"></div>
        </div>