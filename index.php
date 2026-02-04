<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learning Management System - Excellence in Education</title>
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
            color: var(--text-dark);
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Navigation */
        nav {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            padding: 1rem 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow-sm);
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        nav.scrolled {
            box-shadow: var(--shadow-md);
            padding: 0.75rem 5%;
        }

        nav .logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--primary-color);
            letter-spacing: -0.5px;
        }

        nav ul {
            list-style: none;
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        nav ul li {
            position: relative;
        }

        nav ul li a {
            color: var(--text-dark);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            padding: 0.5rem 0;
            position: relative;
        }

        nav ul li a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--accent-color);
            transition: width 0.3s ease;
        }

        nav ul li a:hover::after,
        nav ul li a.active::after {
            width: 100%;
        }

        nav ul li a:hover {
            color: var(--accent-color);
        }

        nav ul li a.btn-signup {
            background: linear-gradient(135deg, var(--accent-color), var(--accent-light));
            color: white;
            padding: 0.6rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
            transition: all 0.3s ease;
        }

        nav ul li a.btn-signup::after {
            display: none;
        }

        nav ul li a.btn-signup:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(245, 158, 11, 0.4);
        }

        nav ul li .dropdown-content {
            display: none;
            position: absolute;
            top: calc(100% + 10px);
            left: 50%;
            transform: translateX(-50%);
            background: white;
            min-width: 220px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow-xl);
            z-index: 100;
            animation: fadeInDown 0.3s ease;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateX(-50%) translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateX(-50%) translateY(0);
            }
        }

        nav ul li .dropdown-content li a {
            color: var(--text-dark);
            padding: 0.875rem 1.5rem;
            display: block;
            transition: all 0.3s ease;
        }

        nav ul li .dropdown-content li a::after {
            display: none;
        }

        nav ul li .dropdown-content li a:hover {
            background: var(--primary-color);
            color: var(--accent-color);
            padding-left: 2rem;
        }

        nav ul li.dropdown:hover .dropdown-content {
            display: block;
        }

        /* Hero Section */
        .hero {
            position: relative;
            height: 90vh;
            min-height: 600px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hero-slider {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
        }

        .hero-slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 1s ease-in-out;
        }

        .hero-slide.active {
            opacity: 1;
        }

        .hero-slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .hero-slide::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
        }

        .hero-content {
            position: relative;
            z-index: 2;
            text-align: center;
            color: white;
            max-width: 900px;
            padding: 0 2rem;
            animation: fadeInUp 1s ease;
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

        .hero-content h1 {
            font-family: 'Playfair Display', serif;
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            line-height: 1.2;
            text-shadow: 0 2px 20px rgba(0, 0, 0, 0.3);
        }

        .hero-content p {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            opacity: 0.95;
            font-weight: 300;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 1rem 2.5rem;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
            font-size: 1rem;
            border: 2px solid transparent;
        }

        .btn-primary {
            background: var(--accent-color);
            color: white;
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 25px rgba(245, 158, 11, 0.5);
        }

        .btn-outline {
            background: transparent;
            color: white;
            border: 2px solid white;
        }

        .btn-outline:hover {
            background: white;
            color: var(--primary-color);
        }

        .hero-indicators {
            position: absolute;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%);
            z-index: 3;
            display: flex;
            gap: 0.75rem;
        }

        .indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .indicator.active {
            background: white;
            width: 32px;
            border-radius: 6px;
        }

        /* VC Section */
        .vc-section {
            padding: 6rem 5%;
            background: var(--bg-light);
        }

        .vc-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 4rem;
            align-items: center;
        }

        .vc-photo {
            text-align: center;
        }

        .vc-photo-wrapper {
            position: relative;
            display: inline-block;
        }

        .vc-photo img {
            width: 320px;
            height: 320px;
            border-radius: 50%;
            object-fit: cover;
            border: 6px solid white;
            box-shadow: var(--shadow-xl);
            position: relative;
            z-index: 2;
        }

        .vc-photo-wrapper::before {
            content: '';
            position: absolute;
            top: -20px;
            left: -20px;
            width: 360px;
            height: 360px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent-color), var(--accent-light));
            z-index: 1;
            opacity: 0.2;
        }

        .vc-photo h3 {
            margin-top: 1.5rem;
            font-family: 'Playfair Display', serif;
            font-size: 1.75rem;
            color: var(--text-dark);
            font-weight: 700;
        }

        .vc-photo p {
            color: var(--text-light);
            font-size: 1rem;
            margin-top: 0.5rem;
            font-style: italic;
        }

        .vc-message h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            color: var(--text-dark);
            margin-bottom: 1.5rem;
            font-weight: 700;
        }

        .vc-message p {
            font-size: 1.125rem;
            color: var(--text-light);
            line-height: 1.8;
            margin-bottom: 1.5rem;
        }

        .vc-quote {
            font-size: 1.25rem;
            font-style: italic;
            color: var(--primary-color);
            border-left: 4px solid var(--accent-color);
            padding-left: 1.5rem;
            margin-top: 2rem;
        }

        /* Stats Section */
        .stats-section {
            background: var(--primary-color);
            color: white;
            padding: 4rem 5%;
        }

        .stats-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 3rem;
        }

        .stat-card {
            text-align: center;
            padding: 2rem;
        }

        .stat-number {
            font-size: 3.5rem;
            font-weight: 800;
            color: var(--accent-color);
            margin-bottom: 0.5rem;
            font-family: 'Playfair Display', serif;
        }

        .stat-label {
            font-size: 1.125rem;
            opacity: 0.9;
            font-weight: 500;
        }

        /* Gallery Section */
        .gallery-section {
            padding: 6rem 5%;
            background: white;
        }

        .section-header {
            text-align: center;
            margin-bottom: 4rem;
        }

        .section-header h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2.75rem;
            color: var(--text-dark);
            margin-bottom: 1rem;
            font-weight: 700;
        }

        .section-header p {
            font-size: 1.125rem;
            color: var(--text-light);
            max-width: 600px;
            margin: 0 auto;
        }

        .gallery {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .gallery-item {
            height: 280px;
            border-radius: 16px;
            overflow: hidden;
            position: relative;
            cursor: pointer;
            box-shadow: var(--shadow-md);
            transition: all 0.4s ease;
        }

        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .gallery-item::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.7), transparent);
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .gallery-item:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
        }

        .gallery-item:hover img {
            transform: scale(1.1);
        }

        .gallery-item:hover::after {
            opacity: 1;
        }

        /* Contact Section */
        .contact-section {
            background: var(--primary-color);
            color: white;
            padding: 6rem 5%;
        }

        .contact-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: start;
        }

        .contact-info h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            margin-bottom: 2rem;
            font-weight: 700;
        }

        .contact-info p {
            font-size: 1.125rem;
            line-height: 2;
            margin: 1.25rem 0;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .contact-info strong {
            color: var(--accent-color);
            min-width: 120px;
            font-weight: 600;
        }

        .contact-map {
            border-radius: 16px;
            overflow: hidden;
            box-shadow: var(--shadow-xl);
            height: 400px;
        }

        .contact-map iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        /* Footer */
        footer {
            background: #0f172a;
            color: #94a3b8;
            text-align: center;
            padding: 2.5rem;
            font-size: 0.95rem;
        }

        footer p {
            margin: 0.5rem 0;
        }

        footer .highlight {
            color: var(--accent-color);
            font-weight: 600;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .vc-container,
            .contact-container {
                grid-template-columns: 1fr;
                gap: 3rem;
            }

            .hero-content h1 {
                font-size: 2.75rem;
            }
        }

        @media (max-width: 768px) {
            nav {
                padding: 1rem 3%;
                flex-wrap: wrap;
            }

            nav ul {
                gap: 1rem;
                flex-wrap: wrap;
                justify-content: center;
            }

            nav ul li .dropdown-content {
                left: 0;
                transform: none;
            }

            .hero {
                height: 70vh;
                min-height: 500px;
            }

            .hero-content h1 {
                font-size: 2.25rem;
            }

            .hero-content p {
                font-size: 1rem;
            }

            .vc-photo img {
                width: 250px;
                height: 250px;
            }

            .vc-photo-wrapper::before {
                width: 290px;
                height: 290px;
            }

            .gallery {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }

            .gallery-item {
                height: 200px;
            }

            .stats-container {
                grid-template-columns: repeat(2, 1fr);
                gap: 2rem;
            }

            .section-header h2 {
                font-size: 2rem;
            }
        }

        @media (max-width: 480px) {
            .gallery {
                grid-template-columns: 1fr;
            }

            .stats-container {
                grid-template-columns: 1fr;
            }

            .hero-buttons {
                flex-direction: column;
                width: 100%;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>

    <?php
    // Load departments from database
    require_once 'db_connect.php';
    $departments_query = $conn->query("SELECT * FROM departments ORDER BY name");
    ?>
    <nav id="navbar">
        <div class="logo">Learning Management System</div>
        <ul>
            <li><a href="index.php" class="active">Home</a></li>
            <li><a href="#about">About</a></li>
            <li class="dropdown">
                <a href="#">Departments</a>
                <ul class="dropdown-content">
                    <?php if ($departments_query && $departments_query->num_rows > 0): ?>
                        <?php while ($dept = $departments_query->fetch_assoc()): ?>
                            <li><a href="dpt.php?id=<?= $dept['depart_id'] ?>"><?= htmlspecialchars($dept['name']) ?></a></li>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <li><a href="#">No departments available</a></li>
                    <?php endif; ?>
                </ul>
            </li>
            <li class="dropdown">
                <a href="#">Admissions</a>
                <ul class="dropdown-content">
                    <li><a href="Admission/ug.php">Undergraduate</a></li>
                    <li><a href="Admission/pg.php">Postgraduate</a></li>
                </ul>
            </li>
            <li><a href="#contact">Contact</a></li>
            <li><a href="login.php">Login</a></li>
            <li><a href="signup.php" class="btn-signup">Sign Up</a></li>
        </ul>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-slider">
            <div class="hero-slide active">
                <img src="https://images.unsplash.com/photo-1541339907198-e08756dedf3f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80" alt="Modern University Campus">
            </div>
            <div class="hero-slide">
                <img src="https://images.unsplash.com/photo-1481627834876-b7833e8f5570?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2028&q=80" alt="Modern Library">
            </div>
            <div class="hero-slide">
                <img src="https://images.unsplash.com/photo-1523050854058-8df90110c9f1?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80" alt="Students Learning">
            </div>
            <div class="hero-slide">
                <img src="https://images.unsplash.com/photo-1434030216411-0b793f4b4173?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80" alt="Graduation Ceremony">
            </div>
        </div>
        <div class="hero-content">
            <h1>Excellence in Education, Innovation in Action</h1>
            <p>Empowering the next generation of leaders, innovators, and thinkers through world-class education and cutting-edge research.</p>
            <div class="hero-buttons">
                <a href="Admission/ug.php" class="btn btn-primary">Apply Now</a>
                <a href="#about" class="btn btn-outline">Learn More</a>
            </div>
        </div>
        <div class="hero-indicators">
            <span class="indicator active" data-slide="0"></span>
            <span class="indicator" data-slide="1"></span>
            <span class="indicator" data-slide="2"></span>
            <span class="indicator" data-slide="3"></span>
    </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-number">50+</div>
                <div class="stat-label">Years of Excellence</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">10K+</div>
                <div class="stat-label">Active Students</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">500+</div>
                <div class="stat-label">Expert Faculty</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">100+</div>
                <div class="stat-label">Programs Offered</div>
        </div>
    </div>
    </section>

    <!-- VC Section -->
    <section class="vc-section" id="about">
        <div class="vc-container">
        <div class="vc-photo">
                <div class="vc-photo-wrapper">
                    <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80" alt="Vice Chancellor">
                </div>
            <h3>Prof. Dr. Sibte Ali</h3>
            <p><em>Vice Chancellor</em></p>
        </div>
            <div class="vc-message">
            <h2>Message from the Vice Chancellor</h2>
                <p>At the Learning Management System, we believe that education is not merely the accumulation of facts, but the profound training of the mind to think critically, innovate boldly, and contribute meaningfully to society.</p>
                <p>Our commitment extends beyond traditional learning. We foster an environment where curiosity meets opportunity, where students are encouraged to push boundaries and explore uncharted territories in their fields of study.</p>
                <div class="vc-quote">
                    "Education is not the learning of facts, but the training of the mind to think."
                </div>
        </div>
    </div>
    </section>

    <!-- Gallery Section -->
    <section class="gallery-section">
        <div class="section-header">
            <h2>Campus Life</h2>
            <p>Experience the vibrant community and state-of-the-art facilities that make our university a home for excellence</p>
        </div>
    <div class="gallery">
            <div class="gallery-item">
                <img src="https://images.unsplash.com/photo-1562774053-701939374585?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2028&q=80" alt="Modern Campus View">
            </div>
            <div class="gallery-item">
                <img src="https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80" alt="University Building">
            </div>
            <div class="gallery-item">
                <img src="https://images.unsplash.com/photo-1497486751825-1233686d5d80?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80" alt="Campus Facilities">
            </div>
            <div class="gallery-item">
                <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2071&q=80" alt="Student Collaboration">
            </div>
            <div class="gallery-item">
                <img src="https://images.unsplash.com/photo-1503676260728-1c00da094a0b?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2022&q=80" alt="Academic Excellence">
            </div>
            <div class="gallery-item">
                <img src="https://images.unsplash.com/photo-1531482615713-2afd69097998?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80" alt="Research Facilities">
            </div>
    </div>
    </section>

    <!-- Contact Section -->
    <section class="contact-section" id="contact">
        <div class="contact-container">
            <div class="contact-info">
                <h2>Get in Touch</h2>
                <p>
                    <strong>Address:</strong>
                    <span>University Road, City, Country</span>
                </p>
                <p>
                    <strong>Email:</strong>
                    <span>info@universityoftechnology.edu</span>
                </p>
                <p>
                    <strong>Phone:</strong>
                    <span>+92 123 4567890</span>
                </p>
                <p>
                    <strong>Office Hours:</strong>
                    <span>Monday - Friday, 9:00 AM - 5:00 PM</span>
                </p>
        </div>
            <div class="contact-map">
            <iframe
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2625.337714068924!2d2.292292615674038!3d48.85884407928752!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47e66fdebbe79b09%3A0x4a8531efc8f5b3f5!2sEiffel%20Tower!5e0!3m2!1sen!2s!4v1697823478940"
                allowfullscreen="" loading="lazy"></iframe>
        </div>
    </div>
    </section>

    <!-- Footer -->
    <footer>
        <p>&copy; <?php echo date("Y"); ?> Learning Management System. All rights reserved.</p>
        <p class="highlight">Empowering Minds, Shaping Futures</p>
    </footer>

    <script>
        // Hero Slider
        let currentSlide = 0;
        const slides = document.querySelectorAll('.hero-slide');
        const indicators = document.querySelectorAll('.indicator');

        function showSlide(index) {
            slides.forEach(slide => slide.classList.remove('active'));
            indicators.forEach(indicator => indicator.classList.remove('active'));
            
            slides[index].classList.add('active');
            indicators[index].classList.add('active');
        }

        function nextSlide() {
            currentSlide = (currentSlide + 1) % slides.length;
            showSlide(currentSlide);
        }

        // Auto-advance slides
        setInterval(nextSlide, 5000);

        // Indicator clicks
        indicators.forEach((indicator, index) => {
            indicator.addEventListener('click', () => {
                currentSlide = index;
                showSlide(currentSlide);
            });
        });

        // Navbar scroll effect
        const navbar = document.getElementById('navbar');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>

</html>