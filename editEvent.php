<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
require_once 'db.php';

if (!isset($_GET['id'])) {
    header("Location: manageEvents.php");
    exit();
}

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$event = $result->fetch_assoc();

if (!$event) {
    header("Location: manageEvents.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event - Admin</title>
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
                <a href="manageEvents.php" class="active">
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
                    <h2 style="font-size: 1.25rem; margin: 0;">Edit Event</h2>
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

            <div class="card"
                style="margin-bottom: 2rem; background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1.5rem;">
                <h3 style="margin-top: 0; margin-bottom: 1.5rem;">Update Event Details</h3>
                <form action="eventActions.php" method="POST">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" value="<?php echo $event['id']; ?>">

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1rem;">
                        <div class="form-group">
                            <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">Event
                                Title</label>
                            <input type="text" name="title" class="form-input"
                                value="<?php echo htmlspecialchars($event['title']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">Date</label>
                            <input type="date" name="event_date" class="form-input"
                                value="<?php echo htmlspecialchars($event['event_date']); ?>" required>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem; margin-bottom: 1rem;">
                        <div class="form-group">
                            <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">Time</label>
                            <input type="time" name="event_time" class="form-input"
                                value="<?php echo htmlspecialchars($event['event_time']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label
                                style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">Location</label>
                            <input type="text" name="location" class="form-input"
                                value="<?php echo htmlspecialchars($event['location']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label
                                style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">Category</label>
                            <select name="category" class="form-input">
                                <?php
                                $categories = ["Workshop", "Seminar", "Webinar", "Hackathon", "Cultural"];
                                foreach ($categories as $cat) {
                                    $selected = ($cat == $event['category']) ? 'selected' : '';
                                    echo "<option value='$cat' $selected>$cat</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label
                            style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">Description</label>
                        <textarea name="description" class="form-input"
                            rows="3"><?php echo htmlspecialchars($event['description']); ?></textarea>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                        <div class="form-group">
                            <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">Price
                                ($)</label>
                            <input type="number" name="price" class="form-input" step="0.01"
                                value="<?php echo htmlspecialchars($event['price']); ?>">
                        </div>
                        <div class="form-group">
                            <label
                                style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">Capacity</label>
                            <input type="number" name="capacity" class="form-input"
                                value="<?php echo htmlspecialchars($event['capacity']); ?>">
                        </div>
                        <div class="form-group">
                            <label
                                style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">Status</label>
                            <select name="status" class="form-input">
                                <option value="upcoming" <?php echo ($event['status'] == 'upcoming') ? 'selected' : ''; ?>
                                    >Upcoming</option>
                                <option value="completed" <?php echo ($event['status'] == 'completed') ? 'selected' : ''; ?>
                                    >Completed</option>
                                <option value="cancelled" <?php echo ($event['status'] == 'cancelled') ? 'selected' : ''; ?>
                                    >Cancelled</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Update Event</button>
                    <a href="manageEvents.php" class="btn btn-outline" style="margin-left: 1rem;">Cancel</a>
                </form>
            </div>

        </main>
    </div>

</body>

</html>