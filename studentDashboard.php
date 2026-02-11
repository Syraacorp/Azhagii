<?php
session_start();
// Basic role check (redirect if not logged in or not a student/user)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
// Assuming role 'user' is for students
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header("Location: adminDashboard.php"); // Redirect admin back to their dash
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Ziya</title>
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
                <a href="index.php" class="logo" style="font-size: 1.25rem;">
                    <span class="sparkle-icon"></span> Ziya
                </a>
            </div>

            <nav class="sidebar-menu">
                <a href="studentDashboard.php" class="active">
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
                    <h2 style="font-size: 1.25rem; margin: 0;">Welcome,
                        <?php echo htmlspecialchars($_SESSION['username']); ?>!
                    </h2>
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

            <!-- Stats Grid -->
            <div class="stats-grid">
                <?php
                $uid = $_SESSION['user_id'];
                // Upcoming Count
                $upcoming_sql = "SELECT COUNT(*) as count FROM registrations r 
                                     JOIN events e ON r.event_id = e.id 
                                     WHERE r.user_id = $uid AND e.status = 'upcoming'";
                $upcoming_res = $conn->query($upcoming_sql);
                $upcoming_count = $upcoming_res->fetch_assoc()['count'];

                // Attended Count (Assuming 'completed' status means attended for now, or use 'attended' status in registration)
                $attended_sql = "SELECT COUNT(*) as count FROM registrations r 
                                     JOIN events e ON r.event_id = e.id 
                                     WHERE r.user_id = $uid AND (e.status = 'completed' OR r.status = 'attended')";
                $attended_res = $conn->query($attended_sql);
                $attended_count = $attended_res->fetch_assoc()['count'];
                ?>
                <div class="stat-card">
                    <div class="stat-icon" style="color: #4285f4; background: rgba(66, 133, 244, 0.1);">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $upcoming_count; ?></div>
                        <div class="stat-label">Upcoming Events</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="color: #34d399; background: rgba(52, 211, 153, 0.1);">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $attended_count; ?></div>
                        <div class="stat-label">Events Attended</div>
                    </div>
                </div>
            </div>

            <!-- Upcoming Events Table -->
            <div class="card"
                style="border: 1px solid var(--border-color); background: var(--bg-surface); border-radius: var(--radius-md); padding: 0; overflow: hidden;">
                <div
                    style="padding: 1.5rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="margin: 0; font-size: 1.1rem;">My Upcoming Schedule</h3>
                    <a href="browseEvents.php" class="btn btn-outline"
                        style="padding: 0.4rem 0.8rem; font-size: 0.85rem;">Browse
                        More</a>
                </div>
                <div class="table-responsive" style="border: none; border-radius: 0;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Event</th>
                                <th>Date</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $schedule_sql = "SELECT e.title, e.event_date, e.location, r.status 
                                             FROM registrations r 
                                             JOIN events e ON r.event_id = e.id 
                                             WHERE r.user_id = $uid AND e.status = 'upcoming' 
                                             ORDER BY e.event_date ASC LIMIT 5";
                            $schedule_res = $conn->query($schedule_sql);

                            if ($schedule_res->num_rows > 0) {
                                while ($row = $schedule_res->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                                    echo "<td>" . date('M d, Y', strtotime($row['event_date'])) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['location']) . "</td>";
                                    echo "<td><span class='status-badge status-success'>" . ucfirst($row['status']) . "</span></td>";
                                    echo "<td><a href='myEvents.php' class='btn btn-primary' style='padding: 0.25rem 0.75rem; font-size: 0.8rem;'>View Details</a></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5' style='text-align:center;'>No upcoming events.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recommended Section -->
            <div style="margin-top: 2rem;">
                <h3 style="margin-bottom: 1rem; font-size: 1.1rem;">Recommended For You</h3>
                <div class="grid"
                    style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; margin-top: 0;">
                    <div class="event-card">
                        <div class="event-date">MAR 10, 2026</div>
                        <h3 class="event-title" style="font-size: 1.1rem;">Digital Marketing Masterclass</h3>
                        <p style="font-size: 0.9rem; margin-bottom: 1rem;">Master SEO, SEM, and Social Media Marketing
                            strategies.</p>
                        <a href="#" class="btn btn-outline" style="width: 100%; justify-content: center;">Register
                            Now</a>
                    </div>
                </div>
            </div>

        </main>
    </div>

</body>

</html>