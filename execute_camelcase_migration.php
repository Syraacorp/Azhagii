<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'db.php';

// Security key to prevent accidental execution
$required_key = '789abc';
$provided_key = $argv[1] ?? $_GET['key'] ?? '';

if ($provided_key !== $required_key) {
    die("ERROR: Invalid migration key. Use: php execute_camelcase_migration.php $required_key\n");
}

echo "=== AZHAGII DATABASE CAMELCASE MIGRATION ===\n\n";

// Read the SQL migration file
$sql_file = __DIR__ . '/migrate_to_camelcase.sql';
if (!file_exists($sql_file)) {
    die("ERROR: migrate_to_camelcase.sql not found!\n");
}

$sql_content = file_get_contents($sql_file);

// Split by semicolons to execute each statement separately
$statements = array_filter(array_map('trim', explode(';', $sql_content)));

echo "Found " . count($statements) . " SQL statements to execute.\n\n";

$success_count = 0;
$error_count = 0;

foreach ($statements as $index => $statement) {
    if (empty($statement)) continue;
    
    // Skip comments
    if (strpos(trim($statement), '--') === 0) continue;
    
    echo "Executing statement " . ($index + 1) . "...\n";
    
    if ($conn->query($statement)) {
        $success_count++;
        echo "✓ Success\n";
    } else {
        $error_count++;
        echo "✗ Error: " . $conn->error . "\n";
        echo "Statement: " . substr($statement, 0, 100) . "...\n";
    }
    echo "\n";
}

echo "\n=== MIGRATION SUMMARY ===\n";
echo "Successful: $success_count\n";
echo "Failed: $error_count\n";

if ($error_count === 0) {
    echo "\n✓ All migrations completed successfully!\n";
    
    // Verify the changes
    echo "\n=== VERIFICATION ===\n";
    
    echo "\nTables in database:\n";
    $result = $conn->query("SHOW TABLES");
    while ($row = $result->fetch_array()) {
        echo "  - " . $row[0] . "\n";
    }
    
    echo "\nColumns in 'users' table:\n";
    $result = $conn->query("DESCRIBE users");
    while ($row = $result->fetch_assoc()) {
        echo "  - " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
    
    echo "\n✓ MIGRATION COMPLETE!\n";
    echo "\nNEXT STEP: Update PHP files to use new camelCase column names.\n";
} else {
    echo "\n✗ Migration completed with errors. Please review and fix.\n";
}

$conn->close();
