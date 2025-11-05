<?php
session_start();
include 'db_connect.php';
include 'middleware.php';
checkRole([2]);

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: homepage.php?logout=success");
    exit;
}

// Pagination setup
$limit = 6;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch pending tasks only
$tasks_query = "SELECT * FROM tasks WHERE status = 'pending' ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$tasks_result = mysqli_query($conn, $tasks_query);

// Total pending tasks count
$countQuery = "SELECT COUNT(*) as total FROM tasks WHERE status = 'pending'";
$countResult = mysqli_query($conn, $countQuery);
$countRow = mysqli_fetch_assoc($countResult);
$totalTasks = $countRow['total'];
$totalPages = ceil($totalTasks / $limit);

// Get user name
$user_name = $_SESSION['user_name'] ?? 'Admin';

// Handle Approve / Reject
if (isset($_POST['approve']) || isset($_POST['reject'])) {
    $task_id = intval($_POST['task_id']);
    $action = isset($_POST['approve']) ? 'approved' : 'rejected';

    $taskRes = mysqli_query($conn, "SELECT user_id, task_name FROM tasks WHERE id = $task_id LIMIT 1");
    if ($taskRow = mysqli_fetch_assoc($taskRes)) {
        $user_id = intval($taskRow['user_id']);
        $task_name = mysqli_real_escape_string($conn, $taskRow['task_name']);

        mysqli_query($conn, "UPDATE tasks SET status = '$action' WHERE id = $task_id");

        $notif_message = "Your task '$task_name' has been $action by admin.";
        $notif_message = mysqli_real_escape_string($conn, $notif_message);

        mysqli_query($conn, "
            INSERT INTO notifications (user_id, message, is_read, created_at)
            VALUES ($user_id, '$notif_message', 0, NOW())
        ");
    }

    header("Location: pending-tasks.php?page=$page");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Tasks - Admin Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="./styling/admin.css">
    
    <style>
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

        .btn {
            transition: all 0.25s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

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
    </style>
</head>

<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <i class="fas fa-tasks"></i> 
        </div>
        <ul class="sidebar-menu">
            <li><a href="admin-dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="all-users.php"><i class="fas fa-users"></i> All Users</a></li>
            <li><a href="all-tasks.php"><i class="fas fa-list"></i> All Tasks</a></li>
            <li><a href="pending-tasks.php" class="active"><i class="fas fa-clock"></i> Pending Tasks</a></li>
            <li><a href="approved-tasks.php"><i class="fas fa-check-circle"></i> Approved Tasks</a></li>
            <li><a href="rejected-tasks.php"><i class="fas fa-times-circle"></i> Rejected Tasks</a></li>
        </ul>
    </div>

    <!-- Top Navbar -->
    <div class="top-navbar">
        <div class="admin-profile">
            <a href="admin-dashboard.php" class="btn btn-success">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
            <form action="" method="post" class="logout-form ms-2">
                <button type="submit" name="logout" class="btn btn-outline-danger">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </button>
            </form>

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
            <h2><i class="fas fa-clock me-2"></i>Pending Tasks Management</h2>
            <p>Review and manage pending tasks waiting for approval</p>
        </div>

        <!-- Tasks Table -->
        <div class="table-section">
            <div class="table-header">
                <h4><i class="fas fa-clipboard-list me-2"></i>Pending Tasks (<?php echo $totalTasks; ?>)</h4>
            </div>

            <?php if (mysqli_num_rows($tasks_result) > 0): ?>
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
                            <?php while ($task = mysqli_fetch_assoc($tasks_result)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($task['id']); ?></td>
                                    <td><strong><?php echo htmlspecialchars($task['task_name']); ?></strong></td>
                                    <td>
                                        <i class="far fa-calendar text-muted me-2"></i>
                                        <?php echo date('M d, Y', strtotime($task['created_at'])); ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($task['image'])): ?>
                                            <img src="<?php echo htmlspecialchars($task['image']); ?>" alt="Task Image" style="width:60px; height:60px; object-fit:cover; border-radius:6px;">
                                        <?php else: ?>
                                            <span class="text-muted">No Image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="admin-action-dropdown">
                                            <button class="admin-action-toggle" onclick="toggleAdminActionMenu(event, this)" aria-label="Actions">
                                                ....
                                            </button>
                                            <div class="admin-action-menu">
                                                <button type="button" class="text-info" data-bs-toggle="modal" data-bs-target="#viewTaskModal" data-name="<?php echo htmlspecialchars($task['task_name'], ENT_QUOTES, 'UTF-8'); ?>" data-desc="<?php echo htmlspecialchars($task['description'], ENT_QUOTES, 'UTF-8'); ?>" data-created="<?php echo date('M d, Y', strtotime($task['created_at'])); ?>" data-status="<?php echo htmlspecialchars($task['status']); ?>" data-image="<?php echo htmlspecialchars($task['image'] ?? ''); ?>">
                                                    <i class="fas fa-eye"></i> View Details
                                                </button>
                                                <form method="POST" style="margin:0;">
                                                    <input type="hidden" name="task_id" value="<?= $task['id']; ?>">
                                                    <button type="submit" name="approve" class="text-success">
                                                        <i class="fas fa-check-circle"></i> Approve
                                                    </button>
                                                </form>
                                                <form method="POST" style="margin:0;">
                                                    <input type="hidden" name="task_id" value="<?= $task['id']; ?>">
                                                    <button type="submit" name="reject" class="text-danger">
                                                        <i class="fas fa-times-circle"></i> Reject
                                                    </button>
                                                </form>
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
                    <nav aria-label="Tasks Pagination">
                        <ul class="pagination pagination-modern justify-content-center">
                            <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?php echo ($page > 1) ? 'pending-tasks.php?page=' . ($page - 1) : '#'; ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>

                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                    <a class="page-link" href="pending-tasks.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?php echo ($page < $totalPages) ? 'pending-tasks.php?page=' . ($page + 1) : '#'; ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>

            <?php else: ?>
                <div class="text-center py-5">
                    <div class="icon-box mx-auto mb-4" style="width: 80px; height: 80px;">
                        <i class="fas fa-clock text-white fs-1"></i>
                    </div>
                    <h3 class="fw-semibold text-dark mb-2">No Pending Tasks</h3>
                    <p class="text-muted mb-4">All tasks have been reviewed!</p>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <!-- View Task Modal -->
    <div class="modal fade" id="viewTaskModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="fas fa-info-circle me-2"></i>Task Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
                        <p id="admin_view_description" class="border-start border-4 border-warning ps-3" style="white-space: pre-wrap;"></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
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
