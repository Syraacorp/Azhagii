<?php
ob_start();
error_reporting(0);
ini_set('display_errors', 0);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

include "db.php";
if (!isset($conn) || !$conn) {
    echo json_encode(['status' => 500, 'message' => 'Database connection failed']);
    exit;
}

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', 3600);
    session_start();
}

// ── Helpers ──────────────────────────────────────────────
function respond($status, $msg, $data = null) {
    $r = ['status' => $status, 'message' => $msg];
    if ($data !== null) $r['data'] = $data;
    ob_end_clean();
    echo json_encode($r);
    exit;
}
function esc($v) { global $conn; return mysqli_real_escape_string($conn, $v); }
function isLogged() { return isset($_SESSION['user_id']); }
function role() { return $_SESSION['role'] ?? ''; }
function uid() { return $_SESSION['user_id'] ?? 0; }
function cid() { return $_SESSION['college_id'] ?? 0; }
function hasRole($roles) { return in_array(role(), (array)$roles); }
function requireLogin() { if (!isLogged()) respond(401, 'Please login first'); }
function requireRole($roles) { requireLogin(); if (!hasRole($roles)) respond(403, 'Access denied'); }

// ══════════════════════════════════════════════════════════
//  AUTHENTICATION
// ══════════════════════════════════════════════════════════

if (isset($_POST['login_user'])) {
    $email = esc($_POST['email'] ?? '');
    $password = esc($_POST['password'] ?? '');
    $q = "SELECT u.*, c.name as college_name FROM users u LEFT JOIN colleges c ON u.college_id=c.id WHERE u.email='$email' AND u.password='$password' AND u.status='active'";
    $r = mysqli_query($conn, $q);
    if ($r && mysqli_num_rows($r) > 0) {
        $u = mysqli_fetch_assoc($r);
        $_SESSION['user_id'] = $u['id'];
        $_SESSION['user_name'] = $u['name'];
        $_SESSION['user_email'] = $u['email'];
        $_SESSION['role'] = $u['role'];
        $_SESSION['college_id'] = $u['college_id'];
        $_SESSION['college_name'] = $u['college_name'] ?? '';
        respond(200, 'Login successful', ['role' => $u['role'], 'name' => $u['name']]);
    }
    respond(401, 'Invalid email or password');
}

if (isset($_POST['register_student'])) {
    $name = esc($_POST['name'] ?? '');
    $email = esc($_POST['email'] ?? '');
    $password = esc($_POST['password'] ?? '');
    $college_id = intval($_POST['college_id'] ?? 0);
    $phone = esc($_POST['phone'] ?? '');
    if (!$name || !$email || !$password || !$college_id) respond(400, 'All fields are required');
    $chk = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
    if ($chk && mysqli_num_rows($chk) > 0) respond(409, 'Email already registered');
    $chk2 = mysqli_query($conn, "SELECT id FROM colleges WHERE id=$college_id AND status='active'");
    if (!$chk2 || mysqli_num_rows($chk2) == 0) respond(400, 'Invalid college');
    $q = "INSERT INTO users (name,email,password,role,college_id,phone) VALUES ('$name','$email','$password','ziyaaStudents',$college_id,'$phone')";
    if (mysqli_query($conn, $q)) respond(200, 'Registration successful! Please login.');
    respond(500, 'Registration failed');
}

if (isset($_POST['check_session'])) {
    if (isLogged()) {
        respond(200, 'Active', [
            'user_id' => uid(), 'user_name' => $_SESSION['user_name'],
            'user_email' => $_SESSION['user_email'] ?? '', 'role' => role(),
            'college_id' => cid(), 'college_name' => $_SESSION['college_name'] ?? ''
        ]);
    }
    respond(401, 'No session');
}

if (isset($_POST['logout_user'])) {
    session_destroy();
    respond(200, 'Logged out');
}

// ══════════════════════════════════════════════════════════
//  COLLEGES (superAdmin)
// ══════════════════════════════════════════════════════════

if (isset($_POST['get_colleges'])) {
    requireLogin();
    $q = "SELECT c.*, (SELECT COUNT(*) FROM users WHERE college_id=c.id) as user_count FROM colleges c ORDER BY c.name";
    $r = mysqli_query($conn, $q);
    $data = [];
    while ($r && $row = mysqli_fetch_assoc($r)) $data[] = $row;
    respond(200, 'OK', $data);
}

if (isset($_POST['get_colleges_public'])) {
    $r = mysqli_query($conn, "SELECT id, name, code, city FROM colleges WHERE status='active' ORDER BY name");
    $data = [];
    while ($r && $row = mysqli_fetch_assoc($r)) $data[] = $row;
    respond(200, 'OK', $data);
}

if (isset($_POST['add_college'])) {
    requireRole('superAdmin');
    $name = esc($_POST['name'] ?? '');
    $code = esc($_POST['code'] ?? '');
    $address = esc($_POST['address'] ?? '');
    $city = esc($_POST['city'] ?? '');
    if (!$name || !$code) respond(400, 'Name and code are required');
    $chk = mysqli_query($conn, "SELECT id FROM colleges WHERE code='$code'");
    if ($chk && mysqli_num_rows($chk) > 0) respond(409, 'College code already exists');
    $q = "INSERT INTO colleges (name,code,address,city) VALUES ('$name','$code','$address','$city')";
    if (mysqli_query($conn, $q)) respond(200, 'College added');
    respond(500, 'Failed to add college');
}

if (isset($_POST['update_college'])) {
    requireRole('superAdmin');
    $id = intval($_POST['id'] ?? 0);
    $name = esc($_POST['name'] ?? '');
    $code = esc($_POST['code'] ?? '');
    $address = esc($_POST['address'] ?? '');
    $city = esc($_POST['city'] ?? '');
    $status = esc($_POST['status'] ?? 'active');
    $chk = mysqli_query($conn, "SELECT id FROM colleges WHERE code='$code' AND id!=$id");
    if ($chk && mysqli_num_rows($chk) > 0) respond(409, 'College code already exists');
    $q = "UPDATE colleges SET name='$name',code='$code',address='$address',city='$city',status='$status' WHERE id=$id";
    if (mysqli_query($conn, $q)) respond(200, 'College updated');
    respond(500, 'Update failed');
}

if (isset($_POST['delete_college'])) {
    requireRole('superAdmin');
    $id = intval($_POST['id'] ?? 0);
    if (mysqli_query($conn, "DELETE FROM colleges WHERE id=$id")) respond(200, 'College deleted');
    respond(500, 'Delete failed');
}

// ══════════════════════════════════════════════════════════
//  USERS (superAdmin, adminZiyaa)
// ══════════════════════════════════════════════════════════

if (isset($_POST['get_users'])) {
    requireRole(['superAdmin', 'adminZiyaa']);
    $where = "1=1";
    if (isset($_POST['role_filter']) && $_POST['role_filter']) $where .= " AND u.role='" . esc($_POST['role_filter']) . "'";
    if (isset($_POST['college_filter']) && $_POST['college_filter']) $where .= " AND u.college_id=" . intval($_POST['college_filter']);
    // adminZiyaa cannot see superAdmin users
    if (role() === 'adminZiyaa') $where .= " AND u.role != 'superAdmin'";
    $q = "SELECT u.*, c.name as college_name FROM users u LEFT JOIN colleges c ON u.college_id=c.id WHERE $where ORDER BY u.created_at DESC";
    $r = mysqli_query($conn, $q);
    $data = [];
    while ($r && $row = mysqli_fetch_assoc($r)) { unset($row['password']); $data[] = $row; }
    respond(200, 'OK', $data);
}

if (isset($_POST['add_user'])) {
    requireRole(['superAdmin', 'adminZiyaa']);
    $name = esc($_POST['name'] ?? '');
    $email = esc($_POST['email'] ?? '');
    $password = esc($_POST['password'] ?? '');
    $urole = esc($_POST['role'] ?? '');
    $college_id = intval($_POST['college_id'] ?? 0);
    $phone = esc($_POST['phone'] ?? '');
    if (!$name || !$email || !$password || !$urole) respond(400, 'Required fields missing');
    // adminZiyaa cannot create superAdmin or other adminZiyaa
    if (role() === 'adminZiyaa' && in_array($urole, ['superAdmin', 'adminZiyaa'])) respond(403, 'Cannot create this role');
    $chk = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
    if ($chk && mysqli_num_rows($chk) > 0) respond(409, 'Email already exists');
    $cid_sql = $college_id ? $college_id : "NULL";
    $q = "INSERT INTO users (name,email,password,role,college_id,phone) VALUES ('$name','$email','$password','$urole',$cid_sql,'$phone')";
    if (mysqli_query($conn, $q)) respond(200, 'User added');
    respond(500, 'Failed to add user');
}

if (isset($_POST['update_user'])) {
    requireRole(['superAdmin', 'adminZiyaa']);
    $id = intval($_POST['id'] ?? 0);
    $name = esc($_POST['name'] ?? '');
    $email = esc($_POST['email'] ?? '');
    $urole = esc($_POST['role'] ?? '');
    $college_id = intval($_POST['college_id'] ?? 0);
    $phone = esc($_POST['phone'] ?? '');
    $status = esc($_POST['status'] ?? 'active');
    $cid_sql = $college_id ? $college_id : "NULL";
    $set = "name='$name',email='$email',role='$urole',college_id=$cid_sql,phone='$phone',status='$status'";
    if (isset($_POST['password']) && $_POST['password'] !== '') {
        $set .= ",password='" . esc($_POST['password']) . "'";
    }
    $chk = mysqli_query($conn, "SELECT id FROM users WHERE email='$email' AND id!=$id");
    if ($chk && mysqli_num_rows($chk) > 0) respond(409, 'Email already exists');
    if (mysqli_query($conn, "UPDATE users SET $set WHERE id=$id")) respond(200, 'User updated');
    respond(500, 'Update failed');
}

if (isset($_POST['delete_user'])) {
    requireRole(['superAdmin', 'adminZiyaa']);
    $id = intval($_POST['id'] ?? 0);
    if ($id == uid()) respond(400, 'Cannot delete yourself');
    if (mysqli_query($conn, "DELETE FROM users WHERE id=$id")) respond(200, 'User deleted');
    respond(500, 'Delete failed');
}

// ══════════════════════════════════════════════════════════
//  COURSES (superAdmin, adminZiyaa)
// ══════════════════════════════════════════════════════════

if (isset($_POST['get_courses'])) {
    requireLogin();
    $where = "1=1";
    if (hasRole(['ziyaaCoordinator'])) {
        // Coordinator sees courses assigned to their college
        $cid = cid();
        $where = "c.id IN (SELECT course_id FROM course_colleges WHERE college_id=$cid)";
    } else if (hasRole(['ziyaaStudents'])) {
        $cid = cid();
        $where = "c.status='active' AND c.id IN (SELECT course_id FROM course_colleges WHERE college_id=$cid)";
    }
    $q = "SELECT c.*, u.name as creator_name,
          (SELECT COUNT(*) FROM course_colleges WHERE course_id=c.id) as college_count,
          (SELECT COUNT(*) FROM enrollments WHERE course_id=c.id) as enrollment_count,
          (SELECT COUNT(*) FROM course_content WHERE course_id=c.id) as content_count
          FROM courses c LEFT JOIN users u ON c.created_by=u.id WHERE $where ORDER BY c.created_at DESC";
    $r = mysqli_query($conn, $q);
    $data = [];
    while ($r && $row = mysqli_fetch_assoc($r)) $data[] = $row;
    respond(200, 'OK', $data);
}

if (isset($_POST['add_course'])) {
    requireRole(['superAdmin', 'adminZiyaa']);
    $title = esc($_POST['title'] ?? '');
    $desc = esc($_POST['description'] ?? '');
    $cat = esc($_POST['category'] ?? '');
    $status = esc($_POST['status'] ?? 'draft');
    $thumb = '';
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION);
        $fname = 'thumb_' . time() . '_' . rand(1000,9999) . '.' . $ext;
        move_uploaded_file($_FILES['thumbnail']['tmp_name'], "uploads/thumbnails/$fname");
        $thumb = "uploads/thumbnails/$fname";
    }
    if (!$title) respond(400, 'Title is required');
    $by = uid();
    $q = "INSERT INTO courses (title,description,category,thumbnail,created_by,status) VALUES ('$title','$desc','$cat','$thumb',$by,'$status')";
    if (mysqli_query($conn, $q)) respond(200, 'Course created', ['id' => mysqli_insert_id($conn)]);
    respond(500, 'Failed to create course');
}

if (isset($_POST['update_course'])) {
    requireRole(['superAdmin', 'adminZiyaa']);
    $id = intval($_POST['id'] ?? 0);
    $title = esc($_POST['title'] ?? '');
    $desc = esc($_POST['description'] ?? '');
    $cat = esc($_POST['category'] ?? '');
    $status = esc($_POST['status'] ?? 'draft');
    $set = "title='$title',description='$desc',category='$cat',status='$status'";
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION);
        $fname = 'thumb_' . time() . '_' . rand(1000,9999) . '.' . $ext;
        move_uploaded_file($_FILES['thumbnail']['tmp_name'], "uploads/thumbnails/$fname");
        $set .= ",thumbnail='uploads/thumbnails/$fname'";
    }
    if (mysqli_query($conn, "UPDATE courses SET $set WHERE id=$id")) respond(200, 'Course updated');
    respond(500, 'Update failed');
}

if (isset($_POST['delete_course'])) {
    requireRole(['superAdmin', 'adminZiyaa']);
    $id = intval($_POST['id'] ?? 0);
    if (mysqli_query($conn, "DELETE FROM courses WHERE id=$id")) respond(200, 'Course deleted');
    respond(500, 'Delete failed');
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
    while ($r && $row = mysqli_fetch_assoc($r)) $data[] = $row;
    respond(200, 'OK', $data);
}

if (isset($_POST['assign_course'])) {
    requireRole(['superAdmin', 'adminZiyaa']);
    $course_id = intval($_POST['course_id'] ?? 0);
    $college_id = intval($_POST['college_id'] ?? 0);
    if (!$course_id || !$college_id) respond(400, 'Course and college required');
    $chk = mysqli_query($conn, "SELECT id FROM course_colleges WHERE course_id=$course_id AND college_id=$college_id");
    if ($chk && mysqli_num_rows($chk) > 0) respond(409, 'Already assigned');
    $by = uid();
    $q = "INSERT INTO course_colleges (course_id,college_id,assigned_by) VALUES ($course_id,$college_id,$by)";
    if (mysqli_query($conn, $q)) respond(200, 'Course assigned to college');
    respond(500, 'Assignment failed');
}

if (isset($_POST['unassign_course'])) {
    requireRole(['superAdmin', 'adminZiyaa']);
    $course_id = intval($_POST['course_id'] ?? 0);
    $college_id = intval($_POST['college_id'] ?? 0);
    if (mysqli_query($conn, "DELETE FROM course_colleges WHERE course_id=$course_id AND college_id=$college_id"))
        respond(200, 'Course unassigned');
    respond(500, 'Failed');
}

// ══════════════════════════════════════════════════════════
//  CONTENT (ziyaaCoordinator primarily)
// ══════════════════════════════════════════════════════════

if (isset($_POST['get_content'])) {
    requireLogin();
    $course_id = intval($_POST['course_id'] ?? 0);
    $where = "cc.course_id=$course_id";
    // Coordinators see only their college content
    if (hasRole('ziyaaCoordinator')) $where .= " AND cc.college_id=" . cid();
    // Students see content from their college
    if (hasRole('ziyaaStudents')) $where .= " AND cc.college_id=" . cid() . " AND cc.status='active'";
    $q = "SELECT cc.*, u.name as uploader_name, cl.name as college_name
          FROM course_content cc
          LEFT JOIN users u ON cc.uploaded_by=u.id
          LEFT JOIN colleges cl ON cc.college_id=cl.id
          WHERE $where ORDER BY cc.sort_order ASC, cc.created_at ASC";
    $r = mysqli_query($conn, $q);
    $data = [];
    while ($r && $row = mysqli_fetch_assoc($r)) $data[] = $row;
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
    if (!$course_id || !$title) respond(400, 'Course and title required');

    // Handle file uploads for PDF type
    if ($type === 'pdf' && isset($_FILES['content_file']) && $_FILES['content_file']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['content_file']['name'], PATHINFO_EXTENSION);
        $fname = 'content_' . time() . '_' . rand(1000,9999) . '.' . $ext;
        move_uploaded_file($_FILES['content_file']['tmp_name'], "uploads/content/$fname");
        $data_val = "uploads/content/$fname";
    }

    $college_id = hasRole('ziyaaCoordinator') ? cid() : intval($_POST['college_id'] ?? 0);
    $by = uid();
    $q = "INSERT INTO course_content (course_id,title,description,content_type,content_data,uploaded_by,college_id,sort_order)
          VALUES ($course_id,'$title','$desc','$type','$data_val',$by," . ($college_id ?: "NULL") . ",$sort)";
    if (mysqli_query($conn, $q)) respond(200, 'Content added');
    respond(500, 'Failed to add content');
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

    if ($type === 'pdf' && isset($_FILES['content_file']) && $_FILES['content_file']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['content_file']['name'], PATHINFO_EXTENSION);
        $fname = 'content_' . time() . '_' . rand(1000,9999) . '.' . $ext;
        move_uploaded_file($_FILES['content_file']['tmp_name'], "uploads/content/$fname");
        $data_val = "uploads/content/$fname";
    }

    // Coordinators can only edit their own content
    $extra = hasRole('ziyaaCoordinator') ? " AND uploaded_by=" . uid() : "";
    $q = "UPDATE course_content SET title='$title',description='$desc',content_type='$type',content_data='$data_val',sort_order=$sort,status='$status' WHERE id=$id $extra";
    if (mysqli_query($conn, $q)) respond(200, 'Content updated');
    respond(500, 'Update failed');
}

if (isset($_POST['delete_content'])) {
    requireRole(['superAdmin', 'adminZiyaa', 'ziyaaCoordinator']);
    $id = intval($_POST['id'] ?? 0);
    $extra = hasRole('ziyaaCoordinator') ? " AND uploaded_by=" . uid() : "";
    if (mysqli_query($conn, "DELETE FROM course_content WHERE id=$id $extra")) respond(200, 'Content deleted');
    respond(500, 'Delete failed');
}

// ══════════════════════════════════════════════════════════
//  ENROLLMENTS
// ══════════════════════════════════════════════════════════

if (isset($_POST['enroll_student'])) {
    requireRole('ziyaaStudents');
    $course_id = intval($_POST['course_id'] ?? 0);
    // Check course is assigned to student's college
    $cid = cid();
    $chk = mysqli_query($conn, "SELECT id FROM course_colleges WHERE course_id=$course_id AND college_id=$cid");
    if (!$chk || mysqli_num_rows($chk) == 0) respond(403, 'Course not available for your college');
    $sid = uid();
    $chk2 = mysqli_query($conn, "SELECT id FROM enrollments WHERE student_id=$sid AND course_id=$course_id");
    if ($chk2 && mysqli_num_rows($chk2) > 0) respond(409, 'Already enrolled');
    $q = "INSERT INTO enrollments (student_id,course_id) VALUES ($sid,$course_id)";
    if (mysqli_query($conn, $q)) respond(200, 'Enrolled successfully');
    respond(500, 'Enrollment failed');
}

if (isset($_POST['unenroll_student'])) {
    requireLogin();
    $id = intval($_POST['id'] ?? 0);
    $extra = hasRole('ziyaaStudents') ? " AND student_id=" . uid() : "";
    if (mysqli_query($conn, "DELETE FROM enrollments WHERE id=$id $extra")) respond(200, 'Unenrolled');
    respond(500, 'Failed');
}

if (isset($_POST['get_enrollments'])) {
    requireLogin();
    $where = "1=1";
    if (isset($_POST['course_id']) && $_POST['course_id']) $where .= " AND e.course_id=" . intval($_POST['course_id']);
    if (hasRole('ziyaaStudents')) $where .= " AND e.student_id=" . uid();
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
    while ($r && $row = mysqli_fetch_assoc($r)) $data[] = $row;
    respond(200, 'OK', $data);
}

if (isset($_POST['update_progress'])) {
    requireRole('ziyaaStudents');
    $id = intval($_POST['id'] ?? 0);
    $progress = intval($_POST['progress'] ?? 0);
    $sid = uid();
    $set = "progress=$progress";
    if ($progress >= 100) $set .= ", status='completed', completed_at=NOW()";
    if (mysqli_query($conn, "UPDATE enrollments SET $set WHERE id=$id AND student_id=$sid")) respond(200, 'Progress updated');
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
    while ($r && $row = mysqli_fetch_assoc($r)) $data[] = $row;
    respond(200, 'OK', $data);
}

// ══════════════════════════════════════════════════════════
//  DASHBOARD STATS
// ══════════════════════════════════════════════════════════

if (isset($_POST['get_dashboard_stats'])) {
    requireLogin();
    $stats = [];

    if (hasRole('superAdmin')) {
        $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM colleges"); $stats['colleges'] = mysqli_fetch_assoc($r)['c'];
        $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM users"); $stats['users'] = mysqli_fetch_assoc($r)['c'];
        $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM courses"); $stats['courses'] = mysqli_fetch_assoc($r)['c'];
        $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM enrollments"); $stats['enrollments'] = mysqli_fetch_assoc($r)['c'];
        $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM users WHERE role='ziyaaStudents'"); $stats['students'] = mysqli_fetch_assoc($r)['c'];
        $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM users WHERE role='ziyaaCoordinator'"); $stats['coordinators'] = mysqli_fetch_assoc($r)['c'];
        // Recent users
        $r = mysqli_query($conn, "SELECT u.name,u.email,u.role,u.created_at,c.name as college_name FROM users u LEFT JOIN colleges c ON u.college_id=c.id ORDER BY u.created_at DESC LIMIT 5");
        $stats['recent_users'] = [];
        while ($r && $row = mysqli_fetch_assoc($r)) $stats['recent_users'][] = $row;
    }
    else if (hasRole('adminZiyaa')) {
        $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM users WHERE role IN ('ziyaaCoordinator','ziyaaStudents')"); $stats['users'] = mysqli_fetch_assoc($r)['c'];
        $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM courses"); $stats['courses'] = mysqli_fetch_assoc($r)['c'];
        $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM colleges"); $stats['colleges'] = mysqli_fetch_assoc($r)['c'];
        $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM enrollments"); $stats['enrollments'] = mysqli_fetch_assoc($r)['c'];
    }
    else if (hasRole('ziyaaCoordinator')) {
        $cid = cid();
        $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM course_colleges WHERE college_id=$cid"); $stats['courses'] = mysqli_fetch_assoc($r)['c'];
        $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM users WHERE college_id=$cid AND role='ziyaaStudents'"); $stats['students'] = mysqli_fetch_assoc($r)['c'];
        $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM course_content WHERE college_id=$cid"); $stats['content'] = mysqli_fetch_assoc($r)['c'];
        $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM enrollments e JOIN users u ON e.student_id=u.id WHERE u.college_id=$cid"); $stats['enrollments'] = mysqli_fetch_assoc($r)['c'];
    }
    else if (hasRole('ziyaaStudents')) {
        $sid = uid(); $cid = cid();
        $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM enrollments WHERE student_id=$sid"); $stats['enrolled'] = mysqli_fetch_assoc($r)['c'];
        $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM enrollments WHERE student_id=$sid AND status='completed'"); $stats['completed'] = mysqli_fetch_assoc($r)['c'];
        $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM course_colleges WHERE college_id=$cid"); $stats['available'] = mysqli_fetch_assoc($r)['c'];
        $r = mysqli_query($conn, "SELECT AVG(progress) as p FROM enrollments WHERE student_id=$sid"); $stats['avg_progress'] = round(mysqli_fetch_assoc($r)['p'] ?? 0);
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
    if (hasRole('ziyaaCoordinator')) $where .= " AND u.college_id=" . cid();
    $q = "SELECT e.*, u.name as student_name, u.email as student_email, u.phone, cl.name as college_name
          FROM enrollments e
          JOIN users u ON e.student_id=u.id
          LEFT JOIN colleges cl ON u.college_id=cl.id
          WHERE $where ORDER BY u.name";
    $r = mysqli_query($conn, $q);
    $data = [];
    while ($r && $row = mysqli_fetch_assoc($r)) $data[] = $row;
    respond(200, 'OK', $data);
}

ob_end_clean();
echo json_encode(['status' => 404, 'message' => 'Invalid request']);
?>
