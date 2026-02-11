<?php
session_start();
require_once 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 401, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_id'])) {
    $event_id = intval($_POST['event_id']);
    $user_id = $_SESSION['user_id'];

    // Check if relevant event exists
    $checkEvent = $conn->query("SELECT * FROM events WHERE id = $event_id AND status = 'upcoming'");
    if ($checkEvent->num_rows == 0) {
        echo json_encode(['status' => 404, 'message' => 'Event not found or unavailable']);
        exit();
    }

    // Check existing registration
    $checkReg = $conn->query("SELECT * FROM registrations WHERE user_id = $user_id AND event_id = $event_id");
    if ($checkReg->num_rows > 0) {
        echo json_encode(['status' => 409, 'message' => 'Already registered']);
        exit();
    }

    // Register
    $stmt = $conn->prepare("INSERT INTO registrations (user_id, event_id, status) VALUES (?, ?, 'registered')");
    $stmt->bind_param("ii", $user_id, $event_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 200, 'message' => 'Successfully registered for the event!']);
    } else {
        echo json_encode(['status' => 500, 'message' => 'Registration failed: ' . $conn->error]);
    }
    $stmt->close();
}
?>