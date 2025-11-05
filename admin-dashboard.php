<?php
// session_start();
include 'db_connect.php';
include 'middleware.php';
include 'functions.php';
checkRole([2]);

$user_id = $_SESSION['user_id'];

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Handle Approve / Reject
if (isset($_POST['approve']) || isset($_POST['reject'])) {

    $task_id = intval($_POST['task_id']);
    $action = isset($_POST['approve']) ? 'approved' : 'rejected';

    // Step 1: Fetch task owner and title
    $taskRes = mysqli_query($conn, "SELECT user_id, task_name FROM tasks WHERE id = $task_id LIMIT 1");
    if ($taskRow = mysqli_fetch_assoc($taskRes)) {

        $user_id = intval($taskRow['user_id']);
        $task_name = mysqli_real_escape_string($conn, $taskRow['task_name']);

        // Step 2: Update task status
        mysqli_query($conn, "UPDATE tasks SET status = '$action' WHERE id = $task_id");

        // Step 3: Insert notification for user
        $notif_message = "Your task '$task_name' has been $action by admin.";
        $notif_message = mysqli_real_escape_string($conn, $notif_message);

        mysqli_query($conn, "
            INSERT INTO notifications (user_id, message, is_read, created_at)
            VALUES ($user_id, '$notif_message', 0, NOW())
        ");
    }

    // Step 4: Redirect back
    header("Location: admin-dashboard.php");
    exit;
}





// name of logged admin 
$user_name_query = "SELECT role_id FROM users WHERE role_id = 2 LIMIT 1";
$userResult = mysqli_query($conn, $user_name_query);

$user_name = $_SESSION['user_name'] ?? 'Admin';
if ($userResult && mysqli_num_rows($userResult) > 0) {
    $row = mysqli_fetch_assoc($userResult);
    if (!empty($row['role'])) {
        $user_name = ucfirst($row['role']);
        

    }
}

if (isset($_POST['add_task'])) {
    $taskName = trim($_POST['task_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $imagePath = null;

    if ($taskName !== '' && $description !== '') {
        if (!empty($_FILES['task_image']['name'])) {
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $imageName = time() . '_' . basename($_FILES['task_image']['name']);
            $targetPath = $uploadDir . $imageName;

            if (move_uploaded_file($_FILES['task_image']['tmp_name'], $targetPath)) {
                $imagePath = $targetPath;
            }
        }

        $stmt = mysqli_prepare($conn, "INSERT INTO tasks (user_id, task_name, description, image, status) VALUES (?, ?, ?, ?, 'approved')");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'isss', $user_id, $taskName, $description, $imagePath);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            header("Location: admin-dashboard.php?created=1");
            exit;
        }
    }
}

// $user_id = $_SESSION['user_id'];

if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: homepage.php?logout=success");
    exit;
}

// All task  
$limit = 6;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$allUsers = "SELECT * FROM `tasks` ORDER BY `created_at` DESC LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $allUsers);

// Total tasks count for pagination
$countQuery = "SELECT COUNT(*) as total FROM tasks";
$countResult = mysqli_query($conn, $countQuery);
$countRow = mysqli_fetch_assoc($countResult);
$totalTasks = $countRow['total'];
$totalPages = ceil($totalTasks / $limit);

// all Users 
$fetchAllUsers = "SELECT COUNT(*) AS total_users from users where role_id = 3";
$countedResult = mysqli_query($conn, $fetchAllUsers);
if ($countedResult) {
    $row =  mysqli_fetch_assoc($countedResult);
}

// all task 
$fetchAllTaskQuery = "SELECT COUNT(*) AS total_task FROM tasks";
$queryResult = mysqli_query($conn, $fetchAllTaskQuery);

if ($queryResult) {
    $Taskrow = mysqli_fetch_assoc($queryResult);
}

// for notifications 
$admin_id = $_SESSION['user_id'];

$notificationsquery = "
    SELECT n.id, n.user_id, n.message, n.created_at, u.name 
    FROM notifications n 
    JOIN users u ON n.user_id = u.id 
    WHERE n.is_read = 0 
    AND n.user_id = $admin_id
    ORDER BY n.created_at DESC
";

$notifyresult = mysqli_query($conn, $notificationsquery);
$notification_count = mysqli_num_rows($notifyresult);

// Mark notification as read
if (isset($_POST['mark_read'])) {
    $notif_id = intval($_POST['notif_id']);
    mysqli_query($conn, "UPDATE notifications SET is_read = 1 WHERE id = '$notif_id'");
    header("Location: admin-dashboard.php");
    exit;
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

// all rejected task 
$fetchAllRejectedQuery = "SELECT COUNT(*) AS rejected_task FROM tasks WHERE status = 'rejected'";
$rejectedueryResulty = mysqli_query($conn, $fetchAllRejectedQuery);

if ($rejectedueryResulty) {
    $Recjectedrow = mysqli_fetch_assoc($rejectedueryResulty);
}

// all blocked task 
$fetchAllBlockedQuery = "SELECT COUNT(*) AS blcoked_task FROM tasks WHERE status = 'blocked'"; // fixed status value
$blocledqueryResulty = mysqli_query($conn, $fetchAllBlockedQuery);

if ($blocledqueryResulty) {
    $Blockedrow = mysqli_fetch_assoc($blocledqueryResulty);
}



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="./styling/admin.css">
    <style>
        .sidebar-menu .sidebar-logout-form {
            margin: 0;
        }

        .sidebar-menu .sidebar-menu-item {
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
            padding: 12px 20px;
            border: none;
            background: transparent;
            color: inherit;
            font-size: 16px;
            text-align: left;
        }

        .sidebar-menu .sidebar-menu-item:hover,
        .sidebar-menu .sidebar-menu-item:focus {
            background: rgba(255, 255, 255, 0.08);
            outline: none;
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
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.18);
        }

        .btn:focus-visible {
            outline: 2px solid rgba(102, 126, 234, 0.45);
            outline-offset: 4px;
        }

        .btn-success {
            box-shadow: 0 10px 24px rgba(46, 204, 113, 0.25);
        }

        .btn-success:hover {
            background-color: #1e9d57;
        }

        .btn-outline-danger {
            border-width: 2px;
            box-shadow: 0 8px 20px rgba(231, 76, 60, 0.18);
        }

        .btn-outline-danger:hover {
            color: #fff;
            background-color: #e74c3c;
            border-color: #e74c3c;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            box-shadow: 0 12px 28px rgba(118, 75, 162, 0.25);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #5b70dd, #6b4295);
        }

        .btn-primary:active {
            background: linear-gradient(135deg, #4d63c7, #5d3883);
        }

        .notification-bell {
            position: relative;
            cursor: pointer;
            margin-right: 20px;
        }

        .notification-bell i {
            font-size: 24px;
            color: #667eea;
        }

        .notification-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #e74c3c;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }

        .notification-dropdown {
            position: absolute;
            top: 50px;
            right: 0;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            width: 350px;
            max-height: 400px;
            overflow-y: auto;
            display: none;
            z-index: 1000;
        }

        .notification-dropdown.show {
            display: block;
        }

        .notification-header {
            padding: 15px;
            border-bottom: 1px solid #eee;
            font-weight: bold;
        }

        .notification-item {
            padding: 15px;
            border-bottom: 1px solid #f5f5f5;
            transition: background 0.2s;
        }

        .notification-item:hover {
            background: #f8f9fa;
        }

        .notification-item p {
            margin: 0;
            font-size: 14px;
        }

        .notification-item small {
            color: #999;
            display: block;
            margin-top: 5px;
        }

        .mark-read-btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
            margin-top: 8px;
        }

        .mark-read-btn:hover {
            background: #5568d3;
        }

        .no-notifications {
            padding: 30px;
            text-align: center;
            color: #999;
        }

        /* Action dropdown menu for admin */
        .admin-action-dropdown {
            position: relative;
            display: inline-block;
        }

        .admin-action-toggle {
            background: transparent;
            border: none;
            font-size: 22px;
            cursor: pointer;
            padding: 4px 10px;
            color: #667eea;
            font-weight: bold;
            transition: color 0.2s;
        }

        .admin-action-toggle:hover {
            color: #5568d3;
        }

        .admin-action-menu {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12);
            min-width: 180px;
            z-index: 100;
            overflow: hidden;
        }

        .admin-action-menu.show {
            display: block;
        }

        .admin-action-menu button,
        .admin-action-menu a {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            padding: 12px 16px;
            text-align: left;
            border: none;
            background: transparent;
            color: #333;
            text-decoration: none;
            cursor: pointer;
            transition: background 0.2s, color 0.2s;
            font-size: 14px;
        }

        .admin-action-menu button:hover,
        .admin-action-menu a:hover {
            background: #f5f5f5;
        }

        .admin-action-menu .text-success:hover {
            background: #d4edda;
            color: #155724;
        }

        .admin-action-menu .text-danger:hover {
            background: #f8d7da;
            color: #721c24;
        }

        .admin-action-menu .text-info:hover {
            background: #d1ecf1;
            color: #0c5460;
        }

        /* Pagination */
        .pagination-modern {
            margin-top: 2rem;
        }

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
    </style>
</head>

<body>

    <?php 
    $activePage = 'dashboard';
    include 'sidebar.php'; 
    ?>

    <!-- Top Navbar -->
    <div class="top-navbar">
        <div class="admin-profile">
            <a href="homepage.php" class="btn btn-success">
                <i class="fas fa-sign-out-alt me-2"></i>Back
            </a>
            <form action="" method="post" class="logout-form ms-2">
                <button type="submit" name="logout" class="btn btn-outline-danger">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </button>
            </form>

            <!-- Notification Bell -->
            <div class="notification-bell" onclick="toggleNotifications()">
                <i class="fas fa-bell"></i>
                <?php if ($notification_count > 0): ?>
                    <span class="notification-badge"><?php echo $notification_count; ?></span>
                <?php endif; ?>

                <div class="notification-dropdown" id="notificationDropdown">
                    <div class="notification-header">
                        Notifications (<?php echo $notification_count; ?>)
                    </div>
                    <?php if ($notification_count > 0): ?>
                        <?php
                        // mysqli_data_seek($notifyresult, 0);
                        while ($notif = mysqli_fetch_assoc($notifyresult)):
                        ?>
                            <div class="notification-item">
                                <p><strong><?php echo htmlspecialchars($notif['name']); ?></strong></p>
                                <p><?php echo htmlspecialchars($notif['message']); ?></p>
                                <small><?php echo date('M d, Y h:i A', strtotime($notif['created_at'])); ?></small>
                                <form method="POST" style="margin: 0;">
                                    <input type="hidden" name="notif_id" value="<?php echo $notif['id']; ?>">
                                    <button type="submit" name="mark_read" class="btn btn-primary btn-sm d-flex align-items-center gap-2">
                                        <i class="bi bi-check-circle-fill"></i> Mark as Read
                                    </button>

                                </form>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="no-notifications">
                            <i class="fas fa-inbox" style="font-size: 40px; color: #ddd;"></i>
                            <p>No new notifications</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div>
                <div class="admin-name">Admin Panel</div>
                <b>Welcome, <?php echo htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8'); ?></b>
            </div>
            <img src="https://ui-avatars.com/api/?name=Admin&background=667eea&color=fff&size=128" alt="Admin">
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">

        <!-- Welcome Header -->
        <div class="welcome-header">
            <h2>👋 Welcome, Admin — Manage Tasks & Users Easily</h2>
            <p>Monitor and manage all tasks, users, and approvals from one place</p>
        </div>

        <!-- Stats Cards -->
        <div class="stats-container">
            <div class="stat-card blue">
                <div class="stat-card-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-card-title">Total Users</div>
                <div class="stat-card-value"> <?php echo $row['total_users'] ?></div>
            </div>

            <div class="stat-card purple">
                <div class="stat-card-icon">
                    <i class="fas fa-tasks"></i>
                </div>
                <div class="stat-card-title">Total Tasks</div>
                <div class="stat-card-value"><?php echo $Taskrow['total_task'] ?></div>
            </div>

            <div class="stat-card orange">
                <div class="stat-card-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-card-title">Pending Approvals</div>
                <div class="stat-card-value"><?php echo $Pendingrow['pending_task'] ?> </div>
            </div>
            <div class="stat-card orange">
                <div class="stat-card-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-card-title">Approve</div>
                <div class="stat-card-value"><?php echo $Approverow['approve_task'] ?> </div>
            </div>
            <div class="stat-card orange">
                <div class="stat-card-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-card-title">Rejected</div>
                <div class="stat-card-value"><?php echo $Recjectedrow['rejected_task'] ?> </div>
            </div>
            <div class="stat-card orange">
                <div class="stat-card-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-card-title">Blocked</div>
                <div class="stat-card-value"><?php echo $Blockedrow['blcoked_task'] ?> </div>
            </div>
        </div>

        <!-- Tasks Table -->
        <div class="table-section">
            <div class="table-header">
                <h4><i class="fas fa-list me-2"></i>All Tasks Overview</h4>
                <?php if (hasPermission('add_task')): ?>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTaskModal">
                    <i class="fas fa-plus me-2"></i>Create Task
                </button>
                <?php endif; ?>
            </div>
            <?php if (mysqli_num_rows($result) > 0) : ?>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Post Title</th>
                                <th>Created At</th>
                                <th>Image</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($task = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($task['id']); ?></td>
                                    <td><?php echo htmlspecialchars($task['task_name']); ?></td>
                                    <td>
                                         <i class="far fa-calendar text-muted me-2"></i>
                                        <?php echo date('Y-m-d', strtotime($task['created_at'])); ?>


                                    </td>
                                    <td>
                                        <?php if (!empty($task['image'])): ?>
                                            <img src="<?php echo htmlspecialchars($task['image']); ?>" alt="Task Image" style="width:60px; height:60px; object-fit:cover; border-radius:6px;">
                                        <?php else: ?>
                                            <span class="text-muted">No Image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($task['status'] == 'approved'): ?>
                                            <span class="badge bg-success">Approved</span>
                                        <?php elseif ($task['status'] == 'rejected'): ?>
                                            <span class="badge bg-danger">Rejected</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-center">
                                        <div class="admin-action-dropdown">
                                            <button class="admin-action-toggle" onclick="toggleAdminActionMenu(event, this)" aria-label="Actions">
                                                ....
                                            </button>
                                            <div class="admin-action-menu">
                                                <?php if (hasPermission('view-details')): ?>
                                                <button type="button" class="text-info" data-bs-toggle="modal" data-bs-target="#viewTaskModal" data-name="<?php echo htmlspecialchars($task['task_name'], ENT_QUOTES, 'UTF-8'); ?>" data-desc="<?php echo htmlspecialchars($task['description'], ENT_QUOTES, 'UTF-8'); ?>" data-created="<?php echo date('M d, Y', strtotime($task['created_at'])); ?>" data-status="<?php echo htmlspecialchars($task['status']); ?>" data-image="<?php echo htmlspecialchars($task['image'] ?? ''); ?>">
                                                    <i class="fas fa-eye"></i> View Details
                                                </button>
                                                <?php endif; ?>
                                                <?php if (hasPermission('approve-task')): ?>
                                                <form method="POST" style="margin:0;">
                                                    <input type="hidden" name="task_id" value="<?= $task['id']; ?>">
                                                    <button type="submit" name="approve" class="text-success">
                                                        <i class="fas fa-check-circle"></i> Approve
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                                <?php if (hasPermission('reject-task')): ?>
                                                <form method="POST" style="margin:0;">
                                                    <input type="hidden" name="task_id" value="<?= $task['id']; ?>">
                                                    <button type="submit" name="reject" class="text-danger">
                                                        <i class="fas fa-times-circle"></i> Reject
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>


                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <nav aria-label="Admin Tasks Pagination">
                        <ul class="pagination pagination-modern justify-content-center">
                            <!-- Previous Button -->
                            <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?php echo ($page > 1) ? 'admin-dashboard.php?page=' . ($page - 1) : '#'; ?>" aria-label="Previous">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>

                            <!-- Page Numbers -->
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                    <a class="page-link" href="admin-dashboard.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <!-- Next Button -->
                            <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?php echo ($page < $totalPages) ? 'admin-dashboard.php?page=' . ($page + 1) : '#'; ?>" aria-label="Next">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>

            <?php else: ?>
                <div class="text-center py-5">
                    <div class="icon-box mx-auto mb-4" style="width: 80px; height: 80px;">
                        <i class="fas fa-inbox text-white fs-1"></i>
                    </div>
                    <h3 class="fw-semibold text-dark mb-2">No Tasks Yet</h3>
                    <p class="text-muted mb-4">Start by adding your first task to get organized!</p>
                    <button class="btn btn-add btn-success text-white px-4 py-2" data-bs-toggle="modal" data-bs-target="#addTaskModal">
                        <i class="fas fa-plus-circle me-2"></i>Add Your First Task
                    </button>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <!-- Add Task Modal -->
    <div class="modal fade" id="addTaskModal" tabindex="-1" aria-labelledby="addTaskModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" class="modal-content" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="addTaskModalLabel">Create New Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="task_name" class="form-label">Task Title</label>
                        <input type="text" class="form-control" id="task_name" name="task_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Task Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="task_image" class="form-label">Task Image (optional)</label>
                        <input type="file" class="form-control" id="task_image" name="task_image" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_task" class="btn btn-primary">Save Task</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Task Details Modal (Admin) -->
    <div class="modal fade" id="viewTaskModal" tabindex="-1" aria-labelledby="viewTaskModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="viewTaskModalLabel">
                        <i class="fas fa-info-circle me-2"></i>Task Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-muted">Task Name</label>
                            <p id="admin_view_task_name" class="fs-5 fw-semibold"></p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold text-muted">Status</label>
                            <p id="admin_view_status"></p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold text-muted">Created At</label>
                            <p id="admin_view_created_at" class="text-muted"></p>
                        </div>
                    </div>
                    <div class="mb-3" id="admin_view_image_container" style="display:none;">
                        <label class="form-label fw-bold text-muted">Task Image</label><br>
                        <img id="admin_view_image" src="" alt="Task Image" style="max-width:100%; max-height:300px; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.1);">
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold text-muted">Description</label>
                        <p id="admin_view_description" class="border-start border-4 border-primary ps-3" style="white-space: pre-wrap;"></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const logoutForm = document.querySelector('.logout-form');
            if (!logoutForm) return;

            logoutForm.addEventListener('submit', (event) => {
                if (!confirm('Are you sure you want to logout?')) {
                    event.preventDefault();
                    return;
                }
            });
        });

        function toggleNotifications() {
            const dropdown = document.getElementById('notificationDropdown');
            dropdown.classList.toggle('show');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const bell = document.querySelector('.notification-bell');
            const dropdown = document.getElementById('notificationDropdown');

            if (bell && !bell.contains(event.target)) {
                dropdown.classList.remove('show');
            }
        });

        function toggleAdminActionMenu(event, button) {
            event.stopPropagation();
            const menu = button.nextElementSibling;
            document.querySelectorAll('.admin-action-menu.show').forEach(m => {
                if (m !== menu) m.classList.remove('show');
            });
            menu.classList.toggle('show');
        }

        document.addEventListener('click', function() {
            document.querySelectorAll('.admin-action-menu.show').forEach(m => m.classList.remove('show'));
        });

        // Handle View Task Modal (Admin)
        const viewTaskModal = document.getElementById('viewTaskModal');
        if (viewTaskModal) {
            viewTaskModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                document.getElementById('admin_view_task_name').textContent = button.getAttribute('data-name');
                document.getElementById('admin_view_description').textContent = button.getAttribute('data-desc');
                document.getElementById('admin_view_created_at').textContent = button.getAttribute('data-created');

                const status = button.getAttribute('data-status');
                let badge = '<span class="badge bg-warning text-dark">Pending</span>';
                if (status === 'approved') badge = '<span class="badge bg-success">Approved</span>';
                if (status === 'rejected') badge = '<span class="badge bg-danger">Rejected</span>';
                document.getElementById('admin_view_status').innerHTML = badge;

                const imageSrc = button.getAttribute('data-image');
                const imageContainer = document.getElementById('admin_view_image_container');
                const imageEl = document.getElementById('admin_view_image');
                if (imageSrc && imageSrc !== '') {
                    imageEl.src = imageSrc;
                    imageContainer.style.display = 'block';
                } else {
                    imageContainer.style.display = 'none';
                }
            });
        }
    </script>

</body>

</html>