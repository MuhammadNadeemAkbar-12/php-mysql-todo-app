<?php
include 'db_connect.php';
if (isset($_POST['register'])) {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $check_sql = "SELECT id FROM users WHERE email = '$email'";
    $check_result = mysqli_query($conn, $check_sql);

    if (mysqli_num_rows($check_result) > 0) {
        echo "<p style='color:red;'>Email already registered!</p>";
    } else {
        $insert_sql = "INSERT INTO users (name, email, password) VALUES ('$fullname', '$email', '$hashedPassword')";
        $result = mysqli_query($conn, $insert_sql);

        if ($result) {
            echo "<p style='color:green;'>Registration successful! <a href='login.php'>Login now</a></p>";
        } else {
            echo "<p style='color:red;'>Error: " . mysqli_error($conn) . "</p>";
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
        }

        .signup-container {
            width: 100%;
            max-width: 750px;
            padding: 20px;
            animation: slideIn 0.6s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px 20px 0 0 !important;
            border: none;
            padding: 40px 30px;
            text-align: center;
            animation: slideInDown 0.7s ease-out;
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card-header h1 {
            color: white;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
            letter-spacing: -0.5px;
        }

        .card-header p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 14px;
            margin: 0;
        }

        .card-body {
            padding: 40px 30px;
        }

        .form-group {
            margin-bottom: 20px;
            animation: slideInUp 0.6s ease-out both;
        }

        .form-group:nth-child(1) { animation-delay: 0.1s; }
        .form-group:nth-child(2) { animation-delay: 0.2s; }
        .form-group:nth-child(3) { animation-delay: 0.3s; }
        .form-group:nth-child(4) { animation-delay: 0.4s; }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: #f9f9f9;
        }

        .form-control:focus {
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
            transform: translateY(-2px);
        }

        .form-control::placeholder {
            color: #999;
        }

        .form-check {
            margin-bottom: 25px;
            animation: slideInUp 0.6s ease-out 0.5s both;
        }

        .form-check-input {
            width: 18px;
            height: 18px;
            border: 2px solid #e0e0e0;
            border-radius: 4px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .form-check-input:checked {
            background-color: #667eea;
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .form-check-input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
        }

        .form-check-label {
            margin-left: 8px;
            font-size: 14px;
            color: #666;
            cursor: pointer;
            user-select: none;
        }

        .btn-signup {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            color: white;
            font-weight: 600;
            font-size: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            cursor: pointer;
            animation: slideInUp 0.6s ease-out 0.6s both;
            position: relative;
            overflow: hidden;
        }

        .btn-signup::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            transition: left 0.3s ease;
        }

        .btn-signup:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .btn-signup:hover::before {
            left: 100%;
        }

        .btn-signup:active {
            transform: translateY(-1px);
        }

        .signup-footer {
            text-align: center;
            margin-top: 25px;
            padding-top: 25px;
            border-top: 1px solid #e0e0e0;
            animation: slideInUp 0.6s ease-out 0.7s both;
        }

        .signup-footer p {
            font-size: 14px;
            color: #666;
            margin: 0;
        }

        .signup-footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .signup-footer a:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        @media (max-width: 576px) {
            .signup-container {
                max-width: 100%;
            }

            .card-header {
                padding: 30px 20px;
            }

            .card-header h1 {
                font-size: 28px;
            }

            .card-body {
                padding: 30px 20px;
            }

            .card-header p {
                font-size: 13px;
            }
        }

        @media (max-width: 380px) {
            .card-header h1 {
                font-size: 24px;
            }

            .form-label {
                font-size: 13px;
            }

            .form-control {
                font-size: 13px;
            }
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <div class="card">
            <div class="card-header">
                <h1>Create Account</h1>
                <p>Join us today and get started</p>
            </div>
            <div class="card-body">
                <form method="post" action="">
    <div class="form-group">
        <label for="fullname" class="form-label">Full Name</label>
        <input type="text" class="form-control" id="fullname" name="fullname" placeholder="Enter your full name" required>
    </div>

    <div class="form-group">
        <label for="email" class="form-label">Email Address</label>
        <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
    </div>

    <div class="form-group">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password" placeholder="Create a strong password" required>
    </div>

    <div class="form-check">
        <input class="form-check-input" type="checkbox" id="terms" required>
        <label class="form-check-label" for="terms">
            I agree to the Terms & Conditions
        </label>
    </div>

    <button type="submit" name="register" class="btn-signup">Sign Up</button>
</form>

                <div class="signup-footer">
                    <p>Already have an account? <a href="#login">Sign In</a></p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>