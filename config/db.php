<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'event_manager');

// Define Base URL - Update this if your folder name is different
define('BASE_URL', '/Ziya');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Select database if it exists
try {
    @$conn->select_db(DB_NAME);
} catch (Exception $e) {
    // Database might not exist yet, ignoring...
}
?>