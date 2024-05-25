<?php
// Connect to SQLite database
$db = new SQLite3('safenet.db');

// Create users table if not exists
$db->exec('CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY,
    username TEXT NOT NULL,
    password_hash TEXT NOT NULL,
    email TEXT NOT NULL,
    location TEXT,
    latitude REAL,
    longitude REAL
)');


// Create alerts table if not exists
$db->exec('CREATE TABLE IF NOT EXISTS alerts (
    id INTEGER PRIMARY KEY,
    user_id INTEGER,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    latitude REAL,
    longitude REAL,
    FOREIGN KEY(user_id) REFERENCES users(id)
)');

// Create locations table if not exists
$db->exec('CREATE TABLE IF NOT EXISTS locations (
    id INTEGER PRIMARY KEY,
    alert_id INTEGER,
    latitude REAL,
    longitude REAL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(alert_id) REFERENCES alerts(id)
)');

// Create connections table if not exists
$db->exec('CREATE TABLE IF NOT EXISTS connections (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    friend_id INTEGER,
    relationship TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (friend_id) REFERENCES users(id)
)');
?>