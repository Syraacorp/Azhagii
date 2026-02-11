<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/login.php");
    exit;
}
require_once '../includes/dashboard_header.php';

$event_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$event_res = $conn->query("SELECT * FROM events WHERE id = $event_id");
$event = $event_res->fetch_assoc();

if (!$event) {
    echo '<div class="container"><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> Event not found.</div>';
    echo '<a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Events</a></div>';
    require_once '../includes/dashboard_footer.php';
    exit;
}

// Fetch Registrations
$reg_sql = "SELECT r.*, u.username, u.email 
            FROM registrations r 
            JOIN users u ON r.user_id = u.id 
            WHERE r.event_id = $event_id";
$registrations = $conn->query($reg_sql);
$reg_count = $registrations->num_rows;
?>

<div class="container">
    <a href="index.php" class="btn btn-secondary btn-sm" style="margin-bottom: 1.5rem;">
        <i class="fas fa-arrow-left"></i> Back to Events
    </a>

    <div class="card">
        <h2 style="margin-bottom: 0.5rem;"><?php echo htmlspecialchars($event['title']); ?></h2>

        <div class="event-info">
            <span><i class="fas fa-calendar"></i> <?php echo date('F j, Y, g:i a', strtotime($event['event_date'])); ?></span>
            <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?></span>
            <span><i class="fas fa-users"></i> <?php echo $reg_count; ?> Registered</span>
        </div>

        <?php if (!empty($event['description'])): ?>
            <p class="text-muted" style="margin-bottom: 2rem;"><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
        <?php endif; ?>

        <h3><i class="fas fa-user-check" style="color: var(--primary); margin-right: 0.5rem;"></i> Registered Users</h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Participant Name</th>
                        <th>Email</th>
                        <th>Registration Date</th>
                        <th>Status</th>
                        <th>Feedback</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($reg_count > 0): ?>
                        <?php
                        // Reset pointer
                        $registrations->data_seek(0);
                        while ($row = $registrations->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($row['username']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo date('M j, Y', strtotime($row['registration_date'])); ?></td>
                                <td id="status-<?php echo $row['id']; ?>">
                                    <?php
                                    $badgeClass = '';
                                    $statusLabel = ucfirst($row['status']);
                                    switch ($row['status']) {
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
                                </td>
                                <td><?php echo htmlspecialchars($row['feedback'] ?? '-'); ?></td>
                                <td>
                                    <?php if ($row['status'] !== 'attended'): ?>
                                        <button onclick="markCompleted(<?php echo $row['id']; ?>)"
                                            class="btn btn-primary btn-sm"><i class="fas fa-check"></i> Complete</button>
                                    <?php else: ?>
                                        <button onclick="markCompleted(<?php echo $row['id']; ?>, '<?php echo addslashes($row['feedback'] ?? ''); ?>')"
                                            class="btn btn-secondary btn-sm"><i class="fas fa-edit"></i> Edit</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <i class="fas fa-user-slash"></i>
                                    <p>No registrations yet.</p>
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
function markCompleted(registrationId, currentFeedback) {
    currentFeedback = currentFeedback || '';
    Swal.fire({
        title: 'Mark Event Completed',
        text: 'Provide feedback to the participant:',
        input: 'textarea',
        inputLabel: 'Feedback',
        inputValue: currentFeedback,
        inputPlaceholder: 'Great participation! ...',
        showCancelButton: true,
        confirmButtonColor: '#4f46e5',
        cancelButtonColor: '#64748b',
        confirmButtonText: '<i class="fas fa-check"></i> Save & Mark Completed',
        showLoaderOnConfirm: true,
        preConfirm: function(feedback) {
            return feedback;
        }
    }).then(function(result) {
        if (result.isConfirmed) {
            $.ajax({
                url: 'update_status.php',
                type: 'POST',
                data: { 
                    id: registrationId, 
                    status: 'attended', 
                    feedback: result.value 
                },
                success: function(response) {
                    Swal.fire({
                        title: 'Updated!',
                        text: 'User marked as Completed.',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(function() { location.reload(); });
                },
                error: function() {
                    Swal.fire('Error', 'Could not update status.', 'error');
                }
            });
        }
    });
}
</script>

<?php require_once '../includes/dashboard_footer.php'; ?>