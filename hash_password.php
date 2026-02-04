<?php
// Create this file as hash_password.php
// Run it once to get the hashed password, then delete it for security

$password = 'maqeel1122'; // Your admin password
$hashed = password_hash($password, PASSWORD_DEFAULT);

echo "Original Password: " . $password . "<br>";
echo "Hashed Password: " . $hashed . "<br><br>";

echo "Copy this SQL command and run it in phpMyAdmin:<br><br>";
echo "<textarea rows='3' cols='80' style='font-family: monospace;'>";
echo "UPDATE `admins` SET `password` = '$hashed' WHERE `email` = 'maqeel@university.edu';";
echo "</textarea>";
?>