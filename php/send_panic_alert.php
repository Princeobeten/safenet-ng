<?php
session_start();
require_once __DIR__ . '/../database/safenet.php';

if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo "Unauthorized";
    exit();
}

$username = $_SESSION['username'];
$sql = "SELECT id FROM users WHERE username = :username";
$stmt = $db->prepare($sql);
$stmt->bindValue(':username', $username, SQLITE3_TEXT);
$result = $stmt->execute();
$user = $result->fetchArray(SQLITE3_ASSOC);

if ($user) {
    $user_id = $user['id'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];

    // Insert alert into the alerts table
    $sql = "INSERT INTO alerts (user_id, latitude, longitude) VALUES (:user_id, :latitude, :longitude)";
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
    $stmt->bindValue(':latitude', $latitude, SQLITE3_FLOAT);
    $stmt->bindValue(':longitude', $longitude, SQLITE3_FLOAT);
    $stmt->execute();

    // Fetch all connected family members or friends
    $sql = "SELECT u.email FROM users u JOIN connections c ON u.id = c.friend_id WHERE c.user_id = :user_id";
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
    $connections = $stmt->execute();

    // Send email notifications to connected users
    $subject = "Panic Alert from SafeNet Nigeria";
    $message = "Alert! Your connection {$username} has pressed the panic button. Location: Latitude {$latitude}, Longitude {$longitude}";

    while ($connection = $connections->fetchArray(SQLITE3_ASSOC)) {
        $to = $connection['email'];
        mail($to, $subject, $message);
    }

    echo "Panic alert sent successfully!";
} else {
    http_response_code(404);
    echo "User not found";
}
?>
