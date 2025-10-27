<?php
session_start();
include 'db_connect.php';
include 'middleware.php';
checkRole(['admin']);

$user_id = $_SESSION['user_id'];

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// for status 
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
$allUsers = "SELECT * FROM `tasks` ORDER BY `created_at` DESC";
$result = mysqli_query($conn, $allUsers);


// all Users 
$fetchAllUsers = "SELECT COUNT(*) AS total_users from users where role = 'user'";
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
$fetchAllBlockedQuery = "SELECT COUNT(*) AS blcoked_task FROM tasks WHERE status = 'blcoked'";
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
            <a href="homepage.php" class="btn btn-success">
                <i class="fas fa-sign-out-alt me-2"></i>Back To Homepage
            </a>
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
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTaskModal">
                    <i class="fas fa-plus me-2"></i>Create Task
                </button>
            </div>
            <?php if (mysqli_num_rows($result) > 0) : ?>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <!-- <th>User ID</th> -->
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
                                    <!-- <td><?php echo htmlspecialchars($task['user_id']); ?></td> -->
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

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const logoutForm = document.querySelector('.sidebar-logout-form');
            if (!logoutForm) return;

            logoutForm.addEventListener('submit', (event) => {
                if (!confirm('Are you sure you want to logout?')) {
                    event.preventDefault();
                    return;
                }

            });
        });
    </script>

</body>

</html>