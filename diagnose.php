<?php
require 'db.php';

echo "=== TABLE STRUCTURE ===\n";
$result = mysqli_query($conn, "DESCRIBE users");
while ($row = mysqli_fetch_assoc($result)) {
    print_r($row);
}

echo "\n=== TRIGGERS ===\n";
$result = mysqli_query($conn, "SHOW TRIGGERS LIKE 'users'");
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        print_r($row);
    }
} else {
    echo "No triggers found.\n";
}

echo "\n=== TESTING SINGLE UPDATE ===\n";
// Try updating one specific record with immediate verification
$test_id = 4;
echo "Before update (ID $test_id):\n";
$r = mysqli_query($conn, "SELECT id, username, role FROM users WHERE id = $test_id");
$before = mysqli_fetch_assoc($r);
print_r($before);

echo "\nExecuting: UPDATE users SET role = 'azhagiiStudents' WHERE id = $test_id\n";
mysqli_query($conn, "UPDATE users SET role = 'azhagiiStudents' WHERE id = $test_id");
echo "Affected rows: " . mysqli_affected_rows($conn) . "\n";

echo "\nAfter update (ID $test_id):\n";
$r = mysqli_query($conn, "SELECT id, username, role FROM users WHERE id = $test_id");
$after = mysqli_fetch_assoc($r);
print_r($after);

// Check if there's autocommit issue
echo "\n=== AUTOCOMMIT STATUS ===\n";
$r = mysqli_query($conn, "SELECT @@autocommit");
$ac = mysqli_fetch_row($r);
echo "Autocommit: " . ($ac[0] ? 'ON' : 'OFF') . "\n";

// Force commit
echo "\nForcing COMMIT...\n";
mysqli_commit($conn);

echo "\nAfter COMMIT (ID $test_id):\n";
$r = mysqli_query($conn, "SELECT id, username, role FROM users WHERE id = $test_id");
$final = mysqli_fetch_assoc($r);
print_r($final);
