<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/login.php");
    exit;
}
require_once '../includes/dashboard_header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $date = $_POST['date'];
    $location = $conn->real_escape_string($_POST['location']);
    $max_participants = intval($_POST['max_participants']);

    $sql = "INSERT INTO events (title, description, event_date, location, max_participants) 
            VALUES ('$title', '$description', '$date', '$location', $max_participants)";

    if ($conn->query($sql)) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Success!',
                    text: 'Event created successfully',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                }).then(function() {
                    window.location.href = 'index.php';
                });
            });
        </script>";
    } else {
        $error = "Error: " . $conn->error;
    }
}
?>

<div class="container">
    <div class="card" style="max-width: 800px; margin: 0 auto;">
        <h2><i class="fas fa-plus-circle" style="color: var(--primary); margin-right: 0.5rem;"></i> Create New Event</h2>

        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" id="createEventForm">
            <div class="form-group">
                <label for="title"><i class="fas fa-heading" style="margin-right: 0.25rem; color: var(--text-light);"></i> Event Title</label>
                <input type="text" name="title" id="title" class="form-control" placeholder="Enter event title" required
                    value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="description"><i class="fas fa-align-left" style="margin-right: 0.25rem; color: var(--text-light);"></i> Description</label>
                <textarea name="description" id="description" class="form-control" rows="4" placeholder="Describe the event..." required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
            </div>

            <div class="form-group">
                <label for="date"><i class="fas fa-calendar" style="margin-right: 0.25rem; color: var(--text-light);"></i> Date &amp; Time</label>
                <input type="datetime-local" name="date" id="date" class="form-control" required
                    value="<?php echo isset($_POST['date']) ? htmlspecialchars($_POST['date']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="location"><i class="fas fa-map-marker-alt" style="margin-right: 0.25rem; color: var(--text-light);"></i> Location</label>
                <input type="text" name="location" id="location" class="form-control" placeholder="e.g. Online (Zoom) or Conference Hall" required
                    value="<?php echo isset($_POST['location']) ? htmlspecialchars($_POST['location']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="max_participants"><i class="fas fa-user-friends" style="margin-right: 0.25rem; color: var(--text-light);"></i> Max Participants <small class="text-muted">(0 for unlimited)</small></label>
                <input type="number" name="max_participants" id="max_participants" class="form-control" value="0" min="0">
            </div>

            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Create Event</button>
                <a href="index.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/dashboard_footer.php'; ?>