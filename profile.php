<?php
$pageTitle = 'My Profile';
$currentPage = 'profile';
require 'includes/auth.php';
require 'includes/header.php';
require 'includes/sidebar.php';
?>

<style>
    /* Profile specific layout */
    .profile-dashboard-grid {
        display: grid;
        grid-template-columns: 320px 1fr;
        /* Sidebar-like Left, Main Content */
        gap: 1rem;
        align-items: start;
    }

    /* Column containers */
    .profile-col {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    /* Cards matching dashboard stats/progress cards */
    .profile-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 1.25rem;
        transition: all 0.2s;
    }

    .profile-card:hover {
        border-color: var(--accent-blue);
        box-shadow: var(--shadow-soft);
    }

    /* Typography matches dashboard */
    .profile-card h3 {
        font-size: 1.1rem;
        margin-bottom: 1rem;
        color: var(--text-heading);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    /* Avatar styling */
    .profile-avatar-lg {
        width: 100px;
        height: 100px;
        margin: 0 auto 1rem auto;
        border-radius: 50%;
        background: var(--primary);
        color: #fff;
        font-size: 3rem;
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

    /* Identity Wrapper */
    .identity-wrapper {
        text-align: center;
    }

    .user-name-lg {
        font-size: 1.4rem;
        font-weight: 700;
        color: var(--text-heading);
        margin-bottom: 0.25rem;
    }

    .user-role-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 100px;
        background: rgba(66, 133, 244, 0.1);
        color: var(--accent-blue);
        font-size: 0.85rem;
        border: 1px solid rgba(66, 133, 244, 0.2);
    }

    /* Progress similar to dashboard bars */
    .profile-progress-wrap {
        margin-top: 1.5rem;
        text-align: left;
    }

    .profile-progress-track {
        width: 100%;
        height: 8px;
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

    /* Form Styles from Dashboard */
    .form-group-profile {
        margin-bottom: 0.75rem;
    }

    .form-label-profile {
        display: block;
        font-size: 0.85rem;
        color: var(--text-muted);
        margin-bottom: 0.4rem;
    }

    .form-input-profile {
        width: 100%;
        background: var(--input-bg);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-sm);
        padding: 0.75rem 1rem;
        color: var(--text-main);
        font-size: 0.95rem;
    }

    .form-input-profile:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .form-input-profile:focus {
        outline: none;
        border-color: var(--primary);
    }

    /* Social Icons */
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

    /* Responsive */
    @media (max-width: 1100px) {
        .profile-dashboard-grid {
            grid-template-columns: 1fr;
        }

        .profile-col {
            width: 100%;
        }

        /* Reorder for mobile: Identity first, then Main, then Social */
        .profile-col:nth-child(1) {
            order: 1;
        }

        .profile-col:nth-child(2) {
            order: 2;
        }

        .profile-col:nth-child(2) {
            order: 2;
        }
    }
</style>
<link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<style>
    /* Cropper Modal */
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
        /* Force solid background */
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
        width: 100%;
        background: #000;
        /* Solid background for image area */
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

<!-- NOTE: sidebar.php already opens a global .main-content and .content-wrapper -->
<!-- We just place our grid directly here -->

<form id="profileForm" enctype="multipart/form-data" class="profile-dashboard-grid">

    <!-- ═══ COL 1: IDENTITY (Left Panel) ═══ -->
    <div class="profile-col">
        <div class="profile-card identity-wrapper">
            <div class="profile-avatar-lg" id="profileAvatarDisplay"
                onclick="document.getElementById('profile_photo').click()">
                <?= $avatarInitial ?>
                <div class="upload-overlay">Change</div>
            </div>
            <input type="file" name="profile_photo" id="profile_photo" accept="image/*" style="display:none;"
                onchange="previewImage(this)">

            <div class="user-name-lg" id="displayNameHeader">Loading...</div>
            <div class="user-role-badge" id="displayRoleHeader">...</div>
            <div style="font-size:0.9rem;color:var(--text-muted);margin-top:0.5rem;" id="displayDeptHeader"></div>

            <div class="profile-progress-wrap">
                <div class="d-flex justify-content-between"
                    style="font-size:0.85rem; display:flex; justify-content:space-between;">
                    <span style="color:var(--text-heading);">Profile Strength</span>
                    <span id="progressText" style="font-weight:600;">0%</span>
                </div>
                <div class="profile-progress-track">
                    <div class="profile-progress-fill" id="progressBar" style="width: 0%"></div>
                </div>
                <div id="completionNote" style="font-size:0.8rem;margin-top:0.5rem;color:var(--text-muted);">Complete
                    all fields to finish.</div>
            </div>

            <hr style="border:0; border-top:1px solid var(--border-color); margin: 1.5rem 0;">

            <div class="save-btn-wrapper">
                <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>
        </div>

        <div class="profile-card">
            <h3><i class="fas fa-lock" style="color:var(--accent-pink);"></i> Security</h3>
            <div class="form-group-profile" style="margin-bottom:0;">
                <label class="form-label-profile">Change Password <span class="text-muted"
                        style="font-weight:normal;">(Optional)</span></label>
                <input type="password" name="password" id="password" class="form-input-profile"
                    placeholder="New Password">
            </div>
        </div>
    </div>

    <!-- ═══ COL 2: MAIN DETAILS (Center Panel) ═══ -->
    <div class="profile-col">
        <div class="profile-card">
            <h3><i class="fas fa-user-circle" style="color:var(--accent-purple);"></i> Personal & Academic Details</h3>

            <div class="row" style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
                <div class="form-group-profile">
                    <label class="form-label-profile">Full Name <span style="color:#ef4444">*</span></label>
                    <input type="text" name="name" id="name" class="form-input-profile" required>
                </div>
                <div class="form-group-profile">
                    <label class="form-label-profile">Phone Number</label>
                    <input type="text" name="phone" id="phone" class="form-input-profile" placeholder="+91...">
                </div>
            </div>

            <div class="row" style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
                <div class="form-group-profile">
                    <label class="form-label-profile">Gender</label>
                    <select name="gender" id="gender" class="form-input-profile">
                        <option value="">Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group-profile">
                    <label class="form-label-profile">Date of Birth</label>
                    <input type="date" name="dob" id="dob" class="form-input-profile">
                </div>
            </div>

            <div class="row" style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
                <div class="form-group-profile">
                    <label class="form-label-profile">Address</label>
                    <textarea name="address" id="address" class="form-input-profile" rows="1"
                        placeholder="Your address..."></textarea>
                </div>
                <div class="form-group-profile">
                    <label class="form-label-profile">Bio / Tagline</label>
                    <textarea name="bio" id="bio" class="form-input-profile" rows="1"
                        placeholder="Tell us about yourself..."></textarea>
                </div>
            </div>

            <div class="row" style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:1rem;">
                <div class="form-group-profile">
                    <label class="form-label-profile">Username</label>
                    <input type="text" id="username" class="form-input-profile" disabled>
                </div>
                <div class="form-group-profile">
                    <label class="form-label-profile">Email Address</label>
                    <input type="email" id="email" class="form-input-profile" disabled>
                </div>
                <div class="form-group-profile">
                    <label class="form-label-profile">College</label>
                    <input type="text" id="college" class="form-input-profile" disabled>
                </div>
            </div>

            <div id="studentFields" style="display:none;">
                <div class="row" style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:1rem;">
                    <div class="form-group-profile">
                        <label class="form-label-profile">Dept</label>
                        <input type="text" id="department" class="form-input-profile" disabled>
                    </div>
                    <div class="form-group-profile">
                        <label class="form-label-profile">Year</label>
                        <input type="text" id="year" class="form-input-profile" disabled>
                    </div>
                    <div class="form-group-profile">
                        <label class="form-label-profile">Roll No</label>
                        <input type="text" id="roll_number" class="form-input-profile" disabled>
                    </div>
                </div>
            </div>
        </div>

        <div class="profile-card" style="margin-top:1.5rem;">
            <h3><i class="fas fa-share-alt" style="color:var(--accent-blue);"></i> Social Profiles</h3>
            <div class="row" style="display:grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap:1rem;">
                <div class="form-group-profile">
                    <label class="form-label-profile">GitHub</label>
                    <div class="social-input-group">
                        <i class="fab fa-github"></i>
                        <input type="url" name="github_url" id="github_url" class="form-input-profile"
                            placeholder="https://github.com/...">
                    </div>
                </div>
                <div class="form-group-profile">
                    <label class="form-label-profile">LinkedIn</label>
                    <div class="social-input-group">
                        <i class="fab fa-linkedin" style="color:#0077b5;"></i>
                        <input type="url" name="linkedin_url" id="linkedin_url" class="form-input-profile"
                            placeholder="https://linkedin.com/...">
                    </div>
                </div>
                <div class="form-group-profile">
                    <label class="form-label-profile">HackerRank</label>
                    <div class="social-input-group">
                        <i class="fab fa-hackerrank" style="color:#2ec866;"></i>
                        <input type="url" name="hackerrank_url" id="hackerrank_url" class="form-input-profile"
                            placeholder="https://hackerrank.com/...">
                    </div>
                </div>
                <div class="form-group-profile">
                    <label class="form-label-profile">LeetCode</label>
                    <div class="social-input-group">
                        <i class="fas fa-code" style="color:#ffa116;"></i>
                        <input type="url" name="leetcode_url" id="leetcode_url" class="form-input-profile"
                            placeholder="https://leetcode.com/...">
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
            <button type="button" class="btn btn-primary" onclick="cropAndSave()">
                <i class="fas fa-check"></i> Save Photo
            </button>
        </div>
    </div>
</div>

<script>
    let cropper;
    let croppedBlob = null;

    $(document).ready(function () {
        // Move modal to body to avoid stacking context issues
        $('#cropperModal').appendTo('body');
    });

    function previewImage(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const reader = new FileReader();
            reader.onload = function (e) {
                $('#imageToCrop').attr('src', e.target.result);
                $('#cropperModal').css('display', 'flex');
                // Trigger reflow
                void document.getElementById('cropperModal').offsetWidth;
                $('#cropperModal').addClass('show');

                // Small delay to ensure modal is visible before initializing cropper
                setTimeout(() => {
                    const image = document.getElementById('imageToCrop');
                    if (cropper) cropper.destroy();

                    cropper = new Cropper(image, {
                        aspectRatio: 1,
                        viewMode: 1,
                        dragMode: 'move',
                        autoCropArea: 1,
                        background: false,
                        responsive: true,
                        restore: false,
                    });
                }, 100);
            }
            reader.readAsDataURL(file);
        }
    }

    function closeCropper() {
        $('#cropperModal').removeClass('show');
        setTimeout(() => {
            $('#cropperModal').hide();
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
            document.getElementById('profile_photo').value = '';
        }, 300); // Matches transition duration
    }

    function cropAndSave() {
        if (cropper) {
            cropper.getCroppedCanvas({
                width: 400, height: 400,
                fillColor: '#fff',
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high',
            }).toBlob((blob) => {
                croppedBlob = blob;
                const url = URL.createObjectURL(blob);
                $('#profileAvatarDisplay').html(`<img src="${url}" alt="Avatar"><div class="upload-overlay">Change</div>`);
                closeCropper();
            }, 'image/jpeg', 0.9);
        }
    }

    $(document).ready(function () {
        // Check incomplete
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('incomplete')) {
            Swal.fire({
                toast: true, position: 'top-end', icon: 'info',
                title: 'Please complete your profile',
                showConfirmButton: false, timer: 3000
            });
        }

        // Load Data
        $.post('backend.php', { get_my_profile: 1 }, function (res) {
            if (res.status === 200) {
                const d = res.data;

                $('#displayNameHeader').text(d.name);
                $('#displayRoleHeader').text(d.role.replace(/([A-Z])/g, ' $1').trim());
                const deptText = (d.department ? d.department : '') + (d.year ? ' - ' + d.year : '');
                $('#displayDeptHeader').text(deptText);

                $('#name').val(d.name);
                $('#username').val(d.username);
                $('#email').val(d.email);
                $('#phone').val(d.phone);
                $('#gender').val(d.gender);
                $('#dob').val(d.dob);
                $('#address').val(d.address);
                $('#bio').val(d.bio);

                $('#college').val(d.college_name + ' (' + d.college_code + ')');
                if (d.role === 'ziyaaStudents') {
                    $('#department').val(d.department);
                    $('#year').val(d.year);
                    $('#roll_number').val(d.roll_number);
                    $('#studentFields').show();
                }

                $('#github_url').val(d.github_url);
                $('#linkedin_url').val(d.linkedin_url);
                $('#hackerrank_url').val(d.hackerrank_url);
                $('#leetcode_url').val(d.leetcode_url);

                const pct = d.profile_completion || 0;
                $('#progressBar').css('width', pct + '%');
                $('#progressText').text(pct + '%');
                if (pct >= 100) {
                    $('#progressBar').removeClass('bg-warning').addClass('bg-success');
                    $('#completionNote').empty(); // Clear default message
                } else {
                    $('#progressBar').addClass('bg-warning');
                }

                // Avatar
                if (d.profile_photo) {
                    $('#profileAvatarDisplay').html(`<img src="${d.profile_photo}" alt="Avatar">` +
                        `<div class="upload-overlay">Change</div>`);
                } else {
                    $('#profileAvatarDisplay').html(d.name.charAt(0).toUpperCase() +
                        `<div class="upload-overlay">Change</div>`);
                }

                // HANDLE LOCKED STATE (Admins bypass)
                const adminRoles = ['superAdmin', 'adminZiyaa'];
                const isAdmin = adminRoles.includes(d.role);

                if (d.is_locked == 1 && !isAdmin) {
                    // Disable all inputs
                    $('input, textarea, select').not('[type=search]').prop('disabled', true);
                    $('#profileAvatarDisplay').attr('onclick', ''); // Disable click
                    $('.upload-overlay').remove(); // Remove overlay

                    // Replace Save Button with Locked Message
                    const btnArea = $('.save-btn-wrapper');
                    btnArea.empty();

                    if (d.unlock_request) {
                        btnArea.append(`
                        <div class="alert alert-warning p-2 text-center" style="font-size:0.85rem;">
                            <i class="fas fa-clock"></i> Unlock Pending<br>
                            Reason: ${d.unlock_request.request_reason}
                        </div>
                    `);
                    } else {
                        btnArea.append(`
                        <button type="button" class="btn btn-warning btn-save-full" onclick="requestUnlock()">
                            <i class="fas fa-lock"></i> Request Edit
                        </button>
                        <p class="text-muted text-center mt-2" style="font-size:0.75rem;">Profile locked after completion.</p>
                    `);
                    }

                    $('#completionNote').html('<span class="text-success"><i class="fas fa-lock"></i> Profile Locked</span>');
                } else if (d.is_locked == 1 && isAdmin) {
                    // Admin View: Warn but allow edit
                    $('#completionNote').html('<span class="text-warning"><i class="fas fa-unlock"></i> Locked (Admin Override)</span>');
                }
            } else {
                Swal.fire('Error', res.message || 'Failed to load profile', 'error');
            }
        }, 'json').fail(function(xhr, status, error) {
            console.error('Failed to load profile:', status, error);
            Swal.fire('Error', 'Failed to load profile data. Please refresh the page.', 'error');
        });

        // Submit
        $('#profileForm').submit(function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('update_my_profile', 1);

            if (croppedBlob) {
                formData.set('profile_photo', croppedBlob, 'profile.jpg');
            }

            const btn = $(this).find('button[type=submit]');
            const oldHtml = btn.html();
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

            $.ajax({
                url: 'backend.php', type: 'POST', data: formData,
                processData: false, contentType: false, dataType: 'json',
                success: function (res) {
                    if (res.status === 200) {
                        Swal.fire({
                            icon: 'success', title: 'Saved!',
                            toast: true, position: 'top-end',
                            showConfirmButton: false, timer: 1500
                        }).then(() => location.reload());
                    } else {
                        Swal.fire('Error', res.message, 'error');
                        btn.prop('disabled', false).html(oldHtml);
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error:', status, error, xhr.responseText);
                    Swal.fire('Error', 'Failed to save profile. Please try again.', 'error');
                    btn.prop('disabled', false).html(oldHtml);
                }
            });
        });
    });

    function requestUnlock() {
        Swal.fire({
            title: 'Request Profile Unlock',
            text: 'Please state why you need to edit your profile details.',
            input: 'textarea',
            inputPlaceholder: 'Reason for editing...',
            showCancelButton: true,
            confirmButtonText: 'Send Request'
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                $.post('backend.php', { request_profile_unlock: 1, reason: result.value }, function (res) {
                    if (res.status === 200) {
                        Swal.fire('Sent!', 'Your request has been sent to the admin.', 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                }, 'json').fail(function(xhr, status, error) {
                    console.error('Failed to send unlock request:', status, error);
                    Swal.fire('Error', 'Failed to send request. Please try again.', 'error');
                });
            }
        });
    }
</script>

<?php require 'includes/footer.php'; ?>