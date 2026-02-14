<?php
require 'db.php';

echo "=== FULL DATABASE SCHEMA ANALYSIS ===\n\n";

// Get all tables
$tables_result = mysqli_query($conn, "SHOW TABLES");
$tables = [];

while ($row = mysqli_fetch_array($tables_result)) {
    $tables[] = $row[0];
}

echo "Found " . count($tables) . " tables\n\n";

// For each table, get structure
foreach ($tables as $table) {
    echo "TABLE: $table\n";
    echo str_repeat("=", 80) . "\n";
    
    $columns = mysqli_query($conn, "DESCRIBE $table");
    echo sprintf("%-30s | %-40s | %-5s | %-10s\n", "Column", "Type", "Null", "Key");
    echo str_repeat("-", 80) . "\n";
    
    while ($col = mysqli_fetch_assoc($columns)) {
        echo sprintf("%-30s | %-40s | %-5s | %-10s\n", 
            $col['Field'], 
            $col['Type'], 
            $col['Null'], 
            $col['Key']
        );
    }
    
    // Get indexes
    $indexes = mysqli_query($conn, "SHOW INDEXES FROM $table");
    $idx_list = [];
    while ($idx = mysqli_fetch_assoc($indexes)) {
        $idx_list[] = $idx['Key_name'] . " (" . $idx['Column_name'] . ")";
    }
    if (!empty($idx_list)) {
        echo "\nIndexes: " . implode(", ", $idx_list) . "\n";
    }
    
    // Get foreign keys
    $fks = mysqli_query($conn, "
        SELECT 
            CONSTRAINT_NAME,
            COLUMN_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = '$dbname'
        AND TABLE_NAME = '$table'
        AND REFERENCED_TABLE_NAME IS NOT NULL
    ");
    
    $fk_list = [];
    while ($fk = mysqli_fetch_assoc($fks)) {
        $fk_list[] = $fk['COLUMN_NAME'] . " -> " . $fk['REFERENCED_TABLE_NAME'] . "." . $fk['REFERENCED_COLUMN_NAME'];
    }
    if (!empty($fk_list)) {
        echo "Foreign Keys: " . implode(", ", $fk_list) . "\n";
    }
    
    echo "\n";
}

// Sample data from key tables
echo "\n=== SAMPLE DATA ===\n\n";

foreach (['users', 'colleges', 'courses'] as $table) {
    echo "TABLE: $table (first 3 rows)\n";
    $result = mysqli_query($conn, "SELECT * FROM $table LIMIT 3");
    if ($result && mysqli_num_rows($result) > 0) {
        $first_row = mysqli_fetch_assoc($result);
        echo "Columns: " . implode(", ", array_keys($first_row)) . "\n\n";
    }
}

$conn->close();
