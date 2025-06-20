<?php
session_start();
require_once '../config/db_config.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit;
}

if (!isset($_POST['ad_id'], $_POST['status'])) {
    header("Location: ../index.php?error=Missing required parameters");
    exit;
}

$ad_id = $_POST['ad_id'];
$status = $_POST['status'];
$seller_id = $_SESSION['user']['id'];

try {
    // Verify ownership
    $check = $conn->prepare("SELECT seller_id FROM ads WHERE id = ?");
    $check->execute([$ad_id]);
    $ad = $check->fetch();
    
    if (!$ad || $ad['seller_id'] != $seller_id) {
        header("Location: ../index.php?error=Unauthorized access");
        exit;
    }

    // Ensure valid status update (Only 'available' or 'sold')
    if (!in_array($status, ['available', 'sold'])) {
        throw new Exception("Invalid status value");
    }

    // Update the ad's status in the database
    $stmt = $conn->prepare("UPDATE ads SET status = ? WHERE id = ? AND seller_id = ?");
    if ($stmt->execute([$status, $ad_id, $seller_id])) {
        header("Location: ../ads/ad_detail.php?id=" . $ad_id . "&message=Status updated successfully");
        exit;
    } else {
        throw new Exception("Could not update ad status");
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header("Location: ../ads/ad_detail.php?id=" . $ad_id);
    exit;
}
?>
