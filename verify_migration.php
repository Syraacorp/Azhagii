<?php
require_once 'db.php';

echo "=== VERIFICATION OF DATABASE SCHEMA ===\n\n";

// Get all tables
$result = $conn->query("SHOW TABLES");
echo "Tables in database:\n";
$tables = [];
while ($row = $result->fetch_array()) {
    $tables[] = $row[0];
    echo "  - " . $row[0] . "\n";
}

echo "\n";

// Check each table's columns
foreach ($tables as $table) {
    echo "=== Table: $table ===\n";
    $result = $conn->query("DESCRIBE `$table`");
    while ($row = $result->fetch_assoc()) {
        echo "  " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
    echo "\n";
}

$conn->close();
