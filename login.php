<?php
require_once 'db.php';
session_start();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Ziya</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Google+Sans:wght@400;500;700&family=Inter:wght@400;500;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .swal2-popup {
            background: #1e1f20 !important;
            color: #e3e3e3 !important;
            border: 1px solid #444746 !important;
        }

        .swal2-title {
            color: #e3e3e3 !important;
        }

        .swal2-timer-progress-bar {
            background: linear-gradient(90deg, #4285f4, #9b72cb, #d96570) !important;
        }
    </style>
    </style>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>

<body>

    <div class="auth-wrapper">
        <!-- Left Side: Brand -->
        <div class="auth-brand-side">
            <div class="auth-brand-bg"></div>
            <div class="auth-brand-content">
                <div class="logo">
                    <span class="sparkle-icon"></span> Ziya
                </div>
                <p>Welcome back! Experience the future of event management.</p>
            </div>
        </div>

        <!-- Right Side: Form -->
        <div class="auth-form-side">
            <a href="index.php" class="back-to-home">
                <i class="fas fa-arrow-left"></i> Back to Home
            </a>

            <div class="auth-card">
                <div class="auth-header">
                    <h2>Sign In</h2>
                    <p style="color: var(--text-muted);">Welcome back! Please enter your details.</p>
                </div>

                <!-- Error container handled by Swal, kept empty -->

                <form id="loginForm">
                    <div class="form-group">
                        <label
                            style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; color: var(--text-muted);">Username</label>
                        <div style="position: relative;">
                            <input type="text" name="username" class="form-input" placeholder="Your Username" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <label style="font-size: 0.9rem; color: var(--text-muted);">Password</label>
                            <a href="#" style="font-size: 0.85rem; color: var(--accent-blue);">Forgot?</a>
                        </div>
                        <div style="position: relative;">
                            <input type="password" name="password" class="form-input" placeholder="••••••••" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary"
                        style="width: 100%; justify-content: center; margin-top: 1rem;">
                        Sign In
                    </button>
                </form>

                <script>
                    $(document).on('submit', '#loginForm', function (e) {
                        e.preventDefault();

                        var formData = new FormData(this);
                        formData.append("login_user", true);

                        $.ajax({
                            type: "POST",
                            url: "backend.php",
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function (response) {
                                var res = response; // backend.php returns JSON with content-type header, jquery auto parses
                                if (res.status == 200) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Success',
                                        text: res.message,
                                        background: '#1e1f20',
                                        color: '#e3e3e3',
                                        confirmButtonColor: '#4285f4'
                                    }).then((result) => {
                                        window.location.href = res.redirect;
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: res.message,
                                        background: '#1e1f20',
                                        color: '#e3e3e3',
                                        confirmButtonColor: '#4285f4'
                                    });
                                }
                            },
                            error: function (xhr, status, error) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Server Error',
                                    text: 'Something went wrong!',
                                    background: '#1e1f20',
                                    color: '#e3e3e3',
                                    confirmButtonColor: '#4285f4'
                                });
                            }
                        });
                    });
                </script>

                <div style="text-align: center; margin-top: 2rem; font-size: 0.9rem; color: var(--text-muted);">
                    Don't have an account? <a href="register.php" class="text-gradient" style="font-weight: 600;">Sign
                        up</a>
                </div>
            </div>
        </div>
    </div>

</body>

</html>