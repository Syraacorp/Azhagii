<?php
require_once 'config/db.php'; // Required for BASE_URL
session_start();
session_destroy();
header("Location: " . BASE_URL . "/login.php");
exit;
?>