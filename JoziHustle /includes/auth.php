<?php
// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}



function requireLogin() {
    if (!isset($_SESSION['user'])) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header("Location: ../auth/login.php"); 
        exit;
    }
}






/**
 * Check if user is logged in
 *
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Get current user array
 *
 * @return array|null
 */
function currentUser() {
    return $_SESSION['user'] ?? null;
}

/**
 * Check if the logged in user is an admin (user ID 1)
 *
 * @return bool
 */
function isAdmin() {
    return isLoggedIn() && $_SESSION['user_id'] == 1;
}
