<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    die("Unauthorized");
}
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reg_id = intval($_POST['id']);
    // Ensure the user owns this registration
    $user_id = $_SESSION['user_id'];

    $sql = "UPDATE registrations SET status = 'cancelled' WHERE id = $reg_id AND user_id = $user_id";
    if ($conn->query($sql)) {
        echo "Success";
    } else {
        echo "Error";
    }
}
?>