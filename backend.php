<?php
ob_start();
error_reporting(0);
ini_set('display_errors', 0);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

include "db.php";
include_once "includes/csrf.php";

if (!isset($conn) || !$conn) {
    echo json_encode(['status' => 500, 'message' => 'Database connection failed']);
    exit;
}

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', 3600);
    session_start();
}

// ── Helpers ──────────────────────────────────────────────
function respond($status, $msg, $data = null)
{
    $r = ['status' => $status, 'message' => $msg];
    if ($data !== null)
        $r['data'] = $data;
    ob_end_clean();
    $json = json_encode($r, JSON_INVALID_UTF8_SUBSTITUTE);
    echo $json !== false ? $json : json_encode(['status' => $status, 'message' => $msg]);
    exit;
}
function esc($v)
{
    global $conn;
    return mysqli_real_escape_string($conn, $v);
}

// Secure file upload helper
function uploadFile($fileKey, $allowedMimes, $allowedExts, $maxSizeMB, $uploadDir, $prefix = 'file') {
    if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    
    // Check MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $_FILES[$fileKey]['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime, $allowedMimes)) {
        respond(400, "Invalid file type. Allowed MIME types: " . implode(', ', $allowedMimes));
    }
    
    // Check extension
    $ext = strtolower(pathinfo($_FILES[$fileKey]['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExts)) {
        respond(400, "Invalid file extension. Allowed: " . implode(', ', $allowedExts));
    }
    
    // Check file size
    if ($_FILES[$fileKey]['size'] > $maxSizeMB * 1024 * 1024) {
        respond(400, "File too large. Maximum size: {$maxSizeMB}MB");
    }
    
    // Generate safe filename
    $fname = $prefix . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    
    // Ensure directory exists
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            respond(500, "Failed to create upload directory");
        }
    }
    
    $targetFile = "$uploadDir/$fname";
    if (move_uploaded_file($_FILES[$fileKey]['tmp_name'], $targetFile)) {
        return $targetFile;
    } else {
        respond(500, "Failed to save uploaded file");
    }
}

function isLogged()
{
    return isset($_SESSION['user_id']);
}
function role()
{
    return $_SESSION['role'] ?? '';
}
function uid()
{
    return $_SESSION['user_id'] ?? 0;
}
function cid()
{
    return $_SESSION['college_id'] ?? 0;
}
function hasRole($roles)
{
    return in_array(role(), (array) $roles);
}
function requireLogin()
{
    if (!isLogged())
        respond(401, 'Please login first');
}
function requireRole($roles)
{
    requireLogin();
    if (!hasRole($roles))
        respond(403, 'Access denied');
}

// ══════════════════════════════════════════════════════════
//  AUTHENTICATION
// ══════════════════════════════════════════════════════════

if (isset($_POST['login_user'])) {
    $username = esc($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $stmt = $conn->prepare("SELECT u.*, c.name as college_name FROM users u LEFT JOIN colleges c ON u.college_id=c.id WHERE u.username=? AND u.status='active'");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($u = $result->fetch_assoc()) {
        // Check if password is hashed (starts with $2y$ for bcrypt)
        $passwordValid = false;
        if (strpos($u['password'], '$2y$') === 0) {
            // Password is hashed, use password_verify
            $passwordValid = password_verify($password, $u['password']);
        } else {
            // Legacy plaintext password - verify and rehash
            if ($password === $u['password']) {
                $passwordValid = true;
                // Rehash password for security
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $updateStmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
                $updateStmt->bind_param("si", $hashedPassword, $u['id']);
                $updateStmt->execute();
                $updateStmt->close();
            }
        }
        
        if ($passwordValid) {
            $_SESSION['user_id'] = $u['id'];
            $_SESSION['user_name'] = $u['name'];
            $_SESSION['user_email'] = $u['email'];
            $_SESSION['role'] = $u['role'];
            $_SESSION['college_id'] = $u['college_id'];
            $_SESSION['college_name'] = $u['college_name'] ?? '';
            $_SESSION['profile_photo'] = $u['profile_photo'] ?? '';
            respond(200, 'Login successful', ['role' => $u['role'], 'name' => $u['name']]);
        }
    }
    $stmt->close();
    respond(401, 'Invalid username or password');
}

if (isset($_POST['register_student'])) {
    $name = esc($_POST['name'] ?? '');
    $email = esc($_POST['email'] ?? '');
    $username = esc($_POST['username'] ?? '');
    $raw_password = $_POST['password'] ?? '';
    $college_id = intval($_POST['college_id'] ?? 0);
    $department = esc($_POST['department'] ?? '');
    $year = esc($_POST['year'] ?? '');
    $roll_number = esc($_POST['roll_number'] ?? '');
    $phone = esc($_POST['phone'] ?? '');
    
    if (!$name || !$email || !$username || !$raw_password || !$college_id || !$department || !$year || !$roll_number)
        respond(400, 'All required fields must be filled');
    if (strlen($username) < 4 || !preg_match('/^[a-zA-Z0-9_]+$/', $username))
        respond(400, 'Username must be at least 4 characters (letters, numbers, underscores only)');
    if (strlen($raw_password) < 6)
        respond(400, 'Password must be at least 6 characters');
    if (strlen($roll_number) !== 12)
        respond(400, 'Roll number must be exactly 12 characters');
    
    // Check existing email
    $stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $stmt->close();
        respond(409, 'Email already registered');
    }
    $stmt->close();
    
    // Check existing username
    $stmt = $conn->prepare("SELECT id FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $stmt->close();
        respond(409, 'Username already taken');
    }
    $stmt->close();
    
    // Check existing roll number
    $stmt = $conn->prepare("SELECT id FROM users WHERE roll_number=?");
    $stmt->bind_param("s", $roll_number);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $stmt->close();
        respond(409, 'Roll number already registered');
    }
    $stmt->close();
    
    // Validate college
    $stmt = $conn->prepare("SELECT id FROM colleges WHERE id=? AND status='active'");
    $stmt->bind_param("i", $college_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows == 0) {
        $stmt->close();
        respond(400, 'Invalid college');
    }
    $stmt->close();
    
    // Hash password securely
    $hashed = password_hash($raw_password, PASSWORD_DEFAULT);
    
    // Insert user
    $stmt = $conn->prepare("INSERT INTO users (name,email,username,password,role,college_id,department,year,roll_number,phone) VALUES (?,?,?,?,'ziyaaStudents',?,?,?,?,?)");
    $stmt->bind_param("ssssissss", $name, $email, $username, $hashed, $college_id, $department, $year, $roll_number, $phone);
    
    if ($stmt->execute()) {
        $newId = $stmt->insert_id;
        $stmt->close();
        
        // Get college name
        $stmt = $conn->prepare("SELECT name FROM colleges WHERE id=?");
        $stmt->bind_param("i", $college_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $collegeName = '';
        if ($row = $result->fetch_assoc()) {
            $collegeName = $row['name'];
        }
        $stmt->close();
        
        // Auto-login after registration
        $_SESSION['user_id'] = $newId;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;
        $_SESSION['role'] = 'ziyaaStudents';
        $_SESSION['college_id'] = $college_id;
        $_SESSION['college_name'] = $collegeName;
        respond(200, 'Registration successful!');
    } else {
        $error = $stmt->error;
        $stmt->close();
        respond(500, 'Registration failed: ' . $error);
    }
}

if (isset($_POST['check_session'])) {
    if (isLogged()) {
        respond(200, 'Active', [
            'user_id' => uid(),
            'user_name' => $_SESSION['user_name'],
            'user_email' => $_SESSION['user_email'] ?? '',
            'role' => role(),
            'college_id' => cid(),
            'college_name' => $_SESSION['college_name'] ?? ''
        ]);
    }
    respond(401, 'No session');
}

if (isset($_POST['logout_user'])) {
    session_destroy();
    respond(200, 'Logged out');
}

// ══════════════════════════════════════════════════════════
//  PROFILE (Self-Service)
// ══════════════════════════════════════════════════════════

if (isset($_POST['get_my_profile'])) {
    requireLogin();
    $uid = uid();
    
    $stmt = $conn->prepare("SELECT u.*, c.name as college_name, c.code as college_code 
          FROM users u 
          LEFT JOIN colleges c ON u.college_id=c.id 
          WHERE u.id=?");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $stmt->close();
        unset($row['password']);
        // Calculate completion
        $filled = 0;
        $total = 0;

        // Basic (4)
        $basicProps = ['name', 'email', 'username', 'role'];
        foreach ($basicProps as $p) {
            $total++;
            if (!empty($row[$p]))
                $filled++;
        }

        // Contact (2)
        $contactProps = ['phone', 'address'];
        foreach ($contactProps as $p) {
            $total++;
            if (!empty($row[$p]))
                $filled++;
        }

        // Bio (1)
        $bioProps = ['bio'];
        foreach ($bioProps as $p) {
            $total++;
            if (!empty($row[$p]))
                $filled++;
        }

        // Personal (2)
        $personalProps = ['dob', 'gender'];
        foreach ($personalProps as $p) {
            $total++;
            if (!empty($row[$p]))
                $filled++;
        }

        // Academic (4) - only for students/coordinators mainly, but let's count for all for now or check role
        if ($row['role'] == 'ziyaaStudents') {
            $acadProps = ['college_id', 'department', 'year', 'roll_number'];
            foreach ($acadProps as $p) {
                $total++;
                if (!empty($row[$p]))
                    $filled++;
            }
        } else {
            // For others, college_id might be relevant, others not so much. 
            // Let's keep it simple: if fields exist in DB and relevant to role.
            if (!empty($row['college_id'])) {
                $total++;
                $filled++;
            } // college is usually required for logic
        }

        // Assets (5)
        $assetProps = ['profile_photo', 'github_url', 'linkedin_url', 'hackerrank_url', 'leetcode_url'];
        foreach ($assetProps as $p) {
            $total++;
            if (!empty($row[$p]))
                $filled++;
        }

        $pct = ($total > 0) ? round(($filled / $total) * 100) : 0;
        $row['profile_completion'] = $pct;

        // Auto-lock if 100% complete and not already locked
        if ($pct == 100 && (!isset($row['is_locked']) || $row['is_locked'] == 0)) {
            // Check if column exists physically (we ran migration but safe check)
            if (array_key_exists('is_locked', $row)) {
                $stmt2 = $conn->prepare("UPDATE users SET is_locked=1 WHERE id=?");
                $stmt2->bind_param("i", $uid);
                $stmt2->execute();
                $stmt2->close();
                $row['is_locked'] = 1;
            }
        }

        // Check for pending unlock request
        $stmt3 = $conn->prepare("SELECT status, request_reason FROM profile_requests WHERE user_id=? AND status='pending' ORDER BY created_at DESC LIMIT 1");
        $stmt3->bind_param("i", $uid);
        $stmt3->execute();
        $result3 = $stmt3->get_result();
        if ($prow = $result3->fetch_assoc()) {
            $row['unlock_request'] = $prow;
        }
        $stmt3->close();

        respond(200, 'OK', $row);
    }
    $stmt->close();
    respond(404, 'User not found');
}

if (isset($_POST['update_my_profile'])) {
    requireLogin();
    $uid = uid();

    // Check lock status - Admins bypass lock
    $stmt = $conn->prepare("SELECT is_locked, role FROM users WHERE id=?");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $allowedRoles = ['superAdmin', 'adminZiyaa'];
        if ($row['is_locked'] == 1 && !in_array($row['role'], $allowedRoles)) {
            $stmt->close();
            respond(403, 'Profile is locked. Request unlock from admin.');
        }
    }
    $stmt->close();

    $name = esc($_POST['name'] ?? '');
    $phone = esc($_POST['phone'] ?? '');
    $bio = esc($_POST['bio'] ?? '');
    $gender = esc($_POST['gender'] ?? '');
    $dob = esc($_POST['dob'] ?? '');
    $address = esc($_POST['address'] ?? '');
    $git = esc($_POST['github_url'] ?? '');
    $li = esc($_POST['linkedin_url'] ?? '');
    $hr = esc($_POST['hackerrank_url'] ?? '');
    $lc = esc($_POST['leetcode_url'] ?? '');

    // Optional: Password update
    $hashedPassword = null;
    if (!empty($_POST['password'])) {
        $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
    }

    // Optional: Profile Photo
    $photoPath = null;
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        // Check file type by MIME type AND extension
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $_FILES['profile_photo']['tmp_name']);
        finfo_close($finfo);
        
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $ext = strtolower(pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (!in_array($mime, $allowedMimes) || !in_array($ext, $allowedExts)) {
            respond(400, "Invalid image format. Allowed: JPG, PNG, WEBP, GIF");
        }

        // Check file size (max 5MB)
        if ($_FILES['profile_photo']['size'] > 5 * 1024 * 1024) {
            respond(400, "File too large. Maximum size: 5MB");
        }

        $fname = 'profile_' . $uid . '_' . time() . '.' . $ext;
        $targetDir = 'uploads/profiles';

        if (!is_dir($targetDir)) {
            if (!mkdir($targetDir, 0755, true)) {
                respond(500, "Failed to create upload directory");
            }
        }

        $targetFile = "$targetDir/$fname";
        if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $targetFile)) {
            $photoPath = $targetFile;
        } else {
            respond(500, "Failed to save uploaded file");
        }
    } elseif (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        respond(400, "File upload error code: " . $_FILES['profile_photo']['error']);
    }

    // Build update query with prepared statement
    if ($hashedPassword !== null && $photoPath !== null) {
        $stmt = $conn->prepare("UPDATE users SET name=?, phone=?, bio=?, gender=?, dob=NULLIF(?,''), address=?, github_url=?, linkedin_url=?, hackerrank_url=?, leetcode_url=?, password=?, profile_photo=? WHERE id=?");
        $stmt->bind_param("ssssssssssssi", $name, $phone, $bio, $gender, $dob, $address, $git, $li, $hr, $lc, $hashedPassword, $photoPath, $uid);
    } elseif ($hashedPassword !== null) {
        $stmt = $conn->prepare("UPDATE users SET name=?, phone=?, bio=?, gender=?, dob=NULLIF(?,''), address=?, github_url=?, linkedin_url=?, hackerrank_url=?, leetcode_url=?, password=? WHERE id=?");
        $stmt->bind_param("sssssssssssi", $name, $phone, $bio, $gender, $dob, $address, $git, $li, $hr, $lc, $hashedPassword, $uid);
    } elseif ($photoPath !== null) {
        $stmt = $conn->prepare("UPDATE users SET name=?, phone=?, bio=?, gender=?, dob=NULLIF(?,''), address=?, github_url=?, linkedin_url=?, hackerrank_url=?, leetcode_url=?, profile_photo=? WHERE id=?");
        $stmt->bind_param("sssssssssssi", $name, $phone, $bio, $gender, $dob, $address, $git, $li, $hr, $lc, $photoPath, $uid);
    } else {
        $stmt = $conn->prepare("UPDATE users SET name=?, phone=?, bio=?, gender=?, dob=NULLIF(?,''), address=?, github_url=?, linkedin_url=?, hackerrank_url=?, leetcode_url=? WHERE id=?");
        $stmt->bind_param("ssssssssssi", $name, $phone, $bio, $gender, $dob, $address, $git, $li, $hr, $lc, $uid);
    }

    if ($stmt->execute()) {
        $stmt->close();
        $_SESSION['user_name'] = $name; // update session
        if ($photoPath !== null) {
            $_SESSION['profile_photo'] = $photoPath;
        }
        respond(200, 'Profile updated successfully');
    } else {
        $error = $stmt->error;
        $stmt->close();
        respond(500, 'Update failed: ' . $error);
    }
}

if (isset($_POST['request_profile_unlock'])) {
    requireLogin();
    $uid = uid();
    $reason = esc($_POST['reason'] ?? '');
    if (!$reason)
        respond(400, 'Reason is required');

    // Check if already pending
    $stmt = $conn->prepare("SELECT id FROM profile_requests WHERE user_id=? AND status='pending'");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $stmt->close();
        respond(400, 'Request already pending');
    }
    $stmt->close();

    $stmt2 = $conn->prepare("INSERT INTO profile_requests (user_id, request_reason) VALUES (?, ?)");
    $stmt2->bind_param("is", $uid, $reason);
    if ($stmt2->execute()) {
        $stmt2->close();
        respond(200, 'Unlock request sent to admin');
    } else {
        $error = $stmt2->error;
        $stmt2->close();
        respond(500, 'Request failed: ' . $error);
    }
}

// ── Admin: Review Profile Requests ──────────────────────
if (isset($_POST['get_profile_requests'])) {
    requireRole(['superAdmin', 'adminZiyaa']);
    $q = "SELECT pr.*, u.name as user_name, u.email as user_email, col.name as college_name 
          FROM profile_requests pr 
          JOIN users u ON pr.user_id=u.id 
          LEFT JOIN colleges col ON u.college_id=col.id
          WHERE pr.status='pending' ORDER BY pr.created_at DESC";
    $r = mysqli_query($conn, $q);
    $data = [];
    while ($r && $row = mysqli_fetch_assoc($r))
        $data[] = $row;
    respond(200, 'OK', $data);
}

if (isset($_POST['resolve_profile_request'])) {
    requireRole(['superAdmin', 'adminZiyaa']);
    $req_id = intval($_POST['request_id'] ?? 0);
    $action = $_POST['action'] ?? ''; // approve or reject
    $by = uid();

    if (!in_array($action, ['approve', 'reject']))
        respond(400, 'Invalid action');

    $status = ($action === 'approve') ? 'approved' : 'rejected';

    // Start transaction
    mysqli_begin_transaction($conn);

    try {
        // Update request
        $stmt = $conn->prepare("UPDATE profile_requests SET status=?, resolved_by=?, resolved_at=NOW() WHERE id=?");
        $stmt->bind_param("sii", $status, $by, $req_id);
        $stmt->execute();
        $stmt->close();

        // If approved, unlock user
        if ($action === 'approve') {
            // Get user_id from request
            $stmt2 = $conn->prepare("SELECT user_id FROM profile_requests WHERE id=?");
            $stmt2->bind_param("i", $req_id);
            $stmt2->execute();
            $result = $stmt2->get_result();
            if ($row = $result->fetch_assoc()) {
                $target_uid = $row['user_id'];
                $stmt3 = $conn->prepare("UPDATE users SET is_locked=0 WHERE id=?");
                $stmt3->bind_param("i", $target_uid);
                $stmt3->execute();
                $stmt3->close();
            }
            $stmt2->close();
        }

        mysqli_commit($conn);
        respond(200, 'Request ' . $status);
    } catch (Exception $e) {
        mysqli_rollback($conn);
        respond(500, 'Failed to resolve request');
    }
}

// ══════════════════════════════════════════════════════════
//  COLLEGES (superAdmin)
// ══════════════════════════════════════════════════════════

if (isset($_POST['get_colleges'])) {
    requireLogin();
    $q = "SELECT c.*, (SELECT COUNT(*) FROM users WHERE college_id=c.id) as user_count FROM colleges c ORDER BY c.name";
    $r = mysqli_query($conn, $q);
    $data = [];
    while ($r && $row = mysqli_fetch_assoc($r))
        $data[] = $row;
    respond(200, 'OK', $data);
}

if (isset($_POST['get_colleges_public'])) {
    $r = mysqli_query($conn, "SELECT id, name, code, city FROM colleges WHERE status='active' ORDER BY name");
    $data = [];
    while ($r && $row = mysqli_fetch_assoc($r))
        $data[] = $row;
    respond(200, 'OK', $data);
}

if (isset($_POST['add_college'])) {
    requireRole('superAdmin');
    $name = esc($_POST['name'] ?? '');
    $code = esc($_POST['code'] ?? '');
    $address = esc($_POST['address'] ?? '');
    $city = esc($_POST['city'] ?? '');
    
    if (!$name || !$code)
        respond(400, 'Name and code are required');
    
    // Check if code already exists
    $stmt = $conn->prepare("SELECT id FROM colleges WHERE code=?");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $stmt->close();
        respond(409, 'College code already exists');
    }
    $stmt->close();
    
    $stmt2 = $conn->prepare("INSERT INTO colleges (name,code,address,city) VALUES (?,?,?,?)");
    $stmt2->bind_param("ssss", $name, $code, $address, $city);
    if ($stmt2->execute()) {
        $stmt2->close();
        respond(200, 'College added');
    } else {
        $error = $stmt2->error;
        $stmt2->close();
        respond(500, 'Failed to add college: ' . $error);
    }
}

if (isset($_POST['update_college'])) {
    requireRole('superAdmin');
    $id = intval($_POST['id'] ?? 0);
    $name = esc($_POST['name'] ?? '');
    $code = esc($_POST['code'] ?? '');
    $address = esc($_POST['address'] ?? '');
    $city = esc($_POST['city'] ?? '');
    $status = esc($_POST['status'] ?? 'active');
    
    // Check if code already exists for other colleges
    $stmt = $conn->prepare("SELECT id FROM colleges WHERE code=? AND id!=?");
    $stmt->bind_param("si", $code, $id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $stmt->close();
        respond(409, 'College code already exists');
    }
    $stmt->close();
    
    $stmt2 = $conn->prepare("UPDATE colleges SET name=?,code=?,address=?,city=?,status=? WHERE id=?");
    $stmt2->bind_param("sssssi", $name, $code, $address, $city, $status, $id);
    if ($stmt2->execute()) {
        $stmt2->close();
        respond(200, 'College updated');
    } else {
        $error = $stmt2->error;
        $stmt2->close();
        respond(500, 'Update failed: ' . $error);
    }
}

if (isset($_POST['delete_college'])) {
    requireRole('superAdmin');
    $id = intval($_POST['id'] ?? 0);
    
    $stmt = $conn->prepare("DELETE FROM colleges WHERE id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $stmt->close();
        respond(200, 'College deleted');
    } else {
        $error = $stmt->error;
        $stmt->close();
        respond(500, 'Delete failed: ' . $error);
    }
}

// ══════════════════════════════════════════════════════════
//  USERS (superAdmin, adminZiyaa)
// ══════════════════════════════════════════════════════════

if (isset($_POST['get_users'])) {
    requireRole(['superAdmin', 'adminZiyaa']);
    $where = "1=1";
    if (isset($_POST['role_filter']) && $_POST['role_filter'])
        $where .= " AND u.role='" . esc($_POST['role_filter']) . "'";
    if (isset($_POST['college_filter']) && $_POST['college_filter'])
        $where .= " AND u.college_id=" . intval($_POST['college_filter']);
    // adminZiyaa cannot see superAdmin users
    if (role() === 'adminZiyaa')
        $where .= " AND u.role != 'superAdmin'";
    $q = "SELECT u.*, c.name as college_name FROM users u LEFT JOIN colleges c ON u.college_id=c.id WHERE $where ORDER BY u.created_at DESC";
    $r = mysqli_query($conn, $q);
    $data = [];
    while ($r && $row = mysqli_fetch_assoc($r)) {
        unset($row['password']);
        $data[] = $row;
    }
    respond(200, 'OK', $data);
}

if (isset($_POST['add_user'])) {
    requireRole(['superAdmin', 'adminZiyaa']);
    $name = esc($_POST['name'] ?? '');
    $email = esc($_POST['email'] ?? '');
    $raw_password = $_POST['password'] ?? '';
    $urole = esc($_POST['role'] ?? '');
    $college_id = intval($_POST['college_id'] ?? 0);
    $phone = esc($_POST['phone'] ?? '');
    
    if (!$name || !$email || !$raw_password || !$urole)
        respond(400, 'Required fields missing');
    
    // adminZiyaa cannot create superAdmin or other adminZiyaa
    if (role() === 'adminZiyaa' && in_array($urole, ['superAdmin', 'adminZiyaa']))
        respond(403, 'Cannot create this role');
    
    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $stmt->close();
        respond(409, 'Email already exists');
    }
    $stmt->close();
    
    // Hash password securely
    $hashed_pw = password_hash($raw_password, PASSWORD_DEFAULT);
    $cid_sql = $college_id ? $college_id : null;
    
    $stmt2 = $conn->prepare("INSERT INTO users (name,email,password,role,college_id,phone) VALUES (?,?,?,?,?,?)");
    $stmt2->bind_param("ssssis", $name, $email, $hashed_pw, $urole, $cid_sql, $phone);
    
    if ($stmt2->execute()) {
        $stmt2->close();
        respond(200, 'User added');
    } else {
        $error = $stmt2->error;
        $stmt2->close();
        respond(500, 'Failed to add user: ' . $error);
    }
}

if (isset($_POST['update_user'])) {
    requireRole(['superAdmin', 'adminZiyaa']);
    $id = intval($_POST['id'] ?? 0);
    
    // adminZiyaa cannot edit superAdmin users
    if (role() === 'adminZiyaa') {
        $stmt = $conn->prepare("SELECT role FROM users WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($targetRow = $result->fetch_assoc()) {
            if ($targetRow['role'] === 'superAdmin') {
                $stmt->close();
                respond(403, 'Cannot edit this user');
            }
        }
        $stmt->close();
    }
    
    $name = esc($_POST['name'] ?? '');
    $email = esc($_POST['email'] ?? '');
    $urole = esc($_POST['role'] ?? '');
    $college_id = intval($_POST['college_id'] ?? 0);
    $phone = esc($_POST['phone'] ?? '');
    $status = esc($_POST['status'] ?? 'active');
    
    // adminZiyaa cannot escalate roles to superAdmin or adminZiyaa
    if (role() === 'adminZiyaa' && in_array($urole, ['superAdmin', 'adminZiyaa']))
        respond(403, 'Cannot assign this role');
    
    $cid_sql = $college_id ? $college_id : null;
    
    // Check if email exists for other users
    $stmt2 = $conn->prepare("SELECT id FROM users WHERE email=? AND id!=?");
    $stmt2->bind_param("si", $email, $id);
    $stmt2->execute();
    if ($stmt2->get_result()->num_rows > 0) {
        $stmt2->close();
        respond(409, 'Email already exists');
    }
    $stmt2->close();
    
    // Update with or without password
    if (isset($_POST['password']) && $_POST['password'] !== '') {
        $hashed_pw = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt3 = $conn->prepare("UPDATE users SET name=?,email=?,role=?,college_id=?,phone=?,status=?,password=? WHERE id=?");
        $stmt3->bind_param("sssisssi", $name, $email, $urole, $cid_sql, $phone, $status, $hashed_pw, $id);
    } else {
        $stmt3 = $conn->prepare("UPDATE users SET name=?,email=?,role=?,college_id=?,phone=?,status=? WHERE id=?");
        $stmt3->bind_param("sssissi", $name, $email, $urole, $cid_sql, $phone, $status, $id);
    }
    
    if ($stmt3->execute()) {
        $stmt3->close();
        respond(200, 'User updated');
    } else {
        $error = $stmt3->error;
        $stmt3->close();
        respond(500, 'Update failed: ' . $error);
    }
}

if (isset($_POST['delete_user'])) {
    requireRole(['superAdmin', 'adminZiyaa']);
    $id = intval($_POST['id'] ?? 0);
    
    if ($id == uid())
        respond(400, 'Cannot delete yourself');
    
    // adminZiyaa cannot delete superAdmin users
    if (role() === 'adminZiyaa') {
        $stmt = $conn->prepare("SELECT role FROM users WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($targetRow = $result->fetch_assoc()) {
            if ($targetRow['role'] === 'superAdmin') {
                $stmt->close();
                respond(403, 'Cannot delete this user');
            }
        }
        $stmt->close();
    }
    
    $stmt2 = $conn->prepare("DELETE FROM users WHERE id=?");
    $stmt2->bind_param("i", $id);
    if ($stmt2->execute()) {
        $stmt2->close();
        respond(200, 'User deleted');
    } else {
        $error = $stmt2->error;
        $stmt2->close();
        respond(500, 'Delete failed: ' . $error);
    }
}

// ══════════════════════════════════════════════════════════
//  COURSES (superAdmin, adminZiyaa)
// ══════════════════════════════════════════════════════════

if (isset($_POST['get_courses'])) {
    requireLogin();
    $where = "1=1";
    if (hasRole(['ziyaaCoordinator'])) {
        // Coordinator sees courses assigned to their college + courses they created
        $cid = cid();
        $uid = uid();
        $where = "(c.id IN (SELECT course_id FROM course_colleges WHERE college_id=$cid) OR c.created_by=$uid)";
    } else if (hasRole(['ziyaaStudents'])) {
        $cid = cid();
        $where = "c.status='active' AND c.id IN (SELECT course_id FROM course_colleges WHERE college_id=$cid)";
    }
    // Status filter
    if (isset($_POST['status_filter']) && $_POST['status_filter'])
        $where .= " AND c.status='" . esc($_POST['status_filter']) . "'";
    $q = "SELECT c.*, u.name as creator_name, ap.name as approver_name,
          (SELECT COUNT(*) FROM course_colleges WHERE course_id=c.id) as college_count,
          (SELECT COUNT(*) FROM enrollments WHERE course_id=c.id) as enrollment_count,
          (SELECT COUNT(*) FROM course_content WHERE course_id=c.id) as content_count,
          (SELECT COUNT(*) FROM subjects WHERE course_id=c.id) as subject_count
          FROM courses c LEFT JOIN users u ON c.created_by=u.id
          LEFT JOIN users ap ON c.approved_by=ap.id
          WHERE $where ORDER BY c.created_at DESC";
    $r = mysqli_query($conn, $q);
    $data = [];
    while ($r && $row = mysqli_fetch_assoc($r))
        $data[] = $row;
    respond(200, 'OK', $data);
}

// ── Get Pending Courses (for approval) ───────────────────
if (isset($_POST['get_pending_courses'])) {
    requireRole(['superAdmin', 'adminZiyaa']);
    $q = "SELECT c.*, u.name as creator_name, col.name as creator_college,
          (SELECT COUNT(*) FROM subjects WHERE course_id=c.id) as subject_count
          FROM courses c
          LEFT JOIN users u ON c.created_by=u.id
          LEFT JOIN colleges col ON u.college_id=col.id
          WHERE c.status='pending' ORDER BY c.created_at DESC";
    $r = mysqli_query($conn, $q);
    $data = [];
    while ($r && $row = mysqli_fetch_assoc($r))
        $data[] = $row;
    respond(200, 'OK', $data);
}

// ── Get Course Detail (with subjects & topics) ───────────
if (isset($_POST['get_course_detail'])) {
    requireLogin();
    $course_id = intval($_POST['course_id'] ?? 0);
    $q = "SELECT c.*, u.name as creator_name, ap.name as approver_name
          FROM courses c LEFT JOIN users u ON c.created_by=u.id
          LEFT JOIN users ap ON c.approved_by=ap.id
          WHERE c.id=$course_id";
    $r = mysqli_query($conn, $q);
    if (!$r || mysqli_num_rows($r) == 0)
        respond(404, 'Course not found');
    $course = mysqli_fetch_assoc($r);
    // Get subjects with topics
    $sq = "SELECT s.*, (SELECT COUNT(*) FROM topics WHERE subject_id=s.id) as topic_count FROM subjects s WHERE s.course_id=$course_id ORDER BY s.created_at ASC";
    $sr = mysqli_query($conn, $sq);
    $subjects = [];
    while ($sr && $srow = mysqli_fetch_assoc($sr)) {
        $tq = "SELECT t.* FROM topics t WHERE t.subject_id={$srow['id']} ORDER BY t.created_at ASC";
        $tr = mysqli_query($conn, $tq);
        $srow['topics'] = [];
        while ($tr && $trow = mysqli_fetch_assoc($tr))
            $srow['topics'][] = $trow;
        $subjects[] = $srow;
    }
    $course['subjects'] = $subjects;
    respond(200, 'OK', $course);
}

if (isset($_POST['add_course'])) {
    requireRole(['superAdmin', 'adminZiyaa', 'ziyaaCoordinator']);
    $title = esc($_POST['title'] ?? '');
    $code = esc($_POST['course_code'] ?? '');
    $desc = esc($_POST['description'] ?? '');
    $cat = esc($_POST['category'] ?? '');
    $course_type = esc($_POST['course_type'] ?? 'theory');
    $semester = esc($_POST['semester'] ?? '');
    $regulation = esc($_POST['regulation'] ?? '');
    $academic_year = esc($_POST['academic_year'] ?? '');
    // Coordinators always submit as pending; admins choose status
    if (hasRole('ziyaaCoordinator')) {
        $status = 'pending';
    } else {
        $status = esc($_POST['status'] ?? 'draft');
    }
    
    // Handle thumbnail upload with security checks
    $thumb = '';
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        $thumb = uploadFile('thumbnail', $allowedMimes, $allowedExts, 5, 'uploads/thumbnails', 'thumb');
    }
    
    // Handle syllabus PDF upload with security checks
    $syllabus = '';
    if (isset($_FILES['syllabus']) && $_FILES['syllabus']['error'] === UPLOAD_ERR_OK) {
        $allowedMimes = ['application/pdf'];
        $allowedExts = ['pdf'];
        $syllabus = uploadFile('syllabus', $allowedMimes, $allowedExts, 5, 'uploads/content', 'syllabus');
    }
    
    if (!$title)
        respond(400, 'Title is required');
    $by = uid();
    
    $stmt = $conn->prepare("INSERT INTO courses (title,course_code,description,category,course_type,thumbnail,syllabus,semester,regulation,academic_year,created_by,status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param("ssssssssssss", $title, $code, $desc, $cat, $course_type, $thumb, $syllabus, $semester, $regulation, $academic_year, $by, $status);
    
    if ($stmt->execute()) {
        $newCourseId = $stmt->insert_id;
        $stmt->close();
        
        // If coordinator created, auto-assign to their college
        if (hasRole('ziyaaCoordinator') && cid()) {
            $college_id = cid();
            $stmt2 = $conn->prepare("INSERT IGNORE INTO course_colleges (course_id,college_id,assigned_by) VALUES (?,?,?)");
            $stmt2->bind_param("iii", $newCourseId, $college_id, $by);
            $stmt2->execute();
            $stmt2->close();
        }
        respond(200, 'Course created' . (hasRole('ziyaaCoordinator') ? ' and submitted for approval' : ''), ['id' => $newCourseId]);
    } else {
        $error = $stmt->error;
        $stmt->close();
        respond(500, 'Failed to create course: ' . $error);
    }
}

if (isset($_POST['update_course'])) {
    requireRole(['superAdmin', 'adminZiyaa', 'ziyaaCoordinator']);
    $id = intval($_POST['id'] ?? 0);
    // Coordinators can only edit their own pending/rejected courses
    if (hasRole('ziyaaCoordinator')) {
        $stmt_check = $conn->prepare("SELECT status, created_by FROM courses WHERE id=?");
        $stmt_check->bind_param("i", $id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        if ($row = $result_check->fetch_assoc()) {
            if ($row['created_by'] != uid()) {
                $stmt_check->close();
                respond(403, 'Cannot edit this course');
            }
            if (!in_array($row['status'], ['pending', 'rejected'])) {
                $stmt_check->close();
                respond(403, 'Can only edit pending or rejected courses');
            }
        }
        $stmt_check->close();
    }
    $title = esc($_POST['title'] ?? '');
    $code = esc($_POST['course_code'] ?? '');
    $desc = esc($_POST['description'] ?? '');
    $cat = esc($_POST['category'] ?? '');
    $course_type = esc($_POST['course_type'] ?? 'theory');
    $semester = esc($_POST['semester'] ?? '');
    $regulation = esc($_POST['regulation'] ?? '');
    $academic_year = esc($_POST['academic_year'] ?? '');
    // Coordinators resubmit as pending; admins set status
    if (hasRole('ziyaaCoordinator')) {
        $status = 'pending';
    } else {
        $status = esc($_POST['status'] ?? 'draft');
    }
    $set = "title='$title',course_code='$code',description='$desc',category='$cat',course_type='$course_type',semester='$semester',regulation='$regulation',academic_year='$academic_year',status='$status'";
    
    // Handle thumbnail upload with security checks
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        $thumb_path = uploadFile('thumbnail', $allowedMimes, $allowedExts, 5, 'uploads/thumbnails', 'thumb');
        $set .= ",thumbnail='" . esc($thumb_path) . "'";
    }
    
    // Handle syllabus PDF upload with security checks
    if (isset($_FILES['syllabus']) && $_FILES['syllabus']['error'] === UPLOAD_ERR_OK) {
        $allowedMimes = ['application/pdf'];
        $allowedExts = ['pdf'];
        $syllabus_path = uploadFile('syllabus', $allowedMimes, $allowedExts, 5, 'uploads/content', 'syllabus');
        $set .= ",syllabus='" . esc($syllabus_path) . "'";
    }
    if (mysqli_query($conn, "UPDATE courses SET $set WHERE id=$id"))
        respond(200, 'Course updated');
    respond(500, 'Update failed');
}

if (isset($_POST['delete_course'])) {
    requireRole(['superAdmin', 'adminZiyaa', 'ziyaaCoordinator']);
    $id = intval($_POST['id'] ?? 0);
    // Coordinators can only delete their own pending/rejected courses
    if (hasRole('ziyaaCoordinator')) {
        $stmt_check = $conn->prepare("SELECT status, created_by FROM courses WHERE id=?");
        $stmt_check->bind_param("i", $id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        if ($row = $result_check->fetch_assoc()) {
            if ($row['created_by'] != uid()) {
                $stmt_check->close();
                respond(403, 'Cannot delete this course');
            }
            if (!in_array($row['status'], ['pending', 'rejected'])) {
                $stmt_check->close();
                respond(403, 'Can only delete pending or rejected courses');
            }
        }
        $stmt_check->close();
    }
    
    $stmt = $conn->prepare("DELETE FROM courses WHERE id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $stmt->close();
        respond(200, 'Course deleted');
    } else {
        $error = $stmt->error;
        $stmt->close();
        respond(500, 'Delete failed: ' . $error);
    }
}

// ── Course Approval / Rejection ──────────────────────────

if (isset($_POST['approve_course'])) {
    requireRole(['superAdmin', 'adminZiyaa']);
    $id = intval($_POST['id'] ?? 0);
    $by = uid();
    
    $stmt = $conn->prepare("UPDATE courses SET status='active', approved_by=?, approved_at=NOW(), rejection_reason=NULL WHERE id=? AND status='pending'");
    $stmt->bind_param("ii", $by, $id);
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $stmt->close();
        respond(200, 'Course approved and published');
    } else {
        $stmt->close();
        respond(500, 'Approval failed');
    }
}

if (isset($_POST['reject_course'])) {
    requireRole(['superAdmin', 'adminZiyaa']);
    $id = intval($_POST['id'] ?? 0);
    $reason = esc($_POST['reason'] ?? '');
    $by = uid();
    
    $stmt = $conn->prepare("UPDATE courses SET status='rejected', approved_by=?, approved_at=NOW(), rejection_reason=? WHERE id=? AND status='pending'");
    $stmt->bind_param("isi", $by, $reason, $id);
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $stmt->close();
        respond(200, 'Course rejected');
    } else {
        $stmt->close();
        respond(500, 'Rejection failed');
    }
}

// ── Course Assignments ───────────────────────────────────

if (isset($_POST['get_course_assignments'])) {
    requireRole(['superAdmin', 'adminZiyaa']);
    $cid = intval($_POST['course_id'] ?? 0);
    $q = "SELECT cc.*, c.name as college_name, c.code as college_code, u.name as assigned_by_name
          FROM course_colleges cc
          JOIN colleges c ON cc.college_id=c.id
          LEFT JOIN users u ON cc.assigned_by=u.id
          WHERE cc.course_id=$cid ORDER BY cc.assigned_at DESC";
    $r = mysqli_query($conn, $q);
    $data = [];
    while ($r && $row = mysqli_fetch_assoc($r))
        $data[] = $row;
    respond(200, 'OK', $data);
}

if (isset($_POST['assign_course'])) {
    requireRole(['superAdmin', 'adminZiyaa']);
    $course_id = intval($_POST['course_id'] ?? 0);
    $college_id = intval($_POST['college_id'] ?? 0);
    if (!$course_id || !$college_id)
        respond(400, 'Course and college required');
    
    // Check if already assigned
    $stmt_check = $conn->prepare("SELECT id FROM course_colleges WHERE course_id=? AND college_id=?");
    $stmt_check->bind_param("ii", $course_id, $college_id);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows > 0) {
        $stmt_check->close();
        respond(409, 'Already assigned');
    }
    $stmt_check->close();
    
    $by = uid();
    $stmt = $conn->prepare("INSERT INTO course_colleges (course_id,college_id,assigned_by) VALUES (?,?,?)");
    $stmt->bind_param("iii", $course_id, $college_id, $by);
    
    if ($stmt->execute()) {
        $stmt->close();
        respond(200, 'Course assigned to college');
    } else {
        $error = $stmt->error;
        $stmt->close();
        respond(500, 'Assignment failed: ' . $error);
    }
}

if (isset($_POST['unassign_course'])) {
    requireRole(['superAdmin', 'adminZiyaa']);
    $course_id = intval($_POST['course_id'] ?? 0);
    $college_id = intval($_POST['college_id'] ?? 0);
    
    $stmt = $conn->prepare("DELETE FROM course_colleges WHERE course_id=? AND college_id=?");
    $stmt->bind_param("ii", $course_id, $college_id);
    
    if ($stmt->execute()) {
        $stmt->close();
        respond(200, 'Course unassigned');
    } else {
        $error = $stmt->error;
        $stmt->close();
        respond(500, 'Failed: ' . $error);
    }
}

// ══════════════════════════════════════════════════════════
//  SUBJECTS (superAdmin, adminZiyaa)
// ══════════════════════════════════════════════════════════

if (isset($_POST['get_subjects'])) {
    requireLogin();
    $course_id = intval($_POST['course_id'] ?? 0);
    $where = "1=1";
    if ($course_id)
        $where .= " AND course_id=$course_id";
    $q = "SELECT * FROM subjects WHERE $where ORDER BY created_at DESC";
    $r = mysqli_query($conn, $q);
    $data = [];
    while ($r && $row = mysqli_fetch_assoc($r))
        $data[] = $row;
    respond(200, 'OK', $data);
}

if (isset($_POST['add_subject'])) {
    requireRole(['superAdmin', 'adminZiyaa', 'ziyaaCoordinator']);
    $course_id = intval($_POST['course_id'] ?? 0);
    $title = esc($_POST['title'] ?? '');
    $code = esc($_POST['code'] ?? '');
    $desc = esc($_POST['description'] ?? '');
    if (!$course_id || !$title)
        respond(400, 'Course and Title are required');
    // Coordinators can only add subjects to their own courses
    if (hasRole('ziyaaCoordinator')) {
        $stmt_check = $conn->prepare("SELECT created_by FROM courses WHERE id=?");
        $stmt_check->bind_param("i", $course_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        if ($row = $result_check->fetch_assoc()) {
            if ($row['created_by'] != uid()) {
                $stmt_check->close();
                respond(403, 'Can only add subjects to your own courses');
            }
        }
        $stmt_check->close();
    }
    
    $stmt = $conn->prepare("INSERT INTO subjects (course_id, title, code, description) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $course_id, $title, $code, $desc);
    
    if ($stmt->execute()) {
        $stmt->close();
        respond(200, 'Subject added');
    } else {
        $error = $stmt->error;
        $stmt->close();
        respond(500, 'Failed to add subject: ' . $error);
    }
}

if (isset($_POST['update_subject'])) {
    requireRole(['superAdmin', 'adminZiyaa', 'ziyaaCoordinator']);
    $id = intval($_POST['id'] ?? 0);
    $title = esc($_POST['title'] ?? '');
    $code = esc($_POST['code'] ?? '');
    $desc = esc($_POST['description'] ?? '');
    $status = esc($_POST['status'] ?? 'active');

    $stmt = $conn->prepare("UPDATE subjects SET title=?, code=?, description=?, status=? WHERE id=?");
    $stmt->bind_param("ssssi", $title, $code, $desc, $status, $id);
    
    if ($stmt->execute()) {
        $stmt->close();
        respond(200, 'Subject updated');
    } else {
        $error = $stmt->error;
        $stmt->close();
        respond(500, 'Update failed: ' . $error);
    }
}

if (isset($_POST['delete_subject'])) {
    requireRole(['superAdmin', 'adminZiyaa', 'ziyaaCoordinator']);
    $id = intval($_POST['id'] ?? 0);
    
    $stmt = $conn->prepare("DELETE FROM subjects WHERE id=?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $stmt->close();
        respond(200, 'Subject deleted');
    } else {
        $error = $stmt->error;
        $stmt->close();
        respond(500, 'Delete failed: ' . $error);
    }
}

// ══════════════════════════════════════════════════════════
//  TOPICS (per subject/unit)
// ══════════════════════════════════════════════════════════

if (isset($_POST['get_topics'])) {
    requireLogin();
    $subject_id = intval($_POST['subject_id'] ?? 0);
    if (!$subject_id)
        respond(400, 'Subject ID required');
    $q = "SELECT t.*, u.name as creator_name FROM topics t LEFT JOIN users u ON t.created_by=u.id WHERE t.subject_id=$subject_id ORDER BY t.created_at ASC";
    $r = mysqli_query($conn, $q);
    $data = [];
    while ($r && $row = mysqli_fetch_assoc($r))
        $data[] = $row;
    respond(200, 'OK', $data);
}

if (isset($_POST['add_topic'])) {
    requireRole(['superAdmin', 'adminZiyaa', 'ziyaaCoordinator']);
    $subject_id = intval($_POST['subject_id'] ?? 0);
    $title = esc($_POST['title'] ?? '');
    $desc = esc($_POST['description'] ?? '');
    if (!$subject_id || !$title)
        respond(400, 'Subject and title are required');
    $by = uid();
    
    $stmt = $conn->prepare("INSERT INTO topics (subject_id, title, description, created_by) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("issi", $subject_id, $title, $desc, $by);
    
    if ($stmt->execute()) {
        $stmt->close();
        respond(200, 'Topic added');
    } else {
        $error = $stmt->error;
        $stmt->close();
        respond(500, 'Failed to add topic: ' . $error);
    }
}

if (isset($_POST['update_topic'])) {
    requireRole(['superAdmin', 'adminZiyaa', 'ziyaaCoordinator']);
    $id = intval($_POST['id'] ?? 0);
    $title = esc($_POST['title'] ?? '');
    $desc = esc($_POST['description'] ?? '');
    $status = esc($_POST['status'] ?? 'active');
    
    $stmt = $conn->prepare("UPDATE topics SET title=?, description=?, status=? WHERE id=?");
    $stmt->bind_param("sssi", $title, $desc, $status, $id);
    
    if ($stmt->execute()) {
        $stmt->close();
        respond(200, 'Topic updated');
    } else {
        $error = $stmt->error;
        $stmt->close();
        respond(500, 'Update failed: ' . $error);
    }
}

if (isset($_POST['delete_topic'])) {
    requireRole(['superAdmin', 'adminZiyaa', 'ziyaaCoordinator']);
    $id = intval($_POST['id'] ?? 0);
    
    $stmt = $conn->prepare("DELETE FROM topics WHERE id=?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $stmt->close();
        respond(200, 'Topic deleted');
    } else {
        $error = $stmt->error;
        $stmt->close();
        respond(500, 'Delete failed: ' . $error);
    }
}

// ══════════════════════════════════════════════════════════
//  CONTENT (ziyaaCoordinator primarily)
// ══════════════════════════════════════════════════════════

if (isset($_POST['get_content'])) {
    requireLogin();
    $course_id = intval($_POST['course_id'] ?? 0);
    $where = "cc.course_id=$course_id";
    // Coordinators see only their college content
    if (hasRole('ziyaaCoordinator'))
        $where .= " AND cc.college_id=" . cid();
    // Students see content from their college
    if (hasRole('ziyaaStudents'))
        $where .= " AND cc.college_id=" . cid() . " AND cc.status='active'";
    $q = "SELECT cc.*, u.name as uploader_name, cl.name as college_name, s.title as subject_title
          FROM course_content cc
          LEFT JOIN users u ON cc.uploaded_by=u.id
          LEFT JOIN colleges cl ON cc.college_id=cl.id
          LEFT JOIN subjects s ON cc.subject_id=s.id
          WHERE $where ORDER BY cc.sort_order ASC, cc.created_at ASC";
    $r = mysqli_query($conn, $q);
    $data = [];
    while ($r && $row = mysqli_fetch_assoc($r))
        $data[] = $row;
    respond(200, 'OK', $data);
}

if (isset($_POST['add_content'])) {
    requireRole(['superAdmin', 'adminZiyaa', 'ziyaaCoordinator']);
    $course_id = intval($_POST['course_id'] ?? 0);
    $title = esc($_POST['title'] ?? '');
    $desc = esc($_POST['description'] ?? '');
    $type = esc($_POST['content_type'] ?? 'text');
    $data_val = esc($_POST['content_data'] ?? '');
    $sort = intval($_POST['sort_order'] ?? 0);
    if (!$course_id || !$title)
        respond(400, 'Course and title required');

    // Handle file uploads for PDF type with security checks
    if ($type === 'pdf' && isset($_FILES['content_file']) && $_FILES['content_file']['error'] === UPLOAD_ERR_OK) {
        $allowedMimes = [
            'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain', 'application/zip', 'application/x-zip-compressed'
        ];
        $allowedExts = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt', 'zip'];
        $data_val = uploadFile('content_file', $allowedMimes, $allowedExts, 10, 'uploads/content', 'content');
    }

    $college_id = hasRole('ziyaaCoordinator') ? cid() : intval($_POST['college_id'] ?? 0);
    $subject_id = intval($_POST['subject_id'] ?? 0);
    $by = uid();
    
    $subject_id_val = $subject_id ?: null;
    $college_id_val = $college_id ?: null;
    
    $stmt = $conn->prepare("INSERT INTO course_content (course_id,subject_id,title,description,content_type,content_data,uploaded_by,college_id,sort_order) VALUES (?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param("iisssssii", $course_id, $subject_id_val, $title, $desc, $type, $data_val, $by, $college_id_val, $sort);
    
    if ($stmt->execute()) {
        $stmt->close();
        respond(200, 'Content added');
    } else {
        $error = $stmt->error;
        $stmt->close();
        respond(500, 'Failed to add content: ' . $error);
    }
}

if (isset($_POST['update_content'])) {
    requireRole(['superAdmin', 'adminZiyaa', 'ziyaaCoordinator']);
    $id = intval($_POST['id'] ?? 0);
    $title = esc($_POST['title'] ?? '');
    $desc = esc($_POST['description'] ?? '');
    $type = esc($_POST['content_type'] ?? 'text');
    $data_val = esc($_POST['content_data'] ?? '');
    $sort = intval($_POST['sort_order'] ?? 0);
    $status = esc($_POST['status'] ?? 'active');

    // Handle file uploads for PDF type with security checks
    if ($type === 'pdf' && isset($_FILES['content_file']) && $_FILES['content_file']['error'] === UPLOAD_ERR_OK) {
        $allowedMimes = [
            'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain', 'application/zip', 'application/x-zip-compressed'
        ];
        $allowedExts = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt', 'zip'];
        $data_val = uploadFile('content_file', $allowedMimes, $allowedExts, 10, 'uploads/content', 'content');
    }

    // Coordinators can only edit their own content
    $subject_id = intval($_POST['subject_id'] ?? 0);
    $subject_id_val = $subject_id ?: null;
    
    if (hasRole('ziyaaCoordinator')) {
        $uploader = uid();
        $stmt = $conn->prepare("UPDATE course_content SET title=?,description=?,content_type=?,content_data=?,sort_order=?,status=?,subject_id=? WHERE id=? AND uploaded_by=?");
        $stmt->bind_param("ssssisiii", $title, $desc, $type, $data_val, $sort, $status, $subject_id_val, $id, $uploader);
    } else {
        $stmt = $conn->prepare("UPDATE course_content SET title=?,description=?,content_type=?,content_data=?,sort_order=?,status=?,subject_id=? WHERE id=?");
        $stmt->bind_param("ssssisii", $title, $desc, $type, $data_val, $sort, $status, $subject_id_val, $id);
    }
    
    if ($stmt->execute()) {
        $stmt->close();
        respond(200, 'Content updated');
    } else {
        $error = $stmt->error;
        $stmt->close();
        respond(500, 'Update failed: ' . $error);
    }
}

if (isset($_POST['delete_content'])) {
    requireRole(['superAdmin', 'adminZiyaa', 'ziyaaCoordinator']);
    $id = intval($_POST['id'] ?? 0);
    
    if (hasRole('ziyaaCoordinator')) {
        $uploader = uid();
        $stmt = $conn->prepare("DELETE FROM course_content WHERE id=? AND uploaded_by=?");
        $stmt->bind_param("ii", $id, $uploader);
    } else {
        $stmt = $conn->prepare("DELETE FROM course_content WHERE id=?");
        $stmt->bind_param("i", $id);
    }
    
    if ($stmt->execute()) {
        $stmt->close();
        respond(200, 'Content deleted');
    } else {
        $error = $stmt->error;
        $stmt->close();
        respond(500, 'Delete failed: ' . $error);
    }
}

// ══════════════════════════════════════════════════════════
//  ENROLLMENTS
// ══════════════════════════════════════════════════════════

if (isset($_POST['enroll_student'])) {
    requireRole('ziyaaStudents');
    $course_id = intval($_POST['course_id'] ?? 0);
    // Check course is assigned to student's college
    $student_college_id = cid();
    
    $stmt_check = $conn->prepare("SELECT id FROM course_colleges WHERE course_id=? AND college_id=?");
    $stmt_check->bind_param("ii", $course_id, $student_college_id);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows == 0) {
        $stmt_check->close();
        respond(403, 'Course not available for your college');
    }
    $stmt_check->close();
    
    $sid = uid();
    $stmt_check2 = $conn->prepare("SELECT id FROM enrollments WHERE student_id=? AND course_id=?");
    $stmt_check2->bind_param("ii", $sid, $course_id);
    $stmt_check2->execute();
    if ($stmt_check2->get_result()->num_rows > 0) {
        $stmt_check2->close();
        respond(409, 'Already enrolled');
    }
    $stmt_check2->close();
    
    $stmt = $conn->prepare("INSERT INTO enrollments (student_id,course_id) VALUES (?,?)");
    $stmt->bind_param("ii", $sid, $course_id);
    
    if ($stmt->execute()) {
        $stmt->close();
        respond(200, 'Enrolled successfully');
    } else {
        $error = $stmt->error;
        $stmt->close();
        respond(500, 'Enrollment failed: ' . $error);
    }
}

if (isset($_POST['unenroll_student'])) {
    requireLogin();
    $id = intval($_POST['id'] ?? 0);
    
    if (hasRole('ziyaaStudents')) {
        $sid = uid();
        $stmt = $conn->prepare("DELETE FROM enrollments WHERE id=? AND student_id=?");
        $stmt->bind_param("ii", $id, $sid);
    } else {
        $stmt = $conn->prepare("DELETE FROM enrollments WHERE id=?");
        $stmt->bind_param("i", $id);
    }
    
    if ($stmt->execute()) {
        $stmt->close();
        respond(200, 'Unenrolled');
    } else {
        $error = $stmt->error;
        $stmt->close();
        respond(500, 'Failed: ' . $error);
    }
}

if (isset($_POST['get_enrollments'])) {
    requireLogin();
    $where = "1=1";
    if (isset($_POST['course_id']) && $_POST['course_id'])
        $where .= " AND e.course_id=" . intval($_POST['course_id']);
    if (hasRole('ziyaaStudents'))
        $where .= " AND e.student_id=" . uid();
    if (hasRole('ziyaaCoordinator')) {
        $cid = cid();
        $where .= " AND e.course_id IN (SELECT course_id FROM course_colleges WHERE college_id=$cid)";
        $where .= " AND u.college_id=$cid";
    }
    $q = "SELECT e.*, u.name as student_name, u.email as student_email, c.title as course_title, cl.name as college_name
          FROM enrollments e
          JOIN users u ON e.student_id=u.id
          JOIN courses c ON e.course_id=c.id
          LEFT JOIN colleges cl ON u.college_id=cl.id
          WHERE $where ORDER BY e.enrolled_at DESC";
    $r = mysqli_query($conn, $q);
    $data = [];
    while ($r && $row = mysqli_fetch_assoc($r))
        $data[] = $row;
    respond(200, 'OK', $data);
}

if (isset($_POST['update_progress'])) {
    requireRole('ziyaaStudents');
    $id = intval($_POST['id'] ?? 0);
    $progress = intval($_POST['progress'] ?? 0);
    $sid = uid();
    $set = "progress=$progress";
    if ($progress >= 100)
        $set .= ", status='completed', completed_at=NOW()";
    if (mysqli_query($conn, "UPDATE enrollments SET $set WHERE id=$id AND student_id=$sid"))
        respond(200, 'Progress updated');
    respond(500, 'Update failed');
}

if (isset($_POST['get_my_courses'])) {
    requireRole('ziyaaStudents');
    $sid = uid();
    $q = "SELECT c.*, e.id as enrollment_id, e.progress, e.status as enroll_status, e.enrolled_at,
          (SELECT COUNT(*) FROM course_content WHERE course_id=c.id AND college_id=" . cid() . " AND status='active') as content_count
          FROM enrollments e
          JOIN courses c ON e.course_id=c.id
          WHERE e.student_id=$sid ORDER BY e.enrolled_at DESC";
    $r = mysqli_query($conn, $q);
    $data = [];
    while ($r && $row = mysqli_fetch_assoc($r))
        $data[] = $row;
    respond(200, 'OK', $data);
}

// ══════════════════════════════════════════════════════════
//  DASHBOARD STATS
// ══════════════════════════════════════════════════════════

if (isset($_POST['get_dashboard_stats'])) {
    requireLogin();
    $stats = [];

    if (hasRole('superAdmin')) {
        $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM colleges");
        $stats['colleges'] = mysqli_fetch_assoc($r)['c'];
        $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM users");
        $stats['users'] = mysqli_fetch_assoc($r)['c'];
        $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM courses");
        $stats['courses'] = mysqli_fetch_assoc($r)['c'];
        $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM enrollments");
        $stats['enrollments'] = mysqli_fetch_assoc($r)['c'];
        $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM users WHERE role='ziyaaStudents'");
        $stats['students'] = mysqli_fetch_assoc($r)['c'];
        $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM users WHERE role='ziyaaCoordinator'");
        $stats['coordinators'] = mysqli_fetch_assoc($r)['c'];
        // Approval stats
        $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM courses WHERE status='pending'");
        $stats['pending_courses'] = mysqli_fetch_assoc($r)['c'];
        $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM courses WHERE status='rejected'");
        $stats['rejected_courses'] = mysqli_fetch_assoc($r)['c'];
        // Recent users
        $r = mysqli_query($conn, "SELECT u.name,u.email,u.role,u.created_at,c.name as college_name FROM users u LEFT JOIN colleges c ON u.college_id=c.id ORDER BY u.created_at DESC LIMIT 5");
        $stats['recent_users'] = [];
        while ($r && $row = mysqli_fetch_assoc($r))
            $stats['recent_users'][] = $row;
    } else if (hasRole('adminZiyaa')) {
        $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM users WHERE role IN ('ziyaaCoordinator','ziyaaStudents')");
        $stats['users'] = mysqli_fetch_assoc($r)['c'];
        $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM courses");
        $stats['courses'] = mysqli_fetch_assoc($r)['c'];
        $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM colleges");
        $stats['colleges'] = mysqli_fetch_assoc($r)['c'];
        $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM enrollments");
        $stats['enrollments'] = mysqli_fetch_assoc($r)['c'];
        // Approval stats for admin
        $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM courses WHERE status='pending'");
        $stats['pending_courses'] = mysqli_fetch_assoc($r)['c'];
        $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM courses WHERE status='rejected'");
        $stats['rejected_courses'] = mysqli_fetch_assoc($r)['c'];
    } else if (hasRole('ziyaaCoordinator')) {
        $cid = cid();
        $uid_val = uid();
        $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM course_colleges WHERE college_id=$cid");
        $stats['courses'] = mysqli_fetch_assoc($r)['c'];
        $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM users WHERE college_id=$cid AND role='ziyaaStudents'");
        $stats['students'] = mysqli_fetch_assoc($r)['c'];
        $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM course_content WHERE college_id=$cid");
        $stats['content'] = mysqli_fetch_assoc($r)['c'];
        $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM enrollments e JOIN users u ON e.student_id=u.id WHERE u.college_id=$cid");
        $stats['enrollments'] = mysqli_fetch_assoc($r)['c'];
        // Coordinator's own course stats
        $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM courses WHERE created_by=$uid_val");
        $stats['my_courses'] = mysqli_fetch_assoc($r)['c'];
        $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM courses WHERE created_by=$uid_val AND status='pending'");
        $stats['my_pending'] = mysqli_fetch_assoc($r)['c'];
        $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM courses WHERE created_by=$uid_val AND status='active'");
        $stats['my_approved'] = mysqli_fetch_assoc($r)['c'];
        $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM courses WHERE created_by=$uid_val AND status='rejected'");
        $stats['my_rejected'] = mysqli_fetch_assoc($r)['c'];
    } else if (hasRole('ziyaaStudents')) {
        $sid = uid();
        $cid = cid();

        $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM enrollments WHERE student_id=$sid");
        $stats['enrolled'] = ($r && $row = mysqli_fetch_assoc($r)) ? intval($row['c']) : 0;

        $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM enrollments WHERE student_id=$sid AND status='completed'");
        $stats['completed'] = ($r && $row = mysqli_fetch_assoc($r)) ? intval($row['c']) : 0;

        $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM course_colleges WHERE college_id=$cid");
        $stats['available'] = ($r && $row = mysqli_fetch_assoc($r)) ? intval($row['c']) : 0;

        $r = mysqli_query($conn, "SELECT AVG(progress) as p FROM enrollments WHERE student_id=$sid");
        $avgRow = ($r) ? mysqli_fetch_assoc($r) : null;
        $stats['avg_progress'] = ($avgRow && $avgRow['p'] !== null) ? round(floatval($avgRow['p'])) : 0;

        // Profile completion
        $r = mysqli_query($conn, "SELECT * FROM users WHERE id=$sid");
        $u = ($r) ? mysqli_fetch_assoc($r) : [];
        $profileFields = ['name', 'email', 'username', 'phone', 'college_id', 'department', 'year', 'roll_number'];
        $filled = 0;
        if ($u) {
            foreach ($profileFields as $f) {
                if (isset($u[$f]) && $u[$f] !== '' && $u[$f] !== null && $u[$f] !== '0')
                    $filled++;
            }
        }
        $stats['profile_completion'] = round(($filled / count($profileFields)) * 100);
        $stats['profile_filled'] = $filled;
        $stats['profile_total'] = count($profileFields);

        // Per-course progress
        $r = mysqli_query($conn, "SELECT c.title, e.progress, e.status as enroll_status FROM enrollments e JOIN courses c ON e.course_id=c.id WHERE e.student_id=$sid ORDER BY e.enrolled_at DESC");
        $stats['course_progress'] = [];
        while ($r && $row = mysqli_fetch_assoc($r))
            $stats['course_progress'][] = $row;
    }

    respond(200, 'OK', $stats);
}

// ══════════════════════════════════════════════════════════
//  PROFILE
// ══════════════════════════════════════════════════════════

if (isset($_POST['update_profile'])) {
    requireLogin();
    $name = esc($_POST['name'] ?? '');
    $phone = esc($_POST['phone'] ?? '');
    $set = "name='$name',phone='$phone'";
    if (isset($_POST['password']) && $_POST['password'] !== '') {
        $set .= ",password='" . esc($_POST['password']) . "'";
    }
    if (mysqli_query($conn, "UPDATE users SET $set WHERE id=" . uid())) {
        $_SESSION['user_name'] = $name;
        respond(200, 'Profile updated');
    }
    respond(500, 'Update failed');
}

if (isset($_POST['get_profile'])) {
    requireLogin();
    $r = mysqli_query($conn, "SELECT u.*, c.name as college_name FROM users u LEFT JOIN colleges c ON u.college_id=c.id WHERE u.id=" . uid());
    if ($r && $row = mysqli_fetch_assoc($r)) {
        unset($row['password']);
        respond(200, 'OK', $row);
    }
    respond(404, 'User not found');
}

// ══════════════════════════════════════════════════════════
//  COORDINATOR: Course Students
// ══════════════════════════════════════════════════════════

if (isset($_POST['get_course_students'])) {
    requireRole(['superAdmin', 'adminZiyaa', 'ziyaaCoordinator']);
    $course_id = intval($_POST['course_id'] ?? 0);
    $where = "e.course_id=$course_id";
    if (hasRole('ziyaaCoordinator'))
        $where .= " AND u.college_id=" . cid();
    $q = "SELECT e.*, u.name as student_name, u.email as student_email, u.phone, cl.name as college_name
          FROM enrollments e
          JOIN users u ON e.student_id=u.id
          LEFT JOIN colleges cl ON u.college_id=cl.id
          WHERE $where ORDER BY u.name";
    $r = mysqli_query($conn, $q);
    $data = [];
    while ($r && $row = mysqli_fetch_assoc($r))
        $data[] = $row;
    respond(200, 'OK', $data);
}

ob_end_clean();
echo json_encode(['status' => 404, 'message' => 'Invalid request']);
?>