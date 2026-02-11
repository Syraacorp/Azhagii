<?php
require_once 'config/db.php';

$sql = file_get_contents('database.sql');

if ($conn->multi_query($sql)) {
    echo "Database and tables created successfully!";
    // Clear results to allow further queries if needed
    while ($conn->next_result()) {
        ;
    }
} else {
    echo "Error creating database: " . $conn->error;
}

$conn->close();
?>