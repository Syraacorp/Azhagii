<?php
require_once 'config/db.php';

echo "<h2>Environment Status Check</h2>";

// Check Database Connection
if ($conn->connect_error) {
    echo "<p style='color:red'>[FAIL] Database Connection: " . $conn->connect_error . "</p>";
} else {
    echo "<p style='color:green'>[PASS] Database Connection Established.</p>";
}

// Check GD Library
if (extension_loaded('gd')) {
    echo "<p style='color:green'>[PASS] GD Library is enabled.</p>";
    $gd_info = gd_info();
    echo "<ul>";
    echo "<li>GD Version: " . $gd_info['GD Version'] . "</li>";
    echo "<li>FreeType Support: " . ($gd_info['FreeType Support'] ? 'Yes' : 'No') . "</li>";
    echo "</ul>";
} else {
    echo "<p style='color:red'>[FAIL] GD Library is NOT enabled. Certificate generation will fail.</p>";
}

// Check Write Permissions (General check)
if (is_writable(__DIR__)) {
    echo "<p style='color:green'>[PASS] Root directory is writable.</p>";
} else {
    echo "<p style='color:orange'>[WARN] Root directory might not be writable (Required for uploads if added).</p>";
}

echo "<hr>";
echo "<p>To initialize the database, please run <a href='setup.php'>setup.php</a></p>";
echo "<p><a href='/'>Go to Home</a></p>";
?>