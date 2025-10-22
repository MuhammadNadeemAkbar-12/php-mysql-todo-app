
<?php
include 'db_connect.php';
session_start();


$approvedQuery = "SELECT * FROM tasks WHERE status = 'approved'";
$queryRun = mysqli_query($conn, $approvedQuery);
if(mysqli_num_rows($queryRun) > 0){
    while ($rows = mysqli_fetch_assoc($queryRun)){
        // print_r ($rows['id']);
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskFlow - Manage Your Tasks Easily</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            color: #333;
            overflow-x: hidden;
        }
        
        /* Navbar Styles */
        .navbar {
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
            padding: 1rem 0;
            transition: all 0.3s ease;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: #4A90E2;
            transition: color 0.3s ease;
        }
        
        .navbar-brand:hover {
            color: #357ABD;
        }
        
        .nav-link {
            color: #555;
            font-weight: 500;
            margin: 0 0.5rem;
            transition: color 0.3s ease;
        }
        
        .nav-link:hover {
            color: #4A90E2;
        }
        
        .btn-login {
            color: #4A90E2;
            border: 2px solid #4A90E2;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            background: #4A90E2;
            color: white;
        }
        
        .btn-register {
            background: #4A90E2;
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            margin-left: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .btn-register:hover {
            background: #357ABD;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(74, 144, 226, 0.3);
        }
        
        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
            padding: 80px 0 60px;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="2" fill="white" opacity="0.1"/></svg>');
            opacity: 0.3;
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
            animation: fadeInUp 1s ease;
        }
        
        .hero-section h1 {
            font-size: 3.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }
        
        .hero-section p {
            font-size: 1.25rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 2rem;
            max-width: 600px;
        }
        
        .btn-cta {
            background: white;
            color: #667eea;
            padding: 1rem 2.5rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }
        
        .btn-cta:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
            color: #667eea;
        }
        
        /* Approved Tasks Section */
        .tasks-section {
            padding: 80px 0;
            background: #f8f9fa;
        }
        
        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 1rem;
            text-align: center;
        }
        
        .section-subtitle {
            color: #666;
            text-align: center;
            margin-bottom: 3rem;
            font-size: 1.1rem;
        }
        
        .task-card {
            background: white;
            border: none;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            opacity: 0;
            animation: fadeInUp 0.6s ease forwards;
        }
        
        .task-card:nth-child(1) { animation-delay: 0.1s; }
        .task-card:nth-child(2) { animation-delay: 0.2s; }
        .task-card:nth-child(3) { animation-delay: 0.3s; }
        .task-card:nth-child(4) { animation-delay: 0.4s; }
        .task-card:nth-child(5) { animation-delay: 0.5s; }
        .task-card:nth-child(6) { animation-delay: 0.6s; }
        
        .task-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }
        
        .task-badge {
            background: #e3f2fd;
            color: #4A90E2;
            padding: 0.3rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 1rem;
        }
        
        .task-card h5 {
            color: #333;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .task-card p {
            color: #666;
            margin-bottom: 1rem;
            line-height: 1.6;
        }
        
        .task-date {
            color: #999;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
        }
        
        .task-date i {
            margin-right: 0.5rem;
        }
        
        /* About Section */
        .about-section {
            padding: 80px 0;
            background: white;
        }
        
        .about-content {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .about-section p {
            color: #666;
            line-height: 1.8;
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
        }
        
        .feature-list {
            list-style: none;
            padding: 0;
        }
        
        .feature-list li {
            padding: 1rem 0;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
        }
        
        .feature-list li:last-child {
            border-bottom: none;
        }
        
        .feature-list i {
            color: #4A90E2;
            font-size: 1.5rem;
            margin-right: 1rem;
            min-width: 30px;
        }
        
        /* Footer */
        .footer {
            background: #2c3e50;
            color: white;
            padding: 3rem 0 1.5rem;
        }
        
        .footer-content {
            text-align: center;
        }
        
        .social-icons {
            margin: 2rem 0;
        }
        
        .social-icons a {
            color: white;
            font-size: 1.5rem;
            margin: 0 1rem;
            transition: all 0.3s ease;
        }
        
        .social-icons a:hover {
            color: #4A90E2;
            transform: translateY(-3px);
        }
        
        .footer p {
            margin: 0;
            color: rgba(255, 255, 255, 0.7);
        }
        
        /* Animations */
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
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero-section h1 {
                font-size: 2.5rem;
            }
            
            .hero-section p {
                font-size: 1.1rem;
            }
            
            .section-title {
                font-size: 2rem;
            }
            
            .navbar-nav {
                text-align: center;
                padding: 1rem 0;
            }
            
            .btn-login, .btn-register {
                margin: 0.5rem 0;
            }
        }
    </style>
</head>
<body>
    
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-tasks"></i> TaskFlow
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="#home">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn-login" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn-register" href="register.php">Register</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Hero Section -->
    <section class="hero-section" id="home">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 hero-content">
                    <h1>Manage Your Tasks Easily</h1>
                    <p>Stay organized and boost your productivity with our simple and intuitive task management system. Keep track of everything that matters, from daily to-dos to long-term projects.</p>
                    <button class="btn btn-cta">Get Started</button>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Approved Tasks Section -->
     <?php if(mysqli_num_rows($queryRun) > 0):?>
    <section class="tasks-section" id="tasks">
        <div class="container">
            <h2 class="section-title">Approved Tasks</h2>
            <p class="section-subtitle">Recently completed and approved tasks from our community</p>
            
            <div class="row">
                
                <div class="col-lg-4 col-md-6">
                    <div class="task-card">
                        <?php while ($task = mysqli_fetch_assoc($result)):; ?>
                        <span class="task-badge">Approved</span>
                        <h5>Complete Project Proposal</h5>
                        <p>Finalize the quarterly project proposal with budget estimates and timeline for the upcoming development cycle.</p>
                        <div class="task-date">
                            <i class="far fa-calendar"></i>
                            <span>October 20, 2025</span>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="task-card">
                        <span class="task-badge">Approved</span>
                        <h5>Team Meeting Preparation</h5>
                        <p>Prepare presentation slides and documentation for the weekly team sync meeting with stakeholders.</p>
                        <div class="task-date">
                            <i class="far fa-calendar"></i>
                            <span>October 19, 2025</span>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="task-card">
                        <span class="task-badge">Approved</span>
                        <h5>Database Optimization</h5>
                        <p>Review and optimize database queries to improve application performance and reduce load times.</p>
                        <div class="task-date">
                            <i class="far fa-calendar"></i>
                            <span>October 18, 2025</span>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="task-card">
                        <span class="task-badge">Approved</span>
                        <h5>Client Feedback Review</h5>
                        <p>Analyze client feedback from the latest product release and create action items for improvements.</p>
                        <div class="task-date">
                            <i class="far fa-calendar"></i>
                            <span>October 17, 2025</span>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="task-card">
                        <span class="task-badge">Approved</span>
                        <h5>Update Documentation</h5>
                        <p>Update technical documentation with new features and API endpoints for the development team.</p>
                        <div class="task-date">
                            <i class="far fa-calendar"></i>
                            <span>October 16, 2025</span>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="task-card">
                        <span class="task-badge">Approved</span>
                        <h5>Security Audit</h5>
                        <p>Conduct comprehensive security audit of the application and implement necessary patches and updates.</p>
                        <div class="task-date">
                            <i class="far fa-calendar"></i>
                            <span>October 15, 2025</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    
    <!-- About Section -->
    <section class="about-section" id="about">
        <div class="container">
            <h2 class="section-title">About TaskFlow</h2>
            <p class="section-subtitle">Simple, powerful task management for everyone</p>
            
            <div class="about-content">
                <p>TaskFlow is a modern task management platform designed to help individuals and teams stay organized and productive. Our intuitive interface makes it easy to create, track, and complete tasks efficiently.</p>
                
                <p>Whether you're managing personal projects or collaborating with a team, TaskFlow provides the tools you need to succeed. Our approval system ensures quality and accountability in every task.</p>
                
                <ul class="feature-list">
                    <li>
                        <i class="fas fa-check-circle"></i>
                        <span><strong>Easy Task Creation:</strong> Create and organize tasks with just a few clicks</span>
                    </li>
                    <li>
                        <i class="fas fa-users"></i>
                        <span><strong>Team Collaboration:</strong> Work together seamlessly with your team members</span>
                    </li>
                    <li>
                        <i class="fas fa-shield-alt"></i>
                        <span><strong>Approval System:</strong> Built-in approval workflow for quality assurance</span>
                    </li>
                    <li>
                        <i class="fas fa-chart-line"></i>
                        <span><strong>Progress Tracking:</strong> Monitor your productivity and track completion rates</span>
                    </li>
                </ul>
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="social-icons">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-linkedin"></i></a>
                </div>
                <p>&copy; 2025 TaskFlow. All rights reserved.</p>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Smooth scrolling for navigation links
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
        
        // Navbar background on scroll
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.style.background = 'rgba(255, 255, 255, 1)';
            } else {
                navbar.style.background = 'rgba(255, 255, 255, 0.98)';
            }
        });
    </script>
    
</body>
</html>