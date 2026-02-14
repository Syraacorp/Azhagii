<?php
/**
 * Fix Database ENUM - Update role column definition
 * This must be run BEFORE updating role values
 */

require 'db.php';

echo "=== FIXING DATABASE SCHEMA ===\n\n";

echo "Current ENUM definition:\n";
$result = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'role'");
$col = mysqli_fetch_assoc($result);
echo " Type: {$col['Type']}\n\n";

echo "Updating ENUM to include new Azhagii roles...\n";

// ALTER the table to update the ENUM
$sql = "ALTER TABLE users MODIFY COLUMN role ENUM(
    'superAdmin',
    'adminAzhagii', 
    'azhagiiCoordinator',
    'azhagiiStudents',
    'adminZiyaa',
    'ziyaaCoordinator',
    'ziyaaStudents'
) NOT NULL";

if (mysqli_query($conn, $sql)) {
    echo "✓ ENUM updated successfully!\n\n";
} else {
    die("✗ Failed to update ENUM: " . mysqli_error($conn) . "\n");
}

echo "New ENUM definition:\n";
$result = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'role'");
$col = mysqli_fetch_assoc($result);
echo " Type: {$col['Type']}\n\n";

echo "Now migrating role values...\n";

// Now we can update the roles
$updates = [
    ['old' => 'ziyaaStudents', 'new' => 'azhagiiStudents'],
    ['old' => 'ziyaaCoordinator', 'new' => 'azhagiiCoordinator'],
    ['old' => 'adminZiyaa', 'new' => 'adminAzhagii']
];

foreach ($updates as $update) {
    $sql = "UPDATE users SET role = '{$update['new']}' WHERE role = '{$update['old']}'";
    if (mysqli_query($conn, $sql)) {
        $affected = mysqli_affected_rows($conn);
        echo "  ✓ {$update['old']} → {$update['new']}: $affected records\n";
    } else {
        echo "  ✗ Failed: " . mysqli_error($conn) . "\n";
    }
}

echo "\n=== FIXING EMPTY ROLES ===\n";
echo "The previous migration cleared some roles. Restoring based on user data...\n\n";

// Fix the empty roles
$fixes = [
    "UPDATE users SET role = 'azhagiiStudents' WHERE role = '' AND department IS NOT NULL AND department != ''",
    "UPDATE users SET role = 'adminAzhagii' WHERE role = '' AND username = 'navi'",
    "UPDATE users SET role = 'azhagiiCoordinator' WHERE role = '' AND (department IS NULL OR department = '') AND username != 'navi'"
];

foreach ($fixes as $sql) {
    if (mysqli_query($conn, $sql)) {
        $affected = mysqli_affected_rows($conn);
        echo "  ✓ Fixed $affected records\n";
    }
}

echo "\n=== CLEANUP: Remove old ENUM values ===\n";
$sql = "ALTER TABLE users MODIFY COLUMN role ENUM(
    'superAdmin',
    'adminAzhagii',
    'azhagiiCoordinator',
    'azhagiiStudents'
) NOT NULL";

if (mysqli_query($conn, $sql)) {
    echo "✓ Old ENUM values removed\n\n";
}

echo "=== FINAL VERIFICATION ===\n";
$result = mysqli_query($conn, "SELECT role, COUNT(*) as count FROM users GROUP BY role");
while ($row = mysqli_fetch_assoc($result)) {
    echo "  {$row['role']}: {$row['count']} users\n";
}

echo "\n✅ DATABASE SCHEMA AND DATA FULLY UPDATED!\n";

$conn->close();
