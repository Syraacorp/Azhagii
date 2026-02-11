<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/login.php");
    exit;
}
require_once '../includes/dashboard_header.php';

// Fetch stats
$total_events = $conn->query("SELECT COUNT(*) as c FROM events")->fetch_assoc()['c'];
$total_users = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='user'")->fetch_assoc()['c'];
$total_registrations = $conn->query("SELECT COUNT(*) as c FROM registrations WHERE status='registered'")->fetch_assoc()['c'];
$total_completed = $conn->query("SELECT COUNT(*) as c FROM registrations WHERE status='attended'")->fetch_assoc()['c'];

// Fetch events
$sql = "SELECT * FROM events ORDER BY event_date DESC";
$result = $conn->query($sql);
?>

<div class="container">
    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-calendar-alt"></i></div>
            <div class="stat-info">
                <h3><?php echo $total_events; ?></h3>
                <p>Total Events</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-users"></i></div>
            <div class="stat-info">
                <h3><?php echo $total_users; ?></h3>
                <p>Registered Users</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange"><i class="fas fa-clipboard-list"></i></div>
            <div class="stat-info">
                <h3><?php echo $total_registrations; ?></h3>
                <p>Active Registrations</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon purple"><i class="fas fa-check-double"></i></div>
            <div class="stat-info">
                <h3><?php echo $total_completed; ?></h3>
                <p>Completed</p>
            </div>
        </div>
    </div>

    <div class="dashboard-top-bar">
        <h3 style="margin: 0;">All Events</h3>
        <a href="create_event.php" class="btn btn-primary"><i class="fas fa-plus"></i> Create New Event</a>
    </div>

    <div class="card" style="padding: 0; overflow: hidden;">
        <div class="table-container" style="border: none;">
            <table>
                <thead>
                    <tr>
                        <th>Event Title</th>
                        <th>Date</th>
                        <th>Location</th>
                        <th>Participants</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()):
                            $eid = $row['id'];
                            $count_res = $conn->query("SELECT COUNT(*) as count FROM registrations WHERE event_id = $eid");
                            $count = $count_res->fetch_assoc()['count'];
                            $max = $row['max_participants'];
                            ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($row['title']); ?></strong></td>
                                <td><?php echo date('M j, Y, g:i a', strtotime($row['event_date'])); ?></td>
                                <td><?php echo htmlspecialchars($row['location']); ?></td>
                                <td>
                                    <span class="badge <?php echo ($max > 0 && $count >= $max) ? 'badge-cancelled' : 'badge-registered'; ?>">
                                        <?php echo $count; ?> / <?php echo $max == 0 ? '&infin;' : $max; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="event_details.php?id=<?php echo $row['id']; ?>" class="btn btn-secondary btn-sm">
                                        <i class="fas fa-eye"></i> View</a>
                                    <button onclick="deleteEvent(<?php echo $row['id']; ?>)" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <i class="fas fa-calendar-plus"></i>
                                    <p>No events found. Create your first event!</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function deleteEvent(id) {
        Swal.fire({
            title: 'Delete Event?',
            text: "This will permanently remove the event and all registrations.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: '<i class="fas fa-trash"></i> Yes, delete it',
            cancelButtonText: 'Cancel'
        }).then(function(result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'delete_event.php',
                    type: 'POST',
                    data: { id: id },
                    success: function (response) {
                        Swal.fire({
                            title: 'Deleted!',
                            text: 'Event has been deleted.',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(function() { location.reload(); });
                    },
                    error: function () {
                        Swal.fire('Error!', 'Something went wrong.', 'error');
                    }
                });
            }
        });
    }
</script>

<?php require_once '../includes/dashboard_footer.php'; ?>