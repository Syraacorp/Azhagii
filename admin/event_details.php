<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/login.php");
    exit;
}
require_once '../config/db.php';
require_once '../includes/dashboard_header.php';

$event_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$event_res = $conn->query("SELECT * FROM events WHERE id = $event_id");
$event = $event_res->fetch_assoc();

if (!$event) {
    die("Event not found");
}

// Fetch Registrations
$reg_sql = "SELECT r.*, u.username, u.email 
            FROM registrations r 
            JOIN users u ON r.user_id = u.id 
            WHERE r.event_id = $event_id";
$registrations = $conn->query($reg_sql);
?>

<div class="container">
    <div class="card">
        <h2><?php echo htmlspecialchars($event['title']); ?> - Manage Attendees</h2>
        <p><strong>Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($event['event_date'])); ?></p>
        <p><strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?></p>

        <h3 style="margin-top: 2rem;">Registered Users</h3>
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
                    <?php if ($registrations->num_rows > 0): ?>
                        <?php while ($row = $registrations->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo date('M j, Y', strtotime($row['registration_date'])); ?></td>
                                <td id="status-<?php echo $row['id']; ?>">
                                    <?php
                                    $statusClass = '';
                                    switch ($row['status']) {
                                        case 'registered':
                                            $statusClass = 'color: orange;';
                                            break;
                                        case 'attended':
                                            $statusClass = 'color: green; font-weight: bold;';
                                            break; // 'Attended' implies 'Completed'
                                        case 'cancelled':
                                            $statusClass = 'color: red;';
                                            break;
                                    }
                                    echo "<span style='$statusClass'>" . ($row['status'] === 'attended' ? 'Completed' : ucfirst($row['status'])) . "</span>";
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['feedback'] ?: '-'); ?></td>
                                <td>
                                    <?php if ($row['status'] !== 'attended'): ?>
                                        <button onclick="markCompleted(<?php echo $row['id']; ?>)"
                                            class="btn btn-primary btn-sm">Mark Completed</button>
                                    <?php else: ?>
                                        <button
                                            onclick="markCompleted(<?php echo $row['id']; ?>, '<?php echo addslashes($row['feedback']); ?>')"
                                            class="btn btn-secondary btn-sm" style="font-size: 0.8rem;">Edit Feedback</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No registrations yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function markCompleted(registrationId, currentFeedback = '') {
    Swal.fire({
        title: 'Mark Event Completed',
        text: 'Provide feedback to the participant:',
        input: 'textarea',
        inputLabel: 'Feedback',
        inputValue: currentFeedback,
        inputPlaceholder: 'Great participation! ...',
        showCancelButton: true,
        confirmButtonText: 'Save & Mark Completed',
        showLoaderOnConfirm: true,
        preConfirm: (feedback) => {
            // Optional: validation
            // if (!feedback) {
            //    Swal.showValidationMessage('Feedback is required')
            // }
            return feedback;
        }
    }).then((result) => {
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
                        timer: 1500
                    }).then(() => location.reload());
                },
                error: function() {
                    Swal.fire('Error', 'Could not update status.', 'error');
                }
            });
        }
    })
}
</script>

<?php require_once '../includes/dashboard_footer.php'; ?>