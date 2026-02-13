<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

include_once __DIR__ . '/../db.php';

// ── Session helpers ──
$userId      = $_SESSION['user_id'];
$userName    = $_SESSION['user_name'] ?? '';
$userEmail   = $_SESSION['user_email'] ?? '';
$role        = $_SESSION['role'] ?? '';
$collegeId   = $_SESSION['college_id'] ?? 0;
$collegeName = $_SESSION['college_name'] ?? '';

// ── Role guard helper ──
function requirePageRole($allowed) {
    global $role;
    if (!is_array($allowed)) $allowed = [$allowed];
    if (!in_array($role, $allowed)) {
        header('Location: dashboard.php');
        exit;
    }
}

// ── Dashboard URL helper ──
function dashboardUrl() {
    global $role;
    $map = [
        'superAdmin'       => 'ziyaaDashboard.php',
        'adminZiyaa'       => 'adminDashboard.php',
        'ziyaaCoordinator' => 'coordinatorDashboard.php',
        'ziyaaStudents'    => 'studentDashboard.php',
    ];
    return $map[$role] ?? 'dashboard.php';
}

$dashboardLink = dashboardUrl();

// ── Avatar initial ──
$avatarInitial = strtoupper(substr($userName, 0, 1));
?>
