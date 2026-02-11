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
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($registrations->num_rows > 0): ?>
                        <?php while($row = $registrations->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($row['registration_date'])); ?></td>
                            <td id="status-<?php echo $row['id']; ?>">
                                <?php 
                                    $statusClass = '';
                                    switch($row['status']) {
                                        case 'registered': $statusClass = 'color: orange;'; break;
                                        case 'attended': $statusClass = 'color: green; font-weight: bold;'; break;
                                        case 'cancelled': $statusClass = 'color: red;'; break;
                                    }
                                    echo "<span style='$statusClass'>" . ucfirst($row['status']) . "</span>";
                                ?>
                            </td>
                            <td>
                                <?php if($row['status'] !== 'attended'): ?>
                                    <button onclick="updateStatus(<?php echo $row['id']; ?>, 'attended')" class="btn btn-primary btn-sm">Mark Attended</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5">No registrations yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function updateStatus(registrationId, status) {
    $.ajax({
        url: 'update_status.php',
        type: 'POST',
        data: { id: registrationId, status: status },
        success: function(response) {
            Swal.fire({
                title: 'Updated!',
                text: 'User status marked as ' + status,
                icon: 'success',
                timer: 1500
            }).then(() => location.reload());
        }
    });
}
</script>

<?php require_once '../includes/dashboard_footer.php'; ?>
