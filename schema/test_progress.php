<?php
// Quick test of dashboard stats API
$conn = mysqli_connect('localhost', 'root', '', 'ziya');
if (!$conn) { echo "Connection failed\n"; exit(1); }

// Find a student user
$r = mysqli_query($conn, "SELECT id, name, email, username, phone, college_id, department, year, roll_number FROM users WHERE role='ziyaaStudents' LIMIT 1");
$user = $r ? mysqli_fetch_assoc($r) : null;

if (!$user) { echo "No student user found\n"; exit; }

echo "Student: " . json_encode($user, JSON_PRETTY_PRINT) . "\n\n";

// Calculate profile completion
$profileFields = ['name','email','username','phone','college_id','department','year','roll_number'];
$filled = 0;
foreach ($profileFields as $f) {
    $val = $user[$f] ?? '';
    $isFilled = !empty($val);
    echo "  $f: '$val' => " . ($isFilled ? 'FILLED' : 'EMPTY') . "\n";
    if ($isFilled) $filled++;
}
$pct = round(($filled / count($profileFields)) * 100);
echo "\nProfile completion: $filled/" . count($profileFields) . " = $pct%\n";

// Check enrollments
$sid = $user['id'];
$r = mysqli_query($conn, "SELECT c.title, e.progress, e.status FROM enrollments e JOIN courses c ON e.course_id=c.id WHERE e.student_id=$sid");
echo "\nEnrolled courses:\n";
$count = 0;
while ($r && $row = mysqli_fetch_assoc($r)) {
    echo "  - " . $row['title'] . " | Progress: " . $row['progress'] . "% | Status: " . $row['status'] . "\n";
    $count++;
}
if ($count === 0) echo "  (none)\n";

mysqli_close($conn);
