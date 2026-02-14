<?php
include_once __DIR__ . '/../db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['userId'])) {
    header('Location: login.php');
    exit;
}

include_once __DIR__ . '/csrf.php';

// ── Session helpers ──
$userId = $_SESSION['userId'];
$userName = $_SESSION['user_name'] ?? '';
$userEmail = $_SESSION['user_email'] ?? '';
$role = $_SESSION['role'] ?? '';
$collegeId = $_SESSION['collegeId'] ?? 0;
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

// ── Role check helper (Boolean) ──
if (!function_exists('hasRole')) {
    function hasRole($checkRole)
    {
        global $role;
        if (is_array($checkRole)) {
            return in_array($role, $checkRole);
        }
        return $role === $checkRole;
    }
}

// ── Dashboard URL helper ──
function dashboardUrl()
{
    global $role;
    $map = [
        'superAdmin' => 'azhagiiDashboard.php',
        'adminAzhagii' => 'adminDashboard.php',
        'azhagiiCoordinator' => 'coordinatorDashboard.php',
        'azhagiiStudents' => 'studentDashboard.php',
    ];
    return $map[$role] ?? 'dashboard.php';
}

$dashboardLink = dashboardUrl();

// ── Avatar initial ──
$avatarInitial = strtoupper(substr($userName, 0, 1));
if (!isset($_SESSION['profilePhoto'])) {
    $uid = $_SESSION['userId'];
    $stmt = $conn->prepare("SELECT profilePhoto FROM users WHERE id=?");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($prow = $result->fetch_assoc()) {
        $_SESSION['profilePhoto'] = $prow['profilePhoto'];
    }
    $stmt->close();
}
$profilePhoto = $_SESSION['profilePhoto'] ?? '';

// ── Profile Completion Check ──
$currentScript = basename($_SERVER['PHP_SELF']);
$exemptScripts = ['profile.php', 'backend.php', 'logout.php', 'login.php', 'register.php', 'index.php'];

if (!in_array($currentScript, $exemptScripts) && isset($_SESSION['userId'])) {
    $uid = $_SESSION['userId'];
    $stmt = $conn->prepare("SELECT name, email, username, role, phone, bio, address, dob, gender, collegeId, department, year, rollNumber, profilePhoto FROM users WHERE id=?");
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

        // Contact (2)
        $contactProps = ['phone', 'address'];
        foreach ($contactProps as $p) {
            $total++;
            if (!empty($crow[$p]))
                $filled++;
        }

        // Bio (1)
        $total++;
        if (!empty($crow['bio']))
            $filled++;

        // Personal (2)
        $personalProps = ['dob', 'gender'];
        foreach ($personalProps as $p) {
            $total++;
            if (!empty($crow[$p]))
                $filled++;
        }

        // Academic (Depends on role)
        if ($crow['role'] == 'azhagiiStudents') {
            $acadProps = ['collegeId', 'department', 'year', 'rollNumber'];
            foreach ($acadProps as $p) {
                $total++;
                if (!empty($crow[$p]))
                    $filled++;
            }
        } else {
            if (!empty($crow['collegeId'])) {
                $total++;
                $filled++;
            }
        }

        // Assets (1) - Only profile photo counts, social profiles are optional
        $total++;
        if (!empty($crow['profilePhoto']))
            $filled++;

        $pct = ($total > 0) ? round(($filled / $total) * 100) : 0;

        if ($pct < 100) {
            header('Location: profile.php?incomplete=1');
            exit;
        }
    }
}
?>