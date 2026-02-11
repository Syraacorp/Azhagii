<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Unauthorized");
}
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $status = $conn->real_escape_string($_POST['status']);
    // Capture feedback if provided
    $feedback = isset($_POST['feedback']) ? $conn->real_escape_string($_POST['feedback']) : null;

    // Only update feedback if it's not null (or handle optional feedback logic)
    // If status is attended, feedback is typically expected based on user request "marked the feedback"
    if ($feedback !== null) {
        $sql = "UPDATE registrations SET status = '$status', feedback = '$feedback' WHERE id = $id";
    } else {
        $sql = "UPDATE registrations SET status = '$status' WHERE id = $id";
    }

    if ($conn->query($sql)) {
        echo "Success";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>