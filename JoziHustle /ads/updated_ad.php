<?php
session_start();
require_once '../config/db_config.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../index.php");
    exit;
}

// Check if required fields are present
if (!isset($_POST['ad_id'], $_POST['title'], $_POST['price'], $_POST['description'], $_POST['category'])) {
    $_SESSION['error'] = "Missing required fields";
    header("Location: ad_edit.php?id=" . ($_POST['ad_id'] ?? ''));
    exit;
}

$ad_id = $_POST['ad_id'];
$title = trim($_POST['title']);
$price = trim($_POST['price']);
$description = trim($_POST['description']);
$category = trim($_POST['category']);
$rating = isset($_POST['rating']) ? trim($_POST['rating']) : null;
$seller_id = $_SESSION['user']['id'];

try {
    // Verify ownership
    $check = $conn->prepare("SELECT seller_id, image FROM ads WHERE id = ?");
    $check->execute([$ad_id]);
    $ad = $check->fetch();
    
    if (!$ad || $ad['seller_id'] != $seller_id) {
        header("Location: ../index.php?error=Unauthorized access");
        exit;
    }

    // Handle image update/deletion
    $current_image = $ad['image'];
    $image_path = null;
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        // Handle new image upload
        $image = $_FILES['image'];
        $image_name = uniqid() . "_" . basename($image['name']);
        $target_dir = "../uploads/";
        $target_file = $target_dir . $image_name;
        
        // Validate image
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($image['type'], $allowed_types)) {
            throw new Exception("Only JPG, PNG and GIF images are allowed");
        }
        
        if ($image['size'] > 5 * 1024 * 1024) {
            throw new Exception("File size must be less than 5MB");
        }
        
        // Move uploaded file
        if (move_uploaded_file($image['tmp_name'], $target_file)) {
            $image_path = "uploads/" . $image_name;
            // Delete old image if exists
            if ($current_image && file_exists("../" . $current_image)) {
                unlink("../" . $current_image);
            }
        } else {
            throw new Exception("Failed to upload image");
        }
    } elseif (isset($_POST['delete_image']) && $_POST['delete_image'] === '1') {
        // Delete existing image
        if ($current_image && file_exists("../" . $current_image)) {
            unlink("../" . $current_image);
        }
        $image_path = null;
    }

    // Prepare update query
    $update_fields = ["title = ?", "price = ?", "description = ?", "category = ?", "rating = ?"];
    $params = [$title, $price, $description, $category, $rating];

    if ($image_path !== null || isset($_POST['delete_image'])) {
        $update_fields[] = "image = ?";
        $params[] = $image_path;
    }

    $params[] = $ad_id;
    $params[] = $seller_id;

    // Update ad
    $stmt = $conn->prepare("UPDATE ads SET " . implode(", ", $update_fields) . " WHERE id = ? AND seller_id = ?");
    if ($stmt->execute($params)) {
        header("Location: ad_detail.php?id=" . $ad_id . "&message=Ad updated successfully");
        exit;
    } else {
        throw new Exception("Failed to update ad");
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header("Location: ad_edit.php?id=" . $ad_id);
    exit;
}
?>
