# 📋 Task Manager System

![PHP](https://img.shields.io/badge/PHP-8.2.12-blue)
![MySQL](https://img.shields.io/badge/MySQL-Database-orange)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3.2-purple)
![License](https://img.shields.io/badge/License-MIT-green)

A comprehensive **Task Management System** built with PHP and MySQL featuring
role-based access control (RBAC), real-time notifications, and an intuitive user
interface. Perfect for teams and individuals looking to organize their tasks
efficiently! 🚀

---

## 📑 Table of Contents

- [✨ Features](#-features)
- [🛠️ Technologies Used](#️-technologies-used)
- [📋 Prerequisites](#-prerequisites)
- [⚙️ Installation](#️-installation)
- [🗄️ Database Setup](#️-database-setup)
- [🎯 Usage Guide](#-usage-guide)
- [👥 User Roles & Permissions](#-user-roles--permissions)
- [📂 Project Structure](#-project-structure)
- [🖼️ Screenshots](#️-screenshots)
- [🔐 Security Features](#-security-features)
- [🤝 Contributing](#-contributing)
- [📝 License](#-license)
- [📧 Contact](#-contact)

---

## ✨ Features

### 🎨 Core Features

- ✅ **User Authentication** - Secure login and registration system
- 📝 **Task Management** - Create, read, update, and delete tasks (CRUD
  operations)
- 🖼️ **Image Upload** - Attach images to your tasks
- 🔔 **Real-time Notifications** - Get notified about task approvals/rejections
- 📊 **Task Status Tracking** - Monitor task status (Pending, Approved,
  Rejected, Blocked)
- 📄 **Pagination** - Navigate through tasks efficiently
- 🎭 **Role-Based Access Control (RBAC)** - Different permissions for different
  user roles

### 👤 User Features

- Create and manage personal tasks
- Upload task images
- View task descriptions in detail
- Receive notifications about task status changes
- Delete tasks (soft delete)
- Edit existing tasks with image updates

### 👨‍💼 Admin Features

- Review and approve/reject user tasks
- Block inappropriate tasks
- View all user submissions
- Manage user permissions
- Dashboard with comprehensive statistics

### 🔐 Security Features

- Password hashing with `password_hash()`
- Prepared SQL statements to prevent SQL injection
- Session management
- XSS protection with `htmlspecialchars()`
- Role-based middleware protection
- CSRF protection ready

---

## 🛠️ Technologies Used

### Backend

- **PHP 8.2.12** - Server-side scripting language
- **MySQL** - Relational database management
- **MySQLi** - Database connectivity

### Frontend

- **HTML5** - Markup language
- **CSS3** - Styling with custom animations
- **Bootstrap 5.3.2** - Responsive CSS framework
- **JavaScript (ES6+)** - Client-side interactions
- **Font Awesome 6.4.0** - Icon library

### Development Tools

- **XAMPP** - Local development environment
- **Git** - Version control
- **VS Code** - Code editor (recommended)

---

## 📋 Prerequisites

Before you begin, ensure you have the following installed:

- 🖥️ **XAMPP** (or any PHP development environment)
  - PHP >= 8.0
  - MySQL >= 5.7
  - Apache Server
- 📝 **Text Editor** (VS Code, Sublime Text, etc.)
- 🌐 **Web Browser** (Chrome, Firefox, Edge)
- 📦 **Git** (optional, for cloning)

---

## ⚙️ Installation

### 1️⃣ Clone the Repository

```bash
# Using Git
git clone https://github.com/yourusername/task-manager.git

# Or download ZIP and extract
```

### 2️⃣ Move to XAMPP Directory

```bash
# Move the project folder to XAMPP htdocs
# Windows: C:\xampp\htdocs\task_manager
# Linux: /opt/lampp/htdocs/task_manager
# Mac: /Applications/XAMPP/htdocs/task_manager
```

### 3️⃣ Start XAMPP Services

1. Open **XAMPP Control Panel**
2. Start **Apache** server
3. Start **MySQL** database

---

## 🗄️ Database Setup

### Step 1: Create Database

1. Open **phpMyAdmin**: `http://localhost/phpmyadmin`
2. Create a new database named `task_manager`
3. Select the database

### Step 2: Import Database Schema

Execute the following SQL commands or import the provided SQL file:

```sql
-- Create Users Table
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `role_id` int(11) DEFAULT 3,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create Roles Table
CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert Default Roles
INSERT INTO `roles` (`id`, `name`) VALUES
(1, 'Super Admin'),
(2, 'Admin'),
(3, 'User');

-- Create Tasks Table
CREATE TABLE `tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `task_name` varchar(255) NOT NULL,
  `description` text,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected','blocked') DEFAULT 'pending',
  `is_deleted` tinyint(1) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create Permissions Table
CREATE TABLE `permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert Default Permissions
INSERT INTO `permissions` (`name`, `description`) VALUES
('add_task', 'Create new tasks'),
('edit_task', 'Edit existing tasks'),
('delete_task', 'Delete tasks'),
('view-details', 'View task details'),
('approve_task', 'Approve pending tasks'),
('reject_task', 'Reject tasks'),
('block_task', 'Block tasks');

-- Create Role_Permissions Table
CREATE TABLE `role_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Assign Permissions to User Role (role_id = 3)
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(3, 1), -- add_task
(3, 2), -- edit_task
(3, 3), -- delete_task
(3, 4); -- view-details

-- Assign All Permissions to Admin Role (role_id = 2)
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(2, 1), (2, 2), (2, 3), (2, 4), (2, 5), (2, 6), (2, 7);

-- Create Notifications Table
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Step 3: Configure Database Connection

Edit `db_connect.php`:

```php
<?php
$servername = "localhost";
$username = "root";
$password = "";  // Your MySQL password (default is empty)
$dbname = "task_manager";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
```

---

## 🎯 Usage Guide

### 🚀 Getting Started

1. **Access the Application**

   ```
   http://localhost/task_manager/homepage.php
   ```

2. **Register a New Account**

   - Click on "Sign Up"
   - Fill in your details (Name, Email, Password)
   - Default role: **User**

3. **Login**
   - Use your registered email and password
   - You'll be redirected to your dashboard

### 📝 Managing Tasks

#### ➕ Adding a Task

1. Click **"Add New Task"** button
2. Enter task name and description
3. Upload an image (optional)
4. Click **"Add Task"**
5. Task will be created with **Pending** status

#### ✏️ Editing a Task

1. Click the **three dots** (•••) on any task row
2. Select **"Edit"**
3. Modify task details
4. Upload new image (optional)
5. Click **"Save Changes"**

#### 👁️ Viewing Task Details

1. Click **three dots** (•••)
2. Select **"View Description"**
3. See full task details in modal

#### 🗑️ Deleting a Task

1. Click **three dots** (•••)
2. Select **"Delete"**
3. Confirm deletion
4. Task will be soft-deleted (not removed from database)

### 🔔 Notifications

- Bell icon in navbar shows unread notification count
- Click bell to view notifications
- Mark notifications as read individually
- Admins receive notifications when users submit tasks
- Users receive notifications about task status changes

---

## 👥 User Roles & Permissions

### 🔵 User (Role ID: 3)

**Permissions:**

- ✅ Add tasks
- ✅ Edit own tasks
- ✅ Delete own tasks
- ✅ View task details

**Restrictions:**

- ❌ Cannot approve/reject tasks
- ❌ Cannot view other users' tasks
- ❌ Cannot access admin panel

### 🟡 Admin (Role ID: 2)

**Permissions:**

- ✅ All user permissions
- ✅ Approve pending tasks
- ✅ Reject tasks
- ✅ Block tasks
- ✅ View all users' tasks
- ✅ Access admin dashboard

### 🔴 Super Admin (Role ID: 1)

**Permissions:**

- ✅ All admin permissions
- ✅ Manage users
- ✅ Modify roles and permissions
- ✅ System configuration

---

## 📂 Project Structure

```
task_manager/
│
├── 📄 index.php              # User Dashboard
├── 📄 admin_dashboard.php    # Admin Dashboard
├── 📄 homepage.php           # Landing Page
├── 📄 login.php              # Login Page
├── 📄 registration.php       # Registration Page
├── 📄 db_connect.php         # Database Connection
├── 📄 functions.php          # Helper Functions
├── 📄 middleware.php         # Access Control Middleware
│
├── 📁 uploads/               # Task Images Directory
│   └── 🖼️ [uploaded images]
│
├── 📁 styling/               # CSS Stylesheets
│   ├── index.css
│   ├── admin_dashboard.css
│   ├── homepage.css
│   └── login.css
│
├── 📁 assets/                # Static Assets
│   ├── images/
│   └── icons/
│
└── 📄 README.md              # This File
```

---

## 🖼️ Screenshots

### 🏠 Homepage

![Homepage](screenshots/homepage.png) _Beautiful landing page with gradient
background and modern design_

### 📊 User Dashboard

![User Dashboard](screenshots/user-dashboard.png) _Clean interface for managing
personal tasks_

### 👨‍💼 Admin Dashboard

![Admin Dashboard](screenshots/admin-dashboard.png) _Comprehensive admin panel
for task review and approval_

### 🔔 Notifications

![Notifications](screenshots/notifications.png) _Real-time notification system_

### ➕ Add Task Modal

![Add Task](screenshots/add-task.png) _Easy-to-use task creation form_

---

## 🔐 Security Features

### 🛡️ Implemented Security Measures

1. **Password Security**

   - Passwords hashed using `password_hash()` with bcrypt
   - Never stored in plain text

2. **SQL Injection Prevention**

   - Prepared statements with parameter binding
   - Input validation and sanitization

3. **XSS Protection**

   - Output encoding with `htmlspecialchars()`
   - ENT_QUOTES flag for complete protection

4. **Session Security**

   - Secure session management
   - Session hijacking prevention
   - Auto-logout on inactivity

5. **Access Control**

   - Role-based middleware
   - Permission checking before sensitive operations
   - Unauthorized access prevention

6. **File Upload Security**
   - File type validation
   - File size restrictions
   - Unique filename generation
   - Secure upload directory

---

## 🤝 Contributing

We welcome contributions! Here's how you can help:

### 🌟 Ways to Contribute

1. **Report Bugs** 🐛

   - Open an issue with detailed description
   - Include steps to reproduce

2. **Suggest Features** 💡

   - Share your ideas via issues
   - Explain use cases

3. **Submit Pull Requests** 🔀
   - Fork the repository
   - Create a feature branch
   - Make your changes
   - Submit PR with description

### 📝 Contribution Guidelines

```bash
# Fork and clone
git clone https://github.com/MuhammadNadeemAkbar-12/task-manager.git

# Create feature branch
git checkout -b feature/amazing-feature

# Commit changes
git commit -m "Add amazing feature"

# Push to branch
git push origin feature/amazing-feature

# Open Pull Request
```

### ✅ Code Standards

- Follow PSR-12 coding standards
- Comment complex logic
- Write meaningful commit messages
- Test before submitting

---

## 📝 License

This project is licensed under the **MIT License** - see below for details:

```
MIT License

Copyright (c) 2025 Task Manager System

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

---

## 📧 Contact

### 👨‍💻 Developer Information

- **Name:** Your Name
- **Email:** nadeemakbar1201@gmail.com.com
- **GitHub:** [@yourusername](https://github.com/MuhammadNadeemAkbar-12)
- **LinkedIn:** [Your LinkedIn](https://www.linkedin.com/in/mh-nadeem/)


### 🐛 Report Issues

Found a bug? Have a question?

- Open an issue:
  [GitHub Issues](https://github.com/MuhammadNadeemAkbar-12/task-manager/issues)
- Email: support@yourproject.com

---

## 🙏 Acknowledgments

- **Bootstrap Team** - For the amazing CSS framework
- **Font Awesome** - For the beautiful icons
- **PHP Community** - For excellent documentation
- **Stack Overflow** - For troubleshooting help
- **You** - For using this project! ❤️

---

## 🚀 Future Enhancements

- [ ] Email notifications
- [ ] Task categories and tags
- [ ] Advanced search and filters
- [ ] Task priority levels
- [ ] Due date reminders
- [ ] Export tasks to PDF
- [ ] Dark mode theme
- [ ] Mobile app version
- [ ] RESTful API
- [ ] Multi-language support

---

## 📊 Project Stats

![GitHub stars](https://img.shields.io/github/stars/MuhammadNadeemAkbar-12/task-manager?style=social)
![GitHub forks](https://img.shields.io/github/forks/MuhammadNadeemAkbar-12/task-manager?style=social)
![GitHub issues](https://img.shields.io/github/issues/MuhammadNadeemAkbar-12/task-manager)
![GitHub pull requests](https://img.shields.io/github/issues-pr/MuhammadNadeemAkbar-12/task-manager)

---

<div align="center">

### ⭐ If you found this project helpful, please give it a star!

**Made with ❤️ and PHP**

[⬆ Back to Top](#-task-manager-system)

</div>
