<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$servername = "localhost";
$username = "root";
$password = "";
$database = "university_db";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    // Show detailed error message for debugging
    $error_msg = "Connection failed: " . $conn->connect_error;
    $error_msg .= "<br><br><strong>Common causes:</strong>";
    $error_msg .= "<ul>";
    $error_msg .= "<li>MySQL service is not running in XAMPP</li>";
    $error_msg .= "<li>MySQL is crashing immediately after start</li>";
    $error_msg .= "<li>Port 3306 is blocked or in use by another MySQL instance</li>";
    $error_msg .= "<li>Database '$database' does not exist</li>";
    $error_msg .= "</ul>";
    $error_msg .= "<p><strong>Solution:</strong> Check <a href='mysql_diagnostic.php'>MySQL Diagnostic Tool</a> for detailed information.</p>";
    die($error_msg);
}


$conn->set_charset("utf8mb4");
?>