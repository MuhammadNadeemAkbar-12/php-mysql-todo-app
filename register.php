<?php
include 'db_connect.php';
session_start(); // add this line to use session for messages

if (isset($_POST['register'])) {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $check_sql = "SELECT id FROM users WHERE email = '$email'";
    $check_result = mysqli_query($conn, $check_sql);

    if (mysqli_num_rows($check_result) > 0) {
        $_SESSION['error'] = "Email already registered!";
    } else {
        $insert_sql = "INSERT INTO users (name, email, password) VALUES ('$fullname', '$email', '$hashedPassword')";
        $result = mysqli_query($conn, $insert_sql);

        if ($result) {
            $_SESSION['success'] = "Registration successful! Please login below.";
            header("Location: login.php");
            exit();
        } else {
            $_SESSION['error'] = "Error while saving data: " . mysqli_error($conn);
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

                <?php
                if (!empty($successMsg)) {
                    echo $successMsg;
                }
                if (!empty($errorMsg)) {
                    echo $errorMsg;
                }
                ?>

                <div class="signup-footer">
                    <p>Already have an account? <a href="login.php">Login In</a></p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>