<?php
// Quick test to verify camelCase migration
require_once 'db.php';

echo "=== QUICK VERIFICATION TEST ===\n\n";

$tests = [
    "Test 1: Fetch user with camelCase" => "SELECT id, name, email, collegeId, rollNumber, createdAt FROM users LIMIT 1",
    "Test 2: Fetch course with camelCase" => "SELECT id, title, courseCode, courseType, academicYear, createdAt FROM courses LIMIT 1",
    "Test 3: Fetch enrollment with camelCase" => "SELECT id, studentId, courseId, enrolledAt FROM enrollments LIMIT 1",
    "Test 4: Fetch content with camelCase" => "SELECT id, courseId, contentType, uploadedBy, sortOrder FROM coursecontent LIMIT 1",
    "Test 5: Join test" => "SELECT u.name, c.name as collegeName FROM users u LEFT JOIN colleges c ON u.collegeId=c.id LIMIT 1"
];

$passed = 0;
$failed = 0;

foreach ($tests as $name => $sql) {
    echo "$name...\n";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo "✓ PASSED - Sample data: " . json_encode($row) . "\n\n";
        $passed++;
    } else if ($result && $result->num_rows == 0) {
        echo "✓ PASSED - Query executed (no data)\n\n";
        $passed++;
    } else {
        echo "✗ FAILED - " . $conn->error . "\n\n";
        $failed++;
    }
}

echo "=== RESULTS ===\n";
echo "Passed: $passed\n";
echo "Failed: $failed\n\n";

if ($failed == 0) {
    echo "✓ ALL TESTS PASSED - camelCase migration verified!\n";
    echo "\nThe application is ready to use with the new camelCase database schema.\n";
} else {
    echo "✗ Some tests failed - please review.\n";
}

$conn->close();
