<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "university_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

require_once __DIR__ . '/room_booking_functions.php';

// Check if admin is logged in
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Get admin details safely
$admin_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
$admin_name = isset($_SESSION['name']) ? $_SESSION['name'] : 'Admin';
$admin_email = isset($_SESSION['email']) ? $_SESSION['email'] : '';

// Get counts with error handling
$students_count = 0;
$departments_count = 0;
$faculty_count = 0;
$courses_count = 0;

$result = $conn->query("SELECT COUNT(*) as count FROM students");
if ($result) {
    $students_count = $result->fetch_assoc()['count'];
}

$result = $conn->query("SELECT COUNT(*) as count FROM departments");
if ($result) {
    $departments_count = $result->fetch_assoc()['count'];
}

$result = $conn->query("SELECT COUNT(*) as count FROM faculty");
if ($result) {
    $faculty_count = $result->fetch_assoc()['count'];
}

$result = $conn->query("SELECT COUNT(*) as count FROM courses");
if ($result) {
    $courses_count = $result->fetch_assoc()['count'];
}

$booking_tab = isset($_GET['tab']) ? $_GET['tab'] : 'students';

if ($booking_tab === 'rooms') {
    if (isset($_POST['create_room'])) {
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $location = isset($_POST['location']) ? trim($_POST['location']) : '';
        $capacity = isset($_POST['capacity']) ? (int)$_POST['capacity'] : 0;
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        if ($name !== '' && $capacity >= 0) {
            rb_create_room($conn, $name, $location, $capacity, $is_active);
        }
        header("Location: admin_dashboard.php?tab=rooms");
        exit();
    }
    if (isset($_POST['update_room'])) {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $location = isset($_POST['location']) ? trim($_POST['location']) : '';
        $capacity = isset($_POST['capacity']) ? (int)$_POST['capacity'] : 0;
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        if ($id > 0 && $name !== '' && $capacity >= 0) {
            rb_update_room($conn, $id, $name, $location, $capacity, $is_active);
        }
        header("Location: admin_dashboard.php?tab=rooms");
        exit();
    }
    if (isset($_GET['delete_room'])) {
        $id = (int)$_GET['delete_room'];
        if ($id > 0) {
            rb_delete_room($conn, $id);
        }
        header("Location: admin_dashboard.php?tab=rooms");
        exit();
    }
}

if ($booking_tab === 'bookings') {
    if (isset($_POST['update_booking_status'])) {
        $booking_id = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;
        $status = isset($_POST['status']) ? $_POST['status'] : 'Pending';
        if ($booking_id > 0) {
            rb_update_booking_status($conn, $booking_id, $status);
        }
        header("Location: admin_dashboard.php?tab=bookings");
        exit();
    }
}

// Add Student Handler
if (isset($_POST['add_student'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $major = $_POST['major'];
    $year = $_POST['year'];
    $gpa = floatval($_POST['gpa']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Generate unique student ID by finding the next available number
    $current_year = date('Y');
    $prefix = 'STU-' . $current_year . '-';
    
    // Find the highest number for the current year
    $result = $conn->query("SELECT student_id FROM students WHERE student_id LIKE '" . $prefix . "%' ORDER BY student_id DESC LIMIT 1");
    
    $next_num = 1;
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $last_id = $row['student_id'];
        // Extract the number part (e.g., "0007" from "STU-2025-0007")
        $last_num = intval(substr($last_id, strrpos($last_id, '-') + 1));
        $next_num = $last_num + 1;
    }
    
    // Keep trying until we find an available ID (safety check)
    $max_attempts = 100;
    $attempts = 0;
    do {
        $student_id = $prefix . str_pad($next_num, 4, '0', STR_PAD_LEFT);
        $check_stmt = $conn->prepare("SELECT student_id FROM students WHERE student_id = ?");
        $check_stmt->bind_param("s", $student_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $exists = $check_result->num_rows > 0;
        $check_stmt->close();
        
        if (!$exists) {
            break;
        }
        $next_num++;
        $attempts++;
    } while ($exists && $attempts < $max_attempts);

    $stmt = $conn->prepare("INSERT INTO students (student_id, name, email, phone, major, year, gpa, enrollment_date, password) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?)");
    $stmt->bind_param("ssssssds", $student_id, $name, $email, $phone, $major, $year, $gpa, $password);
    
    if ($stmt->execute()) {
        $stmt->close();
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $error_message = "Error adding student: " . $stmt->error;
        $stmt->close();
        // You might want to set this in a session variable and display it
    }
}

// Delete Student Handler
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_dashboard.php");
    exit();
}

// Update Student Handler
if (isset($_POST['update_student'])) {
    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $major = $_POST['major'];
    $year = $_POST['year'];
    $gpa = floatval($_POST['gpa']);

    $stmt = $conn->prepare("UPDATE students SET name=?, email=?, phone=?, major=?, year=?, gpa=? WHERE id=?");
    $stmt->bind_param("sssssdi", $name, $email, $phone, $major, $year, $gpa, $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_dashboard.php");
    exit();
}

// Get students
$students = $conn->query("SELECT * FROM students ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Learning Management System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #000000;
            --primary-dark: #1a1a1a;
            --primary-light: #333333;
            --accent-color: #fbbf24;
            --accent-light: #fcd34d;
            --text-dark: #000000;
            --text-light: #4b5563;
            --bg-light: #f9fafb;
            --bg-white: #ffffff;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-light);
            overflow-y: auto;
        }

        header {
            background: var(--primary-color);
            color: #fff;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow-md);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        header h2 {
            margin: 0;
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .nav-links {
            display: flex;
            gap: 0.5rem;
            align-items: center;
            flex-wrap: nowrap;
            white-space: nowrap;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.1);
            white-space: nowrap;
        }

        .nav-links a:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .nav-links a.logout-btn {
            background: rgba(220, 53, 69, 0.9);
        }

        .nav-links a.logout-btn:hover {
            background: #dc3545;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2.5rem 2rem;
        }

        .welcome-section {
            background: white;
            padding: 2rem;
            border-radius: 20px;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-md);
        }

        .welcome-section h3 {
            color: var(--text-dark);
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .welcome-section p {
            color: var(--text-light);
            font-size: 1rem;
        }

        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 2rem;
            border-radius: 20px;
            box-shadow: var(--shadow-md);
            display: flex;
            align-items: center;
            gap: 1.5rem;
            transition: all 0.3s ease;
            border: 1px solid rgba(30, 58, 138, 0.1);
        }

        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
            border-color: var(--primary-color);
        }

        .stat-icon {
            font-size: 2.5rem;
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--accent-color);
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(251, 191, 36, 0.3);
        }

        .stat-info h4 {
            font-size: 2rem;
            color: var(--text-dark);
            margin-bottom: 0.25rem;
            font-weight: 700;
        }

        .stat-info p {
            color: var(--text-light);
            font-size: 0.95rem;
            font-weight: 500;
        }

        .table-container {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: var(--shadow-md);
            overflow-x: auto;
        }

        .table-container h3 {
            margin-bottom: 1.5rem;
            color: var(--text-dark);
            font-size: 1.5rem;
            font-weight: 700;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
        }
        
        td {
            word-wrap: break-word;
            max-width: 200px;
        }
        
        td.actions {
            white-space: nowrap;
            max-width: none;
        }

        th {
            background: var(--primary-color);
            color: var(--accent-color);
            font-weight: 600;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        tr:hover {
            background: var(--bg-light);
        }

        td {
            color: var(--text-dark);
            font-size: 0.95rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }

        .btn-add {
            background: var(--accent-color);
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 15px rgba(251, 191, 36, 0.3);
        }

        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(251, 191, 36, 0.4);
            background: var(--accent-light);
        }

        .btn-edit {
            background: #10b981;
            color: white;
        }

        .btn-edit:hover {
            background: #059669;
            transform: translateY(-2px);
        }

        .btn-delete {
            background: #ef4444;
            color: white;
        }

        .btn-delete:hover {
            background: #dc2626;
            transform: translateY(-2px);
        }

        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background: white;
            padding: 2.5rem;
            border-radius: 20px;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            overflow-x: hidden;
            box-shadow: var(--shadow-xl);
            animation: fadeInUp 0.3s ease;
        }

        .modal-content::-webkit-scrollbar {
            width: 8px;
        }

        .modal-content::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .modal-content::-webkit-scrollbar-thumb {
            background: var(--accent-color);
            border-radius: 10px;
        }

        .modal-content::-webkit-scrollbar-thumb:hover {
            background: var(--accent-light);
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-content h3 {
            margin-bottom: 1.5rem;
            text-align: center;
            color: var(--text-dark);
            font-size: 1.5rem;
            font-weight: 700;
        }

        .modal-content input,
        .modal-content select {
            width: 100%;
            margin-bottom: 1rem;
            padding: 0.875rem 1.25rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 0.95rem;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
        }

        .modal-content input:focus,
        .modal-content select:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 4px rgba(251, 191, 36, 0.2);
        }

        .close-btn {
            background: #9ca3af;
            color: white;
            margin-right: 0.5rem;
        }

        .close-btn:hover {
            background: #6b7280;
        }

        .actions {
            display: flex;
            gap: 0.5rem;
            white-space: nowrap;
        }
        
        .actions .btn {
            white-space: nowrap;
            flex-shrink: 0;
        }

        @media (max-width: 768px) {
            header {
                flex-direction: column;
                gap: 1rem;
                padding: 1rem;
            }

            .nav-links {
                width: 100%;
                justify-content: center;
                flex-wrap: wrap;
            }

            .nav-links a {
                font-size: 0.75rem;
                padding: 0.4rem 0.75rem;
            }

            .stats-cards {
                grid-template-columns: 1fr;
            }

            .table-container {
                overflow-x: auto;
            }

            table {
                min-width: 800px;
            }
        }
    </style>
</head>

<body>
    <header>
        <h2>🎓 Admin Dashboard - Learning Management System</h2>
        <div class="nav-links">
            <a href="admin_dashboard.php">Students</a>
            <a href="admin_dashboard.php?tab=rooms">Rooms</a>
            <a href="admin_dashboard.php?tab=bookings">Room Bookings</a>
            <a href="admin_departments.php">Departments</a>
            <a href="admin_faculty.php">Faculty</a>
            <a href="admin_content.php">Content</a>
            <a href="login.php?logout=1" class="logout-btn">Logout</a>
        </div>
    </header>
    <div class="container">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <h3>Welcome back, <?= htmlspecialchars($admin_name) ?>!</h3>
            <p><?= htmlspecialchars($admin_email) ?></p>
        </div>

        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-icon">🎓</div>
                <div class="stat-info">
                    <h4><?= $students_count ?></h4>
                    <p>Total Students</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🏢</div>
                <div class="stat-info">
                    <h4><?= $departments_count ?></h4>
                    <p>Departments</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">👨‍🏫</div>
                <div class="stat-info">
                    <h4><?= $faculty_count ?></h4>
                    <p>Faculty Members</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📚</div>
                <div class="stat-info">
                    <h4><?= $courses_count ?></h4>
                    <p>Courses</p>
                </div>
            </div>
        </div>

        <?php if ($booking_tab === 'students'): ?>
            <div class="table-container">
                <h3>Recent Students</h3>
                <button class="btn btn-add" onclick="openAddModal()">+ Add Student</button>

                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Major</th>
                            <th>Year</th>
                            <th>GPA</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($students && $students->num_rows > 0): ?>
                            <?php while ($row = $students->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['id'] ?></td>
                                    <td><?= htmlspecialchars($row['student_id']) ?></td>
                                    <td><?= htmlspecialchars($row['name']) ?></td>
                                    <td><?= htmlspecialchars($row['email']) ?></td>
                                    <td><?= htmlspecialchars($row['phone']) ?></td>
                                    <td><?= htmlspecialchars($row['major']) ?></td>
                                    <td><?= htmlspecialchars($row['year']) ?></td>
                                    <td><?= number_format($row['gpa'], 2) ?></td>
                                    <td class="actions">
                                        <button class="btn btn-edit" onclick='openEditModal(<?= json_encode($row) ?>)'>Edit</button>
                                        <a class="btn btn-delete" href="?delete=<?= $row['id'] ?>"
                                           onclick="return confirm('Delete this student?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" style="text-align:center;">No students found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php elseif ($booking_tab === 'rooms'): ?>
            <?php
            $rooms = rb_get_rooms($conn);
            ?>
            <div class="table-container">
                <h3>Rooms</h3>
                <button class="btn btn-add" onclick="openRoomAddModal()">+ Add Room</button>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Location</th>
                            <th>Capacity</th>
                            <th>Active</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($rooms) > 0): ?>
                            <?php foreach ($rooms as $room): ?>
                                <tr>
                                    <td><?= $room['id'] ?></td>
                                    <td><?= htmlspecialchars($room['name']) ?></td>
                                    <td><?= htmlspecialchars($room['location']) ?></td>
                                    <td><?= (int)$room['capacity'] ?></td>
                                    <td><?= $room['is_active'] ? 'Yes' : 'No' ?></td>
                                    <td class="actions">
                                        <button class="btn btn-edit" onclick='openRoomEditModal(<?= json_encode($room) ?>)'>Edit</button>
                                        <a class="btn btn-delete" href="?tab=rooms&delete_room=<?= $room['id'] ?>" onclick="return confirm('Delete this room? Existing bookings will also be removed.')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align:center;">No rooms defined</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php elseif ($booking_tab === 'bookings'): ?>
            <?php
            $bookings = rb_get_all_bookings($conn);
            ?>
            <div class="table-container">
                <h3>Room Booking Requests</h3>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Faculty</th>
                            <th>Department</th>
                            <th>Room</th>
                            <th>Date</th>
                            <th>Time Slot</th>
                            <th>Event</th>
                            <th>Persons</th>
                            <th>Status</th>
                            <th>Change Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($bookings) > 0): ?>
                            <?php foreach ($bookings as $b): ?>
                                <tr>
                                    <td><?= $b['id'] ?></td>
                                    <td><?= htmlspecialchars($b['first_name'] . ' ' . $b['last_name']) ?></td>
                                    <td><?= htmlspecialchars($b['department_name'] ? $b['department_name'] : 'N/A') ?></td>
                                    <td><?= htmlspecialchars($b['room_name']) ?></td>
                                    <td><?= htmlspecialchars($b['booking_date']) ?></td>
                                    <td><?= htmlspecialchars($b['time_slot']) ?></td>
                                    <td><?= htmlspecialchars($b['event_title']) ?></td>
                                    <td><?= (int)$b['num_persons'] ?></td>
                                    <td><?= htmlspecialchars($b['status']) ?></td>
                                    <td>
                                        <form method="post" style="display:flex;gap:0.25rem;align-items:center;">
                                            <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                            <select name="status" style="padding:0.25rem 0.35rem;border-radius:6px;border:1px solid #e5e7eb;font-size:0.8rem;">
                                                <option value="Pending" <?= $b['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                                <option value="Approved" <?= $b['status'] === 'Approved' ? 'selected' : '' ?>>Approved</option>
                                                <option value="Rejected" <?= $b['status'] === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                                            </select>
                                            <button type="submit" name="update_booking_status" class="btn btn-edit" style="padding:0.3rem 0.6rem;font-size:0.8rem;">Save</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" style="text-align:center;">No bookings found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Add Modal -->
    <div class="modal" id="addModal">
        <div class="modal-content">
            <h3>Add Student</h3>
            <form method="POST">
                <input type="text" name="name" placeholder="Full Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="text" name="phone" placeholder="Phone">
                <select name="major" required>
                    <option>Computer Science</option>
                    <option>Engineering</option>
                    <option>Business</option>
                    <option>Mathematics</option>
                </select>
                <select name="year" required>
                    <option>Freshman</option>
                    <option>Sophomore</option>
                    <option>Junior</option>
                    <option>Senior</option>
                </select>
                <input type="number" step="0.01" name="gpa" placeholder="GPA (e.g. 3.5)" required>
                <input type="password" name="password" placeholder="Default Password" required>
                <div style="text-align:right;">
                    <button type="button" class="btn close-btn" onclick="closeModal('addModal')">Cancel</button>
                    <button type="submit" name="add_student" class="btn btn-add">Add</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal" id="editModal">
        <div class="modal-content">
            <h3>Edit Student</h3>
            <form method="POST">
                <input type="hidden" name="id" id="edit_id">
                <input type="text" name="name" id="edit_name" required>
                <input type="email" name="email" id="edit_email" required>
                <input type="text" name="phone" id="edit_phone">
                <select name="major" id="edit_major">
                    <option>Computer Science</option>
                    <option>Engineering</option>
                    <option>Business</option>
                    <option>Mathematics</option>
                </select>
                <select name="year" id="edit_year">
                    <option>Freshman</option>
                    <option>Sophomore</option>
                    <option>Junior</option>
                    <option>Senior</option>
                </select>
                <input type="number" step="0.01" name="gpa" id="edit_gpa" required>
                <div style="text-align:right;">
                    <button type="button" class="btn close-btn" onclick="closeModal('editModal')">Cancel</button>
                    <button type="submit" name="update_student" class="btn btn-edit">Update</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal" id="roomAddModal">
        <div class="modal-content">
            <h3>Add Room</h3>
            <form method="POST" action="admin_dashboard.php?tab=rooms">
                <input type="text" name="name" placeholder="Room Name" required>
                <input type="text" name="location" placeholder="Location">
                <input type="number" name="capacity" placeholder="Capacity" min="0">
                <label style="display:flex;align-items:center;gap:0.5rem;margin-bottom:1rem;font-size:0.9rem;">
                    <input type="checkbox" name="is_active" value="1" checked style="width:auto;">
                    Active
                </label>
                <div style="text-align:right;">
                    <button type="button" class="btn close-btn" onclick="closeModal('roomAddModal')">Cancel</button>
                    <button type="submit" name="create_room" class="btn btn-add">Add Room</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal" id="roomEditModal">
        <div class="modal-content">
            <h3>Edit Room</h3>
            <form method="POST" action="admin_dashboard.php?tab=rooms">
                <input type="hidden" name="id" id="edit_room_id">
                <input type="text" name="name" id="edit_room_name" required>
                <input type="text" name="location" id="edit_room_location">
                <input type="number" name="capacity" id="edit_room_capacity" min="0">
                <label style="display:flex;align-items:center;gap:0.5rem;margin-bottom:1rem;font-size:0.9rem;">
                    <input type="checkbox" name="is_active" id="edit_room_active" value="1" style="width:auto;">
                    Active
                </label>
                <div style="text-align:right;">
                    <button type="button" class="btn close-btn" onclick="closeModal('roomEditModal')">Cancel</button>
                    <button type="submit" name="update_room" class="btn btn-edit">Update Room</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('addModal').style.display = 'flex';
        }

        function openEditModal(student) {
            document.getElementById('editModal').style.display = 'flex';
            document.getElementById('edit_id').value = student.id;
            document.getElementById('edit_name').value = student.name;
            document.getElementById('edit_email').value = student.email;
            document.getElementById('edit_phone').value = student.phone;
            document.getElementById('edit_major').value = student.major;
            document.getElementById('edit_year').value = student.year;
            document.getElementById('edit_gpa').value = student.gpa;
        }

        function openRoomAddModal() {
            document.getElementById('roomAddModal').style.display = 'flex';
        }

        function openRoomEditModal(room) {
            document.getElementById('roomEditModal').style.display = 'flex';
            document.getElementById('edit_room_id').value = room.id;
            document.getElementById('edit_room_name').value = room.name;
            document.getElementById('edit_room_location').value = room.location;
            document.getElementById('edit_room_capacity').value = room.capacity;
            document.getElementById('edit_room_active').checked = room.is_active == 1;
        }

        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }
    </script>

</body>

</html>