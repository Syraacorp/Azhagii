<?php
require 'db.php';

$result = mysqli_query($conn, "SELECT id, username, role, CHAR_LENGTH(role) as len, HEX(role) as hex FROM users ORDER BY id");

echo "Detailed role inspection:\n";
echo str_repeat("=", 80) . "\n";

while ($row = mysqli_fetch_assoc($result)) {
    echo "ID: {$row['id']}\n";
    echo "  Username: {$row['username']}\n";
    echo "  Role: [{$row['role']}]\n";
    echo "  Length: {$row['len']} chars\n";
    echo "  Hex: {$row['hex']}\n";
    echo "---\n";
}

// Try direct update
echo "\nAttempting direct SQL update...\n";
$updates = [
    "UPDATE users SET role = 'azhagiiStudents' WHERE id IN (4,7,8)",
    "UPDATE users SET role = 'adminAzhagii' WHERE id = 2",
    "UPDATE users SET role = 'azhagiiCoordinator' WHERE id = 3"
];

foreach ($updates as $sql) {
    if (mysqli_query($conn, $sql)) {
        echo "✓ Executed: $sql\n";
    } else {
        echo "✗ Failed: $sql - " . mysqli_error($conn) . "\n";
    }
}

// Verify
echo "\nFinal check:\n";
$result = mysqli_query($conn, "SELECT id, username, role FROM users ORDER BY id");
while ($row = mysqli_fetch_assoc($result)) {
    echo "ID {$row['id']}: {$row['username']} = '{$row['role']}'\n";
}
