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
    <title>Browse Events - Ziya</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Google+Sans:wght@400;500;700&family=Inter:wght@400;500;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        .event-card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
        }

        .event-card {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
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
                <a href="browseEvents.php" class="active">
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
                    <h2 style="font-size: 1.25rem; margin: 0;">Browse Events</h2>
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

            <div class="event-card-grid">
                <?php
                // Fetch events
                // Left join with registrations to check if already registered
                $uid = $_SESSION['user_id'];
                $sql = "SELECT e.*, 
                        (SELECT COUNT(*) FROM registrations r WHERE r.event_id = e.id AND r.user_id = $uid) as is_registered
                        FROM events e 
                        WHERE e.status = 'upcoming' 
                        ORDER BY e.event_date ASC";

                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $isRegistered = $row['is_registered'] > 0;
                        $btnText = $isRegistered ? "Registered" : "Register Now";
                        $btnClass = $isRegistered ? "btn-secondary" : "btn-primary";
                        $disabled = $isRegistered ? "disabled" : "";
                        $clickAction = $isRegistered ? "" : "registerEvent({$row['id']})";

                        echo '
                        <div class="event-card" style="background: var(--bg-surface); padding: 1.5rem; border: 1px solid var(--border-color); border-radius: var(--radius-md);">
                            <div>
                                <div class="event-date" style="color: var(--accent-blue); font-weight: bold; margin-bottom: 0.5rem;">' . date('M d, Y', strtotime($row['event_date'])) . '</div>
                                <h3 class="event-title" style="margin-top: 0; margin-bottom: 0.5rem; color: white;">' . htmlspecialchars($row['title']) . '</h3>
                                <div style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 1rem;">
                                    <i class="fas fa-clock"></i> ' . date('h:i A', strtotime($row['event_time'])) . ' | 
                                    <i class="fas fa-map-marker-alt"></i> ' . htmlspecialchars($row['location']) . '
                                </div>
                                <p style="font-size: 0.95rem; color: var(--text-main); margin-bottom: 1.5rem;">' . htmlspecialchars($row['description']) . '</p>
                            </div>
                            <div>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; border-top: 1px solid var(--border-color); padding-top: 1rem;">
                                    <span style="font-weight: bold; color:white;">$' . number_format($row['price'], 2) . '</span>
                                    <span style="font-size: 0.85rem; color: var(--text-muted);">' . htmlspecialchars($row['category']) . '</span>
                                </div>
                                <button onclick="' . $clickAction . '" class="btn ' . $btnClass . '" style="width: 100%; justify-content: center;" ' . $disabled . '>' . $btnText . '</button>
                            </div>
                        </div>
                        ';
                    }
                } else {
                    echo "<p>No upcoming events found.</p>";
                }
                ?>
            </div>

        </main>
    </div>

    <script>
        function registerEvent(eventId) {
            Swal.fire({
                title: 'Confirm Registration',
                text: "Do you want to register for this event?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#4285f4',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, register!',
                background: '#1e1f20',
                color: '#e3e3e3'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'registerEventLogic.php',
                        type: 'POST',
                        data: { event_id: eventId },
                        dataType: 'json',
                        success: function (response) {
                            if (response.status === 200) {
                                Swal.fire({
                                    title: 'Registered!',
                                    text: response.message,
                                    icon: 'success',
                                    background: '#1e1f20',
                                    color: '#e3e3e3'
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error!',
                                    text: response.message,
                                    icon: 'error',
                                    background: '#1e1f20',
                                    color: '#e3e3e3'
                                });
                            }
                        }
                    });
                }
            })
        }
    </script>
</body>

</html>