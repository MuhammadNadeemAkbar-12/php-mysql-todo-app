<?php
include 'db_connect.php';
session_start();

$errorMsg = '';
$successMsg = '';

if (isset($_POST['register'])) {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $errors = [];

    // Validation checks
    if (empty($fullname)) {
        $errors[] = "Full name required!";
    }
    if (empty($email)) {
        $errors[] = "Email required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email required!";
    }
    if (empty($password)) {
        $errors[] = "Password required!";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password at least 6 digits!";
    }

    // Terms checkbox (agar required hai)
    // if (empty($_POST['terms'])) {
    //     $errors[] = "Terms & Conditions accept!";
    // }

    if (count($errors) > 0) {
        $errorMsg = "<ul style='color:red;'>";
        foreach ($errors as $err) {
            $errorMsg .= "<li>$err</li>";
        }
        $errorMsg .= "</ul>";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $check_sql = "SELECT id FROM users WHERE email = '$email'";
        $check_result = mysqli_query($conn, $check_sql);

        if (mysqli_num_rows($check_result) > 0) {
            $errorMsg = "<p style='color:red;'>Email already registered!</p>";
        } else {
            $insert_sql = "INSERT INTO users (name, email, password) VALUES ('$fullname', '$email', '$hashedPassword')";
            $result = mysqli_query($conn, $insert_sql);

            if ($result) {
                $successMsg = "<p style='color:green;'>Registration successful! Please login below.</p>";
                header("Location: login.php");
                exit();
            } else {
                $errorMsg = "<p style='color:red;'>Error while saving data: " . mysqli_error($conn) . "</p>";
            }
        }
    }
    mysqli_close($conn);
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register User</title>
    <link rel="stylesheet" href="./styling/register.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body.auth-body {
            background: radial-gradient(60% 120% at 10% 20%, rgba(255,255,255,0.18), transparent 55%), radial-gradient(50% 90% at 90% 80%, rgba(255,255,255,0.12), transparent 65%), linear-gradient(135deg, #ff7a9b, #6c4dff);
            font-family: 'Poppins', sans-serif;
        }
        .auth-card {
            max-width: 960px;
            border-radius: 32px;
        }
        .gradient-panel {
            background: linear-gradient(135deg, #ff7aa0, #8556ff);
            position: relative;
            overflow: hidden;
        }
        .gradient-panel::before,
        .gradient-panel::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,0.22);
        }
        .gradient-panel::before {
            width: 180px;
            height: 180px;
            top: -50px;
            right: -40px;
        }
        .gradient-panel::after {
            width: 140px;
            height: 140px;
            bottom: -40px;
            left: -30px;
            opacity: 0.35;
        }
        .icon-circle {
            width: 60px;
            height: 60px;
            background: rgba(255,255,255,0.18);
            box-shadow: 0 18px 35px rgba(0,0,0,0.16);
        }
        .feature-item {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 18px;
            opacity: 0.8;
        }
        .feature-bullet {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }
        .feature-bullet.orange { background: #ffb37a; }
        .feature-bullet.green { background: #6bffd6; }
        .feature-bullet.purple { background: #b09bff; }
        .form-wrapper {
            background: #fff;
        }
        .input-group-text {
            border-right: none;
            background: transparent;
            color: #7a52ff;
        }
        .input-group .form-control {
            border-left: none;
            padding: 0.75rem 1rem;
            border-radius: 0 16px 16px 0;
        }
        .input-group .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(122,82,255,0.15);
        }
        .btn-signup {
            border: none;
            border-radius: 18px;
            padding: 0.9rem;
            width: 100%;
            background: linear-gradient(90deg, #7c53ff, #ff5fa8);
            color: #fff;
            font-weight: 600;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .btn-signup:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(124,83,255,0.35);
            color: #fff;
        }
        .divider-text {
            position: relative;
            display: inline-block;
            padding: 0 0.75rem;
            color: #8d8f99;
            font-size: 0.85rem;
        }
        .divider-text::before,
        .divider-text::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 48px;
            height: 1px;
            background: rgba(0,0,0,0.08);
        }
        .divider-text::before { left: -48px; }
        .divider-text::after { right: -48px; }
        .social-btn {
            border-radius: 14px;
            padding: 0.75rem 1rem;
            font-weight: 500;
            color: #4c4d59;
            border: 1px solid rgba(0,0,0,0.08);
            background: #fff;
        }
        .social-btn:hover {
            background: rgba(124,83,255,0.08);
            color: #4c4d59;
        }
        .terms-text {
            font-size: 0.85rem;
            color: #8b8d97;
        }
        .auth-footer {
            text-align: center;
        }
        .auth-footer .login-link {
            color: #7c53ff;
            font-weight: 600;
        }
        .auth-footer .login-link:hover {
            text-decoration: underline;
        }
        .back-link {
            color: #8b8d97;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: color 0.2s ease;
        }
        .back-link:hover {
            color: #7c53ff;
        }
        @media (max-width: 991.98px) {
            .gradient-panel {
                text-align: center;
                padding: 3.5rem 2.5rem !important;
            }   
            .feature-item {
                justify-content: center;
            }
        }
        @media (max-width: 575.98px) {
            .auth-card {
                border-radius: 26px;
            }
        }
    </style>
</head>
<body class="auth-body">
    <div class="auth-wrapper d-flex align-items-center justify-content-center min-vh-100 px-3 px-lg-0">
        <div class="auth-card row g-0 shadow-lg overflow-hidden bg-white">
            <div class="col-lg-5 gradient-panel text-white p-5 d-flex flex-column justify-content-between">
                <div>
                    <div class="icon-circle rounded-4 d-inline-flex align-items-center justify-content-center mb-4">
                        <i class="fas fa-star fs-4"></i>
                    </div>
                    <h2 class="fw-bold mb-2">Create Your Account</h2>
                    <p class="mb-4 opacity-80">
                        Unlock smarter task workflows, collaborate seamlessly, and track progress effortlessly with My Task Manager.
                    </p>
                    <div>
                        <div class="feature-item">
                            <span class="feature-bullet orange"></span>
                            <span>All tasks organized in one powerful dashboard</span>
                        </div>
                        <div class="feature-item">
                            <span class="feature-bullet green"></span>
                            <span>Stay informed with intelligent reminders</span>
                        </div>
                        <div class="feature-item">
                            <span class="feature-bullet purple"></span>
                            <span>Customize workflows that fit your style</span>
                        </div>
                    </div>
                </div>
                <small class="opacity-75">© 2025 My Task Manager. All rights reserved.</small>
            </div>
            <div class="col-lg-7 form-wrapper p-5">
                <div class="text-center mb-4">
                    <h3 class="fw-bold mb-1">Sign Up</h3>
                    <p class="text-muted mb-0">Join now and start managing your tasks better</p>
                </div>
                <form method="post" action="">
                    <div class="mb-3">
                        <label for="fullname" class="form-label fw-semibold text-muted">Full Name</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" id="fullname" name="fullname" placeholder="Enter your full name" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold text-muted">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label fw-semibold text-muted">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Create a strong password" required>
                        </div>
                    </div>
                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                        <label class="form-check-label terms-text" for="terms">
                            I agree to the Terms &amp; Conditions
                        </label>
                    </div>
                    <button type="submit" name="register" class="btn-signup">Create Account</button>
                </form>
                <div class="mt-3">
                    <?php
                    if (!empty($successMsg)) {
                        echo $successMsg;
                    }
                    if (!empty($errorMsg)) {
                        echo $errorMsg;
                    }
                    ?>
                </div>
                <div class="text-center my-4">
                    <span class="divider-text">Or sign up with</span>
                </div>
                <div class="d-flex gap-3 mb-4">
                    <button type="button" class="social-btn w-50">
                        <i class="fab fa-google me-2"></i>Google
                    </button>
                    <button type="button" class="social-btn w-50">
                        <i class="fab fa-facebook-f me-2"></i>Facebook
                    </button>
                </div>
                <div class="auth-footer">
                    <p class="text-muted mb-3">
                        Already have an account?
                        <a href="login.php" class="login-link text-decoration-none">Log in here</a>
                    </p>
                    <a href="landingPage.php" class="back-link">
                        <i class="fas fa-arrow-left"></i>
                        Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>