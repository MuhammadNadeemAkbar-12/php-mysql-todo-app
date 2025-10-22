<?php
session_start();
include 'db_connect.php';

$user_id = $_SESSION['user_id'];

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// forn status 
if (isset($_POST['approve'])) {
    $task_id = $_POST['task_id'];
    mysqli_query($conn, "UPDATE tasks SET status = 'approved' WHERE id = '$task_id'");
}

if (isset($_POST['reject'])) {
    $task_id = $_POST['task_id'];
    mysqli_query($conn, "UPDATE tasks SET status = 'rejected' WHERE id = '$task_id'");
}

if (isset($_POST['block'])) {
    $task_id = $_POST['task_id'];
    mysqli_query($conn, "UPDATE tasks SET status = 'blocked' WHERE id = '$task_id'");
}

// name of logged admin 
$user_name_query = "SELECT role FROM users WHERE role = 'admin' LIMIT 1";
$userResult = mysqli_query($conn, $user_name_query);

if ($userResult && mysqli_num_rows($userResult) > 0) {
    $row = mysqli_fetch_assoc($userResult);
    $user_name = $row['role'];
    echo $user_name;
}


// $user_id = $_SESSION['user_id'];

if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}

// All task  
$allUsers = "SELECT * FROM `tasks` ORDER BY `created_at` DESC";
$result = mysqli_query($conn, $allUsers);


// all Users 
$fetchAllUsers = "SELECT COUNT(*) AS total_users from users where role = 'user'";
$countedResult = mysqli_query($conn, $fetchAllUsers);
if($countedResult){
  $row =  mysqli_fetch_assoc($countedResult);
  
}

// all task 
$fetchAllTaskQuery = "SELECT COUNT(*) AS total_task FROM tasks";
$queryResult = mysqli_query($conn, $fetchAllTaskQuery);

if($queryResult){
    $Taskrow = mysqli_fetch_assoc($queryResult);

}


// all pending task 

$fetchAllPendingQuery = "SELECT COUNT(*) AS pending_task FROM tasks WHERE status = 'pending'";
$pendingQueryResulty = mysqli_query($conn, $fetchAllPendingQuery);

if($pendingQueryResulty){
    $Pendingrow = mysqli_fetch_assoc($pendingQueryResulty);

}




?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Task Management System</title>

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
    </style>
</head>

<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <i class="fas fa-tasks"></i> TaskManager
            <?php

            ?>
        </div>
        <ul class="sidebar-menu">
            <li><a href="#" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="#"><i class="fas fa-users"></i> All Users</a></li>
            <li><a href="#"><i class="fas fa-list"></i> All Tasks</a></li>
            <li><a href="#"><i class="fas fa-clock"></i> Pending Tasks</a></li>
            <li><a href="#"><i class="fas fa-check-circle"></i> Approved Tasks</a></li>
            <li><a href="#"><i class="fas fa-times-circle"></i> Rejected Tasks</a></li>
            <li>
                <form action="" method="post" class="sidebar-logout-form">
                    <button type="submit" name="logout" class="sidebar-menu-item">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </form>
            </li>
        </ul>
    </div>

    <!-- Top Navbar -->
    <div class="top-navbar">
        <div class="admin-profile">
            <div>
                <div class="admin-name">Admin Panel</div>
                <?php echo htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8'); ?>
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
                <div class="stat-card-value"><?php echo $Taskrow['total_task']?></div>
            </div>

            <div class="stat-card orange">
                <div class="stat-card-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-card-title">Pending Approvals</div>
                <div class="stat-card-value"><?php echo $Pendingrow['pending_task'] ?> </div>
            </div>
        </div>

        <!-- Tasks Table -->
        <div class="table-section">
            <div class="table-header">
                <h4><i class="fas fa-list me-2"></i>All Tasks Overview</h4>
            </div>
            <?php if (mysqli_num_rows($result) > 0) : ?>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Unique ID</th>
                                <th>User ID</th>
                                <th>User Name</th>
                                <th>Task Description</th>
                                <th>Image</th>
                                <th>Action</th>
                                <th>Status</th>


                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($task = mysqli_fetch_assoc($result)):; ?>

                                <tr>
                                    
                                    <td><?php echo htmlspecialchars($task['id']); ?></td>
                                    <td><?php echo htmlspecialchars($task['user_id']); ?></td>
                                    <td><?php echo htmlspecialchars($task['task_name']); ?></td>
                                    <td><?php echo htmlspecialchars($task['description']); ?></td>
                                    <td>
                                        <?php if (!empty($task['image'])): ?>
                                            <img src="<?php echo htmlspecialchars($task['image']); ?>" alt="Task Image" style="width:60px; height:60px; object-fit:cover;">
                                        <?php else: ?>
                                            <span class="text-muted">No Image</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="task_id" value="<?= $task['id']; ?>">
                                            <button name="approve" class="btn btn-success btn-sm"><i class="fas fa-check"></i> Approve</button>
                                        </form>

                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="task_id" value="<?= $task['id']; ?>">
                                            <button name="reject" class="btn btn-danger btn-sm"><i class="fas fa-times"></i> Reject</button>
                                        </form>

                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="task_id" value="<?= $task['id']; ?>">
                                            <button name="block" class="btn btn-dark btn-sm"><i class="fas fa-ban"></i> Block</button>
                                        </form>
                                    </td>
                                    
                                    <td>
                                        <?php if ($task['status'] == 'approved'): ?>
                                            <span class="badge bg-success">Approved</span>
                                        <?php elseif ($task['status'] == 'rejected'): ?>
                                            <span class="badge bg-danger">Rejected</span>
                                        <?php elseif ($task['status'] == 'blocked'): ?>
                                            <span class="badge bg-dark">Blocked</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        <?php endif; ?>
                                    </td>


                                    </td>
                                </tr>
                        </tbody>
                    <?php endwhile; ?>
                    </table>
                </div>

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

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>