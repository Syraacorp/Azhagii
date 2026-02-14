<?php
$conn = mysqli_connect('localhost', 'root', '', 'ziya');
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error() . "\n");
}

$cols = [];
$res = mysqli_query($conn, "SHOW COLUMNS FROM users");
while ($row = mysqli_fetch_assoc($res)) {
    $cols[] = $row['Field'];
}

// 1. Add dob
if (!in_array('dob', $cols)) {
    if (mysqli_query($conn, "ALTER TABLE users ADD COLUMN dob DATE DEFAULT NULL AFTER phone")) {
        echo "Added dob column.\n";
    } else {
        echo "Error adding dob: " . mysqli_error($conn) . "\n";
    }
} else {
    echo "dob column already exists.\n";
}

// 2. Add gender
if (!in_array('gender', $cols)) {
    if (mysqli_query($conn, "ALTER TABLE users ADD COLUMN gender ENUM('Male','Female','Other') DEFAULT NULL AFTER dob")) {
        echo "Added gender column.\n";
    } else {
        echo "Error adding gender: " . mysqli_error($conn) . "\n";
    }
} else {
    echo "gender column already exists.\n";
}

// 3. Add address
if (!in_array('address', $cols)) {
    if (mysqli_query($conn, "ALTER TABLE users ADD COLUMN address TEXT DEFAULT NULL AFTER gender")) {
        echo "Added address column.\n";
    } else {
        echo "Error adding address: " . mysqli_error($conn) . "\n";
    }
} else {
    echo "address column already exists.\n";
}

mysqli_close($conn);
?>