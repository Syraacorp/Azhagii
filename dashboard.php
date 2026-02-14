<?php
// ── Smart Router: redirect each role to its own dashboard ──
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$role = $_SESSION['role'] ?? '';
$dashboardMap = [
    'superAdmin'       => 'azhagiiDashboard.php',
    'adminAzhagii'       => 'adminDashboard.php',
    'azhagiiCoordinator' => 'coordinatorDashboard.php',
    'azhagiiStudents'    => 'studentDashboard.php',
];

$target = $dashboardMap[$role] ?? null;

if (!$target) {
    // Prevent infinite loop: if role is invalid, force logout
    session_unset();
    session_destroy();
    header('Location: login.php?error=invalid_role');
    exit;
}

header("Location: $target");
exit;
?>
