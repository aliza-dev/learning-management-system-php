<?php
session_start();
require_once 'db_connect.php';

// Get department ID from GET parameter
$dept_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($dept_id == 0) {
    header("Location: index.php");
    exit();
}

// Get department information
$dept_stmt = $conn->prepare("SELECT * FROM departments WHERE depart_id = ?");
$dept_stmt->bind_param("i", $dept_id);
$dept_stmt->execute();
$dept_result = $dept_stmt->get_result();

if ($dept_result->num_rows == 0) {
    header("Location: index.php");
    exit();
}

$department = $dept_result->fetch_assoc();
$dept_stmt->close();

// Get faculty members in this department
$faculty_query = $conn->prepare("SELECT * FROM faculty WHERE department_id = ? ORDER BY is_focal_person DESC, last_name ASC");
$faculty_query->bind_param("i", $dept_id);
$faculty_query->execute();
$faculty_result = $faculty_query->get_result();

// Get news for this department
$news_query = $conn->prepare("SELECT * FROM news WHERE department_id = ? ORDER BY created_at DESC LIMIT 5");
$news_query->bind_param("i", $dept_id);
$news_query->execute();
$news_result = $news_query->get_result();

// Get notices for this department
$notices_query = $conn->prepare("SELECT * FROM notices WHERE department_id = ? ORDER BY notice_date DESC LIMIT 5");
$notices_query->bind_param("i", $dept_id);
$notices_query->execute();
$notices_result = $notices_query->get_result();

// Get events for this department
$events_query = $conn->prepare("SELECT * FROM events WHERE department_id = ? AND event_date >= CURDATE() ORDER BY event_date ASC LIMIT 5");
$events_query->bind_param("i", $dept_id);
$events_query->execute();
$events_result = $events_query->get_result();

// Get notifications for this department
$notifications_query = $conn->prepare("SELECT * FROM notifications WHERE department_id = ? AND is_active = 1 ORDER BY created_at DESC LIMIT 5");
$notifications_query->bind_param("i", $dept_id);
$notifications_query->execute();
$notifications_result = $notifications_query->get_result();

// Get all departments for navbar
$all_departments = $conn->query("SELECT * FROM departments ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($department['name']) ?> - Learning Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #000000;
            --accent-color: #fbbf24;
            --text-dark: #000000;
            --text-light: #4b5563;
            --bg-light: #f9fafb;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-light);
        }

        /* Navbar */
        .navbar {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-family: 'Playfair Display', serif;
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--primary-color) !important;
        }

        .nav-link {
            color: var(--text-dark) !important;
            font-weight: 500;
            margin: 0 0.5rem;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            color: var(--accent-color) !important;
        }

        .dropdown-menu {
            border: none;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            padding: 0.5rem 0;
        }

        .dropdown-item {
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
        }

        .dropdown-item:hover {
            background: var(--primary-color);
            color: var(--accent-color) !important;
            padding-left: 2rem;
        }

        /* Hero Section */
        .dept-hero {
            background: linear-gradient(135deg, var(--primary-color), #1a1a1a);
            color: white;
            padding: 4rem 0;
            margin-bottom: 3rem;
        }

        .dept-hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .dept-hero .dept-code {
            font-size: 1.25rem;
            color: var(--accent-color);
            font-weight: 600;
            letter-spacing: 2px;
        }

        /* Content Sections */
        .content-section {
            margin-bottom: 3rem;
        }

        .section-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            transition: all 0.3s ease;
        }

        .section-card:hover {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            transform: translateY(-5px);
        }

        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 3px solid var(--accent-color);
        }

        .faculty-card {
            background: var(--bg-light);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-left: 4px solid var(--accent-color);
            transition: all 0.3s ease;
        }

        .faculty-card:hover {
            background: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .focal-badge {
            background: var(--accent-color);
            color: var(--primary-color);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-left: 0.5rem;
        }

        .news-item, .notice-item, .event-item, .notification-item {
            padding: 1rem;
            border-left: 3px solid var(--accent-color);
            margin-bottom: 1rem;
            background: var(--bg-light);
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .news-item:hover, .notice-item:hover, .event-item:hover, .notification-item:hover {
            background: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transform: translateX(5px);
        }

        .news-item h5, .notice-item h5, .event-item h5, .notification-item h5 {
            color: var(--text-dark);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .news-item p, .notice-item p, .event-item p, .notification-item p {
            color: var(--text-light);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .date-badge {
            background: var(--primary-color);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            display: inline-block;
            margin-top: 0.5rem;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--text-light);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }

        @media (max-width: 768px) {
            .dept-hero h1 {
                font-size: 2rem;
            }

            .section-card {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">Learning Management System</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="deptDropdown" role="button" data-bs-toggle="dropdown">
                            Departments
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="deptDropdown">
                            <?php if ($all_departments && $all_departments->num_rows > 0): ?>
                                <?php while ($dept = $all_departments->fetch_assoc()): ?>
                                    <li><a class="dropdown-item" href="dpt.php?id=<?= $dept['depart_id'] ?>"><?= htmlspecialchars($dept['name']) ?></a></li>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Department Hero Section -->
    <div class="dept-hero">
        <div class="container">
            <div class="dept-code"><?= htmlspecialchars($department['depart_code']) ?></div>
            <h1><?= htmlspecialchars($department['name']) ?></h1>
            <?php if ($department['description']): ?>
                <p class="lead"><?= htmlspecialchars($department['description']) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="container">
        <!-- Faculty Members Section -->
        <div class="content-section">
            <div class="section-card">
                <h2 class="section-title">👨‍🏫 Faculty Members</h2>
                <?php if ($faculty_result->num_rows > 0): ?>
                    <?php while ($faculty = $faculty_result->fetch_assoc()): ?>
                        <div class="faculty-card">
                            <h5>
                                <?= htmlspecialchars($faculty['first_name'] . ' ' . $faculty['last_name']) ?>
                                <?php if ($faculty['is_focal_person'] == 1): ?>
                                    <span class="focal-badge">⭐ Focal Person</span>
                                <?php endif; ?>
                            </h5>
                            <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($faculty['email']) ?></p>
                            <?php if ($faculty['phone']): ?>
                                <p class="mb-1"><strong>Phone:</strong> <?= htmlspecialchars($faculty['phone']) ?></p>
                            <?php endif; ?>
                            <?php if ($faculty['bio']): ?>
                                <p class="mt-2 mb-0"><small><?= htmlspecialchars($faculty['bio']) ?></small></p>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i>👨‍🏫</i>
                        <p>No faculty members found in this department.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- News Section -->
        <div class="content-section">
            <div class="section-card">
                <h2 class="section-title">📰 News & Updates</h2>
                <?php if ($news_result->num_rows > 0): ?>
                    <?php while ($news = $news_result->fetch_assoc()): ?>
                        <div class="news-item">
                            <h5><?= htmlspecialchars($news['title']) ?></h5>
                            <p><?= htmlspecialchars(substr($news['content'], 0, 200)) ?><?= strlen($news['content']) > 200 ? '...' : '' ?></p>
                            <span class="date-badge"><?= date('M d, Y', strtotime($news['created_at'])) ?></span>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i>📰</i>
                        <p>No news available for this department.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Notice Board Section -->
        <div class="content-section">
            <div class="section-card">
                <h2 class="section-title">📋 Notice Board</h2>
                <?php if ($notices_result->num_rows > 0): ?>
                    <?php while ($notice = $notices_result->fetch_assoc()): ?>
                        <div class="notice-item">
                            <h5><?= htmlspecialchars($notice['title']) ?></h5>
                            <p><?= htmlspecialchars(substr($notice['content'], 0, 200)) ?><?= strlen($notice['content']) > 200 ? '...' : '' ?></p>
                            <span class="date-badge">Notice Date: <?= date('M d, Y', strtotime($notice['notice_date'])) ?></span>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i>📋</i>
                        <p>No notices available for this department.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Events Section -->
        <div class="content-section">
            <div class="section-card">
                <h2 class="section-title">🎉 Upcoming Events</h2>
                <?php if ($events_result->num_rows > 0): ?>
                    <?php while ($event = $events_result->fetch_assoc()): ?>
                        <div class="event-item">
                            <h5><?= htmlspecialchars($event['title']) ?></h5>
                            <p><?= htmlspecialchars(substr($event['description'], 0, 200)) ?><?= strlen($event['description']) > 200 ? '...' : '' ?></p>
                            <p class="mb-1"><strong>Date:</strong> <?= date('M d, Y', strtotime($event['event_date'])) ?></p>
                            <?php if ($event['event_time']): ?>
                                <p class="mb-1"><strong>Time:</strong> <?= date('h:i A', strtotime($event['event_time'])) ?></p>
                            <?php endif; ?>
                            <?php if ($event['location']): ?>
                                <p class="mb-1"><strong>Location:</strong> <?= htmlspecialchars($event['location']) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i>🎉</i>
                        <p>No upcoming events for this department.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Notifications Section -->
        <div class="content-section">
            <div class="section-card">
                <h2 class="section-title">🔔 Notifications</h2>
                <?php if ($notifications_result->num_rows > 0): ?>
                    <?php while ($notification = $notifications_result->fetch_assoc()): ?>
                        <div class="notification-item">
                            <h5><?= htmlspecialchars($notification['title']) ?></h5>
                            <p><?= htmlspecialchars(substr($notification['message'], 0, 200)) ?><?= strlen($notification['message']) > 200 ? '...' : '' ?></p>
                            <span class="date-badge"><?= date('M d, Y', strtotime($notification['created_at'])) ?></span>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i>🔔</i>
                        <p>No notifications available for this department.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-light text-center py-4 mt-5">
        <div class="container">
            <p>&copy; <?php echo date("Y"); ?> Learning Management System. All rights reserved.</p>
            <p class="text-warning mb-0">Empowering Minds, Shaping Futures</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

