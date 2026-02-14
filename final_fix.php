<?php
require 'db.php';

echo "Fixing remaining empty role...\n";

mysqli_query($conn, "UPDATE users SET role = 'azhagiiCoordinator' WHERE role = '' OR role IS NULL");
echo "Fixed " . mysqli_affected_rows($conn) . " user(s)\n\n";

echo "=== FINAL USER LIST ===\n";
$result = mysqli_query($conn, "SELECT id, username, name, role FROM users ORDER BY id");
while ($row = mysqli_fetch_assoc($result)) {
    echo sprintf("ID %-2d | %-15s | %-25s | %s\n", 
        $row['id'], 
        $row['username'] ?: '(none)', 
        $row['name'], 
        $row['role']
    );
}

echo "\n=== ROLE COUNTS ===\n";
$result = mysqli_query($conn, "SELECT role, COUNT(*) as count FROM users GROUP BY role");
while ($row = mysqli_fetch_assoc($result)) {
    echo "{$row['role']}: {$row['count']} users\n";
}

echo "\n✅ All users now have valid Azhagii roles!\n";
