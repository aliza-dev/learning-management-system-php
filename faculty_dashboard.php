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

require_once __DIR__ . '/room_booking_functions.php';

// Check if faculty is logged in
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'faculty') {
    header("Location: login.php");
    exit();
}

$faculty_id = $_SESSION['user_id'];
$faculty_name = $_SESSION['name'];
$faculty_email = $_SESSION['email'];
$is_focal_person = isset($_SESSION['is_focal_person']) ? $_SESSION['is_focal_person'] : 0;
$user_rights = isset($_SESSION['user_rights']) ? $_SESSION['user_rights'] : 'normal';
$department_id = isset($_SESSION['department_id']) ? $_SESSION['department_id'] : null;

// Get faculty and department info
$faculty_query = $conn->prepare("SELECT f.*, d.name as dept_name FROM faculty f LEFT JOIN departments d ON f.department_id = d.depart_id WHERE f.id = ?");
$faculty_query->bind_param("i", $faculty_id);
$faculty_query->execute();
$faculty_result = $faculty_query->get_result();
$faculty_data = $faculty_result->fetch_assoc();
$faculty_query->close();

// Update session with latest data
if ($faculty_data) {
    $is_focal_person = $faculty_data['is_focal_person'];
    $user_rights = $faculty_data['user_rights'] ?? 'normal';
    $department_id = $faculty_data['department_id'];
    $faculty_name = $faculty_data['first_name'] . ' ' . $faculty_data['last_name'];
    $dept_name = $faculty_data['dept_name'] ?? 'N/A';
}

// Check if user has focal person rights
$can_manage = ($is_focal_person == 1 || $user_rights === 'focal_person');

// ========== CRUD HANDLERS (Only for focal_person) ==========

// Add News Handler
if (isset($_POST['add_news']) && $can_manage) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $dept_id = $department_id;

    $stmt = $conn->prepare("INSERT INTO news (title, content, department_id, posted_by, posted_by_type) VALUES (?, ?, ?, ?, 'faculty')");
    $stmt->bind_param("ssii", $title, $content, $dept_id, $faculty_id);
    $stmt->execute();
    $stmt->close();
    header("Location: faculty_dashboard.php?tab=news&msg=added");
    exit();
}

// Update News Handler
if (isset($_POST['update_news']) && $can_manage) {
    $id = intval($_POST['id']);
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    // Focal person can edit any news in their department
    $stmt = $conn->prepare("UPDATE news SET title=?, content=? WHERE id=? AND department_id=?");
    $stmt->bind_param("ssii", $title, $content, $id, $department_id);
    $stmt->execute();
    $stmt->close();
    header("Location: faculty_dashboard.php?tab=news&msg=updated");
    exit();
}

// Delete News Handler
if (isset($_GET['delete_news']) && $can_manage) {
    $id = intval($_GET['delete_news']);
    // Focal person can delete any news in their department
    $stmt = $conn->prepare("DELETE FROM news WHERE id = ? AND department_id = ?");
    $stmt->bind_param("ii", $id, $department_id);
    $stmt->execute();
    $stmt->close();
    header("Location: faculty_dashboard.php?tab=news&msg=deleted");
    exit();
}

// Add Event Handler
if (isset($_POST['add_event']) && $can_manage) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $event_date = $_POST['event_date'];
    $event_time = $_POST['event_time'];
    $location = trim($_POST['location']);
    $dept_id = $department_id;

    $stmt = $conn->prepare("INSERT INTO events (title, description, event_date, event_time, location, department_id, posted_by, posted_by_type) VALUES (?, ?, ?, ?, ?, ?, ?, 'faculty')");
    $stmt->bind_param("sssssii", $title, $description, $event_date, $event_time, $location, $dept_id, $faculty_id);
    $stmt->execute();
    $stmt->close();
    header("Location: faculty_dashboard.php?tab=events&msg=added");
    exit();
}

// Update Event Handler
if (isset($_POST['update_event']) && $can_manage) {
    $id = intval($_POST['id']);
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $event_date = $_POST['event_date'];
    $event_time = $_POST['event_time'];
    $location = trim($_POST['location']);

    // Focal person can edit any event in their department
    $stmt = $conn->prepare("UPDATE events SET title=?, description=?, event_date=?, event_time=?, location=? WHERE id=? AND department_id=?");
    $stmt->bind_param("sssssii", $title, $description, $event_date, $event_time, $location, $id, $department_id);
    $stmt->execute();
    $stmt->close();
    header("Location: faculty_dashboard.php?tab=events&msg=updated");
    exit();
}

// Delete Event Handler
if (isset($_GET['delete_event']) && $can_manage) {
    $id = intval($_GET['delete_event']);
    // Focal person can delete any event in their department
    $stmt = $conn->prepare("DELETE FROM events WHERE id = ? AND department_id = ?");
    $stmt->bind_param("ii", $id, $department_id);
    $stmt->execute();
    $stmt->close();
    header("Location: faculty_dashboard.php?tab=events&msg=deleted");
    exit();
}

// Add Notification Handler
if (isset($_POST['add_notification']) && $can_manage) {
    $title = trim($_POST['title']);
    $message = trim($_POST['message']);
    $target_audience = $_POST['target_audience'];
    $dept_id = $department_id;

    $stmt = $conn->prepare("INSERT INTO notifications (title, message, target_audience, department_id, posted_by, posted_by_type) VALUES (?, ?, ?, ?, ?, 'faculty')");
    $stmt->bind_param("sssii", $title, $message, $target_audience, $dept_id, $faculty_id);
    $stmt->execute();
    $stmt->close();
    header("Location: faculty_dashboard.php?tab=notifications&msg=added");
    exit();
}

// Update Notification Handler
if (isset($_POST['update_notification']) && $can_manage) {
    $id = intval($_POST['id']);
    $title = trim($_POST['title']);
    $message = trim($_POST['message']);
    $target_audience = $_POST['target_audience'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Focal person can edit any notification in their department
    $stmt = $conn->prepare("UPDATE notifications SET title=?, message=?, target_audience=?, is_active=? WHERE id=? AND department_id=?");
    $stmt->bind_param("sssiii", $title, $message, $target_audience, $is_active, $id, $department_id);
    $stmt->execute();
    $stmt->close();
    header("Location: faculty_dashboard.php?tab=notifications&msg=updated");
    exit();
}

// Delete Notification Handler
if (isset($_GET['delete_notification']) && $can_manage) {
    $id = intval($_GET['delete_notification']);
    // Focal person can delete any notification in their department
    $stmt = $conn->prepare("DELETE FROM notifications WHERE id = ? AND department_id = ?");
    $stmt->bind_param("ii", $id, $department_id);
    $stmt->execute();
    $stmt->close();
    header("Location: faculty_dashboard.php?tab=notifications&msg=deleted");
    exit();
}

// Add Update (Notice) Handler
if (isset($_POST['add_update']) && $can_manage) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $notice_date = $_POST['notice_date'];
    $dept_id = $department_id;

    $stmt = $conn->prepare("INSERT INTO notices (title, content, notice_date, department_id, posted_by, posted_by_type) VALUES (?, ?, ?, ?, ?, 'faculty')");
    $stmt->bind_param("sssii", $title, $content, $notice_date, $dept_id, $faculty_id);
    $stmt->execute();
    $stmt->close();
    header("Location: faculty_dashboard.php?tab=updates&msg=added");
    exit();
}

// Update Notice Handler
if (isset($_POST['update_update']) && $can_manage) {
    $id = intval($_POST['id']);
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $notice_date = $_POST['notice_date'];

    // Focal person can edit any notice/update in their department
    $stmt = $conn->prepare("UPDATE notices SET title=?, content=?, notice_date=? WHERE id=? AND department_id=?");
    $stmt->bind_param("sssii", $title, $content, $notice_date, $id, $department_id);
    $stmt->execute();
    $stmt->close();
    header("Location: faculty_dashboard.php?tab=updates&msg=updated");
    exit();
}

// Delete Update (Notice) Handler
if (isset($_GET['delete_update']) && $can_manage) {
    $id = intval($_GET['delete_update']);
    // Focal person can delete any notice/update in their department
    $stmt = $conn->prepare("DELETE FROM notices WHERE id = ? AND department_id = ?");
    $stmt->bind_param("ii", $id, $department_id);
    $stmt->execute();
    $stmt->close();
    header("Location: faculty_dashboard.php?tab=updates&msg=deleted");
    exit();
}

// Get current tab
$current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'overview';

if ($current_tab === 'room_booking' && isset($_POST['create_booking']) && $can_manage) {
    $room_id = isset($_POST['room_id']) ? (int)$_POST['room_id'] : 0;
    $booking_date = isset($_POST['booking_date']) ? $_POST['booking_date'] : '';
    $time_slot = isset($_POST['time_slot']) ? $_POST['time_slot'] : '';
    $event_title = isset($_POST['event_title']) ? trim($_POST['event_title']) : '';
    $num_persons = isset($_POST['num_persons']) ? (int)$_POST['num_persons'] : 0;
    $redirect_base = 'faculty_dashboard.php?tab=room_booking&room_id=' . $room_id . '&booking_date=' . urlencode($booking_date);
    
    if ($room_id > 0 && $booking_date !== '' && $time_slot !== '' && $event_title !== '' && $num_persons > 0) {
        // STRICT backend validation before creating booking
        $validation = rb_validate_booking_request($conn, $room_id, $booking_date, $time_slot, $is_focal_person);
        
        if (!$validation['valid']) {
            header("Location: " . $redirect_base . "&msg=slot_blocked&error=" . urlencode($validation['error']));
            exit();
        }
        
        // Create booking with focal person flag
        $booking_id = rb_create_booking($conn, $faculty_id, $department_id, $room_id, $booking_date, $time_slot, $event_title, $num_persons, $is_focal_person);
        
        if ($booking_id !== false) {
            header("Location: " . $redirect_base . "&msg=booking_created");
            exit();
        } else {
            header("Location: " . $redirect_base . "&msg=slot_unavailable");
            exit();
        }
    } else {
        header("Location: " . $redirect_base . "&msg=invalid_booking");
        exit();
    }
}

// Get data - show department-specific content AND general content (NULL department_id)
// Show items where department_id matches faculty's department OR department_id IS NULL (for all departments)
$news_query = "SELECT n.*, d.name as dept_name FROM news n LEFT JOIN departments d ON n.department_id = d.depart_id WHERE (n.department_id = ? OR n.department_id IS NULL) ORDER BY n.created_at DESC";
$events_query = "SELECT e.*, d.name as dept_name FROM events e LEFT JOIN departments d ON e.department_id = d.depart_id WHERE (e.department_id = ? OR e.department_id IS NULL) ORDER BY e.event_date DESC";
$notifications_query = "SELECT n.*, d.name as dept_name FROM notifications n LEFT JOIN departments d ON n.department_id = d.depart_id WHERE (n.department_id = ? OR n.department_id IS NULL) ORDER BY n.created_at DESC";
$updates_query = "SELECT n.*, d.name as dept_name FROM notices n LEFT JOIN departments d ON n.department_id = d.depart_id WHERE (n.department_id = ? OR n.department_id IS NULL) ORDER BY n.notice_date DESC";

$stmt = $conn->prepare($news_query);
$stmt->bind_param("i", $department_id);
$stmt->execute();
$news = $stmt->get_result();
$stmt->close();

$stmt = $conn->prepare($events_query);
$stmt->bind_param("i", $department_id);
$stmt->execute();
$events = $stmt->get_result();
$stmt->close();

$stmt = $conn->prepare($notifications_query);
$stmt->bind_param("i", $department_id);
$stmt->execute();
$notifications = $stmt->get_result();
$stmt->close();

$stmt = $conn->prepare($updates_query);
$stmt->bind_param("i", $department_id);
$stmt->execute();
$updates = $stmt->get_result();
$stmt->close();

// Get counts - include NULL department_id items
$news_count_query = $conn->prepare("SELECT COUNT(*) as count FROM news WHERE (department_id = ? OR department_id IS NULL)");
$news_count_query->bind_param("i", $department_id);
$news_count_query->execute();
$news_count = $news_count_query->get_result()->fetch_assoc()['count'];
$news_count_query->close();

$events_count_query = $conn->prepare("SELECT COUNT(*) as count FROM events WHERE (department_id = ? OR department_id IS NULL)");
$events_count_query->bind_param("i", $department_id);
$events_count_query->execute();
$events_count = $events_count_query->get_result()->fetch_assoc()['count'];
$events_count_query->close();

$notifications_count_query = $conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE (department_id = ? OR department_id IS NULL)");
$notifications_count_query->bind_param("i", $department_id);
$notifications_count_query->execute();
$notifications_count = $notifications_count_query->get_result()->fetch_assoc()['count'];
$notifications_count_query->close();

$updates_count_query = $conn->prepare("SELECT COUNT(*) as count FROM notices WHERE (department_id = ? OR department_id IS NULL)");
$updates_count_query->bind_param("i", $department_id);
$updates_count_query->execute();
$updates_count = $updates_count_query->get_result()->fetch_assoc()['count'];
$updates_count_query->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Dashboard - Learning Management System</title>
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
            padding: 1.5rem 3rem;
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
            font-size: 1.75rem;
            font-weight: 700;
        }

        .header-right {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .focal-badge {
            background: var(--accent-color);
            padding: 0.75rem 1.5rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            border: 2px solid var(--accent-color);
            color: var(--primary-color);
        }

        .logout-btn {
            background: rgba(220, 53, 69, 0.9);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s;
        }

        .logout-btn:hover {
            background: #dc3545;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 30px;
        }

        .welcome-section {
            background: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .welcome-section h3 {
            color: #333;
            font-size: 24px;
            margin-bottom: 5px;
        }

        .welcome-section p {
            color: #666;
            font-size: 14px;
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
            font-size: 32px;
            color: #333;
            margin-bottom: 5px;
        }

        .stat-info p {
            color: #666;
            font-size: 14px;
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
        }

        .tab-btn:hover {
            background: #e9ecef;
        }

        .tab-btn.active {
            background: var(--accent-color);
            color: var(--primary-color);
        }

        .content-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .content-section h3 {
            margin-bottom: 20px;
            color: #333;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
            transition: 0.3s;
        }

        .btn-add {
            background: var(--accent-color);
            color: var(--primary-color);
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(251, 191, 36, 0.3);
        }

        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(251, 191, 36, 0.4);
            background: var(--accent-light);
        }

        .btn-edit {
            background: #28a745;
            color: white;
            padding: 7px 14px;
            font-size: 13px;
        }

        .btn-delete {
            background: #dc3545;
            color: white;
            padding: 7px 14px;
            font-size: 13px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid #e0e0e0;
            text-align: left;
        }

        th {
            background: var(--primary-color);
            color: var(--accent-color);
        }

        tr:hover {
            background: #f8f9fa;
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
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: var(--shadow-xl);
            animation: fadeInUp 0.3s ease;
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
            margin-bottom: 15px;
            text-align: center;
            color: #333;
        }

        .modal-content input,
        .modal-content select,
        .modal-content textarea {
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
        .modal-content select:focus,
        .modal-content textarea:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 4px rgba(251, 191, 36, 0.2);
        }

        .modal-content textarea {
            min-height: 100px;
            resize: vertical;
        }

        .close-btn {
            background: #ccc;
            margin-right: 8px;
        }

        .actions {
            display: flex;
            gap: 8px;
        }

        .warning-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .warning-box h4 {
            color: #856404;
            margin-bottom: 5px;
        }

        .warning-box p {
            color: #856404;
            font-size: 14px;
        }

        .view-only-notice {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            color: #0d47a1;
        }

        .success-msg {
            background: #d4edda;
            border-left: 4px solid #28a745;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            color: #155724;
        }
    </style>
</head>

<body>
    <header>
        <h2>👨‍🏫 Faculty Dashboard - Learning Management System</h2>
        <div class="header-right">
            <?php if ($can_manage): ?>
                <span class="focal-badge">⭐ Focal Person</span>
            <?php endif; ?>
            <a href="login.php?logout=1" class="logout-btn">Logout</a>
        </div>
    </header>

    <div class="container">
        <div class="welcome-section">
            <h3>Welcome back, <?= htmlspecialchars($faculty_name) ?>!</h3>
            <?php if ($can_manage): ?>
                <p style="color: var(--text-dark); font-weight: 600; margin-top: 0.5rem; font-size: 1.1rem;">
                    <span style="color: var(--accent-color);">⭐ Focal Person</span> - Department: <?= htmlspecialchars($dept_name) ?>
                </p>
            <?php endif; ?>
            <p>Email: <?= htmlspecialchars($faculty_email) ?> | Department: <?= htmlspecialchars($dept_name) ?></p>
            <?php if (!$can_manage): ?>
                <p style="color: #666; margin-top: 10px;">You have view-only access. Contact your department focal person for content management.</p>
            <?php endif; ?>
        </div>

        <?php if (isset($_GET['msg'])): ?>
            <div class="success-msg">
                <?php
                $msg = $_GET['msg'];
                if ($msg == 'added') echo 'Item added successfully!';
                elseif ($msg == 'updated') echo 'Item updated successfully!';
                elseif ($msg == 'deleted') echo 'Item deleted successfully!';
                ?>
            </div>
        <?php endif; ?>

        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-icon">📰</div>
                <div class="stat-info">
                    <h4><?= $news_count ?></h4>
                    <p>News</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🎉</div>
                <div class="stat-info">
                    <h4><?= $events_count ?></h4>
                    <p>Events</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🔔</div>
                <div class="stat-info">
                    <h4><?= $notifications_count ?></h4>
                    <p>Notifications</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📋</div>
                <div class="stat-info">
                    <h4><?= $updates_count ?></h4>
                    <p>Updates</p>
                </div>
            </div>
        </div>

        <div class="tabs">
            <a href="?tab=overview" class="tab-btn <?= $current_tab == 'overview' ? 'active' : '' ?>">📊 Overview</a>
            <a href="?tab=news" class="tab-btn <?= $current_tab == 'news' ? 'active' : '' ?>">📰 News</a>
            <a href="?tab=events" class="tab-btn <?= $current_tab == 'events' ? 'active' : '' ?>">🎉 Events</a>
            <a href="?tab=notifications" class="tab-btn <?= $current_tab == 'notifications' ? 'active' : '' ?>">🔔 Notifications</a>
            <a href="?tab=updates" class="tab-btn <?= $current_tab == 'updates' ? 'active' : '' ?>">📋 Updates</a>
            <?php if ($can_manage): ?>
                <a href="?tab=room_booking" class="tab-btn <?= $current_tab == 'room_booking' ? 'active' : '' ?>">🏢 Room Booking</a>
            <?php endif; ?>
        </div>

        <div class="content-section">
            <?php if ($current_tab == 'overview'): ?>
                <h3>Overview</h3>
                <p>Welcome to your Faculty Dashboard! Here you can view News, Events, Notifications, and Updates for your department.</p>
                <?php if ($can_manage): ?>
                    <p style="margin-top: 10px;">As a <strong>Focal Person</strong>, you can create, edit, and delete content for your department.</p>
                <?php else: ?>
                    <p style="margin-top: 10px;">You have <strong>view-only</strong> access. Use the tabs above to browse content.</p>
                <?php endif; ?>

            <?php elseif ($current_tab == 'news'): ?>
                <h3>News</h3>
                <?php if (!$can_manage): ?>
                    <div class="view-only-notice">View-only mode: You can view news but cannot create, edit, or delete.</div>
                <?php endif; ?>
                <?php if ($can_manage): ?>
                    <button class="btn btn-add" onclick="openModal('newsModal')">+ Add News</button>
                <?php endif; ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Content</th>
                            <th>Department</th>
                            <th>Posted On</th>
                            <?php if ($can_manage): ?>
                                <th>Actions</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($news && $news->num_rows > 0): ?>
                            <?php while ($row = $news->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['id'] ?></td>
                                    <td><?= htmlspecialchars($row['title']) ?></td>
                                    <td><?= htmlspecialchars(substr($row['content'], 0, 50)) ?>...</td>
                                    <td><?= htmlspecialchars($row['dept_name'] ?? 'N/A') ?></td>
                                    <td><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                                    <?php if ($can_manage): ?>
                                        <td class="actions">
                                            <button class="btn btn-edit" onclick='openEditNewsModal(<?= json_encode($row) ?>)'>Edit</button>
                                            <a class="btn btn-delete" href="?delete_news=<?= $row['id'] ?>&tab=news"
                                                onclick="return confirm('Delete this news?')">Delete</a>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?= $can_manage ? '6' : '5' ?>" style="text-align:center;">No news found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

            <?php elseif ($current_tab == 'events'): ?>
                <h3>Events</h3>
                <?php if (!$can_manage): ?>
                    <div class="view-only-notice">View-only mode: You can view events but cannot create, edit, or delete.</div>
                <?php endif; ?>
                <?php if ($can_manage): ?>
                    <button class="btn btn-add" onclick="openModal('eventModal')">+ Add Event</button>
                <?php endif; ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Date & Time</th>
                            <th>Location</th>
                            <th>Department</th>
                            <?php if ($can_manage): ?>
                                <th>Actions</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($events && $events->num_rows > 0): ?>
                            <?php while ($row = $events->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['id'] ?></td>
                                    <td><?= htmlspecialchars($row['title']) ?></td>
                                    <td><?= htmlspecialchars(substr($row['description'], 0, 40)) ?>...</td>
                                    <td><?= date('M d, Y', strtotime($row['event_date'])) ?> <?= $row['event_time'] ?></td>
                                    <td><?= htmlspecialchars($row['location']) ?></td>
                                    <td><?= htmlspecialchars($row['dept_name'] ?? 'N/A') ?></td>
                                    <?php if ($can_manage): ?>
                                        <td class="actions">
                                            <button class="btn btn-edit" onclick='openEditEventModal(<?= json_encode($row) ?>)'>Edit</button>
                                            <a class="btn btn-delete" href="?delete_event=<?= $row['id'] ?>&tab=events"
                                                onclick="return confirm('Delete this event?')">Delete</a>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?= $can_manage ? '7' : '6' ?>" style="text-align:center;">No events found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

            <?php elseif ($current_tab == 'notifications'): ?>
                <h3>Notifications</h3>
                <?php if (!$can_manage): ?>
                    <div class="view-only-notice">View-only mode: You can view notifications but cannot create, edit, or delete.</div>
                <?php endif; ?>
                <?php if ($can_manage): ?>
                    <button class="btn btn-add" onclick="openModal('notificationModal')">+ Add Notification</button>
                <?php endif; ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Message</th>
                            <th>Audience</th>
                            <th>Status</th>
                            <th>Department</th>
                            <th>Created</th>
                            <?php if ($can_manage): ?>
                                <th>Actions</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($notifications && $notifications->num_rows > 0): ?>
                            <?php while ($row = $notifications->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['id'] ?></td>
                                    <td><?= htmlspecialchars($row['title']) ?></td>
                                    <td><?= htmlspecialchars(substr($row['message'], 0, 40)) ?>...</td>
                                    <td><?= ucfirst($row['target_audience']) ?></td>
                                    <td><?= $row['is_active'] ? '✅ Active' : '❌ Inactive' ?></td>
                                    <td><?= htmlspecialchars($row['dept_name'] ?? 'N/A') ?></td>
                                    <td><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                                    <?php if ($can_manage): ?>
                                        <td class="actions">
                                            <button class="btn btn-edit" onclick='openEditNotificationModal(<?= json_encode($row) ?>)'>Edit</button>
                                            <a class="btn btn-delete" href="?delete_notification=<?= $row['id'] ?>&tab=notifications"
                                                onclick="return confirm('Delete this notification?')">Delete</a>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?= $can_manage ? '8' : '7' ?>" style="text-align:center;">No notifications found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

            <?php elseif ($current_tab == 'updates'): ?>
                <h3>Updates</h3>
                <?php if (!$can_manage): ?>
                    <div class="view-only-notice">View-only mode: You can view updates but cannot create, edit, or delete.</div>
                <?php endif; ?>
                <?php if ($can_manage): ?>
                    <button class="btn btn-add" onclick="openModal('updateModal')">+ Add Update</button>
                <?php endif; ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Content</th>
                            <th>Update Date</th>
                            <th>Department</th>
                            <th>Posted On</th>
                            <?php if ($can_manage): ?>
                                <th>Actions</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($updates && $updates->num_rows > 0): ?>
                            <?php while ($row = $updates->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['id'] ?></td>
                                    <td><?= htmlspecialchars($row['title']) ?></td>
                                    <td><?= htmlspecialchars(substr($row['content'], 0, 50)) ?>...</td>
                                    <td><?= date('M d, Y', strtotime($row['notice_date'])) ?></td>
                                    <td><?= htmlspecialchars($row['dept_name'] ?? 'N/A') ?></td>
                                    <td><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                                    <?php if ($can_manage): ?>
                                        <td class="actions">
                                            <button class="btn btn-edit" onclick='openEditUpdateModal(<?= json_encode($row) ?>)'>Edit</button>
                                            <a class="btn btn-delete" href="?delete_update=<?= $row['id'] ?>&tab=updates"
                                                onclick="return confirm('Delete this update?')">Delete</a>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?= $can_manage ? '7' : '6' ?>" style="text-align:center;">No updates found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            <?php elseif ($current_tab == 'room_booking' && $can_manage): ?>
                <?php
                $rooms = rb_get_rooms($conn);
                $selected_room_id = isset($_GET['room_id']) ? (int)$_GET['room_id'] : (count($rooms) > 0 ? (int)$rooms[0]['id'] : 0);
                $selected_date = isset($_GET['booking_date']) ? $_GET['booking_date'] : date('Y-m-d');
                $calendar_year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
                $calendar_month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');
                $time_slots = rb_get_time_slots();
                // Get slot statuses with full status information
                $slot_statuses = $selected_room_id > 0 ? rb_get_slot_statuses($conn, $selected_room_id, $selected_date) : [];
                $faculty_bookings = rb_get_faculty_bookings($conn, $faculty_id);
                $calendar_data = $selected_room_id > 0 ? rb_get_calendar_data($conn, $selected_room_id, $calendar_year, $calendar_month) : [];
                ?>
                <h3>Room Booking</h3>
                <div style="margin-bottom: 1rem; font-size: 0.9rem;">
                    <span style="display:inline-block;width:14px;height:14px;background:#22c55e;border-radius:3px;margin-right:4px;"></span> Available
                    <span style="display:inline-block;width:14px;height:14px;background:#ef4444;border-radius:3px;margin-left:12px;margin-right:4px;"></span> Already Booked (Approved)
                    <span style="display:inline-block;width:14px;height:14px;background:#eab308;border-radius:3px;margin-left:12px;margin-right:4px;"></span> Pending Approval
                    <span style="display:inline-block;width:14px;height:14px;background:#f97316;border-radius:3px;margin-left:12px;margin-right:4px;"></span> Rejected (Can Re-book)
                </div>
                <form method="get" style="margin-bottom: 1rem;display:flex;flex-wrap:wrap;gap:0.75rem;align-items:flex-end;">
                    <input type="hidden" name="tab" value="room_booking">
                    <div>
                        <label style="font-size:0.9rem;display:block;margin-bottom:0.25rem;">Room</label>
                        <select name="room_id" style="padding:0.5rem 0.75rem;border-radius:8px;border:1px solid #e5e7eb;min-width:200px;">
                            <?php foreach ($rooms as $room): ?>
                                <option value="<?= $room['id'] ?>" <?= (int)$room['id'] === $selected_room_id ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($room['name']) ?><?= $room['location'] ? ' - ' . htmlspecialchars($room['location']) : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label style="font-size:0.9rem;display:block;margin-bottom:0.25rem;">Date</label>
                        <input type="date" name="booking_date" value="<?= htmlspecialchars($selected_date) ?>" style="padding:0.5rem 0.75rem;border-radius:8px;border:1px solid #e5e7eb;">
                    </div>
                    <div>
                        <label style="font-size:0.9rem;display:block;margin-bottom:0.25rem;">Calendar Month</label>
                        <input type="month" name="month_picker" value="<?= $calendar_year . '-' . str_pad($calendar_month, 2, '0', STR_PAD_LEFT) ?>" onchange="if(this.value){var p=this.value.split('-');document.getElementById('rb_year').value=p[0];document.getElementById('rb_month').value=parseInt(p[1],10);}">
                        <input type="hidden" id="rb_year" name="year" value="<?= $calendar_year ?>">
                        <input type="hidden" id="rb_month" name="month" value="<?= $calendar_month ?>">
                    </div>
                    <div>
                        <button type="submit" class="btn btn-add">Filter</button>
                    </div>
                </form>
                <?php if (isset($_GET['msg'])): ?>
                    <div class="<?= $_GET['msg'] === 'booking_created' ? 'success-msg' : 'error-msg' ?>" style="margin-bottom:1rem;padding:0.75rem 1rem;border-radius:8px;<?= $_GET['msg'] === 'booking_created' ? 'background:#f0fdf4;color:#16a34a;border-left:4px solid #16a34a;' : 'background:#fef2f2;color:#dc2626;border-left:4px solid #dc2626;' ?>">
                        <?php
                        $m = $_GET['msg'];
                        if ($m === 'booking_created') {
                            echo '✅ Booking request submitted and marked as Pending.';
                        } elseif ($m === 'slot_unavailable') {
                            echo '❌ Selected slot is not available.';
                        } elseif ($m === 'slot_blocked') {
                            $error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : 'Slot is blocked';
                            echo '❌ ' . $error;
                        } elseif ($m === 'invalid_booking') {
                            echo '❌ Invalid booking data. Please fill all required fields.';
                        }
                        ?>
                    </div>
                <?php endif; ?>
                <div style="display:grid;grid-template-columns:minmax(0,1.3fr) minmax(0,1fr);gap:1.5rem;align-items:flex-start;">
                    <div style="background:#f9fafb;border-radius:16px;padding:1.25rem;">
                        <h4 style="margin-bottom:0.75rem;font-size:1.1rem;">Select Time Slot</h4>
                        <form method="post">
                            <input type="hidden" name="tab" value="room_booking">
                            <input type="hidden" name="room_id" value="<?= $selected_room_id ?>">
                            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:0.75rem;margin-bottom:1rem;">
                                <?php foreach ($time_slots as $slot): ?>
                                    <?php
                                    // Get slot status using strict validation
                                    $slot_status = isset($slot_statuses[$slot]) ? $slot_statuses[$slot] : null;
                                    $slot_check = rb_can_book_slot($conn, $selected_room_id, $selected_date, $slot, $is_focal_person);
                                    
                                    // Determine display properties based on status
                                    $can_book = $slot_check['can_book'];
                                    $status_label = $slot_check['reason'];
                                    
                                    // Set colors and styles based on status
                                    if ($slot_status === 'Approved') {
                                        $bg_color = '#fee2e2';
                                        $border_color = '#ef4444';
                                        $text_color = '#991b1b';
                                        $label_text = 'Already Booked';
                                    } elseif ($slot_status === 'Pending') {
                                        $bg_color = '#fef9c3';
                                        $border_color = '#eab308';
                                        $text_color = '#854d0e';
                                        $label_text = 'Pending Approval';
                                    } elseif ($slot_status === 'Rejected') {
                                        $bg_color = '#fff7ed';
                                        $border_color = '#f97316';
                                        $text_color = '#9a3412';
                                        $label_text = 'Rejected';
                                    } else {
                                        $bg_color = '#ecfdf3';
                                        $border_color = '#22c55e';
                                        $text_color = '#166534';
                                        $label_text = 'Available';
                                    }
                                    ?>
                                    <div style="display:flex;flex-direction:column;gap:0.25rem;">
                                        <label style="display:flex;align-items:center;gap:0.35rem;padding:0.5rem 0.6rem;border-radius:10px;border:1px solid <?= $border_color ?>;background:<?= $bg_color ?>;font-size:0.85rem;cursor:<?= $can_book ? 'pointer' : 'not-allowed' ?>;opacity:<?= $can_book ? '1' : '0.7' ?>;">
                                            <input type="radio" name="time_slot" value="<?= htmlspecialchars($slot) ?>" <?= $can_book ? '' : 'disabled' ?> style="width:auto;" required>
                                            <span style="color:<?= $text_color ?>;font-weight:500;"><?= htmlspecialchars($slot) ?></span>
                                        </label>
                                        <span style="font-size:0.7rem;color:<?= $text_color ?>;padding-left:0.5rem;font-weight:500;"><?= htmlspecialchars($label_text) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div style="display:flex;flex-direction:column;gap:0.5rem;margin-bottom:1rem;">
                                <div>
                                    <label style="font-size:0.9rem;display:block;margin-bottom:0.25rem;">Booking Date</label>
                                    <input type="date" name="booking_date" value="<?= htmlspecialchars($selected_date) ?>" required style="width:100%;padding:0.6rem 0.75rem;border-radius:10px;border:1px solid #e5e7eb;">
                                </div>
                                <div>
                                    <label style="font-size:0.9rem;display:block;margin-bottom:0.25rem;">Event Title</label>
                                    <input type="text" name="event_title" required style="width:100%;padding:0.6rem 0.75rem;border-radius:10px;border:1px solid #e5e7eb;">
                                </div>
                                <div>
                                    <label style="font-size:0.9rem;display:block;margin-bottom:0.25rem;">Number of Persons</label>
                                    <input type="number" name="num_persons" min="1" required style="width:100%;padding:0.6rem 0.75rem;border-radius:10px;border:1px solid #e5e7eb;">
                                </div>
                            </div>
                            <button type="submit" name="create_booking" class="btn btn-add">Submit Booking Request</button>
                        </form>
                    </div>
                    <div style="background:#f9fafb;border-radius:16px;padding:1.25rem;overflow-x:auto;">
                        <h4 style="margin-bottom:0.75rem;font-size:1.1rem;">
                            Calendar
                            <?php if ($selected_room_id > 0): ?>
                                <?php
                                $room_label = '';
                                foreach ($rooms as $room) {
                                    if ((int)$room['id'] === $selected_room_id) {
                                        $room_label = $room['name'];
                                        break;
                                    }
                                }
                                ?>
                                - <?= htmlspecialchars($room_label) ?>
                            <?php endif; ?>
                        </h4>
                        <?php
                        $first_day = mktime(0, 0, 0, $calendar_month, 1, $calendar_year);
                        $days_in_month = (int)date('t', $first_day);
                        $start_weekday = (int)date('N', $first_day);
                        $weekdays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                        ?>
                        <table style="width:100%;border-collapse:collapse;font-size:0.85rem;">
                            <thead>
                                <tr>
                                    <?php foreach ($weekdays as $wd): ?>
                                        <th style="padding:0.4rem;border-bottom:1px solid #e5e7eb;background:#111827;color:#fbbf24;"><?= $wd ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $day = 1;
                                $cell = 1;
                                while ($day <= $days_in_month) {
                                    echo '<tr>';
                                    for ($i = 1; $i <= 7; $i++, $cell++) {
                                        if ($cell < $start_weekday || $day > $days_in_month) {
                                            echo '<td style="padding:0.4rem;border-bottom:1px solid #f3f4f6;background:#f9fafb;"></td>';
                                        } else {
                                            $status_counts = isset($calendar_data[$day]) ? $calendar_data[$day] : ['Pending' => 0, 'Approved' => 0, 'Rejected' => 0];
                                            $bg = '#fefce8';
                                            if ($status_counts['Approved'] > 0) {
                                                $bg = '#dcfce7';
                                            } elseif ($status_counts['Rejected'] > 0 && $status_counts['Approved'] === 0) {
                                                $bg = '#fee2e2';
                                            } elseif ($status_counts['Pending'] > 0) {
                                                $bg = '#fef9c3';
                                            }
                                            echo '<td style="padding:0.4rem;border-bottom:1px solid #f3f4f6;background:' . $bg . ';vertical-align:top;min-width:36px;">';
                                            echo '<div style="font-weight:600;margin-bottom:0.15rem;font-size:0.85rem;">' . $day . '</div>';
                                            if ($status_counts['Approved'] > 0) {
                                                echo '<div style="color:#15803d;font-size:0.7rem;">' . $status_counts['Approved'] . ' approved</div>';
                                            }
                                            if ($status_counts['Pending'] > 0) {
                                                echo '<div style="color:#a16207;font-size:0.7rem;">' . $status_counts['Pending'] . ' pending</div>';
                                            }
                                            if ($status_counts['Rejected'] > 0) {
                                                echo '<div style="color:#b91c1c;font-size:0.7rem;">' . $status_counts['Rejected'] . ' rejected</div>';
                                            }
                                            echo '</td>';
                                            $day++;
                                        }
                                    }
                                    echo '</tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div style="margin-top:1.75rem;">
                    <h4 style="margin-bottom:0.75rem;font-size:1.1rem;">My Booking Requests</h4>
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Room</th>
                                <th>Date</th>
                                <th>Time Slot</th>
                                <th>Event</th>
                                <th>Persons</th>
                                <th>Status</th>
                                <th>PDF</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($faculty_bookings) > 0): ?>
                                <?php foreach ($faculty_bookings as $b): ?>
                                    <?php
                                    $pdf_path = rb_get_booking_pdf_path($conn, (int)$b['id']);
                                    ?>
                                    <tr>
                                        <td><?= $b['id'] ?></td>
                                        <td><?= htmlspecialchars($b['room_name']) ?></td>
                                        <td><?= htmlspecialchars($b['booking_date']) ?></td>
                                        <td><?= htmlspecialchars($b['time_slot']) ?></td>
                                        <td><?= htmlspecialchars($b['event_title']) ?></td>
                                        <td><?= (int)$b['num_persons'] ?></td>
                                        <td><?= htmlspecialchars($b['status']) ?></td>
                                        <td>
                                            <?php if ($pdf_path): ?>
                                                <a href="<?= htmlspecialchars($pdf_path) ?>" target="_blank">Open PDF</a>
                                            <?php else: ?>
                                                <a href="generate_booking_pdf.php?id=<?= $b['id'] ?>" target="_blank">Generate PDF</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" style="text-align:center;">No bookings yet</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- News Modals -->
    <div class="modal" id="newsModal">
        <div class="modal-content">
            <h3>Add News</h3>
            <form method="POST">
                <input type="text" name="title" placeholder="News Title" required>
                <textarea name="content" placeholder="News Content" required></textarea>
                <div style="text-align:right;">
                    <button type="button" class="btn close-btn" onclick="closeModal('newsModal')">Cancel</button>
                    <button type="submit" name="add_news" class="btn btn-add">Add</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal" id="editNewsModal">
        <div class="modal-content">
            <h3>Edit News</h3>
            <form method="POST">
                <input type="hidden" name="id" id="edit_news_id">
                <input type="text" name="title" id="edit_news_title" required>
                <textarea name="content" id="edit_news_content" required></textarea>
                <div style="text-align:right;">
                    <button type="button" class="btn close-btn" onclick="closeModal('editNewsModal')">Cancel</button>
                    <button type="submit" name="update_news" class="btn btn-edit">Update</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Event Modals -->
    <div class="modal" id="eventModal">
        <div class="modal-content">
            <h3>Add Event</h3>
            <form method="POST">
                <input type="text" name="title" placeholder="Event Title" required>
                <textarea name="description" placeholder="Event Description" required></textarea>
                <input type="date" name="event_date" required>
                <input type="time" name="event_time" required>
                <input type="text" name="location" placeholder="Event Location" required>
                <div style="text-align:right;">
                    <button type="button" class="btn close-btn" onclick="closeModal('eventModal')">Cancel</button>
                    <button type="submit" name="add_event" class="btn btn-add">Add</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal" id="editEventModal">
        <div class="modal-content">
            <h3>Edit Event</h3>
            <form method="POST">
                <input type="hidden" name="id" id="edit_event_id">
                <input type="text" name="title" id="edit_event_title" required>
                <textarea name="description" id="edit_event_description" required></textarea>
                <input type="date" name="event_date" id="edit_event_date" required>
                <input type="time" name="event_time" id="edit_event_time" required>
                <input type="text" name="location" id="edit_event_location" required>
                <div style="text-align:right;">
                    <button type="button" class="btn close-btn" onclick="closeModal('editEventModal')">Cancel</button>
                    <button type="submit" name="update_event" class="btn btn-edit">Update</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Notification Modals -->
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
                    <option value="department">Department Only</option>
                </select>
                <div style="text-align:right;">
                    <button type="button" class="btn close-btn" onclick="closeModal('notificationModal')">Cancel</button>
                    <button type="submit" name="add_notification" class="btn btn-add">Add</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal" id="editNotificationModal">
        <div class="modal-content">
            <h3>Edit Notification</h3>
            <form method="POST">
                <input type="hidden" name="id" id="edit_notification_id">
                <input type="text" name="title" id="edit_notification_title" required>
                <textarea name="message" id="edit_notification_message" required></textarea>
                <select name="target_audience" id="edit_notification_audience" required>
                    <option value="all">All Users</option>
                    <option value="students">Students Only</option>
                    <option value="faculty">Faculty Only</option>
                    <option value="department">Department Only</option>
                </select>
                <label style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                    <input type="checkbox" name="is_active" id="edit_notification_active" style="width: auto;">
                    <span>Active</span>
                </label>
                <div style="text-align:right;">
                    <button type="button" class="btn close-btn" onclick="closeModal('editNotificationModal')">Cancel</button>
                    <button type="submit" name="update_notification" class="btn btn-edit">Update</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Update Modals -->
    <div class="modal" id="updateModal">
        <div class="modal-content">
            <h3>Add Update</h3>
            <form method="POST">
                <input type="text" name="title" placeholder="Update Title" required>
                <textarea name="content" placeholder="Update Content" required></textarea>
                <input type="date" name="notice_date" required>
                <div style="text-align:right;">
                    <button type="button" class="btn close-btn" onclick="closeModal('updateModal')">Cancel</button>
                    <button type="submit" name="add_update" class="btn btn-add">Add</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal" id="editUpdateModal">
        <div class="modal-content">
            <h3>Edit Update</h3>
            <form method="POST">
                <input type="hidden" name="id" id="edit_update_id">
                <input type="text" name="title" id="edit_update_title" required>
                <textarea name="content" id="edit_update_content" required></textarea>
                <input type="date" name="notice_date" id="edit_update_date" required>
                <div style="text-align:right;">
                    <button type="button" class="btn close-btn" onclick="closeModal('editUpdateModal')">Cancel</button>
                    <button type="submit" name="update_update" class="btn btn-edit">Update</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(id) {
            document.getElementById(id).style.display = 'flex';
        }

        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }

        function openEditNewsModal(news) {
            document.getElementById('editNewsModal').style.display = 'flex';
            document.getElementById('edit_news_id').value = news.id;
            document.getElementById('edit_news_title').value = news.title;
            document.getElementById('edit_news_content').value = news.content;
        }

        function openEditEventModal(event) {
            document.getElementById('editEventModal').style.display = 'flex';
            document.getElementById('edit_event_id').value = event.id;
            document.getElementById('edit_event_title').value = event.title;
            document.getElementById('edit_event_description').value = event.description;
            document.getElementById('edit_event_date').value = event.event_date;
            document.getElementById('edit_event_time').value = event.event_time;
            document.getElementById('edit_event_location').value = event.location;
        }

        function openEditNotificationModal(notification) {
            document.getElementById('editNotificationModal').style.display = 'flex';
            document.getElementById('edit_notification_id').value = notification.id;
            document.getElementById('edit_notification_title').value = notification.title;
            document.getElementById('edit_notification_message').value = notification.message;
            document.getElementById('edit_notification_audience').value = notification.target_audience;
            document.getElementById('edit_notification_active').checked = notification.is_active == 1;
        }

        function openEditUpdateModal(update) {
            document.getElementById('editUpdateModal').style.display = 'flex';
            document.getElementById('edit_update_id').value = update.id;
            document.getElementById('edit_update_title').value = update.title;
            document.getElementById('edit_update_content').value = update.content;
            document.getElementById('edit_update_date').value = update.notice_date;
        }
    </script>
</body>

</html>

