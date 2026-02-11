<?php
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$action = $_REQUEST['action'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && $action == 'create') {
    // Sanitization and Validation
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $date = mysqli_real_escape_string($conn, $_POST['event_date']);
    $time = mysqli_real_escape_string($conn, $_POST['event_time']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $organizer = $_SESSION['username']; // Admin name as organizer
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $price = floatval($_POST['price']);
    $capacity = intval($_POST['capacity']);

    $stmt = $conn->prepare("INSERT INTO events (title, description, event_date, event_time, location, organizer, category, price, capacity) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssid", $title, $description, $date, $time, $location, $organizer, $category, $price, $capacity);

    if ($stmt->execute()) {
        header("Location: manageEvents.php?success=created");
    } else {
        header("Location: manageEvents.php?error=" . urlencode($conn->error));
    }
    $stmt->close();
} elseif ($action == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: manageEvents.php?success=deleted");
    } else {
        header("Location: manageEvents.php?error=" . urlencode($conn->error));
    }
    $stmt->close();
} elseif ($_SERVER["REQUEST_METHOD"] == "POST" && $action == 'update') {
    // Update Event
    $id = intval($_POST['id']);
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $date = mysqli_real_escape_string($conn, $_POST['event_date']);
    $time = mysqli_real_escape_string($conn, $_POST['event_time']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $price = floatval($_POST['price']);
    $capacity = intval($_POST['capacity']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    $stmt = $conn->prepare("UPDATE events SET title=?, description=?, event_date=?, event_time=?, location=?, category=?, price=?, capacity=?, status=? WHERE id=?");
    $stmt->bind_param("ssssssdiss", $title, $description, $date, $time, $location, $category, $price, $capacity, $status, $id);

    if ($stmt->execute()) {
        header("Location: manageEvents.php?success=updated");
    } else {
        header("Location: editEvent.php?id=$id&error=" . urlencode($conn->error));
    }
    $stmt->close();
} else {
    header("Location: manageEvents.php");
}
?>