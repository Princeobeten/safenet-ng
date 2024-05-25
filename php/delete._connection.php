<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/../database/safenet.php';

// Fetch user ID from the database based on session username
$username = $_SESSION['username'];
$sql = "SELECT id FROM users WHERE username = :username";
$stmt = $db->prepare($sql);
$stmt->bindValue(':username', $username, SQLITE3_TEXT);
$result = $stmt->execute();
$user = $result->fetchArray(SQLITE3_ASSOC);
$user_id = $user['id'];

// Get the friend ID from the POST request
$friend_id = $_POST['friend_id'];

// Delete the connection
$sql = "DELETE FROM connections WHERE user_id = :user_id AND friend_id = :friend_id";
$stmt = $db->prepare($sql);
$stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
$stmt->bindValue(':friend_id', $friend_id, SQLITE3_INTEGER);
$stmt->execute();

// Redirect back to the main app page
header("Location: main_app.php");
exit();
?>
