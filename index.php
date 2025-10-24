<?php
session_start();
include 'db_connect.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: landingPage.php");
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
                    <span class="text-muted fs-6">
                        Welcome, <span class="text-primary fw-bold"><?php echo htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8'); ?></span>
                    </span>
                </div>
                <a href="index.php?logout=true" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
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
                                <th>Image</th>
                                <th>Task Name</th>
                                <th>Description</th>
                                <th class="text-center">Actions</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($task = mysqli_fetch_assoc($tasks_result)): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($task['image'])): ?>
                                            <img src="<?php echo htmlspecialchars($task['image']); ?>" alt="Task Image" style="width:60px; height:60px; object-fit:cover;">
                                        <?php else: ?>
                                            <span class="text-muted">No Image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($task['task_name']); ?></td>
                                    <td><?php echo htmlspecialchars($task['description']); ?></td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <button class="btn btn-warning btn-sm text-white" data-bs-toggle="modal" data-bs-target="#editTaskModal" data-id="<?php echo $task['id']; ?>" data-name="<?php echo htmlspecialchars($task['task_name'], ENT_QUOTES, 'UTF-8'); ?>" data-desc="<?php echo htmlspecialchars($task['description'], ENT_QUOTES, 'UTF-8'); ?>"><i class="fas fa-edit me-1"></i>Edit</button>
                                            <a href="index.php?delete_id=<?php echo $task['id']; ?>" class="btn btn-danger btn-sm text-white" onclick="return confirm('Delete this task?')">
                                                <i class="fas fa-trash-alt me-1"></i>Delete
                                            </a>
                                        </div>
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
    </script>

</body>

</html>