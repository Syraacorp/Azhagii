<?php
require_once 'config/db.php';
require_once 'includes/header.php';
?>

<main>
    <div class="container" style="max-width: 700px; margin-top: 2rem;">
        <div class="card">
            <h2><i class="fas fa-heartbeat" style="color: var(--primary); margin-right: 0.5rem;"></i> Environment Status Check</h2>

            <?php
            // Check Database Connection
            if ($conn->connect_error) {
                echo '<div class="alert alert-error"><i class="fas fa-times-circle"></i> <strong>Database Connection:</strong> ' . $conn->connect_error . '</div>';
            } else {
                echo '<div class="alert alert-success"><i class="fas fa-check-circle"></i> <strong>Database Connection:</strong> Established successfully.</div>';
            }

            // Check GD Library
            if (extension_loaded('gd')) {
                $gd_info = gd_info();
                echo '<div class="alert alert-success"><i class="fas fa-check-circle"></i> <strong>GD Library:</strong> Enabled (v' . $gd_info['GD Version'] . ', FreeType: ' . ($gd_info['FreeType Support'] ? 'Yes' : 'No') . ')</div>';
            } else {
                echo '<div class="alert alert-error"><i class="fas fa-times-circle"></i> <strong>GD Library:</strong> NOT enabled. Certificate generation will fail.</div>';
            }

            // Check Write Permissions
            if (is_writable(__DIR__)) {
                echo '<div class="alert alert-success"><i class="fas fa-check-circle"></i> <strong>Root Directory:</strong> Writable.</div>';
            } else {
                echo '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> <strong>Root Directory:</strong> May not be writable.</div>';
            }
            ?>

            <div style="display: flex; gap: 1rem; margin-top: 1.5rem; flex-wrap: wrap;">
                <a href="<?php echo BASE_URL; ?>/setup.php" class="btn btn-primary"><i class="fas fa-database"></i> Run Setup</a>
                <a href="<?php echo BASE_URL; ?>/" class="btn btn-secondary"><i class="fas fa-home"></i> Go Home</a>
            </div>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>