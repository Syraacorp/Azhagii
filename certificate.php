<?php
session_start();
require_once 'config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/login.php");
    exit;
}

// Get the event ID
if (!isset($_GET['id'])) {
    die("Event ID required");
}

$event_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Verify attendance
$sql = "SELECT r.*, e.title, e.event_date, u.username 
        FROM registrations r 
        JOIN events e ON r.event_id = e.id 
        JOIN users u ON r.user_id = u.id 
        WHERE r.user_id = $user_id AND r.event_id = $event_id AND r.status = 'attended'";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    die("Certificate not available or attendance not confirmed.");
}

$data = $result->fetch_assoc();

// Generate Image using GD
// Determine content to write
$name = strtoupper($data['username']);
$event_name = $data['title'];
$date = date('F j, Y', strtotime($data['event_date']));

// Define image dimensions
$width = 800;
$height = 600;

// Create image resource
$image = imagecreatetruecolor($width, $height);

// Define colors
$white = imagecolorallocate($image, 255, 255, 255);
$black = imagecolorallocate($image, 0, 0, 0);
$blue = imagecolorallocate($image, 74, 144, 226); // Primary Color
$gold = imagecolorallocate($image, 212, 175, 55);

// Fill background
imagefilledrectangle($image, 0, 0, $width, $height, $white);

// Draw a border
imagerectangle($image, 20, 20, $width - 20, $height - 20, $blue);
imagerectangle($image, 25, 25, $width - 25, $height - 25, $gold);

// Add Fonts (Using built-in fonts as fallback if TTF not available)
// For better quality, you would use imagettftext with a path to a .ttf file

// Title
$title_text = "CERTIFICATE OF PARTICIPATION";
// Center the text
$font_size = 5; // Built-in font size (1-5)
$x_title = ($width - imagefontwidth($font_size) * strlen($title_text)) / 2;
imagestring($image, $font_size, $x_title, 100, $title_text, $blue);

// Presented to
$pres_text = "This is proudly presented to";
$x_pres = ($width - imagefontwidth(4) * strlen($pres_text)) / 2;
imagestring($image, 4, $x_pres, 180, $pres_text, $black);

// Name
$name_text = $name;
$font_size_name = 5;
// Hack to make it look bigger/bolder by drawing multiple times slightly offset
$x_name = ($width - imagefontwidth($font_size_name) * strlen($name_text)) / 2;
imagestring($image, $font_size_name, $x_name, 230, $name_text, $black);
imagestring($image, $font_size_name, $x_name + 1, 230, $name_text, $black);

// For
$for_text = "For successfully attending the event";
$x_for = ($width - imagefontwidth(3) * strlen($for_text)) / 2;
imagestring($image, 3, $x_for, 300, $for_text, $black);

// Event Name
$event_text = '"' . $event_name . '"';
$x_event = ($width - imagefontwidth(5) * strlen($event_text)) / 2;
imagestring($image, 5, $x_event, 340, $event_text, $blue);

// Date
$date_text = "Date: " . $date;
$x_date = ($width - imagefontwidth(3) * strlen($date_text)) / 2;
imagestring($image, 3, $x_date, 400, $date_text, $black);

// Signature Line
imageline($image, $width / 2 - 100, 500, $width / 2 + 100, 500, $black);
$sig_text = "Event Organizer";
$x_sig = ($width - imagefontwidth(3) * strlen($sig_text)) / 2;
imagestring($image, 3, $x_sig, 510, $sig_text, $black);


// Output
header('Content-Type: image/png');
header('Content-Disposition: attachment; filename="certificate_' . $event_id . '.png"');
imagepng($image);
imagedestroy($image);
?>