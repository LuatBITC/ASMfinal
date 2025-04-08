// Product data handling
let products = [];
let cart = [];

// Fetch products from database
async function fetchProducts() {
  try {
    const response = await fetch('get_products.php');
    products = await response.json();
    displayProducts();
  } catch (error) {
    console.error('Error fetching products:', error);
  }
}

// Display products
function displayProducts() {
  const productList = document.querySelector('.product-list');
  productList.innerHTML = products.map(product => `
        <div class="product" data-id="${product.id}">
            <img src="${product.img_link}" alt="${product.name}">
            <h3>${product.name}</h3>
            <p>Price: $${product.price}</p>
            <button onclick="addToCart(${product.id})">Add to Cart</button>
            <button onclick="buyNow(${product.id})">Buy Now</button>
            <button onclick="showDetails(${product.id})">Details</button>
        </div>
    `).join('');
}

// Cart functionality
function addToCart(productId) {
  const product = products.find(p => p.id === productId);
  if (product) {
    cart.push(product);
    updateCart();
    showNotification('Product added to cart!');
  }
}

function updateCart() {
  localStorage.setItem('cart', JSON.stringify(cart));
}

function buyNow(productId) {
  const product = products.find(p => p.id === productId);
  if (product) {
    cart = [product];
    updateCart();
    window.location.href = 'checkout.html';
  }
}

// Product details popup
function showDetails(productId) {
  const product = products.find(p => p.id === productId);
  if (product) {
    const popup = document.getElementById('product-popup');
    const popupContent = document.querySelector('.popup-content');

    popupContent.innerHTML = `
            <span class="close-popup" onclick="closePopup()">&times;</span>
            <img src="${product.img_link}" alt="${product.name}">
            <div class="product-info">
                <h2>${product.name}</h2>
                <p><strong>Price:</strong> $${product.price}</p>
                <p><strong>Processor:</strong> ${product.processor}</p>
                <p><strong>RAM:</strong> ${product.ram}</p>
                <p><strong>Storage:</strong> ${product.storage}</p>
                <p><strong>Display:</strong> ${product.display}"</p>
                <p><strong>Rating:</strong> ${product.rating} (${product.no_of_ratings} ratings)</p>
                <button onclick="addToCart(${product.id})">Add to Cart</button>
                <button onclick="buyNow(${product.id})">Buy Now</button>
            </div>
        `;

    popup.style.display = 'block';
  }
}

function closePopup() {
  document.getElementById('product-popup').style.display = 'none';
}

// Chatbot functionality
const chatbox = document.getElementById('chatbox');
const chatMessages = document.getElementById('chatbox-messages');
const userInput = document.getElementById('user-input');

function toggleChatbox() {
  chatbox.classList.toggle('active');
}

async function sendMessage() {
  const message = userInput.value.trim();
  if (message) {
    // Add user message to chat
    appendMessage('user', message);
    userInput.value = '';

    try {
      // Send message to chatbot API
      const response = await fetch('chatbot.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ message })
      });

      const data = await response.json();

      // Add bot response to chat
      appendMessage('bot', data.response);
    } catch (error) {
      console.error('Error sending message:', error);
      appendMessage('bot', 'Sorry, I encountered an error. Please try again.');
    }
  }
}

function appendMessage(sender, message) {
  const messageDiv = document.createElement('div');
  messageDiv.className = `message ${sender}-message`;
  messageDiv.textContent = message;
  chatMessages.appendChild(messageDiv);
  chatMessages.scrollTop = chatMessages.scrollHeight;
}

// Notifications
function showNotification(message) {
  const notification = document.createElement('div');
  notification.className = 'notification';
  notification.textContent = message;
  document.body.appendChild(notification);

  setTimeout(() => {
    notification.remove();
  }, 3000);
}

// Event listeners
document.addEventListener('DOMContentLoaded', () => {
  fetchProducts();

  // Load cart from localStorage
  const savedCart = localStorage.getItem('cart');
  if (savedCart) {
    cart = JSON.parse(savedCart);
  }

  // Enter key in chatbox
  userInput.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') {
      sendMessage();
    }
  });
});

// Close popup when clicking outside
window.onclick = (event) => {
  const popup = document.getElementById('product-popup');
  if (event.target === popup) {
    popup.style.display = 'none';
  }
};

function sendMessage() {
  const userInput = document.getElementById('user-input').value.trim();
  const messages = document.getElementById('chatbox-messages');

  if (userInput !== '') {
    const userMessage = document.createElement('div');
    userMessage.textContent = 'You: ' + userInput;
    messages.appendChild(userMessage);

    const aiResponse = document.createElement('div');
    let response;

    if (userInput.toLowerCase().includes('laptops')) {
      response = 'We have a wide range of laptops: Laptop 1, Laptop 2, Laptop 3...';
    } else if (userInput.toLowerCase().includes('price')) {
      response = 'Our laptops range from $800 to $1500.';
    } else if (userInput.toLowerCase().includes('help')) {
      response = 'I am here to assist you. Please ask any question!';
    } else {
      response = 'Sorry, I didn't understand that.Could you rephrase ? ';
      }

    aiResponse.textContent = 'AI: ' + response;
    messages.appendChild(aiResponse);

    saveChatHistory('You: ' + userInput, 'AI: ' + response);

    document.getElementById('user-input').value = '';
    messages.scrollTop = messages.scrollHeight;
  }
}

function saveChatHistory(userMessage, aiMessage) {
  let chatHistory = JSON.parse(localStorage.getItem('chatHistory')) || [];
  chatHistory.push({ userMessage, aiMessage });
  localStorage.setItem('chatHistory', JSON.stringify(chatHistory));
}

function loadChatHistory() {
  const messages = document.getElementById('chatbox-messages');
  const chatHistory = JSON.parse(localStorage.getItem('chatHistory')) || [];
  chatHistory.forEach(chat => {
    const userMessage = document.createElement('div');
    userMessage.textContent = chat.userMessage;
    messages.appendChild(userMessage);

    const aiMessage = document.createElement('div');
    aiMessage.textContent = chat.aiMessage;
    messages.appendChild(aiMessage);
  });
}

function toggleChatbox() {
  const chatbox = document.getElementById('chatbox');
  chatbox.classList.toggle('show');
}

function addToCart(product) {
  alert(product + ' has been added to your cart!');
}

function buyNow(product) {
  alert('Proceeding to buy ' + product);
}


const products = [
  {
    id: 1,
    name: "Lenovo Intel Core i5 11th Gen",
    price: "62.990$",
    description: "Lenovo IdeaPad 5 14ITL05 is a thin and light laptop with a modern design in Graphite Gray. With an Intel Core i5-1135G7 processor, 16GB RAM and a 512GB SSD, the device meets the needs of office work, study and entertainment. The 14-inch Full HD screen with four-sided thin bezels provides a sharp visual experience. The backlit keyboard supports working in low-light environments. The device comes pre-installed with Windows 11 Home and comes with Microsoft Office.",
    specs: "CPU: Intel Core i5-1135G7 <br> GPU: Intel Integrated Graphics, <br> RAM: 16GB DDR4, <br> SSD: 512GB SSD PCIe 4.0 NVMe, <br> Screen: 14 inch Full HD (1920 x 1080), <br> Operating system: Windows 11 Home, <br> Connection ports: 2 x USB-A, 1 x USB-C (supports Thunderbolt 4), HDMI, SD card reader, <br> Wireless connection: Wi-Fi 6, Bluetooth 5.1, <br> Weight: About 1.39 kg, <br> Keyboard: Backlit",
    warranty: "The product is warranted for 1 year, including 1 year Premium Care and 1 year accidental damage protection (ADP).",
    image: "./img/anh1.webp"
  },
  {
    id: 2,
    name: "15s-fq5007TU Laptop",
    price: "39.900₹",
    description: "Lenovo V15 G2 ITL is a laptop aimed at business users and students, with an elegant design and stable performance. Equipped with an Intel Core i3-1115G4 processor, 4GB RAM and a 256GB SSD, the device meets the basic needs of office work, study and entertainment. The 15.6-inch Full HD screen with anti-glare technology helps protect the eyes and provides a clear visual experience. The device weighs about 1.7 kg, convenient for moving.",
    specs: "+ CPU: Intel Core i3-1115G4, <br> RAM: 4GB DDR4 3200MHz (upgradable up to 12GB), <br> Hard drive: 256GB SSD M.2 2242 PCIe 3.0x4 NVMe, <br> Screen: 15.6 inch FHD (1920x1080), TN panel, 250 nits brightness, anti-glare, 45% NTSC, <br> Graphics card: Intel UHD Graphics, <br> Webcam: 720p with privacy cover, <br> Battery: 2 cell, 38Wh, <br> Dimensions: 359.2 x 235.8 x 19.9 mm, <br> Weight: 1.7 kg, <br> Operating system: FreeDOS (Windows not included)",
    warranty: "12 months",
    image: "./img/anh2.webp"
  },
  {
    id: 3,
    name: "ASUS TUF Gaming F15 Core i5 10th Gen",
    price: "49.990$",
    description: "ASUS TUF Gaming F15 FX506LH is a powerful gaming laptop, designed to meet the needs of gaming and multitasking. With an Intel Core i5-10300H processor, 8GB RAM and 512GB SSD, the machine provides stable performance for heavy tasks. The 15.6-inch Full HD screen with a 144Hz refresh rate provides smooth image display, suitable for gamers. The NVIDIA GeForce GTX 1650 4GB GDDR6 discrete graphics card ensures good graphics processing. The keyboard has RGB backlighting, highlighting the design and supporting gaming in low-light environments.",
    specs: "+ CPU: Intel Core i5-10300H (2.5GHz, up to 4.5GHz, 4 cores, 8 threads), <br> RAM: 8GB DDR4 2933MHz (2 slots, supports up to 32GB), <br> Hard drive: 512GB SSD M.2 NVMe PCIe 3.0 (supports upgrade with 2 M.2 slots and 1 HDD slot), <br> Screen: 15.6 inch Full HD (1920 x 1080), IPS panel, 144Hz refresh rate, anti-glare, 45% NTSC, 62.5% sRGB, <br> Graphics card: NVIDIA GeForce GTX 1650 4GB GDDR6, <br> Battery: 3 cell, 48Wh, + Weight: 2.3 kg, <br> Operating system: Windows 11 Home",
    warranty: "1 year",
    image: "./img/anh3.webp"
  },
  {
    id: 4,
    name: "ASUS VivoBook 15 (2022) Core i3 10th Gen",
    price: "33.990$",
    description: "ASUS VivoBook 15 X515JA-BQ322WS is a thin and light laptop with a modern design in Transparent Silver. Equipped with an Intel Core i3-1005G1 processor, 8GB RAM and a 512GB SSD, the device meets the basic needs of office work, study and entertainment. The 15.6-inch Full HD screen with NanoEdge thin bezels provides a spacious and sharp visual experience. The device weighs about 1.8 kg, convenient for moving. In addition, the fingerprint sensor integrated on the touchpad helps log in quickly and securely.",
    specs: "+ CPU: Intel Core i3-1005G1 (1.2 GHz, up to 3.4 GHz, 2 cores 4 threads), <br> RAM: 8GB DDR4, <br> Hard drive: 512GB SSD, <br> Screen: 15.6 inch Full HD (1920 x 1080), IPS panel, anti-glare, 250 nits brightness, 45% NTSC, <br> Graphics card: Intel UHD Graphics, <br> Battery: 2 cell, 37Wh, <br> Weight: 1.8 kg, <br> Operating system: Windows 11 Home, <br> Keyboard: Chiclet Keyboard with numeric keypad, <br> Security: Fingerprint sensor integrated on touchpad",
    warranty: "1 years",
    image: "./img/anh4.webp"
  },
  {
    id: 5,
    name: "Lenovo Athlon Dual Core",
    price: "18.990$",
    description: "Lenovo IdeaPad Slim 14 is a thin and light laptop with a modern design, suitable for students and office users. Equipped with an AMD Athlon 3050U processor, 8GB RAM and a 256GB SSD hard drive, the device meets the basic needs of office work, study and entertainment. The 14-inch HD screen provides a clear visual experience. The device is pre-installed with Windows 11 and Microsoft Office Professional 2021, providing maximum support for work and study.",
    specs: "+ CPU: AMD Athlon™ 3050U (2 cores, 2 threads, base clock 2.3GHz, max 3.2GHz), <br> RAM: 8GB DDR4, <br> Hard drive: 256GB SSD, <br> Screen: 14 inch HD Widescreen, <br> Graphics card: AMD Radeon Graphics, <br> Battery: 38Wh, <br> Weight: 1.38 kg, <br> Operating system: Windows 11, <br> Included software: Microsoft Office Professional 2021",
    warranty: "1 years",
    image: "./img/anh5.jpg"
  },
  {
    id: 6,
    name: "APPLE 2020 Macbook Air M1",
    price: "86.990$",
    description: "The MacBook Air 2020 is equipped with an Apple M1 chip with an 8-core CPU and a 7- or 8-core GPU, providing superior performance compared to previous MacBook Air models. It has a 13.3-inch Retina display with a resolution of 2560 x 1600, supporting True Tone technology, providing sharp images and accurate colors. Weighing only 1.29 kg, the MacBook Air 2020 is easy to carry and use in many situations.",
    specs: "CPU:  + CPU: Apple M1 with 8 cores (4 high-performance cores and 4 power-saving cores), <br> GPU: 7 cores or 8 cores, <br> RAM: 8GB or 16GB, <br> Hard drive: 256GB, 512GB or 1TB SSD, <br> Screen: 13.3-inch Retina, 2560 x 1600 resolution, True Tone support, <br> Ports: Two Thunderbolt 3 (USB-C) ports, <br> Battery: Up to 15 hours of wireless web browsing, <br> Weight: 1.29 kg",
    warranty: "1 years",
    image: "./img/anh6.jpg"
  },
  {
    id: 7,
    name: "ASUS VivoBook 14 (2021) Celeron Dual Core",
    price: "23.990$",
    description: "ASUS VivoBook 14 (2021) is a thin and light laptop, suitable for everyday tasks such as office work, study and basic entertainment.",
    specs: "CPU: Intel Celeron Dual Core N4020, <br> RAM: 4 GB DDR4, Hard drive: 256 GB SSD, <br> Screen: 14 inch HD (1366 x 768), 16:9 ratio, 200 nits brightness, 45% NTSC color gamut, anti-glare,<br> Graphics: Intel UHD Graphics",
    warranty: "12 months",
    image: "./img/anh7.jpg"
  },
  {
    id: 8,
    name: "DELL Vostro Ryzen 3 Quad Core 5425U",
    price: "36.890$",
    description: "Dell Vostro 3425 is a laptop in Dell's Vostro line, designed with stable performance, suitable for basic office, study and entertainment tasks.",
    specs: "CPU: AMD Ryzen™ 3 5425U (2.70GHz, up to 4.10GHz, 4 cores,<br>8 threads, 8MB cache),<br> RAM: 4GB DDR4 Bus 3200MHz (2 slots, supports up to 32GB),<br> Hard drive: 256GB PCIe® NVMe™ M.2 SSD,<br> Screen: 14 inch Full HD (1920 x 1080), anti-glare technology, 250 nits brightness,<br> Graphics: AMD Radeon™ Graphics,<br> Wireless connection: Wi-Fi 6 (802.11ax), Bluetooth 5.1, Battery: 3 cell, 41 Whr,<br> Weight: 1.40 kg,<br> Operating system: Windows 11 Home SL 64bit <br> Office Home & Student 2021",
    warranty: "1 years",
    image: "./img/anh8.webp"
  },
  {
    id: 9,
    name: "Lenovo V15 G2 Core i3 11th Gen",
    price: "33.999$",
    description: "Lenovo V15 G2 ITL is a laptop in Lenovo's V Series, designed to be thin, light and have stable performance, suitable for basic office, study and entertainment tasks.",
    specs: "CPU: Intel Core i3-1115G4 (2 cores, 4 threads, base speed 3.0 GHz, max 4.1 GHz, 6MB cache) <br> RAM: 4GB DDR4 3200MHz (upgradable to 12GB) <br> Hard drive: 256GB SSD M.2 2242 PCIe 3.0x4 NVMe <br> Screen: 15.6 inch Full HD (1920x1080), TN panel, 250 nits brightness, anti-glare, 45% NTSC <br> Graphics: Intel UHD Graphics <br> Wireless connection: Wi-Fi 5 (802.11ac), Bluetooth 5.0 <br> Battery: 2 cell, 38Wh <br> Weight: 1.7 kg <br> Operating system: FreeDOS (no pre-installed operating system)",
    warranty: "1 years",
    image: "./img/anh9.png"
  },
  {
    id: 10,
    name: "RedmiBook Pro Core i5 11th Gen",
    price: "38990$",
    description: "RedmiBook Pro is Xiaomi's high-end laptop line, designed with powerful performance and high-quality screen, suitable for office tasks, learning and multimedia entertainment.",
    specs: "CPU: Intel Core i5-11300H (up to 4.4 GHz, 4 cores, 8 threads, 8 MB cache) <br> RAM: 16 GB DDR4 3200 MHz <br> Hard drive: 512 GB PCIe SSD <br> Screen: 15.6 inch 3.2K (3200 x 2000), 90 Hz refresh rate, 16:10 ratio, 300 nits brightness, 100% sRGB color gamut, anti-glare technology <br> Graphics: Intel Iris Xe Graphics or NVIDIA GeForce MX450 (depending on version) <br> Wireless connectivity: Wi-Fi 6 (802.11ax), Bluetooth 5.1 <br> Battery: 70 Whr, supports 100W USB-C fast charging <br> Weight: 1.79 kg <br> Operating system: Windows 10 Home",
    warranty: "12 months",
    image: "./img/anh10.jpg"
  },
  {
    id: 11,
    name: "acer Aspire 3 Ryzen 3 Dual Core 3250U",
    price: "26.990$",
    description: "Acer Aspire 3 is Acer's popular laptop line, designed to meet basic needs such as office work, study and light entertainment.",
    specs: "CPU: AMD Ryzen™ 3 3250U (2 cores, 4 threads, base speed 2.6 GHz, maximum 3.5 GHz, 4MB cache) <br> RAM: 4GB DDR4 2400MHz (soldered on the board), 1 free RAM slot, support upgrade up to 12GB <br> Hard drive: 256GB PCIe NVMe SSD <br> Screen: 15.6 inch Full HD (1920 x 1080), IPS panel, Acer ComfyView technology, 60Hz refresh rate <br> Graphics: AMD Radeon™ Graphics <br> Wireless connection: Wi-Fi 5 (802.11a/b/g/n/ac), Bluetooth 5.0 <br> Battery: 2 cell, 36Wh <br> Weight: 1.7 kg <br> Operating system: Windows 10 Home",
    warranty: "1 years",
    image: "./img/anh11.webp"
  },
  {
    id: 12,
    name: "ASUS Vivobook 14 (2022) Core i5 12th Gen",
    price: "66.990$",
    description: "ASUS Vivobook 14 (2022) is a laptop in ASUS's Vivobook series, designed with powerful performance and modern design, suitable for office tasks, learning and multimedia entertainment.",
    specs: "CPU: Intel® Core™ i5-1240P (12 cores, 16 threads, base speed 1.7 GHz, max 4.4 GHz, 12MB cache) <br> RAM: 8GB DDR4 on board, support upgrade up to 16GB <br> Hard drive: 512GB M.2 NVMe™ PCIe® 3.0 SSD <br> Screen: 14 inch Full HD (1920 x 1080), IPS panel, 250 nits brightness, 45% NTSC color gamut, anti-glare <br> Graphics: Intel® Iris® Xe Graphics <br> Wireless connectivity: Wi-Fi 6 (802.11ax), Bluetooth 5.3 <br> Battery: 42Wh, 3 cell Li-ion <br> Weight: 1.5 kg <br> Operating system: Windows 11 Home",
    warranty: "1 years",
    image: "./img/ah12.webp"
  },
];

function showPopup(productId) {
  const product = products.find(p => p.id === productId);

  if (product) {
    const popup = document.getElementById('product-popup');
    const popupImage = document.getElementById('popup-image');
    const popupInfo = document.getElementById('popup-info');

    popupImage.src = product.image;
    popupInfo.innerHTML = `
          <h3>${product.name}</h3>
          <p><strong>Price:</strong> ${product.price}</p>
          <p><strong>Description:</strong> ${product.description}</p>
          <p><strong>Specifications:</strong> ${product.specs}</p>
          <p><strong>Warranty:</strong> ${product.warranty}</p>
      `;

    popup.style.display = 'flex';
  }
}

function closePopup() {
  document.getElementById('product-popup').style.display = 'none';
}

document.querySelectorAll('.product').forEach((product, index) => {
  product.addEventListener('click', () => {
    const productId = index + 1;
    showPopup(productId);
  });
});

let cart = [];

function addToCart(product) {
  cart.push(product);
  alert(`${product} has been added to your cart!`);
  updateCartUI();
}

function updateCartUI() {
  const cartContainer = document.getElementById('cart-container');
  if (cart.length > 0) {
    cartContainer.innerHTML = cart
      .map(item => `<p>${item} <button onclick="removeFromCart('${item}')">Remove</button></p>`)
      .join('');
  } else {
    cartContainer.innerHTML = '<p>Your cart is currently empty.</p>';
  }
}


function redirectToCheckout() {
  if (cart.length > 0) {
    window.location.href = 'checkout.html';
  } else {
    alert('Your cart is empty!');
  }
}

document.addEventListener('DOMContentLoaded', () => {
  updateCartUI();
});

// Thêm sản phẩm vào giỏ hàng
function addToCart(productId) {
  const product = products.find(p => p.id === productId);
  if (product) {
    // Lấy giỏ hàng hiện tại từ localStorage hoặc tạo mảng trống nếu chưa có
    const cart = JSON.parse(localStorage.getItem('cart')) || [];

    // Thêm sản phẩm mới vào giỏ hàng
    cart.push(product);

    // Cập nhật lại giỏ hàng trong localStorage
    localStorage.setItem('cart', JSON.stringify(cart));

    alert(`${product.name} đã được thêm vào giỏ hàng!`);
    updateCartUI();
  }
}


// Mua ngay
function buyNow(productId) {
  const product = products.find(p => p.id === productId);
  if (product) {
    cart = [product]; // Thay thế giỏ hàng bằng sản phẩm vừa chọn
    localStorage.setItem('cart', JSON.stringify(cart));
    window.location.href = './checkout.html';
  }
}

function updateCartUI() {
  const cartContainer = document.getElementById('cart-container');
  const cart = JSON.parse(localStorage.getItem('cart')) || [];

  if (cart.length > 0) {
    cartContainer.innerHTML = cart
      .map(item => `
              <div class="cart-item" style= "display: flex; justify-content: space-between; align-items: center;">
                  <img src="${item.image}" alt="${item.name}" style="width: 300px; height: auto;">
                  <p><strong>${item.name}</strong></p>
                  <p>Price: ${item.price}</p>
                  <button onclick="removeFromCart(${item.id})">Remove</button>
                  <button onclick="buyNow(${item.id})">Buy Now</button>
              </div>
          `)
      .join('');
  } else {
    cartContainer.innerHTML = '<p>Your cart is currently empty.</p>';
  }
}

function removeFromCart(productId) {
  const cart = JSON.parse(localStorage.getItem('cart')) || [];
  const updatedCart = cart.filter(item => item.id !== productId);
  localStorage.setItem('cart', JSON.stringify(updatedCart));
  updateCartUI();
}


// Tải giỏ hàng khi trang được tải
document.addEventListener('DOMContentLoaded', () => {
  updateCartUI();
});
function buyNow(productId) {
  const product = products.find(p => p.id === productId); // Tìm sản phẩm dựa trên id
  if (product) {
    localStorage.setItem('cart', JSON.stringify([product])); // Chỉ lưu sản phẩm được chọn vào giỏ hàng
    window.location.href = './checkout.html'; // Chuyển hướng đến trang checkout
  }
}
