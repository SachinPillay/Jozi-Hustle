<?php
session_start();
require_once '../config/db_config.php';
$category = isset($_GET['category']) ? $_GET['category'] : '';

$stmt = $conn->prepare("SELECT * FROM ads WHERE category = ? ORDER BY id DESC");
$stmt->execute([$category]);
$ads = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category: <?php echo htmlspecialchars($category); ?> - Jozi Hustle</title>
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
    <!-- Breadcrumb -->
    <nav style="margin-bottom: 2rem;">
        <a href="../index.php" style="color: #667eea; text-decoration: none;">
            <i class="fas fa-home"></i> Home
        </a>
        <span style="margin: 0 0.5rem; color: #ccc;">></span>
        <a href="../pages/categories.php" style="color: #667eea; text-decoration: none;">
            <i class="fas fa-list"></i> Categories
        </a>
        <span style="margin: 0 0.5rem; color: #ccc;">></span>
        <span style="color: #666;"><?php echo htmlspecialchars($category); ?></span>
    </nav>

    <!-- Category Header -->
    <div style="text-align: center; margin-bottom: 3rem; padding: 2rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 15px;">
        <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem;">
            <?php 
            $categoryIcons = [
                'Food' => '🍕',
                'Electronics' => '📱',
                'Clothing' => '👕',
                'Beverages' => '🥤',
                'Household Contents' => '🏠',
                'Other' => '📦'
            ];
            echo $categoryIcons[$category] ?? '📦';
            ?> 
            <?php echo htmlspecialchars($category); ?>
        </h1>
        <p style="font-size: 1.2rem; opacity: 0.9;">
            <?php echo count($ads); ?> items available
        </p>
    </div>

    <?php if (empty($ads)): ?>
        <div style="text-align: center; padding: 4rem 0; background: white; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
            <i class="fas fa-box-open" style="font-size: 4rem; color: #ccc; margin-bottom: 1rem;"></i>
            <h3 style="color: #666; margin-bottom: 1rem;">No ads found in this category</h3>
            <p style="color: #999; margin-bottom: 2rem;">Be the first to post an ad in <?php echo htmlspecialchars($category); ?>!</p>
            <a href="ad.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Post First Ad
            </a>
        </div>
    <?php else: ?>
        <!-- Products Grid -->
        <div class="products-grid">
            <?php foreach ($ads as $ad): ?>
                <div class="product-card fade-in" onclick="window.location.href='ad_detail.php?id=<?php echo $ad['id']; ?>'">
                    <div class="product-image">
                        <?php if (!empty($ad['image'])): ?>
                            <img src="../<?php echo htmlspecialchars($ad['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($ad['title']); ?>" 
                                 style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <i class="fas fa-image" style="font-size: 3rem; color: #ccc;"></i>
                            <p style="margin-top: 0.5rem; color: #999;">No Image</p>
                        <?php endif; ?>
                    </div>

                    <div class="product-info">
                        <h3 class="product-title"><?php echo htmlspecialchars($ad['title']); ?></h3>
                        <div class="product-price">R<?php echo number_format($ad['price'], 2); ?></div>
                        
                        <?php if (!empty($ad['short_description'])): ?>
                            <p class="product-description"><?php echo htmlspecialchars($ad['short_description']); ?></p>
                        <?php endif; ?>

                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
                            <?php if (!empty($ad['rating']) && $ad['rating'] > 0): ?>
                                <div style="color: #ffc107;">
                                    <?php 
                                    for ($i = 1; $i <= 5; $i++) {
                                        echo $i <= $ad['rating'] ? '⭐' : '☆';
                                    }
                                    ?>
                                    <span style="color: #666; margin-left: 0.5rem;">(<?php echo $ad['rating']; ?>)</span>
                                </div>
                            <?php else: ?>
                                <span style="color: #999; font-size: 0.9rem;">No rating</span>
                            <?php endif; ?>
                            
                            <span class="product-category"><?php echo htmlspecialchars($ad['category']); ?></span>
                        </div>

                        <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #eee;">
                            <small style="color: #999;">
                                <i class="fas fa-calendar"></i> 
                                <?php echo date('M j, Y', strtotime($ad['created_at'] ?? 'now')); ?>
                            </small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Back to Categories -->
    <div style="text-align: center; margin-top: 3rem;">
        <a href="../pages/categories.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to All Categories
        </a>
    </div>
</div>

<script src="../assets/js/script.js"></script>
<script>
// Mobile menu toggle
document.querySelector('.mobile-menu-toggle').addEventListener('click', function() {
    document.querySelector('.nav-links').classList.toggle('active');
});

// Add fade-in animation to product cards
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.product-card');
    cards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
    });
});
</script>
</body>
</html>
