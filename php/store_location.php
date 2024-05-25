<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/../database/safenet.php';

// Fetch user details from the database based on session username
$username = $_SESSION['username'];
$sql = "SELECT id FROM users WHERE username = :username";
$stmt = $db->prepare($sql);
$stmt->bindValue(':username', $username, SQLITE3_TEXT);
$result = $stmt->execute();
$user = $result->fetchArray(SQLITE3_ASSOC);
$user_id = $user['id'];

// Retrieve latitude and longitude from the POST data
$latitude = $_POST['latitude'];
$longitude = $_POST['longitude'];

// Store latitude and longitude in the database
$sql = "UPDATE users SET latitude = :latitude, longitude = :longitude WHERE id = :user_id";
$stmt = $db->prepare($sql);
$stmt->bindValue(':latitude', $latitude, SQLITE3_FLOAT);
$stmt->bindValue(':longitude', $longitude, SQLITE3_FLOAT);
$stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
$stmt->execute();

echo "Location updated successfully.";
?>
