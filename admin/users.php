<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/login.php");
    exit;
}
require_once '../includes/dashboard_header.php';

// Fetch users
$sql = "SELECT * FROM users ORDER BY created_at DESC";
$result = $conn->query($sql);
$user_count = $result->num_rows;
?>

<div class="container">
    <div class="dashboard-top-bar">
        <h3 style="margin: 0;">All Users <span class="badge badge-user" style="margin-left: 0.5rem;"><?php echo $user_count; ?></span></h3>
    </div>

    <div class="card" style="padding: 0; overflow: hidden;">
        <div class="table-container" style="border: none;">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Joined Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($row['username']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td>
                                <span class="badge <?php echo $row['role'] === 'admin' ? 'badge-admin' : 'badge-user'; ?>">
                                    <?php echo ucfirst($row['role']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M j, Y', strtotime($row['created_at'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <i class="fas fa-user-slash"></i>
                                    <p>No users found.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/dashboard_footer.php'; ?>
