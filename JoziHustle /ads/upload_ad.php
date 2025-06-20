<?php
session_start();
require_once '../config/db_config.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit;
}

// Ensure required fields are submitted
if (!isset($_FILES['image']) || !isset($_POST['title']) || !isset($_POST['price']) || 
    !isset($_POST['description']) || !isset($_POST['category'])) {
    die("Please fill in all required fields: Title, Price, Description, Category and Image");
}

$title = trim($_POST['title']);
$price = trim($_POST['price']);
$description = trim($_POST['description']);
$category = trim($_POST['category']);
$seller_id = $_SESSION['user']['id'];

// Validate price
if (!is_numeric($price) || $price <= 0) {
    die("Error: Price must be a positive number");
}

// Validate image file type
$allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
if (!in_array($_FILES['image']['type'], $allowed_types)) {
    die("Error: Only JPG, PNG and GIF images are allowed");
}

// Validate file size (max 5MB)
if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
    die("Error: File size must be less than 5MB");
}

// Validate category
$valid_categories = ['Food', 'Electronics', 'Clothing', 'Beverages', 'Household Contents', 'Other'];
if (!in_array($category, $valid_categories)) {
    die("Error: Invalid category selected");
}

// Handle image upload
$image = $_FILES['image'];
$image_name = uniqid() . "_" . basename($image['name']); // Unique filename
$target_dir = "../uploads/";
$target_file = $target_dir . $image_name;

// Store the path in database relative to root
$db_image_path = "uploads/" . $image_name;

// Check for upload errors
if ($image['error'] !== UPLOAD_ERR_OK) {
    die("Error: File upload failed with error code " . $image['error']);
}

// Verify `uploads/` folder is writable
if (!is_writable($target_dir)) {
    die("Error: Uploads folder is not writable.");
}

// Move the uploaded file
if (!move_uploaded_file($image['tmp_name'], $target_file)) {
    die("Error: Could not save image. Check folder permissions.");
}

// Insert ad into database
$stmt = $conn->prepare("INSERT INTO ads (seller_id, title, price, description, image, category) VALUES (?, ?, ?, ?, ?, ?)");
if ($stmt->execute([$seller_id, $title, $price, $description, $db_image_path, $category])) {
    // Redirect AFTER successful insertion
    header("Location: ../index.php?message=Ad posted successfully");
    exit;
} else {
    die("Error: Could not insert ad.");
}
?>
