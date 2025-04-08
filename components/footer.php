<footer>
        <p>&copy; 2025 Laptop Store. All rights reserved.</p>
    </footer>

    <!-- Chatbot -->
    <div id="chatbot" class="chatbot minimized">
        <div class="chat-header" onclick="toggleChat()">
            <i class="bi bi-chat-dots-fill"></i>
            <span>Chat with Us</span>
            <!-- <button class="minimize-btn" onclick="event.stopPropagation()">
                <i class="bi bi-dash-lg"></i>
            </button> -->
        </div>
        <div class="chat-body">
            <div class="messages" id="chat-messages">
                <div class="message bot">
                    <div class="message-content">
                        Hi! How can I help you today? You can ask me about:
                        • Products and prices
                        • Shipping and delivery
                        • Payment methods
                        • Warranty and returns
                        • Store locations
                        • Technical support
                    </div>
                    <div class="message-time">Now</div>
                </div>
            </div>
            <div class="chat-input">
                <input type="text" id="user-message" placeholder="Type your message..."
                    onkeypress="handleKeyPress(event)">
                <button onclick="sendMessage()">
                    <i class="bi bi-send-fill"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toast-container" class="position-fixed bottom-0 end-0 p-3" style="z-index: 1050;"></div>

    <!-- Chatbot Styles -->
    <style>
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
    user-select: none;
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
    padding: 5px;
    border-radius: 50%;
    transition: background-color 0.2s;
}

.chat-header .minimize-btn:hover {
    background: rgba(255, 255, 255, 0.1);
}

.chat-body {
    height: 400px;
    display: flex;
    flex-direction: column;
    transition: height 0.3s ease;
}

.messages {
    flex-grow: 1;
    padding: 20px;
    overflow-y: auto;
    background: #f8f9fa;
}

.message {
    margin-bottom: 15px;
    display: flex;
    flex-direction: column;
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
    align-items: flex-end;
}

.message-content {
    max-width: 80%;
    padding: 12px 16px;
    border-radius: 15px;
    background: white;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    margin-bottom: 5px;
    white-space: pre-line;
}

.message.user .message-content {
    background: #0d6efd;
    color: white;
}

.message-time {
    font-size: 0.75em;
    color: #666;
    margin-top: 2px;
}

.chat-input {
    padding: 15px;
    border-top: 1px solid #eee;
    display: flex;
    gap: 10px;
    background: white;
}

.chat-input input {
    flex-grow: 1;
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 20px;
    outline: none;
    transition: border-color 0.2s;
}

.chat-input input:focus {
    border-color: #0d6efd;
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
    transition: all 0.2s;
}

.chat-input button:hover {
    background: #0b5ed7;
    transform: scale(1.05);
}

.chatbot.minimized .chat-body {
    display: none;
}

@media (max-width: 576px) {
    .chatbot {
        width: calc(100% - 40px);
        bottom: 10px;
        right: 20px;
    }
}
    </style>

    <!-- Chatbot Scripts -->
    <script>
document.addEventListener('DOMContentLoaded', function() {
    // Set initial timestamp
    const initialTimestamp = document.querySelector('.message-time');
    if (initialTimestamp) {
        initialTimestamp.textContent = new Date().toLocaleTimeString([], {
            hour: '2-digit',
            minute: '2-digit'
        });
    }
});

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

    addMessage(message, 'user');
    input.value = '';

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
    <div class="gtranslate_wrapper"></div>
    <script>
window.gtranslateSettings = {
    "default_language": "vi",
    "detect_browser_language": true,
    "languages": ["vi", "en"],
    "wrapper_selector": ".gtranslate_wrapper"
}
    </script>
    <script src="https://cdn.gtranslate.net/widgets/latest/float.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    </body>

    </html>