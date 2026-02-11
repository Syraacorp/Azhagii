<?php
require_once 'config/db.php';

// Create Admin User (Plain Text Password)
$admin_pass = 'admin123';
$sql_admin = "INSERT INTO users (username, email, password, role) 
              VALUES ('Admin User', 'admin@example.com', '$admin_pass', 'admin')
              ON DUPLICATE KEY UPDATE role='admin', password='$admin_pass'";

if ($conn->query($sql_admin)) {
    echo "Admin user created (Email: admin@example.com, Pass: admin123)<br>";
} else {
    echo "Error creating admin: " . $conn->error . "<br>";
}

// Create Sample Events
$events = [
    [
        'title' => 'Web Development Bootcamp',
        'desc' => 'Learn full-stack web development with PHP and MySQL.',
        'date' => date('Y-m-d H:i:s', strtotime('+1 week')),
        'loc' => 'Online (Zoom)',
        'max' => 50
    ],
    [
        'title' => 'AI & Machine Learning Summit',
        'desc' => 'Explore the future of AI with industry experts.',
        'date' => date('Y-m-d H:i:s', strtotime('+2 weeks')),
        'loc' => 'Convention Center, New York',
        'max' => 200
    ],
    [
        'title' => 'Digital Marketing Workshop',
        'desc' => 'Master SEO, SEM, and Social Media Marketing strategies.',
        'date' => date('Y-m-d H:i:s', strtotime('+3 days')),
        'loc' => 'Tech Hub, San Francisco',
        'max' => 30
    ]
];

foreach ($events as $event) {
    $t = $conn->real_escape_string($event['title']);
    $d = $conn->real_escape_string($event['desc']);
    $dt = $event['date'];
    $l = $conn->real_escape_string($event['loc']);
    $m = $event['max'];

    // Check if exists to avoid duplicates on refresh
    $check = $conn->query("SELECT id FROM events WHERE title = '$t'");
    if ($check->num_rows == 0) {
        $sql = "INSERT INTO events (title, description, event_date, location, max_participants) 
                VALUES ('$t', '$d', '$dt', '$l', $m)";
        if ($conn->query($sql)) {
            echo "Created event: $t<br>";
        }
    }
}

echo "<hr>";
echo "<a href='" . BASE_URL . "/login.php'>Go to Login</a>";
?>