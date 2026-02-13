<?php
$conn = mysqli_connect('localhost', 'root', '', 'ziya');
if (!$conn) {
    echo "Connection failed\n";
    exit(1);
}

$cols = [];
$r = mysqli_query($conn, 'SHOW COLUMNS FROM users');
while ($row = mysqli_fetch_assoc($r))
    $cols[] = $row['Field'];

// 1. Add is_locked to users
if (!in_array('is_locked', $cols)) {
    if (mysqli_query($conn, "ALTER TABLE users ADD COLUMN is_locked TINYINT(1) DEFAULT 0 AFTER status"))
        echo "Added is_locked column\n";
    else
        echo "Error adding is_locked: " . mysqli_error($conn) . "\n";
}

// 2. Create profile_requests table
$sql = "CREATE TABLE IF NOT EXISTS profile_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    request_reason TEXT,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL,
    resolved_by INT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (mysqli_query($conn, $sql))
    echo "Table profile_requests created/verified.\n";
else
    echo "Error creating table: " . mysqli_error($conn) . "\n";

mysqli_close($conn);
?>