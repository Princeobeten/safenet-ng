<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/../database/safenet.php';

$username = $_SESSION['username'];

// Get user ID
$sql = "SELECT id FROM users WHERE username = :username";
$stmt = $db->prepare($sql);
$stmt->bindValue(':username', $username, SQLITE3_TEXT);
$result = $stmt->execute();
$user = $result->fetchArray(SQLITE3_ASSOC);
$user_id = $user['id'];

// Get form data
$friend_username = trim($_POST['friendUsername']);
$relationship = trim($_POST['relationship']);

// Get friend ID
$sql = "SELECT id FROM users WHERE username = :friend_username";
$stmt = $db->prepare($sql);
$stmt->bindValue(':friend_username', $friend_username, SQLITE3_TEXT);
$result = $stmt->execute();
$friend = $result->fetchArray(SQLITE3_ASSOC);
$friend_id = $friend['id'];

// Insert connection into database
$sql = "INSERT INTO connections (user_id, friend_id, relationship) VALUES (:user_id, :friend_id, :relationship)";
$stmt = $db->prepare($sql);
$stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
$stmt->bindValue(':friend_id', $friend_id, SQLITE3_INTEGER);
$stmt->bindValue(':relationship', $relationship, SQLITE3_TEXT);

if ($stmt->execute()) {
    header("Location: main-app.php");
} else {
    echo "Something went wrong. Please try again later.";
}
?>
