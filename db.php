<?php
// Database configuration - Works both locally and in Docker
$servername = getenv('DB_HOST') ?: 'localhost';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';
// Database name: 'ziya' works fine, or rename to 'azhagii' (see OPTIONAL_rename_database.sql)
$dbname = getenv('DB_NAME') ?: 'ziya';

// Secure session settings
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 1 : 0);
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.use_strict_mode', 1);
    ini_set('session.gc_maxlifetime', 3600);
}

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    // Output JSON error (compatible with backend.php AJAX expectations)
    header('Content-Type: application/json');
    echo json_encode(['status' => 500, 'message' => 'Database connection failed']);
    exit;
}

// Set charset to UTF-8
$conn->set_charset("utf8mb4");
?>
