<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Undergraduate Admissions - Learning Management System</title>
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

        .badge {
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

        .info-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin: 2rem 0;
        }

        .info-card {
            background: var(--bg-light);
            padding: 2rem;
            border-radius: 16px;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .info-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }

        .info-card h3 {
            color: var(--primary-color);
            font-size: 1.5rem;
            margin-bottom: 1rem;
            font-weight: 700;
        }

        .info-card ul {
            list-style: none;
            color: var(--text-light);
        }

        .info-card ul li {
            padding: 0.5rem 0;
            padding-left: 1.5rem;
            position: relative;
        }

        .info-card ul li::before {
            content: '✓';
            position: absolute;
            left: 0;
            color: var(--accent-color);
            font-weight: 700;
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
            box-shadow: 0 4px 15px rgba(30, 58, 138, 0.3);
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

            .info-cards {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="badge">Undergraduate Programs</div>
        <h1>Undergraduate Admissions</h1>
        <div class="content">
            <p>Welcome to the Undergraduate Admissions page at Learning Management System. We offer a wide range of undergraduate programs designed to prepare you for a successful career in your chosen field.</p>
        </div>

        <div class="info-cards">
            <div class="info-card">
                <h3>📋 Application Requirements</h3>
                <ul>
                    <li>High school diploma or equivalent</li>
                    <li>Minimum GPA of 3.0</li>
                    <li>Standardized test scores (SAT/ACT)</li>
                    <li>Letters of recommendation</li>
                    <li>Personal statement</li>
                </ul>
            </div>
            <div class="info-card">
                <h3>📅 Important Dates</h3>
                <ul>
                    <li>Application Deadline: March 1st</li>
                    <li>Early Decision: November 15th</li>
                    <li>Regular Decision: January 15th</li>
                    <li>Financial Aid Deadline: February 1st</li>
                </ul>
            </div>
            <div class="info-card">
                <h3>💰 Financial Aid</h3>
                <ul>
                    <li>Scholarships available</li>
                    <li>Need-based financial aid</li>
                    <li>Work-study programs</li>
                    <li>Student loans and grants</li>
                </ul>
            </div>
        </div>

        <a href="../index.php" class="btn">
            <span>←</span>
            <span>Back to Home</span>
        </a>
    </div>
</body>

</html>

