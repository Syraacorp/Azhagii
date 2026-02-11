<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/login.php");
    exit;
}
require_once '../config/db.php';
require_once '../includes/dashboard_header.php';

$user_id = $_SESSION['user_id'];

// Get available events (excluding already registered ones)
$avail_sql = "SELECT * FROM events 
              WHERE id NOT IN (SELECT event_id FROM registrations WHERE user_id = $user_id) 
              AND event_date > NOW() 
              ORDER BY event_date ASC";
$avail_res = $conn->query($avail_sql);

// Get my registrations
$my_sql = "SELECT r.*, e.title, e.event_date, e.location 
           FROM registrations r 
           JOIN events e ON r.event_id = e.id 
           WHERE r.user_id = $user_id 
           ORDER BY e.event_date DESC";
$my_res = $conn->query($my_sql);
?>

<div class="container">
    <div class="grid">
        <!-- Available Events -->
        <div class="card">
            <h3>Upcoming Events</h3>
            <?php if ($avail_res->num_rows > 0): ?>
                <ul style="list-style: none; margin-top: 1rem;">
                    <?php while ($event = $avail_res->fetch_assoc()): ?>
                        <li style="border-bottom: 1px solid #eee; padding: 1rem 0;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <h4>
                                        <?php echo htmlspecialchars($event['title']); ?>
                                    </h4>
                                    <small>
                                        <?php echo date('M j, Y h:i A', strtotime($event['event_date'])); ?> |
                                        <?php echo htmlspecialchars($event['location']); ?>
                                    </small>
                                </div>
                                <button onclick="registerEvent(<?php echo $event['id']; ?>)"
                                    class="btn btn-primary btn-sm">Register</button>
                            </div>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>No new upcoming events available.</p>
            <?php endif; ?>
        </div>

        <!-- My Events -->
        <div class="card">
            <h3>My Registrations</h3>
            <?php if ($my_res->num_rows > 0): ?>
                <ul style="list-style: none; margin-top: 1rem;">
                    <?php while ($reg = $my_res->fetch_assoc()): ?>
                        <li style="border-bottom: 1px solid #eee; padding: 1rem 0;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <h4>
                                        <?php echo htmlspecialchars($reg['title']); ?>
                                    </h4>
                                    <small>Status:
                                        <span
                                            style="font-weight: bold; color: <?php echo $reg['status'] === 'attended' ? 'green' : ($reg['status'] === 'cancelled' ? 'red' : 'orange'); ?>">
                                            <?php echo ucfirst($reg['status']); ?>
                                        </span>
                                    </small>
                                </div>
                                <div>
                                    <?php if ($reg['status'] === 'attended'): ?>
                                        <a href="<?php echo BASE_URL; ?>/certificate.php?id=<?php echo $reg['event_id']; ?>"
                                            target="_blank" class="btn btn-sm"
                                            style="background-color: #10b981; color: white; border: none; padding: 0.4rem 0.8rem; border-radius: 0.5rem; text-decoration: none;"><i
                                                class="fas fa-download"></i> Certificate</a>
                                    <?php elseif ($reg['status'] === 'registered'): ?>
                                        <button onclick="cancelRegistration(<?php echo $reg['id']; ?>)"
                                            class="btn btn-danger btn-sm">Cancel</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>You haven't registered for any events yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function registerEvent(eventId) {
        $.ajax({
            url: 'register_event.php',
            type: 'POST',
            data: { event_id: eventId },
            success: function (response) {
                Swal.fire('Registered!', 'You have successfully marked your spot.', 'success')
                    .then(() => location.reload());
            },
            error: function () {
                Swal.fire('Error', 'Could not register.', 'error');
            }
        });
    }

    function cancelRegistration(regId) {
        Swal.fire({
            title: 'Cancel Registration?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'cancel_registration.php',
                    type: 'POST',
                    data: { id: regId },
                    success: function (response) {
                        Swal.fire('Cancelled', 'Registration cancelled.', 'success')
                            .then(() => location.reload());
                    }
                });
            }
        })
    }
</script>

<?php require_once '../includes/dashboard_footer.php'; ?>