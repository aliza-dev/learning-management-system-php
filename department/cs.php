<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Computer Science Department - Learning Management System</title>
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
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
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
            padding: 2rem;
            overflow-y: auto;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 3rem;
            box-shadow: var(--shadow-xl);
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

        h1 {
            font-family: 'Playfair Display', serif;
            font-size: 3rem;
            color: var(--text-dark);
            margin-bottom: 1rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .dept-badge {
            display: inline-block;
            background: var(--accent-color);
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(251, 191, 36, 0.3);
        }

        .content {
            font-size: 1.125rem;
            color: var(--text-light);
            line-height: 1.8;
            margin-bottom: 2rem;
        }

        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin: 2rem 0;
        }

        .feature-card {
            background: var(--bg-light);
            padding: 1.5rem;
            border-radius: 16px;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }

        .feature-card h3 {
            color: var(--primary-color);
            font-size: 1.25rem;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .feature-card p {
            color: var(--text-light);
            font-size: 0.95rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 2rem;
            color: white;
            background: var(--primary-color);
            color: var(--accent-color);
            padding: 1rem 2rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(251, 191, 36, 0.3);
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(251, 191, 36, 0.4);
        }

        @media (max-width: 768px) {
            .container {
                padding: 2rem 1.5rem;
            }

            h1 {
                font-size: 2rem;
            }

            .features {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="dept-badge">Computer Science Department</div>
        <h1>Computer Science</h1>
        <div class="content">
            <p>Welcome to the Computer Science Department at Learning Management System. Our department is dedicated to excellence in programming, artificial intelligence, software engineering, databases, and modern computing technologies.</p>
        </div>

        <div class="features">
            <div class="feature-card">
                <h3>💻 Programming</h3>
                <p>Master multiple programming languages and frameworks</p>
            </div>
            <div class="feature-card">
                <h3>🤖 Artificial Intelligence</h3>
                <p>Explore cutting-edge AI and machine learning technologies</p>
            </div>
            <div class="feature-card">
                <h3>🔧 Software Engineering</h3>
                <p>Learn best practices in software development and design</p>
            </div>
            <div class="feature-card">
                <h3>🗄️ Databases</h3>
                <p>Understand database design, management, and optimization</p>
            </div>
        </div>

        <a href="../index.php" class="btn">
            <span>←</span>
            <span>Back to Home</span>
        </a>
    </div>
</body>

</html>
