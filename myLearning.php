<?php
$pageTitle   = 'My Learning';
$currentPage = 'myLearning';
require 'includes/auth.php';
requirePageRole('azhagiiStudents');
require 'includes/header.php';
require 'includes/sidebar.php';
?>

<div class="cards-grid" id="myLearningGrid"></div>

<?php require 'includes/footer.php'; ?>
