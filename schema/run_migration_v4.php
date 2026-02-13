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

$missing = [];
if (!in_array('profile_photo', $cols))
    $missing[] = "ADD COLUMN profile_photo VARCHAR(500) DEFAULT NULL";
if (!in_array('github_url', $cols))
    $missing[] = "ADD COLUMN github_url VARCHAR(255) DEFAULT NULL";
if (!in_array('linkedin_url', $cols))
    $missing[] = "ADD COLUMN linkedin_url VARCHAR(255) DEFAULT NULL";
if (!in_array('hackerrank_url', $cols))
    $missing[] = "ADD COLUMN hackerrank_url VARCHAR(255) DEFAULT NULL";
if (!in_array('leetcode_url', $cols))
    $missing[] = "ADD COLUMN leetcode_url VARCHAR(255) DEFAULT NULL";
if (!in_array('bio', $cols))
    $missing[] = "ADD COLUMN bio TEXT DEFAULT NULL";

if (empty($missing)) {
    echo "All profile columns exist.\n";
} else {
    foreach ($missing as $sql) {
        $fullSql = "ALTER TABLE users $sql";
        if (mysqli_query($conn, $fullSql))
            echo "Executed: $sql\n";
        else
            echo "Error: " . mysqli_error($conn) . "\n";
    }
}
mysqli_close($conn);
?>