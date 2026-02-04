<?php
// MySQL Diagnostic Tool
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>MySQL Diagnostic Tool</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 30px auto; padding: 20px; background: #f5f5f5; }
        .success { color: #16a34a; background: #f0fdf4; padding: 15px; border-radius: 8px; margin: 10px 0; border-left: 4px solid #16a34a; }
        .error { color: #dc2626; background: #fef2f2; padding: 15px; border-radius: 8px; margin: 10px 0; border-left: 4px solid #dc2626; }
        .warning { color: #d97706; background: #fffbeb; padding: 15px; border-radius: 8px; margin: 10px 0; border-left: 4px solid #d97706; }
        .info { color: #2563eb; background: #eff6ff; padding: 15px; border-radius: 8px; margin: 10px 0; border-left: 4px solid #2563eb; }
        h1 { color: #1f2937; }
        h2 { color: #374151; margin-top: 30px; }
        code { background: #f3f4f6; padding: 2px 6px; border-radius: 4px; font-family: 'Courier New', monospace; }
        ul { line-height: 1.8; }
    </style>
</head>
<body>
    <h1>🔍 MySQL Diagnostic Tool</h1>
    
    <?php
    $servername = "localhost";
    $username = "root";
    $password = "";
    $database = "university_db";
    
    // Test 1: Check if MySQL port is accessible
    echo "<h2>1. Port Connectivity Test</h2>";
    $port = 3306;
    $connection = @fsockopen($servername, $port, $errno, $errstr, 2);
    if ($connection) {
        echo "<div class='success'>✅ Port $port is open and accessible</div>";
        fclose($connection);
    } else {
        echo "<div class='error'>❌ Cannot connect to port $port</div>";
        echo "<div class='info'>Error: $errstr ($errno)</div>";
        echo "<div class='warning'>💡 MySQL might not be running or port is blocked</div>";
    }
    
    // Test 2: Try MySQL connection
    echo "<h2>2. MySQL Connection Test</h2>";
    try {
        $conn = @new mysqli($servername, $username, $password);
        
        if ($conn->connect_error) {
            echo "<div class='error'>❌ MySQL Connection Failed</div>";
            echo "<div class='info'>Error: " . $conn->connect_error . "</div>";
            echo "<div class='warning'>💡 Possible causes:</div>";
            echo "<ul>";
            echo "<li>MySQL service is not running</li>";
            echo "<li>MySQL is crashing immediately after start</li>";
            echo "<li>Wrong credentials (username/password)</li>";
            echo "<li>Port conflict (another MySQL instance running)</li>";
            echo "</ul>";
        } else {
            echo "<div class='success'>✅ MySQL Connection Successful!</div>";
            echo "<div class='info'>MySQL Version: " . $conn->server_info . "</div>";
            
            // Test 3: Check if database exists
            echo "<h2>3. Database Check</h2>";
            $db_check = $conn->query("SHOW DATABASES LIKE '$database'");
            if ($db_check && $db_check->num_rows > 0) {
                echo "<div class='success'>✅ Database '$database' exists</div>";
                
                // Test 4: Check tables
                $conn->select_db($database);
                echo "<h2>4. Tables Check</h2>";
                $tables = ['students', 'departments', 'faculty', 'courses', 'admin', 'rooms', 'room_bookings'];
                $missing_tables = [];
                
                foreach ($tables as $table) {
                    $result = $conn->query("SHOW TABLES LIKE '$table'");
                    if ($result && $result->num_rows > 0) {
                        echo "<div class='success'>✅ Table '$table' exists</div>";
                    } else {
                        echo "<div class='warning'>⚠️ Table '$table' does NOT exist</div>";
                        $missing_tables[] = $table;
                    }
                }
                
                if (!empty($missing_tables)) {
                    echo "<div class='info'>💡 Run <code>install.php</code> or <code>setup.php</code> to create missing tables</div>";
                }
            } else {
                echo "<div class='warning'>⚠️ Database '$database' does NOT exist</div>";
                echo "<div class='info'>💡 Run <code>install.php</code> or <code>setup.php</code> to create the database</div>";
            }
            
            $conn->close();
        }
    } catch (Exception $e) {
        echo "<div class='error'>❌ Exception: " . $e->getMessage() . "</div>";
    }
    
    // Test 5: Check for other MySQL processes
    echo "<h2>5. Process Check</h2>";
    $output = shell_exec('tasklist | findstr mysqld');
    if ($output) {
        echo "<div class='info'>MySQL processes found:</div>";
        echo "<pre style='background: #f3f4f6; padding: 10px; border-radius: 4px;'>" . htmlspecialchars($output) . "</pre>";
    } else {
        echo "<div class='warning'>⚠️ No mysqld.exe processes found</div>";
    }
    
    // Test 6: Check port usage
    echo "<h2>6. Port 3306 Usage</h2>";
    $port_output = shell_exec('netstat -ano | findstr :3306');
    if ($port_output) {
        echo "<div class='info'>Port 3306 is in use:</div>";
        echo "<pre style='background: #f3f4f6; padding: 10px; border-radius: 4px;'>" . htmlspecialchars($port_output) . "</pre>";
    } else {
        echo "<div class='warning'>⚠️ Port 3306 is not in use</div>";
    }
    ?>
    
    <h2>📋 Recommended Actions</h2>
    <div class="info">
        <h3>If MySQL is crashing:</h3>
        <ol>
            <li><strong>Check XAMPP MySQL Logs:</strong> Open XAMPP Control Panel → Click "Logs" button next to MySQL</li>
            <li><strong>Check for port conflicts:</strong> Make sure no other MySQL instance is running</li>
            <li><strong>Try restarting MySQL:</strong> Stop and start MySQL in XAMPP Control Panel</li>
            <li><strong>Check MySQL data folder:</strong> Ensure <code>C:\xampp\mysql\data</code> has proper permissions</li>
            <li><strong>Run as Administrator:</strong> Try running XAMPP Control Panel as Administrator</li>
        </ol>
        
        <h3>If connection works but database is missing:</h3>
        <ol>
            <li>Access: <a href="install.php">install.php</a> to set up the database</li>
            <li>Or access: <a href="setup.php">setup.php</a> for basic setup</li>
        </ol>
    </div>
    
    <hr>
    <p><a href="index.php">← Back to Home</a> | <a href="error_check.php">Error Check Tool</a></p>
</body>
</html>


