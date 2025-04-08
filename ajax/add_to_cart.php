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
        $response = ['error' => 'Please login to add items to cart'];
    }
    // Validate input
    else if (!isset($_POST['product_id']) || !is_numeric($_POST['product_id'])) {
        $response = ['error' => 'Invalid product ID'];
    } else {
        $user_id = $_SESSION['user']['id'];
        $product_id = intval($_POST['product_id']);
        $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

        if ($quantity <= 0) {
            $response = ['error' => 'Invalid quantity'];
        } else {
            // Add to cart
            $result = addToCart($user_id, $product_id, $quantity);

            if (isset($result['error'])) {
                $response = ['error' => $result['error']];
            } else {
                // Get updated cart count
                $cart_count = getCartCount($user_id);
                $response = [
                    'success' => 'Product added to cart successfully',
                    'cart_count' => $cart_count
                ];
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
