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

$page_title = "Shopping Cart";
include 'components/header.php';
?>

<style>
    .page-title {
        font-size: 2.5rem;
        font-weight: 500;
        color: #333;
        margin-bottom: 0.5rem;
        padding-top: 5rem;
        /* Increased padding-top */
        position: relative;
        /* Added position relative */
    }

    .page-header {
        padding-bottom: 2rem;
        background: #fff;
        margin-bottom: 2rem;
        position: relative;
        /* Added position relative */
        z-index: 1;
        /* Added z-index */
    }

    .breadcrumb-nav {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 0.5rem;
        /* Added padding-top */
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

    .cart-items-count {
        color: #6c757d;
        font-size: 0.9rem;
    }

    /* Add margin to main content */
    .cart-content {
        position: relative;
        /* Added position relative */
        z-index: 0;
        /* Added z-index */
        margin-top: 1rem;
    }

    /* Added styles for header spacing */
    .header-wrapper+.page-header {
        margin-top: -3rem;
        /* Negative margin to adjust for header space */
    }
</style>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <h1 class="page-title">Shopping Cart</h1>
        <div class="breadcrumb-nav">
            <div class="breadcrumb-left">
                <a href="index.php">Home</a>
                <span class="breadcrumb-separator">/</span>
                <span>Shopping Cart</span>
            </div>
            <div class="cart-items-count">
                <?php if (!empty($cart_items) && !isset($cart_items['error'])): ?>
                    <?php echo count($cart_items); ?> items in cart
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Cart Content -->
<div class="cart-content bg-light py-5">
    <div class="container">
        <?php if (isset($cart_items['error']) || empty($cart_items)): ?>
            <div class="card border-0 shadow-sm text-center p-5">
                <div class="card-body py-5">
                    <div class="display-1 text-muted mb-4">
                        <i class="bi bi-cart-x"></i>
                    </div>
                    <h2 class="h3 mb-3">Your cart is empty</h2>
                    <p class="text-muted mb-4">Looks like you haven't added any items to your cart yet.</p>
                    <a href="products.php" class="btn btn-primary btn-lg rounded-pill px-5">
                        <i class="bi bi-arrow-left me-2"></i>Continue Shopping
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <!-- Cart Items -->
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-borderless align-middle mb-0">
                                    <thead class="table-light text-uppercase small">
                                        <tr>
                                            <th scope="col" class="ps-3">Product</th>
                                            <th scope="col">Price</th>
                                            <th scope="col" style="width: 160px;">Quantity</th>
                                            <th scope="col">Total</th>
                                            <th scope="col"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="border-top">
                                        <?php foreach ($cart_items as $item): ?>
                                            <tr>
                                                <td class="ps-3">
                                                    <div class="d-flex align-items-center">
                                                        <img src="<?php echo htmlspecialchars($item['img_link']); ?>"
                                                            alt="<?php echo htmlspecialchars($item['name']); ?>" class="rounded"
                                                            style="width: 80px; height: 80px; object-fit: contain;">
                                                        <div class="ms-3">
                                                            <h6 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h6>
                                                            <span class="text-muted small">SKU:
                                                                <?php echo $item['product_id']; ?></span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?php echo formatPrice(convertToVND($item['price'])); ?></td>
                                                <td>
                                                    <div class="input-group input-group-sm">
                                                        <button class="btn btn-outline-secondary border" type="button"
                                                            onclick="updateQuantity(<?php echo $item['product_id']; ?>, 'decrease')">
                                                            <i class="bi bi-dash"></i>
                                                        </button>
                                                        <input type="number" class="form-control text-center border"
                                                            value="<?php echo $item['quantity']; ?>" min="1"
                                                            onchange="updateQuantity(<?php echo $item['product_id']; ?>, 'set', this.value)">
                                                        <button class="btn btn-outline-secondary border" type="button"
                                                            onclick="updateQuantity(<?php echo $item['product_id']; ?>, 'increase')">
                                                            <i class="bi bi-plus"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                                <td class="fw-bold">
                                                    <?php echo formatPrice(convertToVND($item['total_price'])); ?></td>
                                                <td class="text-end pe-3">
                                                    <button class="btn btn-link text-danger p-0"
                                                        onclick="removeFromCart(<?php echo $item['product_id']; ?>)">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title mb-4">Order Summary</h5>
                            <div class="d-flex justify-content-between mb-3">
                                <span class="text-muted">Subtotal</span>
                                <span class="fw-bold"><?php echo formatPrice(convertToVND($cart_total)); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span class="text-muted">Shipping</span>
                                <span class="text-success">Free</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-4">
                                <span class="h5 mb-0">Total</span>
                                <span class="h5 mb-0"><?php echo formatPrice(convertToVND($cart_total)); ?></span>
                            </div>
                            <div class="d-grid gap-2">
                                <a href="checkout.php" class="btn btn-primary btn-lg">
                                    <i class="bi bi-credit-card me-2"></i>Proceed to Checkout
                                </a>
                                <a href="products.php" class="btn btn-outline-primary">
                                    <i class="bi bi-arrow-left me-2"></i>Continue Shopping
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Secure Shopping -->
                    <div class="card border-0 shadow-sm mt-4">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <i class="bi bi-shield-check text-success h4 mb-0 me-2"></i>
                                <h6 class="mb-0">Secure Shopping</h6>
                            </div>
                            <p class="small text-muted mb-0">Your payment information is processed securely.</p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    function updateQuantity(productId, action, value = null) {
        let quantity;
        const input = document.querySelector(`input[onchange*="${productId}"]`);

        switch (action) {
            case 'increase':
                quantity = parseInt(input.value) + 1;
                break;
            case 'decrease':
                quantity = Math.max(1, parseInt(input.value) - 1);
                break;
            case 'set':
                quantity = Math.max(1, parseInt(value));
                break;
        }

        fetch('ajax/update_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}&quantity=${quantity}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    showToast(data.error, 'danger');
                } else {
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred while updating the cart', 'danger');
            });
    }

    function removeFromCart(productId) {
        if (!confirm('Are you sure you want to remove this item from your cart?')) {
            return;
        }

        fetch('ajax/update_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}&quantity=0`
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    showToast(data.error, 'danger');
                } else {
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred while removing the item', 'danger');
            });
    }
</script>

<?php include 'components/footer.php'; ?>