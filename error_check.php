<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<h1>PHP Error Check</h1>";
echo "<p>PHP Version: " . phpversion() . "</p>";

// Test database connection
echo "<h2>Database Connection Test</h2>";
$servername = "localhost";
$username = "root";
$password = "";
$database = "university_db";

try {
    $conn = new mysqli($servername, $username, $password, $database);
    
    if ($conn->connect_error) {
        echo "<p style='color: red;'>❌ Database connection failed: " . $conn->connect_error . "</p>";
    } else {
        echo "<p style='color: green;'>✅ Database connection successful!</p>";
        
        // Check if tables exist
        $tables = ['students', 'departments', 'faculty', 'courses', 'admin', 'rooms', 'room_bookings'];
        echo "<h3>Table Check:</h3><ul>";
        foreach ($tables as $table) {
            $result = $conn->query("SHOW TABLES LIKE '$table'");
            if ($result && $result->num_rows > 0) {
                echo "<li style='color: green;'>✅ Table '$table' exists</li>";
            } else {
                echo "<li style='color: red;'>❌ Table '$table' does NOT exist</li>";
            }
        }
        echo "</ul>";
    }
    $conn->close();
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

// Test file includes
echo "<h2>File Include Test</h2>";
$files_to_check = [
    'db_connect.php',
    'room_booking_functions.php',
    'index.php',
    'admin_dashboard.php',
    'login.php'
];

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✅ File '$file' exists</p>";
    } else {
        echo "<p style='color: red;'>❌ File '$file' NOT found</p>";
    }
}

// Test session
echo "<h2>Session Test</h2>";
if (session_start()) {
    echo "<p style='color: green;'>✅ Session started successfully</p>";
} else {
    echo "<p style='color: red;'>❌ Session failed to start</p>";
}

echo "<hr>";
echo "<p><a href='index.php'>Go to Index</a> | <a href='login.php'>Go to Login</a></p>";
?>


