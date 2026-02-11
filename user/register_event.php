<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    die("Unauthorized");
}
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $event_id = intval($_POST['event_id']);

    // Check if already registered
    $check = $conn->query("SELECT * FROM registrations WHERE user_id = $user_id AND event_id = $event_id");

    if ($check->num_rows == 0) {
        $sql = "INSERT INTO registrations (user_id, event_id, status) VALUES ($user_id, $event_id, 'registered')";
        if ($conn->query($sql)) {
            echo "Success";
        } else {
            echo "Error";
        }
    } else {
        echo "Already Registered";
    }
}
?>