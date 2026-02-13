<?php
$pageTitle   = 'Browse Courses';
$currentPage = 'browseCourses';
require 'includes/auth.php';
requirePageRole('ziyaaStudents');
require 'includes/header.php';
require 'includes/sidebar.php';
?>

<div class="cards-grid" id="browseCoursesGrid"></div>

<?php require 'includes/footer.php'; ?>
