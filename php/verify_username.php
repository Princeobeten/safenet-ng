<?php
require_once __DIR__ . '/../database/safenet.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);

    $sql = "SELECT id FROM users WHERE username = :username";
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':username', $username, SQLITE3_TEXT);

    $result = $stmt->execute();
    if ($result->fetchArray(SQLITE3_ASSOC)) {
        echo "Username is available.";
    } else {
        echo "Username not found.";
    }
}
?>
