<?php
session_start();
require_once 'database.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user']['id'];
$cart_items = getCart($user_id);
$cart_total = getCartTotal($user_id);

// Get user details
$user = getUserById($user_id);

$page_title = "Checkout";
include 'components/header.php';
?>

<style>
    .page-title {
        font-size: 2.5rem;
        font-weight: 500;
        color: #333;
        margin-bottom: 0.5rem;
        padding-top: 5rem;
        position: relative;
    }

    .page-header {
        padding-bottom: 2rem;
        background: #fff;
        margin-bottom: 2rem;
        position: relative;
        z-index: 1;
    }

    .breadcrumb-nav {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 0.5rem;
    }

    .breadcrumb-left {
        display: flex;
        align-items: center;
        color: #6c757d;
    }

    .breadcrumb-left a {
        color: #6c757d;
        text-decoration: none;
    }

    .breadcrumb-left a:hover {
        color: #0d6efd;
    }

    .breadcrumb-separator {
        margin: 0 0.5rem;
        color: #6c757d;
    }

    .checkout-content {
        position: relative;
        z-index: 0;
        margin-top: 1rem;
    }

    .header-wrapper+.page-header {
        margin-top: -3rem;
    }

    .form-check-input:checked {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }

    .payment-method {
        padding: 1rem;
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        margin-bottom: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .payment-method:hover {
        border-color: #0d6efd;
    }

    .payment-method.selected {
        border-color: #0d6efd;
        background-color: #f8f9ff;
    }

    .payment-method-header {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .payment-method-icon {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #f8f9fa;
        border-radius: 0.5rem;
    }

    .payment-method-icon i {
        font-size: 1.5rem;
        color: #0d6efd;
    }

    .order-item {
        display: flex;
        align-items: center;
        padding: 1rem 0;
        border-bottom: 1px solid #dee2e6;
    }

    .order-item:last-child {
        border-bottom: none;
    }

    .order-item-image {
        width: 64px;
        height: 64px;
        object-fit: contain;
        margin-right: 1rem;
    }

    .order-item-details {
        flex-grow: 1;
    }

    .order-item-price {
        font-weight: 500;
        color: #333;
    }
</style>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <h1 class="page-title">Checkout</h1>
        <div class="breadcrumb-nav">
            <div class="breadcrumb-left">
                <a href="index.php">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="cart.php">Shopping Cart</a>
                <span class="breadcrumb-separator">/</span>
                <span>Checkout</span>
            </div>
        </div>
    </div>
</div>

<!-- Checkout Content -->
<div class="checkout-content bg-light py-5">
    <div class="container">
        <?php if (isset($cart_items['error']) || empty($cart_items)): ?>
            <div class="card border-0 shadow-sm text-center p-5">
                <div class="card-body py-5">
                    <div class="display-1 text-muted mb-4">
                        <i class="bi bi-cart-x"></i>
                    </div>
                    <h2 class="h3 mb-3">Your cart is empty</h2>
                    <p class="text-muted mb-4">Please add some items to your cart before proceeding to checkout.</p>
                    <a href="products.php" class="btn btn-primary btn-lg rounded-pill px-5">
                        <i class="bi bi-arrow-left me-2"></i>Continue Shopping
                    </a>
                </div>
            </div>
        <?php else: ?>
            <form id="checkoutForm" action="process_order.php" method="POST">
                <div class="row g-4">
                    <!-- Shipping Information -->
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body">
                                <h5 class="card-title mb-4">Shipping Information</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="fullName" class="form-label">Full Name</label>
                                        <input type="text" class="form-control" id="fullName" name="fullName" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="phone" class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                                    </div>
                                    <div class="col-12">
                                        <label for="email" class="form-label">Email Address</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                    </div>
                                    <div class="col-12">
                                        <label for="address" class="form-label">Delivery Address</label>
                                        <textarea class="form-control" id="address" name="address" rows="3" required><?php echo htmlspecialchars($user['address']); ?></textarea>
                                    </div>
                                    <div class="col-12">
                                        <label for="notes" class="form-label">Order Notes (Optional)</label>
                                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Special notes for delivery"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title mb-4">Payment Method</h5>
                                <div class="payment-methods">
                                    <div class="payment-method selected" onclick="selectPaymentMethod(this, 'cod')">
                                        <div class="payment-method-header">
                                            <div class="payment-method-icon">
                                                <i class="bi bi-cash"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1">Cash on Delivery</h6>
                                                <p class="small text-muted mb-0">Pay when you receive your order</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="payment-method" onclick="selectPaymentMethod(this, 'bank')">
                                        <div class="payment-method-header">
                                            <div class="payment-method-icon">
                                                <i class="bi bi-bank"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1">Bank Transfer</h6>
                                                <p class="small text-muted mb-0">Pay via bank transfer</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="payment-method" onclick="selectPaymentMethod(this, 'card')">
                                        <div class="payment-method-header">
                                            <div class="payment-method-icon">
                                                <i class="bi bi-credit-card"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1">Credit/Debit Card</h6>
                                                <p class="small text-muted mb-0">Pay with your card</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="payment_method" id="payment_method" value="cod">
                            </div>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm sticky-md-top" style="top: 2rem;">
                            <div class="card-body">
                                <h5 class="card-title mb-4">Order Summary</h5>

                                <!-- Order Items -->
                                <div class="order-items mb-4">
                                    <?php foreach ($cart_items as $item): ?>
                                        <div class="order-item">
                                            <img src="<?php echo htmlspecialchars($item['img_link']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="order-item-image">
                                            <div class="order-item-details">
                                                <h6 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h6>
                                                <p class="small text-muted mb-0">Quantity: <?php echo $item['quantity']; ?></p>
                                            </div>
                                            <div class="order-item-price">
                                                <?php echo formatPrice(convertToVND($item['total_price'])); ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <!-- Price Details -->
                                <div class="price-details">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Subtotal</span>
                                        <span><?php echo formatPrice(convertToVND($cart_total)); ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Shipping</span>
                                        <span class="text-success">Free</span>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between mb-4">
                                        <span class="h5 mb-0">Total</span>
                                        <span class="h5 mb-0"><?php echo formatPrice(convertToVND($cart_total)); ?></span>
                                    </div>

                                    <button type="submit" class="btn btn-primary btn-lg w-100">
                                        <i class="bi bi-lock-fill me-2"></i>Place Order
                                    </button>
                                </div>

                                <!-- Security Badge -->
                                <div class="mt-4">
                                    <div class="d-flex align-items-center text-muted small">
                                        <i class="bi bi-shield-check me-2"></i>
                                        <span>Your payment information is processed securely</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
    function selectPaymentMethod(element, method) {
        // Remove selected class from all payment methods
        document.querySelectorAll('.payment-method').forEach(el => {
            el.classList.remove('selected');
        });

        // Add selected class to clicked payment method
        element.classList.add('selected');

        // Update hidden input value
        document.getElementById('payment_method').value = method;
    }

    // Form validation
    document.getElementById('checkoutForm').addEventListener('submit', function(e) {
        e.preventDefault();

        // Basic form validation
        const requiredFields = ['fullName', 'phone', 'email', 'address'];
        let isValid = true;

        requiredFields.forEach(field => {
            const input = document.getElementById(field);
            if (!input.value.trim()) {
                input.classList.add('is-invalid');
                isValid = false;
            } else {
                input.classList.remove('is-invalid');
            }
        });

        if (isValid) {
            // Show confirmation dialog
            if (confirm('Are you sure you want to place this order?')) {
                this.submit();
            }
        } else {
            showToast('Please fill in all required fields', 'danger');
        }
    });
</script>

<?php include 'components/footer.php'; ?>