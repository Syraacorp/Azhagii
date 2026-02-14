<?php
session_start();
if (isset($_SESSION['user_id'])) { header('Location: dashboard.php'); exit; }
include 'db.php';
$colleges = [];
$stmt = $conn->prepare("SELECT id, name, code, city FROM colleges WHERE status='active' ORDER BY name");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $colleges[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Azhagii LMS</title>
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
                <p>Join your college learning community</p>
            </div>
        </div>
        <div class="auth-form-side reg-form-side">
            <a href="index.php" class="back-to-home"><i class="fas fa-arrow-left"></i> Home</a>
            <div class="auth-card reg-card">
                <div class="auth-header" style="margin-bottom:1rem;">
                    <h2 style="font-size:1.4rem;">Create Account</h2>
                    <p style="font-size:0.85rem;">Register as a student to get started</p>
                </div>

                <!-- Tab Navigation -->
                <div class="reg-tabs">
                    <button type="button" class="reg-tab active" data-tab="1">
                        <span class="reg-tab-num">1</span> Personal Info
                    </button>
                    <button type="button" class="reg-tab" data-tab="2">
                        <span class="reg-tab-num">2</span> Account Setup
                    </button>
                </div>

                <form id="registerForm" autocomplete="off">
                    <!-- ═══ TAB 1: Personal Information ═══ -->
                    <div class="reg-tab-panel active" id="tabPanel1">
                        <div class="form-group">
                            <label class="form-label">Full Name <span class="req">*</span></label>
                            <input type="text" name="name" id="name" class="form-input" placeholder="Azhagii" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">College <span class="req">*</span></label>
                            <select name="college_id" id="college" class="form-input" required>
                                <option value="">Select your college</option>
                                <?php foreach ($colleges as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?> (<?= htmlspecialchars($c['code']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-row">
                            <div class="form-group" style="flex:1;">
                                <label class="form-label">Department <span class="req">*</span></label>
                                <select name="department" id="department" class="form-input" required>
                                    <option value="" disabled selected>Select Department</option>
                                    <option value="AIDS">Artificial Intelligence and Data Science</option>
                                    <option value="AIML">Artificial Intelligence and Machine Learning</option>
                                    <option value="CYBER">Computer Science and Engineering (Cyber Security)</option>
                                    <option value="CSE">Computer Science Engineering</option>
                                    <option value="CSBS">Computer Science And Business Systems</option>
                                    <option value="ECE">Electronics & Communication Engineering</option>
                                    <option value="EEE">Electrical & Electronics Engineering</option>
                                    <option value="MECH">Mechanical Engineering</option>
                                    <option value="CIVIL">Civil Engineering</option>
                                    <option value="IT">Information Technology</option>
                                    <option value="VLSI">Electronics Engineering (VLSI Design)</option>
                                </select>
                            </div>
                            <div class="form-group" style="flex:1;">
                                <label class="form-label">Year <span class="req">*</span></label>
                                <select name="year" id="year" class="form-input" required>
                                    <option value="">Select</option>
                                    <option value="I year">I Year</option>
                                    <option value="II year">II Year</option>
                                    <option value="III year">III Year</option>
                                    <option value="IV year">IV Year</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Roll Number <span class="req">*</span></label>
                            <input type="text" name="roll_number" id="rollNumber" class="form-input" placeholder="Select dept & year first" required maxlength="12">
                            <small class="roll-feedback" id="rollFeedback"></small>
                        </div>

                        <div class="form-row">
                            <div class="form-group" style="flex:1.2;">
                                <label class="form-label">Email <span class="req">*</span></label>
                                <input type="email" name="email" id="email" class="form-input" placeholder="you@example.com" required>
                            </div>
                            <div class="form-group" style="flex:0.8;">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" id="phone" class="form-input" placeholder="+91 9876543210">
                            </div>
                        </div>

                        <button type="button" class="btn btn-primary btn-compact" id="btnNext" style="width:100%;justify-content:center;">
                            Next <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>

                    <!-- ═══ TAB 2: Account Setup ═══ -->
                    <div class="reg-tab-panel" id="tabPanel2">
                        <div class="form-group">
                            <label class="form-label">Username <span class="req">*</span></label>
                            <input type="text" name="username" id="username" class="form-input" placeholder="Choose a unique username" required minlength="4">
                            <small class="field-hint">At least 4 characters, letters, numbers and underscores only</small>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Password <span class="req">*</span></label>
                            <div class="password-wrapper">
                                <input type="password" name="password" id="password" class="form-input" placeholder="Min 6 characters" required minlength="6">
                                <button type="button" class="pw-toggle" id="togglePw"><i class="fas fa-eye"></i></button>
                            </div>
                            <div class="pw-strength-bar" id="pwStrengthBar">
                                <div class="pw-strength-fill" id="pwStrengthFill"></div>
                            </div>
                            <small class="pw-strength-text" id="pwStrengthText"></small>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Confirm Password <span class="req">*</span></label>
                            <div class="password-wrapper">
                                <input type="password" name="confirm_password" id="confirmPassword" class="form-input" placeholder="Re-enter your password" required minlength="6">
                                <button type="button" class="pw-toggle" id="toggleCpw"><i class="fas fa-eye"></i></button>
                            </div>
                            <small class="pw-match-feedback" id="pwMatchFeedback"></small>
                        </div>

                        <!-- Password Requirements -->
                        <div class="pw-requirements pw-requirements-compact" id="pwRequirements">
                            <ul>
                                <li id="reqLen"><i class="fas fa-circle"></i> 6+ chars</li>
                                <li id="reqUpper"><i class="fas fa-circle"></i> Uppercase</li>
                                <li id="reqNum"><i class="fas fa-circle"></i> Number</li>
                                <li id="reqLower"><i class="fas fa-circle"></i> Lowercase</li>
                                <li id="reqSpecial"><i class="fas fa-circle"></i> Special</li>
                                <li id="reqMatch"><i class="fas fa-circle"></i> Match</li>
                            </ul>
                        </div>

                        <div class="reg-btn-row">
                            <button type="button" class="btn btn-secondary btn-compact" id="btnBack" style="flex:1;justify-content:center;">
                                <i class="fas fa-arrow-left"></i> Back
                            </button>
                            <button type="submit" class="btn btn-primary btn-compact" id="btnRegister" style="flex:2;justify-content:center;" disabled>
                                <i class="fas fa-user-plus"></i> Register
                            </button>
                        </div>
                    </div>
                </form>

                <p style="text-align:center;margin-top:0.75rem;font-size:0.85rem;">
                    Already have an account? <a href="login.php">Sign in</a>
                </p>
            </div>
        </div>
    </div>

    <script>
    (function() {
        // ══════════════════════════════════════════
        //  TAB NAVIGATION
        // ══════════════════════════════════════════
        const tabs = document.querySelectorAll('.reg-tab');
        const panels = document.querySelectorAll('.reg-tab-panel');
        const btnNext = document.getElementById('btnNext');
        const btnBack = document.getElementById('btnBack');

        function switchTab(num) {
            tabs.forEach(t => t.classList.toggle('active', t.dataset.tab == num));
            panels.forEach(p => p.classList.remove('active'));
            document.getElementById('tabPanel' + num).classList.add('active');
        }

        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                const target = parseInt(this.dataset.tab);
                if (target === 2 && !validateTab1()) return;
                switchTab(target);
            });
        });

        btnNext.addEventListener('click', function() {
            if (validateTab1()) switchTab(2);
        });
        btnBack.addEventListener('click', function() { switchTab(1); });

        function validateTab1() {
            const name = document.getElementById('name').value.trim();
            const college = document.getElementById('college').value;
            const dept = document.getElementById('department').value;
            const year = document.getElementById('year').value;
            const roll = document.getElementById('rollNumber').value.trim();
            const email = document.getElementById('email').value.trim();

            if (!name) { showFieldError('name', 'Full name is required'); return false; }
            if (!college) { showFieldError('college', 'Please select your college'); return false; }
            if (!dept) { showFieldError('department', 'Please select your department'); return false; }
            if (!year) { showFieldError('year', 'Please select your year'); return false; }
            if (!roll || roll.length !== 12) { showFieldError('rollNumber', 'Valid 12-digit roll number is required'); return false; }
            if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { showFieldError('email', 'Valid email is required'); return false; }
            return true;
        }

        function showFieldError(id, msg) {
            const el = document.getElementById(id);
            el.focus();
            el.classList.add('input-error');
            Swal.fire({ icon:'warning', title:'Missing Info', text: msg, timer: 2000, showConfirmButton: false });
            setTimeout(() => el.classList.remove('input-error'), 3000);
        }

        // ══════════════════════════════════════════
        //  ROLL NUMBER AUTO-FILL
        // ══════════════════════════════════════════
        const departmentSelect = document.getElementById('department');
        const yearSelect = document.getElementById('year');
        const rollNumberInput = document.getElementById('rollNumber');

        let currentFixedPrefix = '';
        let isRollPrefixLocked = false;

        const deptCodes = {
            'AIDS': 'BAD', 'AIML': 'BAM', 'CSE': 'BCS', 'CSBS': 'BCB',
            'CYBER': 'BSC', 'ECE': 'BEC', 'EEE': 'BEE', 'MECH': 'BME',
            'CIVIL': 'BCE', 'IT': 'BIT', 'VLSI': 'BEV'
        };

        const yearCodes = {
            'I year': '927625', 'II year': '927624',
            'III year': '927623', 'IV year': '927622'
        };

        rollNumberInput.addEventListener('input', function() {
            if (isRollPrefixLocked && currentFixedPrefix) {
                if (!this.value.startsWith(currentFixedPrefix)) {
                    this.value = currentFixedPrefix;
                }
            }
        });

        rollNumberInput.addEventListener('keydown', function(e) {
            if (isRollPrefixLocked && currentFixedPrefix) {
                if (this.selectionStart <= currentFixedPrefix.length && e.key === 'Backspace') {
                    e.preventDefault();
                }
            }
        });

        function checkAutoFillRollNumber() {
            const dept = departmentSelect.value;

            if (dept === 'CYBER') {
                Array.from(yearSelect.options).forEach(opt => {
                    if (opt.value === 'I year' || opt.value === '') {
                        opt.style.display = 'block'; opt.disabled = false;
                    } else {
                        opt.style.display = 'none'; opt.disabled = true;
                    }
                });
                if (yearSelect.value && yearSelect.value !== 'I year') {
                    yearSelect.value = 'I year';
                }
            } else {
                Array.from(yearSelect.options).forEach(opt => {
                    opt.style.display = 'block'; opt.disabled = false;
                });
            }

            const year = yearSelect.value;
            let prefix = '';

            if (dept && year && yearCodes[year]) {
                const yCode = yearCodes[year];
                let dCode = deptCodes[dept] || '';
                if (dept === 'AIML' && year === 'IV year') {
                    dCode = 'BAL';
                }
                if (dCode) prefix = yCode + dCode;
            }

            if (prefix) {
                if (currentFixedPrefix !== prefix) {
                    rollNumberInput.value = prefix;
                } else if (!rollNumberInput.value.startsWith(prefix)) {
                    rollNumberInput.value = prefix;
                }
                currentFixedPrefix = prefix;
                isRollPrefixLocked = true;
                rollNumberInput.placeholder = prefix + '___';
            } else {
                isRollPrefixLocked = false;
                currentFixedPrefix = '';
                rollNumberInput.placeholder = 'Select dept & year first';
            }
        }

        departmentSelect.addEventListener('change', checkAutoFillRollNumber);
        yearSelect.addEventListener('change', checkAutoFillRollNumber);

        // Real-time Roll Number Validation
        let rollTimer;
        const rollDoneTypingInterval = 400;
        const rollFeedback = document.getElementById('rollFeedback');

        rollNumberInput.addEventListener('keyup', function() {
            clearTimeout(rollTimer);
            const val = this.value;
            if (!val) { rollFeedback.textContent = ''; rollFeedback.className = 'roll-feedback'; return; }
            rollTimer = setTimeout(() => {
                if (val.length === 12) {
                    rollFeedback.textContent = '✓ Valid roll number format';
                    rollFeedback.className = 'roll-feedback valid';
                } else {
                    rollFeedback.textContent = '✗ Roll number must be 12 characters (' + val.length + '/12)';
                    rollFeedback.className = 'roll-feedback invalid';
                }
            }, rollDoneTypingInterval);
        });

        // ══════════════════════════════════════════
        //  PASSWORD VALIDATION
        // ══════════════════════════════════════════
        const passwordInput = document.getElementById('password');
        const confirmInput = document.getElementById('confirmPassword');
        const strengthFill = document.getElementById('pwStrengthFill');
        const strengthText = document.getElementById('pwStrengthText');
        const matchFeedback = document.getElementById('pwMatchFeedback');
        const btnRegister = document.getElementById('btnRegister');

        const reqLen = document.getElementById('reqLen');
        const reqUpper = document.getElementById('reqUpper');
        const reqLower = document.getElementById('reqLower');
        const reqNum = document.getElementById('reqNum');
        const reqSpecial = document.getElementById('reqSpecial');
        const reqMatch = document.getElementById('reqMatch');

        function checkRequirements(pw) {
            const checks = {
                len: pw.length >= 6,
                upper: /[A-Z]/.test(pw),
                lower: /[a-z]/.test(pw),
                num: /[0-9]/.test(pw),
                special: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(pw)
            };

            setReqState(reqLen, checks.len);
            setReqState(reqUpper, checks.upper);
            setReqState(reqLower, checks.lower);
            setReqState(reqNum, checks.num);
            setReqState(reqSpecial, checks.special);

            return checks;
        }

        function setReqState(el, passed) {
            const icon = el.querySelector('i');
            if (passed) {
                el.classList.add('passed');
                el.classList.remove('failed');
                icon.className = 'fas fa-check-circle';
            } else {
                el.classList.remove('passed');
                el.classList.add('failed');
                icon.className = 'fas fa-circle';
            }
        }

        function getStrength(pw) {
            let score = 0;
            if (pw.length >= 6) score++;
            if (pw.length >= 10) score++;
            if (/[A-Z]/.test(pw)) score++;
            if (/[a-z]/.test(pw)) score++;
            if (/[0-9]/.test(pw)) score++;
            if (/[^a-zA-Z0-9]/.test(pw)) score++;
            return score; // 0-6
        }

        function updateStrengthBar(pw) {
            if (!pw) {
                strengthFill.style.width = '0%';
                strengthFill.className = 'pw-strength-fill';
                strengthText.textContent = '';
                return;
            }
            const score = getStrength(pw);
            const pct = Math.round((score / 6) * 100);
            strengthFill.style.width = pct + '%';

            if (score <= 2) {
                strengthFill.className = 'pw-strength-fill weak';
                strengthText.textContent = 'Weak';
                strengthText.style.color = '#ef4444';
            } else if (score <= 4) {
                strengthFill.className = 'pw-strength-fill medium';
                strengthText.textContent = 'Medium';
                strengthText.style.color = '#f59e0b';
            } else {
                strengthFill.className = 'pw-strength-fill strong';
                strengthText.textContent = 'Strong';
                strengthText.style.color = '#22c55e';
            }
        }

        function checkPasswordMatch() {
            const pw = passwordInput.value;
            const cpw = confirmInput.value;
            let match = false;
            if (!cpw) {
                matchFeedback.textContent = '';
                matchFeedback.className = 'pw-match-feedback';
            } else if (pw === cpw) {
                matchFeedback.textContent = '✓ Passwords match';
                matchFeedback.className = 'pw-match-feedback match';
                match = true;
            } else {
                matchFeedback.textContent = '✗ Passwords do not match';
                matchFeedback.className = 'pw-match-feedback no-match';
            }
            setReqState(reqMatch, match);
            return match;
        }

        function updateRegisterButton() {
            const pw = passwordInput.value;
            const checks = checkRequirements(pw);
            const allPassed = checks.len && checks.upper && checks.lower && checks.num && checks.special;
            const matching = checkPasswordMatch();
            const username = document.getElementById('username').value.trim();
            btnRegister.disabled = !(allPassed && matching && username.length >= 4);
        }

        passwordInput.addEventListener('input', function() {
            updateStrengthBar(this.value);
            checkRequirements(this.value);
            updateRegisterButton();
        });

        confirmInput.addEventListener('input', updateRegisterButton);
        document.getElementById('username').addEventListener('input', updateRegisterButton);

        // Toggle password visibility
        document.getElementById('togglePw').addEventListener('click', function() {
            const inp = document.getElementById('password');
            const icon = this.querySelector('i');
            if (inp.type === 'password') { inp.type = 'text'; icon.className = 'fas fa-eye-slash'; }
            else { inp.type = 'password'; icon.className = 'fas fa-eye'; }
        });
        document.getElementById('toggleCpw').addEventListener('click', function() {
            const inp = document.getElementById('confirmPassword');
            const icon = this.querySelector('i');
            if (inp.type === 'password') { inp.type = 'text'; icon.className = 'fas fa-eye-slash'; }
            else { inp.type = 'password'; icon.className = 'fas fa-eye'; }
        });

        // ══════════════════════════════════════════
        //  FORM SUBMIT
        // ══════════════════════════════════════════
        $('#registerForm').submit(function(e) {
            e.preventDefault();

            const pw = $('#password').val();
            const cpw = $('#confirmPassword').val();
            if (pw !== cpw) {
                Swal.fire({ icon:'error', title:'Mismatch', text:'Passwords do not match' });
                return;
            }

            const btn = $('#btnRegister');
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Registering...');

            $.post('backend.php', {
                register_student: 1,
                name: $('#name').val(),
                email: $('#email').val(),
                username: $('#username').val(),
                password: pw,
                college_id: $('#college').val(),
                department: $('#department').val(),
                year: $('#year').val(),
                roll_number: $('#rollNumber').val(),
                phone: $('#phone').val()
            }, function(res) {
                if (res.status === 200) {
                    Swal.fire({ icon:'success', title:'Registered!', text:res.message, timer:2000, showConfirmButton:false })
                    .then(() => window.location.href = 'studentDashboard.php');
                } else {
                    Swal.fire({ icon:'error', title:'Failed', text:res.message });
                    btn.prop('disabled', false).html('<i class="fas fa-user-plus"></i> Register');
                }
            }, 'json').fail(function() {
                Swal.fire({ icon:'error', title:'Error', text:'Connection failed' });
                btn.prop('disabled', false).html('<i class="fas fa-user-plus"></i> Register');
            });
        });
    })();
    </script>
</body>
</html>
