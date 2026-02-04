<?php
/**
 * Database Installation Script
 * Run this file once to set up the database and tables
 * Access via: http://localhost/university/install.php
 */

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "university_db";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Database Installation</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .success { color: #16a34a; background: #f0fdf4; padding: 15px; border-radius: 8px; margin: 10px 0; border-left: 4px solid #16a34a; }
        .error { color: #dc2626; background: #fef2f2; padding: 15px; border-radius: 8px; margin: 10px 0; border-left: 4px solid #dc2626; }
        .info { color: #2563eb; background: #eff6ff; padding: 15px; border-radius: 8px; margin: 10px 0; border-left: 4px solid #2563eb; }
        h1 { color: #1f2937; }
        .btn { display: inline-block; padding: 10px 20px; background: #fbbf24; color: #000; text-decoration: none; border-radius: 8px; margin-top: 20px; }
    </style>
</head>
<body>
    <h1>🗄️ Database Installation</h1>";

// Read and execute SQL file
$sql_file = __DIR__ . '/database_schema.sql';
if (!file_exists($sql_file)) {
    die("<div class='error'>❌ SQL file not found: database_schema.sql</div></body></html>");
}

$sql_content = file_get_contents($sql_file);

// Split by semicolon and execute each statement
$statements = array_filter(array_map('trim', explode(';', $sql_content)));

$success_count = 0;
$error_count = 0;

foreach ($statements as $statement) {
    if (empty($statement) || strpos($statement, '--') === 0) {
        continue;
    }
    
    // Skip DELIMITER and trigger creation (handle separately)
    if (stripos($statement, 'DELIMITER') !== false || stripos($statement, 'CREATE TRIGGER') !== false) {
        continue;
    }
    
    if ($conn->query($statement)) {
        $success_count++;
        if (stripos($statement, 'CREATE TABLE') !== false) {
            $table_name = '';
            if (preg_match('/CREATE TABLE.*?`?(\w+)`?/i', $statement, $matches)) {
                $table_name = $matches[1];
            }
            echo "<div class='success'>✅ Table '$table_name' created successfully</div>";
        } elseif (stripos($statement, 'CREATE DATABASE') !== false) {
            echo "<div class='success'>✅ Database '$dbname' created successfully</div>";
        } elseif (stripos($statement, 'INSERT INTO') !== false) {
            echo "<div class='success'>✅ Default admin user created</div>";
        }
    } else {
        $error_count++;
        // Ignore "already exists" errors
        if (strpos($conn->error, 'already exists') === false && strpos($conn->error, 'Duplicate entry') === false) {
            echo "<div class='error'>⚠️ " . htmlspecialchars($conn->error) . "</div>";
        }
    }
}

// Create trigger manually
$conn->select_db($dbname);
$trigger_sql = "
DELIMITER $$

CREATE TRIGGER IF NOT EXISTS ensure_one_focal_person
BEFORE UPDATE ON faculty
FOR EACH ROW
BEGIN
    IF NEW.is_focal_person = 1 AND OLD.is_focal_person = 0 THEN
        UPDATE faculty 
        SET is_focal_person = 0, user_rights = 'normal'
        WHERE department_id = NEW.department_id 
        AND id != NEW.id 
        AND is_focal_person = 1;
        
        SET NEW.user_rights = 'focal_person';
    END IF;
    
    IF NEW.is_focal_person = 0 AND OLD.is_focal_person = 1 THEN
        SET NEW.user_rights = 'normal';
    END IF;
END$$

DELIMITER ;
";

// Try to create trigger (may fail if already exists, which is fine)
$conn->multi_query($trigger_sql);
while ($conn->next_result()) {;}

echo "<div class='info'>
    <h2>📊 Installation Summary</h2>
    <p><strong>Successful operations:</strong> $success_count</p>
    <p><strong>Errors (mostly 'already exists'):</strong> $error_count</p>
</div>";

echo "<div class='info'>
    <h2>🔑 Default Admin Credentials</h2>
    <p><strong>Email:</strong> admin@university.edu</p>
    <p><strong>Password:</strong> admin123</p>
    <p><em>Please change the password after first login!</em></p>
</div>";

echo "<div class='success'>
    <h2>✅ Installation Complete!</h2>
    <p>Your database has been set up successfully.</p>
    <a href='login.php' class='btn'>Go to Login Page</a>
    <a href='index.php' class='btn'>Go to Homepage</a>
</div>";

$conn->close();
echo "</body></html>";
?>

