<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

include_once __DIR__ . '/../db.php';
include_once __DIR__ . '/csrf.php';

// ── Session helpers ──
$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'] ?? '';
$userEmail = $_SESSION['user_email'] ?? '';
$role = $_SESSION['role'] ?? '';
$collegeId = $_SESSION['college_id'] ?? 0;
$collegeName = $_SESSION['college_name'] ?? '';

// ── Role guard helper ──
function requirePageRole($allowed)
{
    global $role;
    if (!is_array($allowed))
        $allowed = [$allowed];
    if (!in_array($role, $allowed)) {
        header('Location: dashboard.php');
        exit;
    }
}

// ── Dashboard URL helper ──
function dashboardUrl()
{
    global $role;
    $map = [
        'superAdmin' => 'ziyaaDashboard.php',
        'adminZiyaa' => 'adminDashboard.php',
        'ziyaaCoordinator' => 'coordinatorDashboard.php',
        'ziyaaStudents' => 'studentDashboard.php',
    ];
    return $map[$role] ?? 'dashboard.php';
}

$dashboardLink = dashboardUrl();

// ── Avatar initial ──
$avatarInitial = strtoupper(substr($userName, 0, 1));
if (!isset($_SESSION['profile_photo'])) {
    $uid = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT profile_photo FROM users WHERE id=?");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($prow = $result->fetch_assoc()) {
        $_SESSION['profile_photo'] = $prow['profile_photo'];
    }
    $stmt->close();
}
$profilePhoto = $_SESSION['profile_photo'] ?? '';

// ── Profile Completion Check ──
$currentScript = basename($_SERVER['PHP_SELF']);
$exemptScripts = ['profile.php', 'backend.php', 'logout.php', 'login.php', 'register.php', 'index.php'];

if (!in_array($currentScript, $exemptScripts) && isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT name, email, username, role, phone, bio, college_id, department, year, roll_number, profile_photo, github_url, linkedin_url, hackerrank_url, leetcode_url FROM users WHERE id=?");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($crow = $result->fetch_assoc()) {
        $filled = 0;
        $total = 0;

        // Basic (4)
        $basicProps = ['name', 'email', 'username', 'role'];
        foreach ($basicProps as $p) {
            $total++;
            if (!empty($crow[$p]))
                $filled++;
        }

        // Contact/Bio (2)
        $contactProps = ['phone', 'bio'];
        foreach ($contactProps as $p) {
            $total++;
            if (!empty($crow[$p]))
                $filled++;
        }

        // Academic (Depends on role)
        if ($crow['role'] == 'ziyaaStudents') {
            $acadProps = ['college_id', 'department', 'year', 'roll_number'];
            foreach ($acadProps as $p) {
                $total++;
                if (!empty($crow[$p]))
                    $filled++;
            }
        } else {
            if (!empty($crow['college_id'])) {
                $total++;
                $filled++;
            }
        }

        // Assets (5)
        $assetProps = ['profile_photo', 'github_url', 'linkedin_url', 'hackerrank_url', 'leetcode_url'];
        foreach ($assetProps as $p) {
            $total++;
            if (!empty($crow[$p]))
                $filled++;
        }

        $pct = ($total > 0) ? round(($filled / $total) * 100) : 0;

        if ($pct < 100) {
            header('Location: profile.php?incomplete=1');
            exit;
        }
    }
}
?>