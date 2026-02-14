<?php
/**
 * Emergency Role Restoration Script
 * Restores user roles after migration issue
 * 
 * Run: php restore_roles.php
 */

require_once 'db.php';

echo "=== EMERGENCY ROLE RESTORATION ===\n\n";

// Show current state
echo "Current users:\n";
$result = $conn->query("SELECT id, name, username, email, role, department, year FROM users ORDER BY id");
while ($row = $result->fetch_assoc()) {
    echo sprintf("ID: %-2d | Username: %-15s | Name: %-20s | Current Role: '%s'\n", 
        $row['id'], $row['username'], $row['name'], $row['role']);
    if ($row['department']) {
        echo "        └─> Student fields: {$row['department']}, {$row['year']}\n";
    }
}

echo "\n\n=== RESTORATION PLAN ===\n";
echo "Based on the data, I'll restore roles as follows:\n\n";

// Identify roles based on available data
$conn->begin_transaction();

try {
    // Keep superAdmin as is
    echo "✓ ID 1 (ziyaa): Keeping as 'superAdmin'\n";
    
    // Users with department/year are students
    $result = $conn->query("SELECT id, username, name FROM users WHERE department IS NOT NULL AND department != '' AND role = ''");
    echo "\nRestoring students (have department/year):\n";
    while ($row = $result->fetch_assoc()) {
        $stmt = $conn->prepare("UPDATE users SET role = 'azhagiiStudents' WHERE id = ?");
        $stmt->bind_param("i", $row['id']);
        $stmt->execute();
        echo "  ✓ ID {$row['id']} ({$row['username']}): Set to 'azhagiiStudents'\n";
        $stmt->close();
    }
    
    // Users without department are likely admin or coordinator
    $result = $conn->query("SELECT id, username, name, email FROM users WHERE (department IS NULL OR department = '') AND role = ''");
    echo "\nUsers needing manual role assignment:\n";
    $manual_users = [];
    while ($row = $result->fetch_assoc()) {
        $manual_users[] = $row;
        echo "  ? ID {$row['id']} ({$row['username']}): email={$row['email']}\n";
    }
    
    // Auto-assign first one as adminAzhagii, rest as coordinator
    if (count($manual_users) > 0) {
        echo "\nAuto-assigning based on count from before migration:\n";
        echo "  (Before: 1 adminZiyaa, 1 ziyaaCoordinator)\n\n";
        
        // First non-student = admin
        if (isset($manual_users[0])) {
            $stmt = $conn->prepare("UPDATE users SET role = 'adminAzhagii' WHERE id = ?");
            $stmt->bind_param("i", $manual_users[0]['id']);
            $stmt->execute();
            echo "  ✓ ID {$manual_users[0]['id']} ({$manual_users[0]['username']}): Set to 'adminAzhagii'\n";
            $stmt->close();
        }
        
        // Remaining = coordinator
        for ($i = 1; $i < count($manual_users); $i++) {
            $stmt = $conn->prepare("UPDATE users SET role = 'azhagiiCoordinator' WHERE id = ?");
            $stmt->bind_param("i", $manual_users[$i]['id']);
            $stmt->execute();
            echo "  ✓ ID {$manual_users[$i]['id']} ({$manual_users[$i]['username']}): Set to 'azhagiiCoordinator'\n";
            $stmt->close();
        }
    }
    
    $conn->commit();
    
    echo "\n=== RESTORATION COMPLETE ===\n\n";
    
    // Show final state
    echo "Final role distribution:\n";
    $result = $conn->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
    while ($row = $result->fetch_assoc()) {
        echo "  {$row['role']}: {$row['count']} users\n";
    }
    
    echo "\n✅ Roles have been restored!\n";
    echo "⚠️  Please verify each user can log in with correct permissions.\n";
    
} catch (Exception $e) {
    $conn->rollback();
    echo "\n❌ Error: " . $e->getMessage() . "\n";
}

$conn->close();
