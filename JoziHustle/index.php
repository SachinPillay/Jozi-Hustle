<?php
session_start();
require_once 'config/db_config.php';

// If user is not logged in, show welcome page
if (!isset($_SESSION['user'])) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Jozi Hustle</title>
    <link rel="stylesheet" href="assets/css/StyleSheet.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>

<div class="hero">
    <div class="container">
        <img src="assets/images/Jozi.png" alt="Jozi Hustle Logo" style="max-width: 150px; margin-bottom: 20px; border-radius: 50%; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
        <h1 class="fade-in">Welcome to Jozi Hustle</h1>
        <p class="fade-in">Your premier  marketplace - Buy, Sell, Connect</p>
        
        <div style="display: flex; gap: 1rem; justify-content: center; margin-top: 2rem;">
            <a href="auth/login.php" class="btn btn-primary">
                <i class="fas fa-sign-in-alt"></i> Login
            </a>
            <a href="auth/register.php" class="btn btn-secondary">
                <i class="fas fa-user-plus"></i> Register
            </a>
        </div>
    </div>
</div>

<div class="container">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; margin: 3rem 0;">
        <div class="feature-card fade-in">
            <div class="feature-icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <h3>Easy Buying</h3>
            <p>Browse through thousands of local products at affordable prices</p>
        </div>
        
        <div class="feature-card fade-in">
            <div class="feature-icon">
                <i class="fas fa-tags"></i>
            </div>
            <h3>Quick Selling</h3>
            <p>Post your items quickly and reach fellow traders in your area</p>
        </div>
        
        <div class="feature-card fade-in">
            <div class="feature-icon">
                <i class="fas fa-comments"></i>
            </div>
            <h3>Safe Communication</h3>
            <p>Built-in chat system to communicate safely with buyers and sellers</p>
        </div>
    </div>
</div>

</body>
</html>
<?php
    exit;
}

// If user is logged in, show marketplace
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';

// Build query with filters
$query = "SELECT ads.*, users.name, users.surname FROM ads 
          JOIN users ON ads.seller_id = users.id 
          WHERE ads.blocked = 0";
$params = [];

if ($search) {
    $query .= " AND (ads.title LIKE ? OR ads.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category) {
    $query .= " AND ads.category = ?";
    $params[] = $category;
}

$query .= " ORDER BY ads.id DESC";

try {
    $stmt = $conn->prepare($query);
    if (!$stmt->execute($params)) {
        error_log("Query execution failed: " . implode(" ", $stmt->errorInfo()));
    }
    $ads = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $ads = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jozi Hustle - Marketplace</title>
    <link rel="stylesheet" href="assets/css/StyleSheet.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar">
    <div class="nav-container">
        <a href="index.php" class="logo">
            <i class="fas fa-store"></i> Jozi Hustle
        </a>
        <ul class="nav-links">
            <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
            <li><a href="pages/categories.php"><i class="fas fa-list"></i> Categories</a></li>
            <li><a href="pages/chat.php"><i class="fas fa-comments"></i> Chat</a></li>
            <li><a href="ads/ad.php"><i class="fas fa-plus"></i> Post Ad</a></li>
            <li><a href="pages/wishlist.php"><i class="fas fa-heart"></i> Wishlist</a></li>
            <li><a href="user/user_account.php"><i class="fas fa-user"></i> My Account</a></li>
            <li><a href="auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
</nav>

<div class="container main-content">
    <div class="text-center mb-4">
        <h1 style="color: #667eea; margin-bottom: 0.5rem;">Marketplace</h1>
        <p style="color: #666;">Discover amazing deals from informal traders around you </p>
    </div>
    
    <!-- Search and Filter Section -->
    <div class="search-filters">
        <form action="index.php" method="GET" class="search-row">
            <input type="text" name="search" placeholder="🔍 Search products..." 
                   value="<?php echo htmlspecialchars($search); ?>" 
                   class="search-input">
            
            <select name="category" class="filter-select" onchange="this.form.submit()">
                <option value="">All Categories</option>
                <option value="Food" <?php echo $category === 'Food' ? 'selected' : ''; ?>>🍕 Food</option>
                <option value="Electronics" <?php echo $category === 'Electronics' ? 'selected' : ''; ?>>📱 Electronics</option>
                <option value="Clothing" <?php echo $category === 'Clothing' ? 'selected' : ''; ?>>👕 Clothing</option>
                <option value="Beverages" <?php echo $category === 'Beverages' ? 'selected' : ''; ?>>🥤 Beverages</option>
                <option value="Household Contents" <?php echo $category === 'Household Contents' ? 'selected' : ''; ?>>🏠 Household</option>
                <option value="Other" <?php echo $category === 'Other' ? 'selected' : ''; ?>>📦 Other</option>
            </select>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Search
            </button>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Clear
            </a>
        </form>
    </div>

    <!-- Listings Grid -->
    <div class="products-grid">
        <?php if (empty($ads)): ?>
            <div style="grid-column: 1/-1; text-align: center; padding: 40px; background: white; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                <i class="fas fa-box-open" style="font-size: 4rem; color: #ccc; margin-bottom: 1rem;"></i>
                <p style="font-size: 1.2rem; margin-bottom: 1rem;">No listings found</p>
                <a href="ads/ad.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Post the first ad!
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($ads as $ad): ?>
                <div class="product-card" data-ad-id="<?php echo $ad['id']; ?>">
                    <div class="product-image">
                        <?php if ($ad['image']): ?>
                            <img src="<?php echo htmlspecialchars($ad['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($ad['title']); ?>"
                                 style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <div style="width: 100%; height: 100%; background: linear-gradient(45deg, #f8f9fa, #e9ecef); display: flex; align-items: center; justify-content: center; flex-direction: column;">
                                <i class="fas fa-image" style="font-size: 2rem; color: #6c757d; margin-bottom: 0.5rem;"></i>
                                <span style="color: #6c757d;">No Image</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="product-info">
                        <h3 class="product-title">
                            <a href="ads/ad_detail.php?id=<?php echo $ad['id']; ?>" style="text-decoration: none; color: inherit;">
                                <?php echo htmlspecialchars($ad['title']); ?>
                            </a>
                        </h3>
                        
                        <div class="product-price">
                            R<?php echo number_format($ad['price'], 2); ?>
                        </div>
                        
                        <p class="product-description">
                            <?php echo htmlspecialchars(substr($ad['description'], 0, 100)); ?>
                            <?php echo strlen($ad['description']) > 100 ? '...' : ''; ?>
                        </p>
                        
                        <?php if ($ad['category']): ?>
                            <span class="product-category">
                                <?php echo htmlspecialchars($ad['category']); ?>
                            </span>
                        <?php endif; ?>
                        
                        <div style="margin-top: 1rem; font-size: 0.8rem; color: #666;">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($ad['name'] . ' ' . $ad['surname']); ?>
                        </div>
                        
                        <div style="margin-top: 1rem; display: flex; gap: 0.5rem;">
                            <a href="ads/ad_detail.php?id=<?php echo $ad['id']; ?>" class="btn btn-primary" style="flex: 1; text-align: center; text-decoration: none; font-size: 0.9rem;">
                                <i class="fas fa-eye"></i> View
                            </a>
                            
                            <?php if ($ad['seller_id'] != $_SESSION['user']['id']): ?>
                                <button class="btn btn-secondary wishlist-btn" data-ad-id="<?php echo $ad['id']; ?>" style="font-size: 0.9rem;">
                                    <i class="fas fa-heart"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script src="assets/js/script.js"></script>
</body>
</html>
