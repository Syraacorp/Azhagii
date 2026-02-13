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
    $email = esc($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $q = "SELECT u.*, c.name as college_name FROM users u LEFT JOIN colleges c ON u.college_id=c.id WHERE u.email='$email' AND u.status='active'";
    $r = mysqli_query($conn, $q);
    if ($r && mysqli_num_rows($r) > 0) {
        $u = mysqli_fetch_assoc($r);
        // Support both hashed and legacy plaintext passwords
        if (password_verify($password, $u['password']) || $u['password'] === $password) {
            // If legacy plaintext password matched, upgrade it to hashed
            if ($u['password'] === $password) {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                mysqli_query($conn, "UPDATE users SET password='" . esc($hashed) . "' WHERE id=" . intval($u['id']));
            }
            $_SESSION['user_id'] = $u['id'];
            $_SESSION['user_name'] = $u['name'];
            $_SESSION['user_email'] = $u['email'];
            $_SESSION['role'] = $u['role'];
            $_SESSION['college_id'] = $u['college_id'];
            $_SESSION['college_name'] = $u['college_name'] ?? '';
            respond(200, 'Login successful', ['role' => $u['role'], 'name' => $u['name']]);
        }
    }
    respond(401, 'Invalid email or password');
}

if (isset($_POST['register_student'])) {
    $name = esc($_POST['name'] ?? '');
    $email = esc($_POST['email'] ?? '');
    $raw_password = $_POST['password'] ?? '';
    $college_id = intval($_POST['college_id'] ?? 0);
    $phone = esc($_POST['phone'] ?? '');
    if (!$name || !$email || !$raw_password || !$college_id)
        respond(400, 'All fields are required');
    if (strlen($raw_password) < 6)
        respond(400, 'Password must be at least 6 characters');
    $chk = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
    if ($chk && mysqli_num_rows($chk) > 0)
        respond(409, 'Email already registered');
    $chk2 = mysqli_query($conn, "SELECT id FROM colleges WHERE id=$college_id AND status='active'");
    if (!$chk2 || mysqli_num_rows($chk2) == 0)
        respond(400, 'Invalid college');
    $hashed = esc(password_hash($raw_password, PASSWORD_DEFAULT));
    $q = "INSERT INTO users (name,email,password,role,college_id,phone) VALUES ('$name','$email','$hashed','ziyaaStudents',$college_id,'$phone')";
    if (mysqli_query($conn, $q))
        respond(200, 'Registration successful! Please login.');
    respond(500, 'Registration failed');
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
    $chk = mysqli_query($conn, "SELECT id FROM colleges WHERE code='$code'");
    if ($chk && mysqli_num_rows($chk) > 0)
        respond(409, 'College code already exists');
    $q = "INSERT INTO colleges (name,code,address,city) VALUES ('$name','$code','$address','$city')";
    if (mysqli_query($conn, $q))
        respond(200, 'College added');
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
    if ($chk && mysqli_num_rows($chk) > 0)
        respond(409, 'College code already exists');
    $q = "UPDATE colleges SET name='$name',code='$code',address='$address',city='$city',status='$status' WHERE id=$id";
    if (mysqli_query($conn, $q))
        respond(200, 'College updated');
    respond(500, 'Update failed');
}

if (isset($_POST['delete_college'])) {
    requireRole('superAdmin');
    $id = intval($_POST['id'] ?? 0);
    if (mysqli_query($conn, "DELETE FROM colleges WHERE id=$id"))
        respond(200, 'College deleted');
    respond(500, 'Delete failed');
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
    $chk = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
    if ($chk && mysqli_num_rows($chk) > 0)
        respond(409, 'Email already exists');
    $cid_sql = $college_id ? $college_id : "NULL";
    $hashed_pw = esc(password_hash($raw_password, PASSWORD_DEFAULT));
    $q = "INSERT INTO users (name,email,password,role,college_id,phone) VALUES ('$name','$email','$hashed_pw','$urole',$cid_sql,'$phone')";
    if (mysqli_query($conn, $q))
        respond(200, 'User added');
    respond(500, 'Failed to add user');
}

if (isset($_POST['update_user'])) {
    requireRole(['superAdmin', 'adminZiyaa']);
    $id = intval($_POST['id'] ?? 0);
    // adminZiyaa cannot edit superAdmin users
    if (role() === 'adminZiyaa') {
        $targetCheck = mysqli_query($conn, "SELECT role FROM users WHERE id=$id");
        if ($targetCheck && ($targetRow = mysqli_fetch_assoc($targetCheck)) && $targetRow['role'] === 'superAdmin') {
            respond(403, 'Cannot edit this user');
        }
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
    $cid_sql = $college_id ? $college_id : "NULL";
    $set = "name='$name',email='$email',role='$urole',college_id=$cid_sql,phone='$phone',status='$status'";
    if (isset($_POST['password']) && $_POST['password'] !== '') {
        $set .= ",password='" . esc(password_hash($_POST['password'], PASSWORD_DEFAULT)) . "'";
    }
    $chk = mysqli_query($conn, "SELECT id FROM users WHERE email='$email' AND id!=$id");
    if ($chk && mysqli_num_rows($chk) > 0)
        respond(409, 'Email already exists');
    if (mysqli_query($conn, "UPDATE users SET $set WHERE id=$id"))
        respond(200, 'User updated');
    respond(500, 'Update failed');
}

if (isset($_POST['delete_user'])) {
    requireRole(['superAdmin', 'adminZiyaa']);
    $id = intval($_POST['id'] ?? 0);
    if ($id == uid())
        respond(400, 'Cannot delete yourself');
    // adminZiyaa cannot delete superAdmin users
    if (role() === 'adminZiyaa') {
        $targetCheck = mysqli_query($conn, "SELECT role FROM users WHERE id=$id");
        if ($targetCheck && ($targetRow = mysqli_fetch_assoc($targetCheck)) && $targetRow['role'] === 'superAdmin') {
            respond(403, 'Cannot delete this user');
        }
    }
    if (mysqli_query($conn, "DELETE FROM users WHERE id=$id"))
        respond(200, 'User deleted');
    respond(500, 'Delete failed');
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
    if (!$r || mysqli_num_rows($r) == 0) respond(404, 'Course not found');
    $course = mysqli_fetch_assoc($r);
    // Get subjects with topics
    $sq = "SELECT s.*, (SELECT COUNT(*) FROM topics WHERE subject_id=s.id) as topic_count FROM subjects s WHERE s.course_id=$course_id ORDER BY s.created_at ASC";
    $sr = mysqli_query($conn, $sq);
    $subjects = [];
    while ($sr && $srow = mysqli_fetch_assoc($sr)) {
        $tq = "SELECT t.* FROM topics t WHERE t.subject_id={$srow['id']} ORDER BY t.created_at ASC";
        $tr = mysqli_query($conn, $tq);
        $srow['topics'] = [];
        while ($tr && $trow = mysqli_fetch_assoc($tr)) $srow['topics'][] = $trow;
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
    $thumb = '';
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION));
        $allowed_thumb = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        if (!in_array($ext, $allowed_thumb))
            respond(400, 'Invalid thumbnail format. Allowed: ' . implode(', ', $allowed_thumb));
        $fname = 'thumb_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
        move_uploaded_file($_FILES['thumbnail']['tmp_name'], "uploads/thumbnails/$fname");
        $thumb = "uploads/thumbnails/$fname";
    }
    // Handle syllabus PDF upload
    $syllabus = '';
    if (isset($_FILES['syllabus']) && $_FILES['syllabus']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['syllabus']['name'], PATHINFO_EXTENSION));
        if ($ext !== 'pdf') respond(400, 'Syllabus must be a PDF file');
        if ($_FILES['syllabus']['size'] > 2 * 1024 * 1024) respond(400, 'Syllabus file must be under 2MB');
        $fname = 'syllabus_' . time() . '_' . rand(1000, 9999) . '.pdf';
        move_uploaded_file($_FILES['syllabus']['tmp_name'], "uploads/content/$fname");
        $syllabus = "uploads/content/$fname";
    }
    if (!$title)
        respond(400, 'Title is required');
    $by = uid();
    $q = "INSERT INTO courses (title,course_code,description,category,course_type,thumbnail,syllabus,semester,regulation,academic_year,created_by,status)
          VALUES ('$title','$code','$desc','$cat','$course_type','$thumb','$syllabus','$semester','$regulation','$academic_year',$by,'$status')";
    if (mysqli_query($conn, $q)) {
        $newCourseId = mysqli_insert_id($conn);
        // If coordinator created, auto-assign to their college
        if (hasRole('ziyaaCoordinator') && cid()) {
            mysqli_query($conn, "INSERT IGNORE INTO course_colleges (course_id,college_id,assigned_by) VALUES ($newCourseId," . cid() . ",$by)");
        }
        respond(200, 'Course created' . (hasRole('ziyaaCoordinator') ? ' and submitted for approval' : ''), ['id' => $newCourseId]);
    }
    respond(500, 'Failed to create course');
}

if (isset($_POST['update_course'])) {
    requireRole(['superAdmin', 'adminZiyaa', 'ziyaaCoordinator']);
    $id = intval($_POST['id'] ?? 0);
    // Coordinators can only edit their own pending/rejected courses
    if (hasRole('ziyaaCoordinator')) {
        $chk = mysqli_query($conn, "SELECT status, created_by FROM courses WHERE id=$id");
        if ($chk && $row = mysqli_fetch_assoc($chk)) {
            if ($row['created_by'] != uid()) respond(403, 'Cannot edit this course');
            if (!in_array($row['status'], ['pending', 'rejected'])) respond(403, 'Can only edit pending or rejected courses');
        }
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
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION));
        $allowed_thumb = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        if (!in_array($ext, $allowed_thumb))
            respond(400, 'Invalid thumbnail format. Allowed: ' . implode(', ', $allowed_thumb));
        $fname = 'thumb_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
        move_uploaded_file($_FILES['thumbnail']['tmp_name'], "uploads/thumbnails/$fname");
        $set .= ",thumbnail='uploads/thumbnails/$fname'";
    }
    // Handle syllabus PDF upload
    if (isset($_FILES['syllabus']) && $_FILES['syllabus']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['syllabus']['name'], PATHINFO_EXTENSION));
        if ($ext !== 'pdf') respond(400, 'Syllabus must be a PDF file');
        if ($_FILES['syllabus']['size'] > 2 * 1024 * 1024) respond(400, 'Syllabus file must be under 2MB');
        $fname = 'syllabus_' . time() . '_' . rand(1000, 9999) . '.pdf';
        move_uploaded_file($_FILES['syllabus']['tmp_name'], "uploads/content/$fname");
        $set .= ",syllabus='uploads/content/$fname'";
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
        $chk = mysqli_query($conn, "SELECT status, created_by FROM courses WHERE id=$id");
        if ($chk && $row = mysqli_fetch_assoc($chk)) {
            if ($row['created_by'] != uid()) respond(403, 'Cannot delete this course');
            if (!in_array($row['status'], ['pending', 'rejected'])) respond(403, 'Can only delete pending or rejected courses');
        }
    }
    if (mysqli_query($conn, "DELETE FROM courses WHERE id=$id"))
        respond(200, 'Course deleted');
    respond(500, 'Delete failed');
}

// ── Course Approval / Rejection ──────────────────────────

if (isset($_POST['approve_course'])) {
    requireRole(['superAdmin', 'adminZiyaa']);
    $id = intval($_POST['id'] ?? 0);
    $by = uid();
    if (mysqli_query($conn, "UPDATE courses SET status='active', approved_by=$by, approved_at=NOW(), rejection_reason=NULL WHERE id=$id AND status='pending'"))
        respond(200, 'Course approved and published');
    respond(500, 'Approval failed');
}

if (isset($_POST['reject_course'])) {
    requireRole(['superAdmin', 'adminZiyaa']);
    $id = intval($_POST['id'] ?? 0);
    $reason = esc($_POST['reason'] ?? '');
    $by = uid();
    if (mysqli_query($conn, "UPDATE courses SET status='rejected', approved_by=$by, approved_at=NOW(), rejection_reason='$reason' WHERE id=$id AND status='pending'"))
        respond(200, 'Course rejected');
    respond(500, 'Rejection failed');
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
    $chk = mysqli_query($conn, "SELECT id FROM course_colleges WHERE course_id=$course_id AND college_id=$college_id");
    if ($chk && mysqli_num_rows($chk) > 0)
        respond(409, 'Already assigned');
    $by = uid();
    $q = "INSERT INTO course_colleges (course_id,college_id,assigned_by) VALUES ($course_id,$college_id,$by)";
    if (mysqli_query($conn, $q))
        respond(200, 'Course assigned to college');
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
        $chk = mysqli_query($conn, "SELECT created_by FROM courses WHERE id=$course_id");
        if ($chk && $row = mysqli_fetch_assoc($chk)) {
            if ($row['created_by'] != uid()) respond(403, 'Can only add subjects to your own courses');
        }
    }
    $q = "INSERT INTO subjects (course_id, title, code, description) VALUES ($course_id, '$title', '$code', '$desc')";
    if (mysqli_query($conn, $q))
        respond(200, 'Subject added');
    respond(500, 'Failed to add subject');
}

if (isset($_POST['update_subject'])) {
    requireRole(['superAdmin', 'adminZiyaa', 'ziyaaCoordinator']);
    $id = intval($_POST['id'] ?? 0);
    $title = esc($_POST['title'] ?? '');
    $code = esc($_POST['code'] ?? '');
    $desc = esc($_POST['description'] ?? '');
    $status = esc($_POST['status'] ?? 'active');

    $q = "UPDATE subjects SET title='$title', code='$code', description='$desc', status='$status' WHERE id=$id";
    if (mysqli_query($conn, $q))
        respond(200, 'Subject updated');
    respond(500, 'Update failed');
}

if (isset($_POST['delete_subject'])) {
    requireRole(['superAdmin', 'adminZiyaa', 'ziyaaCoordinator']);
    $id = intval($_POST['id'] ?? 0);
    if (mysqli_query($conn, "DELETE FROM subjects WHERE id=$id"))
        respond(200, 'Subject deleted');
    respond(500, 'Delete failed');
}

// ══════════════════════════════════════════════════════════
//  TOPICS (per subject/unit)
// ══════════════════════════════════════════════════════════

if (isset($_POST['get_topics'])) {
    requireLogin();
    $subject_id = intval($_POST['subject_id'] ?? 0);
    if (!$subject_id) respond(400, 'Subject ID required');
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
    $q = "INSERT INTO topics (subject_id, title, description, created_by) VALUES ($subject_id, '$title', '$desc', $by)";
    if (mysqli_query($conn, $q))
        respond(200, 'Topic added');
    respond(500, 'Failed to add topic');
}

if (isset($_POST['update_topic'])) {
    requireRole(['superAdmin', 'adminZiyaa', 'ziyaaCoordinator']);
    $id = intval($_POST['id'] ?? 0);
    $title = esc($_POST['title'] ?? '');
    $desc = esc($_POST['description'] ?? '');
    $status = esc($_POST['status'] ?? 'active');
    $q = "UPDATE topics SET title='$title', description='$desc', status='$status' WHERE id=$id";
    if (mysqli_query($conn, $q))
        respond(200, 'Topic updated');
    respond(500, 'Update failed');
}

if (isset($_POST['delete_topic'])) {
    requireRole(['superAdmin', 'adminZiyaa', 'ziyaaCoordinator']);
    $id = intval($_POST['id'] ?? 0);
    if (mysqli_query($conn, "DELETE FROM topics WHERE id=$id"))
        respond(200, 'Topic deleted');
    respond(500, 'Delete failed');
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

    // Handle file uploads for PDF type
    if ($type === 'pdf' && isset($_FILES['content_file']) && $_FILES['content_file']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['content_file']['name'], PATHINFO_EXTENSION));
        $allowed_content = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt', 'zip'];
        if (!in_array($ext, $allowed_content))
            respond(400, 'Invalid file format. Allowed: ' . implode(', ', $allowed_content));
        $fname = 'content_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
        move_uploaded_file($_FILES['content_file']['tmp_name'], "uploads/content/$fname");
        $data_val = "uploads/content/$fname";
    }

    $college_id = hasRole('ziyaaCoordinator') ? cid() : intval($_POST['college_id'] ?? 0);
    $subject_id = intval($_POST['subject_id'] ?? 0);
    $by = uid();
    $q = "INSERT INTO course_content (course_id,subject_id,title,description,content_type,content_data,uploaded_by,college_id,sort_order)
          VALUES ($course_id," . ($subject_id ?: "NULL") . ",'$title','$desc','$type','$data_val',$by," . ($college_id ?: "NULL") . ",$sort)";
    if (mysqli_query($conn, $q))
        respond(200, 'Content added');
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
        $ext = strtolower(pathinfo($_FILES['content_file']['name'], PATHINFO_EXTENSION));
        $allowed_content = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt', 'zip'];
        if (!in_array($ext, $allowed_content))
            respond(400, 'Invalid file format. Allowed: ' . implode(', ', $allowed_content));
        $fname = 'content_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
        move_uploaded_file($_FILES['content_file']['tmp_name'], "uploads/content/$fname");
        $data_val = "uploads/content/$fname";
    }

    // Coordinators can only edit their own content
    $extra = hasRole('ziyaaCoordinator') ? " AND uploaded_by=" . uid() : "";
    $subject_id = intval($_POST['subject_id'] ?? 0);
    $subj_sql = $subject_id ? $subject_id : "NULL";
    $q = "UPDATE course_content SET title='$title',description='$desc',content_type='$type',content_data='$data_val',sort_order=$sort,status='$status',subject_id=$subj_sql WHERE id=$id $extra";
    if (mysqli_query($conn, $q))
        respond(200, 'Content updated');
    respond(500, 'Update failed');
}

if (isset($_POST['delete_content'])) {
    requireRole(['superAdmin', 'adminZiyaa', 'ziyaaCoordinator']);
    $id = intval($_POST['id'] ?? 0);
    $extra = hasRole('ziyaaCoordinator') ? " AND uploaded_by=" . uid() : "";
    if (mysqli_query($conn, "DELETE FROM course_content WHERE id=$id $extra"))
        respond(200, 'Content deleted');
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
    if (!$chk || mysqli_num_rows($chk) == 0)
        respond(403, 'Course not available for your college');
    $sid = uid();
    $chk2 = mysqli_query($conn, "SELECT id FROM enrollments WHERE student_id=$sid AND course_id=$course_id");
    if ($chk2 && mysqli_num_rows($chk2) > 0)
        respond(409, 'Already enrolled');
    $q = "INSERT INTO enrollments (student_id,course_id) VALUES ($sid,$course_id)";
    if (mysqli_query($conn, $q))
        respond(200, 'Enrolled successfully');
    respond(500, 'Enrollment failed');
}

if (isset($_POST['unenroll_student'])) {
    requireLogin();
    $id = intval($_POST['id'] ?? 0);
    $extra = hasRole('ziyaaStudents') ? " AND student_id=" . uid() : "";
    if (mysqli_query($conn, "DELETE FROM enrollments WHERE id=$id $extra"))
        respond(200, 'Unenrolled');
    respond(500, 'Failed');
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
        $stats['enrolled'] = mysqli_fetch_assoc($r)['c'];
        $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM enrollments WHERE student_id=$sid AND status='completed'");
        $stats['completed'] = mysqli_fetch_assoc($r)['c'];
        $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM course_colleges WHERE college_id=$cid");
        $stats['available'] = mysqli_fetch_assoc($r)['c'];
        $r = mysqli_query($conn, "SELECT AVG(progress) as p FROM enrollments WHERE student_id=$sid");
        $stats['avg_progress'] = round(mysqli_fetch_assoc($r)['p'] ?? 0);
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
        $set .= ",password='" . esc(password_hash($_POST['password'], PASSWORD_DEFAULT)) . "'";
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