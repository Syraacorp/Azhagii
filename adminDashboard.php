<?php
session_start();
require_once 'db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch stats
// Total Users (Students)
$user_sql = "SELECT COUNT(*) as count FROM users WHERE role != 'admin'";
$user_res = $conn->query($user_sql);
$total_users = $user_res->fetch_assoc()['count'];

// Active Events
$event_sql = "SELECT COUNT(*) as count FROM events WHERE status = 'upcoming'";
$event_res = $conn->query($event_sql);
$active_events = $event_res->fetch_assoc()['count'];

// Tickets Sold (Registrations)
$reg_sql = "SELECT COUNT(*) as count FROM registrations WHERE status = 'registered'";
$reg_res = $conn->query($reg_sql);
$tickets_sold = $reg_res->fetch_assoc()['count'];

// Total Revenue
// Join registrations with events to sum the price
$rev_sql = "SELECT SUM(e.price) as revenue FROM registrations r JOIN events e ON r.event_id = e.id WHERE r.status = 'registered'";
$rev_res = $conn->query($rev_sql);
$revenue_row = $rev_res->fetch_assoc();
$total_revenue = $revenue_row['revenue'] ? $revenue_row['revenue'] : 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Ziya</title>
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
                <a href="#" class="logo" style="font-size: 1.25rem;">
                    <span class="sparkle-icon"></span> Ziya Admin
                </a>
            </div>

            <nav class="sidebar-menu">
                <a href="adminDashboard.php" class="active">
                    <i class="fas fa-chart-pie"></i> Dashboard
                </a>
                <a href="manageEvents.php">
                    <i class="fas fa-calendar-alt"></i> Manage Events
                </a>
                <a href="manageUsers.php">
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
                    <h2 style="font-size: 1.25rem; margin: 0;">Dashboard Overview</h2>
                </div>
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

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="color: #4285f4; background: rgba(66, 133, 244, 0.1);">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $total_users; ?></div>
                        <div class="stat-label">Total Students</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="color: #9b72cb; background: rgba(155, 114, 203, 0.1);">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $active_events; ?></div>
                        <div class="stat-label">Active Events</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="color: #d96570; background: rgba(217, 101, 112, 0.1);">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $tickets_sold; ?></div>
                        <div class="stat-label">Registrations</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="color: #34d399; background: rgba(52, 211, 153, 0.1);">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value">$<?php echo number_format($total_revenue, 2); ?></div>
                        <div class="stat-label">Total Revenue</div>
                    </div>
                </div>
            </div>

            <!-- Recent Events Table -->
            <div class="card"
                style="border: 1px solid var(--border-color); background: var(--bg-surface); border-radius: var(--radius-md); padding: 0; overflow: hidden;">
                <div
                    style="padding: 1.5rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="margin: 0; font-size: 1.1rem;">Recent Events</h3>
                    <a href="manageEvents.php" class="btn btn-outline"
                        style="padding: 0.4rem 0.8rem; font-size: 0.85rem;">View All</a>
                </div>
                <div class="table-responsive" style="border: none; border-radius: 0;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Event Name</th>
                                <th>Category</th>
                                <th>Date</th>
                                <th>Capacity</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $recent_sql = "SELECT id, title, category, event_date, capacity, status FROM events ORDER BY created_at DESC LIMIT 5";
                            $recent_res = $conn->query($recent_sql);

                            if ($recent_res->num_rows > 0) {
                                while ($row = $recent_res->fetch_assoc()) {
                                    $dateStr = date('M d, Y', strtotime($row['event_date']));
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['category']) . "</td>";
                                    echo "<td>{$dateStr}</td>";
                                    echo "<td>{$row['capacity']}</td>";
                                    echo "<td><a href='editEvent.php?id={$row['id']}' class='btn btn-outline' style='padding: 0.25rem 0.5rem; font-size: 0.8rem;'>Edit</a></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5' style='text-align:center;'>No recent events found.</td></tr>";
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