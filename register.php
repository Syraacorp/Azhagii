<?php
require_once 'config/db.php';
require_once 'includes/header.php';

if (isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = 'user';

    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        $check = $conn->query("SELECT id FROM users WHERE email = '$email'");
        if ($check->num_rows > 0) {
            $error = "Email already registered.";
        } else {
            $sql = "INSERT INTO users (username, email, password, role) VALUES ('$username', '$email', '$password', '$role')";

            if ($conn->query($sql)) {
                $_SESSION['success'] = "Registration successful! Please login.";
                header("Location: " . BASE_URL . "/login.php");
                exit;
            } else {
                $error = "Error: " . $conn->error;
            }
        }
    }
}
?>

<div class="auth-wrapper">
    <div class="auth-card" style="max-width: 480px;">
        <div class="card">
            <h2><i class="fas fa-user-plus" style="color: var(--primary); margin-right: 0.5rem;"></i> Create Account</h2>
            <p class="auth-subtitle">Join us and start managing your events.</p>

            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="username"><i class="fas fa-user" style="margin-right: 0.25rem; color: var(--text-light);"></i> Full Name</label>
                    <input type="text" name="username" id="username" class="form-control" placeholder="John Doe" required
                        value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope" style="margin-right: 0.25rem; color: var(--text-light);"></i> Email Address</label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="you@example.com" required
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock" style="margin-right: 0.25rem; color: var(--text-light);"></i> Password</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Min 6 characters" required minlength="6">
                </div>
                <div class="form-group">
                    <label for="confirm_password"><i class="fas fa-lock" style="margin-right: 0.25rem; color: var(--text-light);"></i> Confirm Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Re-enter password" required minlength="6">
                </div>
                <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-user-plus"></i> Register</button>
            </form>
            <p class="auth-footer">
                Already have an account? <a href="<?php echo BASE_URL; ?>/login.php">Login here</a>
            </p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>