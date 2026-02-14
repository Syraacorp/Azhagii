<?php
$pageTitle = 'Profile Unlock Requests';
$currentPage = 'profileRequestSSR';
require 'includes/auth.php';
requirePageRole(['superAdmin', 'adminAzhagii']);

// Fetch Profile Requests
$q = "SELECT pr.*, u.name as user_name, u.email as user_email, c.name as college_name 
      FROM profilerequests pr 
      JOIN users u ON pr.userId=u.id 
      LEFT JOIN colleges c ON u.collegeId=c.id 
      WHERE pr.status='pending' 
      ORDER BY pr.createdAt DESC";
$requests = [];
$r = mysqli_query($conn, $q);
while ($r && $row = mysqli_fetch_assoc($r)) {
    $requests[] = $row;
}

require 'includes/header.php';
require 'includes/sidebar.php';
?>

<div class="header-actions section-toolbar" style="margin-bottom:1.5rem;">
    <div>
        <h2>Access Requests</h2>
        <p>Manage user requests to unlock and edit their profiles.</p>
    </div>
    <button class="btn btn-outline btn-sm" onclick="location.reload()">
        <i class="fas fa-sync-alt"></i> Refresh
    </button>
</div>

<!-- Requests Grid -->
<div class="requests-grid" id="requestsContainer">
    <?php if (empty($requests)): ?>
        <div class="empty-state" style="grid-column: 1/-1;">
            <i class="fas fa-check-circle" style="color:var(--accent-blue);opacity:1;"></i>
            <p>No pending requests.</p>
        </div>
    <?php else: ?>
        <?php foreach ($requests as $req):
            $initial = strtoupper(substr($req['user_name'], 0, 1));
            ?>
            <div class="request-card" id="req-<?= $req['id'] ?>">
                <div class="req-header">
                    <div class="req-avatar"><?= $initial ?></div>
                    <div class="req-info">
                        <h4><?= htmlspecialchars($req['user_name']) ?></h4>
                        <p><?= htmlspecialchars($req['college_name'] ?? 'Unknown College') ?></p>
                        <p style="font-size:0.75rem;"><?= htmlspecialchars($req['user_email']) ?></p>
                    </div>
                </div>

                <div class="req-meta">
                    <span><i class="far fa-clock"></i> <?= date('M d, Y H:i:s', strtotime($req['createdAt'])) ?></span>
                </div>

                <div class="req-reason">
                    <strong>Reason:</strong><br>
                    <?= nl2br(htmlspecialchars($req['requestReason'])) ?>
                </div>

                <div class="req-actions">
                    <button class="btn btn-sm btn-approve" onclick="resolveRequest(<?= $req['id'] ?>, 'approve')">
                        <i class="fas fa-check"></i> Approve
                    </button>
                    <button class="btn btn-sm btn-reject" onclick="resolveRequest(<?= $req['id'] ?>, 'reject')">
                        <i class="fas fa-times"></i> Reject
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<style>
    .requests-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem;
    }

    .request-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 1.5rem;
        transition: all 0.2s;
        display: flex;
        flex-direction: column;
    }

    .request-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-soft);
        border-color: var(--accent-blue);
    }

    .req-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .req-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: var(--primary-gradient);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.2rem;
    }

    .req-info h4 {
        margin: 0;
        font-size: 1rem;
        color: var(--text-heading);
    }

    .req-info p {
        margin: 0;
        font-size: 0.85rem;
        color: var(--text-muted);
    }

    .req-reason {
        background: var(--bg-body);
        padding: 1rem;
        border-radius: var(--radius-sm);
        border: 1px solid var(--border-color);
        margin-bottom: 1.5rem;
        font-size: 0.9rem;
        color: var(--text-main);
        flex: 1;
    }

    .req-meta {
        font-size: 0.8rem;
        color: var(--text-muted);
        margin-bottom: 0.5rem;
        display: flex;
        justify-content: space-between;
    }

    .req-actions {
        display: flex;
        gap: 0.75rem;
    }

    .btn-approve {
        flex: 1;
        justify-content: center;
        background: rgba(16, 185, 129, 0.1);
        color: #34d399;
        border: 1px solid rgba(16, 185, 129, 0.2);
    }

    .btn-approve:hover {
        background: #34d399;
        color: #fff;
    }

    .btn-reject {
        flex: 1;
        justify-content: center;
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
        border: 1px solid rgba(239, 68, 68, 0.2);
    }

    .btn-reject:hover {
        background: #ef4444;
        color: #fff;
    }
</style>

<script>
    function resolveRequest(id, action) {
        const actionText = action === 'approve' ? 'Approve' : 'Reject';
        const confirmBtnColor = action === 'approve' ? '#10b981' : '#ef4444';

        Swal.fire({
            title: `Confirm ${actionText}?`,
            text: action === 'approve' ? "User will be able to edit their profile immediately." : "User will be notified of rejection.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: confirmBtnColor,
            confirmButtonText: `Yes, ${actionText}`
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('backend.php', { resolve_profile_request: 1, request_id: id, action: action }, function (res) {
                    if (res.status === 200) {
                        Swal.fire('Success', `Request ${action}d successfully`, 'success');
                        $(`#req-${id}`).fadeOut();
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                }, 'json');
            }
        });
    }
</script>

<?php require 'includes/footer.php'; ?>