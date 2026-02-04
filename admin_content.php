<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "university_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if admin is logged in
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$admin_name = isset($_SESSION['name']) ? $_SESSION['name'] : 'Admin';
$admin_email = isset($_SESSION['email']) ? $_SESSION['email'] : '';

// Get counts with error handling
$students_count = 0;
$departments_count = 0;
$faculty_count = 0;
$courses_count = 0;

$result = $conn->query("SELECT COUNT(*) as count FROM students");
if ($result) $students_count = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM departments");
if ($result) $departments_count = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM faculty");
if ($result) $faculty_count = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM courses");
if ($result) $courses_count = $result->fetch_assoc()['count'];

// Get instructors for dropdown
$instructors = $conn->query("SELECT id, first_name, last_name, CONCAT(first_name, ' ', last_name) as name FROM faculty ORDER BY first_name, last_name");

// Add Course Handler
if (isset($_POST['add_course'])) {
    $code = strtoupper(trim($_POST['code']));
    $name = trim($_POST['name']);
    $credits = intval($_POST['credits']);
    $schedule = trim($_POST['schedule']);
    $room = trim($_POST['room']);
    $instructor_id = intval($_POST['instructor_id']);
    $semester = trim($_POST['semester']);

    $stmt = $conn->prepare("INSERT INTO courses (code, name, credits, schedule, room, instructor_id, semester) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssissis", $code, $name, $credits, $schedule, $room, $instructor_id, $semester);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_content.php");
    exit();
}

// Delete Course Handler
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_content.php");
    exit();
}

// Update Course Handler
if (isset($_POST['update_course'])) {
    $id = intval($_POST['id']);
    $code = strtoupper(trim($_POST['code']));
    $name = trim($_POST['name']);
    $credits = intval($_POST['credits']);
    $schedule = trim($_POST['schedule']);
    $room = trim($_POST['room']);
    $instructor_id = intval($_POST['instructor_id']);
    $semester = trim($_POST['semester']);

    $stmt = $conn->prepare("UPDATE courses SET code=?, name=?, credits=?, schedule=?, room=?, instructor_id=?, semester=? WHERE id=?");
    $stmt->bind_param("ssissisi", $code, $name, $credits, $schedule, $room, $instructor_id, $semester, $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_content.php");
    exit();
}

// Get courses with instructor names
$courses = $conn->query("SELECT c.*, CONCAT(f.first_name, ' ', f.last_name) as instructor_name FROM courses c LEFT JOIN faculty f ON c.instructor_id = f.id ORDER BY c.id DESC");

// Check and add department_id column if it doesn't exist (with proper positioning)
$tables_config = [
    'news' => 'content',
    'notices' => 'notice_date',
    'events' => 'location',
    'notifications' => 'target_audience'
];

foreach ($tables_config as $table => $after_column) {
    $check = $conn->query("SHOW COLUMNS FROM `$table` LIKE 'department_id'");
    if ($check && $check->num_rows == 0) {
        // Add the column
        @$conn->query("ALTER TABLE `$table` ADD COLUMN `department_id` INT(11) NULL AFTER `$after_column`");
        // Try to add foreign key (may fail if constraint already exists or table doesn't exist)
        @$conn->query("ALTER TABLE `$table` ADD CONSTRAINT `fk_{$table}_dept` FOREIGN KEY (`department_id`) REFERENCES `departments`(`depart_id`) ON DELETE SET NULL");
    }
}

// Get departments for dropdown
$departments = $conn->query("SELECT * FROM departments ORDER BY name");

// Get current tab
$current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'courses';

// News Management
if (isset($_POST['add_news'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $department_id = !empty($_POST['department_id']) ? intval($_POST['department_id']) : null;
    
    // Check if department_id column exists
    $check = $conn->query("SHOW COLUMNS FROM news LIKE 'department_id'");
    if ($check && $check->num_rows > 0) {
        $stmt = $conn->prepare("INSERT INTO news (title, content, department_id, posted_by, posted_by_type) VALUES (?, ?, ?, ?, 'admin')");
        $admin_id = $_SESSION['user_id'];
        $stmt->bind_param("ssii", $title, $content, $department_id, $admin_id);
    } else {
        $stmt = $conn->prepare("INSERT INTO news (title, content, posted_by, posted_by_type) VALUES (?, ?, ?, 'admin')");
        $admin_id = $_SESSION['user_id'];
        $stmt->bind_param("ssi", $title, $content, $admin_id);
    }
    $stmt->execute();
    $stmt->close();
    header("Location: admin_content.php?tab=news");
    exit();
}

if (isset($_GET['delete_news'])) {
    $id = intval($_GET['delete_news']);
    $stmt = $conn->prepare("DELETE FROM news WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_content.php?tab=news");
    exit();
}

// Notice Management
if (isset($_POST['add_notice'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $notice_date = $_POST['notice_date'];
    $department_id = !empty($_POST['department_id']) ? intval($_POST['department_id']) : null;
    
    // Check if department_id column exists
    $check = $conn->query("SHOW COLUMNS FROM notices LIKE 'department_id'");
    if ($check && $check->num_rows > 0) {
        $stmt = $conn->prepare("INSERT INTO notices (title, content, notice_date, department_id, posted_by, posted_by_type) VALUES (?, ?, ?, ?, ?, 'admin')");
        $admin_id = $_SESSION['user_id'];
        $stmt->bind_param("sssii", $title, $content, $notice_date, $department_id, $admin_id);
    } else {
        $stmt = $conn->prepare("INSERT INTO notices (title, content, notice_date, posted_by, posted_by_type) VALUES (?, ?, ?, ?, 'admin')");
        $admin_id = $_SESSION['user_id'];
        $stmt->bind_param("sssi", $title, $content, $notice_date, $admin_id);
    }
    $stmt->execute();
    $stmt->close();
    header("Location: admin_content.php?tab=notices");
    exit();
}

if (isset($_GET['delete_notice'])) {
    $id = intval($_GET['delete_notice']);
    $stmt = $conn->prepare("DELETE FROM notices WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_content.php?tab=notices");
    exit();
}

// Event Management
if (isset($_POST['add_event'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $event_date = $_POST['event_date'];
    $event_time = $_POST['event_time'];
    $location = trim($_POST['location']);
    $department_id = !empty($_POST['department_id']) ? intval($_POST['department_id']) : null;
    
    // Check if department_id column exists
    $check = $conn->query("SHOW COLUMNS FROM events LIKE 'department_id'");
    if ($check && $check->num_rows > 0) {
        $stmt = $conn->prepare("INSERT INTO events (title, description, event_date, event_time, location, department_id, posted_by, posted_by_type) VALUES (?, ?, ?, ?, ?, ?, ?, 'admin')");
        $admin_id = $_SESSION['user_id'];
        $stmt->bind_param("sssssii", $title, $description, $event_date, $event_time, $location, $department_id, $admin_id);
    } else {
        $stmt = $conn->prepare("INSERT INTO events (title, description, event_date, event_time, location, posted_by, posted_by_type) VALUES (?, ?, ?, ?, ?, ?, 'admin')");
        $admin_id = $_SESSION['user_id'];
        $stmt->bind_param("sssssi", $title, $description, $event_date, $event_time, $location, $admin_id);
    }
    $stmt->execute();
    $stmt->close();
    header("Location: admin_content.php?tab=events");
    exit();
}

if (isset($_GET['delete_event'])) {
    $id = intval($_GET['delete_event']);
    $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_content.php?tab=events");
    exit();
}

// Notification Management
if (isset($_POST['add_notification'])) {
    $title = trim($_POST['title']);
    $message = trim($_POST['message']);
    $target_audience = $_POST['target_audience'];
    $department_id = !empty($_POST['department_id']) ? intval($_POST['department_id']) : null;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Check if department_id column exists
    $check = $conn->query("SHOW COLUMNS FROM notifications LIKE 'department_id'");
    if ($check && $check->num_rows > 0) {
        $stmt = $conn->prepare("INSERT INTO notifications (title, message, target_audience, department_id, is_active, posted_by, posted_by_type) VALUES (?, ?, ?, ?, ?, ?, 'admin')");
        $admin_id = $_SESSION['user_id'];
        $stmt->bind_param("sssiii", $title, $message, $target_audience, $department_id, $is_active, $admin_id);
    } else {
        $stmt = $conn->prepare("INSERT INTO notifications (title, message, target_audience, is_active, posted_by, posted_by_type) VALUES (?, ?, ?, ?, ?, 'admin')");
        $admin_id = $_SESSION['user_id'];
        $stmt->bind_param("sssii", $title, $message, $target_audience, $is_active, $admin_id);
    }
    $stmt->execute();
    $stmt->close();
    header("Location: admin_content.php?tab=notifications");
    exit();
}

if (isset($_GET['delete_notification'])) {
    $id = intval($_GET['delete_notification']);
    $stmt = $conn->prepare("DELETE FROM notifications WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_content.php?tab=notifications");
    exit();
}

// Get all content - check if department_id column exists first
$news_has_dept = false;
$notices_has_dept = false;
$events_has_dept = false;
$notifications_has_dept = false;

$check = $conn->query("SHOW COLUMNS FROM news LIKE 'department_id'");
if ($check && $check->num_rows > 0) $news_has_dept = true;

$check = $conn->query("SHOW COLUMNS FROM notices LIKE 'department_id'");
if ($check && $check->num_rows > 0) $notices_has_dept = true;

$check = $conn->query("SHOW COLUMNS FROM events LIKE 'department_id'");
if ($check && $check->num_rows > 0) $events_has_dept = true;

$check = $conn->query("SHOW COLUMNS FROM notifications LIKE 'department_id'");
if ($check && $check->num_rows > 0) $notifications_has_dept = true;

// Get all content with conditional department joins
if ($news_has_dept) {
    $all_news = $conn->query("SELECT n.*, d.name as dept_name FROM news n LEFT JOIN departments d ON n.department_id = d.depart_id ORDER BY n.created_at DESC");
} else {
    $all_news = $conn->query("SELECT n.*, 'All' as dept_name FROM news n ORDER BY n.created_at DESC");
}

if ($notices_has_dept) {
    $all_notices = $conn->query("SELECT n.*, d.name as dept_name FROM notices n LEFT JOIN departments d ON n.department_id = d.depart_id ORDER BY n.notice_date DESC");
} else {
    $all_notices = $conn->query("SELECT n.*, 'All' as dept_name FROM notices n ORDER BY n.notice_date DESC");
}

if ($events_has_dept) {
    $all_events = $conn->query("SELECT e.*, d.name as dept_name FROM events e LEFT JOIN departments d ON e.department_id = d.depart_id ORDER BY e.event_date DESC");
} else {
    $all_events = $conn->query("SELECT e.*, 'All' as dept_name FROM events e ORDER BY e.event_date DESC");
}

if ($notifications_has_dept) {
    $all_notifications = $conn->query("SELECT n.*, d.name as dept_name FROM notifications n LEFT JOIN departments d ON n.department_id = d.depart_id ORDER BY n.created_at DESC");
} else {
    $all_notifications = $conn->query("SELECT n.*, 'All' as dept_name FROM notifications n ORDER BY n.created_at DESC");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courses - Learning Management System</title>
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
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
            border-color: var(--accent-color);
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
            word-wrap: break-word;
            max-width: 200px;
        }

        td.actions {
            white-space: nowrap;
            max-width: none;
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
        .modal-content select,
        .modal-content textarea {
            width: 100%;
            padding: 1rem;
            margin-bottom: 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 0.95rem;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
        }

        .modal-content input:focus,
        .modal-content select:focus,
        .modal-content textarea:focus {
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
        }

        .actions .btn {
            white-space: nowrap;
            flex-shrink: 0;
        }

        .tabs {
            display: flex;
            gap: 0.75rem;
            margin-bottom: 2rem;
            background: white;
            padding: 1.5rem;
            border-radius: 20px;
            box-shadow: var(--shadow-md);
            flex-wrap: wrap;
        }

        .tab-btn {
            padding: 0.875rem 1.5rem;
            border: none;
            background: var(--bg-light);
            color: var(--text-light);
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.95rem;
            text-decoration: none;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }

        .tab-btn:hover {
            background: #e9ecef;
        }

        .tab-btn.active {
            background: var(--accent-color);
            color: var(--primary-color);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .modal-content textarea {
            min-height: 120px;
            resize: vertical;
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
        <h2>📚 Content Management - Learning Management System</h2>
        <div class="nav-links">
            <a href="admin_dashboard.php">Students</a>
            <a href="admin_departments.php">Departments</a>
            <a href="admin_faculty.php">Faculty</a>
            <a href="admin_content.php">Content</a>
            <a href="login.php?logout=1" class="logout-btn">Logout</a>
        </div>
    </header>

    <div class="container">
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

        <!-- Tabs -->
        <div class="tabs">
            <a href="?tab=courses" class="tab-btn <?= $current_tab == 'courses' ? 'active' : '' ?>">📚 Courses</a>
            <a href="?tab=news" class="tab-btn <?= $current_tab == 'news' ? 'active' : '' ?>">📰 News</a>
            <a href="?tab=notices" class="tab-btn <?= $current_tab == 'notices' ? 'active' : '' ?>">📋 Notices</a>
            <a href="?tab=events" class="tab-btn <?= $current_tab == 'events' ? 'active' : '' ?>">🎉 Events</a>
            <a href="?tab=notifications" class="tab-btn <?= $current_tab == 'notifications' ? 'active' : '' ?>">🔔 Notifications</a>
        </div>

        <!-- Courses Tab -->
        <div class="table-container tab-content <?= $current_tab == 'courses' ? 'active' : '' ?>">
            <h3>All Courses</h3>
            <button class="btn btn-add" onclick="openAddModal()">+ Add Course</button>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Code</th>
                        <th>Course Name</th>
                        <th>Credits</th>
                        <th>Schedule</th>
                        <th>Room</th>
                        <th>Instructor</th>
                        <th>Semester</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($courses && $courses->num_rows > 0): ?>
                        <?php while ($row = $courses->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><strong><?= htmlspecialchars($row['code']) ?></strong></td>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td><?= $row['credits'] ?></td>
                                <td><?= htmlspecialchars($row['schedule']) ?></td>
                                <td><?= htmlspecialchars($row['room']) ?></td>
                                <td><?= htmlspecialchars($row['instructor_name'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($row['semester']) ?></td>
                                <td class="actions">
                                    <button class="btn btn-edit" onclick='openEditModal(<?= json_encode($row) ?>)'>Edit</button>
                                    <a class="btn btn-delete" href="?delete=<?= $row['id'] ?>" 
                                        onclick="return confirm('Delete this course?')">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" style="text-align:center;">No courses found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- News Tab -->
        <div class="table-container tab-content <?= $current_tab == 'news' ? 'active' : '' ?>">
            <h3>News & Updates</h3>
            <button class="btn btn-add" onclick="openNewsModal()">+ Add News</button>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Content</th>
                        <th>Department</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($all_news && $all_news->num_rows > 0): ?>
                        <?php while ($row = $all_news->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= htmlspecialchars($row['title']) ?></td>
                                <td><?= htmlspecialchars(substr($row['content'], 0, 50)) ?>...</td>
                                <td><?= htmlspecialchars($row['dept_name'] ?? 'All') ?></td>
                                <td><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                                <td class="actions">
                                    <a class="btn btn-delete" href="?delete_news=<?= $row['id'] ?>&tab=news" 
                                        onclick="return confirm('Delete this news?')">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align:center;">No news found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Notices Tab -->
        <div class="table-container tab-content <?= $current_tab == 'notices' ? 'active' : '' ?>">
            <h3>Notice Board</h3>
            <button class="btn btn-add" onclick="openNoticeModal()">+ Add Notice</button>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Content</th>
                        <th>Department</th>
                        <th>Notice Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($all_notices && $all_notices->num_rows > 0): ?>
                        <?php while ($row = $all_notices->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= htmlspecialchars($row['title']) ?></td>
                                <td><?= htmlspecialchars(substr($row['content'], 0, 50)) ?>...</td>
                                <td><?= htmlspecialchars($row['dept_name'] ?? 'All') ?></td>
                                <td><?= date('M d, Y', strtotime($row['notice_date'])) ?></td>
                                <td class="actions">
                                    <a class="btn btn-delete" href="?delete_notice=<?= $row['id'] ?>&tab=notices" 
                                        onclick="return confirm('Delete this notice?')">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align:center;">No notices found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Events Tab -->
        <div class="table-container tab-content <?= $current_tab == 'events' ? 'active' : '' ?>">
            <h3>Events</h3>
            <button class="btn btn-add" onclick="openEventModal()">+ Add Event</button>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Department</th>
                        <th>Date & Time</th>
                        <th>Location</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($all_events && $all_events->num_rows > 0): ?>
                        <?php while ($row = $all_events->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= htmlspecialchars($row['title']) ?></td>
                                <td><?= htmlspecialchars(substr($row['description'], 0, 40)) ?>...</td>
                                <td><?= htmlspecialchars($row['dept_name'] ?? 'All') ?></td>
                                <td><?= date('M d, Y', strtotime($row['event_date'])) ?> <?= $row['event_time'] ? date('h:i A', strtotime($row['event_time'])) : '' ?></td>
                                <td><?= htmlspecialchars($row['location']) ?></td>
                                <td class="actions">
                                    <a class="btn btn-delete" href="?delete_event=<?= $row['id'] ?>&tab=events" 
                                        onclick="return confirm('Delete this event?')">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align:center;">No events found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Notifications Tab -->
        <div class="table-container tab-content <?= $current_tab == 'notifications' ? 'active' : '' ?>">
            <h3>Notifications</h3>
            <button class="btn btn-add" onclick="openNotificationModal()">+ Add Notification</button>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Message</th>
                        <th>Department</th>
                        <th>Audience</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($all_notifications && $all_notifications->num_rows > 0): ?>
                        <?php while ($row = $all_notifications->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= htmlspecialchars($row['title']) ?></td>
                                <td><?= htmlspecialchars(substr($row['message'], 0, 40)) ?>...</td>
                                <td><?= htmlspecialchars($row['dept_name'] ?? 'All') ?></td>
                                <td><?= ucfirst($row['target_audience']) ?></td>
                                <td><?= $row['is_active'] ? '✅ Active' : '❌ Inactive' ?></td>
                                <td><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                                <td class="actions">
                                    <a class="btn btn-delete" href="?delete_notification=<?= $row['id'] ?>&tab=notifications" 
                                        onclick="return confirm('Delete this notification?')">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align:center;">No notifications found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Modal -->
    <div class="modal" id="addModal">
        <div class="modal-content">
            <h3>Add New Course</h3>
            <form method="POST">
                <input type="text" name="code" placeholder="Course Code (e.g., CS 101)" required>
                <input type="text" name="name" placeholder="Course Name" required>
                <input type="number" name="credits" placeholder="Credits" min="1" max="6" required>
                <input type="text" name="schedule" placeholder="Schedule (e.g., Mon, Wed 10:00 AM)" required>
                <input type="text" name="room" placeholder="Room (e.g., Engineering Hall 204)" required>
                <select name="instructor_id" required>
                    <option value="">Select Instructor</option>
                    <?php
                    $instructors->data_seek(0);
                    while ($inst = $instructors->fetch_assoc()):
                        ?>
                        <option value="<?= $inst['id'] ?>"><?= htmlspecialchars($inst['name']) ?></option>
                    <?php endwhile; ?>
                </select>
                <input type="text" name="semester" placeholder="Semester (e.g., Fall 2024)" required>
                <div style="text-align:right;">
                    <button type="button" class="btn close-btn" onclick="closeModal('addModal')">Cancel</button>
                    <button type="submit" name="add_course" class="btn btn-add">Add</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal" id="editModal">
        <div class="modal-content">
            <h3>Edit Course</h3>
            <form method="POST">
                <input type="hidden" name="id" id="edit_id">
                <input type="text" name="code" id="edit_code" placeholder="Course Code" required>
                <input type="text" name="name" id="edit_name" placeholder="Course Name" required>
                <input type="number" name="credits" id="edit_credits" placeholder="Credits" min="1" max="6" required>
                <input type="text" name="schedule" id="edit_schedule" placeholder="Schedule" required>
                <input type="text" name="room" id="edit_room" placeholder="Room" required>
                <select name="instructor_id" id="edit_instructor_id" required>
                    <option value="">Select Instructor</option>
                    <?php
                    $instructors->data_seek(0);
                    while ($inst = $instructors->fetch_assoc()):
                        ?>
                        <option value="<?= $inst['id'] ?>"><?= htmlspecialchars($inst['name']) ?></option>
                    <?php endwhile; ?>
                </select>
                <input type="text" name="semester" id="edit_semester" placeholder="Semester" required>
                <div style="text-align:right;">
                    <button type="button" class="btn close-btn" onclick="closeModal('editModal')">Cancel</button>
                    <button type="submit" name="update_course" class="btn btn-edit">Update</button>
                </div>
            </form>
        </div>
    </div>

    <!-- News Modal -->
    <div class="modal" id="newsModal">
        <div class="modal-content">
            <h3>Add News</h3>
            <form method="POST">
                <input type="text" name="title" placeholder="News Title" required>
                <textarea name="content" placeholder="News Content" required></textarea>
                <select name="department_id">
                    <option value="">All Departments</option>
                    <?php
                    $departments->data_seek(0);
                    while ($dept = $departments->fetch_assoc()):
                        ?>
                        <option value="<?= $dept['depart_id'] ?>"><?= htmlspecialchars($dept['name']) ?></option>
                    <?php endwhile; ?>
                </select>
                <div style="text-align:right;">
                    <button type="button" class="btn close-btn" onclick="closeModal('newsModal')">Cancel</button>
                    <button type="submit" name="add_news" class="btn btn-add">Add</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Notice Modal -->
    <div class="modal" id="noticeModal">
        <div class="modal-content">
            <h3>Add Notice</h3>
            <form method="POST">
                <input type="text" name="title" placeholder="Notice Title" required>
                <textarea name="content" placeholder="Notice Content" required></textarea>
                <input type="date" name="notice_date" required>
                <select name="department_id">
                    <option value="">All Departments</option>
                    <?php
                    $departments->data_seek(0);
                    while ($dept = $departments->fetch_assoc()):
                        ?>
                        <option value="<?= $dept['depart_id'] ?>"><?= htmlspecialchars($dept['name']) ?></option>
                    <?php endwhile; ?>
                </select>
                <div style="text-align:right;">
                    <button type="button" class="btn close-btn" onclick="closeModal('noticeModal')">Cancel</button>
                    <button type="submit" name="add_notice" class="btn btn-add">Add</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Event Modal -->
    <div class="modal" id="eventModal">
        <div class="modal-content">
            <h3>Add Event</h3>
            <form method="POST">
                <input type="text" name="title" placeholder="Event Title" required>
                <textarea name="description" placeholder="Event Description" required></textarea>
                <input type="date" name="event_date" required>
                <input type="time" name="event_time" required>
                <input type="text" name="location" placeholder="Event Location" required>
                <select name="department_id">
                    <option value="">All Departments</option>
                    <?php
                    $departments->data_seek(0);
                    while ($dept = $departments->fetch_assoc()):
                        ?>
                        <option value="<?= $dept['depart_id'] ?>"><?= htmlspecialchars($dept['name']) ?></option>
                    <?php endwhile; ?>
                </select>
                <div style="text-align:right;">
                    <button type="button" class="btn close-btn" onclick="closeModal('eventModal')">Cancel</button>
                    <button type="submit" name="add_event" class="btn btn-add">Add</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Notification Modal -->
    <div class="modal" id="notificationModal">
        <div class="modal-content">
            <h3>Add Notification</h3>
            <form method="POST">
                <input type="text" name="title" placeholder="Notification Title" required>
                <textarea name="message" placeholder="Notification Message" required></textarea>
                <select name="target_audience" required>
                    <option value="">Select Audience</option>
                    <option value="all">All Users</option>
                    <option value="students">Students Only</option>
                    <option value="faculty">Faculty Only</option>
                </select>
                <select name="department_id">
                    <option value="">All Departments</option>
                    <?php
                    $departments->data_seek(0);
                    while ($dept = $departments->fetch_assoc()):
                        ?>
                        <option value="<?= $dept['depart_id'] ?>"><?= htmlspecialchars($dept['name']) ?></option>
                    <?php endwhile; ?>
                </select>
                <label style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                    <input type="checkbox" name="is_active" checked style="width: auto;">
                    <span>Active</span>
                </label>
                <div style="text-align:right;">
                    <button type="button" class="btn close-btn" onclick="closeModal('notificationModal')">Cancel</button>
                    <button type="submit" name="add_notification" class="btn btn-add">Add</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('addModal').style.display = 'flex';
        }

        function openEditModal(course) {
            document.getElementById('editModal').style.display = 'flex';
            document.getElementById('edit_id').value = course.id;
            document.getElementById('edit_code').value = course.code;
            document.getElementById('edit_name').value = course.name;
            document.getElementById('edit_credits').value = course.credits;
            document.getElementById('edit_schedule').value = course.schedule;
            document.getElementById('edit_room').value = course.room;
            document.getElementById('edit_instructor_id').value = course.instructor_id;
            document.getElementById('edit_semester').value = course.semester;
        }

        function openNewsModal() {
            document.getElementById('newsModal').style.display = 'flex';
        }

        function openNoticeModal() {
            document.getElementById('noticeModal').style.display = 'flex';
        }

        function openEventModal() {
            document.getElementById('eventModal').style.display = 'flex';
        }

        function openNotificationModal() {
            document.getElementById('notificationModal').style.display = 'flex';
        }

        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>

</html>