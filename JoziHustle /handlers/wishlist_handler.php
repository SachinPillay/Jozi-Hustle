<?php
session_start();
require_once '../config/db_config.php';

// Set content type for JSON responses
header('Content-Type: application/json');

if (!isset($_POST['ad_id'])) {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        echo json_encode(['success' => false, 'message' => 'No ad ID received']);
        exit;
    } else {
        die("Error: No ad ID received.");
    }
}

if (!isset($_SESSION['user'])) {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        echo json_encode(['success' => false, 'message' => 'Please login first']);
        exit;
    } else {
        header("Location: ../auth/login.php");
        exit;
    }
}

$user_id = $_SESSION['user']['id'];
$ad_id = $_POST['ad_id'];

try {
    // Check if ad is already in wishlist
    $stmt = $conn->prepare("SELECT id FROM wishlist WHERE buyer_id = ? AND ad_id = ?");
    $stmt->execute([$user_id, $ad_id]);
    $exists = $stmt->fetch();

    if ($exists) {
        // Remove from wishlist
        $stmt = $conn->prepare("DELETE FROM wishlist WHERE buyer_id = ? AND ad_id = ?");
        $stmt->execute([$user_id, $ad_id]);
        
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo json_encode(['success' => true, 'action' => 'removed', 'message' => 'Removed from wishlist']);
        } else {
            header("Location: ../pages/wishlist.php?message=removed");
        }
    } else {
        // Add to wishlist
        $stmt = $conn->prepare("INSERT INTO wishlist (buyer_id, ad_id, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$user_id, $ad_id]);
        
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo json_encode(['success' => true, 'action' => 'added', 'message' => 'Added to wishlist']);
        } else {
            header("Location: ../pages/wishlist.php?message=added");
        }
    }
} catch (Exception $e) {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    } else {
        die("Database error: " . $e->getMessage());
    }
}
?>
