<?php
$pageTitle   = 'My Courses';
$currentPage = 'myCourses';
require 'includes/auth.php';
requirePageRole('azhagiiCoordinator');
require 'includes/header.php';
require 'includes/sidebar.php';
?>

<div class="cards-grid" id="coordCoursesGrid"></div>

<?php require 'includes/footer.php'; ?>
