<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/../database/safenet.php';

// Fetch user details from the database based on session username
$username = $_SESSION['username'];
$sql = "SELECT id, location, latitude, longitude FROM users WHERE username = :username";
$stmt = $db->prepare($sql);
$stmt->bindValue(':username', $username, SQLITE3_TEXT);
$result = $stmt->execute();
$user = $result->fetchArray(SQLITE3_ASSOC);
$user_id = $user['id'];
$location = $user['location'];
$latitude = $user['latitude'];
$longitude = $user['longitude'];

// Fetch connected family members or friends
$sql = "SELECT u.username, c.friend_id, c.relationship FROM users u JOIN connections c ON u.id = c.friend_id WHERE c.user_id = :user_id";
$stmt = $db->prepare($sql);
$stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
$connections = $stmt->execute();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SafeNet Nigeria - Main App</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/9e0c1972f9.js" crossorigin="anonymous"></script>
    <script>
        function showAddConnectionPopup() {
            document.getElementById('addConnectionPopup').style.display = 'flex';
        }

        function hideAddConnectionPopup() {
            document.getElementById('addConnectionPopup').style.display = 'none';
        }

        function verifyUsername() {
            var username = document.getElementById('friendUsername').value;
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'verify_username.php', true);
            xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    document.getElementById('verificationResult').innerText = xhr.responseText;
                }
            };
            xhr.send('username=' + username);
        }

        function confirmLogout(event) {
            if (!confirm("Are you sure you want to logout?")) {
                event.preventDefault();
            }
        }
    </script>


</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center p-4">

    <!-- Logout Button -->
    <div class="w-full max-w-sm mb-4">
        <form action="logout.php" method="post" onsubmit="confirmLogout(event)" class="w-full">
            <button type="submit" class="bg-gray-700 text-white w-full px-4 py-2 rounded-full">Logout</button>
        </form>
    </div>

    <!-- Main Container -->
    <div class="w-full max-w-sm bg-white rounded-lg shadow-md p-4">

        <!-- Profile Header -->
        <div class="flex flex-col items-center mb-4">
            <img class="w-24 h-24 rounded-full" src="/img/profile-image.png" alt="Profile Picture">
            <h2 class="mt-2 text-xl font-bold"><?php echo htmlspecialchars($username); ?></h2>
        </div>

        <!-- Current Location -->
        <div class="text-center mb-4">
            <p class="text-gray-700 font-semibold">Latitude:</p>
            <p id="latitude"><?php echo htmlspecialchars($latitude); ?></p>
            <p class="text-gray-700 font-semibold">Longitude:</p>
            <p id="longitude"><?php echo htmlspecialchars($longitude); ?></p>
            <button id="refreshLocation"><i class="fas fa-sync"></i></button>
        </div>

        <!-- Add Connection Button -->
        <div class="text-center mb-4">
            <button onclick="showAddConnectionPopup()" class="bg-blue-500 text-white px-4 py-2 rounded-full">Add Family Member or Friend</button>
        </div>

        <!-- Add Connection Popup -->
        <div id="addConnectionPopup" style="display:none;" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex justify-center items-center">
            <div class="bg-white p-6 rounded shadow-lg w-full max-w-md">
                <h2 class="text-xl font-bold mb-4 text-center">Add Connection</h2>
                <form action="add_connection.php" method="post">
                    <div class="mb-4">
                        <label for="friendUsername" class="block text-sm font-medium text-gray-700">Friend's Username:</label>
                        <input type="text" id="friendUsername" name="friendUsername" required class="mt-1 p-2 block w-full border rounded-md focus:ring-blue-500 focus:border-blue-500">
                        <button type="button" onclick="verifyUsername()" class="mt-2 bg-blue-500 text-white px-4 py-2 rounded-full">Verify</button>
                        <span id="verificationResult" class="block mt-2 text-red-500"></span>
                    </div>
                    <div class="mb-4">
                        <label for="relationship" class="block text-sm font-medium text-gray-700">Relationship:</label>
                        <input type="text" id="relationship" name="relationship" required class="mt-1 p-2 block w-full border rounded-md focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="flex justify-end">
                        <button type="button" onclick="hideAddConnectionPopup()" class="bg-gray-500 text-white px-4 py-2 rounded-full mr-2">Cancel</button>
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-full">Add</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Connected Family Members or Friends List -->
        <div class="bg-white rounded-lg shadow-md p-4 mb-4">
            <h2 class="text-xl font-bold mb-2 text-center">Connected Family Members or Friends:</h2>
            <ul class="list-disc list-inside space-y-2">
                <?php while ($connection = $connections->fetchArray(SQLITE3_ASSOC)): ?>
                    <li class="flex justify-between items-center">
                        <span><?php echo htmlspecialchars($connection['username']) . " (" . htmlspecialchars($connection['relationship']) . ")"; ?></span>
                        <form action="delete_connection.php" method="post" onsubmit="return confirm('Are you sure you want to delete this connection?');" class="inline">
                            <input type="hidden" name="friend_id" value="<?php echo htmlspecialchars($connection['friend_id']); ?>">
                            <button type="submit" class="bg-red-500 text-white px-2 py-1 rounded-full text-sm">Delete</button>
                        </form>
                    </li>
                <?php endwhile; ?>
            </ul>
        </div>

        <!-- Panic Button -->
        <div class="text-center mb-4">
            <h2 class="text-xl font-bold mb-2">Panic Button</h2>
            <button id="panicButton" class="bg-red-500 text-white px-4 py-2 rounded-full">Panic</button>
        </div>
    </div>

    <script>
       document.getElementById('panicButton').addEventListener('click', function() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(sendPanicAlert);
            } else {
                alert("Geolocation is not supported by this browser.");
            }
        });

        function sendPanicAlert(position) {
            var latitude = position.coords.latitude;
            var longitude = position.coords.longitude;

            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'send_panic_alert.php', true);
            xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    alert('Panic alert sent to all connected family and friends!');
                }
            };
            xhr.send('latitude=' + latitude + '&longitude=' + longitude);
        }



        document.getElementById('refreshLocation').addEventListener('click', function() {
            getLocation();
        });

        function getLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(showPosition);
            } else {
                alert("Geolocation is not supported by this browser.");
            }
        }

        function showPosition(position) {
            var latitude = position.coords.latitude;
            var longitude = position.coords.longitude;
            document.getElementById('latitude').textContent = latitude;
            document.getElementById('longitude').textContent = longitude;
            // Now you can send latitude and longitude to your PHP script to store in the database
            sendLocationToServer(latitude, longitude);
        }

        function sendLocationToServer(latitude, longitude) {
            // You can use AJAX to send the latitude and longitude to your PHP script
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'store_location.php', true);
            xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    console.log(xhr.responseText);
                }
            };
            xhr.send('latitude=' + latitude + '&longitude=' + longitude);
        }
    </script>
</body>
</html>
