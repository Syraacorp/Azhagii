<?php
$conn = mysqli_connect('localhost', 'root', '', 'ziya');
if (!$conn) { echo "Connection failed\n"; exit(1); }

$cols = [];
$r = mysqli_query($conn, 'SHOW COLUMNS FROM users');
while ($row = mysqli_fetch_assoc($r)) $cols[] = $row['Field'];
echo "Existing columns: " . implode(', ', $cols) . "\n";

if (!in_array('username', $cols)) {
    if (mysqli_query($conn, "ALTER TABLE users ADD COLUMN username VARCHAR(100) UNIQUE NULL AFTER email"))
        echo "Added username\n";
    else echo "Error adding username: " . mysqli_error($conn) . "\n";
}
if (!in_array('department', $cols)) {
    if (mysqli_query($conn, "ALTER TABLE users ADD COLUMN department VARCHAR(50) NULL AFTER college_id"))
        echo "Added department\n";
    else echo "Error adding department: " . mysqli_error($conn) . "\n";
}
if (!in_array('year', $cols)) {
    if (mysqli_query($conn, "ALTER TABLE users ADD COLUMN `year` VARCHAR(20) NULL AFTER department"))
        echo "Added year\n";
    else echo "Error adding year: " . mysqli_error($conn) . "\n";
}
if (!in_array('roll_number', $cols)) {
    if (mysqli_query($conn, "ALTER TABLE users ADD COLUMN roll_number VARCHAR(20) UNIQUE NULL AFTER `year`"))
        echo "Added roll_number\n";
    else echo "Error adding roll_number: " . mysqli_error($conn) . "\n";
}

echo "Migration complete!\n";
mysqli_close($conn);
