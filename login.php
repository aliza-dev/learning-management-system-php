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

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $user_type = $_POST['user_type'];

    if ($user_type == 'admin') {
        $stmt = $conn->prepare("SELECT * FROM admin WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $admin = $result->fetch_assoc();
            if (password_verify($password, $admin['password'])) {
                $_SESSION['user_id'] = $admin['id'];
                $_SESSION['user_type'] = 'admin';
                $_SESSION['name'] = $admin['fullname'];
                $_SESSION['email'] = $admin['email'];
                header("Location: admin_dashboard.php");
                exit();
            } else {
                $message = 'Invalid email or password!';
                $messageType = 'error';
            }
        } else {
            $message = 'Admin account not found!';
            $messageType = 'error';
        }
        $stmt->close();
    } elseif ($user_type == 'faculty') {
        $stmt = $conn->prepare("SELECT * FROM faculty WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $faculty = $result->fetch_assoc();

            if (password_verify($password, $faculty['password'])) {
                $_SESSION['user_id'] = $faculty['id'];
                $_SESSION['user_type'] = 'faculty';
                $_SESSION['name'] = $faculty['first_name'] . ' ' . $faculty['last_name'];
                $_SESSION['email'] = $faculty['email'];
                $_SESSION['is_focal_person'] = $faculty['is_focal_person'];
                $_SESSION['department_id'] = $faculty['department_id'];
                $_SESSION['user_rights'] = $faculty['user_rights'] ?? 'normal';
                
                // Redirect all faculty to faculty dashboard
                header("Location: faculty_dashboard.php");
                exit();
            } else {
                $message = 'Invalid email or password!';
                $messageType = 'error';
            }
        } else {
            $message = 'Faculty account not found!';
            $messageType = 'error';
        }
        $stmt->close();
    } else {
        $stmt = $conn->prepare("SELECT * FROM students WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $student = $result->fetch_assoc();

            if (password_verify($password, $student['password']) || $password == $student['student_id']) {
                $_SESSION['user_id'] = $student['id'];
                $_SESSION['user_type'] = 'student';
                $_SESSION['name'] = $student['name'];
                $_SESSION['email'] = $student['email'];
                $_SESSION['student_id'] = $student['student_id'];
                header("Location: student_dashboard.php?student_id=" . $student['id']);
                exit();
            } else {
                $message = 'Invalid email or password!';
                $messageType = 'error';
            }
        } else {
            $message = 'Student account not found!';
            $messageType = 'error';
        }
        $stmt->close();
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - Learning Management System</title>
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
    background: #ffffff;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    position: relative;
    overflow-y: auto;
}

body::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: url('https://images.unsplash.com/photo-1523050854058-8df90110c9f1?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80') center/cover;
    opacity: 0.1;
    z-index: 0;
}

.login-container {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 480px;
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(20px);
    border-radius: 24px;
    padding: 3rem;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    animation: fadeInUp 0.6s ease;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.header {
    text-align: center;
    margin-bottom: 2.5rem;
}

        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

.header h2 {
    font-size: 1.75rem;
    color: var(--text-dark);
    margin-bottom: 0.5rem;
    font-weight: 700;
}

.header p {
    color: var(--text-light);
    font-size: 0.95rem;
}

.message {
    padding: 1rem;
    border-radius: 12px;
    margin-bottom: 1.5rem;
    font-size: 0.9rem;
    text-align: center;
    font-weight: 500;
    animation: slideDown 0.3s ease;
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

.message.error {
    background: #fef2f2;
    color: #dc2626;
    border-left: 4px solid #dc2626;
}

.message.success {
    background: #f0fdf4;
    color: #16a34a;
    border-left: 4px solid #16a34a;
}

.role-box {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.75rem;
    margin-bottom: 1.75rem;
    background: var(--bg-light);
    padding: 0.5rem;
    border-radius: 16px;
}

.role-box input {
    display: none;
}

.role-box label {
    padding: 0.875rem 1rem;
    text-align: center;
    border: 2px solid transparent;
    border-radius: 12px;
    cursor: pointer;
    font-size: 0.875rem;
    font-weight: 600;
    background: white;
    color: var(--text-dark);
    transition: all 0.3s ease;
    position: relative;
}

.role-box label:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.role-box input:checked + label {
            border-color: var(--accent-color);
            color: var(--primary-color);
            background: var(--accent-color);
            box-shadow: 0 4px 12px rgba(251, 191, 36, 0.3);
        }

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    font-size: 0.875rem;
    margin-bottom: 0.5rem;
    display: block;
    color: var(--text-dark);
    font-weight: 600;
}

.form-group input {
    width: 100%;
    padding: 1rem 1.25rem;
    border-radius: 12px;
    border: 2px solid #e5e7eb;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    font-family: 'Poppins', sans-serif;
    background: white;
}

.form-group input:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 4px rgba(251, 191, 36, 0.2);
    outline: none;
            transform: translateY(-2px);
        }

.form-group input::placeholder {
    color: #9ca3af;
}

.btn {
    width: 100%;
    padding: 1rem;
    background: linear-gradient(135deg, var(--accent-color), var(--accent-light));
    border: none;
    border-radius: 12px;
    color: white;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    font-family: 'Poppins', sans-serif;
    box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
    margin-top: 0.5rem;
}

.btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(245, 158, 11, 0.4);
}

.btn:active {
    transform: translateY(-1px);
}

.footer {
    text-align: center;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid #e5e7eb;
}

.footer p {
    color: var(--text-light);
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}

.footer a {
            color: var(--accent-color);
    text-decoration: none;
    font-weight: 600;
            transition: all 0.3s ease;
        }

        .footer a:hover {
            color: var(--primary-color);
            text-decoration: underline;
        }

.back-home {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--text-light);
    text-decoration: none;
    font-size: 0.875rem;
    margin-top: 1rem;
    transition: all 0.3s ease;
}

        .back-home:hover {
            color: var(--accent-color);
            transform: translateX(-4px);
        }

@media (max-width: 640px) {
    .login-container {
        padding: 2rem 1.5rem;
    }

    .header h2 {
        font-size: 1.5rem;
    }

    .role-box {
        grid-template-columns: 1fr;
    }

    .role-box label {
        padding: 1rem;
    }
}
</style>

</head>

<body>

<div class="login-container">

    <div class="header">
        <div class="logo">Learning Management System</div>
        <h2>Welcome Back</h2>
        <p>Sign in to access your account</p>
    </div>

    <?php if($message): ?>
        <div class="message <?php echo $messageType; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form method="POST">

        <div class="role-box">
            <input type="radio" id="student" name="user_type" value="student" checked>
            <label for="student">Student</label>

            <input type="radio" id="faculty" name="user_type" value="faculty">
            <label for="faculty">Faculty</label>

            <input type="radio" id="admin" name="user_type" value="admin">
            <label for="admin">Admin</label>
        </div>

        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" placeholder="Enter your email address" required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>
        </div>

        <button type="submit" name="login" class="btn">Sign In</button>

    </form>

    <div class="footer">
        <p>Don't have an account? <a href="signup.php">Create Account</a></p>
        <a href="index.php" class="back-home">← Back to Home</a>
    </div>

</div>

</body>
</html>
