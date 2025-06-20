<?php
session_start();
require_once '../config/db_config.php';

if (isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check user in database
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Both email and password are required.";
        header("Location: login.php");
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user'] = $user;
        $_SESSION['role'] = (strtolower($user['email']) === 'admin@jozihustle.com') ? 'admin' : 'user';
        
        // Check if there's a redirect URL
        if (isset($_SESSION['redirect_after_login'])) {
            $redirect = $_SESSION['redirect_after_login'];
            unset($_SESSION['redirect_after_login']);
            header("Location: $redirect");
        } else {
            header("Location: " . (strtolower($user['email']) === 'admin@jozihustle.com' ? "../admin/index.php" : "../index.php"));
        }
        exit;
    } else {
        $_SESSION['error'] = "Invalid email or password.";
        header("Location: login.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Jozi Hustle</title>
    <link rel="stylesheet" href="../assets/css/StyleSheet.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>

<div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 2rem;">
    <div class="form-container fade-in" style="width: 100%; max-width: 400px; margin: 0;">
        <div style="text-align: center; margin-bottom: 2rem;">
            <?php if(isset($_SESSION['success'])): ?>
                <div class="alert alert-success" style="background: #28a745; color: white; padding: 1rem; border-radius: 10px; margin-bottom: 1rem;">
                    <i class="fas fa-check-circle"></i> 
                    <?php 
                        echo $_SESSION['success'];
                        unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>
            <i class="fas fa-store" style="font-size: 3rem; color: #667eea; margin-bottom: 1rem;"></i>
            <h2 style="color: #333; margin: 0;">Welcome Back</h2>
            <p style="color: #666; margin: 0.5rem 0 0 0;">Sign in to your Jozi Hustle account</p>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error" style="background: #dc3545; color: white; padding: 1rem; border-radius: 10px; margin-bottom: 1rem;">
                <i class="fas fa-exclamation-circle"></i> 
                <?php 
                    echo htmlspecialchars($_SESSION['error']);
                    unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <form method="post" class="login-form">
            <div class="form-group">
                <label for="email">
                    <i class="fas fa-envelope"></i> Email Address
                </label>
                <input type="email" name="email" id="email" required 
                       placeholder="Enter your email" 
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="password">
                    <i class="fas fa-lock"></i> Password
                </label>
                <input type="password" name="password" id="password" required 
                       placeholder="Enter your password">
            </div>

            <button type="submit" class="btn btn-primary btn-full">
                <i class="fas fa-sign-in-alt"></i> Sign In
            </button>
        </form>

        <div style="text-align: center; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #e1e5e9;">
            <p style="color: #666; margin-bottom: 1rem;">Don't have an account?</p>
            <a href="register.php" class="btn btn-secondary" style="text-decoration: none;">
                <i class="fas fa-user-plus"></i> Create Account
            </a>
        </div>

        <div style="text-align: center; margin-top: 1.5rem;">
            <a href="../index.php" style="color: #667eea; text-decoration: none; font-size: 0.9rem;">
                <i class="fas fa-arrow-left"></i> Back to Home
            </a>
        </div>
    </div>
</div>

<script src="../assets/js/script.js"></script>
</body>
</html>
