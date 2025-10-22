<?php
session_start(); 
include 'db_connect.php'; 

$errorMsg = ''; 




if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];    
    $errors = [];

    // Validation checks
    if (empty($email)) {
        $errors[] = "Email required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email required!";
    }
    if (empty($password)) {
        $errors[] = "Password required!";
    }

    if (count($errors) > 0) {
        $errorMsg = "<ul style='color:red;'>";
        foreach ($errors as $err) {
            $errorMsg .= "<li>$err</li>";
        }
        $errorMsg .= "</ul>";
    } else {
        $sql = "SELECT * FROM users WHERE email = '$email'";
        $result = mysqli_query($conn, $sql);
        // print_r($result);
        $row = mysqli_fetch_assoc($result);
        if ($row) {
             if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['user_name'] = $row['name'];
                $_SESSION['user_role'] = $row['role'];
 
                if($row['role'] == 'admin') {
                   header("Location: admin-dashboard.php");
                    exit;
                }else{
                    header("Location: index.php");
                    exit;
                }
            } else {
                $errorMsg = "<p style='color:red; text-align:center;'>Incorrect password!</p>";
            }
        } else {
            $errorMsg = "<p style='color:red; text-align:center;'>No account found with this email!</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login User</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./styling/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body.auth-body {
            background: radial-gradient(60% 120% at 15% 20%, rgba(255,255,255,0.18), transparent 55%), radial-gradient(50% 90% at 85% 80%, rgba(255,255,255,0.12), transparent 65%), linear-gradient(135deg, #3c28ff, #ff3cb4);
            font-family: 'Poppins', sans-serif;
        }
        .auth-card {
            max-width: 920px;
            border-radius: 32px;
        }
        .gradient-panel {
            background: linear-gradient(135deg, #5b3dff, #a829ff);
            position: relative;
        }
        .gradient-panel::before,
        .gradient-panel::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,0.18);
            filter: blur(0);
        }
        .gradient-panel::before {
            width: 160px;
            height: 160px;
            top: -40px;
            right: -40px;
            opacity: 0.5;
        }
        .gradient-panel::after {
            width: 120px;
            height: 120px;
            bottom: -30px;
            left: -30px;
            opacity: 0.35;
        }
        .icon-circle {
            width: 56px;
            height: 56px;
            background: rgba(255,255,255,0.16);
            box-shadow: 0 18px 35px rgba(0,0,0,0.12);
        }
        .feature-bullet {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 10px;
        }
        .feature-bullet.pink { background: #ff9bcf; }
        .feature-bullet.green { background: #4ef3c7; }
        .feature-bullet.blue { background: #6be0ff; }
        .input-group-text {
            background: transparent;
            border-right: none;
            color: #744bff;
        }
        .input-group .form-control {
            border-left: none;
            padding: 0.75rem 1rem;
            border-radius: 0 16px 16px 0;
        }
        .input-group .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(117,76,255,0.15);
        }
        .btn-login {
            border: none;
            border-radius: 18px;
            padding: 0.85rem;
            background: linear-gradient(90deg, #6b49ff, #ff49a7);
            color: #fff;
            font-weight: 600;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(107,73,255,0.35);
            color: #fff;
        }
        .small-divider {
            display: inline-block;
            position: relative;
            padding: 0 0.75rem;
            color: #8b8c92;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .small-divider::before,
        .small-divider::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 46px;
            height: 1px;
            background: rgba(0,0,0,0.08);
        }
        .small-divider::before { left: -46px; }
        .small-divider::after { right: -46px; }
        .social-btn {
            border-radius: 14px;
            padding: 0.7rem 1rem;
            font-weight: 500;
            color: #4a4b57;
            border: 1px solid rgba(0,0,0,0.08);
            background: #fff;
        }
        .social-btn:hover {
            background: rgba(101,72,255,0.08);
            color: #4a4b57;
        }
        .auth-footer {
            text-align: center;
        }
        .auth-footer .signup-link {
            color: #5b3dff;
            font-weight: 600;
        }
        .auth-footer .signup-link:hover {
            text-decoration: underline;
        }
        .back-link {
            color: #8b8c92;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: color 0.2s ease;
        }
        .back-link:hover {
            color: #5b3dff;
        }
        @media (max-width: 991.98px) {
            .gradient-panel {
                padding: 3rem 2.5rem !important;
                text-align: center;
            }
            .gradient-panel .feature-bullet {
                margin-right: 6px;
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
            <div class="col-lg-6 gradient-panel text-white p-5 d-flex flex-column">
                <div>
                    <div class="icon-circle rounded-4 d-inline-flex align-items-center justify-content-center mb-4">
                        <i class="fas fa-check fs-4"></i>
                    </div>
                    <h2 class="fw-bold mb-2">Task Manager</h2>
                    <h4 class="fw-semibold mb-4">Welcome Back!</h4>
                    <p class="mb-4 opacity-75">
                        Stay on top of your tasks, monitor progress, and keep your productivity high with your personal task hub.
                    </p>
                    <ul class="list-unstyled opacity-75">
                        <li class="mb-3">
                            <span class="feature-bullet pink"></span> Personalized productivity insights
                        </li>
                        <li class="mb-3">
                            <span class="feature-bullet green"></span> Smart task organization
                        </li>
                        <li class="mb-3">
                            <span class="feature-bullet blue"></span> Real-time collaboration alerts
                        </li>
                    </ul>
                </div>
                <div class="pt-4">
                    <small class="opacity-75">© 2025 My Task Manager. All rights reserved.</small>
                </div>
            </div>
            <div class="col-lg-6 bg-white p-5">
                <div class="text-center mb-4">
                    <h3 class="fw-bold mb-1">Sign In</h3>
                    <p class="text-muted mb-0">Welcome back! Please sign in to your account</p>
                </div>
                <form method="post" action="">
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold text-muted">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-envelope"></i>
                            </span>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold text-muted">Password</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="remember-me">
                            <label class="form-check-label small text-muted" for="remember-me">
                                Remember me
                            </label>
                        </div>

                    </div>
                    <button type="submit" name="login" class="btn-login w-100">Sign In</button>
                </form>
                <?php
                if (!empty($errorMsg)) {
                    echo $errorMsg;
                }
                ?>
                <div class="text-center my-4">
                    <span class="small-divider">Or continue with</span>
                </div>
                <div class="d-flex gap-3">
                    <button type="button" class="social-btn w-50">
                        <i class="fab fa-google me-2"></i>Google
                    </button>
                    <button type="button" class="social-btn w-50">
                        <i class="fab fa-facebook-f me-2"></i>Facebook
                    </button>
                </div>
                <div class="auth-footer mt-4">
                    <p class="mb-3 text-muted">
                        Don't have an account?
                        <a href="register.php" class="signup-link text-decoration-none">Sign up here</a>
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
