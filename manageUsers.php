<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
require_once 'db.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - Admin</title>
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
                <a href="adminDashboard.php" class="logo" style="font-size: 1.25rem;">
                    <span class="sparkle-icon"></span> Ziya Admin
                </a>
            </div>

            <nav class="sidebar-menu">
                <a href="adminDashboard.php">
                    <i class="fas fa-chart-pie"></i> Dashboard
                </a>
                <a href="manageEvents.php">
                    <i class="fas fa-calendar-alt"></i> Manage Events
                </a>
                <a href="manageUsers.php" class="active">
                    <i class="fas fa-users"></i> Users
                </a>
                <a href="registrations.php">
                    <i class="fas fa-clipboard-list"></i> Registrations
                </a>
                <a href="analytics.php">
                    <i class="fas fa-chart-line"></i> Analytics
                </a>
                <a href="settings.php">
                    <i class="fas fa-cog"></i> Settings
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
                    <h2 style="font-size: 1.25rem; margin: 0;">Users</h2>
                </div>
                <!-- Profile logic same as Dashboard -->
                <div class="user-profile">
                    <div style="text-align: right;">
                        <div style="font-weight: 600; font-size: 0.9rem;">
                            <?php echo htmlspecialchars($_SESSION['username']); ?>
                        </div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">Administrator</div>
                    </div>
                    <div class="avatar-circle">
                        <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                    </div>
                </div>
            </header>

            <!-- Users List -->
            <div class="card"
                style="border: 1px solid var(--border-color); background: var(--bg-surface); border-radius: var(--radius-md); padding: 0; overflow: hidden;">
                <div style="padding: 1.5rem; border-bottom: 1px solid var(--border-color);">
                    <h3 style="margin: 0;">Registered Users</h3>
                </div>
                <div class="table-responsive" style="border: none; border-radius: 0;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Department</th>
                                <th>Year</th>
                                <th>Register No</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Username</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $result = $conn->query("SELECT * FROM users WHERE role != 'admin' ORDER BY id DESC");
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>{$row['name']}</td>";
                                    echo "<td>{$row['department']}</td>";
                                    echo "<td>{$row['year']}</td>";
                                    echo "<td>{$row['regno']}</td>";
                                    echo "<td>{$row['email']}</td>";
                                    echo "<td>{$row['phone']}</td>";
                                    echo "<td>{$row['username']}</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='7' style='text-align:center;'>No users found.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

</body>

</html>