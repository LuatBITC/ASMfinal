<?php
require_once 'database.php';
include 'components/header.php';

// Get all brands
$brands = getAllBrands();
?>

<main>
    <!-- Hero Section with Carousel -->
    <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
        <!-- Carousel Indicators -->
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2"></button>
        </div>

        <!-- Carousel Items -->
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="https://nhatkhanhtech.com/wp-content/uploads/2019/10/Banner-gaming_laptops.jpg"
                    class="d-block w-100" alt="Gaming Laptops">
            </div>
            <div class="carousel-item">
                <img src="https://img.freepik.com/premium-psd/black-friday-sale-laptops-gadgets-banner-template-3d-render_444361-44.jpg?semt=ais_hybrid"
                    class="d-block w-100" alt="Business Laptops">
            </div>
            <div class="carousel-item">
                <img src="https://cdn.tgdd.vn/Files/2021/12/20/1405435/laptopgamingdohoah22_1280x720-800-resize.jpg"
                    class="d-block w-100" alt="Student Laptops">
            </div>
        </div>

        <!-- Carousel Controls -->
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>

    <!-- Featured Categories -->
    <section id="categories">
        <h2 class="section-title">Shop by Category</h2>
        <div class="category-grid">
            <a href="products.php?category=gaming" class="category-card">
                <div class="category-icon">🎮</div>
                <h3>Gaming Laptops</h3>
                <p>High-performance machines for ultimate gaming experience</p>
            </a>
            <a href="products.php?category=business" class="category-card">
                <div class="category-icon">💼</div>
                <h3>Business Laptops</h3>
                <p>Reliable devices for professional use</p>
            </a>
            <a href="products.php?category=student" class="category-card">
                <div class="category-icon">📚</div>
                <h3>Student Laptops</h3>
                <p>Affordable options for learning</p>
            </a>
            <a href="products.php?category=premium" class="category-card">
                <div class="category-icon">✨</div>
                <h3>Premium Laptops</h3>
                <p>Luxury devices with cutting-edge technology</p>
            </a>
        </div>
    </section>

    <!-- Products by Brand -->
    <section id="products" class="py-5">
        <div class="container">
            <h2 class="section-title text-center mb-5">Featured Products by Brand</h2>
            <?php foreach ($brands as $brand):
                $brandProducts = getLimitedProductsByBrand($brand, 3);
                if (!empty($brandProducts)):
            ?>
                    <div class="brand-section bg-white rounded-4 shadow-sm p-4 mb-5">
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
                            <div class="brand-info">
                                <h3 class="h4 mb-1"><?php echo htmlspecialchars($brand); ?></h3>
                                <span class="text-muted small"><?php echo getTotalProductsByBrand($brand); ?> products</span>
                            </div>
                            <a href="products.php?brand=<?php echo urlencode($brand); ?>"
                                class="btn btn-outline-primary rounded-pill px-4">
                                View All <i class="bi bi-arrow-right ms-2"></i>
                            </a>
                        </div>
                        <div class="row g-4">
                            <?php foreach ($brandProducts as $product): ?>
                                <div class="col-md-6 col-lg-4">
                                    <div class="card h-100 border-0 shadow-sm product-card">
                                        <div class="position-relative">
                                            <a href="product-detail.php?id=<?php echo $product['id']; ?>">
                                                <img src="<?php echo $product['img_link']; ?>" class="card-img-top p-3"
                                                    alt="<?php echo $product['name']; ?>">
                                            </a>
                                            <div class="product-overlay">
                                                <button onclick="showDetails(<?php echo $product['id']; ?>)"
                                                    class="btn btn-light rounded-pill">
                                                    <i class="bi bi-eye me-2"></i>Quick View
                                                </button>
                                            </div>
                                        </div>
                                        <div class="card-body d-flex flex-column">
                                            <a href="product-detail.php?id=<?php echo $product['id']; ?>"
                                                class="text-decoration-none">
                                                <h4 class="card-title h6 mb-3"><?php echo $product['name']; ?></h4>
                                            </a>
                                            <div class="specs mb-3">
                                                <div class="d-flex align-items-center mb-2">
                                                    <i class="bi bi-cpu-fill text-primary me-2"></i>
                                                    <span class="small text-muted"><?php echo $product['processor']; ?></span>
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-memory text-primary me-2"></i>
                                                    <span class="small text-muted"><?php echo $product['ram']; ?></span>
                                                </div>
                                            </div>
                                            <div class="text-center mb-3">
                                                <span
                                                    class="h5 text-danger fw-bold"><?php echo $product['formatted_price']; ?></span>
                                            </div>
                                            <div class="mt-auto d-grid gap-2">
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
                            <?php endforeach; ?>
                        </div>
                    </div>
            <?php
                endif;
            endforeach;
            ?>
        </div>
    </section>

    <!-- Why Choose Us -->
    <section id="features">
        <h2 class="section-title">Why Choose Us</h2>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">🚚</div>
                <h3>Free Shipping</h3>
                <p>On orders over $999</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">💯</div>
                <h3>Genuine Products</h3>
                <p>100% authentic guarantee</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🔒</div>
                <h3>Secure Payment</h3>
                <p>Multiple payment options</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🎮</div>
                <h3>Expert Support</h3>
                <p>24/7 dedicated support</p>
            </div>
        </div>
    </section>
</main>

<!-- Product Popup -->
<div id="product-popup" class="popup">
    <div class="popup-content">
        <span class="close-popup" onclick="closePopup()">&times;</span>
        <div class="popup-grid">
            <div class="popup-image">
                <img id="popup-image" src="" alt="Product Image">
            </div>
            <div class="popup-details">
                <div id="popup-info"></div>
            </div>
        </div>
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

    // Cart and Buy functions
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

                showToast(data.success || 'Product added to cart');
                if (data.cart_count) {
                    updateCartCount(data.cart_count);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error adding to cart', 'danger');
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

    function showDetails(productId) {
        window.location.href = 'product-detail.php?id=' + productId;
    }

    // Chatbot functions
    function toggleChat() {
        const chatbot = document.getElementById('chatbot');
        chatbot.classList.toggle('minimized');
    }

    function handleKeyPress(event) {
        if (event.key === 'Enter') {
            sendMessage();
        }
    }

    function sendMessage() {
        const input = document.getElementById('user-message');
        const message = input.value.trim();

        if (message === '') return;

        // Add user message to chat
        addMessage(message, 'user');
        input.value = '';

        // Send to server
        fetch('chatbot.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    message: message
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error('Error:', data.error);
                    return;
                }
                addMessage(data.message, 'bot');
            })
            .catch(error => {
                console.error('Error:', error);
                addMessage('Sorry, I encountered an error. Please try again.', 'bot');
            });
    }

    function addMessage(message, type) {
        const messagesDiv = document.getElementById('chat-messages');
        const time = new Date().toLocaleTimeString([], {
            hour: '2-digit',
            minute: '2-digit'
        });

        const messageHTML = `
        <div class="message ${type}">
            <div class="message-content">${message}</div>
            <div class="message-time">${time}</div>
        </div>
    `;

        messagesDiv.insertAdjacentHTML('beforeend', messageHTML);
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }

    function appendMessage(sender, message) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `${sender}-message`;
        // Allow HTML content for links
        messageDiv.innerHTML = message;
        document.getElementById('chatbox-messages').appendChild(messageDiv);
        document.getElementById('chatbox-messages').scrollTop = document.getElementById('chatbox-messages').scrollHeight;
    }
</script>

<style>
    /* Toast container */
    #toast-container {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 1050;
    }

    .toast {
        min-width: 200px;
    }

    /* Product card styles */
    .product-card {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }

    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .product-overlay {
        position: absolute;
        top: 10px;
        right: 10px;
        opacity: 0;
        transition: opacity 0.2s ease-in-out;
    }

    .product-card:hover .product-overlay {
        opacity: 1;
    }

    .specs {
        font-size: 0.875rem;
    }

    .specs i {
        width: 20px;
        text-align: center;
        margin-right: 8px;
    }

    /* Button styles */
    .btn-outline-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 5px rgba(13, 110, 253, 0.2);
    }

    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 5px rgba(13, 110, 253, 0.3);
    }

    /* Chatbot styles */
    .chatbot {
        position: fixed;
        bottom: 20px;
        right: 20px;
        width: 350px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
        z-index: 1000;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: all 0.3s ease;
    }

    .chat-header {
        background: #0d6efd;
        color: white;
        padding: 15px 20px;
        display: flex;
        align-items: center;
        cursor: pointer;
    }

    .chat-header i {
        margin-right: 10px;
        font-size: 1.2em;
    }

    .chat-header .minimize-btn {
        margin-left: auto;
        background: none;
        border: none;
        color: white;
        cursor: pointer;
    }

    .chat-body {
        height: 400px;
        display: flex;
        flex-direction: column;
    }

    .messages {
        flex-grow: 1;
        padding: 20px;
        overflow-y: auto;
    }

    .message {
        margin-bottom: 15px;
        display: flex;
        flex-direction: column;
    }

    .message.user {
        align-items: flex-end;
    }

    .message-content {
        max-width: 80%;
        padding: 10px 15px;
        border-radius: 15px;
        background: #f0f2f5;
        margin-bottom: 5px;
    }

    .message.user .message-content {
        background: #0d6efd;
        color: white;
    }

    .message-time {
        font-size: 0.75em;
        color: #666;
    }

    .chat-input {
        padding: 15px;
        border-top: 1px solid #eee;
        display: flex;
        gap: 10px;
    }

    .chat-input input {
        flex-grow: 1;
        padding: 8px 15px;
        border: 1px solid #ddd;
        border-radius: 20px;
        outline: none;
    }

    .chat-input button {
        background: #0d6efd;
        color: white;
        border: none;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background-color 0.2s;
    }

    .chat-input button:hover {
        background: #0b5ed7;
    }

    .chatbot.minimized .chat-body {
        display: none;
    }
</style>

<?php include 'components/footer.php'; ?>