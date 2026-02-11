<?php
require_once 'config/db.php';
require_once 'includes/header.php';

if (isset($_SESSION['user_id'])) {
    header("Location: /");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    // Select 'password' instead of 'password_hash'
    $sql = "SELECT id, username, password, role FROM users WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        // DIRECT PLAIN TEXT COMPARISON
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

<div class="container" style="max-width: 400px; margin-top: 4rem;">
    <div class="card">
        <h2 style="text-align: center; margin-bottom: 2rem;">Login</h2>
        <?php if (isset($error)): ?>
            <div style="color: red; margin-bottom: 1rem; text-align: center;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">Sign In</button>
        </form>
        <p style="text-align: center; margin-top: 1rem;">
            Don't have an account? <a href="<?php echo BASE_URL; ?>/register.php"
                style="color: var(--primary);">Register here</a>.
        </p>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>