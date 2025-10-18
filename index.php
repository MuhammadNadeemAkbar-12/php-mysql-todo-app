<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Task Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        .navbar-custom {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .dashboard-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        .table-wrapper {
            border-radius: 10px;
            overflow: hidden;
        }
        .task-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px;
            border: 1px solid #dee2e6;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .task-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .icon-box {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .icon-box-small {
            width: 35px;
            height: 35px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .btn-add {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            box-shadow: 0 4px 6px rgba(16, 185, 129, 0.3);
        }
        .btn-add:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(16, 185, 129, 0.4);
        }
        .btn-edit {
            background-color: #fbbf24;
            border: none;
        }
        .btn-edit:hover {
            background-color: #f59e0b;
        }
        .btn-delete {
            background-color: #ef4444;
            border: none;
        }
        .btn-delete:hover {
            background-color: #dc2626;
        }
        .status-badge {
            padding: 6px 16px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.875rem;
        }
        .status-done {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }
        thead {
            background: linear-gradient(135deg, #e0e7ff 0%, #ede9fe 100%);
        }
        .table > tbody > tr:hover {
            background-color: #f9fafb;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Navbar -->
        <nav class="navbar navbar-custom mb-4 p-3">
            <div class="container-fluid">
                <div class="d-flex align-items-center">
                    <div class="icon-box me-3">
                        <i class="fas fa-tasks text-white fs-5"></i>
                    </div>
                    <span class="text-muted fs-6">
                        Welcome, <span class="text-primary fw-bold"></span>
                    </span>
                </div>
                <button class="btn btn-danger">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </button>
            </div>
        </nav>

        <!-- Main Dashboard Card -->
        <div class="dashboard-card p-4 p-md-5">
            <!-- Header Section -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
                <div class="mb-3 mb-md-0">
                    <h1 class="display-5 fw-bold text-dark mb-2">My Task Manager</h1>
                    <p class="text-muted mb-0">Manage and organize your tasks efficiently</p>
                </div>
                <button class="btn btn-add btn-success text-white px-4 py-2">
                    <i class="fas fa-plus-circle me-2"></i>Add New Task
                </button>
            </div>

            <!-- Desktop Table View -->
            <div class="d-none d-md-block table-wrapper">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th scope="col" class="py-3 px-4">Task Name</th>
                            <th scope="col" class="py-3 px-4">Status</th>
                            <th scope="col" class="py-3 px-4 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="py-3 px-4">
                                <div class="d-flex align-items-center">
                                    <div class="icon-box-small me-3">
                                        <i class="fas fa-clipboard-list text-white"></i>
                                    </div>
                                    <span class="fw-medium text-dark"></span>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                               
                                <span class="status-badge status-done">
                                    <i class="fas fa-check-circle me-1"></i>Done
                                </span>
                                
                                <span class="status-badge status-pending">
                                    <i class="fas fa-clock me-1"></i>Pending
                                </span>
                                
                            </td>
                            <td class="py-3 px-4">
                                <div class="d-flex justify-content-center gap-2">
                                    <button class="btn btn-edit btn-warning text-white btn-sm px-3">
                                        <i class="fas fa-edit me-1"></i>Edit
                                    </button>
                                    <button class="btn btn-delete btn-danger text-white btn-sm px-3">
                                        <i class="fas fa-trash-alt me-1"></i>Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="d-md-none">
                <div class="task-card p-3 mb-3">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-box-small me-3">
                            <i class="fas fa-clipboard-list text-white"></i>
                        </div>
                        <h5 class="mb-0 fw-semibold"></h5>
                    </div>
                    
                    <div class="mb-3">
                        <span class="status-badge status-done">
                            <i class="fas fa-check-circle me-1"></i>Done
                        </span>
                        <span class="status-badge status-pending">
                            <i class="fas fa-clock me-1"></i>Pending
                        </span>
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn btn-edit btn-warning text-white flex-fill">
                            <i class="fas fa-edit me-2"></i>Edit
                        </button>
                        <button class="btn btn-delete btn-danger text-white flex-fill">
                            <i class="fas fa-trash-alt me-2"></i>Delete
                        </button>
                    </div>
                </div>
            </div>

            <!-- Empty State (show when no tasks) -->
            <!--
            <div class="text-center py-5">
                <div class="icon-box mx-auto mb-4" style="width: 80px; height: 80px;">
                    <i class="fas fa-inbox text-white fs-1"></i>
                </div>
                <h3 class="fw-semibold text-dark mb-2">No Tasks Yet</h3>
                <p class="text-muted mb-4">Start by adding your first task to get organized!</p>
                <button class="btn btn-add btn-success text-white px-4 py-2">
                    <i class="fas fa-plus-circle me-2"></i>Add Your First Task
                </button>
            </div>
            -->
        </div>

        <!-- Footer -->
        <div class="text-center mt-4 text-white">
            <p class="mb-0">&copy; 2025 My Task Manager. Built with ❤️</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>