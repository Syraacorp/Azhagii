<?php
require_once 'config/db.php';
require_once 'includes/header.php';

if (isset($_SESSION['user_id'])) {
    header("Location: /");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = 'user'; // Default role

    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if email exists
        $check = $conn->query("SELECT id FROM users WHERE email = '$email'");
        if ($check->num_rows > 0) {
            $error = "Email already registered.";
        } else {
            // PLAIN TEXT PASSWORD - NO HASHING
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

<div class="container" style="max-width: 500px; margin-top: 4rem;">
    <div class="card">
        <h2 style="text-align: center; margin-bottom: 2rem;">Create Account</h2>
        <?php if (isset($error)): ?>
            <div style="color: red; margin-bottom: 1rem; text-align: center;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Full Name</label>
                <input type="text" name="username" id="username" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">Register</button>
        </form>
        <p style="text-align: center; margin-top: 1rem;">
            Already have an account? <a href="<?php echo BASE_URL; ?>/login.php" style="color: var(--primary);">Login
                here</a>.
        </p>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>