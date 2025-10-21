# 🗂️ My Task Manager

![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-4479A1?logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5-7952B3?logo=bootstrap&logoColor=white)

A clean and intuitive task management dashboard that helps you organize personal
work with image attachments, inline editing, and secure authentication.

---

## 📋 Table of Contents

- [Features](#-features)
- [Tech Stack](#-tech-stack)
- [Getting Started](#-getting-started)
- [Configuration](#-configuration)
- [Project Structure](#-project-structure)
- [Usage](#-usage)
- [Roadmap](#-roadmap)
- [Contributing](#-contributing)
- [License](#-license)

---

## ✨ Features

- 🔐 Session-based authentication flow with logout support.
- ✅ Create, edit, and delete tasks with instant UI updates.
- 🖼️ Optional task image uploads stored per user.
- 📱 Responsive Bootstrap 5 layout ready for desktop and mobile.
- 🧰 Organized modals for add/edit interactions.

---

## 🛠 Tech Stack

- **Backend:** PHP 8+, MySQLi
- **Frontend:** Bootstrap 5, Font Awesome 6
- **Environment:** XAMPP / LAMP stack

---

## 🚀 Getting Started

### Prerequisites

- PHP 8.x
- MySQL 5.7+ (or MariaDB)
- Composer _(optional, if you extend the project)_
- XAMPP or equivalent local stack

### Installation

```bash
git clone https://github.com/<your-handle>/task_manager.git
cd task_manager
```

1. Import `database.sql` _(create one matching your schema)_ into MySQL.
2. Update `db_connect.php` with your database credentials.
3. Place the project inside your web root (e.g.,
   `c:\xampp\htdocs\php\task_manager`).
4. Ensure the `uploads/` directory is writable (`chmod 775 uploads` on Linux).

---

## ⚙️ Configuration

Add environment values in `db_connect.php`:

```php
// Example
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'task_manager';
```

> 🔒 Keep credentials out of version control in production deployments.

---

## 🧭 Project Structure

```text
task_manager/
├─ index.php
├─ login.php
├─ db_connect.php
├─ styling/
│  └─ index.css
├─ uploads/
└─ README.md
```

---

## 📚 Usage

1. Register/login via the authentication screen.
2. Click **Add New Task** to create entries with optional images.
3. Use the **Edit** button (pencil icon) for inline updates.
4. **Delete** removes tasks permanently after confirmation.

---

## 🗺 Roadmap

- [ ] Password reset flow
- [ ] Task categories & priorities
- [ ] REST API endpoints
- [ ] Unit and E2E test coverage

---

## 🤝 Contributing

Pull requests are welcome! Please open an issue first to discuss major changes.

1. Fork the repository.
2. Create your feature branch.
3. Commit using conventional messages.
4. Open a pull request targeting `main`.

---

## 📄 License

This project is licensed under the [MIT License](LICENSE) — feel free to adapt
and extend.
