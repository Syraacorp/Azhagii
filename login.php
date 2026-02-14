<?php
session_start();
if (isset($_SESSION['userId'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Azhagii LMS</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Google+Sans:wght@400;500;700&family=Inter:wght@400;500;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <script>
        const savedTheme = localStorage.getItem('Azhagii-theme') || 'dark';
        document.body.setAttribute('data-theme', savedTheme);
    </script>
    <div class="auth-wrapper">
        <div class="auth-brand-side">
            <div class="auth-brand-bg"></div>
            <div class="auth-brand-content">
                <div class="logo" style="font-size:3.5rem;justify-content:center;margin-bottom:1rem;">
                    <span class="sparkle-icon" style="width:40px;height:40px;"></span> Azhagii
                </div>
                <p>Multi-College Learning Management System</p>
            </div>
        </div>
        <div class="auth-form-side">
            <a href="index.php" class="back-to-home"><i class="fas fa-arrow-left"></i> Home</a>
            <div class="auth-card">
                <div class="auth-header">
                    <h2>Welcome Back</h2>
                    <p>Sign in to continue to your dashboard</p>
                </div>
                <form id="loginForm">
                    <div class="form-group">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-input" placeholder="Enter your username"
                            required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-input" placeholder="Enter your password"
                            required>
                    </div>
                    <button type="submit" class="btn btn-primary"
                        style="width:100%;justify-content:center;margin-top:0.5rem;">
                        <i class="fas fa-sign-in-alt"></i> Sign In
                    </button>
                </form>
                <p style="text-align:center;margin-top:1.5rem;font-size:0.9rem;">
                    New student? <a href="register.php">Create an account</a>
                </p>
            </div>
        </div>
    </div>
    <script>
        $('#loginForm').submit(function (e) {
            e.preventDefault();
            const btn = $(this).find('button[type=submit]');
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Signing in...');
            $.post('backend.php', {
                login_user: 1,
                username: $('[name=username]').val(),
                password: $('[name=password]').val()
            }, function (res) {
                if (res.status === 200) {
                    Swal.fire({ icon: 'success', title: 'Welcome!', text: res.message, timer: 1500, showConfirmButton: false })
                        .then(() => window.location.href = 'dashboard.php');
                } else {
                    Swal.fire({ icon: 'error', title: 'Login Failed', text: res.message });
                    btn.prop('disabled', false).html('<i class="fas fa-sign-in-alt"></i> Sign In');
                }
            }, 'json').fail(function () {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Connection failed' });
                btn.prop('disabled', false).html('<i class="fas fa-sign-in-alt"></i> Sign In');
            });
        });
    </script>
</body>

</html>