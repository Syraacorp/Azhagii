<?php
require_once 'db.php';

$key = $argv[1] ?? '';
if ($key !== '789abc') die("ERROR: Invalid key\n");

echo "=== MANUAL CLEANUP - ONE BY ONE ===\n\n";

$statements = [
    ["colleges", "ALTER TABLE colleges CHANGE COLUMN createdAt createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP"],
    ["coursecolleges", "ALTER TABLE coursecolleges CHANGE COLUMN courseId courseId INT(11) NOT NULL"],
    ["coursecontent", "ALTER TABLE coursecontent CHANGE COLUMN courseId courseId INT(11) NOT NULL"],
    ["courses", "ALTER TABLE courses CHANGE COLUMN courseCode courseCode VARCHAR(50) UNIQUE"],
    ["enrollments", "ALTER TABLE enrollments CHANGE COLUMN studentId studentId INT(11) NOT NULL"],
    ["events", "ALTER TABLE events CHANGE COLUMN eventDate eventDate DATE NOT NULL"],
    ["profilerequests", "ALTER TABLE profilerequests CHANGE COLUMN userId userId INT(11) NOT NULL"],
    ["subjects", "ALTER TABLE subjects CHANGE COLUMN courseId courseId INT(11) NOT NULL"],
    ["topics", "ALTER TABLE topics CHANGE COLUMN subjectId subjectId INT(11) NOT NULL"],
    ["users", "ALTER TABLE users CHANGE COLUMN collegeId collegeId INT(11)"],
];

foreach ($statements as $item) {
    list($table, $sql) = $item;
    echo "[$table] ";
    
    if ($conn->query($sql)) {
        echo "✓\n";
    } else {
        echo "✗ " . $conn->error . "\n";
    }
}

echo "\n=== VERIFICATION ===\n\n";

foreach (['users', 'courses', 'coursecolleges', 'coursecontent', 'colleges'] as $table) {
    echo "$table columns: ";
    $result = $conn->query("DESCRIBE `$table`");
    $cols = [];
    while ($row = $result->fetch_assoc()) {
        $cols[] = $row['Field'];
    }
    echo implode(', ', $cols) . "\n";
}

$conn->close();
