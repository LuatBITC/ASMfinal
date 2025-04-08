<?php
session_start();
require_once 'database.php';
require_once 'mail_config.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: checkout.php');
    exit;
}

// Get user ID and cart items
$user_id = $_SESSION['user']['id'];
$cart_items = getCart($user_id);
$cart_total = getCartTotal($user_id);

// Validate cart
if (empty($cart_items) || isset($cart_items['error'])) {
    $_SESSION['error'] = 'Your cart is empty';
    header('Location: cart.php');
    exit;
}

try {
    global $pdo;
    // Start transaction
    $pdo->beginTransaction();

    // Get form data
    $fullName = $_POST['fullName'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $address = $_POST['address'] ?? '';
    $notes = $_POST['notes'] ?? '';
    $payment_method = $_POST['payment_method'] ?? 'cod';

    // Validate required fields
    if (empty($fullName) || empty($phone) || empty($email) || empty($address)) {
        throw new Exception('Please fill in all required fields');
    }

    // Generate order ID (current timestamp + random number)
    $order_id = time() . rand(1000, 9999);

    // Insert into orders table
    $stmt = $pdo->prepare("INSERT INTO orders (order_id, user_id, full_name, phone, email, address, notes, payment_method, total_amount, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");

    if (!$stmt->execute([$order_id, $user_id, $fullName, $phone, $email, $address, $notes, $payment_method, $cart_total])) {
        throw new Exception('Error creating order');
    }

    $order_db_id = $pdo->lastInsertId();

    // Insert order items
    $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");

    foreach ($cart_items as $item) {
        if (!$stmt->execute([$order_db_id, $item['product_id'], $item['quantity'], $item['price']])) {
            throw new Exception('Error adding order items');
        }
    }

    // Clear user's cart
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    if (!$stmt->execute([$user_id])) {
        throw new Exception('Error clearing cart');
    }

    // Commit transaction
    $pdo->commit();

    // Store order info in session for thank you page
    $_SESSION['last_order'] = [
        'order_id' => $order_id,
        'total' => $cart_total,
        'payment_method' => $payment_method
    ];

    // Prepare email content
    $to = $email;
    $subject = "Order Confirmation - Order #$order_id";

    // Common email header
    $message = "
    <html>
    <head>
        <title>Order Confirmation</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #f8f9fa; padding: 20px; text-align: center; }
            .order-details { margin: 20px 0; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            th, td { padding: 10px; border-bottom: 1px solid #ddd; text-align: left; }
            .total { font-weight: bold; background: #f8f9fa; }
            .bank-info { background: #f8f9fa; padding: 15px; margin: 20px 0; border-radius: 5px; }
            .important { color: #dc3545; font-weight: bold; }
            .logo { margin-bottom: 20px; }
            .logo img { max-width: 150px; }
            .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; font-size: 0.9em; color: #666; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <div class='logo'>
                    <img src='https://i.imgur.com/YB9TNuG.png' alt='Laptop Store Logo'>
                </div>
                <h2>Thank you for your order!</h2>
                <p>Your order #$order_id has been received.</p>
            </div>
            
            <div class='order-details'>
                <h3>Order Details:</h3>
                <table>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price</th>
                    </tr>";

    foreach ($cart_items as $item) {
        $message .= "
                    <tr>
                        <td>{$item['name']}</td>
                        <td>{$item['quantity']}</td>
                        <td>" . formatPrice(convertToVND($item['total_price'])) . "</td>
                    </tr>";
    }

    $message .= "
                    <tr class='total'>
                        <td colspan='2'><strong>Total:</strong></td>
                        <td><strong>" . formatPrice(convertToVND($cart_total)) . "</strong></td>
                    </tr>
                </table>
                
                <h3>Shipping Information:</h3>
                <p>
                Name: $fullName<br>
                Address: $address<br>
                Phone: $phone<br>
                Email: $email
                </p>";

    // Different content based on payment method
    if ($payment_method === 'bank') {
        // Generate VietQR URL
        $amount = number_format($cart_total, 0, '', '');
        $bank_id = '970436'; // Vietcombank bank code
        $account_no = '1031981346';
        $account_name = 'Pham Phu Thang';
        $memo = "Thanh toan don hang #$order_id";

        // URL encode the memo
        $memo = urlencode($memo);

        // Generate VietQR image URL
        $qr_url = "https://img.vietqr.io/image/{$bank_id}-{$account_no}-compact.png?amount={$amount}&addInfo={$memo}&accountName={$account_name}";

        $message .= "
                <div class='bank-info'>
                    <h3>Payment Information</h3>
                    <p>Please transfer the total amount to our bank account:</p>
                    <div class='bank-details'>
                        <div class='qr-code' style='text-align: center; margin: 20px 0;'>
                            <img src='$qr_url' alt='QR Code' style='max-width: 300px; width: 100%;'>
                            <p style='margin-top: 10px; font-size: 0.9em; color: #666;'>Scan QR code to transfer</p>
                        </div>
                        <div class='bank-account-info'>
                            <p>
                            Bank: <strong>Vietcombank</strong><br>
                            Account Number: <strong>1234567890</strong><br>
                            Account Name: <strong>LAPTOP STORE</strong><br>
                            Branch: <strong>Da Nang</strong><br>
                            Amount: <strong>" . formatPrice(convertToVND($cart_total)) . "</strong><br>
                            Reference: <strong>Order #$order_id</strong>
                            </p>
                        </div>
                    </div>
                    <p class='important'>Important: Please include your Order ID (#$order_id) in the transfer description.</p>
                    <p>Your order will be processed after we receive your payment.</p>
                    <div class='transfer-steps' style='margin-top: 20px; background: #fff; padding: 15px; border-radius: 5px;'>
                        <h4 style='margin-top: 0;'>How to pay:</h4>
                        <ol style='padding-left: 20px;'>
                            <li>Open your banking app</li>
                            <li>Select 'Scan QR' or 'QR Payment'</li>
                            <li>Scan the QR code above</li>
                            <li>Verify the payment information</li>
                            <li>Complete the transfer</li>
                        </ol>
                    </div>
                </div>";
    } else {
        $message .= "
                <div class='delivery-info'>
                    <h3>Delivery Information</h3>
                    <p>Your order will be delivered within 3-5 business days.</p>
                    <p>Payment: <strong>Cash on Delivery (COD)</strong></p>
                    <p>Amount to be paid: <strong>" . formatPrice(convertToVND($cart_total)) . "</strong></p>
                </div>";
    }

    $message .= "
                <p>If you have any questions about your order, please contact us:</p>
                <p>
                Phone: 0766526344<br>
                Email: support@laptopstore.com
                </p>
            </div>
            <div class='footer'>
                <p>This is an automated email, please do not reply directly to this email.</p>
                <p>&copy; " . date('Y') . " Laptop Store. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>";

    // Send email using PHPMailer
    if (!sendMail($to, $subject, $message)) {
        // Log email sending error but don't stop the order process
        error_log("Failed to send order confirmation email to: $email");
    }

    // Redirect to thank you page
    header('Location: thank_you.php');
    exit;
} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($pdo)) {
        $pdo->rollBack();
    }

    $_SESSION['error'] = $e->getMessage();
    header('Location: checkout.php');
    exit;
}