<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/login.php");
    exit;
}
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

// Stats
$total_registered = $conn->query("SELECT COUNT(*) as c FROM registrations WHERE user_id = $user_id AND status='registered'")->fetch_assoc()['c'];
$total_completed = $conn->query("SELECT COUNT(*) as c FROM registrations WHERE user_id = $user_id AND status='attended'")->fetch_assoc()['c'];
$total_cancelled = $conn->query("SELECT COUNT(*) as c FROM registrations WHERE user_id = $user_id AND status='cancelled'")->fetch_assoc()['c'];
?>

<div class="container">
    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon orange"><i class="fas fa-clipboard-list"></i></div>
            <div class="stat-info">
                <h3><?php echo $total_registered; ?></h3>
                <p>Registered</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-check-double"></i></div>
            <div class="stat-info">
                <h3><?php echo $total_completed; ?></h3>
                <p>Completed</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon purple"><i class="fas fa-ban"></i></div>
            <div class="stat-info">
                <h3><?php echo $total_cancelled; ?></h3>
                <p>Cancelled</p>
            </div>
        </div>
    </div>

    <div class="grid">
        <!-- Available Events -->
        <div class="card">
            <h3><i class="fas fa-calendar-alt" style="color: var(--primary); margin-right: 0.5rem;"></i> Upcoming Events</h3>
            <?php if ($avail_res->num_rows > 0): ?>
                <ul class="event-list">
                    <?php while ($event = $avail_res->fetch_assoc()): ?>
                        <li>
                            <div class="event-item">
                                <div class="event-item-info">
                                    <h4><?php echo htmlspecialchars($event['title']); ?></h4>
                                    <small>
                                        <i class="fas fa-calendar"></i> <?php echo date('M j, Y h:i A', strtotime($event['event_date'])); ?>
                                        <br>
                                        <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?>
                                    </small>
                                </div>
                                <div class="event-item-actions">
                                    <button onclick="registerEvent(<?php echo $event['id']; ?>)"
                                        class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Register</button>
                                </div>
                            </div>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-check"></i>
                    <p>No new upcoming events available.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- My Events -->
        <div class="card">
            <h3><i class="fas fa-bookmark" style="color: var(--primary); margin-right: 0.5rem;"></i> My Registrations</h3>
            <?php if ($my_res->num_rows > 0): ?>
                <ul class="event-list">
                    <?php while ($reg = $my_res->fetch_assoc()): ?>
                        <li>
                            <div class="event-item" style="align-items: flex-start;">
                                <div class="event-item-info">
                                    <h4><?php echo htmlspecialchars($reg['title']); ?></h4>
                                    <small>
                                        Status:
                                        <?php
                                        $badgeClass = '';
                                        $statusLabel = ucfirst($reg['status']);
                                        switch ($reg['status']) {
                                            case 'registered':
                                                $badgeClass = 'badge-registered';
                                                break;
                                            case 'attended':
                                                $badgeClass = 'badge-completed';
                                                $statusLabel = 'Completed';
                                                break;
                                            case 'cancelled':
                                                $badgeClass = 'badge-cancelled';
                                                break;
                                        }
                                        ?>
                                        <span class="badge <?php echo $badgeClass; ?>"><?php echo $statusLabel; ?></span>
                                    </small>

                                    <?php if (!empty($reg['feedback'])): ?>
                                        <div class="feedback-block">
                                            <strong><i class="fas fa-comment-dots"></i> Coordinator Feedback</strong>
                                            <?php echo nl2br(htmlspecialchars($reg['feedback'])); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="event-item-actions">
                                    <?php if ($reg['status'] === 'attended'): ?>
                                        <a href="<?php echo BASE_URL; ?>/certificate.php?id=<?php echo $reg['event_id']; ?>"
                                            target="_blank" class="btn btn-success btn-sm">
                                            <i class="fas fa-download"></i> Certificate</a>
                                    <?php elseif ($reg['status'] === 'registered'): ?>
                                        <button onclick="cancelRegistration(<?php echo $reg['id']; ?>)"
                                            class="btn btn-danger btn-sm"><i class="fas fa-times"></i> Cancel</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-folder-open"></i>
                    <p>You haven't registered for any events yet.</p>
                </div>
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
                Swal.fire({
                    title: 'Registered!',
                    text: 'You have successfully marked your spot.',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                }).then(function() { location.reload(); });
            },
            error: function () {
                Swal.fire('Error', 'Could not register. Please try again.', 'error');
            }
        });
    }

    function cancelRegistration(regId) {
        Swal.fire({
            title: 'Cancel Registration?',
            text: 'Are you sure you want to cancel this registration?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: '<i class="fas fa-times"></i> Yes, cancel it',
            cancelButtonText: 'Keep it'
        }).then(function(result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'cancel_registration.php',
                    type: 'POST',
                    data: { id: regId },
                    success: function (response) {
                        Swal.fire({
                            title: 'Cancelled',
                            text: 'Registration cancelled successfully.',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(function() { location.reload(); });
                    }
                });
            }
        });
    }
</script>

<?php require_once '../includes/dashboard_footer.php'; ?>