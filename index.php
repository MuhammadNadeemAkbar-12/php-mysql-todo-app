<?php
session_start();
include 'db_connect.php';
include 'middleware.php';
checkRole(['user']);

$user_id = $_SESSION['user_id'];

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Handle Logout
// if (isset($_GET['logout'])) {
//     session_destroy();
//     header("Location: homepage.php");
//     exit;
// }


// for logout 
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: homepage.php?logout=success");
    exit;
}


// Handle Task Delete
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $delete_id, $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: index.php");
    exit;
}

//  Fetch logged-in user’s name
$user_name = 'User';
if ($stmt = $conn->prepare("SELECT name FROM users WHERE id = ? LIMIT 1")) {
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute() && $stmt->bind_result($fetched_name) && $stmt->fetch()) {
        $user_name = $fetched_name;
    }
    $stmt->close();
}

//  Handle Add Task form submission
if (isset($_POST['add_task'])) {
    $task_name = trim($_POST['task_name']);
    $description = trim($_POST['description']);
    $image_path = null;
    $errors = [];

    // Validation checks
    if (empty($task_name)) {
        $errors[] = "Task Name Must!";
    }
    if (empty($description)) {
        $errors[] = "Description Must!";
    }

    if (count($errors) > 0) {
        echo "<div class='alert alert-danger'><ul>";
        foreach ($errors as $err) {
            echo "<li>$err</li>";
        }
        echo "</ul></div>";
    } else {
        // Image upload
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $image_name = time() . '_' . basename($_FILES["task_image"]["name"]);
        $target_file = $target_dir . $image_name;
        if (move_uploaded_file($_FILES["task_image"]["tmp_name"], $target_file)) {
            $image_path = $target_file;
        }

        $stmt = $conn->prepare("INSERT INTO tasks (user_id, task_name, description, image) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $user_id, $task_name, $description, $image_path);
        $stmt->execute();
        $stmt->close();

        //  Get admin ID
        $adminQuery = $conn->query("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
        $adminRow = $adminQuery->fetch_assoc();
        $admin_id = $adminRow['id'];

        // Prepare notification message
        $notif_message = "New post submitted by " . htmlspecialchars($user_name) . " (User ID: $user_id), waiting for approval.";

        //  Insert notification for admin
        $stmt_notif = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
        $stmt_notif->bind_param("is", $admin_id, $notif_message);
        $stmt_notif->execute();
        $stmt_notif->close();

        // Redirect
        header("Location: index.php");
        exit;
    }
}

// Fetch all user tasks
$tasks_sql = "SELECT * FROM tasks WHERE user_id = $user_id ORDER BY id DESC";
$tasks_result = mysqli_query($conn, $tasks_sql);


// Handle Update Task
// if (isset($_POST['update_task'])) {
//     $edit_id = intval($_POST['edit_id']);
//     $edit_task_name = trim($_POST['edit_task_name']);
//     $edit_description = trim($_POST['edit_description']);

//     if (!empty($edit_task_name)) {
//         $stmt = $conn->prepare("UPDATE tasks SET task_name = ?, description = ? WHERE id = ? AND user_id = ?");
//         $stmt->bind_param("ssii", $edit_task_name, $edit_description, $edit_id, $user_id);
//         $stmt->execute();
//         $stmt->close();

//         header("Location: index.php");
//         exit;
//     } else {
//         echo "<script>alert('Task name cannot be empty.');</script>";
//     }
// }


if (isset($_POST['update_task'])) {

    $edit_id = intval($_POST['edit_id']);
    $edit_task_name = trim($_POST['edit_task_name']);
    $edit_description = trim($_POST['edit_description']);
    $image_path = null;

    if (!empty($_FILES["edit_task_image"]["name"])) {
        echo ("Hello inside");

        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $image_name = time() . '_' . basename($_FILES["edit_task_image"]["name"]);
        $image_file = $target_dir . $image_name;

        if (move_uploaded_file($_FILES["edit_task_image"]["tmp_name"], $image_file)) {
            $image_path = $image_file;
            echo ($image_path);
        }

        $stmt = $conn->prepare("UPDATE tasks SET task_name = ?, description = ?, image = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sssii", $edit_task_name, $edit_description, $image_path, $edit_id, $user_id);
        $stmt->execute();
        $stmt->close();
        header("Location: index.php");
    }

    $stmt = $conn->prepare("UPDATE tasks SET task_name = ?, description = ? WHERE id = ? AND user_id = ? ");
    $stmt->bind_param("ssii", $edit_task_name, $edit_description, $edit_id, $user_id);

    $stmt->execute();
    $stmt->close();
    header("Location: index.php");
    exit;
}

$usersStatus = 'SELECT * FROM tasks';
$userResult = mysqli_query($conn, $usersStatus);
// if (mysqli_num_rows($userResult) > 0) {
//     while ($row = mysqli_fetch_assoc($userResult)) {
//         echo $row['status'], $row['user_id'];
//     }
// }


// Fetch unread notifications for this user
$notif_stmt = $conn->prepare("SELECT id, message, created_at FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC");
$notif_stmt->bind_param("i", $user_id);
$notif_stmt->execute();
$notif_result = $notif_stmt->get_result();
$notif_count = $notif_result->num_rows;
$notif_stmt->close();

// Mark notification as read (from user dashboard)
if (isset($_POST['mark_read'])) {
    $notif_id = intval($_POST['notif_id']);
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $notif_id, $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: index.php");
    exit;
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>User Dashboard</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
	<link rel="stylesheet" href="./styling/index.css">
	<style>
		.logout-form {
			margin: 0;
		}

		.logout-btn {
			display: inline-flex;
			align-items: center;
			gap: 0.5rem;
		}

		.logout-controls {
			display: flex;
			flex-direction: column;
			align-items: flex-end;
			gap: 0.75rem;
		}

		.notification-bell {
			position: relative;
			cursor: pointer;
		}

		.notification-dropdown {
			display: none;
			position: absolute;
			right: 0;
			top: 36px;
			width: 320px;
			z-index: 1000;
		}

		/* Navbar/header improvements */
		.navbar-custom {
			background: linear-gradient(90deg, #2b6cb0 0%, #667eea 50%, #764ba2 100%);
			color: #fff;
			border-radius: 8px;
			box-shadow: 0 8px 22px rgba(38, 70, 160, 0.12);
		}

		.navbar-custom .icon-box {
			width: 46px;
			height: 46px;
			border-radius: 8px;
			background: rgba(255, 255, 255, 0.12);
			display: flex;
			align-items: center;
			justify-content: center;
			box-shadow: inset 0 -2px 0 rgba(0, 0, 0, 0.06);
		}

		.navbar-custom .btn {
			color: #fff;
		}

		.notification-bell {
			position: relative;
		}

		.notification-dropdown.card {
			min-width: 300px;
			top: 48px;
			right: 0;
			box-shadow: 0 10px 28px rgba(0, 0, 0, 0.14);
		}

		/* Dropdown action menu */
		.action-dropdown {
			position: relative;
			display: inline-block;
		}
		.action-dropdown-toggle {
			background: transparent;
			border: none;
			font-size: 20px;
			cursor: pointer;
			padding: 4px 8px;
			color: #555;
		}
		.action-dropdown-toggle:hover {
			color: #000;
		}
		.action-dropdown-menu {
			display: none;
			position: absolute;
			right: 0;
			top: 100%;
			background: #fff;
			border: 1px solid #ddd;
			border-radius: 6px;
			box-shadow: 0 4px 12px rgba(0,0,0,0.1);
			min-width: 160px;
			z-index: 100;
			overflow: hidden;
		}
		.action-dropdown-menu.show {
			display: block;
		}
		.action-dropdown-menu a,
		.action-dropdown-menu button {
			display: block;
			width: 100%;
			padding: 10px 14px;
			text-align: left;
			border: none;
			background: transparent;
			color: #333;
			text-decoration: none;
			cursor: pointer;
			transition: background 0.2s;
		}
		.action-dropdown-menu a:hover,
		.action-dropdown-menu button:hover {
			background: #f5f5f5;
		}
		.action-dropdown-menu .text-danger:hover {
			background: #fee;
		}
		.action-dropdown-menu .text-info:hover {
			background: #e7f3ff;
		}

		@media (max-width: 768px) {
			.navbar-custom {
				padding: 12px;
			}

			.notification-dropdown.card {
				left: 10px;
				right: 10px;
				top: 70px;
			}
		}
	</style>
</head>

<body>
	<div class="container">
		<!-- Navbar -->
		<nav class="navbar navbar-custom mb-4 p-3">
			<div class="container-fluid d-flex justify-content-between align-items-center">
				<div class="d-flex align-items-center">
					<div class="icon-box me-3">
						<i class="fas fa-tasks text-white fs-5"></i>
					</div>
					<span class="text-white fs-6">
						Welcome, <span class="text-white fw-bold"><?php echo htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8'); ?></span>
					</span>
				</div>

				<div class="d-flex align-items-center gap-3">
					<!-- Notification bell -->
					<div class="notification-bell position-relative" onclick="toggleNotifications()" style="cursor:pointer;">
						<i class="fas fa-bell fa-lg text-white"></i>
						<?php if ($notif_count > 0): ?>
							<span class="badge bg-danger position-absolute" style="top:-6px; right:-8px;"><?php echo $notif_count; ?></span>
						<?php endif; ?>

						<div class="notification-dropdown card" id="notificationDropdown" style="display:none; position:absolute;">
							<div class="card-header">
								<strong>Notifications</strong> (<?php echo $notif_count; ?>)
							</div>
							<div class="list-group list-group-flush" style="max-height:300px; overflow:auto;">
								<?php if ($notif_count > 0): ?>
									<?php while ($n = $notif_result->fetch_assoc()): ?>
										<div class="list-group-item">
											<div class="small text-muted"><?php echo date('M d, Y h:i A', strtotime($n['created_at'])); ?></div>
											<div><?php echo htmlspecialchars($n['message']); ?></div>
											<form method="POST" style="margin-top:6px;">
												<input type="hidden" name="notif_id" value="<?php echo $n['id']; ?>">
												<button type="submit" name="mark_read" class="btn btn-sm btn-primary">Mark as read</button>
											</form>
										</div>
									<?php endwhile; ?>
								<?php else: ?>
									<div class="list-group-item text-center text-muted py-4">No new notifications</div>
								<?php endif; ?>
							</div>
						</div>
					</div>

					<a href="homepage.php" class="btn btn-success btn-sm d-flex align-items-center gap-2">
						<i class="fas fa-home"></i> Back
					</a>

					<form action="" method="post" class="logout-form" style="margin:0;">
						<button type="submit" name="logout" class="btn btn-danger btn-sm">
							<i class="fas fa-sign-out-alt"></i> Logout
						</button>
					</form>
				</div>
			</div>
		</nav>

		<!-- Dashboard -->
		<div class="dashboard-card p-4 p-md-5">
			<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
				<div class="mb-3 mb-md-0">
					<h1 class="display-5 fw-bold text-dark mb-2">My Task Manager</h1>
					<p class="text-muted mb-0">Manage and organize your tasks efficiently</p>
				</div>
				<button class="btn btn-add btn-success text-white px-4 py-2" data-bs-toggle="modal" data-bs-target="#addTaskModal">
					<i class="fas fa-plus-circle me-2"></i>Add New Task
				</button>
			</div>

			<!-- Task Table -->
			<?php if (mysqli_num_rows($tasks_result) > 0): ?>
				<div class="table-responsive">
					<table class="table table-hover mb-0">
						<thead>
							<tr>
								
								<th>Post Title</th>
                                <th>Image</th>
								<th>Created At</th>
								<th>Status</th>
								<th class="text-center">Actions</th>
							</tr>
						</thead>
						<tbody>
							<?php while ($task = mysqli_fetch_assoc($tasks_result)): ?>
								<tr>
                                    <td><?php echo htmlspecialchars($task['task_name']); ?></td>
									<td>
										<?php if (!empty($task['image'])): ?>
											<img src="<?php echo htmlspecialchars($task['image']); ?>" alt="Task Image" style="width:60px; height:60px; object-fit:cover;">
										<?php else: ?>
											<span class="text-muted">No Image</span>
										<?php endif; ?>
									</td>
									
									<td><?php echo date('Y-m-d', strtotime($task['created_at'])); ?></td>

									

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
                                    <td class="text-center">
										<div class="action-dropdown">
											<button class="action-dropdown-toggle" onclick="toggleActionMenu(event, this)" aria-label="Actions">
												....
											</button>
											<div class="action-dropdown-menu">
												<button type="button" class="text-info" data-bs-toggle="modal" data-bs-target="#viewDescModal" data-name="<?php echo htmlspecialchars($task['task_name'], ENT_QUOTES, 'UTF-8'); ?>" data-desc="<?php echo htmlspecialchars($task['description'], ENT_QUOTES, 'UTF-8'); ?>" data-created="<?php echo date('M d, Y', strtotime($task['created_at'])); ?>">
													<i class="fas fa-eye me-2"></i>View Description
												</button>
												<button type="button" class="text-warning" data-bs-toggle="modal" data-bs-target="#editTaskModal" data-id="<?php echo $task['id']; ?>" data-name="<?php echo htmlspecialchars($task['task_name'], ENT_QUOTES, 'UTF-8'); ?>" data-desc="<?php echo htmlspecialchars($task['description'], ENT_QUOTES, 'UTF-8'); ?>">
													<i class="fas fa-edit me-2"></i>Edit
												</button>
												<a href="index.php?delete_id=<?php echo $task['id']; ?>" class="text-danger" onclick="return confirm('Delete this task?')">
													<i class="fas fa-trash-alt me-2"></i>Delete
												</a>
											</div>
										</div>
									</td>
								</tr>
							<?php endwhile; ?>
						</tbody>
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

		<!-- Footer -->
		<div class="text-center mt-4 text-white">
			<p class="mb-0">&copy; 2025 My Task Manager. Built with ❤️</p>
		</div>
	</div>

	<!-- Add Task Modal -->
	<div class="modal fade" id="addTaskModal" tabindex="-1" aria-labelledby="addTaskModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<form method="POST" action="" enctype="multipart/form-data">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="addTaskModalLabel">Add New Task</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
					</div>
					<div class="modal-body">
						<div class="mb-3">
							<label class="form-label">Task Name</label>
							<input type="text" name="task_name" class="form-control" required>
						</div>
						<div class="mb-3">
							<label class="form-label">Description</label>
							<textarea name="description" class="form-control" rows="3"></textarea>
						</div>
						<div class="mb-3">
							<label class="form-label">Task Image</label>
							<input type="file" name="task_image" class="form-control" accept="image/*">
						</div>
					</div>
					<div class="modal-footer">
						<button type="submit" name="add_task" class="btn btn-success">Add Task</button>
					</div>
				</div>
			</form>
		</div>
	</div>

	<!-- Edit Task Modal -->
	<div class="modal fade" id="editTaskModal" tabindex="-1" aria-labelledby="editTaskModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<form method="POST" action="" enctype="multipart/form-data">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="editTaskModalLabel">Edit Task</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
					</div>
					<div class="modal-body">
						<input type="hidden" name="edit_id" id="edit_id">
						<div class="mb-3">
							<label class="form-label">Task Name</label>
							<input type="text" name="edit_task_name" id="edit_task_name" class="form-control" required>
						</div>
						<div class="mb-3">
							<label class="form-label">Description</label>
							<textarea name="edit_description" id="edit_description" class="form-control" rows="3"></textarea>
						</div>
						<div class="mb-3">
							<label class="form-label">Task Image</label>
							<input type="file" name="edit_task_image" class="form-control" accept="image/*">
						</div>
					</div>
					<div class="modal-footer">
						<button type="submit" name="update_task" class="btn btn-success">Save Changes</button>
					</div>
				</div>
			</form>
		</div>
	</div>

	<!-- View Description Modal -->
	<div class="modal fade" id="viewDescModal" tabindex="-1" aria-labelledby="viewDescModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="viewDescModalLabel">Task Details</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="mb-3">
						<label class="form-label fw-bold">Task Name</label>
						<p id="view_task_name" class="form-control-plaintext border-bottom pb-2"></p>
					</div>
					<div class="mb-3">
						<label class="form-label fw-bold">Description</label>
						<p id="view_description" class="form-control-plaintext" style="white-space: pre-wrap;"></p>
					</div>
					<div class="mb-0">
						<label class="form-label fw-bold">Created At</label>
						<p id="view_created_at" class="form-control-plaintext text-muted"></p>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

	<script>
		const editTaskModal = document.getElementById('editTaskModal');
		if (editTaskModal) {
			editTaskModal.addEventListener('show.bs.modal', function(event) {
				const button = event.relatedTarget;
				document.getElementById('edit_id').value = button.getAttribute('data-id');
				document.getElementById('edit_task_name').value = button.getAttribute('data-name');
				document.getElementById('edit_description').value = button.getAttribute('data-desc');
			});
		}

		// Handle View Description Modal
		const viewDescModal = document.getElementById('viewDescModal');
		if (viewDescModal) {
			viewDescModal.addEventListener('show.bs.modal', function(event) {
				const button = event.relatedTarget;
				document.getElementById('view_task_name').textContent = button.getAttribute('data-name');
				document.getElementById('view_description').textContent = button.getAttribute('data-desc');
				document.getElementById('view_created_at').textContent = button.getAttribute('data-created');
			});
		}

		function toggleNotifications() {
			const dd = document.getElementById('notificationDropdown');
			if (!dd) return;
			dd.style.display = (dd.style.display === 'block') ? 'none' : 'block';
		}
		document.addEventListener('click', function(e) {
			const bell = document.querySelector('.notification-bell');
			const dd = document.getElementById('notificationDropdown');
			if (!bell || !dd) return;
			if (!bell.contains(e.target)) dd.style.display = 'none';
		});

		function toggleActionMenu(event, button) {
			event.stopPropagation();
			const menu = button.nextElementSibling;
			// Close all other dropdowns
			document.querySelectorAll('.action-dropdown-menu.show').forEach(m => {
				if (m !== menu) m.classList.remove('show');
			});
			menu.classList.toggle('show');
		}

		// Close dropdown when clicking outside
		document.addEventListener('click', function() {
			document.querySelectorAll('.action-dropdown-menu.show').forEach(m => m.classList.remove('show'));
		});
	</script>

</body>

</html>