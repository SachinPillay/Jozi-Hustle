<?php
require_once '../config/db_config.php';
require_once '../includes/auth.php';
requireLogin();

// Display any messages passed via URL parameters
if(isset($_GET['message'])) {
    $message = $_GET['message'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Ad - Jozi Hustle</title>
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
            <li><a href="ad.php" class="active"><i class="fas fa-plus"></i> Post Ad</a></li>
            <li><a href="../pages/wishlist.php"><i class="fas fa-heart"></i> Wishlist</a></li>
            <li><a href="../user/user_account.php"><i class="fas fa-user"></i> My Account</a></li>
            <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
        <button class="mobile-menu-toggle">
            <i class="fas fa-bars"></i>
        </button>
    </div>
</nav>

<div class="container main-content">
    <div class="form-container">
        <h2><i class="fas fa-plus-circle"></i> Post a New Ad</h2>
        
        <?php if(isset($message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> 
                <?php 
                    echo htmlspecialchars($_SESSION['error']);
                    unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="upload_ad.php" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title"><i class="fas fa-heading"></i> Title</label>
                <input type="text" name="title" id="title" placeholder="Enter a catchy title for your ad" required>
            </div>

            <div class="form-group">
                <label for="price"><i class="fas fa-tag"></i> Price (R)</label>
                <input type="number" name="price" id="price" placeholder="0.00" step="0.01" min="0" required>
            </div>

            <div class="form-group">
                <label for="category"><i class="fas fa-layer-group"></i> Category</label>
                <select name="category" id="category" required>
                    <option value="">Select a category</option>
                    <option value="Food">🍕 Food</option>
                    <option value="Electronics">📱 Electronics</option>
                    <option value="Clothing">👕 Clothing</option>
                    <option value="Beverages">🥤 Beverages</option>
                    <option value="Household Contents">🏠 Household Contents</option>
                    <option value="Other">📦 Other</option>
                </select>
            </div>

            <div class="form-group">
                <label for="short_description"><i class="fas fa-align-left"></i> Short Description</label>
                <textarea name="short_description" id="short_description" rows="3" 
                         placeholder="Brief description (will appear in listings)" required></textarea>
            </div>

            <div class="form-group">
                <label for="description"><i class="fas fa-file-alt"></i> Detailed Description</label>
                <textarea name="description" id="description" rows="5" 
                         placeholder="Provide detailed information about your item" required></textarea>
            </div>

            <div class="form-group">
                <label for="image"><i class="fas fa-camera"></i> Upload Image</label>
                <input type="file" name="image" id="image" accept="image/*" required>
                <small style="color: #666; font-size: 0.9rem;">
                    <i class="fas fa-info-circle"></i> Supported formats: JPG, PNG, GIF. Max size: 5MB
                </small>
            </div>

            <button type="submit" class="btn btn-primary btn-full">
                <i class="fas fa-upload"></i> Post Ad
            </button>
        </form>
    </div>
</div>

<script src="../assets/js/script.js"></script>
<script>
// Mobile menu toggle
document.querySelector('.mobile-menu-toggle').addEventListener('click', function() {
    document.querySelector('.nav-links').classList.toggle('active');
});

// Form validation and preview
document.getElementById('image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const fileSize = file.size / 1024 / 1024; // Convert to MB
        if (fileSize > 5) {
            alert('File size should not exceed 5MB');
            this.value = '';
        }
    }
});
</script>
</body>
</html>
