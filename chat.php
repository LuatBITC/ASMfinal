<?php
// Check if session is not already active before starting it
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat with Us - Laptop Store</title>
    <link rel="shortcut icon" type="image/x-icon" href="./img/logo.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="styles.css">
    <style>
    :root {
        --primary-color: #0d6efd;
        --primary-dark: #0b5ed7;
        --light-bg: #f8f9fa;
        --border-color: #dee2e6;
        --text-muted: #6c757d;
    }

    body {
        background-color: #f5f7fa;
        height: 100vh;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .chat-container {
        flex: 1;
        display: flex;
        overflow: hidden;
        background-color: white;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
        border-radius: 12px;
        margin: 20px;
    }

    .chat-area {
        flex: 1;
        display: flex;
        flex-direction: column;
        background-color: white;
    }

    .chat-header {
        padding: 20px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
    }

    .chat-header h2 {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 600;
    }

    .chat-header .status {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background-color: #4CAF50;
        margin-left: 10px;
    }

    .chat-messages {
        flex: 1;
        padding: 20px;
        overflow-y: auto;
        background-color: var(--light-bg);
        display: flex;
        flex-direction: column;
    }

    .chat-input-container {
        padding: 20px;
        border-top: 1px solid var(--border-color);
        display: flex;
        align-items: center;
    }

    .chat-input {
        flex: 1;
        padding: 15px;
        border: 1px solid var(--border-color);
        border-radius: 24px;
        outline: none;
        transition: border-color 0.3s;
        margin-right: 10px;
    }

    .chat-input:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }

    .send-button {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background-color: var(--primary-color);
        color: white;
        border: none;
        display: flex;
        justify-content: center;
        align-items: center;
        cursor: pointer;
        transition: all 0.2s;
    }

    .send-button:hover {
        background-color: var(--primary-dark);
        transform: scale(1.05);
    }

    .send-button i {
        font-size: 1.2rem;
    }

    .message {
        margin-bottom: 20px;
        display: flex;
        flex-direction: column;
        max-width: 75%;
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .message.user {
        align-self: flex-end;
    }

    .message.bot {
        align-self: flex-start;
    }

    .message-content {
        padding: 15px;
        border-radius: 18px;
        margin-bottom: 5px;
        white-space: pre-line;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    }

    .message.user .message-content {
        background-color: var(--primary-color);
        color: white;
        border-top-right-radius: 4px;
    }

    .message.bot .message-content {
        background-color: white;
        border-top-left-radius: 4px;
        border: 1px solid var(--border-color);
    }

    .message-time {
        font-size: 0.75rem;
        color: var(--text-muted);
        margin-top: 5px;
    }

    .message.user .message-time {
        align-self: flex-end;
    }

    .message.bot .message-time {
        align-self: flex-start;
    }

    .back-button {
        margin-right: 15px;
        font-size: 1.2rem;
        color: var(--primary-color);
        cursor: pointer;
    }

    @media (max-width: 768px) {
        .chat-container {
            margin: 0;
            border-radius: 0;
        }

        .message {
            max-width: 85%;
        }
    }

    .typing-indicator {
        display: inline-flex;
        align-items: center;
        margin: 10px 0;
    }

    .typing-indicator span {
        height: 10px;
        width: 10px;
        float: left;
        margin: 0 1px;
        background-color: #9E9EA1;
        display: block;
        border-radius: 50%;
        opacity: 0.4;
    }

    .typing-indicator span:nth-of-type(1) {
        animation: 1s blink infinite 0.3333s;
    }

    .typing-indicator span:nth-of-type(2) {
        animation: 1s blink infinite 0.6666s;
    }

    .typing-indicator span:nth-of-type(3) {
        animation: 1s blink infinite 0.9999s;
    }

    @keyframes blink {
        50% {
            opacity: 1;
        }
    }

    a {
        color: var(--primary-color);
        text-decoration: none;
    }

    a:hover {
        text-decoration: underline;
    }
    </style>
</head>

<body>
    <header>
        <div class="logo">
            <a href="./index.php"><img src="./img/logo.png" alt="Laptop Store Logo"></a>
            <h1>Laptop Store</h1>
        </div>
        <nav>
            <a href="index.php">Home</a>
            <a href="products.php">Products</a>
            <a href="contact.php">Contact</a>
            <a href="cart.php">Cart</a>
            <a href="checkout.php">Check Out</a>
            <?php if (isset($_SESSION['user'])): ?>
            <div class="dropdown d-inline-block">
                <a href="#" class="user-menu dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle"></i>
                    Xin chào, <?php echo htmlspecialchars($_SESSION['user']['username']); ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person"></i> Profile</a></li>
                    <li><a class="dropdown-item" href="orders.php"><i class="bi bi-bag"></i> My Orders</a></li>
                    <?php if (isset($_SESSION['user']['is_admin']) && $_SESSION['user']['is_admin']): ?>
                    <li><a class="dropdown-item" href="admin/index.php"><i class="bi bi-speedometer"></i> Admin
                            Panel</a></li>
                    <?php endif; ?>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                </ul>
            </div>
            <?php else: ?>
            <a href="login.php">
                <i class="bi bi-box-arrow-in-right"></i>
                Đăng nhập
            </a>
            <a href="register.php">
                <i class="bi bi-person-plus"></i>
                Đăng ký
            </a>
            <?php endif; ?>
        </nav>
    </header>

    <main class="chat-container">
        <!-- Main chat area -->
        <section class="chat-area">
            <div class="chat-header">
                <a href="index.php" class="back-button d-md-none">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h2>Laptop Store Assistant</h2>
                <div class="status"></div>
            </div>
            <div class="chat-messages" id="chat-messages">
                <div class="message bot">
                    <div class="message-content">
                        <strong>Welcome to Laptop Store Chat!</strong> 👋

                        I'm your virtual assistant, here to help you find the perfect laptop and answer any questions
                        about our products and services.

                        You can ask me about:
                        • Laptop recommendations based on your needs
                        • Current promotions and discounts
                        • Technical specifications
                        • Shipping and delivery options
                        • Warranty and return policies

                        How can I assist you today?
                    </div>
                    <div class="message-time" id="initial-timestamp">Just now</div>
                </div>
                <!-- Messages will be added here dynamically -->
            </div>
            <div class="chat-input-container">
                <input type="text" id="user-message" class="chat-input" placeholder="Type your message here..."
                    onkeypress="handleKeyPress(event)">
                <button class="send-button" onclick="sendMessage()">
                    <i class="bi bi-send-fill"></i>
                </button>
            </div>
        </section>
    </main>

    <!-- Bootstrap JavaScript Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Set initial timestamp
        const initialTimestamp = document.getElementById('initial-timestamp');
        if (initialTimestamp) {
            initialTimestamp.textContent = new Date().toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    });

    function handleKeyPress(event) {
        if (event.key === 'Enter') {
            sendMessage();
        }
    }

    function sendMessage() {
        const input = document.getElementById('user-message');
        const message = input.value.trim();

        if (message === '') return;

        addMessage(message, 'user');
        input.value = '';

        // Show typing indicator
        const messagesDiv = document.getElementById('chat-messages');
        const typingIndicator = document.createElement('div');
        typingIndicator.className = 'message bot';
        typingIndicator.innerHTML = `
                <div class="typing-indicator">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            `;
        messagesDiv.appendChild(typingIndicator);
        messagesDiv.scrollTop = messagesDiv.scrollHeight;

        // Send to backend
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
                // Remove typing indicator
                messagesDiv.removeChild(typingIndicator);

                if (data.error) {
                    console.error('Error:', data.error);
                    addMessage('Sorry, I encountered an error. Please try again.', 'bot');
                    return;
                }
                addMessage(data.message, 'bot');
            })
            .catch(error => {
                // Remove typing indicator
                messagesDiv.removeChild(typingIndicator);

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

        // For bot messages, allow HTML to render links
        // For user messages, escape HTML for security
        const messageContent = type === 'bot' ? message : escapeHtml(message);

        const messageHTML = `
                <div class="message ${type}">
                    <div class="message-content">${messageContent}</div>
                    <div class="message-time">${time}</div>
                </div>
            `;

        messagesDiv.insertAdjacentHTML('beforeend', messageHTML);
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }

    // Helper function to escape HTML for user input
    function escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
    </script>
</body>

</html>