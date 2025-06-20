<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['role'] != 'buyer') {
    header("Location: login.php");
    exit;
}
require_once 'db_config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Buyer Home - Jozi Hustle</title>
  <link rel="stylesheet" href="StyleSheet.css">
  <style>
    body {
      background-color: #EDF4F2; /* Light grey background */
    }

    /* Navbar */
    .nav {
      background-color: #31473A; /* Dark green navbar */
      padding: 12px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.2);
      border-radius: 8px;
    }

    .nav a {
      color: #EDF4F2; /* Light grey text */
      font-weight: bold;
      text-decoration: none;
      padding: 10px 15px;
      border-radius: 6px;
      transition: all 0.3s ease-in-out;
    }

    .nav a:hover {
      background: rgba(255, 255, 255, 0.2);
    }

    /* Ads Grid */
    .container {
      max-width: 1200px;
      margin: 90px auto;
      padding: 20px;
      background: white;
      border-radius: 12px;
      box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
    }
    
    .ad-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 20px;
      padding: 20px;
      justify-content: center;
    }

    .ad-item {
      background: white;
      padding: 12px;
      border-radius: 15px;
      text-align: center;
      transition: 0.3s;
      box-shadow: 3px 3px 15px rgba(0, 0, 0, 0.15);
      width: 100%;
      max-width: 320px;
      min-height: 230px;
      overflow: hidden;
    }

    .ad-item:hover {
      box-shadow: 0px 0px 15px rgba(255, 255, 255, 0.8);
      transform: scale(1.03);
    }

    .ad-image {
      width: 100%;
      height: auto;
      border-radius: 12px;
      margin-bottom: 10px;
    }

    /* Buttons */
    .action-btn {
      display: inline-block;
      width: 80%;
      padding: 10px;
      margin: 5px 0;
      border: none;
      cursor: pointer;
      font-weight: bold;
      border-radius: 8px;
      background-color: #EDF4F2; /* Light grey buttons */
      color: #31473A; /* Dark green text */
      transition: transform 0.2s, background-color 0.3s ease-in-out;
    }

    .action-btn:hover {
      background-color: #31473A; /* Dark green hover */
      color: #EDF4F2; /* Light grey text */
      transform: scale(1.05); /* Pop-out effect */
    }
  </style>
</head>
<body>

<div class="nav">
  <a href="buyers_home.php">Home</a>
  <a href="categories.php">Categories</a>
  <a href="wishlist.php">Wishlist</a>
  <a href="chat.php">Chat</a>
  <span style="float:right;">
    <a href="user_account.php">My Account</a> |  
    <a href="logout.php">Logout</a>
  </span>
</div>

<div class="container">
  <h2>Welcome, <?php echo htmlspecialchars($_SESSION['user']['name']); ?>!</h2>
  <h3>Marketplace Ads</h3>

  <div class="ad-grid">
    <?php
    $stmt = $conn->prepare("SELECT * FROM ads ORDER BY id DESC");
    $stmt->execute();
    $ads = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($ads as $ad) {
        echo "<div class='ad-item'>";
        echo "<div class='ad-title'>" . htmlspecialchars($ad['title']) . "</div>";

        if ($ad['image']) {
            echo "<img src='" . htmlspecialchars($ad['image']) . "' alt='" . htmlspecialchars($ad['title']) . "' class='ad-image'>";
        }

        echo "<div class='ad-details'>Price: R " . htmlspecialchars($ad['price']) . "</div>";
        echo "<div class='ad-details'>" . htmlspecialchars($ad['short_description']) . "</div>";
        echo "<div class='ad-details'>Rating: " . htmlspecialchars($ad['rating']) . "</div>";

        echo "<a href='ad_detail.php?id=" . htmlspecialchars($ad['id']) . "'>
                <button class='action-btn'>View Details</button>
              </a>";

        echo "</div>";
    }
    ?>
  </div>
</div>

<script src="script.js"></script>
</body>
</html>
