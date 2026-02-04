<?php
session_start();

class Database
{
    private $host = "localhost";
    private $db_name = "university_db";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection()
    {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Connection Error: " . $e->getMessage();
        }
        return $this->conn;
    }
}

class Student
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getStudentById($student_id)
    {
        $query = "SELECT * FROM students WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$student_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getEnrolledCourses($student_id)
    {
        $query = "SELECT c.*, e.grade, e.attendance, e.progress, CONCAT(i.first_name, ' ', i.last_name) as instructor_name
                  FROM enrollments e
                  JOIN courses c ON e.course_id = c.id
                  LEFT JOIN faculty i ON c.instructor_id = i.id
                  WHERE e.student_id = ?
                  ORDER BY c.code";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$student_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStats($student_id)
    {
        $query = "SELECT 
                    COUNT(*) as total_courses,
                    COALESCE(SUM(c.credits), 0) as total_credits,
                    COALESCE(AVG(e.attendance), 0) as avg_attendance
                  FROM enrollments e
                  JOIN courses c ON e.course_id = c.id
                  WHERE e.student_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$student_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

$database = new Database();
$db = $database->getConnection();
$student = new Student($db);

$student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1);
$student_data = $student->getStudentById($student_id);

if (!$student_data) {
    die("Student not found!");
}

// Handle Profile Updates
$message = '';
$messageType = '';
$current_tab = isset($_GET['tab']) ? $_GET['tab'] : (isset($_POST['tab']) ? $_POST['tab'] : 'dashboard');

// Update Email
if (isset($_POST['update_email'])) {
    $new_email = trim($_POST['email']);
    if (filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        // Check if email already exists
        $check_query = "SELECT id FROM students WHERE email = ? AND id != ?";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->execute([$new_email, $student_id]);
        if ($check_stmt->fetch()) {
            $_SESSION['profile_message'] = 'Email already exists!';
            $_SESSION['profile_message_type'] = 'error';
        } else {
            $update_query = "UPDATE students SET email = ? WHERE id = ?";
            $update_stmt = $db->prepare($update_query);
            if ($update_stmt->execute([$new_email, $student_id])) {
                $_SESSION['profile_message'] = 'Email updated successfully!';
                $_SESSION['profile_message_type'] = 'success';
            } else {
                $_SESSION['profile_message'] = 'Failed to update email!';
                $_SESSION['profile_message_type'] = 'error';
            }
        }
    } else {
        $_SESSION['profile_message'] = 'Invalid email format!';
        $_SESSION['profile_message_type'] = 'error';
    }
    header("Location: student_dashboard.php?student_id=$student_id&tab=profile");
    exit();
}

// Upload Profile Picture
if (isset($_POST['upload_profile']) && isset($_FILES['profile_picture'])) {
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_name = time() . "_" . basename($_FILES["profile_picture"]["name"]);
    $target_file = $target_dir . $file_name;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Validate image
    $check = getimagesize($_FILES["profile_picture"]["tmp_name"]);
    if ($check === false) {
        $_SESSION['profile_message'] = 'File is not an image!';
        $_SESSION['profile_message_type'] = 'error';
    } elseif ($_FILES["profile_picture"]["size"] > 5000000) { // 5MB
        $_SESSION['profile_message'] = 'File is too large! Maximum size is 5MB.';
        $_SESSION['profile_message_type'] = 'error';
    } elseif (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
        $_SESSION['profile_message'] = 'Only JPG, JPEG, PNG, GIF & WEBP files are allowed!';
        $_SESSION['profile_message_type'] = 'error';
    } else {
        // Delete old profile picture if exists
        if (!empty($student_data['profile']) && file_exists($target_dir . $student_data['profile'])) {
            @unlink($target_dir . $student_data['profile']);
        }
        
        if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
            $update_query = "UPDATE students SET profile = ? WHERE id = ?";
            $update_stmt = $db->prepare($update_query);
            if ($update_stmt->execute([$file_name, $student_id])) {
                $_SESSION['profile_message'] = 'Profile picture updated successfully!';
                $_SESSION['profile_message_type'] = 'success';
            } else {
                $_SESSION['profile_message'] = 'Failed to update profile picture!';
                $_SESSION['profile_message_type'] = 'error';
            }
        } else {
            $_SESSION['profile_message'] = 'Error uploading file!';
            $_SESSION['profile_message_type'] = 'error';
        }
    }
    header("Location: student_dashboard.php?student_id=$student_id&tab=profile");
    exit();
}

// Remove Profile Picture
if (isset($_POST['remove_profile'])) {
    if (!empty($student_data['profile']) && file_exists("uploads/" . $student_data['profile'])) {
        @unlink("uploads/" . $student_data['profile']);
    }
    $update_query = "UPDATE students SET profile = NULL WHERE id = ?";
    $update_stmt = $db->prepare($update_query);
    if ($update_stmt->execute([$student_id])) {
        $_SESSION['profile_message'] = 'Profile picture removed successfully!';
        $_SESSION['profile_message_type'] = 'success';
    } else {
        $_SESSION['profile_message'] = 'Failed to remove profile picture!';
        $_SESSION['profile_message_type'] = 'error';
    }
    header("Location: student_dashboard.php?student_id=$student_id&tab=profile");
    exit();
}

// Change Password
if (isset($_POST['change_password'])) {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Verify old password
    if (!password_verify($old_password, $student_data['password'])) {
        $_SESSION['profile_message'] = 'Current password is incorrect!';
        $_SESSION['profile_message_type'] = 'error';
    } elseif (strlen($new_password) < 6) {
        $_SESSION['profile_message'] = 'New password must be at least 6 characters long!';
        $_SESSION['profile_message_type'] = 'error';
    } elseif ($new_password !== $confirm_password) {
        $_SESSION['profile_message'] = 'New passwords do not match!';
        $_SESSION['profile_message_type'] = 'error';
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_query = "UPDATE students SET password = ? WHERE id = ?";
        $update_stmt = $db->prepare($update_query);
        if ($update_stmt->execute([$hashed_password, $student_id])) {
            $_SESSION['profile_message'] = 'Password changed successfully!';
            $_SESSION['profile_message_type'] = 'success';
        } else {
            $_SESSION['profile_message'] = 'Failed to change password!';
            $_SESSION['profile_message_type'] = 'error';
        }
    }
    header("Location: student_dashboard.php?student_id=$student_id&tab=profile");
    exit();
}

// Get messages from session
if (isset($_SESSION['profile_message'])) {
    $message = $_SESSION['profile_message'];
    $messageType = $_SESSION['profile_message_type'];
    unset($_SESSION['profile_message']);
    unset($_SESSION['profile_message_type']);
}

$courses = $student->getEnrolledCourses($student_id);
$stats = $student->getStats($student_id);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($student_data['name']); ?> - Dashboard</title>
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

        .header-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-md);
        }

        .profile-section {
            display: flex;
            align-items: center;
            gap: 30px;
        }

        .avatar-container {
            position: relative;
        }

        .avatar {
            width: 120px;
            height: 120px;
            background: var(--primary-color);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--accent-color);
            font-size: 42px;
            font-weight: 700;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            position: relative;
        }

        .status-badge {
            position: absolute;
            bottom: -5px;
            right: -5px;
            background: #10b981;
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
            z-index: 10;
        }

        .profile-details {
            flex: 1;
        }

        .profile-details h1 {
            font-size: 2rem;
            color: var(--text-dark);
            margin-bottom: 8px;
            font-weight: 700;
        }

        .student-id-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(251, 191, 36, 0.1);
            color: var(--text-dark);
            padding: 8px 16px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 16px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--text-dark);
            font-size: 14px;
            font-weight: 500;
        }

        .info-icon {
            font-size: 18px;
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

        .courses-section {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: var(--shadow-md);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .section-header h2 {
            font-size: 1.5rem;
            color: var(--text-dark);
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .course-card {
            background: white;
            border: 2px solid #f1f5f9;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .course-card::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 5px;
            background: var(--accent-color);
            opacity: 0;
            transition: opacity 0.3s;
        }

        .course-card:hover {
            border-color: var(--accent-color);
            box-shadow: var(--shadow-md);
            transform: translateX(5px);
        }

        .course-card:hover::before {
            opacity: 1;
        }

        .course-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 20px;
        }

        .course-code {
            background: var(--primary-color);
            color: var(--accent-color);
            padding: 8px 16px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
            display: inline-block;
            margin-bottom: 12px;
            letter-spacing: 0.5px;
        }

        .course-name {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 16px;
            line-height: 1.4;
        }

        .course-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            color: var(--text-dark);
            font-weight: 500;
        }

        .detail-icon {
            font-size: 16px;
        }

        .detail-label {
            font-weight: 600;
            color: var(--text-dark);
        }
        
        .detail-item span:not(.detail-icon) {
            color: var(--text-dark);
        }

        .course-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
            padding-top: 20px;
            border-top: 2px solid #f1f5f9;
        }

        .badges {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .grade-badge {
            padding: 8px 18px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .grade-A {
            background: #d1fae5;
            color: #065f46;
        }

        .grade-B {
            background: #dbeafe;
            color: #000000;
        }

        .grade-C {
            background: #fef3c7;
            color: #92400e;
        }

        .grade-N {
            background: rgba(251, 191, 36, 0.1);
            color: var(--text-dark);
        }

        .badge {
            padding: 8px 16px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 13px;
            background: rgba(251, 191, 36, 0.1);
            color: var(--text-dark);
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .progress-container {
            flex: 1;
            min-width: 200px;
        }

        .progress-label {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 13px;
            color: var(--text-dark);
            font-weight: 600;
        }

        .progress-bar {
            width: 100%;
            height: 10px;
            background: #f1f5f9;
            border-radius: 10px;
            overflow: hidden;
            position: relative;
        }

        .progress-fill {
            height: 100%;
            background: var(--accent-color);
            border-radius: 10px;
            transition: width 0.6s ease;
            box-shadow: 0 2px 8px rgba(251, 191, 36, 0.4);
        }

        .no-courses {
            text-align: center;
            padding: 60px 20px;
        }

        .no-courses-icon {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .no-courses p {
            color: var(--text-dark);
            font-size: 16px;
            font-weight: 500;
        }

        /* Profile Page Styles */
        .profile-page {
            max-width: 900px;
            margin: 0 auto;
        }

        .profile-header {
            margin-bottom: 2rem;
        }

        .profile-header h2 {
            font-size: 2rem;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .profile-header p {
            color: var(--text-light);
            font-size: 1rem;
        }

        .profile-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-md);
        }

        .card-header {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f1f5f9;
        }

        .card-header h3 {
            font-size: 1.5rem;
            color: var(--text-dark);
            margin-bottom: 0.25rem;
            font-weight: 700;
        }

        .card-subtitle {
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .card-body {
            padding-top: 1rem;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 600;
            color: var(--text-dark);
            flex: 1;
        }

        .info-value {
            color: var(--text-dark);
            font-weight: 500;
            text-align: right;
        }

        .profile-form {
            margin-top: 1rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.75rem;
            font-size: 0.95rem;
        }

        .form-group input {
            width: 100%;
            padding: 0.875rem 1.25rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 0.95rem;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
            margin-bottom: 0.5rem;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 4px rgba(251, 191, 36, 0.2);
        }

        .form-help {
            display: block;
            color: var(--text-light);
            font-size: 0.85rem;
            margin-top: 0.25rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }

        .btn-primary {
            background: var(--accent-color);
            color: var(--primary-color);
            box-shadow: 0 4px 15px rgba(251, 191, 36, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(251, 191, 36, 0.4);
            background: var(--accent-light);
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background: #4b5563;
            transform: translateY(-2px);
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
            transform: translateY(-2px);
        }

        .profile-picture-section {
            margin-top: 2rem;
        }

        .profile-picture-section label {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 1rem;
            font-size: 0.95rem;
        }

        .profile-picture-container {
            display: flex;
            gap: 2rem;
            align-items: flex-start;
            flex-wrap: wrap;
        }

        .profile-preview {
            width: 150px;
            height: 150px;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow-md);
            border: 3px solid var(--accent-color);
        }

        .profile-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-placeholder {
            width: 100%;
            height: 100%;
            background: var(--primary-color);
            color: var(--accent-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            font-weight: 700;
        }

        .profile-actions {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .upload-form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            animation: slideDown 0.3s ease;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }

        .alert-error {
            background: #fef2f2;
            color: #dc2626;
            border-left: 4px solid #ef4444;
        }

        .alert-close {
            cursor: pointer;
            font-size: 1.5rem;
            font-weight: bold;
            opacity: 0.7;
            transition: opacity 0.3s;
        }

        .alert-close:hover {
            opacity: 1;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .password-match {
            color: #10b981;
        }

        .password-mismatch {
            color: #ef4444;
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

            .profile-section {
                flex-direction: column;
                text-align: center;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .stats-cards {
                grid-template-columns: 1fr;
            }

            .course-footer {
                flex-direction: column;
                align-items: stretch;
            }

            .info-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .info-value {
                text-align: left;
            }

            .profile-picture-container {
                flex-direction: column;
            }

            .profile-actions {
                width: 100%;
            }
        }

    </style>
    <script>
        // Profile Picture Preview
        function previewProfilePicture(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('profilePreview');
                    if (preview.tagName === 'IMG') {
                        preview.src = e.target.result;
                    } else {
                        // Replace placeholder with image
                        const img = document.createElement('img');
                        img.id = 'profilePreview';
                        img.src = e.target.result;
                        img.alt = 'Profile Picture';
                        img.style.width = '100%';
                        img.style.height = '100%';
                        img.style.objectFit = 'cover';
                        preview.parentNode.replaceChild(img, preview);
                    }
                    document.getElementById('saveProfileBtn').style.display = 'inline-block';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Password Match Validation
        document.addEventListener('DOMContentLoaded', function() {
            const newPassword = document.getElementById('new_password');
            const confirmPassword = document.getElementById('confirm_password');
            const passwordMatch = document.getElementById('passwordMatch');
            const passwordForm = document.getElementById('passwordForm');

            if (confirmPassword) {
                confirmPassword.addEventListener('input', function() {
                    if (newPassword.value && confirmPassword.value) {
                        if (newPassword.value === confirmPassword.value) {
                            passwordMatch.textContent = '✓ Passwords match';
                            passwordMatch.className = 'form-help password-match';
                        } else {
                            passwordMatch.textContent = '✗ Passwords do not match';
                            passwordMatch.className = 'form-help password-mismatch';
                        }
                    } else {
                        passwordMatch.textContent = '';
                    }
                });

                newPassword.addEventListener('input', function() {
                    if (confirmPassword.value) {
                        confirmPassword.dispatchEvent(new Event('input'));
                    }
                });
            }

            // Auto-hide alert after 5 seconds
            const alertMessage = document.getElementById('alertMessage');
            if (alertMessage) {
                setTimeout(function() {
                    alertMessage.style.opacity = '0';
                    setTimeout(function() {
                        alertMessage.style.display = 'none';
                    }, 300);
                }, 5000);
            }
        });
    </script>
</head>

<body>
    <header>
        <h2>🎓 Student Dashboard - Learning Management System</h2>
        <div class="nav-links">
            <a href="?tab=dashboard">Dashboard</a>
            <a href="?tab=profile">Profile</a>
            <a href="index.php">Website</a>
            <a href="login.php?logout=1" class="logout-btn">Logout</a>
        </div>
    </header>
    <div class="container">
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>" id="alertMessage">
                <?php echo htmlspecialchars($message); ?>
                <span class="alert-close" onclick="this.parentElement.style.display='none'">&times;</span>
            </div>
        <?php endif; ?>
        
        <?php if ($current_tab == 'profile'): ?>
            <!-- Profile Section -->
            <div class="profile-page">
                <div class="profile-header">
                    <h2>👤 My Profile</h2>
                    <p>Manage your profile information and account settings</p>
                </div>

                <!-- Profile Information Card -->
                <div class="profile-card">
                    <div class="card-header">
                        <h3>📋 Profile Information</h3>
                        <span class="card-subtitle">Read-only information</span>
                    </div>
                    <div class="card-body">
                        <div class="info-row">
                            <div class="info-label">
                                <span class="info-icon">👤</span>
                                <span>Full Name</span>
                            </div>
                            <div class="info-value"><?php echo htmlspecialchars($student_data['name']); ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">
                                <span class="info-icon">🎫</span>
                                <span>Roll Number / Registration ID</span>
                            </div>
                            <div class="info-value"><?php echo htmlspecialchars($student_data['student_id']); ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">
                                <span class="info-icon">🎓</span>
                                <span>Department / Program</span>
                            </div>
                            <div class="info-value"><?php echo htmlspecialchars($student_data['major'] ?? 'N/A'); ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">
                                <span class="info-icon">📅</span>
                                <span>Semester / Year</span>
                            </div>
                            <div class="info-value"><?php echo htmlspecialchars($student_data['year'] ?? 'N/A'); ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">
                                <span class="info-icon">📱</span>
                                <span>Phone Number</span>
                            </div>
                            <div class="info-value"><?php echo htmlspecialchars($student_data['phone'] ?? 'N/A'); ?></div>
                        </div>
                    </div>
                </div>

                <!-- Editable Information Card -->
                <div class="profile-card">
                    <div class="card-header">
                        <h3>✏️ Editable Information</h3>
                        <span class="card-subtitle">You can update these fields</span>
                    </div>
                    <div class="card-body">
                        <!-- Email Update Form -->
                        <form method="POST" class="profile-form">
                            <input type="hidden" name="tab" value="profile">
                            <div class="form-group">
                                <label for="email">
                                    <span class="info-icon">📧</span>
                                    Email Address
                                </label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($student_data['email']); ?>" required>
                                <button type="submit" name="update_email" class="btn btn-primary">Update Email</button>
                            </div>
                        </form>

                        <!-- Profile Picture Upload -->
                        <div class="profile-picture-section">
                            <label>
                                <span class="info-icon">📷</span>
                                Profile Picture
                            </label>
                            <div class="profile-picture-container">
                                <div class="profile-preview">
                                    <?php if (!empty($student_data['profile']) && file_exists('uploads/' . $student_data['profile'])): ?>
                                        <img id="profilePreview" src="uploads/<?php echo htmlspecialchars($student_data['profile']); ?>" alt="Profile Picture">
                                    <?php else: ?>
                                        <div id="profilePreview" class="profile-placeholder">
                                            <?php
                                            $names = explode(' ', $student_data['name']);
                                            $initials = strtoupper($names[0][0]);
                                            if (isset($names[1])) {
                                                $initials .= strtoupper($names[1][0]);
                                            }
                                            echo $initials;
                                            ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="profile-actions">
                                    <form method="POST" enctype="multipart/form-data" class="upload-form" id="uploadForm">
                                        <input type="hidden" name="tab" value="profile">
                                        <input type="file" id="profileInput" name="profile_picture" accept="image/*" onchange="previewProfilePicture(this)" style="display: none;">
                                        <label for="profileInput" class="btn btn-secondary">Upload New Photo</label>
                                        <button type="submit" name="upload_profile" class="btn btn-primary" id="saveProfileBtn" style="display: none;">Save Photo</button>
                                    </form>
                                    <?php if (!empty($student_data['profile'])): ?>
                                        <form method="POST" class="remove-form" style="display: inline;">
                                            <input type="hidden" name="tab" value="profile">
                                            <button type="submit" name="remove_profile" class="btn btn-danger" onclick="return confirm('Are you sure you want to remove your profile picture?')">Remove Photo</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Change Password Card -->
                <div class="profile-card">
                    <div class="card-header">
                        <h3>🔒 Account Security</h3>
                        <span class="card-subtitle">Change your password</span>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="profile-form" id="passwordForm">
                            <input type="hidden" name="tab" value="profile">
                            <div class="form-group">
                                <label for="old_password">
                                    <span class="info-icon">🔑</span>
                                    Current Password
                                </label>
                                <input type="password" id="old_password" name="old_password" required>
                            </div>
                            <div class="form-group">
                                <label for="new_password">
                                    <span class="info-icon">🔐</span>
                                    New Password
                                </label>
                                <input type="password" id="new_password" name="new_password" minlength="6" required>
                                <small class="form-help">Minimum 6 characters</small>
                            </div>
                            <div class="form-group">
                                <label for="confirm_password">
                                    <span class="info-icon">✅</span>
                                    Confirm New Password
                                </label>
                                <input type="password" id="confirm_password" name="confirm_password" minlength="6" required>
                                <small class="form-help" id="passwordMatch"></small>
                            </div>
                            <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Dashboard Section -->
            <!-- Welcome Section -->
            <div class="welcome-section">
                <h3>Welcome back, <?php echo htmlspecialchars($student_data['name']); ?>!</h3>
                <p>Here's an overview of your academic progress and enrolled courses.</p>
            </div>

        <div class="header-card">
            <div class="profile-section">
                <div class="avatar-container">
                    <div class="avatar">
                        <?php if (!empty($student_data['profile']) && file_exists('uploads/' . $student_data['profile'])): ?>
                            <img src="<?php echo 'uploads/' . htmlspecialchars($student_data['profile']); ?>"
                                alt="Profile Picture"
                                style="width: 100%; height: 100%; border-radius: 20px; object-fit: cover;">
                        <?php else: ?>
                            <?php
                            $names = explode(' ', $student_data['name']);
                            $initials = strtoupper($names[0][0]);
                            if (isset($names[1])) {
                                $initials .= strtoupper($names[1][0]);
                            }
                            echo $initials;
                            ?>
                        <?php endif; ?>
                    </div>
                    <div class="status-badge">Active</div>
                </div>
            </div>
            <div class="profile-details">
                <h1><?php echo htmlspecialchars($student_data['name']); ?></h1>
                <div class="student-id-badge">
                    <span>🎫</span>
                    <span><?php echo htmlspecialchars($student_data['student_id']); ?></span>
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-icon">📧</span>
                        <span><?php echo htmlspecialchars($student_data['email']); ?></span>
                    </div>
                    <?php if (!empty($student_data['phone'])): ?>
                    <div class="info-item">
                        <span class="info-icon">📱</span>
                        <span><?php echo htmlspecialchars($student_data['phone']); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($student_data['major'])): ?>
                    <div class="info-item">
                        <span class="info-icon">🎓</span>
                        <span><?php echo htmlspecialchars($student_data['major']); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($student_data['year'])): ?>
                    <div class="info-item">
                        <span class="info-icon">📅</span>
                        <span><?php echo htmlspecialchars($student_data['year']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="stats-cards">
        <div class="stat-card">
            <div class="stat-icon">🏆</div>
            <div class="stat-info">
                <h4><?php echo number_format($student_data['gpa'] ?? 0, 2); ?></h4>
                <p>GPA</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">📚</div>
            <div class="stat-info">
                <h4><?php echo $stats['total_courses'] ?: 0; ?></h4>
                <p>Enrolled Courses</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">⭐</div>
            <div class="stat-info">
                <h4><?php echo $stats['total_credits'] ?: 0; ?></h4>
                <p>Total Credits</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">✓</div>
            <div class="stat-info">
                <h4><?php echo number_format($stats['avg_attendance'] ?? 0, 1); ?>%</h4>
                <p>Average Attendance</p>
            </div>
        </div>
    </div>

    <div class="courses-section">
        <div class="section-header">
            <h2>
                <span>📚</span>
                <span>My Courses</span>
            </h2>
        </div>

        <?php if (count($courses) > 0): ?>
            <?php foreach ($courses as $index => $course): ?>
                <div class="course-card">
                    <div class="course-header">
                        <div>
                            <div class="course-code"><?php echo htmlspecialchars($course['code']); ?></div>
                            <div class="course-name"><?php echo htmlspecialchars($course['name']); ?></div>
                        </div>
                    </div>

                    <div class="course-details">
                        <div class="detail-item">
                            <span class="detail-icon">👨‍🏫</span>
                            <span>
                                <span class="detail-label">Instructor:</span>
                                <?php echo htmlspecialchars($course['instructor_name'] ?: 'TBA'); ?>
                            </span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-icon">🕐</span>
                            <span>
                                <span class="detail-label">Schedule:</span>
                                <?php echo htmlspecialchars($course['schedule']); ?>
                            </span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-icon">📍</span>
                            <span>
                                <span class="detail-label">Room:</span>
                                <?php echo htmlspecialchars($course['room']); ?>
                            </span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-icon">⭐</span>
                            <span>
                                <span class="detail-label">Credits:</span>
                                <?php echo htmlspecialchars($course['credits']); ?>
                            </span>
                        </div>
                    </div>

                    <div class="course-footer">
                        <div class="badges">
                            <?php
                            $gradeClass = 'grade-N';
                            if (strpos($course['grade'], 'A') === 0)
                                $gradeClass = 'grade-A';
                            elseif (strpos($course['grade'], 'B') === 0)
                                $gradeClass = 'grade-B';
                            elseif (strpos($course['grade'], 'C') === 0)
                                $gradeClass = 'grade-C';
                            ?>
                            <span class="grade-badge <?php echo $gradeClass; ?>">
                                <span>🎯</span>
                                <span>Grade: <?php echo htmlspecialchars($course['grade']); ?></span>
                            </span>
                            <span class="badge">
                                <span>📊</span>
                                <span>Attendance: <?php echo number_format($course['attendance'], 0); ?>%</span>
                            </span>
                        </div>

                        <div class="progress-container">
                            <div class="progress-label">
                                <span>Course Progress</span>
                                <span><?php echo htmlspecialchars($course['progress']); ?>%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo htmlspecialchars($course['progress']); ?>%">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-courses">
                <div class="no-courses-icon">📚</div>
                <p>You are not enrolled in any courses yet.</p>
            </div>
        <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</body>

</html>