<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/login.php");
    exit;
}
require_once '../config/db.php';
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
            Swal.fire({
                title: 'Success!',
                text: 'Event created successfully',
                icon: 'success'
            }).then(() => {
                window.location.href = 'index.php';
            });
        </script>";
    } else {
        $error = "Error: " . $conn->error;
    }
}
?>

<div class="container">
    <div class="card" style="max-width: 800px; margin: 0 auto;">
        <h2>Create New Event</h2>
        <form method="POST" action="" id="createEventForm">
            <div class="form-group">
                <label>Event Title</label>
                <input type="text" name="title" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="4" required></textarea>
            </div>

            <div class="form-group">
                <label>Date & Time</label>
                <input type="datetime-local" name="date" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Location</label>
                <input type="text" name="location" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Max Participants (0 for unlimited)</label>
                <input type="number" name="max_participants" class="form-control" value="0">
            </div>

            <button type="submit" class="btn btn-primary">Create Event</button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>

<?php require_once '../includes/dashboard_footer.php'; ?>