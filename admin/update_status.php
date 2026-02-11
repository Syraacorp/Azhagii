<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Unauthorized");
}
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $status = $conn->real_escape_string($_POST['status']);

    $sql = "UPDATE registrations SET status = '$status' WHERE id = $id";
    if ($conn->query($sql)) {
        echo "Success";
    } else {
        echo "Error";
    }
}
?>