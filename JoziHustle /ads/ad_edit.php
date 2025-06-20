<?php
session_start();
require_once '../config/db_config.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: ../index.php");
    exit;
}

$ad_id = $_GET['id'];

$stmt = $conn->prepare("
    SELECT ads.*, users.name AS seller_name, users.email AS seller_email, users.phone AS seller_phone 
    FROM ads 
    JOIN users ON ads.seller_id = users.id 
    WHERE ads.id = ?
");
$stmt->execute([$ad_id]);
$ad = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ad) {
    header("Location: ../index.php?error=Ad not found");
    exit;
}

// Verify ownership
if ($ad['seller_id'] != $_SESSION['user']['id']) {
    header("Location: ../index.php?error=Unauthorized access");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Ad - Jozi Hustle</title>
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
            <li><a href="ad.php"><i class="fas fa-plus"></i> Post Ad</a></li>
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
        <h2><i class="fas fa-edit"></i> Edit Ad: <?php echo htmlspecialchars($ad['title']); ?></h2>

        <!-- Image Management -->
        <div style="text-align: center; margin-bottom: 2rem;">
            <?php if (!empty($ad['image'])): ?>
                <!-- Current Image Display -->
                <img src="../<?php echo htmlspecialchars($ad['image']); ?>" 
                     alt="Current Ad Image" 
                     style="max-width: 300px; max-height: 200px; object-fit: cover; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                <p style="color: #666; margin-top: 0.5rem; font-size: 0.9rem;">
                    <i class="fas fa-image"></i> Current Image
                </p>
            <?php else: ?>
                <!-- No Image Placeholder -->
                <div style="width: 300px; height: 200px; background: #f8f9fa; display: flex; align-items: center; justify-content: center; margin: 0 auto; border-radius: 10px;">
                    <span style="color: #6c757d;">No Image</span>
                </div>
            <?php endif; ?>

            <!-- Image Controls -->
            <div style="display: flex; gap: 1rem; justify-content: center; margin-top: 1rem;">
                <label class="btn btn-secondary" style="cursor: pointer;">
                    <i class="fas fa-upload"></i> <?php echo !empty($ad['image']) ? 'Change Image' : 'Add Image'; ?>
                    <input type="file" name="image" accept="image/*" style="display: none;" onchange="showFileName(this)">
                </label>
                <?php if (!empty($ad['image'])): ?>
                    <button type="button" class="btn btn-secondary" style="background: #dc3545;" onclick="deleteImage()">
                        <i class="fas fa-trash"></i> Delete Image
                    </button>
                <?php endif; ?>
            </div>
            <p id="selectedFileName" style="color: #666; margin-top: 0.5rem; font-size: 0.9rem; display: none;"></p>
            <input type="hidden" name="delete_image" id="delete_image" value="0">
        </div>

        <form method="POST" action="updated_ad.php" enctype="multipart/form-data">
            <input type="hidden" name="ad_id" value="<?php echo htmlspecialchars($ad_id); ?>">

            <div class="form-group">
                <label for="title"><i class="fas fa-heading"></i> Title</label>
                <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($ad['title']); ?>" required>
            </div>

            <div class="form-group">
                <label for="price"><i class="fas fa-tag"></i> Price (R)</label>
                <input type="number" name="price" id="price" value="<?php echo htmlspecialchars($ad['price']); ?>" step="0.01" min="0" required>
            </div>

            <div class="form-group">
                <label for="category"><i class="fas fa-layer-group"></i> Category</label>
                <select name="category" id="category" required>
                    <option value="">Select a category</option>
                    <option value="Food" <?php echo ($ad['category'] == 'Food') ? 'selected' : ''; ?>>🍕 Food</option>
                    <option value="Electronics" <?php echo ($ad['category'] == 'Electronics') ? 'selected' : ''; ?>>📱 Electronics</option>
                    <option value="Clothing" <?php echo ($ad['category'] == 'Clothing') ? 'selected' : ''; ?>>👕 Clothing</option>
                    <option value="Beverages" <?php echo ($ad['category'] == 'Beverages') ? 'selected' : ''; ?>>🥤 Beverages</option>
                    <option value="Household Contents" <?php echo ($ad['category'] == 'Household Contents') ? 'selected' : ''; ?>>🏠 Household Contents</option>
                    <option value="Other" <?php echo ($ad['category'] == 'Other') ? 'selected' : ''; ?>>📦 Other</option>
                </select>
            </div>

            <div class="form-group">
                <label for="description"><i class="fas fa-file-alt"></i> Description</label>
                <textarea name="description" id="description" rows="5" required><?php echo htmlspecialchars($ad['description']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="rating"><i class="fas fa-star"></i> Rating</label>
                <select name="rating" id="rating">
                    <option value="">No rating</option>
                    <option value="1" <?php echo ($ad['rating'] == 1) ? 'selected' : ''; ?>>⭐ 1 Star</option>
                    <option value="2" <?php echo ($ad['rating'] == 2) ? 'selected' : ''; ?>>⭐⭐ 2 Stars</option>
                    <option value="3" <?php echo ($ad['rating'] == 3) ? 'selected' : ''; ?>>⭐⭐⭐ 3 Stars</option>
                    <option value="4" <?php echo ($ad['rating'] == 4) ? 'selected' : ''; ?>>⭐⭐⭐⭐ 4 Stars</option>
                    <option value="5" <?php echo ($ad['rating'] == 5) ? 'selected' : ''; ?>>⭐⭐⭐⭐⭐ 5 Stars</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary btn-full">
                <i class="fas fa-save"></i> Update Ad
            </button>
        </form>

        <!-- Action Buttons -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 2rem;">
            <form method="POST" action="../handlers/update_status.php" style="margin: 0;">
                <input type="hidden" name="ad_id" value="<?php echo htmlspecialchars($ad['id']); ?>">
                <button type="submit" name="status" value="sold" class="btn btn-secondary btn-full">
                    <i class="fas fa-check-circle"></i> Mark as Sold
                </button>
            </form>

            <a href="delete_ad.php?id=<?php echo htmlspecialchars($ad['id']); ?>" 
               class="btn btn-secondary btn-full" 
               style="background: #dc3545; text-decoration: none; display: flex; align-items: center; justify-content: center;"
               onclick="return confirm('Are you sure you want to delete this ad? This action cannot be undone.');">
                <i class="fas fa-trash"></i> Delete Ad
            </a>
        </div>

        <!-- Back to Ad Details -->
        <div style="text-align: center; margin-top: 2rem;">
            <a href="ad_detail.php?id=<?php echo htmlspecialchars($ad['id']); ?>" 
               style="color: #667eea; text-decoration: none; font-weight: 500;">
                <i class="fas fa-arrow-left"></i> Back to Ad Details
            </a>
        </div>
    </div>
</div>

<script src="../assets/js/script.js"></script>
<script>
// Show selected filename
function showFileName(input) {
    const fileNameDisplay = document.getElementById('selectedFileName');
    if (input.files && input.files[0]) {
        fileNameDisplay.textContent = 'Selected: ' + input.files[0].name;
        fileNameDisplay.style.display = 'block';
        // Reset delete flag if new image is selected
        document.getElementById('delete_image').value = '0';
    }
}

// Handle image deletion
function deleteImage() {
    if (confirm('Are you sure you want to delete the current image?')) {
        document.getElementById('delete_image').value = '1';
        const currentImage = document.querySelector('img[alt="Current Ad Image"]');
        const noImageDiv = document.createElement('div');
        noImageDiv.className = 'no-image';
        noImageDiv.style = 'width: 300px; height: 200px; background: #f8f9fa; display: flex; align-items: center; justify-content: center; margin: 0 auto; border-radius: 10px;';
        noImageDiv.innerHTML = '<span style="color: #6c757d;">No Image</span>';
        currentImage.parentNode.replaceChild(noImageDiv, currentImage);
    }
}

// Mobile menu toggle
document.querySelector('.mobile-menu-toggle').addEventListener('click', function() {
    document.querySelector('.nav-links').classList.toggle('active');
});
</script>
</body>
</html>
