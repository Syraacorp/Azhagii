<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/login.php");
    exit;
}
require_once '../config/db.php';
require_once '../includes/dashboard_header.php';

// Fetch events
$sql = "SELECT * FROM events ORDER BY event_date DESC";
$result = $conn->query($sql);
?>

<div class="container">
    <div style="display: flex; justify-content: flex-end; align-items: center; margin-bottom: 2rem;">
        <a href="create_event.php" class="btn btn-primary"><i class="fas fa-plus"></i> Create New Event</a>
    </div>

    <div class="card">
        <h3>All Events</h3>
        <div class="table-container">
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
                            // Get users count
                            $eid = $row['id'];
                            $count_sql = "SELECT COUNT(*) as count FROM registrations WHERE event_id = $eid";
                            $count_res = $conn->query($count_sql);
                            $count = $count_res->fetch_assoc()['count'];
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['title']); ?></td>
                                <td><?php echo date('F j, Y, g:i a', strtotime($row['event_date'])); ?></td>
                                <td><?php echo htmlspecialchars($row['location']); ?></td>
                                <td><?php echo $count; ?> /
                                    <?php echo $row['max_participants'] == 0 ? 'â' : $row['max_participants']; ?>
                                </td>
                                <td>
                                    <a href="event_details.php?id=<?php echo $row['id']; ?>" class="btn btn-secondary btn-sm"><i
                                            class="fas fa-eye"></i> View</a>
                                    <button onclick="deleteEvent(<?php echo $row['id']; ?>)" class="btn btn-danger btn-sm"
                                        style="padding: 0.4rem 0.8rem;"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center;">No events found.</td>
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
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'delete_event.php',
                    type: 'POST',
                    data: { id: id },
                    success: function (response) {
                        Swal.fire('Deleted!', 'Event has been deleted.', 'success')
                            .then(() => location.reload());
                    },
                    error: function () {
                        Swal.fire('Error!', 'Something went wrong.', 'error');
                    }
                });
            }
        })
    }
</script>

<?php require_once '../includes/dashboard_footer.php'; ?>