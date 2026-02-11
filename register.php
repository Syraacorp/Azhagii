<?php
require_once 'db.php';
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Ziya</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Google+Sans:wght@400;500;700&family=Inter:wght@400;500;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
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
                <p>Join the community and start creating unforgettable events today.</p>
            </div>
        </div>

        <!-- Right Side: Form -->
        <div class="auth-form-side">
            <a href="index.php" class="back-to-home">
                <i class="fas fa-arrow-left"></i> Back to Home
            </a>

            <div class="auth-card">
                <div class="auth-header">
                    <h2>Create Account</h2>

                    <p style="color: var(--text-muted);">It's free and easy to get started.</p>
                </div>

                <!-- Error/Success containers handled by Swal, kept empty -->



                <form id="registerForm">
                    <!-- Name -->
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label
                            style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; color: var(--text-muted);">Full
                            Name</label>
                        <input type="text" name="name" class="form-input" placeholder="John Doe" required>
                    </div>

                    <!-- Department & Year (Flex) -->
                    <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                        <div class="form-group" style="flex: 1;">
                            <label
                                style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; color: var(--text-muted);">Department</label>
                            <select name="department" id="department" class="form-input" required>
                                <option value="" disabled selected>Select Dept</option>
                                <option value="AIDS">AIDS</option>
                                <option value="AIML">AIML</option>
                                <option value="CSE">CSE</option>
                                <option value="CSBS">CSBS</option>
                                <option value="CYBER">CYBER</option>
                                <option value="ECE">ECE</option>
                                <option value="EEE">EEE</option>
                                <option value="MECH">MECH</option>
                                <option value="CIVIL">CIVIL</option>
                                <option value="IT">IT</option>
                                <option value="VLSI">VLSI</option>
                                <option value="MBA">MBA</option>
                                <option value="MCA">MCA</option>
                            </select>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label
                                style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; color: var(--text-muted);">Year</label>
                            <select name="year" id="year" class="form-input" required>
                                <option value="" disabled selected>Select Year</option>
                                <option value="I year">I Year</option>
                                <option value="II year">II Year</option>
                                <option value="III year">III Year</option>
                                <option value="IV year">IV Year</option>
                            </select>
                        </div>
                    </div>

                    <!-- Roll Number (Auto-filled) -->
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label
                            style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; color: var(--text-muted);">Register
                            Number</label>
                        <input type="text" name="regno" id="rollNumber" class="form-input" placeholder="Auto-generated"
                            required>
                    </div>

                    <!-- Email & Phone (Flex) -->
                    <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                        <div class="form-group" style="flex: 1;">
                            <label
                                style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; color: var(--text-muted);">Email</label>
                            <input type="email" name="email" class="form-input" placeholder="name@example.com" required>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label
                                style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; color: var(--text-muted);">Phone</label>
                            <input type="tel" name="phone" class="form-input" placeholder="1234567890" required>
                        </div>
                    </div>

                    <!-- Username -->
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label
                            style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; color: var(--text-muted);">Username</label>
                        <input type="text" name="username" class="form-input" placeholder="unique_user" required>
                    </div>

                    <!-- Passwords -->
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label
                            style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; color: var(--text-muted);">Password</label>
                        <input type="password" name="password" class="form-input" placeholder="••••••••" required>
                    </div>

                    <div class="form-group">
                        <label
                            style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; color: var(--text-muted);">Confirm
                            Password</label>
                        <input type="password" name="confirm_password" class="form-input" placeholder="••••••••"
                            required>
                    </div>


                    <button type="submit" class="btn btn-primary"
                        style="width: 100%; justify-content: center; margin-top: 1rem;">
                        Create Account
                    </button>
                </form>

                <script>
                    // Roll Number Logic
                    const departmentSelect = document.getElementById('department');
                    const yearSelect = document.getElementById('year');
                    const rollNumberInput = document.getElementById('rollNumber');

                    let currentFixedPrefix = '';
                    let isRollPrefixLocked = false;

                    const deptCodes = {
                        'AIDS': 'BAD',
                        'AIML': 'BAM',
                        'CSE': 'BCS',
                        'CSBS': 'BCB',
                        'CYBER': 'BSC',
                        'ECE': 'BEC',
                        'EEE': 'BEE',
                        'MECH': 'BME',
                        'CIVIL': 'BCE',
                        'IT': 'BIT',
                        'VLSI': 'BEV',
                        'MBA': 'MBA',
                        'MCA': 'MCA'
                    };

                    const yearCodes = {
                        'I year': '927625',
                        'II year': '927624',
                        'III year': '927623',
                        'IV year': '927622'
                    };

                    // Enforce the prefix if locked
                    rollNumberInput.addEventListener('input', function () {
                        if (isRollPrefixLocked && currentFixedPrefix) {
                            if (!this.value.startsWith(currentFixedPrefix)) {
                                this.value = currentFixedPrefix;
                            }
                        }
                    });

                    // Prevent deleting the prefix via backspace for better UX
                    rollNumberInput.addEventListener('keydown', function (e) {
                        if (isRollPrefixLocked && currentFixedPrefix) {
                            if (this.selectionStart <= currentFixedPrefix.length && e.key === 'Backspace') {
                                e.preventDefault();
                            }
                        }
                    });

                    function checkAutoFillRollNumber() {
                        const dept = departmentSelect.value;
                        const year = yearSelect.value;

                        // Logic for CYBER department: Only I Year allowed
                        if (dept === 'CYBER') {
                            // This part handles hiding logic implicitly via option management or validation usually
                            // For simplicity given current HTML structure, we'll keep year options open
                            // If "CYBER" is selected, force logic or show alert if needed.
                            // Implementing user specific logic:
                            Array.from(yearSelect.options).forEach(opt => {
                                if (opt.value === 'I year' || opt.value === '') {
                                    opt.style.display = 'block';
                                    opt.disabled = false;
                                } else {
                                    opt.style.display = 'none';
                                    opt.disabled = true;
                                }
                            });
                            if (yearSelect.value && yearSelect.value !== 'I year') {
                                yearSelect.value = 'I year';
                            }
                        } else {
                            Array.from(yearSelect.options).forEach(opt => {
                                opt.style.display = 'block';
                                opt.disabled = false;
                            });
                        }

                        // Re-fetch year after potential reset
                        const currentYear = yearSelect.value;
                        let prefix = '';

                        if (dept && currentYear && yearCodes[currentYear]) {
                            const yCode = yearCodes[currentYear];
                            let dCode = deptCodes[dept] || '';

                            if (dept === 'AIML' && currentYear === 'IV year') {
                                dCode = 'BAL';
                            }

                            if (dCode) {
                                prefix = yCode + dCode;
                            }
                        }

                        if (prefix) {
                            // Start locking
                            if (currentFixedPrefix !== prefix) {
                                rollNumberInput.value = prefix;
                            } else if (!rollNumberInput.value.startsWith(prefix)) {
                                rollNumberInput.value = prefix;
                            }
                            currentFixedPrefix = prefix;
                            isRollPrefixLocked = true;
                        } else {
                            // unlock if selection invalid
                            // currentFixedPrefix = ''; // Optional: Keep prefix until explicit clear or change?
                            // Let's keep strict behavior
                        }
                    }

                    departmentSelect.addEventListener('change', checkAutoFillRollNumber);
                    yearSelect.addEventListener('change', checkAutoFillRollNumber);


                    // AJAX Submission
                    $(document).on('submit', '#registerForm', function (e) {
                        e.preventDefault();

                        var formData = new FormData(this);
                        formData.append("register_user", true);

                        $.ajax({
                            type: "POST",
                            url: "backend.php",
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function (response) {
                                var res = response;
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

                <div style="text-align: center; margin-top: 2rem; font-size: 0.9rem; c
 o                              lor: var(--text-muted);">
                    Already have an account? <a href="login.php" class="text-gradient" style="font-weight: 600;">Sign
                        In</a>
                </div>
            </div>
        </div>
    </div>


</body>

</html>