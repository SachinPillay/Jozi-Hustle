<?php
session_start();
require_once '../config/db_config.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Error: No ad ID provided.");
}

$ad_id = $_GET['id'];
$seller_id = $_SESSION['user']['id'];

try {
    $conn->beginTransaction();

    // Step 1: Delete all wishlist entries related to the ad
    $wishlist_stmt = $conn->prepare("DELETE FROM wishlist WHERE ad_id = ?");
    $wishlist_stmt->execute([$ad_id]);

    // Step 2: Delete the ad itself
    $ad_stmt = $conn->prepare("DELETE FROM ads WHERE id = ? AND seller_id = ?");
    $ad_stmt->execute([$ad_id, $seller_id]);

    $conn->commit();
    header("Location: ../index.php?message=Ad deleted successfully");
    exit;

} catch (PDOException $e) {
    $conn->rollBack();
    error_log("Error deleting ad ID: $ad_id by Seller ID: $seller_id - " . $e->getMessage(), 0);
    die("Error: Could not delete ad. Please try again later.");
}
?>
