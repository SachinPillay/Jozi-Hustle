<?php
require_once '../config/db_config.php';
require_once '../includes/auth.php';
requireLogin();

$currentUser = $_SESSION['user'];

// Fetch wishlist items
$stmt = $conn->prepare("
    SELECT a.*, u.name as seller_name, u.surname as seller_surname, w.created_at as added_date
    FROM ads a 
    JOIN wishlist w ON a.id = w.ad_id 
    JOIN users u ON a.seller_id = u.id
    WHERE w.buyer_id = ?
    ORDER BY w.created_at DESC
");
$stmt->execute([$currentUser['id']]);
$wishlist_ads = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle remove from wishlist
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_from_wishlist'])) {
    $ad_id = (int)$_POST['ad_id'];
    try {
        $deleteStmt = $conn->prepare("DELETE FROM wishlist WHERE buyer_id = ? AND ad_id = ?");
        $deleteStmt->execute([$currentUser['id'], $ad_id]);
        $_SESSION['success'] = "Item removed from wishlist successfully";
    } catch (Exception $e) {
        $_SESSION['error'] = "Failed to remove item from wishlist";
    }
    header("Location: wishlist.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist - Jozi Hustle</title>
    <link rel="stylesheet" href="../assets/css/StyleSheet.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="../index.php" class="logo">
                <i class="fas fa-store"></i> Jozi Hustle
            </a>
            <ul class="nav-links">
                <li><a href="../index.php"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="categories.php"><i class="fas fa-list"></i> Categories</a></li>
                <li><a href="chat.php"><i class="fas fa-comments"></i> Chat</a></li>
                <li><a href="../ads/ad.php"><i class="fas fa-plus"></i> Post Ad</a></li>
                <li><a href="wishlist.php" class="active"><i class="fas fa-heart"></i> Wishlist</a></li>
                <li><a href="../user/user_account.php"><i class="fas fa-user"></i> My Account</a></li>
                <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
            <button class="mobile-menu-toggle">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </nav>

    <div class="container">
        <div class="wishlist-content">
            <h1>My Wishlist</h1>
            <p class="wishlist-subtitle">Items you've saved for later</p>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> 
                    <?php 
                        echo $_SESSION['success'];
                        unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> 
                    <?php 
                        echo $_SESSION['error'];
                        unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>
            
            <?php if (empty($wishlist_ads)): ?>
                <div class="empty-wishlist">
                    <div class="empty-icon">🤍</div>
                    <h3>Your wishlist is empty</h3>
                    <p>Start browsing and save items you're interested in!</p>
                    <a href="../index.php" class="btn btn-primary">Browse Marketplace</a>
                </div>
            <?php else: ?>
                <div class="wishlist-stats">
                    <p>You have <strong><?php echo count($wishlist_ads); ?></strong> item<?php echo count($wishlist_ads) !== 1 ? 's' : ''; ?> in your wishlist</p>
                </div>
                
                <div class="wishlist-grid">
                    <?php foreach ($wishlist_ads as $ad): ?>
                        <div class="wishlist-item">
                            <div class="wishlist-card">
                                <div class="product-image-section">
                                    <?php if (!empty($ad['image']) && file_exists('../' . $ad['image'])): ?>
                                        <img src="../<?php echo htmlspecialchars($ad['image']); ?>" 
                                             alt="<?php echo htmlspecialchars($ad['title']); ?>" 
                                             class="product-image">
                                    <?php else: ?>
                                        <div class="no-image">No Image</div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="product-info">
                                    <h3 class="product-title">
                                        <a href="../ads/ad_detail.php?id=<?php echo $ad['id']; ?>">
                                            <?php echo htmlspecialchars($ad['title']); ?>
                                        </a>
                                    </h3>
                                    <div class="product-price">R<?php echo number_format($ad['price'], 2); ?></div>
                                    <p class="product-description"><?php echo htmlspecialchars(substr($ad['description'], 0, 100)); ?><?php echo strlen($ad['description']) > 100 ? '...' : ''; ?></p>
                                    
                                    <?php if (!empty($ad['category'])): ?>
                                        <span class="product-category"><?php echo htmlspecialchars($ad['category']); ?></span>
                                    <?php endif; ?>
                                    
                                    <div class="product-meta">
                                        <small>by <?php echo htmlspecialchars($ad['seller_name'] . ' ' . $ad['seller_surname']); ?></small>
                                        <small>Added: <?php echo date('M j, Y', strtotime($ad['added_date'])); ?></small>
                                    </div>
                                </div>
                                
                                <div class="wishlist-actions">
                                    <a href="../ads/ad_detail.php?id=<?php echo $ad['id']; ?>" class="btn btn-primary btn-sm">View Details</a>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="ad_id" value="<?php echo $ad['id']; ?>">
                                        <input type="hidden" name="remove_from_wishlist" value="1">
                                        <button type="submit" class="btn btn-outline btn-sm" onclick="return confirm('Remove this item from your wishlist?')">
                                            ❌ Remove
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="wishlist-actions-footer">
                    <a href="../index.php" class="btn btn-secondary">Continue Shopping</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Jozi Hustle. Built for  success.</p>
        </div>
    </footer>

    <script src="../assets/js/script.js"></script>
</body>
</html>
