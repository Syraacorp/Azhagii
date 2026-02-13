<?php
session_start();
if (isset($_SESSION['user_id'])) { header('Location: dashboard.php'); exit; }
include 'db.php';
$colleges = [];
$r = mysqli_query($conn, "SELECT id, name, code, city FROM colleges WHERE status='active' ORDER BY name");
while ($r && $row = mysqli_fetch_assoc($r)) $colleges[] = $row;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Ziya LMS</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Google+Sans:wght@400;500;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-brand-side">
            <div class="auth-brand-bg"></div>
            <div class="auth-brand-content">
                <div class="logo" style="font-size:3.5rem;justify-content:center;margin-bottom:1rem;">
                    <span class="sparkle-icon" style="width:40px;height:40px;"></span> Ziya
                </div>
                <p>Join your college learning community</p>
            </div>
        </div>
        <div class="auth-form-side">
            <a href="index.php" class="back-to-home"><i class="fas fa-arrow-left"></i> Home</a>
            <div class="auth-card">
                <div class="auth-header">
                    <h2>Create Account</h2>
                    <p>Register as a student to get started</p>
                </div>
                <form id="registerForm">
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-input" placeholder="John Doe" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-input" placeholder="you@example.com" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-input" placeholder="Min 6 characters" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Your College</label>
                        <select name="college_id" class="form-input" required>
                            <option value="">Select your college</option>
                            <?php foreach ($colleges as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?> (<?= htmlspecialchars($c['code']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone (optional)</label>
                        <input type="text" name="phone" class="form-input" placeholder="+91 9876543210">
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:0.5rem;">
                        <i class="fas fa-user-plus"></i> Register
                    </button>
                </form>
                <p style="text-align:center;margin-top:1.5rem;font-size:0.9rem;">
                    Already have an account? <a href="login.php">Sign in</a>
                </p>
            </div>
        </div>
    </div>
    <script>
    $('#registerForm').submit(function(e) {
        e.preventDefault();
        const btn = $(this).find('button[type=submit]');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Registering...');
        $.post('backend.php', {
            register_student: 1,
            name: $('[name=name]').val(),
            email: $('[name=email]').val(),
            password: $('[name=password]').val(),
            college_id: $('[name=college_id]').val(),
            phone: $('[name=phone]').val()
        }, function(res) {
            if (res.status === 200) {
                Swal.fire({icon:'success', title:'Registered!', text:res.message, timer:2000, showConfirmButton:false})
                .then(() => window.location.href = 'login.php');
            } else {
                Swal.fire({icon:'error', title:'Failed', text:res.message});
                btn.prop('disabled', false).html('<i class="fas fa-user-plus"></i> Register');
            }
        }, 'json').fail(function() {
            Swal.fire({icon:'error', title:'Error', text:'Connection failed'});
            btn.prop('disabled', false).html('<i class="fas fa-user-plus"></i> Register');
        });
    });
    </script>
</body>
</html>
