<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "university_db";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

$pdf_dir = __DIR__ . '/uploads/booking_pdfs';
if (!is_dir($pdf_dir)) {
    if (mkdir($pdf_dir, 0777, true)) {
        $dir_success = true;
    } else {
        $dir_success = false;
    }
} else {
    $dir_success = true;
}

$errors = [];
$success = [];

if ($dir_success) {
    $success[] = "PDF directory 'uploads/booking_pdfs' ready";
} else {
    $errors[] = "Error creating PDF directory 'uploads/booking_pdfs'";
}

$sql1 = "CREATE TABLE IF NOT EXISTS rooms (
    id INT(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    location VARCHAR(200),
    capacity INT(11),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql1)) {
    $success[] = "Table 'rooms' created successfully";
} else {
    $errors[] = "Error creating 'rooms' table: " . $conn->error;
}

$sql2 = "CREATE TABLE IF NOT EXISTS room_bookings (
    id INT(11) NOT NULL AUTO_INCREMENT,
    faculty_id INT(11) NOT NULL,
    department_id INT(11),
    room_id INT(11) NOT NULL,
    booking_date DATE NOT NULL,
    time_slot VARCHAR(20) NOT NULL,
    event_title VARCHAR(255) NOT NULL,
    num_persons INT(11) NOT NULL,
    status ENUM('Pending','Approved','Rejected') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_room_booking_unique (room_id, booking_date, time_slot)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql2)) {
    $success[] = "Table 'room_bookings' created successfully";
    
    $check_fk1 = $conn->query("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = 'university_db' AND TABLE_NAME = 'room_bookings' AND CONSTRAINT_NAME = 'fk_room_bookings_faculty'");
    if ($check_fk1->num_rows == 0) {
        $fk1 = "ALTER TABLE room_bookings ADD CONSTRAINT fk_room_bookings_faculty FOREIGN KEY (faculty_id) REFERENCES faculty(id) ON DELETE CASCADE";
        if ($conn->query($fk1)) {
            $success[] = "Foreign key 'fk_room_bookings_faculty' added";
        } else {
            $errors[] = "Error adding foreign key 'fk_room_bookings_faculty': " . $conn->error;
        }
    }
    
    $check_fk2 = $conn->query("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = 'university_db' AND TABLE_NAME = 'room_bookings' AND CONSTRAINT_NAME = 'fk_room_bookings_department'");
    if ($check_fk2->num_rows == 0) {
        $fk2 = "ALTER TABLE room_bookings ADD CONSTRAINT fk_room_bookings_department FOREIGN KEY (department_id) REFERENCES departments(depart_id) ON DELETE SET NULL";
        if ($conn->query($fk2)) {
            $success[] = "Foreign key 'fk_room_bookings_department' added";
        } else {
            $errors[] = "Error adding foreign key 'fk_room_bookings_department': " . $conn->error;
        }
    }
    
    $check_fk3 = $conn->query("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = 'university_db' AND TABLE_NAME = 'room_bookings' AND CONSTRAINT_NAME = 'fk_room_bookings_room'");
    if ($check_fk3->num_rows == 0) {
        $fk3 = "ALTER TABLE room_bookings ADD CONSTRAINT fk_room_bookings_room FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE";
        if ($conn->query($fk3)) {
            $success[] = "Foreign key 'fk_room_bookings_room' added";
        } else {
            $errors[] = "Error adding foreign key 'fk_room_bookings_room': " . $conn->error;
        }
    }
} else {
    $errors[] = "Error creating 'room_bookings' table: " . $conn->error;
}

$sql3 = "CREATE TABLE IF NOT EXISTS room_booking_pdfs (
    id INT(11) NOT NULL AUTO_INCREMENT,
    booking_id INT(11) NOT NULL,
    pdf_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_room_booking_pdf_booking (booking_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql3)) {
    $success[] = "Table 'room_booking_pdfs' created successfully";
    
    $check_fk4 = $conn->query("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = 'university_db' AND TABLE_NAME = 'room_booking_pdfs' AND CONSTRAINT_NAME = 'fk_room_booking_pdfs_booking'");
    if ($check_fk4->num_rows == 0) {
        $fk4 = "ALTER TABLE room_booking_pdfs ADD CONSTRAINT fk_room_booking_pdfs_booking FOREIGN KEY (booking_id) REFERENCES room_bookings(id) ON DELETE CASCADE";
        if ($conn->query($fk4)) {
            $success[] = "Foreign key 'fk_room_booking_pdfs_booking' added";
        } else {
            $errors[] = "Error adding foreign key 'fk_room_booking_pdfs_booking': " . $conn->error;
        }
    }
} else {
    $errors[] = "Error creating 'room_booking_pdfs' table: " . $conn->error;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Booking Tables Setup</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        .success {
            color: #28a745;
            background: #d4edda;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .error {
            color: #dc3545;
            background: #f8d7da;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .info {
            color: #0c5460;
            background: #d1ecf1;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
        a {
            color: #007bff;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border-radius: 5px;
        }
        a:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Room Booking Tables Setup</h1>
        
        <?php if (count($success) > 0): ?>
            <?php foreach ($success as $msg): ?>
                <div class="success">✓ <?= htmlspecialchars($msg) ?></div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <?php if (count($errors) > 0): ?>
            <?php foreach ($errors as $msg): ?>
                <div class="error">✗ <?= htmlspecialchars($msg) ?></div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <?php if (count($errors) === 0 && count($success) > 0): ?>
            <div class="info">
                <strong>Setup Complete!</strong><br>
                All room booking tables have been created successfully.
            </div>
            <a href="admin_dashboard.php?tab=rooms">Go to Admin Dashboard - Rooms</a>
        <?php endif; ?>
    </div>
</body>
</html>

