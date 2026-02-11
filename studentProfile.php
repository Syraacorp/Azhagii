<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}
require_once 'db.php';

// Fetch Current User Details
$uid = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = $uid";
$result = $conn->query($query);
$user = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Ziya</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Google+Sans:wght@400;500;700&family=Inter:wght@400;500;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="dashboard-body">

    <div class="dashboard-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="studentDashboard.php" class="logo" style="font-size: 1.2rem;">
                    <span class="sparkle-icon"></span> Ziya
                </a>
            </div>

            <nav class="sidebar-menu">
                <a href="studentDashboard.php">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <a href="myEvents.php">
                    <i class="fas fa-ticket-alt"></i> My Events
                </a>
                <a href="browseEvents.php">
                    <i class="fas fa-search"></i> Browse Events
                </a>
                <a href="eventHistory.php">
                    <i class="fas fa-history"></i> History
                </a>
                <a href="studentProfile.php" class="active">
                    <i class="fas fa-user-circle"></i> Profile
                </a>
            </nav>

            <div class="sidebar-footer">
                <a href="logout.php"
                    style="color: var(--text-muted); display: flex; align-items: center; gap: 0.75rem; text-decoration: none; font-size: 0.9rem;">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Bar -->
            <header class="top-bar">
                <div>
                    <h2 style="font-size: 1.25rem; margin: 0;">Profile</h2>
                </div>
                <!-- Profile logic same as Dashboard -->
                <div class="user-profile">
                    <div style="text-align: right;">
                        <div style="font-weight: 600; font-size: 0.9rem;">
                            <?php echo htmlspecialchars($_SESSION['username']); ?>
                        </div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">Student</div>
                    </div>
                    <div class="avatar-circle">
                        <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                    </div>
                </div>
            </header>

            <div style="display: flex; gap: 2rem; flex-wrap: wrap;">
                <!-- Profile Card -->
                <div class="card"
                    style="flex: 1; min-width: 300px; text-align: center; padding: 2.5rem; background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-md);">
                    <div class="avatar-circle"
                        style="width: 100px; height: 100px; font-size: 2.5rem; margin: 0 auto 1.5rem auto; background: var(--button-gradient);">
                        <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                    </div>
                    <h3 style="margin: 0; font-size: 1.4rem;">
                        <?php echo htmlspecialchars($user['name']); ?>
                    </h3>
                    <p style="color: var(--text-muted); margin-top: 0.5rem;">
                        <?php echo htmlspecialchars($user['department']); ?> Student
                    </p>

                    <div style="margin-top: 2rem; display: flex; justify-content: space-around;">
                        <div>
                            <div style="font-weight: bold; font-size: 1.2rem;">
                                <?php echo htmlspecialchars($user['year']); ?>
                            </div>
                            <div style="font-size: 0.8rem; color: var(--text-muted);">Year</div>
                        </div>
                        <div>
                            <div style="font-weight: bold; font-size: 1.2rem;">
                                <?php echo htmlspecialchars($user['regno']); ?>
                            </div>
                            <div style="font-size: 0.8rem; color: var(--text-muted);">Roll No</div>
                        </div>
                    </div>
                </div>

                <!-- Profile Details -->
                <div class="card"
                    style="flex: 2; min-width: 300px; background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 2rem;">
                    <h3 style="margin-top: 0; margin-bottom: 2rem;">Personal Details</h3>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                        <div class="form-group">
                            <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">Full
                                Name</label>
                            <input type="text" class="form-input" value="<?php echo htmlspecialchars($user['name']); ?>"
                                readonly>
                        </div>
                        <div class="form-group">
                            <label
                                style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">Email</label>
                            <input type="text" class="form-input"
                                value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label
                                style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">Phone</label>
                            <input type="text" class="form-input"
                                value="<?php echo htmlspecialchars($user['phone']); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label
                                style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">Username</label>
                            <input type="text" class="form-input"
                                value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label
                                style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">Department</label>
                            <input type="text" class="form-input"
                                value="<?php echo htmlspecialchars($user['department']); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">Year</label>
                            <input type="text" class="form-input" value="<?php echo htmlspecialchars($user['year']); ?>"
                                readonly>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>

</body>

</html>