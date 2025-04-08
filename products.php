<?php
require_once 'database.php';
include 'components/header.php';

// Get filter parameters
$brand = isset($_GET['brand']) ? $_GET['brand'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'name_asc';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 12;

// Get all brands for filter
$brands = getAllBrands();

// Get filtered and sorted products
$products = getFilteredProducts($brand, $category, $sort, $page, $per_page);
$total_products = getTotalFilteredProducts($brand, $category);
$total_pages = ceil($total_products / $per_page);
?>

<main class="bg-light py-5">
    <div class="container">
        <!-- Page Header -->
        <div class="row mb-5">
            <div class="col-lg-8">
                <h1 class="display-4 mb-2">Our Laptops</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item active">Products</li>
                        <?php if ($brand): ?>
                            <li class="breadcrumb-item active"><?php echo htmlspecialchars($brand); ?></li>
                        <?php endif; ?>
                    </ol>
                </nav>
            </div>
            <div class="col-lg-4">
                <div class="d-flex justify-content-lg-end align-items-center h-100">
                    <span class="text-muted me-3">
                        <?php echo $total_products; ?> products found
                    </span>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Filters Sidebar -->
            <div class="col-lg-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Filters</h5>
                        <form action="" method="GET" id="filterForm">
                            <!-- Brand Filter -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">Brand</label>
                                <div class="overflow-auto" style="max-height: 200px;">
                                    <?php foreach ($brands as $b): ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="brand"
                                                value="<?php echo $b; ?>" <?php echo ($brand === $b) ? 'checked' : ''; ?>>
                                            <label class="form-check-label">
                                                <?php echo $b; ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Category Filter -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">Category</label>
                                <select class="form-select" name="category">
                                    <option value="">All Categories</option>
                                    <option value="gaming" <?php echo ($category === 'gaming') ? 'selected' : ''; ?>>
                                        Gaming</option>
                                    <option value="business"
                                        <?php echo ($category === 'business') ? 'selected' : ''; ?>>Business</option>
                                    <option value="student" <?php echo ($category === 'student') ? 'selected' : ''; ?>>
                                        Student</option>
                                    <option value="premium" <?php echo ($category === 'premium') ? 'selected' : ''; ?>>
                                        Premium</option>
                                </select>
                            </div>

                            <!-- Sort Filter -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">Sort By</label>
                                <select class="form-select" name="sort">
                                    <option value="name_asc" <?php echo ($sort === 'name_asc') ? 'selected' : ''; ?>>
                                        Name (A-Z)</option>
                                    <option value="name_desc" <?php echo ($sort === 'name_desc') ? 'selected' : ''; ?>>
                                        Name (Z-A)</option>
                                    <option value="price_asc" <?php echo ($sort === 'price_asc') ? 'selected' : ''; ?>>
                                        Price (Low to High)</option>
                                    <option value="price_desc"
                                        <?php echo ($sort === 'price_desc') ? 'selected' : ''; ?>>Price (High to Low)
                                    </option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-funnel-fill me-2"></i>Apply Filters
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="col-lg-9">
                <?php if (empty($products)): ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>No products found matching your criteria.
                    </div>
                <?php else: ?>
                    <div class="row g-4">
                        <?php foreach ($products as $product): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card h-100 border-0 shadow-sm product-card">
                                    <div class="position-relative">
                                        <a href="product-detail.php?id=<?php echo $product['id']; ?>">
                                            <img src="<?php echo $product['img_link']; ?>" class="card-img-top p-3"
                                                alt="<?php echo $product['name']; ?>">
                                        </a>
                                        <div class="product-actions">
                                            <?php if (isset($_SESSION['user'])): ?>
                                                <button onclick="toggleWishlist(<?php echo $product['id']; ?>)"
                                                    class="btn btn-light rounded-circle p-2 wishlist-btn <?php echo isInWishlist($_SESSION['user']['id'], $product['id']) ? 'active' : ''; ?>"
                                                    data-product-id="<?php echo $product['id']; ?>">
                                                    <i
                                                        class="bi bi-heart<?php echo isInWishlist($_SESSION['user']['id'], $product['id']) ? '-fill' : ''; ?>"></i>
                                                </button>
                                            <?php endif; ?>
                                            <button onclick="showQuickView(<?php echo $product['id']; ?>)"
                                                class="btn btn-light rounded-pill">
                                                <i class="bi bi-eye me-2"></i>Quick View
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body d-flex flex-column">
                                        <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="text-decoration-none text-dark">
                                            <h4 class="card-title h6 mb-3"><?php echo $product['name']; ?></h4>
                                        </a>
                                        <div class="specs mb-3">
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="bi bi-cpu-fill text-primary me-2"></i>
                                                <span class="small text-muted text-truncate" style="max-width: 200px;"
                                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                                    title="<?php echo htmlspecialchars($product['processor']); ?>">
                                                    <?php echo htmlspecialchars($product['processor']); ?>
                                                </span>
                                            </div>
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="bi bi-memory text-primary me-2"></i>
                                                <span class="small text-muted text-truncate" style="max-width: 200px;"
                                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                                    title="<?php echo htmlspecialchars($product['ram']); ?>">
                                                    <?php echo htmlspecialchars($product['ram']); ?>
                                                </span>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-hdd-fill text-primary me-2"></i>
                                                <span class="small text-muted text-truncate" style="max-width: 200px;"
                                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                                    title="<?php echo htmlspecialchars($product['storage']); ?>">
                                                    <?php echo htmlspecialchars($product['storage']); ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="mt-auto">
                                            <div class="text-center mb-3">
                                                <span
                                                    class="h5 text-danger fw-bold"><?php echo formatPrice($product['price']); ?></span>
                                            </div>
                                            <div class="d-grid gap-2">
                                                <button onclick="addToCart(<?php echo $product['id']; ?>)"
                                                    class="btn btn-outline-primary">
                                                    <i class="bi bi-cart-plus me-2"></i>Add to Cart
                                                </button>
                                                <button onclick="buyNow(<?php echo $product['id']; ?>)" class="btn btn-primary">
                                                    <i class="bi bi-lightning-fill me-2"></i>Buy Now
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav class="mt-5" aria-label="Product pagination">
                            <ul class="pagination justify-content-center">
                                <!-- Previous Button -->
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link"
                                            href="?page=<?php echo ($page - 1); ?>&brand=<?php echo urlencode($brand); ?>&category=<?php echo urlencode($category); ?>&sort=<?php echo urlencode($sort); ?>">
                                            <i class="bi bi-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php
                                $visible_pages = 5; // Number of visible page links
                                $half_visible = floor($visible_pages / 2);

                                // Calculate start and end page numbers
                                if ($total_pages <= $visible_pages) {
                                    $start_page = 1;
                                    $end_page = $total_pages;
                                } else {
                                    if ($page <= $half_visible) {
                                        $start_page = 1;
                                        $end_page = $visible_pages;
                                    } elseif ($page > $total_pages - $half_visible) {
                                        $start_page = $total_pages - $visible_pages + 1;
                                        $end_page = $total_pages;
                                    } else {
                                        $start_page = $page - $half_visible;
                                        $end_page = $page + $half_visible;
                                    }
                                }
                                ?>

                                <!-- First Page -->
                                <?php if ($start_page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link"
                                            href="?page=1&brand=<?php echo urlencode($brand); ?>&category=<?php echo urlencode($category); ?>&sort=<?php echo urlencode($sort); ?>">1</a>
                                    </li>
                                    <?php if ($start_page > 2): ?>
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <!-- Visible Pages -->
                                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                    <li class="page-item <?php echo ($page === $i) ? 'active' : ''; ?>">
                                        <a class="page-link"
                                            href="?page=<?php echo $i; ?>&brand=<?php echo urlencode($brand); ?>&category=<?php echo urlencode($category); ?>&sort=<?php echo urlencode($sort); ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <!-- Last Page -->
                                <?php if ($end_page < $total_pages): ?>
                                    <?php if ($end_page < $total_pages - 1): ?>
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                    <?php endif; ?>
                                    <li class="page-item">
                                        <a class="page-link"
                                            href="?page=<?php echo $total_pages; ?>&brand=<?php echo urlencode($brand); ?>&category=<?php echo urlencode($category); ?>&sort=<?php echo urlencode($sort); ?>"><?php echo $total_pages; ?></a>
                                    </li>
                                <?php endif; ?>

                                <!-- Next Button -->
                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link"
                                            href="?page=<?php echo ($page + 1); ?>&brand=<?php echo urlencode($brand); ?>&category=<?php echo urlencode($category); ?>&sort=<?php echo urlencode($sort); ?>">
                                            <i class="bi bi-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<div id="product-popup" class="popup">
    <div class="popup-content">
        <span class="close-popup" onclick="closePopup()">&times;</span>
        <img id="popup-image" src="" alt="Product Image">
        <div id="popup-info"></div>
    </div>
</div>

<!-- Initialize tooltips -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>

<script>
    function toggleWishlist(productId) {
        fetch('ajax/toggle_wishlist.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    showToast(data.error, 'danger');
                    return;
                }

                const btn = document.querySelector(`button[data-product-id="${productId}"]`);
                const icon = btn.querySelector('i');

                if (data.action === 'added') {
                    icon.classList.remove('bi-heart');
                    icon.classList.add('bi-heart-fill');
                    btn.classList.add('active');
                    showToast('Product added to wishlist');
                } else {
                    icon.classList.remove('bi-heart-fill');
                    icon.classList.add('bi-heart');
                    btn.classList.remove('active');
                    showToast('Product removed from wishlist');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error updating wishlist', 'danger');
            });
    }

    function addToCart(productId) {
        fetch('ajax/add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    showToast(data.error, 'danger');
                    return;
                }

                showToast(data.success);
                if (data.cart_count) {
                    updateCartCount(data.cart_count);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error adding to cart', 'danger');
            });
    }

    function showToast(message, type = 'success') {
        const toastContainer = document.getElementById('toast-container') || createToastContainer();

        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');

        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;

        toastContainer.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();

        setTimeout(() => {
            toast.remove();
        }, 3000);
    }

    function createToastContainer() {
        const container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'position-fixed bottom-0 end-0 p-3';
        container.style.zIndex = '1050';
        document.body.appendChild(container);
        return container;
    }

    function updateCartCount(count) {
        const cartCountElements = document.querySelectorAll('.cart-count');
        cartCountElements.forEach(element => {
            element.textContent = count;
        });
    }

    function buyNow(productId) {
        addToCart(productId);
        setTimeout(() => {
            window.location.href = 'cart.php';
        }, 500);
    }

    function showQuickView(productId) {
        window.location.href = 'product-detail.php?id=' + productId;
    }
</script>

<style>
    .wishlist-btn {
        transition: all 0.3s ease;
        width: 38px;
        height: 38px;
        padding: 0 !important;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .wishlist-btn:hover {
        background-color: var(--bs-danger) !important;
        color: white;
    }

    .wishlist-btn.active {
        background-color: var(--bs-danger) !important;
        color: white;
    }

    #toast-container {
        z-index: 1050;
    }

    .product-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        transition: transform 0.2s;
        position: relative;
    }

    .product-card:hover {
        transform: translateY(-4px);
    }

    .product-image {
        position: relative;
        padding-top: 75%;
        overflow: hidden;
    }

    .product-image img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s;
    }

    .product-card:hover .product-image img {
        transform: scale(1.05);
    }

    .product-actions {
        position: absolute;
        top: 12px;
        right: 12px;
        display: flex;
        flex-direction: column;
        gap: 8px;
        opacity: 0;
        transform: translateX(10px);
        transition: all 0.3s;
    }

    .product-card:hover .product-actions {
        opacity: 1;
        transform: translateX(0);
    }

    .btn-wishlist,
    .btn-quick-view {
        background: white;
        border: none;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        transition: all 0.2s;
    }

    .btn-wishlist:hover,
    .btn-quick-view:hover {
        transform: scale(1.1);
    }

    .btn-wishlist.active {
        background: #dc3545;
        color: white;
    }

    .product-info {
        padding: 16px;
    }

    .product-title {
        font-size: 1rem;
        font-weight: 500;
        margin-bottom: 8px;
        color: #1a1a1a;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .product-price {
        font-size: 1.125rem;
        font-weight: 600;
        color: #1a73e8;
        margin-bottom: 16px;
    }

    .product-buttons {
        display: flex;
        gap: 8px;
    }

    .btn-add-cart,
    .btn-buy-now {
        flex: 1;
        padding: 8px;
        font-size: 0.875rem;
    }

    /* Toast notification */
    .toast-container {
        position: fixed;
        bottom: 24px;
        right: 24px;
        z-index: 1000;
    }

    .toast {
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        padding: 16px;
        margin-top: 8px;
        display: flex;
        align-items: center;
        gap: 12px;
        animation: slideIn 0.3s ease;
    }

    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }

        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    .toast.success {
        border-left: 4px solid #28a745;
    }

    .toast.error {
        border-left: 4px solid #dc3545;
    }
</style>

<?php include 'components/footer.php'; ?>