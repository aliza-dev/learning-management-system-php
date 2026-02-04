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

// Get counts
$students_count = $conn->query("SELECT COUNT(*) as count FROM students")->fetch_assoc()['count'];
$departments_count = $conn->query("SELECT COUNT(*) as count FROM departments")->fetch_assoc()['count'];
$faculty_count = $conn->query("SELECT COUNT(*) as count FROM faculty")->fetch_assoc()['count'];
$courses_count = $conn->query("SELECT COUNT(*) as count FROM courses")->fetch_assoc()['count'];

// Add Department Handler
if (isset($_POST['add_department'])) {
    $code = strtoupper(trim($_POST['code']));
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);

    $stmt = $conn->prepare("INSERT INTO departments (depart_code, name, description) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $code, $name, $description);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_departments.php");
    exit();
}

// Delete Department Handler
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM departments WHERE depart_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_departments.php");
    exit();
}

// Update Department Handler
if (isset($_POST['update_department'])) {
    $id = intval($_POST['id']);
    $code = strtoupper(trim($_POST['code']));
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);

    $stmt = $conn->prepare("UPDATE departments SET depart_code=?, name=?, description=? WHERE depart_id=?");
    $stmt->bind_param("sssi", $code, $name, $description, $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_departments.php");
    exit();
}

// Get departments
$departments = $conn->query("SELECT * FROM departments ORDER BY depart_id DESC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Departments - Learning Management System</title>
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
            background: var(--accent-color);
            color: var(--primary-color);
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
            max-width: 600px;
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
            margin-bottom: 1.5rem;
            text-align: center;
            color: var(--text-dark);
            font-size: 1.5rem;
            font-weight: 700;
        }

        .modal-content input,
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
        .modal-content textarea:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 4px rgba(251, 191, 36, 0.2);
        }

        .modal-content textarea {
            min-height: 120px;
            resize: vertical;
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
        <h2>🏢 Departments Management - Learning Management System</h2>
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

        <div class="table-container">
            <h3>All Departments</h3>
            <button class="btn btn-add" onclick="openAddModal()">+ Add Department</button>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Code</th>
                        <th>Department Name</th>
                        <th>Description</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($departments && $departments->num_rows > 0): ?>
                        <?php while ($row = $departments->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['depart_id'] ?></td>
                                <td><strong><?= htmlspecialchars($row['depart_code']) ?></strong></td>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td><?= htmlspecialchars($row['description']) ?></td>
                                <td><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                                <td class="actions">
                                    <button class="btn btn-edit" onclick='openEditModal(<?= json_encode($row) ?>)'>Edit</button>
                                    <a class="btn btn-delete" href="?delete=<?= $row['depart_id'] ?>"
                                        onclick="return confirm('Delete this department?')">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align:center;">No departments found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Modal -->
    <div class="modal" id="addModal">
        <div class="modal-content">
            <h3>Add New Department</h3>
            <form method="POST">
                <input type="text" name="code" placeholder="Department Code (e.g., CS, ENG)" required>
                <input type="text" name="name" placeholder="Department Name" required>
                <textarea name="description" placeholder="Description"></textarea>
                <div style="text-align:right;">
                    <button type="button" class="btn close-btn" onclick="closeModal('addModal')">Cancel</button>
                    <button type="submit" name="add_department" class="btn btn-add">Add</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal" id="editModal">
        <div class="modal-content">
            <h3>Edit Department</h3>
            <form method="POST">
                <input type="hidden" name="id" id="edit_id">
                <input type="text" name="code" id="edit_code" placeholder="Department Code" required>
                <input type="text" name="name" id="edit_name" placeholder="Department Name" required>
                <textarea name="description" id="edit_description" placeholder="Description"></textarea>
                <div style="text-align:right;">
                    <button type="button" class="btn close-btn" onclick="closeModal('editModal')">Cancel</button>
                    <button type="submit" name="update_department" class="btn btn-edit">Update</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('addModal').style.display = 'flex';
        }

        function openEditModal(dept) {
            document.getElementById('editModal').style.display = 'flex';
            document.getElementById('edit_id').value = dept.depart_id;
            document.getElementById('edit_code').value = dept.depart_code;
            document.getElementById('edit_name').value = dept.name;
            document.getElementById('edit_description').value = dept.description;
        }

        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }
    </script>
</body>

</html>