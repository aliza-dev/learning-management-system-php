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

// Check if user has focal person rights
if (!$is_focal_person && $user_rights !== 'focal_person') {
    // Normal faculty have limited access
    $is_focal_person = false;
}

// Get counts
$students_count = $conn->query("SELECT COUNT(*) as count FROM students")->fetch_assoc()['count'];
$faculty_count = $conn->query("SELECT COUNT(*) as count FROM faculty")->fetch_assoc()['count'];
$courses_count = $conn->query("SELECT COUNT(*) as count FROM courses")->fetch_assoc()['count'];
$news_count = $conn->query("SELECT COUNT(*) as count FROM news WHERE posted_by = $faculty_id")->fetch_assoc()['count'];

// Add News Handler
if (isset($_POST['add_news'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $department_id = isset($_SESSION['department_id']) ? $_SESSION['department_id'] : null;

    $stmt = $conn->prepare("INSERT INTO news (title, content, department_id, posted_by, posted_by_type) VALUES (?, ?, ?, ?, 'faculty')");
    $stmt->bind_param("ssii", $title, $content, $department_id, $faculty_id);
    $stmt->execute();
    $stmt->close();
    header("Location: focal_dashboard.php?tab=news");
    exit();
}

// Delete News Handler
if (isset($_GET['delete_news'])) {
    $id = intval($_GET['delete_news']);
    $stmt = $conn->prepare("DELETE FROM news WHERE id = ? AND posted_by = ?");
    $stmt->bind_param("ii", $id, $faculty_id);
    $stmt->execute();
    $stmt->close();
    header("Location: focal_dashboard.php?tab=news");
    exit();
}

// Add Notice Handler
if (isset($_POST['add_notice'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $notice_date = $_POST['notice_date'];
    $department_id = isset($_SESSION['department_id']) ? $_SESSION['department_id'] : null;

    $stmt = $conn->prepare("INSERT INTO notices (title, content, notice_date, department_id, posted_by, posted_by_type) VALUES (?, ?, ?, ?, ?, 'faculty')");
    $stmt->bind_param("sssii", $title, $content, $notice_date, $department_id, $faculty_id);
    $stmt->execute();
    $stmt->close();
    header("Location: focal_dashboard.php?tab=notices");
    exit();
}

// Delete Notice Handler
if (isset($_GET['delete_notice'])) {
    $id = intval($_GET['delete_notice']);
    $stmt = $conn->prepare("DELETE FROM notices WHERE id = ? AND posted_by = ?");
    $stmt->bind_param("ii", $id, $faculty_id);
    $stmt->execute();
    $stmt->close();
    header("Location: focal_dashboard.php?tab=notices");
    exit();
}

// Add Event Handler
if (isset($_POST['add_event'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $event_date = $_POST['event_date'];
    $event_time = $_POST['event_time'];
    $location = trim($_POST['location']);
    $department_id = isset($_SESSION['department_id']) ? $_SESSION['department_id'] : null;

    $stmt = $conn->prepare("INSERT INTO events (title, description, event_date, event_time, location, department_id, posted_by, posted_by_type) VALUES (?, ?, ?, ?, ?, ?, ?, 'faculty')");
    $stmt->bind_param("sssssii", $title, $description, $event_date, $event_time, $location, $department_id, $faculty_id);
    $stmt->execute();
    $stmt->close();
    header("Location: focal_dashboard.php?tab=events");
    exit();
}

// Delete Event Handler
if (isset($_GET['delete_event'])) {
    $id = intval($_GET['delete_event']);
    $stmt = $conn->prepare("DELETE FROM events WHERE id = ? AND posted_by = ?");
    $stmt->bind_param("ii", $id, $faculty_id);
    $stmt->execute();
    $stmt->close();
    header("Location: focal_dashboard.php?tab=events");
    exit();
}

// Add Notification Handler
if (isset($_POST['add_notification'])) {
    $title = trim($_POST['title']);
    $message = trim($_POST['message']);
    $target_audience = $_POST['target_audience'];
    $department_id = isset($_SESSION['department_id']) ? $_SESSION['department_id'] : null;

    $stmt = $conn->prepare("INSERT INTO notifications (title, message, target_audience, department_id, posted_by, posted_by_type) VALUES (?, ?, ?, ?, ?, 'faculty')");
    $stmt->bind_param("sssii", $title, $message, $target_audience, $department_id, $faculty_id);
    $stmt->execute();
    $stmt->close();
    header("Location: focal_dashboard.php?tab=notifications");
    exit();
}

// Delete Notification Handler
if (isset($_GET['delete_notification'])) {
    $id = intval($_GET['delete_notification']);
    $stmt = $conn->prepare("DELETE FROM notifications WHERE id = ? AND posted_by = ?");
    $stmt->bind_param("ii", $id, $faculty_id);
    $stmt->execute();
    $stmt->close();
    header("Location: focal_dashboard.php?tab=notifications");
    exit();
}

// Get current tab
$current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'overview';

// Get data based on tab
$news = $conn->query("SELECT * FROM news WHERE posted_by = $faculty_id ORDER BY id DESC");
$notices = $conn->query("SELECT * FROM notices WHERE posted_by = $faculty_id ORDER BY id DESC");
$events = $conn->query("SELECT * FROM events WHERE posted_by = $faculty_id ORDER BY id DESC");
$notifications = $conn->query("SELECT * FROM notifications WHERE posted_by = $faculty_id ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Focal Person Dashboard - Learning Management System</title>
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
    </style>
</head>

<body>
    <header>
        <h2>👨‍🏫 Faculty Dashboard - Learning Management System</h2>
        <div class="header-right">
            <?php if ($is_focal_person): ?>
                <span class="focal-badge">⭐ Focal Person</span>
            <?php endif; ?>
            <a href="login.php?logout=1" class="logout-btn">Logout</a>
        </div>
    </header>

    <div class="container">
        <div class="welcome-section">
            <h3>Welcome back, <?= htmlspecialchars($faculty_name) ?>!</h3>
            <p><?= htmlspecialchars($faculty_email) ?></p>
        </div>

        <?php if (!$is_focal_person): ?>

        <?php endif; ?>

        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-icon">🎓</div>
                <div class="stat-info">
                    <h4><?= $students_count ?></h4>
                    <p>Total Students</p>
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
            <div class="stat-card">
                <div class="stat-icon">📰</div>
                <div class="stat-info">
                    <h4><?= $news_count ?></h4>
                    <p>My News Posts</p>
                </div>
            </div>
        </div>

        <?php if ($is_focal_person): ?>
            <div class="tabs">
                <a href="?tab=overview" class="tab-btn <?= $current_tab == 'overview' ? 'active' : '' ?>">📊 Overview</a>
                <a href="?tab=news" class="tab-btn <?= $current_tab == 'news' ? 'active' : '' ?>">📰 News</a>
                <a href="?tab=notices" class="tab-btn <?= $current_tab == 'notices' ? 'active' : '' ?>">📋 Notices</a>
                <a href="?tab=events" class="tab-btn <?= $current_tab == 'events' ? 'active' : '' ?>">🎉 Events</a>
                <a href="?tab=notifications" class="tab-btn <?= $current_tab == 'notifications' ? 'active' : '' ?>">🔔
                    Notifications</a>
            </div>

            <div class="content-section">
                <?php if ($current_tab == 'overview'): ?>
                    <h3>Overview</h3>
                    <p>Welcome to your Focal Person Dashboard! Use the tabs above to manage news, notices, events, and
                        notifications.</p>

                <?php elseif ($current_tab == 'news'): ?>
                    <h3>Manage News</h3>
                    <button class="btn btn-add" onclick="openModal('newsModal')">+ Add News</button>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Content</th>
                                <th>Posted On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($news && $news->num_rows > 0): ?>
                                <?php while ($row = $news->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $row['id'] ?></td>
                                        <td><?= htmlspecialchars($row['title']) ?></td>
                                        <td><?= htmlspecialchars(substr($row['content'], 0, 50)) ?>...</td>
                                        <td><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                                        <td class="actions">
                                            <a class="btn btn-delete" href="?delete_news=<?= $row['id'] ?>&tab=news"
                                                onclick="return confirm('Delete this news?')">Delete</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align:center;">No news found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                <?php elseif ($current_tab == 'notices'): ?>
                    <h3>Manage Notices</h3>
                    <button class="btn btn-add" onclick="openModal('noticeModal')">+ Add Notice</button>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Content</th>
                                <th>Notice Date</th>
                                <th>Posted On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($notices && $notices->num_rows > 0): ?>
                                <?php while ($row = $notices->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $row['id'] ?></td>
                                        <td><?= htmlspecialchars($row['title']) ?></td>
                                        <td><?= htmlspecialchars(substr($row['content'], 0, 50)) ?>...</td>
                                        <td><?= date('M d, Y', strtotime($row['notice_date'])) ?></td>
                                        <td><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
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

                <?php elseif ($current_tab == 'events'): ?>
                    <h3>Manage Events</h3>
                    <button class="btn btn-add" onclick="openModal('eventModal')">+ Add Event</button>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Date & Time</th>
                                <th>Location</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($events && $events->num_rows > 0): ?>
                                <?php while ($row = $events->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $row['id'] ?></td>
                                        <td><?= htmlspecialchars($row['title']) ?></td>
                                        <td><?= htmlspecialchars(substr($row['description'], 0, 40)) ?>...</td>
                                        <td><?= date('M d, Y', strtotime($row['event_date'])) ?>                 <?= $row['event_time'] ?></td>
                                        <td><?= htmlspecialchars($row['location']) ?></td>
                                        <td class="actions">
                                            <a class="btn btn-delete" href="?delete_event=<?= $row['id'] ?>&tab=events"
                                                onclick="return confirm('Delete this event?')">Delete</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align:center;">No events found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                <?php elseif ($current_tab == 'notifications'): ?>
                    <h3>Manage Notifications</h3>
                    <button class="btn btn-add" onclick="openModal('notificationModal')">+ Add Notification</button>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Message</th>
                                <th>Audience</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
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
                                        <td><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                                        <td class="actions">
                                            <a class="btn btn-delete" href="?delete_notification=<?= $row['id'] ?>&tab=notifications"
                                                onclick="return confirm('Delete this notification?')">Delete</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align:center;">No notifications found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

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

    <div class="modal" id="noticeModal">
        <div class="modal-content">
            <h3>Add Notice</h3>
            <form method="POST">
                <input type="text" name="title" placeholder="Notice Title" required>
                <textarea name="content" placeholder="Notice Content" required></textarea>
                <input type="date" name="notice_date" required>
                <div style="text-align:right;">
                    <button type="button" class="btn close-btn" onclick="closeModal('noticeModal')">Cancel</button>
                    <button type="submit" name="add_notice" class="btn btn-add">Add</button>
                </div>
            </form>
        </div>
    </div>


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
                <div style="text-align:right;">
                    <button type="button" class="btn close-btn"
                        onclick="closeModal('notificationModal')">Cancel</button>
                    <button type="submit" name="add_notification" class="btn btn-add">Add</button>
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
    </script>
</body>

</html>