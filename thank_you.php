<?php
session_start();
require_once 'database.php';

// Check if there's an order to display
if (!isset($_SESSION['last_order'])) {
    header('Location: index.php');
    exit;
}

$order = $_SESSION['last_order'];
$page_title = "Order Confirmation";
include 'components/header.php';
?>

<style>
    .thank-you-section {
        padding: 5rem 0;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        min-height: 80vh;
        display: flex;
        align-items: center;
    }

    .order-success-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        padding: 3rem;
        text-align: center;
        max-width: 800px;
        margin: 0 auto;
    }

    .success-icon {
        width: 100px;
        height: 100px;
        background: #28a745;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 2rem;
    }

    .success-icon i {
        font-size: 3rem;
        color: white;
    }

    .order-id {
        font-size: 1.25rem;
        color: #6c757d;
        margin-bottom: 2rem;
    }

    .order-details {
        background: #f8f9fa;
        border-radius: 0.5rem;
        padding: 1.5rem;
        margin: 2rem 0;
        text-align: left;
    }

    .detail-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #dee2e6;
    }

    .detail-row:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }

    .detail-label {
        color: #6c757d;
        font-weight: 500;
    }

    .detail-value {
        color: #212529;
        font-weight: 600;
    }

    .next-steps {
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 1px solid #dee2e6;
    }

    .next-steps h3 {
        margin-bottom: 1rem;
        color: #495057;
    }

    .next-steps p {
        color: #6c757d;
        margin-bottom: 2rem;
    }

    .action-buttons {
        display: flex;
        gap: 1rem;
        justify-content: center;
    }

    @media (max-width: 576px) {
        .order-success-card {
            padding: 2rem;
        }

        .action-buttons {
            flex-direction: column;
        }

        .action-buttons .btn {
            width: 100%;
        }
    }
</style>

<section class="thank-you-section">
    <div class="container">
        <div class="order-success-card">
            <div class="success-icon">
                <i class="bi bi-check-lg"></i>
            </div>

            <h1 class="display-4 mb-3">Thank You!</h1>
            <p class="lead mb-4">Your order has been placed successfully.</p>

            <div class="order-id">
                Order #<?php echo $order['order_id']; ?>
            </div>

            <div class="order-details">
                <div class="detail-row">
                    <span class="detail-label">Order Total</span>
                    <span class="detail-value"><?php echo formatPrice(convertToVND($order['total'])); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Method</span>
                    <span class="detail-value"><?php echo ucfirst($order['payment_method']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Order Status</span>
                    <span class="detail-value">
                        <span class="badge bg-warning">Processing</span>
                    </span>
                </div>
            </div>

            <div class="next-steps">
                <h3>What's Next?</h3>
                <p>We'll send you an email confirmation with order details and tracking information once your order ships.</p>

                <div class="action-buttons">
                    <a href="products.php" class="btn btn-outline-primary btn-lg">
                        <i class="bi bi-arrow-left me-2"></i>Continue Shopping
                    </a>
                    <a href="#" class="btn btn-primary btn-lg">
                        <i class="bi bi-box me-2"></i>Track Order
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Clear the order from session
unset($_SESSION['last_order']);

include 'components/footer.php';
?>