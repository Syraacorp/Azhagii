<?php
// Database configuration - Works both locally and in Docker
$servername = getenv('DB_HOST') ?: 'localhost';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';
$dbname = getenv('DB_NAME') ?: 'ziya';

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
