<?php
require_once 'config/db.php';
require_once 'includes/header.php';

if (isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT id, username, password, role FROM users WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if ($password === $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header("Location: " . BASE_URL . "/admin/index.php");
            } else {
                header("Location: " . BASE_URL . "/user/index.php");
            }
            exit;
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "User not found.";
    }
}
?>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="card">
            <h2><i class="fas fa-sign-in-alt" style="color: var(--primary); margin-right: 0.5rem;"></i> Login</h2>
            <p class="auth-subtitle">Welcome back! Sign in to your account.</p>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope" style="margin-right: 0.25rem; color: var(--text-light);"></i> Email Address</label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="you@example.com" required
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock" style="margin-right: 0.25rem; color: var(--text-light);"></i> Password</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-sign-in-alt"></i> Sign In</button>
            </form>
            <p class="auth-footer">
                Don't have an account? <a href="<?php echo BASE_URL; ?>/register.php">Register here</a>
            </p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>