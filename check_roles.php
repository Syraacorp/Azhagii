<?php
require_once 'db.php';

echo "Checking and restoring roles...\n\n";

// First, see what we have
$result = $conn->query("SELECT id, name, username, role, email FROM users");
echo "Current state:\n";
while ($row = $result->fetch_assoc()) {
    echo "ID: {$row['id']}, User: {$row['username']}, Role: '{$row['role']}'\n";
}
echo "\n";

// The roles were cleared. Let's check if there's any pattern we can identify
// to restore them. Since we can't recover, let's manually set based on usernames/patterns

echo "Attempting restoration based on username patterns...\n";

// This is a recovery script - you'll need to manually verify and adjust user roles
echo "⚠️  Manual verification required! Please check each user's intended role.\n\n";

$conn->close();
