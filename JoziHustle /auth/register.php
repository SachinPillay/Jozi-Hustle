<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once '../config/db_config.php';
require_once '../includes/auth.php';

if (isLoggedIn()) {
    header("Location: ../index.php");
    exit;
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name    = $_POST['name'];
    $surname = $_POST['surname'];
    $email   = $_POST['email'];
    $phone   = $_POST['phone'];
    $gender  = $_POST['gender'];
    $dob     = $_POST['dob'];
    $password= password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Debugging: Ensure required fields are captured
    if (empty($name) || empty($email) || empty($password)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: register.php");
        exit;
    }

    try {
        // Check if email already exists
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            $_SESSION['error'] = "Email already exists. Please use a different email address.";
            header("Location: register.php");
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO users (name, surname, email, phone, gender, dob, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$name, $surname, $email, $phone, $gender, $dob, $password])) {
            $_SESSION['success'] = "Registration successful! Please login to continue.";
            header("Location: login.php");
            exit;
        } else {
            $_SESSION['error'] = "Registration failed. Please try again.";
            header("Location: register.php");
            exit;
        }
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            $_SESSION['error'] = "Email already exists. Please use a different email.";
        } else {
            $_SESSION['error'] = "Registration failed. Please try again later.";
        }
        header("Location: register.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Jozi Hustle</title>
    <link rel="stylesheet" href="../assets/css/StyleSheet.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>

<div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 2rem;">
    <div class="form-container fade-in" style="width: 100%; max-width: 500px; margin: 0; background: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); padding: 2rem;">
        <div style="text-align: center; margin-bottom: 2rem;">
            <i class="fas fa-user-plus" style="font-size: 3rem; color: #667eea; margin-bottom: 1rem;"></i>
            <h2 style="color: #333; margin: 0;">Create Account</h2>
            <p style="color: #666; margin: 0.5rem 0 0 0;">Join Jozi Hustle marketplace</p>
        </div>
        
        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <form method="post" action="register.php" class="login-form" style="margin-bottom: 2rem;">
            <div class="form-group">
                <label for="name">
                    <i class="fas fa-user"></i> Name
                </label>
                <input type="text" name="name" id="name" required placeholder="Enter your name">
            </div>

            <div class="form-group">
                <label for="surname">
                    <i class="fas fa-user"></i> Surname
                </label>
                <input type="text" name="surname" id="surname" required placeholder="Enter your surname">
            </div>

            <div class="form-group">
                <label for="email">
                    <i class="fas fa-envelope"></i> E-mail
                </label>
                <input type="email" name="email" id="email" required placeholder="Enter your email">
            </div>

            <div class="form-group">
                <label for="phone">
                    <i class="fas fa-phone"></i> Cell Number
                </label>
                <input type="text" name="phone" id="phone" required placeholder="Enter your phone number">
            </div>

            <div class="form-group">
                <label for="password">
                    <i class="fas fa-lock"></i> Password
                </label>
                <input type="password" name="password" id="password" required placeholder="Enter your password">
            </div>

            <div class="form-group">
                <label for="gender">
                    <i class="fas fa-venus-mars"></i> Gender
                </label>
                <select name="gender" id="gender" class="form-control">
                    <option value="">Select gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <div class="form-group">
                <label for="dob">
                    <i class="fas fa-calendar"></i> Date of Birth
                </label>
                <input type="date" name="dob" id="dob" required>
            </div>

            <button type="submit" class="btn btn-primary btn-full">
                <i class="fas fa-user-plus"></i> Create Account
            </button>
        </form>

        <div style="text-align: center; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #e1e5e9;">
            <p style="color: #666; margin-bottom: 1rem;">Already have an account?</p>
            <a href="login.php" class="btn btn-secondary" style="text-decoration: none;">
                <i class="fas fa-sign-in-alt"></i> Sign In
            </a>
        </div>

        <div style="text-align: center; margin-top: 1.5rem;">
            <a href="../index.php" style="color: #667eea; text-decoration: none; font-size: 0.9rem;">
                <i class="fas fa-arrow-left"></i> Back to Home
            </a>
        </div>
    </div>
</div>

</body>
</html>
