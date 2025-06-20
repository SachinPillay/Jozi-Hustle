<?php
require_once '../config/db_config.php';
require_once '../includes/auth.php';
requireLogin();

if (!isset($_GET['id'])) {
    header("Location: ../index.php");
    exit;
}

$ad_id = $_GET['id'];
$stmt = $conn->prepare("SELECT ads.*, users.name AS seller_name, users.email AS seller_email, users.phone AS seller_phone FROM ads JOIN users ON ads.seller_id = users.id WHERE ads.id = ?");
$stmt->execute([$ad_id]);
$ad = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ad) {
    $_SESSION['error'] = "Ad not found.";
    header("Location: ../index.php");
    exit;
}

// Check if ad is in user's wishlist
$in_wishlist = false;
if (isset($_SESSION['user'])) {
    $wishlist_check = $conn->prepare("SELECT 1 FROM wishlist WHERE buyer_id = ? AND ad_id = ?");
    $wishlist_check->execute([$_SESSION['user']['id'], $ad_id]);
    $in_wishlist = $wishlist_check->fetch() !== false;
}

// Handle wishlist operations
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wishlist_action'])) {
    try {
        if ($_POST['wishlist_action'] === 'add') {
            $stmt = $conn->prepare("INSERT INTO wishlist (buyer_id, ad_id, created_at) VALUES (?, ?, NOW())");
            $stmt->execute([$_SESSION['user']['id'], $ad_id]);
            $_SESSION['success'] = "Added to wishlist successfully";
        } else if ($_POST['wishlist_action'] === 'remove') {
            $stmt = $conn->prepare("DELETE FROM wishlist WHERE buyer_id = ? AND ad_id = ?");
            $stmt->execute([$_SESSION['user']['id'], $ad_id]);
            $_SESSION['success'] = "Removed from wishlist successfully";
        }
        header("Location: ad_detail.php?id=" . $ad_id);
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = "Failed to update wishlist";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($ad['title']); ?> - Jozi Hustle</title>
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
    <div class="product-detail-container">
        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success" style="margin-bottom: 1rem;">
                <i class="fas fa-check-circle"></i> 
                <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-error" style="margin-bottom: 1rem;">
                <i class="fas fa-exclamation-circle"></i> 
                <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <!-- Breadcrumb -->
        <nav class="breadcrumb" style="margin-bottom: 2rem;">
            <a href="../index.php" style="color: #667eea; text-decoration: none;">
                <i class="fas fa-home"></i> Home
            </a>
            <?php if (!empty($ad['category'])): ?>
                <span style="margin: 0 0.5rem; color: #ccc;">></span>
                <a href="../pages/categories.php?category=<?php echo urlencode($ad['category']); ?>" style="color: #667eea; text-decoration: none;">
                    <?php echo htmlspecialchars($ad['category']); ?>
                </a>
            <?php endif; ?>
            <span style="margin: 0 0.5rem; color: #ccc;">></span>
            <span style="color: #666;"><?php echo htmlspecialchars($ad['title']); ?></span>
        </nav>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; margin-bottom: 3rem;">
            <!-- Product Image -->
            <div class="product-image-container">
                <?php if (!empty($ad['image'])): ?>
                    <img src="../<?php echo htmlspecialchars($ad['image']); ?>" 
                         alt="<?php echo htmlspecialchars($ad['title']); ?>" 
                         style="width: 100%; height: 400px; object-fit: cover; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                <?php else: ?>
                    <div style="width: 100%; height: 400px; background: linear-gradient(45deg, #f8f9fa, #e9ecef); border-radius: 15px; display: flex; align-items: center; justify-content: center; flex-direction: column;">
                        <i class="fas fa-image" style="font-size: 4rem; color: #6c757d; margin-bottom: 1rem;"></i>
                        <span style="color: #6c757d; font-size: 1.2rem;">No Image Available</span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Product Details -->
            <div class="product-details">
                <h1 style="color: #333; margin-bottom: 1rem; font-size: 2.5rem; font-weight: 700;">
                    <?php echo htmlspecialchars($ad['title']); ?>
                </h1>

                <div style="margin-bottom: 2rem;">
                    <span style="font-size: 2.5rem; font-weight: 700; color: #667eea;">
                        R<?php echo number_format($ad['price'], 2); ?>
                    </span>
                </div>

                <?php if ($ad['category']): ?>
                    <div style="margin-bottom: 2rem;">
                        <span style="background: #E3F2FD; color: #1976D2; padding: 0.5rem 1rem; border-radius: 20px; font-size: 0.9rem; font-weight: 500;">
                            <i class="fas fa-tag"></i> <?php echo htmlspecialchars($ad['category']); ?>
                        </span>
                    </div>
                <?php endif; ?>

                <div style="background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin-bottom: 2rem;">
                    <h3 style="color: #333; margin-bottom: 1rem; font-size: 1.3rem;">
                        <i class="fas fa-info-circle"></i> Description
                    </h3>
                    <p style="color: #666; line-height: 1.8; font-size: 1.1rem;">
                        <?php echo nl2br(htmlspecialchars($ad['description'])); ?>
                    </p>
                </div>

                <div style="display: flex; gap: 1rem; margin-bottom: 2rem;">
                    <?php if ($ad['seller_id'] != $_SESSION['user']['id']): ?>
                        <form method="POST" style="flex: 1;">
                            <input type="hidden" name="wishlist_action" value="<?php echo $in_wishlist ? 'remove' : 'add'; ?>">
                            <button type="submit" class="btn <?php echo $in_wishlist ? 'btn-secondary' : 'btn-primary'; ?> btn-full">
                                <i class="fas fa-heart"></i> 
                                <?php echo $in_wishlist ? 'Remove from Wishlist' : 'Add to Wishlist'; ?>
                            </button>
                        </form>
                        <a href="../pages/chat.php?seller_id=<?php echo htmlspecialchars($ad['seller_id']); ?>" 
                           class="btn btn-secondary" style="flex: 1; text-decoration: none; text-align: center;">
                            <i class="fas fa-comments"></i> Chat with Seller
                        </a>
                    <?php else: ?>
                        <a href="ad_edit.php?id=<?php echo $ad['id']; ?>" 
                           class="btn btn-primary" style="flex: 1; text-decoration: none; text-align: center;">
                            <i class="fas fa-edit"></i> Edit Ad
                        </a>
                        <a href="delete_ad.php?id=<?php echo $ad['id']; ?>" 
                           class="btn btn-secondary" style="flex: 1; text-decoration: none; text-align: center; background: #dc3545;"
                           onclick="return confirm('Are you sure you want to delete this ad? This action cannot be undone.')">
                            <i class="fas fa-trash"></i> Delete Ad
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Seller Information -->
        <div style="background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
            <h3 style="color: #333; margin-bottom: 1.5rem; font-size: 1.5rem;">
                <i class="fas fa-user"></i> Seller Information
            </h3>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                <div>
                    <label style="font-weight: 600; color: #555; display: block; margin-bottom: 0.5rem;">
                        <i class="fas fa-user-circle"></i> Name
                    </label>
                    <p style="color: #333; font-size: 1.1rem;">
                        <?php echo htmlspecialchars($ad['seller_name']); ?>
                    </p>
                </div>
                
                <div>
                    <label style="font-weight: 600; color: #555; display: block; margin-bottom: 0.5rem;">
                        <i class="fas fa-envelope"></i> Email
                    </label>
                    <p style="color: #333; font-size: 1.1rem;">
                        <a href="mailto:<?php echo htmlspecialchars($ad['seller_email']); ?>" 
                           style="color: #667eea; text-decoration: none;">
                            <?php echo htmlspecialchars($ad['seller_email']); ?>
                        </a>
                    </p>
                </div>
                
                <?php if ($ad['seller_phone']): ?>
                <div>
                    <label style="font-weight: 600; color: #555; display: block; margin-bottom: 0.5rem;">
                        <i class="fas fa-phone"></i> Phone
                    </label>
                    <p style="color: #333; font-size: 1.1rem;">
                        <a href="tel:<?php echo htmlspecialchars($ad['seller_phone']); ?>" 
                           style="color: #667eea; text-decoration: none;">
                            <?php echo htmlspecialchars($ad['seller_phone']); ?>
                        </a>
                    </p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Responsive adjustments -->
<style>
@media (max-width: 768px) {
    .container .product-detail-container > div:first-of-type {
        grid-template-columns: 1fr !important;
        gap: 2rem !important;
    }
    
    .container .product-detail-container h1 {
        font-size: 2rem !important;
    }
    
    .container .product-detail-container .product-details > div:nth-child(2) span {
        font-size: 2rem !important;
    }
}
</style>

<script src="../assets/js/script.js"></script>
</body>
</html>
