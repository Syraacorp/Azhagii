<?php
/**
 * Database Migration: Ziyaa to Azhagii
 * 
 * This script updates all role names in the database from Ziyaa to Azhagii
 * Run this script once via browser or command line to migrate the database
 * 
 * URL: http://localhost/Ziya/migrate_db.php
 * CLI: php migrate_db.php
 */

// Prevent running this script accidentally
$MIGRATION_KEY = 'azhagii_migration_2026'; // Change this to enable migration
$provided_key = $_GET['key'] ?? $_SERVER['argv'][1] ?? '';

if ($provided_key !== $MIGRATION_KEY) {
    die("Migration locked. Provide the correct key as ?key=YOUR_KEY or as CLI argument.\n");
}

require_once 'db.php';

if (!$conn) {
    die("Database connection failed!\n");
}

echo "<h2>Database Migration: Ziyaa → Azhagii</h2>";
echo "<pre>\n";

// Start transaction for safety
$conn->begin_transaction();

try {
    echo "Starting migration...\n\n";
    
    // Get counts before migration
    echo "=== BEFORE MIGRATION ===\n";
    $result = $conn->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
    while ($row = $result->fetch_assoc()) {
        echo sprintf("%-25s : %d\n", $row['role'], $row['count']);
    }
    echo "\n";
    
    // Update roles
    echo "Updating user roles...\n";
    
    $updates = [
        ['old' => 'ziyaaStudents', 'new' => 'azhagiiStudents'],
        ['old' => 'ziyaaCoordinator', 'new' => 'azhagiiCoordinator'],
        ['old' => 'adminZiyaa', 'new' => 'adminAzhagii']
    ];
    
    $total_updated = 0;
    foreach ($updates as $update) {
        $stmt = $conn->prepare("UPDATE users SET role = ? WHERE role = ?");
        $stmt->bind_param("ss", $update['new'], $update['old']);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        echo sprintf("  • %s → %s : %d records updated\n", $update['old'], $update['new'], $affected);
        $total_updated += $affected;
        $stmt->close();
    }
    
    echo "\nTotal records updated: $total_updated\n\n";
    
    // Get counts after migration
    echo "=== AFTER MIGRATION ===\n";
    $result = $conn->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
    while ($row = $result->fetch_assoc()) {
        echo sprintf("%-25s : %d\n", $row['role'], $row['count']);
    }
    echo "\n";
    
    // Check for any remaining old role names
    $check = $conn->query("SELECT COUNT(*) as count FROM users WHERE role IN ('ziyaaStudents', 'ziyaaCoordinator', 'adminZiyaa')");
    $remaining = $check->fetch_assoc()['count'];
    
    if ($remaining > 0) {
        throw new Exception("Warning: $remaining records still have old role names!");
    }
    
    // Commit transaction
    $conn->commit();
    
    echo "✅ Migration completed successfully!\n";
    echo "\n=== SUMMARY ===\n";
    echo "✓ All role names updated from Ziyaa to Azhagii\n";
    echo "✓ Transaction committed\n";
    echo "✓ Database is now consistent with code\n\n";
    
    // Update sessions notice
    echo "⚠️  IMPORTANT: All users must log out and log back in for changes to take effect.\n";
    echo "   Consider clearing all active sessions by running:\n";
    echo "   DELETE FROM sessions; (if you have a sessions table)\n";
    
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    echo "\n❌ Migration failed: " . $e->getMessage() . "\n";
    echo "All changes have been rolled back.\n";
    exit(1);
}

echo "</pre>";

// Create a marker file to prevent re-running
file_put_contents('migration_completed.txt', date('Y-m-d H:i:s') . " - Ziyaa to Azhagii migration completed\n");
echo "<p><strong>Migration marker file created.</strong> Delete 'migration_completed.txt' if you need to re-run this migration.</p>";

$conn->close();
