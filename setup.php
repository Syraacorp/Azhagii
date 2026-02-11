<?php
require_once 'config/db.php';

$sql = file_get_contents('database.sql');

if ($conn->multi_query($sql)) {
    echo "<p style='font-family: Inter, sans-serif; padding: 2rem;'>Database and tables created successfully! <a href='" . BASE_URL . "/seed.php'>Run Seed Data</a> | <a href='" . BASE_URL . "/'>Go Home</a></p>";
    // Clear results to allow further queries if needed
    while ($conn->next_result()) {
        ;
    }
} else {
    echo "<p style='font-family: Inter, sans-serif; padding: 2rem; color: red;'>Error creating database: " . $conn->error . "</p>";
}

$conn->close();
?>