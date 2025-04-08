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
        $response = ['error' => 'Please login to update cart'];
    }
    // Validate input
    else if (
        !isset($_POST['product_id']) || !is_numeric($_POST['product_id']) ||
        !isset($_POST['quantity']) || !is_numeric($_POST['quantity'])
    ) {
        $response = ['error' => 'Invalid input parameters'];
    } else {
        $user_id = $_SESSION['user']['id'];
        $product_id = intval($_POST['product_id']);
        $quantity = intval($_POST['quantity']);

        if ($quantity === 0) {
            // Remove item from cart
            $result = removeFromCart($user_id, $product_id);
        } else {
            // Update quantity
            $result = updateCartQuantity($user_id, $product_id, $quantity);
        }

        if (isset($result['error'])) {
            $response = ['error' => $result['error']];
        } else {
            // Get updated cart info
            $cart_count = getCartCount($user_id);
            $cart_total = getCartTotal($user_id);
            $response = [
                'success' => true,
                'cart_count' => $cart_count,
                'cart_total' => formatPrice(convertToVND($cart_total))
            ];
        }
    }
} catch (Exception $e) {
    $response = ['error' => 'An error occurred while updating the cart'];
}

// Clear output buffer and send JSON response
ob_clean();
echo json_encode($response);
exit;
