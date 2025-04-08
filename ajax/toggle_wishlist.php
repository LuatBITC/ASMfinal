<?php
// Prevent any output before headers
ob_start();

// Disable error reporting
error_reporting(0);
ini_set('display_errors', 0);

// Start session and include database
session_start();
require_once '../database.php';

// Set JSON header
header('Content-Type: application/json');

$response = [];

try {
    // Check login status
    if (!isset($_SESSION['user'])) {
        $response = ['error' => 'Please login to manage wishlist'];
    }
    // Validate input
    else if (!isset($_POST['product_id']) || !is_numeric($_POST['product_id'])) {
        $response = ['error' => 'Invalid product ID'];
    } else {
        $user_id = $_SESSION['user']['id'];
        $product_id = intval($_POST['product_id']);

        // Check if product is already in wishlist
        if (isInWishlist($user_id, $product_id)) {
            // Remove from wishlist
            $result = removeFromWishlist($user_id, $product_id);
            if (isset($result['error'])) {
                $response = ['error' => $result['error']];
            } else {
                $response = ['action' => 'removed'];
            }
        } else {
            // Add to wishlist
            $result = addToWishlist($user_id, $product_id);
            if (isset($result['error'])) {
                $response = ['error' => $result['error']];
            } else {
                $response = ['action' => 'added'];
            }
        }
    }
} catch (Exception $e) {
    $response = ['error' => $e->getMessage()];
}

// Clear output buffer and send JSON response
ob_clean();
echo json_encode($response);
exit;
