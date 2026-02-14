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
    <link href="https://fonts.googleapis.com/css2?family=Google+Sans:wght@400;500;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
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
                    <h2>Welcome to Ziya</h2>
                    <p>Sign in to continue to your dashboard</p>
                </div>
                
                <form id="loginFormLocal">
                    <div class="form-group">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-input" name="username" placeholder="Enter username" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-input" name="password" placeholder="Enter password" required>
                        <div style="text-align:right;margin-top:0.5rem;">
                            <a href="#" class="forgot-password">Forgot Password?</a>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width:100%;margin-top:1rem;height:48px;font-size:1rem;">
                        Sign In
                    </button>
                </form>

                <div class="auth-footer">
                    Don't have an account? <a href="register.php">Create Account</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Self-contained login logic
            $('#loginFormLocal').on('submit', function(e) {
                e.preventDefault();
                
                const btn = $(this).find('button[type="submit"]');
                const originalText = btn.html();
                const username = $(this).find('input[name="username"]').val().trim();
                const password = $(this).find('input[name="password"]').val();

                if(!username || !password) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Missing Input',
                        text: 'Please enter both username and password.',
                        confirmButtonColor: '#4285f4'
                    });
                    return;
                }

                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Signing In...');

                $.post('backend.php', {
                    login_user: 1,
                    username: username,
                    password: password
                }, function(res) {
                    if (res.status === 200) {
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end', // Spark style toast
                            showConfirmButton: false,
                            timer: 1500,
                            timerProgressBar: true
                        });
                        Toast.fire({
                            icon: 'success',
                            title: 'Signed in successfully'
                        }).then(() => {
                            window.location.href = 'dashboard.php';
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Login Failed',
                            text: res.message || 'Invalid credentials',
                            confirmButtonColor: '#4285f4'
                        });
                        btn.prop('disabled', false).html(originalText);
                    }
                }, 'json')
                .fail(function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Connection Error',
                        text: 'Unable to connect to the server.',
                        confirmButtonColor: '#4285f4'
                    });
                    btn.prop('disabled', false).html(originalText);
                });
            });
        });
    </script>
</body>
</html>