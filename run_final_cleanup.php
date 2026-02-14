<?php
require_once 'db.php';

$key = $argv[1] ?? '';
if ($key !== '789abc') die("ERROR: Invalid key\n");

echo "=== FINAL CLEANUP ===\n\n";

$sql = file_get_contents(__DIR__ . '/final_cleanup.sql');
$statements = explode(';', $sql);

$success = 0;
$failed = 0;

foreach ($statements as $stmt) {
    $stmt = trim($stmt);
    if (empty($stmt) || strpos($stmt, '--') === 0) continue;
    
    if ($conn->query($stmt)) {
        $success++;
        echo "✓";
    } else {
        $failed++;
        echo "\n✗ Error: " . $conn->error . "\n";
    }
}

echo "\n\nSuccess: $success, Failed: $failed\n";

if ($failed === 0) {
    echo "\n✓ All columns now camelCase!\n\n";
    
    echo "=== FINAL VERIFICATION ===\n";
    
    // Check a few key tables
    foreach (['users', 'courses', 'coursecolleges', 'coursecontent'] as $table) {
        echo "\n$table:\n";
        $result = $conn->query("DESCRIBE `$table`");
        while ($row = $result->fetch_assoc()) {
            echo "  - " . $row['Field'] . "\n";
        }
    }
}

$conn->close();
