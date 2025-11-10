<?php
session_start();
include 'db_connect.php';

$currentPage = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));


$limit = 6;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// for logout 
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: homepage.php");
    exit;
}

// Filtering logic
$filter_data = '';
if (isset($_POST['filter_button'])) {
    $filter_data = $_POST['filter_input'];
}

// Query build  
if (!empty($filter_data)) {
    $approvedQuery = "SELECT * FROM tasks WHERE status = 'approved' AND CONCAT(task_name, description) LIKE '%$filter_data%' LIMIT $limit OFFSET $offset";
    $countQuery = "SELECT COUNT(*) as total FROM tasks WHERE status = 'approved' AND CONCAT(task_name, description) LIKE '%$filter_data%'";
} else {
    $approvedQuery = "SELECT * FROM tasks WHERE status = 'approved' LIMIT $limit OFFSET $offset";
    $countQuery = "SELECT COUNT(*) as total FROM tasks WHERE status = 'approved'";
}

$queryRun = mysqli_query($conn, $approvedQuery);
$result = mysqli_query($conn, $countQuery);
$row = mysqli_fetch_assoc($result);
$collectNumRows = $row['total'];
$totalPages = ceil($collectNumRows / $limit);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskFlow - Manage Your Tasks Easily</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts + Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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

        .btn {
            transition: background-color 0.25s ease, color 0.25s ease, box-shadow 0.25s ease, transform 0.25s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12);
        }

        .btn:active {
            transform: translateY(0);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.18);
        }

        .btn:focus-visible {
            outline: 2px solid rgba(102, 126, 234, 0.45);
            outline-offset: 4px;
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
            box-shadow: 0 12px 26px rgba(74, 144, 226, 0.35);
        }

        .btn-register {
            background: #4A90E2;
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            margin-left: 0.5rem;
            transition: all 0.3s ease;
            box-shadow: 0 10px 22px rgba(74, 144, 226, 0.32);
        }

        .btn-register:hover {
            background: #357ABD;
            transform: translateY(-2px);
            box-shadow: 0 14px 30px rgba(74, 144, 226, 0.4);
        }

        .btn-cta {
            background: white;
            color: #667eea;
            padding: 1rem 2.5rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            border: none;
            box-shadow: 0 12px 30px rgba(102, 126, 234, 0.35);
        }

        .btn-cta:hover {
            box-shadow: 0 18px 36px rgba(102, 126, 234, 0.4);
        }

        .btn-cta:active {
            box-shadow: 0 10px 22px rgba(102, 126, 234, 0.28);
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            box-shadow: 0 10px 24px rgba(118, 75, 162, 0.25);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #5b70dd, #6b4295);
        }

        .btn-primary:active {
            background: linear-gradient(135deg, #4d63c7, #5d3883);
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

        /* Hero Illustration */
        .hero-illustration {
            position: relative;
            z-index: 2;
            animation: float 3s ease-in-out infinite;
        }

        .hero-icon-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
            max-width: 500px;
            margin: 0 auto;
        }

        .hero-icon-item {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .hero-icon-item:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        }

        .hero-icon-item i {
            font-size: 3rem;
            color: white;
            margin-bottom: 1rem;
            display: block;
        }

        .hero-icon-item span {
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
            display: block;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-20px);
            }
        }

        /* Features Section */
        .features-section {
            padding: 80px 0;
            background: white;
        }

        .feature-card {
            text-align: center;
            padding: 2.5rem 2rem;
            border-radius: 15px;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
            border: 1px solid rgba(102, 126, 234, 0.1);
            transition: all 0.3s ease;
            height: 100%;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.2);
            border-color: rgba(102, 126, 234, 0.3);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .feature-icon i {
            font-size: 2.5rem;
            color: white;
        }

        .feature-card h4 {
            font-weight: 700;
            color: #333;
            margin-bottom: 1rem;
        }

        .feature-card p {
            color: #666;
            line-height: 1.6;
            margin: 0;
        }

        /* Stats Section */
        .stats-section {
            background: linear-gradient(135deg, #667eea, #764ba2);
            padding: 60px 0;
            color: white;
        }

        .stat-item {
            text-align: center;
            padding: 2rem 1rem;
        }

        .stat-number {
            font-size: 3.5rem;
            font-weight: 700;
            display: block;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 1.1rem;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 1px;
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
            padding: 1.75rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            position: relative;
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            overflow: hidden;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
        }

        .task-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 35px rgba(74, 144, 226, 0.2);
        }

        .task-card::after {
            content: '';
            position: absolute;
            inset: 0;
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 15px;
            pointer-events: none;
        }

        .task-thumb {
            width: 100%;
            aspect-ratio: 16 / 10;
            border-radius: 12px;
            overflow: hidden;
            background: rgba(74, 144, 226, 0.08);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .task-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .task-placeholder {
            width: 100%;
            height: 100%;
            display: grid;
            place-items: center;
            color: #4A90E2;
            font-weight: 600;
            letter-spacing: 0.03em;
        }

        .task-body {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            position: relative;
            z-index: 1;
        }

        .task-description {
            color: #555;
            line-height: 1.6;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .task-date {
            color: #999;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            margin-top: auto;
        }

        .task-date i {
            margin-right: 0.5rem;
        }

        .like-action {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
        }

        .btn-like {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            border-radius: 999px;
            border: 1px solid rgba(102, 126, 234, 0.4);
            background: #fff;
            color: #667eea;
            padding: 0.4rem 1rem;
            font-weight: 600;
        }

        .btn-like:hover {
            background: rgba(102, 126, 234, 0.08);
        }

        .btn-like.active {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff;
            border-color: transparent;
        }

        .like-count {
            background: rgba(102, 126, 234, 0.12);
            color: #4A4A68;
            padding: 0.35rem 0.75rem;
            border-radius: 999px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .card-cta {
            margin-top: 1rem;
            padding: 0.75rem;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.08), rgba(118, 75, 162, 0.08));
            border-radius: 10px;
            text-align: center;
            color: #667eea;
            font-weight: 600;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border: 1px dashed rgba(102, 126, 234, 0.3);
            transition: all 0.3s ease;
        }

        .card-cta i {
            font-size: 1rem;
            animation: bounceHorizontal 1.5s ease-in-out infinite;
        }

        .task-card:hover .card-cta {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.15), rgba(118, 75, 162, 0.15));
            border-color: rgba(102, 126, 234, 0.5);
        }

        .your-post-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 0.4rem 1rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            z-index: 10;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .your-post-badge i {
            font-size: 0.85rem;
        }

        @keyframes bounceHorizontal {

            0%,
            100% {
                transform: translateX(0);
            }

            50% {
                transform: translateX(5px);
            }
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
            margin: 1.5rem 0;
            display: flex;
            justify-content: center;
            gap: 1.5rem;
        }

        .social-icons a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 45px;
            height: 45px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            color: white;
            font-size: 1.2rem;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .social-icons a:hover {
            background: #4A90E2;
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(74, 144, 226, 0.4);
        }

        .footer p {
            margin: 0;
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.95rem;
        }

        /* pagination  */
        .pagination-modern .page-item {
            transition: transform 0.2s ease;
        }

        .pagination-modern .page-item:not(.disabled):hover {
            transform: translateY(-2px);
        }

        .pagination-modern .page-link {
            border: none;
            margin: 0 0.35rem;
            border-radius: 999px;
            padding: 0.6rem 1.1rem;
            background: rgba(102, 126, 234, 0.12);
            color: #4A4A68;
            font-weight: 600;
            box-shadow: 0 5px 15px rgba(74, 144, 226, 0.15);
            transition: all 0.25s ease;
        }

        .pagination-modern .page-link:hover,
        .pagination-modern .page-item.active .page-link {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff;
            box-shadow: 0 8px 20px rgba(118, 75, 162, 0.25);
        }

        .pagination-modern .page-item.disabled .page-link {
            background: rgba(102, 126, 234, 0.06);
            color: #bbb;
            box-shadow: none;
        }


        /* comments  */
        .comments-section {
            background: #f9f9ff;
            border-radius: 10px;
            padding: 1rem;
            box-shadow: inset 0 1px 5px rgba(0, 0, 0, 0.05);
        }

        .comment-list {
            max-height: 150px;
            overflow-y: auto;
            margin-bottom: 10px;
        }

        .comment {
            background: white;
            border-radius: 8px;
            padding: 0.5rem 0.75rem;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .comment strong {
            color: #4A90E2;
            margin-right: 5px;
        }

        .comment-list .comment:hover {
            box-shadow: 0 4px 16px rgba(102, 126, 234, 0.13);
            background: #f6f8ff;
            transition: box-shadow 0.2s, background 0.2s;
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

            .btn-login,
            .btn-register {
                margin: 0.5rem 0;
            }
        }

        .user-menu .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(102, 126, 234, 0.5);
        }

        .user-menu .dropdown-menu {
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            border: none;
            padding: 0.5rem 0;
        }

        .user-menu .dropdown-item {
            font-weight: 500;
        }

        .user-menu .dropdown-item:hover {
            background: rgba(102, 126, 234, 0.08);
        }
    </style>
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="homepage.php">
                <i class="fas fa-tasks"></i> TaskFlow
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($currentPage === 'homepage.php') ? 'active' : ''; ?>" href="#home">Home</a>
                    </li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($currentPage === 'myposts.php') ? 'active' : ''; ?>" href="myposts.php">My Posts</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown user-menu">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                                <span class="fw-semibold"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                            </a>
                            <!-- <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?php echo ($_SESSION['role_id'] == 2) ? 'admin-dashboard.php' : 'index.php'; ?>">Dashboard</a></li>
                                <li><a class="dropdown-item" href="homepage.php?logout=1">Logout</a></li>
                            </ul> -->
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="<?php
                                                                    if ($_SESSION['role_id'] == 1) {

                                                                        echo 'superadmin.php';
                                                                    } elseif ($_SESSION['role_id'] == 2) {
                                                                        echo 'admin-dashboard.php';
                                                                    }elseif ($_SESSION['role_id'] == 3) {
                                                                        echo 'index.php';
                                                                    } 
                                                                    else {
                                                                        echo 'homepage.php';
                                                                    }
                                                                    ?>">
                                        Dashboard
                                    </a>

                                </li>
                                <li><a class="dropdown-item" href="homepage.php?logout=1">Logout</a></li>
                            </ul>

                        </li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link btn-login" href="login.php">Login</a></li>
                        <li class="nav-item"><a class="nav-link btn-register" href="register.php">Register</a></li>
                    <?php endif; ?>
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
                    <p>Stay organized and boost your productivity with our intuitive task management platform. Track, manage, and complete tasks efficiently.</p>
                    <button class="btn btn-cta" onclick="document.getElementById('tasks').scrollIntoView({behavior: 'smooth'})">
                        <i class="fas fa-rocket me-2"></i>Get Started
                    </button>
                </div>
                <div class="col-lg-6 hero-illustration">
                    <div class="hero-icon-grid">
                        <div class="hero-icon-item">
                            <i class="fas fa-clipboard-list"></i>
                            <span>Organize</span>
                        </div>
                        <div class="hero-icon-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Complete</span>
                        </div>
                        <div class="hero-icon-item">
                            <i class="fas fa-users"></i>
                            <span>Collaborate</span>
                        </div>
                        <div class="hero-icon-item">
                            <i class="fas fa-chart-line"></i>
                            <span>Track</span>
                        </div>
                        <div class="hero-icon-item">
                            <i class="fas fa-bell"></i>
                            <span>Notify</span>
                        </div>
                        <div class="hero-icon-item">
                            <i class="fas fa-trophy"></i>
                            <span>Achieve</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Approved Tasks -->
    <?php if (mysqli_num_rows($queryRun) > 0): ?>
        <section class="tasks-section" id="tasks">
            <div class="container">
                <h2 class="section-title">🌟 Trending Blog</h2>

                <!-- Filter -->
                <form method="POST" class="row mb-4 justify-content-center" style="gap:10px;">
                    <div class="col-md-8 col-lg-6">
                        <input type="text" name="filter_input" class="form-control form-control-lg" placeholder="Search/Filter Record" value="<?php echo htmlspecialchars($filter_data); ?>">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary btn-lg" name="filter_button">Filter Data</button>
                    </div>
                </form>

                <div class="row">
                    <?php while ($task = mysqli_fetch_assoc($queryRun)): ?>
                        <div class="col-lg-4 col-md-6">
                            <a href="post_detail.php?id=<?php echo $task['id']; ?>&ref=homepage" style="text-decoration: none; color: inherit;">
                                <div class="task-card">
                                    <?php
                                    // Check if this post belongs to the logged-in user
                                    if (isset($_SESSION['user_id']) && $task['user_id'] == $_SESSION['user_id']):
                                    ?>
                                        <div class="your-post-badge">
                                            <i class="fas fa-user-check"></i>
                                            <span>Your Post</span>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($task['image'])): ?>
                                        <div class="task-thumb">
                                            <img class="task-image" src="<?php echo htmlspecialchars($task['image']); ?>" alt="Task Image">
                                        </div>
                                    <?php else: ?>
                                        <div class="task-thumb"><span class="task-placeholder">No Image</span></div>
                                    <?php endif; ?>

                                    <div class="task-body">
                                        <h5><?php echo htmlspecialchars($task['status']); ?></h5>
                                        <h5><?php echo htmlspecialchars($task['task_name']); ?></h5>
                                        <p class="task-description">
                                            <?php
                                            $description = htmlspecialchars($task['description']);
                                            $words = explode(' ', $description);
                                            if (count($words) > 30) {
                                                echo implode(' ', array_slice($words, 0, 30)) . '...';
                                            } else {
                                                echo $description;
                                            }
                                            ?>
                                        </p>
                                        <div class="task-date"><i class="far fa-calendar"></i> <?php echo htmlspecialchars($task['created_at']); ?></div>

                                        <!-- for likes  -->
                                     

                                        <!-- Comments Section -->

                                    </div>

                                    <!-- Call to Action -->
                                    <div class="card-cta">
                                        <i class="fas fa-arrow-right"></i>
                                        <span>Click to view full details & comments</span>
                                    </div>

                                </div>
                            </a>
                        </div>
                    <?php endwhile; ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <nav aria-label="Task Pagination">
                        <ul class="pagination pagination-modern justify-content-center mt-4">
                            <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
                                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="filter_input" value="<?php echo htmlspecialchars($filter_data); ?>">
                                        <button type="submit" name="filter_button" class="page-link" formaction="homepage.php?page=<?php echo $i; ?>"><?php echo $i; ?></button>
                                    </form>
                                </li>
                            <?php } ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </section>
    <?php else: ?>
        <section class="tasks-section" id="tasks">
            <div class="container">
                <h2 class="section-title">No Approved Tasks</h2>
                <p class="section-subtitle">There are currently no approved tasks to display.</p>
            </div>
        </section>
    <?php endif; ?>

    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <h2 class="section-title">✨ Why Choose TaskFlow?</h2>
            <p class="section-subtitle">Powerful features to help you stay productive</p>
            <div class="row g-4 mt-4">
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <h4>Lightning Fast</h4>
                        <p>Quick and responsive interface that helps you manage tasks without delay</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h4>Secure & Reliable</h4>
                        <p>Your data is protected with enterprise-grade security measures</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h4>Fully Responsive</h4>
                        <p>Access your tasks anytime, anywhere on any device</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-palette"></i>
                        </div>
                        <h4>Beautiful Design</h4>
                        <p>Modern and intuitive interface that makes task management enjoyable</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-sync-alt"></i>
                        </div>
                        <h4>Real-time Sync</h4>
                        <p>All your tasks synced instantly across all platforms</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h4>24/7 Support</h4>
                        <p>Our dedicated team is always here to help you succeed</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row">
                <div class="col-md-3 col-6">
                    <div class="stat-item">
                        <span class="stat-number">
                            <i class="fas fa-users"></i> <?php echo $collectNumRows; ?>+
                        </span>
                        <span class="stat-label">Active Posts</span>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-item">
                        <span class="stat-number">
                            <i class="fas fa-check-circle"></i> 100%
                        </span>
                        <span class="stat-label">Success Rate</span>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-item">
                        <span class="stat-number">
                            <i class="fas fa-clock"></i> 24/7
                        </span>
                        <span class="stat-label">Availability</span>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-item">
                        <span class="stat-number">
                            <i class="fas fa-heart"></i> 99%
                        </span>
                        <span class="stat-label">Satisfaction</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="social-icons">
                    <a href="https://facebook.com" target="_blank" aria-label="Facebook" title="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://twitter.com" target="_blank" aria-label="Twitter" title="Twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="https://instagram.com" target="_blank" aria-label="Instagram" title="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="https://linkedin.com" target="_blank" aria-label="LinkedIn" title="LinkedIn">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                    <a href="https://github.com" target="_blank" aria-label="GitHub" title="GitHub">
                        <i class="fab fa-github"></i>
                    </a>
                    <a href="https://youtube.com" target="_blank" aria-label="YouTube" title="YouTube">
                        <i class="fab fa-youtube"></i>
                    </a>
                </div>
                <p>&copy; 2025 TaskFlow. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>

</body>

</html>