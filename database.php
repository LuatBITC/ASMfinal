<?php
$host = 'localhost';
$dbname = 'laptop';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Connection failed. Please try again later.");
}

// Function to get all brands
function getAllBrands()
{
    global $pdo;
    $stmt = $pdo->query("SELECT DISTINCT SUBSTRING_INDEX(name, ' ', 1) as brand FROM laptops ORDER BY brand");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Function to get products by brand with pagination
function getProductsByBrand($brand, $page = 1, $items_per_page = 6)
{
    global $pdo;

    $offset = ($page - 1) * $items_per_page;

    $stmt = $pdo->prepare("SELECT *, CAST(REPLACE(REPLACE(price, 'Rs.', ''), ',', '') AS DECIMAL(10,2)) as numeric_price 
                          FROM laptops WHERE name LIKE :brand 
                          ORDER BY numeric_price LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':brand', $brand . '%', PDO::PARAM_STR);
    $stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Convert prices from Rs. to VND
    foreach ($products as &$product) {
        $rupees_price = floatval(str_replace(['Rs.', ','], '', $product['price']));
        $product['original_price'] = $product['price'];
        $product['price'] = convertToVND($rupees_price);
        $product['formatted_price'] = formatPrice($product['price']);
    }

    return $products;
}

// Function to get total products count by brand
function getTotalProductsByBrand($brand)
{
    global $pdo;

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM laptops WHERE name LIKE :brand");
    $stmt->bindValue(':brand', $brand . '%', PDO::PARAM_STR);
    $stmt->execute();

    return $stmt->fetchColumn();
}

// Function to get all laptops
function getAllLaptops()
{
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM laptops");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get laptop by ID
function getLaptopById($id)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM laptops WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get product by ID with formatted price
function getProductById($id)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM laptops WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        // Parse brand from name (assuming first word is brand)
        $product['brand'] = explode(' ', $product['name'])[0];

        // Convert price
        $rupees_price = floatval(str_replace(['Rs.', ','], '', $product['price']));
        $product['original_price'] = $product['price'];
        $product['price'] = convertToVND($rupees_price);
        $product['formatted_price'] = formatPrice($product['price']);

        // Add old price for discount display (optional, random 5-15% higher)
        if (rand(0, 1) == 1) {
            $discount_factor = rand(105, 115) / 100;
            $product['old_price'] = $product['price'] * $discount_factor;
        }
    }

    return $product;
}

// Get related products based on same brand or similar price range
function getRelatedProducts($product_id, $brand, $limit = 4)
{
    global $pdo;

    // Get products with same brand, excluding current product
    $stmt = $pdo->prepare("SELECT * FROM laptops WHERE name LIKE :brand AND id != :product_id ORDER BY RAND() LIMIT :limit");
    $stmt->bindValue(':brand', $brand . '%', PDO::PARAM_STR);
    $stmt->bindValue(':product_id', $product_id, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    $related_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // If we don't have enough related products, get some random products
    if (count($related_products) < $limit) {
        $needed = $limit - count($related_products);
        $existing_ids = array_column($related_products, 'id');
        $existing_ids[] = $product_id;

        $placeholders = implode(',', array_fill(0, count($existing_ids), '?'));

        $stmt = $pdo->prepare("SELECT * FROM laptops WHERE id NOT IN ($placeholders) ORDER BY RAND() LIMIT " . $needed);
        $stmt->execute($existing_ids);

        $additional_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $related_products = array_merge($related_products, $additional_products);
    }

    // Format prices for all related products
    foreach ($related_products as &$product) {
        // Parse brand
        $product['brand'] = explode(' ', $product['name'])[0];

        // Convert price
        $rupees_price = floatval(str_replace(['Rs.', ','], '', $product['price']));
        $product['original_price'] = $product['price'];
        $product['price'] = convertToVND($rupees_price);
        $product['formatted_price'] = formatPrice($product['price']);
    }

    return $related_products;
}

// Function to search laptops
function searchLaptops($keyword)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM laptops WHERE name LIKE ? OR processor LIKE ?");
    $keyword = "%$keyword%";
    $stmt->execute([$keyword, $keyword]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to convert price from Indian Rupees (Rs.) to VND
function convertToVND($rupees_price)
{
    // Tỉ giá Rs. to VND (1 Rs. = khoảng 290 VND, có thể cập nhật theo tỉ giá thực tế)
    $exchange_rate = 290;
    return $rupees_price * $exchange_rate;
}

// Function to format price in VND
function formatPrice($price)
{
    // Format the price without the underline character
    return number_format($price, 0, ',', '.') . ' ₫';
}

// Function to get limited products by brand for homepage
function getLimitedProductsByBrand($brand, $limit = 3)
{
    global $pdo;

    $stmt = $pdo->prepare("SELECT *, CAST(REPLACE(REPLACE(price, 'Rs.', ''), ',', '') AS DECIMAL(10,2)) as numeric_price 
                          FROM laptops WHERE name LIKE :brand 
                          ORDER BY numeric_price LIMIT :limit");
    $stmt->bindValue(':brand', $brand . '%', PDO::PARAM_STR);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Convert prices from Rs. to VND
    foreach ($products as &$product) {
        $rupees_price = floatval(str_replace(['Rs.', ','], '', $product['price']));
        $product['original_price'] = $product['price'];
        $product['price'] = convertToVND($rupees_price);
        $product['formatted_price'] = formatPrice($product['price']);
    }

    return $products;
}

function getFilteredProducts($brand, $category, $sort, $page, $per_page)
{
    global $pdo;

    $params = array();
    $where_clauses = array();

    if (!empty($brand)) {
        $where_clauses[] = "name LIKE :brand";
        $params[':brand'] = $brand . '%';
    }

    if (!empty($category)) {
        $where_clauses[] = "category = :category";
        $params[':category'] = $category;
    }

    $where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

    // Determine sort order
    $order_by = "name ASC"; // default sorting
    switch ($sort) {
        case 'name_desc':
            $order_by = "name DESC";
            break;
        case 'price_asc':
            $order_by = "CAST(REPLACE(REPLACE(price, 'Rs.', ''), ',', '') AS DECIMAL(10,2)) ASC";
            break;
        case 'price_desc':
            $order_by = "CAST(REPLACE(REPLACE(price, 'Rs.', ''), ',', '') AS DECIMAL(10,2)) DESC";
            break;
    }

    $offset = ($page - 1) * $per_page;

    $sql = "SELECT * FROM laptops $where_sql ORDER BY $order_by LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($sql);

    // Bind all parameters using named parameters
    foreach ($params as $param => $value) {
        $stmt->bindValue($param, $value);
    }

    $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

    $stmt->execute();

    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Convert prices from Rs. to VND
    foreach ($products as &$product) {
        $rupees_price = floatval(str_replace(['Rs.', ','], '', $product['price']));
        $product['original_price'] = $product['price'];
        $product['price'] = convertToVND($rupees_price);
        $product['formatted_price'] = formatPrice($product['price']);
    }

    return $products;
}

function getTotalFilteredProducts($brand, $category)
{
    global $pdo;

    $params = array();
    $where_clauses = array();

    if (!empty($brand)) {
        $where_clauses[] = "name LIKE :brand";
        $params[':brand'] = $brand . '%';
    }

    if (!empty($category)) {
        $where_clauses[] = "category = :category";
        $params[':category'] = $category;
    }

    $where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

    $sql = "SELECT COUNT(*) FROM laptops $where_sql";

    $stmt = $pdo->prepare($sql);

    // Bind all parameters using named parameters
    foreach ($params as $param => $value) {
        $stmt->bindValue($param, $value);
    }

    $stmt->execute();

    return $stmt->fetchColumn();
}

function updateDatabaseSchema()
{
    global $pdo;

    try {
        // Start transaction
        $pdo->beginTransaction();

        // Read SQL file
        $sql = file_get_contents('db.sql');

        // Split SQL into individual statements
        $statements = array_filter(array_map('trim', explode(';', $sql)));

        // Execute each statement
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                $pdo->exec($statement);
            }
        }

        // Commit transaction
        $pdo->commit();
        return true;
    } catch (PDOException $e) {
        // Rollback on error
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Error updating database schema: " . $e->getMessage());
        return false;
    }
}

// Execute schema update
try {
    updateDatabaseSchema();
} catch (Exception $e) {
    error_log("Failed to update database schema: " . $e->getMessage());
}

// Session-based Wishlist Functions
function addToWishlist($user_id, $product_id)
{
    global $pdo;
    try {
        // Check if product exists
        $stmt = $pdo->prepare("SELECT id FROM laptops WHERE id = ?");
        $stmt->execute([$product_id]);
        if (!$stmt->fetch()) {
            return ['error' => 'Product not found'];
        }

        // Add to wishlist
        $stmt = $pdo->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $product_id]);
        return ['success' => true];
    } catch (PDOException $e) {
        if ($e->getCode() == '23000') { // Duplicate entry
            return ['error' => 'Product already in wishlist'];
        }
        error_log("Database error in addToWishlist: " . $e->getMessage());
        return ['error' => 'Database error occurred'];
    }
}

function removeFromWishlist($user_id, $product_id)
{
    global $pdo;
    try {
        $stmt = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
        if ($stmt->execute([$user_id, $product_id]) && $stmt->rowCount() > 0) {
            return ['success' => true];
        }
        return ['error' => 'Product not found in wishlist'];
    } catch (PDOException $e) {
        error_log("Database error in removeFromWishlist: " . $e->getMessage());
        return ['error' => 'Database error occurred'];
    }
}

function getWishlist($user_id)
{
    global $pdo;
    try {
        if (!$user_id) {
            return ['error' => 'Please login to view wishlist'];
        }

        $stmt = $pdo->prepare("
            SELECT l.*, p.name, p.price, p.img_link 
            FROM wishlist w 
            JOIN laptops l ON w.product_id = l.id 
            WHERE w.user_id = ?
        ");
        $stmt->execute([$user_id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return ['error' => 'Database error: ' . $e->getMessage()];
    }
}

function isInWishlist($user_id, $product_id)
{
    global $pdo;
    try {
        if (!$user_id) return false;

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        return $stmt->fetchColumn() > 0;
    } catch (PDOException $e) {
        error_log("Database error in isInWishlist: " . $e->getMessage());
        return false;
    }
}

// Session-based Cart Functions
function addToCart($user_id, $product_id, $quantity = 1)
{
    global $pdo;
    try {
        // Check if product exists
        $stmt = $pdo->prepare("SELECT id FROM laptops WHERE id = ?");
        $stmt->execute([$product_id]);
        if (!$stmt->fetch()) {
            return ['error' => 'Product not found'];
        }

        // Check if already in cart
        $stmt = $pdo->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        $existing = $stmt->fetch();

        if ($existing) {
            // Update quantity
            $stmt = $pdo->prepare("UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$quantity, $user_id, $product_id]);
        } else {
            // Add new item
            $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $product_id, $quantity]);
        }
        return ['success' => true];
    } catch (PDOException $e) {
        error_log("Database error in addToCart: " . $e->getMessage());
        return ['error' => 'Database error occurred'];
    }
}

function updateCartQuantity($user_id, $product_id, $quantity)
{
    global $pdo;
    try {
        if (!$user_id) {
            return ['error' => 'Please login to update cart'];
        }

        if ($quantity <= 0) {
            // Remove item if quantity is 0 or less
            return removeFromCart($user_id, $product_id);
        }

        $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$quantity, $user_id, $product_id]);

        return ['success' => 'Cart updated'];
    } catch (PDOException $e) {
        return ['error' => 'Database error: ' . $e->getMessage()];
    }
}

function removeFromCart($user_id, $product_id)
{
    global $pdo;
    try {
        if (!$user_id) {
            return ['error' => 'Please login to remove items from cart'];
        }

        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);

        return ['success' => 'Product removed from cart'];
    } catch (PDOException $e) {
        return ['error' => 'Database error: ' . $e->getMessage()];
    }
}

function getCart($user_id)
{
    global $pdo;
    try {
        if (!$user_id) {
            return ['error' => 'Please login to view cart'];
        }

        $stmt = $pdo->prepare("
            SELECT c.*, l.name, l.price, l.img_link, (l.price * c.quantity) as total_price 
            FROM cart c 
            JOIN laptops l ON c.product_id = l.id 
            WHERE c.user_id = ?
        ");
        $stmt->execute([$user_id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return ['error' => 'Database error: ' . $e->getMessage()];
    }
}

function getCartCount($user_id)
{
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(quantity), 0) as count FROM cart WHERE user_id = ?");
        $stmt->execute([$user_id]);
        return intval($stmt->fetchColumn());
    } catch (PDOException $e) {
        error_log("Database error in getCartCount: " . $e->getMessage());
        return 0;
    }
}

function getCartTotal($user_id)
{
    global $pdo;
    try {
        if (!$user_id) return 0;

        $stmt = $pdo->prepare("
            SELECT SUM(l.price * c.quantity) as total 
            FROM cart c 
            JOIN laptops l ON c.product_id = l.id 
            WHERE c.user_id = ?
        ");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['total'] ?? 0;
    } catch (PDOException $e) {
        return 0;
    }
}

// User Authentication Functions
function registerUser($username, $email, $password, $full_name = '', $phone = '', $address = '')
{
    global $pdo;
    try {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, phone, address) 
                              VALUES (:username, :email, :password, :full_name, :phone, :address)");

        $stmt->execute([
            'username' => $username,
            'email' => $email,
            'password' => $hashed_password,
            'full_name' => $full_name,
            'phone' => $phone,
            'address' => $address
        ]);

        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Duplicate entry
            if (strpos($e->getMessage(), 'username')) {
                return 'username_exists';
            }
            if (strpos($e->getMessage(), 'email')) {
                return 'email_exists';
            }
        }
        return false;
    }
}

function loginUser($username, $password)
{
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username OR email = :email");
        $stmt->execute([
            'username' => $username,
            'email' => $username
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Remove password from session data
            unset($user['password']);
            return $user;
        }

        return false;
    } catch (PDOException $e) {
        return false;
    }
}

function getUserById($id)
{
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT id, username, email, full_name, phone, address, created_at FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return false;
    }
}

function updateUserProfile($id, $data)
{
    global $pdo;
    try {
        $allowed_fields = ['full_name', 'phone', 'address'];
        $updates = [];
        $params = ['id' => $id];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowed_fields)) {
                $updates[] = "$key = :$key";
                $params[$key] = $value;
            }
        }

        if (empty($updates)) {
            return false;
        }

        $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    } catch (PDOException $e) {
        return false;
    }
}

function updateUserPassword($id, $current_password, $new_password)
{
    global $pdo;
    try {
        // Verify current password
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($current_password, $user['password'])) {
            return 'invalid_password';
        }

        // Update to new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        return $stmt->execute([$hashed_password, $id]);
    } catch (PDOException $e) {
        return false;
    }
}
