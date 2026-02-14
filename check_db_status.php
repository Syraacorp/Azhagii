<?php
/**
 * Database Status Checker
 * Checks the current state of role names in the database
 * 
 * URL: http://localhost/Ziya/check_db_status.php
 * CLI: php check_db_status.php
 */

require_once 'db.php';

if (!$conn) {
    die("Database connection failed!\n");
}

echo "<h2>Database Status Check</h2>";
echo "<pre>\n";

echo "Database: " . $conn->get_server_info() . "\n";
echo "Connected to: " . ($dbname ?? 'unknown') . "\n\n";

// Check user roles
echo "=== USER ROLES DISTRIBUTION ===\n";
$result = $conn->query("SELECT role, COUNT(*) as count FROM users GROUP BY role ORDER BY count DESC");

if ($result && $result->num_rows > 0) {
    echo sprintf("%-25s | Count\n", "Role");
    echo str_repeat("-", 40) . "\n";
    
    $has_old_roles = false;
    while ($row = $result->fetch_assoc()) {
        echo sprintf("%-25s | %d\n", $row['role'], $row['count']);
        
        // Check for old role names
        if (in_array($row['role'], ['ziyaaStudents', 'ziyaaCoordinator', 'adminZiyaa'])) {
            $has_old_roles = true;
        }
    }
    
    echo "\n";
    
    // Migration status
    echo "=== MIGRATION STATUS ===\n";
    if ($has_old_roles) {
        echo "❌ OLD ROLE NAMES DETECTED!\n";
        echo "   Migration is needed. Run migrate_db.php\n\n";
        
        // Count old vs new
        $old_count = $conn->query("SELECT COUNT(*) as c FROM users WHERE role IN ('ziyaaStudents', 'ziyaaCoordinator', 'adminZiyaa')")->fetch_assoc()['c'];
        $new_count = $conn->query("SELECT COUNT(*) as c FROM users WHERE role IN ('azhagiiStudents', 'azhagiiCoordinator', 'adminAzhagii')")->fetch_assoc()['c'];
        
        echo "   Old role format: $old_count users\n";
        echo "   New role format: $new_count users\n";
    } else {
        echo "✅ ALL ROLES UPDATED TO AZHAGII FORMAT\n";
        echo "   Database is consistent with the codebase.\n";
    }
} else {
    echo "No users found in database.\n";
}

echo "\n";

// Check if migration marker exists
if (file_exists('migration_completed.txt')) {
    echo "=== MIGRATION HISTORY ===\n";
    echo file_get_contents('migration_completed.txt');
    echo "\n";
}

// Table statistics
echo "=== DATABASE STATISTICS ===\n";
$tables = ['users', 'colleges', 'courses', 'subjects', 'topics', 'coursecontent', 'enrollments'];
foreach ($tables as $table) {
    $result = $conn->query("SELECT COUNT(*) as count FROM $table");
    if ($result) {
        $count = $result->fetch_assoc()['count'];
        echo sprintf("%-20s : %d records\n", $table, $count);
    }
}

echo "</pre>";

$conn->close();
