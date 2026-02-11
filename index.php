<?php require_once 'includes/header.php'; ?>

<main>
    <section class="hero">
        <div class="container">
            <h1>Create Unforgettable Events</h1>
            <p>From planning to certification, we automate the entire process so you can focus on the content.</p>
            <div style="display: flex; gap: 1rem; justify-content: center; margin-top: 2rem;">
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <a href="<?php echo BASE_URL; ?>/register.php" class="btn btn-secondary"
                        style="background: white; color: var(--primary); border: none;">Get Started</a>
                    <a href="<?php echo BASE_URL; ?>/login.php" class="btn btn-primary"
                        style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.4);">Login</a>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?><?php echo $_SESSION['role'] === 'admin' ? '/admin/index.php' : '/user/index.php'; ?>"
                        class="btn btn-secondary" style="background: white; color: var(--primary);">Go to Dashboard</a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="features container">
        <h2 style="text-align: center; margin-bottom: 3rem; font-weight: 800; color: var(--text);">Why Choose
            EventManager?</h2>
        <div class="grid">
            <div class="card" style="text-align: center;">
                <div class="card-header">
                    <i class="fas fa-calendar-check"></i>
                    <h3 class="card-title">Easy Management</h3>
                </div>
                <p style="color: var(--text-light);">Create, edit, and manage events effortlessly. Keep track of all
                    your upcoming sessions in one place.</p>
            </div>

            <div class="card" style="text-align: center;">
                <div class="card-header">
                    <i class="fas fa-users"></i>
                    <h3 class="card-title">Seamless Registration</h3>
                </div>
                <p style="color: var(--text-light);">Allow users to register with a single click. Manage attendee lists
                    and track participation in real-time.</p>
            </div>

            <div class="card" style="text-align: center;">
                <div class="card-header">
                    <i class="fas fa-certificate"></i>
                    <h3 class="card-title">Auto-Certificates</h3>
                </div>
                <p style="color: var(--text-light);">Automatically generate and distribute professional certificates to
                    attendees upon event completion.</p>
            </div>
        </div>
    </section>
</main>

<?php require_once 'includes/footer.php'; ?>