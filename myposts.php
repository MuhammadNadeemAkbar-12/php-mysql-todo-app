<?php
session_start();
include 'db_connect.php';

$currentPage = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

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
    $filter_data = trim($_POST['filter_input'] ?? '');
    $filter_data = mysqli_real_escape_string($conn, $filter_data);
}

$user_id = (int)$_SESSION['user_id'];
$userName = $_SESSION['user_name'] ?? '';

// Query build
if (!empty($filter_data)) {
    // Filtered query
    $approvedQuery = "SELECT * FROM tasks WHERE status = 'approved' AND user_id = {$user_id} AND CONCAT(task_name, description) LIKE '%{$filter_data}%' LIMIT {$limit} OFFSET {$offset}";
    $countQuery = "SELECT COUNT(*) as total FROM tasks WHERE status = 'approved' AND user_id = {$user_id} AND CONCAT(task_name, description) LIKE '%{$filter_data}%'";
} else {
    // Normal query
    $approvedQuery = "SELECT * FROM tasks WHERE status = 'approved' AND user_id = {$user_id} LIMIT {$limit} OFFSET {$offset}";
    $countQuery = "SELECT COUNT(*) as total FROM tasks WHERE status = 'approved' AND user_id = {$user_id}";
}

$queryRun = mysqli_query($conn, $approvedQuery);

// Pagination count
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
    <title>My Posts - TaskFlow</title>

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

        /* Hero Section - Enhanced */
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

        .hero-illustration {
            position: relative;
            z-index: 2;
            animation: float 3s ease-in-out infinite;
        }

        .hero-icon-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2rem;
            max-width: 400px;
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
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
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

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            box-shadow: 0 10px 24px rgba(118, 75, 162, 0.25);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #5b70dd, #6b4295);
        }

        /* ...existing task card styles... */
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

        @keyframes bounceHorizontal {
            0%, 100% { transform: translateX(0); }
            50% { transform: translateX(5px); }
        }

        /* Pagination */
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
            .section-title {
                font-size: 2rem;
            }
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
                        <a class="nav-link <?php echo ($currentPage === 'homepage.php') ? 'active' : ''; ?>" href="homepage.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($currentPage === 'myposts.php') ? 'active' : ''; ?>" href="#tasks">My Posts</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
                    </li>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown user-menu">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                               
                                <span class="fw-semibold"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?php echo ($_SESSION['user_role'] === 'admin') ? 'admin-dashboard.php' : 'index.php'; ?>">Dashboard</a></li>
                                <li><a class="dropdown-item" href="homepage.php?logout=1">Logout</a></li>



                                
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link btn-login" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn-register" href="register.php">Register</a>
                        </li>
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
                    <h1>Hello, <?php echo htmlspecialchars($userName); ?>! 👋</h1>
                    <p>Review and manage all your approved posts. Track engagement, views, and comments from the community.</p>
                    <button class="btn btn-cta" onclick="document.getElementById('tasks').scrollIntoView({behavior: 'smooth'})">
                        <i class="fas fa-arrow-down me-2"></i>View My Posts
                    </button>
                </div>
                <div class="col-lg-6 hero-illustration">
                    <div class="hero-icon-grid">
                        <div class="hero-icon-item">
                            <i class="fas fa-blog"></i>
                            <span>My Posts</span>
                        </div>
                        <div class="hero-icon-item">
                            <i class="fas fa-heart"></i>
                            <span>Engagement</span>
                        </div>
                        <div class="hero-icon-item">
                            <i class="fas fa-chart-line"></i>
                            <span>Analytics</span>
                        </div>
                        <div class="hero-icon-item">
                            <i class="fas fa-trophy"></i>
                            <span>Achievements</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Approved Tasks Section -->
    <?php if (mysqli_num_rows($queryRun) > 0): ?>
        <section class="tasks-section" id="tasks">
            <div class="container">
                <h2 class="section-title">📝 Your Approved Posts</h2>
                <p class="section-subtitle">Manage and track your published content</p>

                <!-- Filter -->
                <form method="POST" class="row mb-4 justify-content-center" style="gap:10px;">
                    <div class="col-md-8 col-lg-6">
                        <input type="text" name="filter_input" class="form-control form-control-lg" placeholder="Search your posts..." value="<?php echo htmlspecialchars($filter_data); ?>">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary btn-lg" name="filter_button">
                            <i class="fas fa-search me-2"></i>Search
                        </button>
                    </div>
                </form>

                <div class="row">
                    <?php while ($task = mysqli_fetch_assoc($queryRun)): ?>
                        <div class="col-lg-4 col-md-6">
                            <a href="post_detail.php?id=<?php echo $task['id']; ?>&ref=myposts" style="text-decoration: none; color: inherit;">
                                <div class="task-card">
                                    <?php if (!empty($task['image'])): ?>
                                        <div class="task-thumb">
                                            <img class="task-image" src="<?php echo htmlspecialchars($task['image']); ?>" alt="Task Image">
                                        </div>
                                    <?php else: ?>
                                        <div class="task-thumb"><span class="task-placeholder">No Image</span></div>
                                    <?php endif; ?>

                                    <div class="task-body">
                                        <h5><?php echo htmlspecialchars($task['task_name']); ?></h5>
                                        <p class="task-description">
                                            <?php 
                                                $description = htmlspecialchars($task['description']);
                                                $words = explode(' ', $description);
                                                echo (count($words) > 30) ? implode(' ', array_slice($words, 0, 30)) . '...' : $description;
                                            ?>
                                        </p>
                                        <div class="task-date"><i class="far fa-calendar"></i> <?php echo date('M d, Y', strtotime($task['created_at'])); ?></div>

                                        <div class="like-action mt-2">
                                            <?php
                                                $task_id = $task['id'];
                                                $countQuery = "SELECT COUNT(*) AS total_likes FROM likes WHERE task_id = $task_id";
                                                $countResult = mysqli_query($conn, $countQuery);
                                                $countRow = mysqli_fetch_assoc($countResult);
                                                $totalLikes = $countRow['total_likes'];
                                            ?>
                                            <button type="button" class="btn btn-like" data-task-id="<?php echo $task['id']; ?>">
                                                <i class="fa-regular fa-heart"></i>
                                                <span>Like</span>
                                            </button>
                                            <span class="like-count"><?php echo $totalLikes; ?> Likes</span>
                                        </div>
                                    </div>

                                    <div class="card-cta">
                                        <i class="fas fa-arrow-right"></i>
                                        <span>View details & comments</span>
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
                                        <button type="submit" name="filter_button" class="page-link" formaction="myposts.php?page=<?php echo $i; ?>"><?php echo $i; ?></button>
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
            <div class="container text-center py-5">
                <div style="font-size: 80px; color: #ddd;">
                    <i class="fas fa-inbox"></i>
                </div>
                <h2 class="section-title mt-4">No Approved Posts Yet</h2>
                <p class="section-subtitle">Create your first post and get it approved to see it here!</p>
                <a href="index.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-plus-circle me-2"></i>Create Post
                </a>
            </div>
        </section>
    <?php endif; ?>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="social-icons">
                    <a href="https://facebook.com" target="_blank" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://twitter.com" target="_blank" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="https://instagram.com" target="_blank" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="https://linkedin.com" target="_blank" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    <a href="https://github.com" target="_blank" aria-label="GitHub"><i class="fab fa-github"></i></a>
                    <a href="https://youtube.com" target="_blank" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                </div>
                <p>&copy; 2025 TaskFlow. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
</body>

</html>