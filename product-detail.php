<?php
require_once 'database.php';
include 'components/header.php';

// Check if product ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: products.php');
    exit;
}

$product_id = (int)$_GET['id'];
$product = getProductById($product_id);

// If product doesn't exist, redirect to products page
if (!$product) {
    header('Location: products.php');
    exit;
}

// Get related products (same brand or category)
$relatedProducts = getRelatedProducts($product_id, $product['brand'], 4);
?>

<main class="container">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="products.php">Sản phẩm</a></li>
            <li class="breadcrumb-item"><a
                    href="products.php?brand=<?php echo urlencode($product['brand']); ?>"><?php echo htmlspecialchars($product['brand']); ?></a>
            </li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($product['name']); ?>
            </li>
        </ol>
    </nav>

    <div class="row">
        <!-- Product Images and Info -->
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="row g-0">
                    <!-- Product Images -->
                    <div class="col-md-5 p-3">
                        <div class="product-image-container">
                            <img src="<?php echo $product['img_link']; ?>" class="img-fluid rounded product-detail-img"
                                alt="<?php echo htmlspecialchars($product['name']); ?>">
                        </div>
                    </div>

                    <!-- Product Info -->
                    <div class="col-md-7 p-4">
                        <div class="product-info h-100 d-flex flex-column">
                            <div class="brand-badge mb-2">
                                <span class="badge bg-light text-dark px-3 py-2 rounded-pill">
                                    <img src="./img/brands/<?php echo strtolower($product['brand']); ?>.png"
                                        alt="<?php echo htmlspecialchars($product['brand']); ?>"
                                        class="brand-logo-small me-2" onerror="this.style.display='none'">
                                    <?php echo htmlspecialchars($product['brand']); ?>
                                </span>
                            </div>

                            <h1 class="product-title h3 mb-3"><?php echo htmlspecialchars($product['name']); ?></h1>

                            <div class="product-meta mb-4">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="text-muted me-3">SKU: <?php echo $product['id']; ?></span>
                                    <span class="text-success"><i class="bi bi-check-circle-fill me-1"></i>Còn
                                        hàng</span>
                                </div>
                            </div>

                            <div class="product-price mb-4">
                                <h2 class="text-danger fw-bold h2 mb-0">
                                    <?php
                                    // Remove the ₫ symbol and add it separately
                                    $price_without_symbol = str_replace(' ₫', '', $product['formatted_price']);
                                    echo $price_without_symbol;
                                    ?> <span class="currency-symbol">₫</span>
                                </h2>
                                <?php if (isset($product['old_price']) && $product['old_price'] > $product['price']): ?>
                                <small class="text-muted text-decoration-line-through me-2">
                                    <?php echo number_format($product['old_price'], 0, ',', '.') . ' ₫'; ?>
                                </small>
                                <span
                                    class="badge bg-danger">-<?php echo round(($product['old_price'] - $product['price']) / $product['old_price'] * 100); ?>%</span>
                                <?php endif; ?>
                            </div>

                            <!-- Key Specifications -->
                            <div class="product-specs mb-4">
                                <h3 class="h6 text-uppercase mb-3">Thông số kỹ thuật chính</h3>
                                <ul class="list-unstyled specs-list">
                                    <li class="d-flex align-items-center mb-3">
                                        <div class="spec-icon me-3">
                                            <i class="bi bi-cpu-fill text-primary"></i>
                                        </div>
                                        <div class="spec-details">
                                            <span class="d-block text-muted small">Bộ vi xử lý</span>
                                            <strong><?php echo htmlspecialchars($product['processor']); ?></strong>
                                        </div>
                                    </li>
                                    <?php if (isset($product['graphics']) && !empty($product['graphics'])): ?>
                                    <li class="d-flex align-items-center mb-3">
                                        <div class="spec-icon me-3">
                                            <i class="bi bi-gpu-card text-primary"></i>
                                        </div>
                                        <div class="spec-details">
                                            <span class="d-block text-muted small">Card đồ họa</span>
                                            <strong><?php echo htmlspecialchars($product['graphics']); ?></strong>
                                        </div>
                                    </li>
                                    <?php endif; ?>
                                    <li class="d-flex align-items-center mb-3">
                                        <div class="spec-icon me-3">
                                            <i class="bi bi-memory text-primary"></i>
                                        </div>
                                        <div class="spec-details">
                                            <span class="d-block text-muted small">RAM</span>
                                            <strong><?php echo htmlspecialchars($product['ram']); ?></strong>
                                        </div>
                                    </li>
                                    <li class="d-flex align-items-center mb-3">
                                        <div class="spec-icon me-3">
                                            <i class="bi bi-device-ssd text-primary"></i>
                                        </div>
                                        <div class="spec-details">
                                            <span class="d-block text-muted small">Lưu trữ</span>
                                            <strong><?php echo htmlspecialchars($product['storage']); ?></strong>
                                        </div>
                                    </li>
                                    <li class="d-flex align-items-center">
                                        <div class="spec-icon me-3">
                                            <i class="bi bi-display text-primary"></i>
                                        </div>
                                        <div class="spec-details">
                                            <span class="d-block text-muted small">Màn hình</span>
                                            <strong><?php echo htmlspecialchars($product['display']); ?></strong>
                                        </div>
                                    </li>
                                </ul>
                            </div>

                            <!-- Add to Cart -->
                            <div class="product-actions mt-auto">
                                <div class="d-flex gap-3">
                                    <button onclick="addToCart(<?php echo $product['id']; ?>)"
                                        class="btn btn-primary btn-lg flex-fill">
                                        <i class="bi bi-cart-plus me-2"></i>Thêm vào giỏ hàng
                                    </button>
                                    <button onclick="buyNow(<?php echo $product['id']; ?>)"
                                        class="btn btn-danger btn-lg flex-fill">
                                        <i class="bi bi-lightning-fill me-2"></i>Mua ngay
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Description and Full Specs -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-header bg-white p-4 border-0">
                            <ul class="nav nav-tabs card-header-tabs" id="productTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="description-tab" data-bs-toggle="tab"
                                        data-bs-target="#description" type="button" role="tab"
                                        aria-controls="description" aria-selected="true">Mô tả sản phẩm</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="specs-tab" data-bs-toggle="tab" data-bs-target="#specs"
                                        type="button" role="tab" aria-controls="specs" aria-selected="false">Thông số kỹ
                                        thuật</button>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body p-4">
                            <div class="tab-content" id="productTabsContent">
                                <!-- Description Tab -->
                                <div class="tab-pane fade show active" id="description" role="tabpanel"
                                    aria-labelledby="description-tab">
                                    <div class="product-description">
                                        <?php if (isset($product['description']) && !empty($product['description'])): ?>
                                        <?php echo $product['description']; ?>
                                        <?php else: ?>
                                        <div class="row mb-4">
                                            <div class="col-md-7">
                                                <h3 class="h5 mb-3"><?php echo htmlspecialchars($product['name']); ?> -
                                                    Hiệu năng ấn tượng, thiết kế hiện đại</h3>
                                                <p>
                                                    <?php echo htmlspecialchars($product['name']); ?> là sự lựa chọn
                                                    hoàn hảo cho những người dùng
                                                    cần một chiếc laptop mạnh mẽ và đáng tin cậy. Được trang bị bộ vi xử
                                                    lý
                                                    <?php echo htmlspecialchars($product['processor']); ?>,
                                                    <?php if (isset($product['graphics']) && !empty($product['graphics'])): ?>
                                                    card đồ họa <?php echo htmlspecialchars($product['graphics']); ?>,
                                                    và
                                                    <?php else: ?>
                                                    và
                                                    <?php endif; ?>
                                                    <?php echo htmlspecialchars($product['ram']); ?> RAM, máy có khả
                                                    năng xử lý mượt mà mọi tác vụ từ
                                                    công việc văn phòng đến các ứng dụng đòi hỏi nhiều tài nguyên hệ
                                                    thống.
                                                </p>
                                                <p>
                                                    Không chỉ mạnh mẽ về hiệu năng,
                                                    <?php echo htmlspecialchars($product['name']); ?> còn gây ấn tượng
                                                    với
                                                    màn hình <?php echo htmlspecialchars($product['display']); ?> cung
                                                    cấp trải nghiệm hình ảnh sắc nét và sống động.
                                                    Ổ cứng <?php echo htmlspecialchars($product['storage']); ?> cung cấp
                                                    không gian lưu trữ rộng rãi và tốc độ truy xuất dữ liệu nhanh chóng.
                                                </p>
                                            </div>
                                            <div class="col-md-5">
                                                <img src="<?php echo $product['img_link']; ?>"
                                                    alt="<?php echo htmlspecialchars($product['name']); ?>"
                                                    class="img-fluid rounded shadow-sm">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12">
                                                <h4 class="h5 mb-3">Tính năng nổi bật</h4>
                                                <ul class="feature-list">
                                                    <li>Hiệu năng vượt trội với
                                                        <?php echo htmlspecialchars($product['processor']); ?></li>
                                                    <?php if (isset($product['graphics']) && !empty($product['graphics'])): ?>
                                                    <li>Trải nghiệm đồ họa mượt mà với
                                                        <?php echo htmlspecialchars($product['graphics']); ?></li>
                                                    <?php endif; ?>
                                                    <li>Đa nhiệm hiệu quả nhờ
                                                        <?php echo htmlspecialchars($product['ram']); ?> RAM</li>
                                                    <li>Lưu trữ nhanh và rộng rãi với
                                                        <?php echo htmlspecialchars($product['storage']); ?></li>
                                                    <li>Màn hình <?php echo htmlspecialchars($product['display']); ?>
                                                        cho chất lượng hiển thị tuyệt vời</li>
                                                    <li>Thiết kế hiện đại, bền bỉ từ thương hiệu
                                                        <?php echo htmlspecialchars($product['brand']); ?></li>
                                                </ul>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Specifications Tab -->
                                <div class="tab-pane fade" id="specs" role="tabpanel" aria-labelledby="specs-tab">
                                    <div class="table-responsive">
                                        <table class="table table-striped specs-table">
                                            <tbody>
                                                <tr>
                                                    <th class="w-25">Thương hiệu</th>
                                                    <td><?php echo htmlspecialchars($product['brand']); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Model</th>
                                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>CPU</th>
                                                    <td><?php echo htmlspecialchars($product['processor']); ?></td>
                                                </tr>
                                                <?php if (isset($product['graphics']) && !empty($product['graphics'])): ?>
                                                <tr>
                                                    <th>Card đồ họa</th>
                                                    <td><?php echo htmlspecialchars($product['graphics']); ?></td>
                                                </tr>
                                                <?php endif; ?>
                                                <tr>
                                                    <th>RAM</th>
                                                    <td><?php echo htmlspecialchars($product['ram']); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Bộ nhớ</th>
                                                    <td><?php echo htmlspecialchars($product['storage']); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Màn hình</th>
                                                    <td><?php echo htmlspecialchars($product['display']); ?></td>
                                                </tr>
                                                <?php if (isset($product['os']) && !empty($product['os'])): ?>
                                                <tr>
                                                    <th>Hệ điều hành</th>
                                                    <td><?php echo htmlspecialchars($product['os']); ?></td>
                                                </tr>
                                                <?php endif; ?>
                                                <?php if (isset($product['weight']) && !empty($product['weight'])): ?>
                                                <tr>
                                                    <th>Cân nặng</th>
                                                    <td><?php echo htmlspecialchars($product['weight']); ?></td>
                                                </tr>
                                                <?php endif; ?>
                                                <?php if (isset($product['dimensions']) && !empty($product['dimensions'])): ?>
                                                <tr>
                                                    <th>Kích thước</th>
                                                    <td><?php echo htmlspecialchars($product['dimensions']); ?></td>
                                                </tr>
                                                <?php endif; ?>
                                                <?php if (isset($product['warranty']) && !empty($product['warranty'])): ?>
                                                <tr>
                                                    <th>Bảo hành</th>
                                                    <td><?php echo htmlspecialchars($product['warranty']); ?></td>
                                                </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar - Related Products -->
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-primary text-white p-3">
                    <h3 class="h6 mb-0"><i class="bi bi-lightning-fill me-2"></i>Sản phẩm tương tự</h3>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($relatedProducts)): ?>
                    <div class="related-products">
                        <?php foreach ($relatedProducts as $relatedProduct): ?>
                        <?php if ($relatedProduct['id'] != $product_id): ?>
                        <div class="related-product p-3 border-bottom">
                            <div class="d-flex">
                                <a href="product-detail.php?id=<?php echo $relatedProduct['id']; ?>"
                                    class="related-product-img me-3 flex-shrink-0">
                                    <img src="<?php echo $relatedProduct['img_link']; ?>"
                                        alt="<?php echo htmlspecialchars($relatedProduct['name']); ?>"
                                        class="img-fluid rounded shadow-sm" width="80">
                                </a>
                                <div class="related-product-info" style="min-width: 0; width: 100%;">
                                    <a href="product-detail.php?id=<?php echo $relatedProduct['id']; ?>"
                                        class="product-name text-decoration-none">
                                        <h4 class="h6 mb-1 text-truncate">
                                            <?php echo htmlspecialchars($relatedProduct['name']); ?></h4>
                                    </a>
                                    <div class="specs my-2">
                                        <div class="small text-muted text-truncate">
                                            <i class="bi bi-cpu-fill text-primary me-1"></i>
                                            <?php echo htmlspecialchars($relatedProduct['processor']); ?>
                                        </div>
                                        <div class="small text-muted text-truncate">
                                            <i class="bi bi-memory text-primary me-1"></i>
                                            <?php echo htmlspecialchars($relatedProduct['ram']); ?>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="product-price text-danger fw-bold">
                                            <?php
                                                        // Remove the ₫ symbol and add it separately
                                                        $price_without_symbol = str_replace(' ₫', '', $relatedProduct['formatted_price']);
                                                        echo $price_without_symbol;
                                                        ?> <span class="currency-symbol">₫</span>
                                        </div>
                                        <button onclick="addToCart(<?php echo $relatedProduct['id']; ?>)"
                                            class="btn btn-sm btn-outline-primary rounded-circle flex-shrink-0">
                                            <i class="bi bi-cart-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="p-4 text-center text-muted">
                        <p>Không có sản phẩm tương tự</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Customer Support Card -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-success text-white p-3">
                    <h3 class="h6 mb-0"><i class="bi bi-headset me-2"></i>Hỗ trợ khách hàng</h3>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex align-items-center mb-3">
                            <i class="bi bi-telephone-fill text-success me-3 fs-4"></i>
                            <div>
                                <small class="d-block text-muted">Tổng đài hỗ trợ</small>
                                <a href="tel:1800123456" class="text-decoration-none fw-bold">113</a>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
.product-detail-img {
    max-height: 400px;
    object-fit: contain;
    width: 100%;
}

.brand-logo-small {
    height: 20px;
    width: auto;
}

.specs-list .spec-icon {
    width: 40px;
    height: 40px;
    background-color: rgba(13, 110, 253, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.specs-list .spec-icon i {
    font-size: 1.2rem;
}

.feature-list {
    padding-left: 1.5rem;
    margin-bottom: 0;
}

.feature-list li {
    margin-bottom: 0.5rem;
}

.specs-table th {
    background-color: #f8f9fa;
}

.nav-tabs .nav-link {
    color: #6c757d;
    font-weight: 500;
    padding: 0.75rem 1.25rem;
    border: none;
    border-radius: 0;
}

.nav-tabs .nav-link.active {
    color: #0d6efd;
    border-bottom: 2px solid #0d6efd;
    background: transparent;
}

.related-product:last-child {
    border-bottom: none !important;
}

.product-image-container {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    padding: 1.5rem;
}

@media (max-width: 768px) {
    .product-detail-img {
        max-height: 300px;
    }
}

/* Enhanced Related Products Styles */
.related-product {
    transition: all 0.2s ease;
}

.related-product:hover {
    background-color: rgba(13, 110, 253, 0.03);
}

.related-product .product-name {
    color: #333;
    transition: color 0.2s ease;
}

.related-product .product-name:hover {
    color: #0d6efd;
}

.related-product img {
    transition: transform 0.3s ease;
}

.related-product:hover img {
    transform: scale(1.05);
}

.related-product-img {
    display: block;
    overflow: hidden;
    border-radius: 0.375rem;
}

.specs .text-muted {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 100%;
}
</style>

<!-- Script for showing toast messages -->
<script>
// Cart functions
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
                if (data.error === 'Not logged in') {
                    window.location.href = 'login.php';
                    return;
                }
                showToast(data.error, 'danger');
                return;
            }

            showToast(data.success || 'Sản phẩm đã được thêm vào giỏ hàng');
            if (data.cart_count) {
                updateCartCount(data.cart_count);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Đã xảy ra lỗi khi thêm vào giỏ hàng', 'danger');
        });
}

function buyNow(productId) {
    addToCart(productId);
    setTimeout(() => {
        window.location.href = 'cart.php';
    }, 500);
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
</script>

<?php include 'components/footer.php'; ?>