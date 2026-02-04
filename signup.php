<?php
session_start();
include('db_connect.php');

$message = '';
$messageType = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = trim($_POST['fullname']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];

    if ($password !== $confirm_password) {
        $message = "Passwords do not match!";
        $messageType = "error";
    } else {
        // Check if email already exists
        $email_check_sql = "SELECT email FROM admin WHERE email = ? UNION SELECT email FROM students WHERE email = ? UNION SELECT email FROM faculty WHERE email = ?";
        $email_check_stmt = $conn->prepare($email_check_sql);
        $email_check_stmt->bind_param("sss", $email, $email, $email);
        $email_check_stmt->execute();
        $email_result = $email_check_stmt->get_result();
        
        // Check if username already exists (for students and admin only, faculty doesn't have username)
        $username_check_sql = "";
        if ($role === 'faculty') {
            // Faculty doesn't use username, skip check
            $username_result = new stdClass();
            $username_result->num_rows = 0;
        } else {
            $username_check_sql = "SELECT username FROM admin WHERE username = ? UNION SELECT username FROM students WHERE username = ?";
            $username_check_stmt = $conn->prepare($username_check_sql);
            $username_check_stmt->bind_param("ss", $username, $username);
            $username_check_stmt->execute();
            $username_result = $username_check_stmt->get_result();
        }
        
        if ($email_result->num_rows > 0) {
            $message = "This email address is already registered. Please use a different email or try logging in.";
            $messageType = "error";
            $email_check_stmt->close();
            $username_check_stmt->close();
        } elseif (isset($username_check_stmt) && $username_result->num_rows > 0) {
            $message = "This username is already taken. Please choose a different username.";
            $messageType = "error";
            $email_check_stmt->close();
            if (isset($username_check_stmt)) $username_check_stmt->close();
        } else {
            $email_check_stmt->close();
            if (isset($username_check_stmt)) $username_check_stmt->close();
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            if ($role === 'admin') {

                $sql = "INSERT INTO admin (fullname, username, email, password) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssss", $fullname, $username, $email, $hashed_password);

            } elseif ($role === 'faculty') {
                
                $first_name = trim($_POST['first_name']);
                $last_name = trim($_POST['last_name']);
                $phone = trim($_POST['phone']);
                $department_id = intval($_POST['department_id']);
                $bio = trim($_POST['bio'] ?? '');
                $hire_date = date('Y-m-d');

                $sql = "INSERT INTO faculty (first_name, last_name, email, phone, department_id, hire_date, bio, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssisss", $first_name, $last_name, $email, $phone, $department_id, $hire_date, $bio, $hashed_password);

            } else {

            $phone = trim($_POST['phone']);

            // ---------------- PROFILE UPLOAD LOGIC (Only for Students) ----------------
            $profile = "";
            if (!empty($_FILES['profile']['name'])) {

                $target_dir = "uploads/";
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true); // if folder missing, create it
                }

                $file_name = time() . "_" . basename($_FILES["profile"]["name"]);
                $target_file = $target_dir . $file_name;

                if (move_uploaded_file($_FILES["profile"]["tmp_name"], $target_file)) {
                    $profile = $file_name;
                } else {
                    $message = "Error uploading profile picture!";
                    $messageType = "error";
                }
            }
            // -------------------------------------------------------------------------

            // Generate unique student_id
            $result = $conn->query("SELECT COUNT(*) as count FROM students");
            $count = $result->fetch_assoc()['count'];
            $student_id = 'STU-' . date('Y') . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);

            // Check if student_id already exists (handle edge case)
            $check_stmt = $conn->prepare("SELECT student_id FROM students WHERE student_id = ?");
            $check_stmt->bind_param("s", $student_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            $attempts = 0;
            while ($check_result->num_rows > 0 && $attempts < 10) {
                $count++;
                $student_id = 'STU-' . date('Y') . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
                $check_stmt->close();
                $check_stmt = $conn->prepare("SELECT student_id FROM students WHERE student_id = ?");
                $check_stmt->bind_param("s", $student_id);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                $attempts++;
            }
            $check_stmt->close();

            $sql = "INSERT INTO students (student_id, name, username, email, phone, profile, password) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssss", $student_id, $fullname, $username, $email, $phone, $profile, $hashed_password);
            }

            if ($stmt->execute()) {
                $message = ucfirst($role) . " registered successfully!";
                $messageType = "success";
            } else {
                // Check for specific database errors
                if (strpos($stmt->error, "Duplicate entry") !== false) {
                    if (strpos($stmt->error, "email") !== false) {
                        $message = "This email address is already registered. Please use a different email or try logging in.";
                    } elseif (strpos($stmt->error, "username") !== false) {
                        $message = "This username is already taken. Please choose a different username.";
                    } else {
                        $message = "This account already exists. Please try logging in instead.";
                    }
                } else {
                    $message = "Registration failed: " . $stmt->error;
                }
                $messageType = "error";
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Learning Management System</title>
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

        .container {
            position: relative;
            z-index: 1;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            padding: 3rem;
            border-radius: 24px;
            width: 100%;
            max-width: 520px;
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
            margin-bottom: 2rem;
        }

        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        h2 {
            font-size: 1.75rem;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .subtitle {
            color: var(--text-light);
            font-size: 0.95rem;
        }

        .role-selector {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.75rem;
            margin-bottom: 1.75rem;
            background: var(--bg-light);
            padding: 0.5rem;
            border-radius: 16px;
        }

        .role-btn {
            padding: 1.25rem 1rem;
            background: white;
            border: 2px solid transparent;
            border-radius: 12px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            font-size: 0.95rem;
            color: var(--text-dark);
            box-shadow: var(--shadow-sm);
        }

        .role-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .role-btn.active {
            background: var(--accent-color);
            color: var(--primary-color);
            border-color: var(--accent-color);
            box-shadow: 0 6px 20px rgba(251, 191, 36, 0.3);
        }

        .role-btn .emoji {
            font-size: 1.5rem;
            display: block;
            margin-bottom: 0.5rem;
        }

        .message {
            text-align: center;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
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

        .success {
            background: #f0fdf4;
            color: #16a34a;
            border-left: 4px solid #16a34a;
        }

        .error {
            background: #fef2f2;
            color: #dc2626;
            border-left: 4px solid #dc2626;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group label {
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
            display: block;
            color: var(--text-dark);
            font-weight: 600;
        }

        form input[type="text"],
        form input[type="email"],
        form input[type="password"],
        form input[type="file"] {
            width: 100%;
            padding: 1rem 1.25rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 0.95rem;
            font-family: 'Poppins', sans-serif;
            background: white;
            transition: all 0.3s ease;
        }

        form input[type="file"] {
            padding: 0.75rem;
            cursor: pointer;
        }

        form input:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 4px rgba(251, 191, 36, 0.2);
            transform: translateY(-2px);
        }

        form input::placeholder {
            color: #9ca3af;
        }

        form button {
            width: 100%;
            padding: 1rem;
            background: var(--accent-color);
            color: var(--primary-color);
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
            box-shadow: 0 4px 15px rgba(251, 191, 36, 0.3);
            margin-top: 0.5rem;
        }

        form button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(251, 191, 36, 0.4);
            background: var(--accent-light);
        }

        form button:active {
            transform: translateY(-1px);
        }

        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e5e7eb;
        }

        .login-link p {
            color: var(--text-light);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .login-link a {
            color: var(--accent-color);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .login-link a:hover {
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

        #studentFields,
        #facultyFields {
            display: none;
            animation: fadeIn 0.3s ease;
        }
        
        form select {
            width: 100%;
            padding: 1rem 1.25rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 0.95rem;
            font-family: 'Poppins', sans-serif;
            background: white;
            transition: all 0.3s ease;
        }
        
        form select:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 4px rgba(251, 191, 36, 0.2);
            transform: translateY(-2px);
        }
        
        form textarea {
            width: 100%;
            padding: 1rem 1.25rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 0.95rem;
            font-family: 'Poppins', sans-serif;
            background: white;
            transition: all 0.3s ease;
            resize: vertical;
            min-height: 100px;
        }
        
        form textarea:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 4px rgba(251, 191, 36, 0.2);
            transform: translateY(-2px);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

            @media (max-width: 768px) {
            .container {
                padding: 2rem 1.5rem;
            }

            h2 {
                font-size: 1.5rem;
            }

            .role-selector {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <script>
        function selectRole(role) {
            document.getElementById("role").value = role;
            document.getElementById("studentBtn").classList.remove("active");
            document.getElementById("adminBtn").classList.remove("active");
            document.getElementById("facultyBtn").classList.remove("active");

            document.getElementById("studentFields").style.display = "none";
            document.getElementById("facultyFields").style.display = "none";

            if (role === "student") {
                document.getElementById("studentBtn").classList.add("active");
                document.getElementById("studentFields").style.display = "block";
                document.getElementById("fullnameGroup").style.display = "block";
                document.getElementById("usernameGroup").style.display = "block";
                document.getElementById("fullname").required = true;
                document.getElementById("username").required = true;
            } else if (role === "faculty") {
                document.getElementById("facultyBtn").classList.add("active");
                document.getElementById("facultyFields").style.display = "block";
                document.getElementById("fullnameGroup").style.display = "none";
                document.getElementById("usernameGroup").style.display = "none";
                document.getElementById("fullname").required = false;
                document.getElementById("username").required = false;
            } else {
                document.getElementById("adminBtn").classList.add("active");
                document.getElementById("fullnameGroup").style.display = "block";
                document.getElementById("usernameGroup").style.display = "block";
                document.getElementById("fullname").required = true;
                document.getElementById("username").required = true;
            }
        }
    </script>
</head>

<body onload="selectRole('student')">
    <div class="container">
        <div class="header">
            <div class="logo">Learning Management System</div>
            <h2>Create Account</h2>
            <p class="subtitle">Join us and start your learning journey</p>
        </div>

        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="role-selector">
            <div class="role-btn active" id="studentBtn" onclick="selectRole('student')">
                <span class="emoji">🎓</span> Student
            </div>
            <div class="role-btn" id="facultyBtn" onclick="selectRole('faculty')">
                <span class="emoji">👨‍🏫</span> Faculty
            </div>
            <div class="role-btn" id="adminBtn" onclick="selectRole('admin')">
                <span class="emoji">👨‍💼</span> Admin
            </div>
        </div>

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="role" id="role" value="student">

            <div class="form-group" id="fullnameGroup">
                <label for="fullname">Full Name</label>
                <input type="text" id="fullname" name="fullname" placeholder="Enter your full name" required>
            </div>

            <div class="form-group" id="usernameGroup">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Choose a username" required>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="Enter your email address" required>
            </div>

            <div id="studentFields">
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="text" id="phone" name="phone" placeholder="Enter your phone number">
                </div>
                <div class="form-group">
                    <label for="profile">Profile Picture</label>
                    <input type="file" id="profile" name="profile" accept="image/*">
                </div>
            </div>

            <div id="facultyFields">
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" placeholder="Enter your first name">
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" placeholder="Enter your last name">
                </div>
                <div class="form-group">
                    <label for="faculty_phone">Phone Number</label>
                    <input type="text" id="faculty_phone" name="phone" placeholder="Enter your phone number">
                </div>
                <div class="form-group">
                    <label for="department_id">Department</label>
                    <select id="department_id" name="department_id" required>
                        <option value="">Select Department</option>
                        <?php
                        $departments = $conn->query("SELECT * FROM departments ORDER BY name");
                        if ($departments && $departments->num_rows > 0) {
                            while ($dept = $departments->fetch_assoc()) {
                                echo '<option value="' . $dept['depart_id'] . '">' . htmlspecialchars($dept['name']) . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="bio">Bio (Optional)</label>
                    <textarea id="bio" name="bio" placeholder="Tell us about yourself"></textarea>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Create a strong password" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter your password" required>
            </div>

            <button type="submit">Create Account</button>

            <div class="login-link">
                <p>Already have an account? <a href="login.php">Sign In</a></p>
                <a href="index.php" class="back-home">← Back to Home</a>
            </div>
        </form>
    </div>
</body>

</html>