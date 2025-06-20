<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit;
}
require_once '../config/db_config.php';

$user = $_SESSION['user'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $surname = trim($_POST['surname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);

    // Update user information in the users table
    $stmt = $conn->prepare("UPDATE users SET name=?, surname=?, email=?, phone=? WHERE id=?");
    
    if ($stmt->execute([$name, $surname, $email, $phone, $user['id']])) {
        // Update session data
        $_SESSION['user'] = array_merge($user, [
            'name' => $name, 
            'surname' => $surname, 
            'email' => $email, 
            'phone' => $phone
        ]);
        $msg = "Profile updated successfully.";
    } else {
        $msg = "Update failed.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - Jozi Hustle</title>
    <link rel="stylesheet" href="../assets/css/StyleSheet.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar">
    <div class="nav-container">
        <a href="../index.php" class="logo">
            <i class="fas fa-store"></i> Jozi Hustle
        </a>
        <ul class="nav-links">
            <li><a href="../index.php"><i class="fas fa-home"></i> Home</a></li>
            <li><a href="../pages/categories.php"><i class="fas fa-list"></i> Categories</a></li>
            <li><a href="../pages/chat.php"><i class="fas fa-comments"></i> Chat</a></li>
            <li><a href="../ads/ad.php"><i class="fas fa-plus"></i> Post Ad</a></li>
            <li><a href="../pages/wishlist.php"><i class="fas fa-heart"></i> Wishlist</a></li>
            <li><a href="user_account.php" class="active"><i class="fas fa-user"></i> My Account</a></li>
            <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
        <button class="mobile-menu-toggle">
            <i class="fas fa-bars"></i>
        </button>
    </div>
</nav>

<div class="container main-content">
    <div class="form-container">
        <h2><i class="fas fa-user-circle"></i> My Account</h2>
        
        <?php if(isset($msg)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="name"><i class="fas fa-user"></i> First Name</label>
                <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="surname"><i class="fas fa-user"></i> Surname</label>
                <input type="text" name="surname" id="surname" value="<?php echo htmlspecialchars($user['surname'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email</label>
                <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="phone"><i class="fas fa-phone"></i> Phone Number</label>
                <input type="tel" name="phone" id="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="e.g., +27 12 345 6789">
            </div>

            <button type="submit" class="btn btn-primary btn-full">
                <i class="fas fa-save"></i> Update Profile
            </button>
        </form>

        <!-- Account Information -->
        <div style="margin-top: 2rem; padding: 1.5rem; background: #f8f9fa; border-radius: 10px;">
            <h3 style="margin-bottom: 1rem; color: #333;">
                <i class="fas fa-info-circle"></i> Account Information
            </h3>
            <div style="display: grid; gap: 0.5rem;">
                <p><strong>Account ID:</strong> <?php echo htmlspecialchars($user['id']); ?></p>
                <p><strong>Member Since:</strong> <?php echo isset($user['created_at']) ? date('F j, Y', strtotime($user['created_at'])) : 'Not available'; ?></p>
                <p><strong>Account Status:</strong> <span style="color: #28a745; font-weight: 600;">Active</span></p>
            </div>
        </div>

        <!-- Quick Actions -->
        <div style="margin-top: 2rem; display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <a href="../ads/ad.php" class="btn btn-secondary" style="text-decoration: none; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-plus"></i> Post New Ad
            </a>
            <a href="../pages/wishlist.php" class="btn btn-secondary" style="text-decoration: none; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-heart"></i> View Wishlist
            </a>
        </div>
    </div>
</div>

<script src="../assets/js/script.js"></script>
<script>
// Mobile menu toggle
document.querySelector('.mobile-menu-toggle').addEventListener('click', function() {
    document.querySelector('.nav-links').classList.toggle('active');
});
</script>
</body>
</html>
