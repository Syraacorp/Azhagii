<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'db.php';

// Security key
$required_key = '789abc';
$provided_key = $argv[1] ?? $_GET['key'] ?? '';

if ($provided_key !== $required_key) {
    die("ERROR: Invalid migration key. Use: php migrate_fixed_exec.php $required_key\n");
}

echo "=== EXECUTING FIXED CAMELCASE MIGRATION ===\n\n";

$sql_file = __DIR__ . '/migrate_fixed.sql';
if (!file_exists($sql_file)) {
    die("ERROR: migrate_fixed.sql not found!\n");
}

$sql_content = file_get_contents($sql_file);

// Split by semicolons and execute each statement
$statements = explode(';', $sql_content);

$success = 0;
$failed = 0;

foreach ($statements as $index => $stmt) {
    $stmt = trim($stmt);
    if (empty($stmt) || strpos($stmt, '--') === 0) continue;
    
    echo "Executing statement " . ($index + 1) . "...\n";
    
    if ($conn->query($stmt)) {
        $success++;
        echo "✓ Success\n\n";
    } else {
        $failed++;
        echo "✗ Error: " . $conn->error . "\n";
        echo "Statement: " . substr($stmt, 0, 100) . "...\n\n";
    }
}

echo "\n=== SUMMARY ===\n";
echo "Success: $success\n";
echo "Failed: $failed\n";

if ($failed === 0) {
    echo "\n✓ Migration completed successfully!\n\n";
    
    // Verify
    echo "=== VERIFICATION ===\n\n";
    
    $result = $conn->query("SHOW TABLES");
    echo "Tables:\n";
    while ($row = $result->fetch_array()) {
        echo "  - " . $row[0] . "\n";
    }
    
    echo "\nUsers table columns:\n";
    $result = $conn->query("DESCRIBE users");
    while ($row = $result->fetch_assoc()) {
        echo "  - " . $row['Field'] . "\n";
    }
}

$conn->close();
