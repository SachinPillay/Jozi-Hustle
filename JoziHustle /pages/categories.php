<?php
session_start();
require_once '../config/db_config.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit;
}

// Get selected category from URL
$selectedCategory = isset($_GET['category']) ? trim($_GET['category']) : '';

// Get all available categories with product counts
$categoryStmt = $conn->query("
    SELECT category, COUNT(*) as product_count 
    FROM ads 
    WHERE category IS NOT NULL AND category != ''
    GROUP BY category 
    ORDER BY category
");
$categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

// If a specific category is selected, get its products
$categoryAds = [];
if (!empty($selectedCategory)) {
    $stmt = $conn->prepare("
        SELECT a.*, u.name as seller_name, u.surname as seller_surname 
        FROM ads a 
        JOIN users u ON a.seller_id = u.id 
        WHERE a.category = ?
        ORDER BY a.created_at DESC
    ");
    $stmt->execute([$selectedCategory]);
    $categoryAds = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo !empty($selectedCategory) ? htmlspecialchars($selectedCategory) . ' - ' : ''; ?>Categories - Jozi Hustle</title>
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
                <li><a href="categories.php" class="active"><i class="fas fa-list"></i> Categories</a></li>
                <li><a href="chat.php"><i class="fas fa-comments"></i> Chat</a></li>
                <li><a href="../ads/ad.php"><i class="fas fa-plus"></i> Post Ad</a></li>
                <li><a href="wishlist.php"><i class="fas fa-heart"></i> Wishlist</a></li>
                <li><a href="../user/user_account.php"><i class="fas fa-user"></i> My Account</a></li>
                <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
            <button class="mobile-menu-toggle">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </nav>

    <div class="container">
        <?php if (empty($selectedCategory)): ?>
            <!-- Categories Overview -->
            <div class="categories-content">
                <h1>Browse by Category</h1>
                <p class="categories-subtitle">Find exactly what you're looking for</p>
                
                <div class="categories-grid">
                    <?php foreach ($categories as $category): ?>
                        <div class="category-card">
                            <a href="categories.php?category=<?php echo urlencode($category['category']); ?>" class="category-link">
                                <div class="category-icon">
                                    <?php
                                    // Add category-specific icons
                                    $icons = [
                                        'Electronics' => '📱',
                                        'Clothing' => '👕',
                                        'Food' => '🍎',
                                        'Beverages' => '🥤',
                                        'Books' => '📚',
                                        'Furniture' => '🪑',
                                        'Sports' => '⚽',
                                        'Health & Beauty' => '💄',
                                        'Household Contents' => '🏠',
                                        'Other' => '📦'
                                    ];
                                    echo isset($icons[$category['category']]) ? $icons[$category['category']] : '📦';
                                    ?>
                                </div>
                                <h3 class="category-name"><?php echo htmlspecialchars($category['category']); ?></h3>
                                <p class="category-count"><?php echo $category['product_count']; ?> item<?php echo $category['product_count'] !== 1 ? 's' : ''; ?></p>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="browse-all">
                    <a href="../index.php" class="btn btn-outline">Browse All Products</a>
                </div>
            </div>
            
        <?php else: ?>
            <!-- Specific Category View -->
            <div class="category-view">
                <div class="category-header">
                    <a href="categories.php" class="back-btn">← All Categories</a>
                    <h1><?php echo htmlspecialchars($selectedCategory); ?></h1>
                    <p class="category-subtitle"><?php echo count($categoryAds); ?> item<?php echo count($categoryAds) !== 1 ? 's' : ''; ?> found</p>
                </div>
                
                <?php if (empty($categoryAds)): ?>
                    <div class="empty-category">
                        <div class="empty-icon">📦</div>
                        <h3>No items in this category yet</h3>
                        <p>Be the first to post an item in <?php echo htmlspecialchars($selectedCategory); ?>!</p>
                        <a href="../ads/ad.php" class="btn btn-primary">Post an Ad</a>
                    </div>
                <?php else: ?>
                    <div class="products-grid">
                        <?php foreach ($categoryAds as $ad): ?>
                            <div class="product-card" onclick="window.location.href='../ads/ad_detail.php?id=<?php echo $ad['id']; ?>'">
                                <?php if (!empty($ad['image']) && file_exists('../' . $ad['image'])): ?>
                                    <img src="../<?php echo htmlspecialchars($ad['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($ad['title']); ?>" 
                                         class="product-image">
                                <?php else: ?>
                                    <div class="product-image">No Image Available</div>
                                <?php endif; ?>
                                
                                <div class="product-info">
                                    <h3 class="product-title">
                                        <a href="../ads/ad_detail.php?id=<?php echo $ad['id']; ?>">
                                            <?php echo htmlspecialchars($ad['title']); ?>
                                        </a>
                                    </h3>
                                    <div class="product-price">R<?php echo number_format($ad['price'], 2); ?></div>
                                    <p class="product-description"><?php echo htmlspecialchars(substr($ad['description'], 0, 100)); ?><?php echo strlen($ad['description']) > 100 ? '...' : ''; ?></p>
                                    
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
                                        <span class="product-category"><?php echo htmlspecialchars($ad['category']); ?></span>
                                        <small style="color: #666;">
                                            by <?php echo htmlspecialchars($ad['seller_name'] . ' ' . $ad['seller_surname']); ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Jozi Hustle. Built for student success.</p>
        </div>
    </footer>

    <script src="../assets/js/script.js"></script>
    <script>
    // Mobile menu toggle
    document.querySelector('.mobile-menu-toggle').addEventListener('click', function() {
        document.querySelector('.nav-links').classList.toggle('active');
    });
    </script>

    <style>
        .categories-content {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
            text-align: center;
        }
        
        .categories-content h1 {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .categories-subtitle {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 3rem;
        }
        
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }
        
        .category-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
        }
        
        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .category-link {
            display: block;
            padding: 2rem 1.5rem;
            text-decoration: none;
            color: inherit;
        }
        
        .category-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .category-name {
            font-size: 1.3rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .category-count {
            color: #666;
            font-size: 0.9rem;
            margin: 0;
        }
        
        .browse-all {
            padding: 2rem 0;
        }
        
        .category-view {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .category-header {
            margin-bottom: 2rem;
        }
        
        .back-btn {
            display: inline-block;
            margin-bottom: 1rem;
            color: #007bff;
            text-decoration: none;
            font-weight: 500;
        }
        
        .back-btn:hover {
            text-decoration: underline;
        }
        
        .category-header h1 {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .category-subtitle {
            color: #666;
            font-size: 1.1rem;
        }
        
        .empty-category {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .empty-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        
        .empty-category h3 {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 1rem;
        }
        
        .empty-category p {
            color: #666;
            margin-bottom: 2rem;
        }
        
        .nav-links .active {
            color: #007bff;
            font-weight: 600;
        }
        
        .product-card {
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        
        .product-card:hover {
            transform: translateY(-3px);
        }
        
        .product-title a {
            color: #333;
            text-decoration: none;
        }
        
        .product-title a:hover {
            color: #007bff;
        }
        
        .product-category {
            background: #e9ecef;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            color: #495057;
            text-decoration: none;
        }
        
        @media (max-width: 768px) {
            .categories-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 1rem;
            }
            
            .categories-content h1,
            .category-header h1 {
                font-size: 2rem;
            }
            
            .category-link {
                padding: 1.5rem 1rem;
            }
            
            .category-icon {
                font-size: 2.5rem;
            }
        }
    </style>
</body>
</html>
