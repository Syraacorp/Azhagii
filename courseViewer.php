<?php
$pageTitle   = 'Course Viewer';
$currentPage = 'courseViewer';
require 'includes/auth.php';
requirePageRole('azhagiiStudents');
require 'includes/header.php';
require 'includes/sidebar.php';
?>

<a href="myLearning.php" class="btn btn-outline" style="margin-bottom:1.5rem;"><i class="fas fa-arrow-left"></i> Back to My Learning</a>
<div id="courseViewContainer"></div>

<?php require 'includes/footer.php'; ?>
