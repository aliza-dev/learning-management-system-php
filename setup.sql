<?php
// -----------------------------
// DATABASE SETUP SCRIPT
// -----------------------------
// This script creates the database, the `users` table, and adds one admin user.
// Run it once by visiting: http://localhost/institute/setup.php
// -----------------------------

$servername = "localhost";
$username = "root";     // default for XAMPP
$password = "";         // default for XAMPP
$dbname = "university_db"; // You can rename if you want

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}

// Create Database
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    echo "✅ Database '$dbname' created successfully.<br>";
} else {
    echo "❌ Error creating database: " . $conn->error . "<br>";
}

// Select the Database
$conn->select_db($dbname);

// Create Users Table
$sql = "CREATE TABLE IF NOT EXISTS students (
    id INT(11) NOT NULL AUTO_INCREMENT,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
)";
if ($conn->query($sql) === TRUE) {
    echo "✅ Table 'users' created successfully.<br>";
} else {
    echo "❌ Error creating table: " . $conn->error . "<br>";
}

// Insert a Sample Admin User
$hashed_password = password_hash("admin123", PASSWORD_DEFAULT);
$sql = "INSERT INTO users (fullname, email, username, password)
        VALUES ('Admin User', 'admin@institute.edu', 'admin', '$hashed_password')";

if ($conn->query($sql) === TRUE) {
    echo "✅ Sample user 'admin' added successfully.<br>";
} else {
    echo "⚠️ Note: Sample user might already exist.<br>";
}

$conn->close();

echo "<br>🎓 Setup completed successfully! You can now use your login and signup pages.";
?>