<?php
session_start();
if (!isset($_SESSION['student'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Welcome - University</title>
    <style>
        body {
            font-family: Arial;
            text-align: center;
            background: #f8f9fa;
            margin-top: 100px;
        }

        a {
            text-decoration: none;
            color: white;
            background: #003366;
            padding: 10px 15px;
            border-radius: 5px;
        }

        a:hover {
            background: #002244;
        }
    </style>
</head>

<body>
    <h2>Welcome, <?php echo $_SESSION['student']; ?> 🎓</h2>
    <p>You have successfully logged in to the University Portal.</p>
    <a href="logout.php">Logout</a>
</body>

</html>