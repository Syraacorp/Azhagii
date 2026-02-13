<?php
// ── Smart Router: redirect each role to its own dashboard ──
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$role = $_SESSION['role'] ?? '';
$dashboardMap = [
    'superAdmin'       => 'superAdminDashboard.php',
    'adminZiyaa'       => 'adminZiyaaDashboard.php',
    'ziyaaCoordinator' => 'coordinatorDashboard.php',
    'ziyaaStudents'    => 'studentDashboard.php',
];

$target = $dashboardMap[$role] ?? 'login.php';
header("Location: $target");
exit;
?>
