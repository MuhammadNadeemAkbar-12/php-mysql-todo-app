<?php

include 'db_connect.php';
session_start();
include 'middleware.php';
checkRole(['superadmin']);

$user_id = $_SESSION['user_id'];

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}





// admin name email details 
$user_name_query = "SELECT email, name role FROM users WHERE role = 'superadmin' LIMIT 1";
$userResult = mysqli_query($conn, $user_name_query);

$user_name = $_SESSION['user_name'];
if ($userResult && mysqli_num_rows($userResult) > 0) {
    $row = mysqli_fetch_assoc($userResult);
    if (!empty($row['role'])) {
        $user_name = ucfirst($row['role']);
    }
}

// Pagination for Posts
$posts_per_page = 3;
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $posts_per_page;

// Count total posts
if ($_SESSION['user_role'] === 'superadmin') {
    $countQuery = "SELECT COUNT(*) AS total FROM tasks WHERE status IN ('approved', 'pending')";
} else {
    $countQuery = "SELECT COUNT(*) AS total FROM tasks WHERE status = 'approved'";
}
$countResult = mysqli_query($conn, $countQuery);
$totalPosts = mysqli_fetch_assoc($countResult)['total'];
$total_pages = ceil($totalPosts / $posts_per_page);

if ($_SESSION['user_role'] === 'superadmin') {
    $postsQuery = "
        SELECT 
            t.id AS task_id,
            t.task_name,
            t.description,
            t.image,
            t.status,
            u.created_at,
            u.id AS user_id,
            u.name AS author_name
        FROM tasks t
        JOIN users u ON t.user_id = u.id 
        WHERE t.status IN ('approved', 'pending')
        ORDER BY t.created_at DESC
        LIMIT $posts_per_page OFFSET $offset
    ";
} else {
    $postsQuery = "
        SELECT 
            t.id AS task_id,
            t.task_name,
            t.description,
            t,.image,
            t.status,
            t.created_at,
            u.id AS user_id,
            u.name AS author_name
        FROM tasks t
        JOIN users u ON t.user_id = u.id 
        WHERE t.status = 'approved'
        ORDER BY t.created_at DESC
        LIMIT $posts_per_page OFFSET $offset
    ";
}



$result = mysqli_query($conn, $postsQuery);


// all Users 
$fetchAllUsers = "SELECT COUNT(*) AS total_users from users where role = 'user'";
$countedResult = mysqli_query($conn, $fetchAllUsers);
if ($countedResult) {
    $alluserrow =  mysqli_fetch_assoc($countedResult);
}

// all task 
$fetchAllTaskQuery = "SELECT COUNT(*) AS total_task FROM tasks";
$queryResult = mysqli_query($conn, $fetchAllTaskQuery);

if ($queryResult) {
    $Taskrow = mysqli_fetch_assoc($queryResult);
}


// all pending task 
$fetchAllPendingQuery = "SELECT COUNT(*) AS pending_task FROM tasks WHERE status = 'pending'";
$pendingQueryResulty = mysqli_query($conn, $fetchAllPendingQuery);

if ($pendingQueryResulty) {
    $Pendingrow = mysqli_fetch_assoc($pendingQueryResulty);
}

// all approve task 
$fetchAllApproveQuery = "SELECT COUNT(*) AS approve_task FROM tasks WHERE status = 'approved'";
$approveQueryResulty = mysqli_query($conn, $fetchAllApproveQuery);

if ($approveQueryResulty) {
    $Approverow = mysqli_fetch_assoc($approveQueryResulty);
}
// echo $Approverow['approve_task'];


// admin name email details 
$user_name_query = "SELECT email, name role FROM users WHERE role = 'superadmin' LIMIT 1";
$userResult = mysqli_query($conn, $user_name_query);

$user_name = $_SESSION['user_name'];
if ($userResult && mysqli_num_rows($userResult) > 0) {
    $row = mysqli_fetch_assoc($userResult);
    if (!empty($row['role'])) {
        $user_name = ucfirst($row['role']);
    }
}

// Handle Task Delete (Super Admin)
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);

    $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ?");
    $stmt->bind_param("i", $delete_id);

    if ($stmt->execute()) {
        echo "<script> window.location.href='superadmin.php';</script>";
    } else {
        echo "<script>alert('Error deleting task');</script>";
    }

    $stmt->close();
}

// Pagination for Pending Users
$users_per_page = 3;
$current_user_page = isset($_GET['user_page']) ? max(1, intval($_GET['user_page'])) : 1;
$user_offset = ($current_user_page - 1) * $users_per_page;

// Count total pending users
$countPendingUsersQuery = "SELECT COUNT(*) AS total FROM tasks t 
    JOIN users u ON t.user_id = u.id 
    WHERE t.status = 'pending' AND u.role = 'user'";
$countPendingResult = mysqli_query($conn, $countPendingUsersQuery);
$totalPendingUsers = mysqli_fetch_assoc($countPendingResult)['total'];
$total_user_pages = ceil($totalPendingUsers / $users_per_page);

// for approving pending users with pagination
$pendingUsersQuery = "SELECT 
    t.id AS task_id,
    t.task_name,
    t.description,
    t.status,
    t.created_at, 
    u.name AS user_name,
    u.email,
    u.role
FROM tasks t
JOIN users u ON t.user_id = u.id
WHERE t.status = 'pending' AND u.role = 'user'
ORDER BY t.created_at DESC
LIMIT $users_per_page OFFSET $user_offset";
$pendingUsersResult = mysqli_query($conn, $pendingUsersQuery);


// Fetch all admins from the users table WITH PAGINATION
// Step 1: Set how many admins per page (3 admins)
$admins_per_page = 3;

// Step 2: Get current page number from URL, default to page 1
$current_admin_page = isset($_GET['admin_page']) ? max(1, intval($_GET['admin_page'])) : 1;

// Step 3: Calculate offset (skip kitne records)
// Example: Page 1 = offset 0, Page 2 = offset 3, Page 3 = offset 6
$admin_offset = ($current_admin_page - 1) * $admins_per_page;

// Step 4: Count total admins (to calculate total pages)
$count_admins_query = "SELECT COUNT(*) AS total FROM users WHERE role = 'admin'";
$count_admins_result = mysqli_query($conn, $count_admins_query);
$total_admins = mysqli_fetch_assoc($count_admins_result)['total'];

// Step 5: Calculate total pages
// Example: 10 admins / 3 per page = 4 pages (ceiling rounds up)
$total_admin_pages = ceil($total_admins / $admins_per_page);

// Step 6: Fetch only admins for current page using LIMIT and OFFSET
$admin_list = "SELECT id, name, email, created_at, role, status 
               FROM users 
               WHERE role = 'admin' 
               ORDER BY created_at DESC
               LIMIT $admins_per_page OFFSET $admin_offset";
$admin_result = mysqli_query($conn, $admin_list);

// query fofr Reject user post 
if (isset($_POST['reject'])) {
    $task_id = intval($_POST['task_id']);
    // echo "<script>alert(' $task_id');</script>";
    if ($task_id > 0) {
        $update_query = "UPDATE tasks SET status = 'rejected' WHERE id = $task_id AND status = 'pending'";
        $update_result = mysqli_query($conn, $update_query);
        header("Location: superadmin.php");
        exit;
    }
}

// query for approve 
if (isset($_POST['approve'])) {
    $task_id = intval($_POST['task_id']);
    if ($task_id > 0) {
        $update = mysqli_query($conn, "UPDATE tasks SET status = 'approved' WHERE id = $task_id");
        header("Location: superadmin.php");
        exit;
    }
}

// Handle Logout - Destroy session and redirect to homepage
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: homepage.php");
    exit;
}

// for modal register  
if (isset($_POST['addAdminForm'])) {
    // Process form submission
    $admin_name = $_POST['adminName'] ?? '';
    $admin_email = trim($_POST['adminEmail']) ?? '';
    $admin_password = $_POST['adminPassword'] ?? '';

    // Check if email already exists
    $check_email = "SELECT email FROM users WHERE email = '$admin_email'";
    $res = mysqli_query($conn, $check_email);

    if ($res && mysqli_num_rows($res) > 0) {
        echo "<script>alert('Email already exists');</script>";
        exit;
    }

    // Validate inputs (basic example)
    if (!filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email format');</script>";
    } elseif (strlen($admin_password) < 6) {
        echo "<script>alert('Password must be at least 6 characters long');</script>";
    } else {
        // Hash the password
        $hashed_password = password_hash($admin_password, PASSWORD_BCRYPT);

        // Insert new admin into database
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'admin')");
        $stmt->bind_param("sss", $admin_name, $admin_email, $hashed_password);

        if ($stmt->execute()) {
            echo "<script>alert('New admin added successfully');</script>";
            header("Location: superadmin.php");
        } else {
            echo "<script>alert('Error adding admin: " . $stmt->error . "');</script>";
        }

        $stmt->close();
    }
}

// for active or inactive accoutt
if (isset($_POST['activate'])) {
    $admin_id = $_POST['admin_id'];
    $query = "UPDATE users SET status='active' WHERE id='$admin_id'";
    mysqli_query($conn, $query);
    header("Location: superadmin.php");
    exit;
}

if (isset($_POST['deactivate'])) {
    $admin_id = $_POST['admin_id'];
    $query = "UPDATE users SET status='inactive' WHERE id='$admin_id'";
    mysqli_query($conn, $query);
    header("Location: superadmin.php");
    exit;
}

// Bell notification for unread messages
$adminID = $_SESSION['user_id'];
$notifQuery = "SELECT COUNT(*) AS unread_count FROM notifications WHERE user_id = $adminID AND is_read = 0";
$notifResult = mysqli_query($conn, $notifQuery);


// Fetch recent notifications for dropdown
$notificationsListQuery = "
    SELECT 
        n.id, 
        n.message, 
        n.is_read, 
        n.created_at, 
        u.name AS sender_name, 
        u.email AS sender_email
    FROM notifications n
    JOIN users u ON n.user_id = u.id WHERE u.role = 'superadmin' && n.is_read = 0
    ORDER BY n.created_at DESC
    LIMIT 10";
$notificationsListResult = mysqli_query($conn, $notificationsListQuery);



// for all users 
// Pagination setup
$limit = 3;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch all users with pagination
$allusers_query = "SELECT * FROM users ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$allusers_result = mysqli_query($conn, $allusers_query);

// Total users count
$countQuery = "SELECT COUNT(*) as total FROM users";
$countResult = mysqli_query($conn, $countQuery);
$countRow = mysqli_fetch_assoc($countResult);
$totalUsers = $countRow['total'];
$totalPages = ceil($totalUsers / $limit);

// mark as read toggleNotifications
if(isset($_POST['mark_read'])){
    $notif_id = intval($_POST['notif_id']);
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $notif_id, $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: superadmin.php");
    exit;
}




?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Dashboard</title>


    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #0d6efd;
            --accent-color: #4f46e5;
            --bg-muted: #f8fafc;
            --sidebar-width: 260px;
            --card-radius: 1.25rem;
            --card-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
        }

        body {
            background: var(--bg-muted);
            font-family: "Inter", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: #1e293b;
        }

        a {
            text-decoration: none;
        }

        .dashboard {
            min-height: 100vh;
        }

        /* Sidebar */
        #sidebar-toggle {
            display: none;
        }

        .sidebar {
            position: fixed;
            inset: 0 auto 0 0;
            width: var(--sidebar-width);
            background: #fff;
            box-shadow: 1px 0 0 rgba(15, 23, 42, 0.05);
            z-index: 1020;
            transition: transform 0.3s ease;
        }

        .sidebar__header {
            padding: 1.5rem 1.75rem;
            border-bottom: 1px solid rgba(148, 163, 184, 0.2);
        }

        .sidebar__nav {
            padding: 1.25rem 1.5rem;
        }

        .sidebar__nav .nav-link {
            display: flex;
            align-items: center;
            gap: 0.875rem;
            padding: 0.75rem 0.85rem;
            border-radius: 0.85rem;
            color: #475569;
            font-weight: 500;
            transition: background 0.2s ease, color 0.2s ease;
        }

        .sidebar__nav .nav-link:hover {
            background: rgba(79, 70, 229, 0.08);
            color: #111827;
        }

        .sidebar__nav .nav-link.active {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: #fff;
            box-shadow: 0 12px 24px rgba(13, 110, 253, 0.18);
        }

        .sidebar__nav .nav-link .bi {
            font-size: 1.1rem;
        }

        .sidebar__footer {
            padding: 1.5rem;
            border-top: 1px solid rgba(148, 163, 184, 0.2);
        }

        /* Main layout */
        .main {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 1010;
            background: rgba(255, 255, 255, 0.82);
            backdrop-filter: saturate(180%) blur(18px);
            border-bottom: 1px solid rgba(148, 163, 184, 0.25);
        }

        .topbar__content {
            padding: 1.1rem 1.75rem;
        }

        .search-control {
            position: relative;
        }

        .search-control .bi-search {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }

        .search-control input {
            padding-left: 2.75rem;
            border-radius: 0.9rem;
            border: 1px solid rgba(148, 163, 184, 0.35);
            background: #f1f5f9;
            font-size: 0.95rem;
        }

        .search-control input:focus {
            border-color: var(--primary-color);
            box-shadow: none;
            background: #fff;
        }

        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 999px;
            object-fit: cover;
        }

        .stat-card {
            background: #fff;
            border-radius: var(--card-radius);
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            height: 100%;
        }

        .stat-card__icon {
            width: 48px;
            height: 48px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            background: rgba(13, 110, 253, 0.12);
            color: var(--primary-color);
        }

        .stat-card__sparkline {
            width: 100%;
            height: 48px;
        }

        .panel {
            background: #fff;
            border-radius: var(--card-radius);
            box-shadow: var(--card-shadow);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .panel__header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(148, 163, 184, 0.22);
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .panel__body {
            padding: 1.25rem 1.5rem;
        }

        .table {
            vertical-align: middle;
        }

        .table thead th {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            font-weight: 600;
            color: #94a3b8;
            border-bottom-width: 1px;
        }

        .table tbody tr {
            transition: transform 0.18s ease, box-shadow 0.18s ease;
        }


        
        .table tbody tr:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.08);
        }

        .badge-soft {
            padding: 0.45rem 0.85rem;
            border-radius: 999px;
            font-weight: 600;
            font-size: 0.72rem;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }

        .badge-soft[data-status="approved"] {
            background: rgba(34, 197, 94, 0.12);
            color: #15803d;
        }

        .badge-soft[data-status="pending"] {
            background: rgba(250, 204, 21, 0.18);
            color: #b45309;
        }

        .badge-soft[data-status="rejected"] {
            background: rgba(239, 68, 68, 0.12);
            color: #b91c1c;
        }

        .btn-pill {
            border-radius: 999px;
            padding-inline: 1.1rem;
        }

        .btn-action {
            border-radius: 0.75rem;
            padding: 0.4rem 0.9rem;
            font-size: 0.78rem;
            font-weight: 600;
        }

        .btn-action:hover,
        .btn-action:focus {
            transform: translateY(-0.75px);
            box-shadow: 0 10px 18px rgba(13, 110, 253, 0.18);
        }

        .footer {
            margin-top: auto;
            padding: 1.5rem 1.75rem;
            color: #64748b;
            font-size: 0.85rem;
        }

        /* Slide-over placeholder */
        .slide-over {
            position: relative;
            border: 1px dashed rgba(99, 102, 241, 0.4);
            border-radius: 1rem;
            padding: 1.5rem;
            text-align: center;
            color: #4f46e5;
            background: rgba(79, 70, 229, 0.06);
        }

        /* Modal Styling */
        .modal-content {
            border-radius: var(--card-radius);
            border: none;
            box-shadow: 0 20px 60px rgba(15, 23, 42, 0.3);
        }

        .modal-header {
            border-bottom: 1px solid rgba(148, 163, 184, 0.2);
            padding: 1.5rem;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            border-top: 1px solid rgba(148, 163, 184, 0.2);
            padding: 1.25rem 1.5rem;
        }

        /* Responsive */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }

            #sidebar-toggle:checked~.sidebar {
                transform: translateX(0);
                box-shadow: 20px 0 45px rgba(15, 23, 42, 0.15);
            }

            .main {
                margin-left: 0;
            }

            .topbar__content {
                padding: 1rem 1.25rem;
            }
        }

        @media (max-width: 575.98px) {
            .panel__body {
                padding: 1.1rem;
            }

            .panel__header {
                padding: 1.1rem;
            }

            .stat-card {
                padding: 1.25rem;
            }
        }
    </style>
</head>

<body>
    <div class="dashboard">

        <input type="checkbox" id="sidebar-toggle" class="d-none">
        <aside class="sidebar">
            <div class="sidebar__header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <span class="btn btn-sm btn-light rounded-circle text-primary">
                        <i class="bi bi-shield-lock-fill"></i>
                    </span>
                    <div>

                        <h1 class="h5 fw-bold mb-0 text-primary">AdminPanel</h1>
                        <p class="mb-0 text-muted small">Super Admin Console</p>
                    </div>
                </div>
                <label for="sidebar-toggle" class="btn btn-outline-secondary btn-sm d-lg-none">
                    <i class="bi bi-x-lg"></i>
                </label>
            </div>
            <nav class="sidebar__nav">
                <ul class="list-unstyled mb-4">
                    <li class="mb-1">
                        <a href="#overview" class="nav-link active" aria-current="true">
                            <i class="bi bi-speedometer2"></i>
                            Dashboard Overview
                        </a>
                    </li>
                    <li class="mb-1">
                        <a href="#users" class="nav-link">
                            <i class="bi bi-people-fill"></i>
                            Manage Users
                        </a>
                    </li>
                    <li class="mb-1">
                        <a href="#admins" class="nav-link">
                            <i class="bi bi-person-badge-fill"></i>
                            Manage Admins
                        </a>
                    </li>
                    <li class="mb-1">
                        <a href="#posts" class="nav-link">
                            <i class="bi bi-file-earmark-text"></i>
                            Posts
                        </a>
                    </li>
                    <li class="mb-1">
                        <a href="#reports" class="nav-link">
                            <i class="bi bi-clipboard-data"></i>
                            Reports & Logs
                        </a>
                    </li>
                    <li class="mb-1">
                        <a href="#settings" class="nav-link">
                            <i class="bi bi-gear-wide-connected"></i>
                            Site Settings
                        </a>
                    </li>
                    <li class="mb-1">
                        <a href="#backups" class="nav-link">
                            <i class="bi bi-cloud-arrow-down"></i>
                            Backups
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="sidebar__footer">
                <div class="text-center p-3">
                    <p class="small text-muted mb-0">
                        <i class="bi bi-shield-check-fill text-success me-1"></i>
                        Logged in as Super Admin
                    </p>
                </div>
            </div>
        </aside>

        <div class="main">
            <header class="topbar">
                <div class="topbar__content d-flex align-items-center justify-content-between gap-3">

                    <div class="d-flex align-items-center gap-3">
                        <a href="homepage.php" class="btn btn-success">
                            <i class="fas fa-sign-out-alt me-2"></i>Back
                        </a>
                        <label for="sidebar-toggle" class="btn btn-light rounded-3 d-lg-none">
                            <i class="bi bi-list fs-4"></i>
                            <span class="visually-hidden">Toggle sidebar</span>

                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <?php
                        $notifRow = mysqli_fetch_assoc($notifResult);
                        $unreadCount = $notifRow['unread_count'] ?? 0;
                        ?>

                        <!-- Notifications Dropdown -->
                        <div class="dropdown">
                            <button type="button"
                                class="btn btn-light rounded-pill position-relative text-secondary"
                                id="notificationDropdown"
                                data-bs-toggle="dropdown"
                                aria-expanded="false"
                                aria-label="Notifications">
                                <i class="bi bi-bell"></i>
                                <?php if ($unreadCount > 0): ?>
                                    <span class="position-absolute top-0 start-100 translate-middle badge bg-danger rounded-pill">
                                        <?php echo htmlspecialchars($unreadCount); ?>
                                    </span>
                                <?php endif; ?>
                            </button>

                            <!-- Notification Dropdown Menu -->
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2"
                                aria-labelledby="notificationDropdown"
                                style="min-width: 380px; max-width: 450px; border-radius: 1rem; max-height: 500px; overflow-y: auto;">

                                <!-- Header -->
                                <li class="px-3 py-2 border-bottom">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0 fw-bold">
                                            <i class="bi bi-bell-fill text-primary me-2"></i>Notifications
                                        </h6>
                                        <?php if ($unreadCount > 0): ?>
                                            <span class="badge bg-danger rounded-pill"><?php echo $unreadCount; ?> New</span>
                                        <?php endif; ?>
                                    </div>
                                </li>

                                <!-- Notification List -->
                                <?php if (mysqli_num_rows($notificationsListResult) > 0): ?>
                                    <?php while ($notification = mysqli_fetch_assoc($notificationsListResult)): ?>
                                        <li>
                                            <div class="dropdown-item py-3 px-3 <?php echo $notification['is_read'] == 0 ? 'bg-light' : ''; ?>"
                                                style="border-left: 3px solid <?php echo $notification['is_read'] == 0 ? '#0d6efd' : 'transparent'; ?>;">
                                                <div class="d-flex gap-3">
                                                    <!-- Sender Avatar -->
                                                    <?php
                                                    $senderName = $notification['sender_name'] ?? 'Unknown';
                                                    $senderInitials = strtoupper(substr($senderName, 0, 2));
                                                    $avatarColor = $notification['is_read'] == 0 ? '#0d6efd' : '#6c757d';
                                                    ?>
                                                    <div class="flex-shrink-0">
                                                        <img
                                                            src="data:image/svg+xml,<?php echo rawurlencode("
                                                                <svg xmlns='http://www.w3.org/2000/svg' width='40' height='40'>
                                                                    <rect width='40' height='40' rx='20' fill='{$avatarColor}'/>
                                                                    <text x='50%' y='50%' dominant-baseline='middle' text-anchor='middle' fill='white' font-size='16'>{$senderInitials}</text>
                                                                </svg>
                                                            "); ?>"
                                                            alt="Avatar" width="40" height="40">
                                                    </div>

                                                    <!-- Notification Content -->
                                                    <div class="flex-grow-1">
                                                        <div class="fw-semibold small mb-1">
                                                            <?php echo htmlspecialchars($senderName); ?>
                                                            <?php if ($notification['is_read'] == 0): ?>
                                                                <span class="badge bg-primary badge-sm ms-1">New</span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <p class="mb-1 text-muted small">
                                                            <?php echo htmlspecialchars($notification['message']); ?>
                                                        </p>
                                                        <div class="d-flex align-items-center justify-content-between gap-2">
                                                            <small class="text-muted">
                                                                <i class="bi bi-clock me-1"></i>
                                                                <?php echo date('d M Y, h:i A', strtotime($notification['created_at'])); ?>
                                                            </small>
                                                            
                                                            <!-- Mark as Read Button -->
                                                            <?php if ($notification['is_read'] == 0): ?>
                                                                <form method="POST" action="" class="d-inline">
                                                                    <input type="hidden" name="notif_id" value="<?php echo $notification['id']; ?>">
                                                                    <button type="submit" 
                                                                            name="mark_read" 
                                                                            class="btn btn-sm btn-outline-primary rounded-pill px-2 py-0"
                                                                            style="font-size: 0.7rem;">
                                                                        <i class="bi bi-check2 me-1"></i>Mark as Read
                                                                    </button>
                                                                </form>
                                                            <?php endif; ?>
                                                               
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>

                                    <?php endwhile; ?>

                                    <!-- View All Button -->
                                    <!-- <li class="border-top mt-2">
                                        <a class="dropdown-item text-center py-2 fw-semibold text-primary" href="#">
                                            <i class="bi bi-arrow-right-circle me-1"></i>View All Notifications
                                        </a>
                                    </li> -->
                                <?php else: ?>
                                    <!-- No Notifications -->
                                    <li class="text-center py-5">
                                        <i class="bi bi-bell-slash text-muted" style="font-size: 3rem;"></i>
                                        <p class="text-muted mt-2 mb-0">No notifications yet</p>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>

                        <!-- User Profile Dropdown with Logout -->
                        <div class="dropdown">
                            <button class="btn btn-light rounded-pill d-flex align-items-center gap-2 dropdown-toggle"
                                type="button"
                                id="userDropdown"
                                data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <?php
                                // User name ka first letter (uppercase)
                                $firstLetter = strtoupper(substr($user_name, 0, 2));
                                ?>
                                <img
                                    src="data:image/svg+xml,<?php
                                                            echo rawurlencode("
                    <svg xmlns='http://www.w3.org/2000/svg' width='32' height='32'>
                        <rect width='32' height='32' rx='16' fill='#4f46e5'/>
                        <text x='50%' y='50%' dominant-baseline='middle' text-anchor='middle' fill='white' font-size='14'>{$firstLetter}</text>
                    </svg>
                ");
                                                            ?>"
                                    alt="<?php echo htmlspecialchars($user_name); ?> avatar"
                                    width="32" height="32">
                                <div class="d-none d-sm-block text-start">
                                    <div class="fw-semibold small">
                                        <?php echo htmlspecialchars($user_name); ?>
                                    </div>
                                    <div class="text-muted small">
                                        Super Admin
                                    </div>
                                </div>
                            </button>

                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2"
                                aria-labelledby="userDropdown"
                                style="min-width: 250px; border-radius: 1rem;">
                                <li class="px-3 py-2 border-bottom">
                                    <div class="d-flex align-items-center gap-2">
                                        <img
                                            src="data:image/svg+xml,<?php
                                                                    echo rawurlencode("
                                <svg xmlns='http://www.w3.org/2000/svg' width='40' height='40'>
                                    <rect width='40' height='40' rx='20' fill='#4f46e5'/>
                                    <text x='50%' y='50%' dominant-baseline='middle' text-anchor='middle' fill='white' font-size='16'>{$firstLetter}</text>
                                </svg>
                            ");
                                                                    ?>"
                                            alt="Avatar" width="40" height="40">
                                        <div class="flex-grow-1">
                                            <div class="fw-bold"><?php echo htmlspecialchars($user_name); ?></div>
                                            <div class="small text-muted"><?php echo htmlspecialchars($row['email']); ?></div>
                                        </div>
                                    </div>
                                </li>
                                <li><a class="dropdown-item py-2" href="#overview">
                                        <i class="bi bi-speedometer2 me-2"></i>Dashboard
                                    </a></li>
                                <li><a class="dropdown-item py-2" href="#settings">
                                        <i class="bi bi-gear me-2"></i>Settings
                                    </a></li>
                                <li><a class="dropdown-item py-2" href="homepage.php">
                                        <i class="bi bi-house me-2"></i>Go to Homepage
                                    </a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <form method="POST" action="" class="px-2">
                                        <button type="submit" name="logout" class="btn btn-danger w-100 btn-pill">
                                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-grow-1">
                <section id="overview" class="container-fluid py-4">
                    <h2 class="h4 fw-bold mb-4">Dashboard Overview</h2>
                    <div class="row g-4">
                        <div class="col-12 col-sm-6 col-xl-3">
                            <div class="stat-card">
                                <div class="d-flex align-items-start justify-content-between mb-3">
                                    <div>
                                        <p class="text-muted text-uppercase small mb-1">Total Users</p>
                                        <h3 class="h2 fw-bold mb-0">
                                            <?php echo htmlspecialchars($alluserrow['total_users']) ?>
                                        </h3>
                                    </div>
                                    <span class="stat-card__icon">
                                        <i class="bi bi-people-fill"></i>
                                    </span>
                                </div>
                                <p class="text-success small fw-semibold mb-2">
                                    <i class="bi bi-arrow-up-right"></i>
                                    12% from last month
                                </p>
                                <svg class="stat-card__sparkline" viewBox="0 0 120 48" role="img" aria-label="User growth sparkline">
                                    <polyline fill="transparent" stroke="#0d6efd" stroke-width="3" points="0,35 20,28 45,30 65,18 90,14 115,20" stroke-linecap="round" stroke-linejoin="round"></polyline>
                                </svg>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-xl-3">
                            <div class="stat-card">
                                <div class="d-flex align-items-start justify-content-between mb-3">
                                    <div>
                                        <p class="text-muted text-uppercase small mb-1">Pending Approvals</p>
                                        <h3 class="h2 fw-bold mb-0">
                                            <?php echo $Pendingrow['pending_task']; ?>
                                        </h3>
                                    </div>
                                    <span class="stat-card__icon" style="background:rgba(244, 189, 72, 0.16); color:#d97706;">
                                        <i class="bi bi-hourglass-split"></i>
                                    </span>
                                </div>
                                <p class="text-warning small fw-semibold mb-2">
                                    <i class="bi bi-exclamation-circle"></i>
                                    Needs attention
                                </p>
                                <svg class="stat-card__sparkline" viewBox="0 0 120 48" aria-label="Approvals trend">
                                    <polyline fill="transparent" stroke="#d97706" stroke-width="3" points="0,15 20,20 40,22 60,30 80,28 100,33 120,26" stroke-linecap="round" stroke-linejoin="round"></polyline>
                                </svg>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-xl-3">
                            <div class="stat-card">
                                <div class="d-flex align-items-start justify-content-between mb-3">
                                    <div>
                                        <p class="text-muted text-uppercase small mb-1">Total Posts</p>
                                        <h3 class="h2 fw-bold mb-0">
                                            <?php echo $Taskrow['total_task']; ?>
                                        </h3>
                                    </div>
                                    <span class="stat-card__icon" style="background:rgba(34,197,94,0.14); color:#166534;">
                                        <i class="bi bi-journal-richtext"></i>
                                    </span>
                                </div>
                                <p class="text-success small fw-semibold mb-2">
                                    <i class="bi bi-arrow-up-right"></i>
                                    8% increase
                                </p>
                                <svg class="stat-card__sparkline" viewBox="0 0 120 48" aria-label="Posts trend">
                                    <polyline fill="transparent" stroke="#22c55e" stroke-width="3" points="0,30 25,26 50,32 75,20 100,22 120,16" stroke-linecap="round" stroke-linejoin="round"></polyline>
                                </svg>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-xl-3">
                            <div class="stat-card">
                                <div class="d-flex align-items-start justify-content-between mb-3">
                                    <div>
                                        <p class="text-muted text-uppercase small mb-1">Post on home page</p>
                                        <h3 class="h2 fw-bold mb-0">
                                            <?php echo $Approverow['approve_task']; ?>
                                        </h3>
                                    </div>
                                    <span class="stat-card__icon" style="background:rgba(79,70,229,0.16); color:#4f46e5;">
                                        <i class="bi bi-person-plus"></i>
                                    </span>
                                </div>
                                <p class="text-muted small fw-semibold mb-2">
                                    <i class="bi bi-calendar-week"></i>
                                    <!-- PHP: echo date('M d, Y'); -->
                                    Nov 03, 2025
                                </p>
                                <svg class="stat-card__sparkline" viewBox="0 0 120 48" aria-label="Daily signups trend">
                                    <polyline fill="transparent" stroke="#4f46e5" stroke-width="3"
                                        points="0,22 25,15 45,18 70,12 95,14 118,10"
                                        stroke-linecap="round" stroke-linejoin="round"></polyline>
                                </svg>

                            </div>
                        </div>
                </section>

                <section id="users" class="container-fluid pb-4">
                    <div class="panel">
                        <div class="panel__header">
                            <div class="d-flex align-items-center gap-2">
                                <span class="btn btn-sm btn-light rounded-circle text-warning">
                                    <i class="bi bi-clock-history"></i>
                                </span>
                                <div>
                                    <h3 class="h5 fw-semibold mb-0">Pending User Approvals</h3>
                                    <p class="mb-0 text-muted small">Review and manage newly registered accounts.</p>
                                </div>
                            </div>
                          

                        </div>
                        <div class="panel__body p-0">
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th scope="col">User</th>
                                            <th scope="col">Email</th>
                                            <th scope="col">Role</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Registered At</th>
                                            <th scope="col" class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- PHP: loop pending users as $user -->

                                        <?php if (mysqli_num_rows($pendingUsersResult) > 0) : ?>
                                            <?php while ($userspending = mysqli_fetch_assoc($pendingUsersResult) ) : ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <?php
                                                            $name = $userspending['user_name'] ?? '';

                                                            $words = explode(' ', trim($name));

                                                            // Get initials (first letters of first two words)
                                                            if (count($words) >= 2) {
                                                                $initials = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
                                                            } else {
                                                                $initials = strtoupper(substr($name, 0, 2));
                                                            }

                                                            $color = '#06b6d4';
                                                            ?>

                                                            <!-- Avatar with initials -->
                                                            <img
                                                                src="data:image/svg+xml,<?php echo rawurlencode("<svg xmlns='http://www.w3.org/2000/svg' width='36' height='36'><rect width='36' height='36' rx='18' fill='{$color}'/><text x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' fill='white' font-size='14'>{$initials}</text></svg>"); ?>"
                                                                alt="<?php echo htmlspecialchars($name); ?> avatar"
                                                                class="rounded-circle">

                                                            <!-- User Info -->
                                                            <div>
                                                                <strong><?php echo htmlspecialchars($userspending['user_name']); ?></strong>
                                                                <div class="text-muted small">ID: <?php echo $userspending['task_id']; ?></div>
                                                            </div>
                                                        </div>
                                                    </td>

                                                    <td><?php echo $userspending['email'] ?></td>
                                                    <td><span class="badge bg-light text-primary fw-semibold"><?php echo $userspending['role'] ?></span></td>
                                                    <td><span class="badge-soft" data-status="pending"><i class="bi bi-clock"></i><?php echo htmlspecialchars($userspending['status']) ?></span></td>
                                                    <td><?php echo date('d M Y', strtotime($userspending['created_at'])); ?></td>
                                                    <td>
                                                        <div class="d-flex justify-content-end gap-2">
                                                            <form method="POST" action="">
                                                                <input type="hidden" name="task_id" value="<?php echo $userspending['task_id']; ?>">
                                                                <button type="submit" name="approve" class="btn btn-success btn-action">
                                                                    <i class="bi bi-check-circle me-1"></i>Approve
                                                                </button>
                                                                <input type="hidden" name="task_id" value="<?php echo $userspending['task_id']; ?>">
                                                                <button type="submit" name="reject" class="btn btn-warning btn-action text-dark">
                                                                    <i class="bi bi-x-circle me-1"></i>Reject
                                                                </button>
                                                            </form>



                                                        </div>
                                                    </td>
                                                </tr>

                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">No posts found.</td>
                                            </tr>
                                        <?php endif; ?>
                                        <!-- TODO: inject users table rows here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="panel__body border-top">
                            <nav aria-label="Pending users pagination">
                                <ul class="pagination pagination-sm justify-content-center mb-0 gap-1">
                                    <?php if ($current_user_page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link rounded-pill" href="?user_page=<?php echo $current_user_page - 1; ?>#users">Previous</a>
                                        </li>
                                    <?php else: ?>
                                        <li class="page-item disabled">
                                            <span class="page-link rounded-pill">Previous</span>
                                        </li>
                                    <?php endif; ?>

                                    <?php for ($i = 1; $i <= $total_user_pages; $i++): ?>
                                        <li class="page-item <?php echo ($i == $current_user_page) ? 'active' : ''; ?>">
                                            <a class="page-link rounded-pill" href="?user_page=<?php echo $i; ?>#users">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if ($current_user_page < $total_user_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link rounded-pill" href="?user_page=<?php echo $current_user_page + 1; ?>#users">Next</a>
                                        </li>
                                    <?php else: ?>
                                        <li class="page-item disabled">
                                            <span class="page-link rounded-pill">Next</span>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                            <p class="text-muted small mb-0 mt-2 text-center">
                                Showing <?php echo min($user_offset + 1, $totalPendingUsers); ?> to <?php echo min($user_offset + $users_per_page, $totalPendingUsers); ?> of <?php echo $totalPendingUsers; ?> pending users
                            </p>
                        </div>
                    </div>
                </section>

                <section id="admins" class="container-fluid pb-4">
                    <div class="panel">
                        <div class="panel__header">
                            <div class="d-flex align-items-center gap-2">
                                <span class="btn btn-sm btn-light rounded-circle text-primary">
                                    <i class="bi bi-person-badge-fill"></i>
                                </span>
                                <div>
                                    <h3 class="h5 fw-semibold mb-0">Manage Administrators</h3>
                                    <p class="mb-0 text-muted small">Control internal access and permissions.</p>
                                </div>
                            </div>
                            <!-- Button trigger modal -->
                            <a href="#" class="btn btn-primary btn-pill btn-sm" data-bs-toggle="modal" data-bs-target="#addAdminModal">
                                <i class="bi bi-plus-lg me-2"></i>Add Admin
                            </a>

                            <!-- Modal -->
                            <div class="modal fade" id="addAdminModal" tabindex="-1" aria-labelledby="addAdminModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">

                                        <!-- Modal Header -->
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="addAdminModalLabel">Add New Admin</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>

                                        <!-- Modal Body -->
                                        <!-- getting all modal body form fields properly aligned -->
                                        <?php



                                        ?>

                                        <div class="modal-body">
                                            <form method="POST" id="addAdminForm" autocomplete="off">
                                                <div class="mb-3">
                                                    <label for="adminName" class="form-label">Name</label>
                                                    <input type="text" class="form-control" id="adminName" name="adminName" placeholder="Enter name" required autocomplete="off">
                                                </div>
                                                <div class="mb-3">
                                                    <label for="adminEmail" class="form-label">Email</label>
                                                    <input type="email" class="form-control" id="adminEmail" name="adminEmail" placeholder="Enter email" required autocomplete="new-password">
                                                </div>
                                                <div class="mb-3">
                                                    <label for="adminPassword" class="form-label">Password</label>
                                                    <input type="password" class="form-control" id="adminPassword" name="adminPassword" placeholder="Enter password" required>
                                                </div>
                                                <!-- Modal Footer -->
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    <button type="submit" name="addAdminForm" class="btn btn-primary">Create Admin</button>
                                                </div>
                                            </form>
                                        </div>



                                    </div>
                                </div>
                            </div>



                        </div>

                        <!-- for admins  -->
                        <div class="panel__body p-0">
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th scope="col">Admin</th>
                                            <th scope="col">Email</th>
                                            <th scope="col">Role</th>
                                            <th scope="col">Last Login</th>
                                            <th scope="col">Status</th>

                                            <th scope="col" class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- PHP: loop admins as $admin -->
                                        <?php if (mysqli_num_rows($admin_result) > 0) : ?>
                                            <?php while ($admin = mysqli_fetch_assoc($admin_result)) : ?>
                                                <?php
                                                // Get admin name safely
                                                $name = $admin['name'] ?? '';
                                                $email = $admin['email'] ?? '';
                                                // $role = $admin['role'] ?? '';




                                                // Generate initials (first 2 words)
                                                $words = explode(' ', trim($name));
                                                if (count($words) >= 2) {
                                                    $initials = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
                                                } else {
                                                    $initials = strtoupper(substr($name, 0, 2));
                                                }

                                                // Avatar color
                                                $color = '#2563eb';
                                                ?>

                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <!-- Avatar with initials -->
                                                            <img
                                                                src="data:image/svg+xml,<?php echo rawurlencode("
                            <svg xmlns='http://www.w3.org/2000/svg' width='36' height='36'>
                                <rect width='36' height='36' rx='18' fill='{$color}'/>
                                <text x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' fill='white' font-size='14'>{$initials}</text>
                            </svg>
                        "); ?>"
                                                                alt="<?php echo htmlspecialchars($name); ?> avatar"
                                                                class="rounded-circle">

                                                            <!-- Admin Info -->
                                                            <div>
                                                                <strong><?php echo htmlspecialchars($name); ?></strong>
                                                                <div class="text-muted small">Lead Administrator</div>
                                                            </div>
                                                        </div>
                                                    </td>

                                                    <td><?php echo htmlspecialchars($email); ?></td>
                                                    <td>
                                                        <span class="badge bg-danger-subtle text-danger fw-semibold">
                                                            <?php echo htmlspecialchars($admin['role']); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo date('d M Y', strtotime($admin['created_at'])); ?></td>
                                                    <td><?php echo ucfirst(htmlspecialchars($admin['status'])); ?></td>




                                                    <td>
                                                        <?php if ($admin['status'] == 'active') { ?>
                                                            <form method="POST" action="">
                                                                <input type="hidden" name="admin_id" value="<?= $admin['id']; ?>">
                                                                <button type="submit" name="deactivate" class="btn btn-warning btn-sm">Deactivate</button>

                                                            </form>
                                                        <?php } else { ?>
                                                            <form method="POST" action="">
                                                                <input type="hidden" name="admin_id" value="<?= $admin['id']; ?>">
                                                                <button type="submit" name="activate" class="btn btn-success btn-sm">Activate</button>
                                                            </form>
                                                        <?php } ?>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">No admins found.</td>
                                            </tr>
                                        <?php endif; ?>

                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Admin Pagination Section -->
                        <div class="panel__body border-top">
                            <nav aria-label="Admins pagination">
                                <ul class="pagination pagination-sm justify-content-center mb-0 gap-1">
                                    <!-- Previous Button -->
                                    <?php if ($current_admin_page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link rounded-pill"
                                                href="?admin_page=<?php echo $current_admin_page - 1; ?>#admins">
                                                Previous
                                            </a>
                                        </li>
                                    <?php else: ?>
                                        <li class="page-item disabled">
                                            <span class="page-link rounded-pill">Previous</span>
                                        </li>
                                    <?php endif; ?>

                                    <!-- Page Numbers -->
                                    <?php for ($i = 1; $i <= $total_admin_pages; $i++): ?>
                                        <li class="page-item <?php echo ($i == $current_admin_page) ? 'active' : ''; ?>">
                                            <a class="page-link rounded-pill"
                                                href="?admin_page=<?php echo $i; ?>#admins">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>

                                    <!-- Next Button -->
                                    <?php if ($current_admin_page < $total_admin_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link rounded-pill"
                                                href="?admin_page=<?php echo $current_admin_page + 1; ?>#admins">
                                                Next
                                            </a>
                                        </li>
                                    <?php else: ?>
                                        <li class="page-item disabled">
                                            <span class="page-link rounded-pill">Next</span>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>

                            <!-- Show current range of admins -->
                            <p class="text-muted small mb-0 mt-2 text-center">
                                Showing <?php echo min($admin_offset + 1, $total_admins); ?>
                                to <?php echo min($admin_offset + $admins_per_page, $total_admins); ?>
                                of <?php echo $total_admins; ?> administrators
                            </p>
                        </div>
                    </div>
                </section>

                <!-- All Registered Users Section -->
                <section id="all-users" class="container-fluid pb-4">
                    <div class="panel">
                        <!-- Header -->
                        <div class="panel__header">
                            <div class="d-flex align-items-center gap-2">
                                <span class="btn btn-sm btn-light rounded-circle text-primary">
                                    <i class="bi bi-people-fill"></i>
                                </span>
                                <div>
                                    <h3 class="h5 fw-semibold mb-0">All Registered Users</h3>
                                    <p class="mb-0 text-muted small">View and manage all registered users in the system.</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-primary-subtle text-primary px-3 py-2">
                                    <i class="bi bi-person-check-fill me-1"></i>
                                    <?php echo $totalUsers; ?> Total Users
                                </span>
                            </div>
                        </div>

                        <!-- Users Table -->
                        <div class="panel__body p-0">
                            <?php if (mysqli_num_rows($allusers_result) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th scope="col">User</th>
                                                <th scope="col">Email</th>
                                                <th scope="col">Role</th>
                                                <th scope="col">Registered</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($user = mysqli_fetch_assoc($allusers_result)): ?>
                                                <?php
                                                // Generate initials
                                                $userName = $user['name'] ?? '';
                                                $words = explode(' ', trim($userName));
                                                if (count($words) >= 2) {
                                                    $userInitials = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
                                                } else {
                                                    $userInitials = strtoupper(substr($userName, 0, 2));
                                                }

                                                // Avatar color based on role
                                                if ($user['role'] === 'superadmin') {
                                                    $avatarColor = '#9333ea';
                                                } elseif ($user['role'] === 'admin') {
                                                    $avatarColor = '#dc2626';
                                                } else {
                                                    $avatarColor = '#2563eb';
                                                }
                                                ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <!-- Avatar with initials -->
                                                            <img
                                                                src="data:image/svg+xml,<?php echo rawurlencode("
                                                                    <svg xmlns='http://www.w3.org/2000/svg' width='36' height='36'>
                                                                        <rect width='36' height='36' rx='18' fill='{$avatarColor}'/>
                                                                        <text x='50%' y='50%' dominant-baseline='middle' text-anchor='middle' fill='white' font-size='14'>{$userInitials}</text>
                                                                    </svg>
                                                                "); ?>"
                                                                alt="<?php echo htmlspecialchars($userName); ?> avatar"
                                                                class="rounded-circle">

                                                            <!-- User Info -->
                                                            <div>
                                                                <strong><?php echo htmlspecialchars($user['name']); ?></strong>
                                                                <div class="text-muted small">
                                                                    ID: <?php echo $user['id']; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>

                                                    <td>
                                                        <i class="bi bi-envelope text-muted me-1"></i>
                                                        <?php echo htmlspecialchars($user['email']); ?>
                                                    </td>

                                                    <td>
                                                        <?php if ($user['role'] === 'superadmin'): ?>
                                                            <span class="badge bg-purple-subtle text-purple fw-semibold" style="background: rgba(168, 85, 247, 0.1); color: #9333ea;">
                                                                <i class="bi bi-shield-fill-check me-1"></i>Super Admin
                                                            </span>
                                                        <?php elseif ($user['role'] === 'admin'): ?>
                                                            <span class="badge bg-danger-subtle text-danger fw-semibold">
                                                                <i class="bi bi-shield-fill me-1"></i>Admin
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="badge bg-primary-subtle text-primary fw-semibold">
                                                                <i class="bi bi-person-fill me-1"></i>User
                                                            </span>
                                                        <?php endif; ?>
                                                    </td>

                                                    <td>
                                                        <i class="bi bi-calendar3 text-muted me-1"></i>
                                                        <span class="small text-muted">
                                                            <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination -->
                                <?php if ($totalPages > 1): ?>
                                    <div class="panel__body border-top">
                                        <nav aria-label="Users pagination">
                                            <ul class="pagination pagination-sm justify-content-center mb-0 gap-1">
                                                <!-- Previous Button -->
                                                <?php if ($page > 1): ?>
                                                    <li class="page-item">
                                                        <a class="page-link rounded-pill" 
                                                           href="?page=<?php echo $page - 1; ?>#all-users">
                                                            Previous
                                                        </a>
                                                    </li>
                                                <?php else: ?>
                                                    <li class="page-item disabled">
                                                        <span class="page-link rounded-pill">Previous</span>
                                                    </li>
                                                <?php endif; ?>

                                                <!-- Page Numbers -->
                                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                                        <a class="page-link rounded-pill" 
                                                           href="?page=<?php echo $i; ?>#all-users">
                                                            <?php echo $i; ?>
                                                        </a>
                                                    </li>
                                                <?php endfor; ?>

                                                <!-- Next Button -->
                                                <?php if ($page < $totalPages): ?>
                                                    <li class="page-item">
                                                        <a class="page-link rounded-pill" 
                                                           href="?page=<?php echo $page + 1; ?>#all-users">
                                                            Next
                                                        </a>
                                                    </li>
                                                <?php else: ?>
                                                    <li class="page-item disabled">
                                                        <span class="page-link rounded-pill">Next</span>
                                                    </li>
                                                <?php endif; ?>
                                            </ul>
                                        </nav>

                                        <!-- Pagination Info -->
                                        <p class="text-muted small mb-0 mt-2 text-center">
                                            Showing <?php echo min($offset + 1, $totalUsers); ?> 
                                            to <?php echo min($offset + $limit, $totalUsers); ?> 
                                            of <?php echo $totalUsers; ?> users
                                        </p>
                                    </div>
                                <?php endif; ?>

                            <?php else: ?>
                                <!-- Empty State -->
                                <div class="text-center py-5">
                                    <div class="mb-4">
                                        <i class="bi bi-people text-muted" style="font-size: 4rem;"></i>
                                    </div>
                                    <h3 class="h5 fw-bold mb-2">No Users Found</h3>
                                    <p class="text-muted mb-0">
                                        There are no registered users in the system yet.
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>

                <!-- for posts  -->
                <section id="posts" class="container-fluid pb-4">
                    <div class="panel">
                        <div class="panel__header">
                            <div class="d-flex align-items-center gap-2">
                                <span class="btn btn-sm btn-light rounded-circle text-success">
                                    <i class="bi bi-file-earmark-text"></i>
                                </span>
                                <div>
                                    <h3 class="h5 fw-semibold mb-0">Posts Management</h3>
                                    <p class="mb-0 text-muted small">Review, publish, or archive content submissions.</p>
                                </div>
                            </div>
                            <form class="d-flex gap-2 align-items-center">
                                <label class="text-muted small" for="post-status-filter">Status</label>
                                <select id="post-status-filter" class="form-select form-select-sm">
                                    <option selected>All Status</option>

                                </select>
                                <!-- PHP: filter posts by status -->
                            </form>
                        </div>
                        <div class="panel__body p-0">
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th scope="col">Title</th>
                                            <th scope="col">Author</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Published</th>
                                            <th scope="col" class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (mysqli_num_rows($result) > 0): ?>
                                            <?php while ($post = mysqli_fetch_assoc($result)): ?>
                                                <tr>

                                                    <td>
                                                        <strong><?php echo htmlspecialchars($post['task_name']); ?></strong>
                                                        <p class="text-muted small mb-0">
                                                            <?php echo htmlspecialchars(substr($post['description'], 0, 50)) . '...'; ?>
                                                        </p>
                                                    </td>

                                                    <!-- Author Info -->

                                                    <td>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <!-- Avatar with initials fallback -->
                                                            <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center"
                                                                style="width: 32px; height: 32px; font-size: 0.9rem;">
                                                                <?php echo strtoupper(substr($post['author_name'], 0, 1)); ?>
                                                            </div>

                                                            <!-- Author Name -->
                                                            <span class="small fw-semibold">
                                                                <?php echo htmlspecialchars($post['author_name']); ?>
                                                            </span>
                                                        </div>
                                                    </td>



                                                    <!-- Status (optional placeholder) -->
                                                    <td>
                                                        <span class="badge-soft" data-status="<?php echo htmlspecialchars($post['status']); ?>">
                                                            <?php if ($post['status'] === 'approved'): ?>
                                                                <i class="bi bi-patch-check"></i> Approved
                                                            <?php elseif ($post['status'] === 'pending'): ?>
                                                                <i class="bi bi-hourglass-split"></i> Pending
                                                            <?php else: ?>
                                                                <i class="bi bi-x-circle"></i> Rejected
                                                            <?php endif; ?>
                                                        </span>
                                                    </td>


                                                    <!-- Created Date -->
                                                    <td class="small text-muted">

                                                        <?php echo date('M d, Y', strtotime($post['created_at'])); ?>
                                                    </td>

                                                    <!-- Actions -->
                                                    <td>
                                                        <div class="d-flex justify-content-end gap-2">
                                                            <!-- View -->
                                                            <button type="button"
                                                                class="btn btn-outline-secondary btn-action"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#viewTaskModal<?php echo $post['task_id']; ?>">
                                                                <i class="bi bi-eye me-1"></i>View
                                                            </button>

                                                            <!-- Delete -->

                                                            <a href="superadmin.php?delete_id=<?php echo $post['task_id']; ?>"
                                                                class="btn btn-outline-danger btn-action"
                                                                onclick="return confirm('Are you sure you want to delete this task?')">
                                                                <i class="bi bi-trash me-1"></i>Delete
                                                            </a>

                                                        </div>
                                                    </td>
                                                </tr>

                                                <!-- Modal for Task Details -->
                                                <div class="modal fade" id="viewTaskModal<?php echo $post['task_id']; ?>" tabindex="-1" aria-labelledby="viewTaskModalLabel<?php echo $post['task_id']; ?>" aria-hidden="true">
                                                    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <div>
                                                                    <h5 class="modal-title fw-bold" id="viewTaskModalLabel<?php echo $post['task_id']; ?>">
                                                                        <?php echo htmlspecialchars($post['task_name']); ?>
                                                                    </h5>
                                                                    <p class="text-muted small mb-0">Task ID: <?php echo $post['task_id']; ?></p>
                                                                </div>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="row g-4">
                                                                    <div class="col-md-6">
                                                                        <h6 class="text-muted text-uppercase small mb-2">Author</h6>
                                                                        <div class="d-flex align-items-center gap-2">
                                                                            <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center"
                                                                                style="width: 40px; height: 40px; font-size: 1rem;">
                                                                                <?php echo strtoupper(substr($post['author_name'], 0, 1)); ?>
                                                                            </div>
                                                                            <div>
                                                                                <strong><?php echo htmlspecialchars($post['author_name']); ?></strong>
                                                                                <div class="text-muted small">ID: <?php echo $post['user_id']; ?></div>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-md-6">
                                                                        <h6 class="text-muted text-uppercase small mb-2">Status</h6>
                                                                        <span class="badge-soft" data-status="<?php echo htmlspecialchars($post['status']); ?>">
                                                                            <?php if ($post['status'] === 'approved'): ?>
                                                                                <i class="bi bi-patch-check"></i> Approved
                                                                            <?php elseif ($post['status'] === 'pending'): ?>
                                                                                <i class="bi bi-hourglass-split"></i> Pending
                                                                            <?php else: ?>
                                                                                <i class="bi bi-x-circle"></i> Rejected
                                                                            <?php endif; ?>
                                                                        </span>
                                                                    </div>

                                                                    <div class="col-md-6">
                                                                        <h6 class="text-muted text-uppercase small mb-2">Created Date</h6>
                                                                        <p class="mb-0">
                                                                            <i class="bi bi-calendar-event me-2"></i>
                                                                            <?php echo date('d M Y', strtotime($post['created_at'])); ?>
                                                                        </p>
                                                                    </div>

                                                                    <div class="col-md-6">
                                                                        <h6 class="text-muted text-uppercase small mb-2">Created Time</h6>
                                                                        <p class="mb-0">
                                                                            <i class="bi bi-clock me-2"></i>
                                                                            <?php echo date('h:i A', strtotime($post['created_at'])); ?>
                                                                        </p>
                                                                    </div>
                                                                    <div class="col-12">
                                                                        <h6 class="text-muted text-uppercase small mb-2">Image</h6>
                                                                        <div class="p-3 bg-light rounded-3 text-center">
                                                                            <?php if (!empty($post['image'])): ?>
                                                                                <img src="<?php echo htmlspecialchars($post['image']); ?>"
                                                                                    alt="Task Image"
                                                                                    class="img-fluid rounded shadow-sm"
                                                                                    style="max-height: 400px; object-fit: contain;">
                                                                            <?php else: ?>
                                                                                <span class="text-muted">No Image Available</span>
                                                                            <?php endif; ?>
                                                                        </div>

                                                                    </div>

                                                                    <div class="col-12">
                                                                        <h6 class="text-muted text-uppercase small mb-2">Description</h6>
                                                                        <div class="p-3 bg-light rounded-3">
                                                                            <p class="mb-0" style="white-space: pre-wrap;"><?php echo htmlspecialchars($post['description']); ?></p>
                                                                        </div>
                                                                    </div>

                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary btn-pill" data-bs-dismiss="modal">
                                                                    <i class="bi bi-x-circle me-2"></i>Close
                                                                </button>
                                                                <a href="superadmin.php?delete_id=<?php echo $post['task_id']; ?>"
                                                                    class="btn btn-danger btn-pill"
                                                                    onclick="return confirm('Are you sure you want to delete this task?')">
                                                                    <i class="bi bi-trash me-2"></i>Delete Task
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">No posts found.</td>
                                            </tr>
                                        <?php endif; ?>

                                        <!-- TODO: inject posts rows here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="panel__body border-top">
                            <nav aria-label="Posts pagination">
                                <ul class="pagination pagination-sm justify-content-center mb-0 gap-1">
                                    <?php if ($current_page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link rounded-pill" href="?page=<?php echo $current_page - 1; ?>#posts">Previous</a>
                                        </li>
                                    <?php else: ?>
                                        <li class="page-item disabled">
                                            <span class="page-link rounded-pill">Previous</span>
                                        </li>
                                    <?php endif; ?>

                                    <?php
                                    // Show max 5 page numbers
                                    $max_pages_to_show = 5;
                                    $start_page = max(1, $current_page - 2);
                                    $end_page = min($total_pages, $start_page + $max_pages_to_show - 1);

                                    // Adjust start if we're near the end
                                    if ($end_page - $start_page < $max_pages_to_show - 1) {
                                        $start_page = max(1, $end_page - $max_pages_to_show + 1);
                                    }

                                    // Show first page + ellipsis if needed
                                    if ($start_page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link rounded-pill" href="?page=1#posts">1</a>
                                                                               </li>
                                        <?php if ($start_page > 2): ?>
                                            <li class="page-item disabled">
                                                <span class="page-link rounded-pill">...</span>
                                            </li>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                        <li class="page-item <?php echo ($i == $current_page) ? 'active' : ''; ?>">
                                            <a class="page-link rounded-pill" href="?page=<?php echo $i; ?>#posts">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php
                                    // Show ellipsis + last page if needed
                                    if ($end_page < $total_pages): ?>
                                        <?php if ($end_page < $total_pages - 1): ?>
                                            <li class="page-item disabled">
                                                <span class="page-link rounded-pill">...</span>
                                            </li>
                                        <?php endif; ?>
                                        <li class="page-item">
                                            <a class="page-link rounded-pill" href="?page=<?php echo $total_pages; ?>#posts"><?php echo $total_pages; ?></a>
                                        </li>
                                    <?php endif; ?>

                                    <?php if ($current_page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link rounded-pill" href="?page=<?php echo $current_page + 1; ?>#posts">Next</a>
                                        </li>
                                    <?php else: ?>
                                        <li class="page-item disabled">
                                            <span class="page-link rounded-pill">Next</span>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                            <p class="text-muted small mb-0 mt-2 text-center">
                                Showing <?php echo min($offset + 1, $totalPosts); ?> to <?php echo min($offset + $posts_per_page, $totalPosts); ?> of <?php echo $totalPosts; ?> posts
                            </p>
                        </div>
                        <div class="panel__body">
                            <div id="post-slide-over" class="slide-over">
                                <strong>Post details slide-over placeholder.</strong>
                                <p class="mb-0 small">
                                    Implement modal or slide-over with backend/JS integration when ready.
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="reports" class="container-fluid pb-4">
                    <div class="row g-4">
                        <div class="col-12 col-lg-8">
                            <div class="panel h-100">
                                <div class="panel__header">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="btn btn-sm btn-light rounded-circle text-info">
                                            <i class="bi bi-activity"></i>
                                        </span>
                                        <div>
                                            <h3 class="h5 fw-semibold mb-0">Recent Activity Logs</h3>
                                            <p class="mb-0 text-muted small">Track critical actions across the platform.</p>
                                        </div>
                                    </div>
                                    <a href="<!-- /admin/logs.php -->" class="btn btn-outline-secondary btn-pill btn-sm">View all logs</a>
                                </div>
                                <div class="panel__body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th scope="col">User</th>
                                                    <th scope="col">Action</th>
                                                    <th scope="col">IP Address</th>
                                                    <th scope="col">Timestamp</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- PHP: loop activity logs as $log -->
                                                <tr>
                                                    <td class="small fw-semibold">John Doe</td>
                                                    <td class="small">
                                                        <span class="badge-soft" data-status="approved">
                                                            <i class="bi bi-shield-check"></i>User Approved
                                                        </span>
                                                    </td>
                                                    <td class="text-muted small">192.168.1.105</td>
                                                    <td class="text-muted small">Nov 03, 2025 • 08:45</td>
                                                </tr>
                                                <tr>
                                                    <td class="small fw-semibold">Super Admin</td>
                                                    <td class="small">
                                                        <span class="badge-soft" data-status="approved">
                                                            <i class="bi bi-pencil"></i>Post Edited
                                                        </span>
                                                    </td>
                                                    <td class="text-muted small">192.168.1.100</td>
                                                    <td class="text-muted small">Nov 03, 2025 • 08:30</td>
                                                </tr>
                                                <!-- TODO: inject activity rows here -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="panel__body">
                                    <a href="<!-- /admin/logs/download.php -->" class="btn btn-outline-primary btn-pill btn-sm">
                                        <i class="bi bi-download me-2"></i>Download latest log
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-lg-4" id="backups">
                            <div class="panel h-100">
                                <div class="panel__header">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="btn btn-sm btn-light rounded-circle text-primary">
                                            <i class="bi bi-cloud-arrow-down"></i>
                                        </span>
                                        <div>
                                            <h3 class="h5 fw-semibold mb-0">Backups</h3>
                                            <p class="mb-0 text-muted small">Create and download database snapshots.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="panel__body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <p class="text-muted small mb-1">Last backup</p>
                                            <h4 class="h6 fw-semibold mb-0">
                                                <!-- PHP: echo $last_backup_at; -->
                                                Nov 03, 2025 • 02:00
                                            </h4>
                                        </div>
                                        <span class="btn btn-light rounded-circle text-success">
                                            <i class="bi bi-check-circle-fill"></i>
                                        </span>
                                    </div>
                                    <form method="post" action="<!-- /admin/create_backup.php -->" class="d-grid gap-2">
                                        <!-- <?php // echo $csrf_token; 
                                                ?> -->
                                        <button type="submit" class="btn btn-primary btn-pill">
                                            <i class="bi bi-cloud-arrow-up me-2"></i>Create backup
                                        </button>
                                    </form>
                                    <a href="<!-- /admin/download_backup.php -->" class="btn btn-outline-secondary btn-pill w-100 mt-3">
                                        <i class="bi bi-download me-2"></i>Download latest
                                    </a>
                                </div>
                                <div class="panel__body border-top">
                                    <h5 class="h6 fw-semibold mb-3">System Info</h5>
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between small mb-2">
                                            <span class="text-muted">Disk usage</span>
                                            <span class="fw-semibold">
                                                <!-- PHP: echo $disk_usage_percent; -->
                                                45%
                                            </span>
                                        </div>
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar bg-primary" style="width: 45%;"></div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between small mb-2">
                                            <span class="text-muted">Memory usage</span>
                                            <span class="fw-semibold">62%</span>
                                        </div>
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar bg-warning" style="width: 62%;"></div>
                                        </div>
                                    </div>
                                    <ul class="list-unstyled small text-muted mb-0">
                                        <li class="d-flex justify-content-between">
                                            <span>PHP Version</span>
                                            <strong><!-- PHP: echo PHP_VERSION; -->8.2.0</strong>
                                        </li>
                                        <li class="d-flex justify-content-between">
                                            <span>Database</span>
                                            <strong>MySQL 8.0</strong>
                                        </li>
                                        <li class="d-flex justify-content-between">
                                            <span>Server</span>
                                            <strong>Apache 2.4</strong>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="settings" class="container-fluid pb-4">
                    <div class="panel">
                        <div class="panel__header">
                            <div class="d-flex align-items-center gap-2">
                                <span class="btn btn-sm btn-light rounded-circle text-secondary">
                                    <i class="bi bi-gear-wide-connected"></i>
                                </span>
                                <div>
                                    <h3 class="h5 fw-semibold mb-0">Site Settings</h3>
                                    <p class="mb-0 text-muted small">Placeholder section for future settings forms.</p>
                                </div>
                            </div>
                        </div>
                        <div class="panel__body">
                            <p class="mb-0 text-muted">Integrate configuration forms here.</p>
                        </div>
                    </div>
                </section>
            </main>

            <footer class="footer text-center text-md-start">
                <div class="row align-items-center g-2">
                    <div class="col-md">
                        © 2025 AdminPanel. All rights reserved.
                    </div>
                    <div class="col-md-auto">
                        <div class="d-flex gap-3 justify-content-center justify-content-md-end">
                            <a href="#" class="text-secondary small">Documentation</a>
                            <a href="#" class="text-secondary small">Support</a>
                            <a href="#" class="text-secondary small">Privacy Policy</a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>