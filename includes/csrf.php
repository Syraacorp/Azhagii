<?php
// CSRF Token Generation and Validation
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function getCSRFInput() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(generateCSRFToken()) . '">';
}

function requireCSRF() {
    $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
    if (!validateCSRFToken($token)) {
        header('HTTP/1.1 403 Forbidden');
        die(json_encode(['status' => 403, 'message' => 'Invalid CSRF token']));
    }
}
?>
