<?php
$pageTitle = 'My Profile';
$currentPage = 'profileSSR';
require 'includes/auth.php';
require 'includes/header.php';
require 'includes/sidebar.php';

$uid = $_SESSION['userId'];
$stmt = $conn->prepare("SELECT u.*, c.name as college_name, c.code as college_code 
      FROM users u 
      LEFT JOIN colleges c ON u.collegeId=c.id 
      WHERE u.id=?");
$stmt->bind_param("i", $uid);
$stmt->execute();
$r = $stmt->get_result();
$u = $r->fetch_assoc();
$stmt->close();

// Profile Completion Calculation
$filled = 0;
// Basic (4)
if (!empty($u['name']))
    $filled++;
if (!empty($u['email']))
    $filled++;
if (!empty($u['username']))
    $filled++;
if (!empty($u['role']))
    $filled++;
// Contact (2)
if (!empty($u['phone']))
    $filled++;
if (!empty($u['address']))
    $filled++;
// Bio (1)
if (!empty($u['bio']))
    $filled++;
// Personal (2)
if (!empty($u['dob']))
    $filled++;
if (!empty($u['gender']))
    $filled++;
// Academic (Depends on role)
if ($u['role'] == 'azhagiiStudents') {
    if (!empty($u['collegeId']))
        $filled++;
    if (!empty($u['department']))
        $filled++;
    if (!empty($u['year']))
        $filled++;
    if (!empty($u['rollNumber']))
        $filled++;
    $total = 13;
} else {
    // For admins/coordinators, college is enough
    if (!empty($u['collegeId']))
        $filled++;
    $total = 10;
}
// Assets (1)
if (!empty($u['profilePhoto']))
    $filled++;
$total++; // Total assets count (1)

$pct = ($total > 0) ? round(($filled / $total) * 100) : 0;
$pctColor = ($pct >= 100) ? 'bg-success' : 'bg-warning';

// Locked State
$isAdmin = in_array($u['role'], ['superAdmin', 'adminAzhagii']);
$isLocked = ($u['isLocked'] == 1 && !$isAdmin);
$disabledAttr = $isLocked ? 'disabled' : '';

// Avatar
$avatarHtml = '';
if (!empty($u['profilePhoto'])) {
    $avatarHtml = '<img src="' . htmlspecialchars($u['profilePhoto']) . '" alt="Avatar">';
} else {
    $avatarHtml = strtoupper(substr($u['name'], 0, 1));
}
if (!$isLocked) {
    $avatarHtml .= '<div class="upload-overlay">Change</div>';
}

$displayName = htmlspecialchars($u['name']);
$displayID = htmlspecialchars($u['azhagiiID'] ?? $u['role']);
$deptText = ($u['department'] ?? '') . ($u['year'] ? ' - ' . $u['year'] : '');
?>

<style>
    /* Reduce page padding for profile page */
    .content-wrapper {
        padding-top: 5rem !important;
        padding-bottom: 0.75rem !important;
    }

    /* Profile specific layout */
    .profile-dashboard-grid {
        display: grid;
        grid-template-columns: 280px 1fr;
        gap: 0.75rem;
        align-items: start;
    }

    .profile-col {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .profile-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 0.875rem;
        transition: all 0.2s;
    }

    .profile-card:hover {
        border-color: var(--accent-blue);
        box-shadow: var(--shadow-soft);
    }

    .profile-card h3 {
        font-size: 1rem;
        margin-bottom: 0.625rem;
        color: var(--text-heading);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .profile-avatar-lg {
        width: 80px;
        height: 80px;
        margin: 0 auto 0.75rem auto;
        border-radius: 50%;
        background: var(--primary);
        color: #fff;
        font-size: 2.25rem;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
        border: 4px solid var(--bg-body);
        box-shadow: var(--shadow-soft);
        cursor: pointer;
    }

    .profile-avatar-lg img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .upload-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        background: rgba(0, 0, 0, 0.6);
        color: white;
        font-size: 0.8rem;
        padding: 6px 0;
        opacity: 0;
        transition: 0.2s;
    }

    .profile-avatar-lg:hover .upload-overlay {
        opacity: 1;
    }

    .identity-wrapper {
        text-align: center;
    }

    .user-name-lg {
        font-size: 1.15rem;
        font-weight: 700;
        color: var(--text-heading);
        margin-bottom: 0.25rem;
    }

    .user-role-badge {
        display: inline-block;
        padding: 0.2rem 0.6rem;
        border-radius: 100px;
        background: rgba(66, 133, 244, 0.1);
        color: var(--accent-blue);
        font-size: 0.85rem;
        border: 1px solid rgba(66, 133, 244, 0.2);
    }

    .profile-progress-wrap {
        margin-top: 0.875rem;
        text-align: left;
    }

    .profile-progress-track {
        width: 100%;
        height: 6px;
        background: var(--bg-surface-hover);
        border-radius: 4px;
        overflow: hidden;
        margin-top: 0.5rem;
    }

    .profile-progress-fill {
        height: 100%;
        background: var(--primary-gradient);
        transition: width 0.5s;
        border-radius: 4px;
    }

    .form-group-profile {
        margin-bottom: 0.5rem;
    }

    .form-label-profile {
        display: block;
        font-size: 0.8rem;
        color: var(--text-muted);
        margin-bottom: 0.3rem;
    }

    .form-input-profile {
        width: 100%;
        background: var(--input-bg);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-sm);
        padding: 0.5rem 0.75rem;
        color: var(--text-main);
        font-size: 0.875rem;
        line-height: 1.3;
    }

    .form-input-profile:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .form-input-profile:focus {
        outline: none;
        border-color: var(--primary);
    }

    select.form-input-profile {
        height: auto;
    }

    .social-input-group {
        position: relative;
    }

    .social-input-group i {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
    }

    .social-input-group input {
        padding-left: 2.8rem;
    }

    @media (max-width: 1100px) {
        .profile-dashboard-grid {
            grid-template-columns: 1fr;
        }

        .profile-col:nth-child(1) {
            order: 1;
        }

        .profile-col:nth-child(2) {
            order: 2;
        }
    }
</style>
<link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<style>
    .cropper-modal {
        display: none;
        position: fixed;
        z-index: 10000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.85);
        backdrop-filter: blur(4px);
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .cropper-modal.show {
        opacity: 1;
    }

    .cropper-content {
        background-color: var(--bg-surface) !important;
        color: var(--text-main);
        padding: 2rem;
        border-radius: var(--radius-lg);
        width: 90%;
        max-width: 500px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        border: 1px solid var(--border-color);
        position: relative;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .cropper-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
    }

    .cropper-header h3 {
        margin: 0;
        font-size: 1.25rem;
    }

    .img-container {
        height: 400px;
        max-height: 50vh;
        width: 100%;
        background: #000;
        overflow: hidden;
        border-radius: var(--radius-sm);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .img-container img {
        max-width: 100%;
        max-height: 100%;
        display: block;
    }

    .cropper-actions {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        margin-top: 0.5rem;
    }
</style>

<form id="profileForm" enctype="multipart/form-data" class="profile-dashboard-grid">

    <!-- ═══ COL 1: IDENTITY (Left Panel) ═══ -->
    <div class="profile-col">
        <div class="profile-card identity-wrapper">
            <div class="profile-avatar-lg" id="profileAvatarDisplay"
                onclick="<?= !$isLocked ? "document.getElementById('profilePhoto').click()" : "" ?>">
                <?= $avatarHtml ?>
            </div>
            <input type="file" name="profilePhoto" id="profilePhoto" accept="image/*" style="display:none;"
                onchange="previewImage(this)">

            <div class="user-name-lg" id="displayNameHeader"><?= $displayName ?></div>
            <div class="user-role-badge" id="displayAzhagiiID"><?= $displayID ?></div>
            <div style="font-size:0.85rem;color:var(--text-muted);margin-top:0.375rem;" id="displayDeptHeader">
                <?= $deptText ?></div>

            <div class="profile-progress-wrap">
                <div class="d-flex justify-content-between"
                    style="font-size:0.8rem; display:flex; justify-content:space-between;">
                    <span style="color:var(--text-heading);">Profile Strength</span>
                    <span id="progressText" style="font-weight:600;"><?= $pct ?>%</span>
                </div>
                <div class="profile-progress-track">
                    <div class="profile-progress-fill <?= $pctColor ?>" id="progressBar" style="width: <?= $pct ?>%">
                    </div>
                </div>
                <div id="completionNote" style="font-size:0.75rem;margin-top:0.375rem;color:var(--text-muted);">
                    <?php if ($isLocked): ?>
                        <span class="text-success"><i class="fas fa-lock"></i> Profile Locked</span>
                    <?php else: ?>
                        Complete all fields to finish.
                    <?php endif; ?>
                </div>
            </div>

            <hr style="border:0; border-top:1px solid var(--border-color); margin: 0.75rem 0;">

            <div class="save-btn-wrapper">
                <?php if ($isLocked): ?>
                    <button type="button" class="btn btn-warning btn-save-full" onclick="requestUnlock()">
                        <i class="fas fa-lock"></i> Request Edit
                    </button>
                <?php else: ?>
                    <button type="submit" class="btn btn-primary"
                        style="width:100%; justify-content:center; padding: 0.625rem;">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <div class="profile-card">
            <h3><i class="fas fa-lock" style="color:var(--accent-pink);"></i> Security</h3>
            <div class="form-group-profile" style="margin-bottom:0;">
                <label class="form-label-profile">Change Password <span class="text-muted"
                        style="font-weight:normal;">(Optional)</span></label>
                <input type="password" name="password" id="password" class="form-input-profile"
                    placeholder="New Password" <?= $disabledAttr ?>>
            </div>
        </div>
    </div>

    <!-- ═══ COL 2: MAIN DETAILS (Center Panel) ═══ -->
    <div class="profile-col">
        <div class="profile-card">
            <h3><i class="fas fa-user-circle" style="color:var(--accent-purple);"></i> Personal & Academic Details</h3>

            <div class="row responsive-grid-2">
                <div class="form-group-profile">
                    <label class="form-label-profile">Full Name <span style="color:#ef4444">*</span></label>
                    <input type="text" name="name" id="name" class="form-input-profile" required
                        value="<?= htmlspecialchars($u['name']) ?>" <?= $disabledAttr ?>>
                </div>
                <div class="form-group-profile">
                    <label class="form-label-profile">Phone Number</label>
                    <input type="text" name="phone" id="phone" class="form-input-profile" placeholder="+91..."
                        value="<?= htmlspecialchars($u['phone'] ?? '') ?>" <?= $disabledAttr ?>>
                </div>
            </div>

            <div class="row responsive-grid-2">
                <div class="form-group-profile">
                    <label class="form-label-profile">Gender</label>
                    <select name="gender" id="gender" class="form-input-profile" <?= $disabledAttr ?>>
                        <option value="">Select Gender</option>
                        <option value="Male" <?= ($u['gender'] == 'Male') ? 'selected' : '' ?>>Male</option>
                        <option value="Female" <?= ($u['gender'] == 'Female') ? 'selected' : '' ?>>Female</option>
                        <option value="Other" <?= ($u['gender'] == 'Other') ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
                <div class="form-group-profile">
                    <label class="form-label-profile">Date of Birth</label>
                    <input type="date" name="dob" id="dob" class="form-input-profile"
                        value="<?= htmlspecialchars($u['dob'] ?? '') ?>" <?= $disabledAttr ?>>
                </div>
            </div>

            <div class="row responsive-grid-2">
                <div class="form-group-profile">
                    <label class="form-label-profile">Address</label>
                    <input type="text" name="address" id="address" class="form-input-profile"
                        placeholder="Your address..." value="<?= htmlspecialchars($u['address'] ?? '') ?>"
                        <?= $disabledAttr ?>>
                </div>
                <div class="form-group-profile">
                    <label class="form-label-profile">Bio / Tagline</label>
                    <input type="text" name="bio" id="bio" class="form-input-profile"
                        placeholder="Tell us about yourself..." value="<?= htmlspecialchars($u['bio'] ?? '') ?>"
                        <?= $disabledAttr ?>>
                </div>
            </div>

            <div class="row responsive-grid-3">
                <div class="form-group-profile">
                    <label class="form-label-profile">Username</label>
                    <input type="text" id="username" class="form-input-profile" disabled
                        value="<?= htmlspecialchars($u['username']) ?>">
                </div>
                <div class="form-group-profile">
                    <label class="form-label-profile">Email Address</label>
                    <input type="email" id="email" class="form-input-profile" disabled
                        value="<?= htmlspecialchars($u['email']) ?>">
                </div>
                <div class="form-group-profile">
                    <label class="form-label-profile">College</label>
                    <input type="text" id="college" class="form-input-profile" disabled
                        value="<?= htmlspecialchars(($u['college_name'] ?? '') . ($u['college_code'] ? ' (' . $u['college_code'] . ')' : '')) ?>">
                </div>
            </div>

            <?php if ($u['role'] == 'azhagiiStudents'): ?>
                <div id="studentFields">
                    <div class="row responsive-grid-4">
                        <div class="form-group-profile">
                            <label class="form-label-profile">Azhagii ID</label>
                            <input type="text" id="azhagiiID" class="form-input-profile" disabled
                                value="<?= htmlspecialchars($u['azhagiiID'] ?? '') ?>">
                        </div>
                        <div class="form-group-profile">
                            <label class="form-label-profile">Dept</label>
                            <input type="text" id="department" class="form-input-profile" disabled
                                value="<?= htmlspecialchars($u['department'] ?? '') ?>">
                        </div>
                        <div class="form-group-profile">
                            <label class="form-label-profile">Year</label>
                            <input type="text" id="year" class="form-input-profile" disabled
                                value="<?= htmlspecialchars($u['year'] ?? '') ?>">
                        </div>
                        <div class="form-group-profile">
                            <label class="form-label-profile">Roll No</label>
                            <input type="text" id="rollNumber" class="form-input-profile" disabled
                                value="<?= htmlspecialchars($u['rollNumber'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="profile-card" style="margin-top:0;">
            <h3><i class="fas fa-share-alt" style="color:var(--accent-blue);"></i> Social Profiles</h3>
            <div class="row responsive-grid-4">
                <div class="form-group-profile">
                    <label class="form-label-profile">GitHub</label>
                    <div class="social-input-group">
                        <i class="fab fa-github"></i>
                        <input type="url" name="githubUrl" id="githubUrl" class="form-input-profile"
                            placeholder="https://github.com/..." value="<?= htmlspecialchars($u['githubUrl'] ?? '') ?>"
                            <?= $disabledAttr ?>>
                    </div>
                </div>
                <div class="form-group-profile">
                    <label class="form-label-profile">LinkedIn</label>
                    <div class="social-input-group">
                        <i class="fab fa-linkedin" style="color:#0077b5;"></i>
                        <input type="url" name="linkedinUrl" id="linkedinUrl" class="form-input-profile"
                            placeholder="https://linkedin.com/..."
                            value="<?= htmlspecialchars($u['linkedinUrl'] ?? '') ?>" <?= $disabledAttr ?>>
                    </div>
                </div>
                <div class="form-group-profile">
                    <label class="form-label-profile">HackerRank</label>
                    <div class="social-input-group">
                        <i class="fab fa-hackerrank" style="color:#2ec866;"></i>
                        <input type="url" name="hackerrankUrl" id="hackerrankUrl" class="form-input-profile"
                            placeholder="https://hackerrank.com/..."
                            value="<?= htmlspecialchars($u['hackerrankUrl'] ?? '') ?>" <?= $disabledAttr ?>>
                    </div>
                </div>
                <div class="form-group-profile">
                    <label class="form-label-profile">LeetCode</label>
                    <div class="social-input-group">
                        <i class="fas fa-code" style="color:#ffa116;"></i>
                        <input type="url" name="leetcodeUrl" id="leetcodeUrl" class="form-input-profile"
                            placeholder="https://leetcode.com/..."
                            value="<?= htmlspecialchars($u['leetcodeUrl'] ?? '') ?>" <?= $disabledAttr ?>>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Cropper Modal -->
<div id="cropperModal" class="cropper-modal">
    <div class="cropper-content">
        <div class="cropper-header">
            <h3>Adjust Image</h3>
            <button type="button" class="btn-close" onclick="closeCropper()"
                style="background:none;border:none;color:var(--text-muted);cursor:pointer;"><i
                    class="fas fa-times"></i></button>
        </div>
        <div class="img-container">
            <img id="imageToCrop" src="" alt="Crop Preview">
        </div>
        <div class="cropper-actions">
            <button type="button" class="btn btn-outline" onclick="closeCropper()">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="cropAndSave()"><i class="fas fa-check"></i> Save
                Photo</button>
        </div>
    </div>
</div>

<script>
    let cropper;
    let croppedBlob = null;

    $(document).ready(function () {
        $('#cropperModal').appendTo('body');

        // Check incomplete param
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('incomplete')) {
            Swal.fire({ toast: true, position: 'top-end', icon: 'info', title: 'Please complete your profile', showConfirmButton: false, timer: 3000 });
        }

        // Handle Profile Form Submit
        $('#profileForm').submit(function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('update_my_profile', 1);
            if (croppedBlob) formData.set('profilePhoto', croppedBlob, 'profile.jpg');

            const btn = $(this).find('button[type=submit]');
            const oldHtml = btn.html();
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

            $.ajax({
                url: 'backend.php', type: 'POST', data: formData,
                processData: false, contentType: false, dataType: 'json',
                success: function (res) {
                    if (res.status === 200) {
                        Swal.fire({ icon: 'success', title: 'Saved!', toast: true, position: 'top-end', showConfirmButton: false, timer: 1500 })
                            .then(() => location.reload());
                    } else {
                        Swal.fire('Error', res.message, 'error');
                        btn.prop('disabled', false).html(oldHtml);
                    }
                },
                error: function (xhr, status, error) {
                    Swal.fire('Error', 'Failed to save profile. Please try again.', 'error');
                    btn.prop('disabled', false).html(oldHtml);
                }
            });
        });
    });

    function previewImage(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const reader = new FileReader();
            reader.onload = function (e) {
                $('#imageToCrop').attr('src', e.target.result);
                $('#cropperModal').css('display', 'flex');
                void document.getElementById('cropperModal').offsetWidth;
                $('#cropperModal').addClass('show');
                setTimeout(() => {
                    const image = document.getElementById('imageToCrop');
                    if (cropper) cropper.destroy();
                    cropper = new Cropper(image, { aspectRatio: 1, viewMode: 1, dragMode: 'move', autoCropArea: 1, background: false, responsive: true, restore: false });
                }, 100);
            }
            reader.readAsDataURL(file);
        }
    }

    function closeCropper() {
        $('#cropperModal').removeClass('show');
        setTimeout(() => {
            $('#cropperModal').hide();
            if (cropper) { cropper.destroy(); cropper = null; }
            document.getElementById('profilePhoto').value = '';
        }, 300);
    }

    function cropAndSave() {
        if (cropper) {
            Swal.fire({ title: 'Saving Photo...', html: 'Please wait...', allowOutsideClick: false, showConfirmButton: false, willOpen: () => Swal.showLoading() });
            cropper.getCroppedCanvas({ width: 400, height: 400, fillColor: '#fff' }).toBlob((blob) => {
                croppedBlob = blob;
                const formData = new FormData();
                formData.append('update_profile_photo', 1);
                formData.append('profilePhoto', blob, 'profile.jpg');
                $.ajax({
                    url: 'backend.php', type: 'POST', data: formData, processData: false, contentType: false, dataType: 'json',
                    success: function (res) {
                        if (res.status === 200) {
                            const url = URL.createObjectURL(blob);
                            $('#profileAvatarDisplay').html(`<img src="${url}" alt="Avatar"><div class="upload-overlay">Change</div>`);
                            closeCropper();
                            Swal.fire({ icon: 'success', title: 'Photo Saved!', toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 });
                        } else { Swal.fire('Error', res.message, 'error'); }
                    },
                    error: function () { Swal.fire('Error', 'Upload failed.', 'error'); }
                });
            }, 'image/jpeg', 0.9);
        }
    }

    function requestUnlock() {
        Swal.fire({ title: 'Request Profile Unlock', text: 'Why is an edit needed?', input: 'textarea', showCancelButton: true, confirmButtonText: 'Send Request' }).then((result) => {
            if (result.isConfirmed && result.value) {
                $.post('backend.php', { request_profile_unlock: 1, reason: result.value }, function (res) {
                    if (res.status === 200) Swal.fire('Sent!', 'Request sent.', 'success').then(() => location.reload());
                    else Swal.fire('Error', res.message, 'error');
                }, 'json');
            }
        });
    }
</script>

<?php require 'includes/footer.php'; ?>