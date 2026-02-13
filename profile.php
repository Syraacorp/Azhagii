<?php
$pageTitle   = 'My Profile';
$currentPage = 'profile';
require 'includes/auth.php';
require 'includes/header.php';
require 'includes/sidebar.php';
?>

<div class="card" style="max-width:600px;">
    <h3 style="margin-bottom:1.5rem;">My Profile</h3>
    <form id="profileForm">
        <div class="form-group">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-input" id="profileName" required>
        </div>
        <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" class="form-input" id="profileEmail" disabled>
        </div>
        <div class="form-group">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" class="form-input" id="profilePhone">
        </div>
        <div class="form-group">
            <label class="form-label">College</label>
            <input type="text" class="form-input" id="profileCollege" disabled>
        </div>
        <div class="form-group">
            <label class="form-label">New Password (leave blank to keep current)</label>
            <input type="password" name="password" class="form-input" id="profilePassword" placeholder="Enter new password">
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Profile</button>
    </form>
</div>

<?php require 'includes/footer.php'; ?>
