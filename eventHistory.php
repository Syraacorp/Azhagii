<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
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
    <title>Event History - Ziya</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Google+Sans:wght@400;500;700&family=Inter:wght@400;500;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .event-card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
        }
    </style>
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
                <a href="eventHistory.php" class="active">
                    <i class="fas fa-history"></i> History
                </a>
                <a href="studentProfile.php">
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
                    <h2 style="font-size: 1.25rem; margin: 0;">Event History</h2>
                </div>
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

            <div class="card"
                style="border: 1px solid var(--border-color); background: var(--bg-surface); border-radius: var(--radius-md); padding: 0; overflow: hidden;">
                <div style="padding: 1.5rem; border-bottom: 1px solid var(--border-color);">
                    <h3 style="margin: 0;">Past Events</h3>
                </div>
                <div class="table-responsive" style="border: none; border-radius: 0;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Event</th>
                                <th>Date</th>
                                <th>Location</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $uid = $_SESSION['user_id'];
                            $sql = "SELECT e.title, e.event_date, e.location, r.status 
                                    FROM registrations r 
                                    JOIN events e ON r.event_id = e.id 
                                    WHERE r.user_id = $uid AND e.status = 'completed'
                                    ORDER BY e.event_date DESC";
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $statusClass = 'status-warning';
                                    echo "<tr>";
                                    echo "<td>{$row['title']}</td>";
                                    echo "<td>" . date('M d, Y', strtotime($row['event_date'])) . "</td>";
                                    echo "<td>{$row['location']}</td>";
                                    echo "<td><span class='status-badge {$statusClass}'>Attended</span></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4' style='text-align:center;'>No past events found.</td></tr>";
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