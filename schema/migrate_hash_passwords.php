<?php
/**
 * Migration Script: Hash Existing Plaintext Passwords
 * 
 * This script converts all plaintext passwords in the database to secure bcrypt hashes.
 * Run this ONCE after deploying the security fixes.
 * 
 * Usage: Run from browser: http://yoursite.com/schema/migrate_hash_passwords.php
 * Or from CLI: php migrate_hash_passwords.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require_once __DIR__ . '/../db.php';

if (!$conn) {
    die("Database connection failed. Please check db.php configuration.\n");
}

echo "<h2>Password Migration Script</h2>\n";
echo "<p>Starting password migration...</p>\n";

// Get all users with non-hashed passwords (passwords not starting with $2y$)
$query = "SELECT id, username, password FROM users WHERE password NOT LIKE '$2y$%'";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($conn) . "\n");
}

$totalUsers = mysqli_num_rows($result);
$updated = 0;
$failed = 0;

echo "<p>Found {$totalUsers} users with plaintext passwords.</p>\n";
echo "<ul>\n";

while ($user = mysqli_fetch_assoc($result)) {
    $userId = $user['id'];
    $username = $user['username'];
    $plaintextPassword = $user['password'];
    
    // Hash the password
    $hashedPassword = password_hash($plaintextPassword, PASSWORD_DEFAULT);
    
    // Update the user's password
    $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
    $stmt->bind_param("si", $hashedPassword, $userId);
    
    if ($stmt->execute()) {
        echo "<li style='color:green;'>✓ Updated password for user: {$username} (ID: {$userId})</li>\n";
        $updated++;
    } else {
        echo "<li style='color:red;'>✗ Failed to update password for user: {$username} (ID: {$userId}) - " . $stmt->error . "</li>\n";
        $failed++;
    }
    
    $stmt->close();
    flush(); // Output progressively
}

echo "</ul>\n";
echo "<h3>Migration Complete!</h3>\n";
echo "<p><strong>Summary:</strong></p>\n";
echo "<ul>\n";
echo "<li>Total users found: {$totalUsers}</li>\n";
echo "<li style='color:green;'>Successfully updated: {$updated}</li>\n";
echo "<li style='color:red;'>Failed: {$failed}</li>\n";
echo "</ul>\n";

if ($updated > 0) {
    echo "<p style='color:green;font-weight:bold;'>✓ All plaintext passwords have been securely hashed!</p>\n";
    echo "<p><strong>IMPORTANT:</strong> Please delete or restrict access to this migration script for security.</p>\n";
} else {
    echo "<p>No passwords were updated. They may already be hashed.</p>\n";
}

mysqli_close($conn);
?>
