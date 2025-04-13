<?php
require_once '../database.php';
include 'header.php';

// Handle actions
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';
$error = '';

// Handle product deletion
if ($action == 'delete' && $id > 0) {
    try {
        $stmt = $pdo->prepare("DELETE FROM laptops WHERE id = ?");
        if ($stmt->execute([$id])) {
            $message = "Product successfully deleted.";
        }
    } catch (PDOException $e) {
        $error = "Failed to delete product: " . $e->getMessage();
    }
    // Redirect to list after delete
    $action = 'list';
}

// Handle form submission for add/edit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && ($action == 'add' || $action == 'edit')) {
    $name = $_POST['name'] ?? '';
    $processor = $_POST['processor'] ?? '';
    $ram = $_POST['ram'] ?? '';
    $storage = $_POST['storage'] ?? '';
    $display = $_POST['display'] ?? '';
    $price = $_POST['price'] ?? '';
    $category = $_POST['category'] ?? '';
    $img_link = $_POST['img_link'] ?? '';

    // Validate input
    if (empty($name) || empty($processor) || empty($ram) || empty($price)) {
        $error = "Name, processor, RAM and price are required fields.";
    } else {
        try {
            if ($action == 'add') {
                // Insert new product
                $stmt = $pdo->prepare("INSERT INTO laptops (name, processor, ram, storage, display, price, category, img_link) 
                                      VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                if ($stmt->execute([$name, $processor, $ram, $storage, $display, $price, $category, $img_link])) {
                    $message = "Product successfully added.";
                    // Reset for new form
                    $action = 'list';
                }
            } else {
                // Update existing product
                $stmt = $pdo->prepare("UPDATE laptops SET name = ?, processor = ?, ram = ?, storage = ?, 
                                     display = ?, price = ?, category = ?, img_link = ? WHERE id = ?");
                if ($stmt->execute([$name, $processor, $ram, $storage, $display, $price, $category, $img_link, $id])) {
                    $message = "Product successfully updated.";
                    // Return to list
                    $action = 'list';
                }
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

// Get product data for edit
$product = [];
if ($action == 'edit' && $id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM laptops WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$product) {
            $error = "Product not found.";
            $action = 'list';
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
        $action = 'list';
    }
}

// Display appropriate view based on action
if ($action == 'list') {
    // Pagination settings
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $items_per_page = 10;
    $offset = ($page - 1) * $items_per_page;

    // Get all products for list view with pagination
    try {
        // Get total count
        $stmt = $pdo->query("SELECT COUNT(*) FROM laptops");
        $total_products = $stmt->fetchColumn();
        $total_pages = ceil($total_products / $items_per_page);

        // Get products for current page
        $stmt = $pdo->prepare("SELECT * FROM laptops ORDER BY id DESC LIMIT ? OFFSET ?");
        $stmt->bindValue(1, $items_per_page, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
        $products = [];
        $total_pages = 1;
    }
?>

    <!-- Display success/error messages -->
    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <!-- Product List View -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div><i class="bi bi-table me-1"></i> Product List</div>
            <a href="?action=add" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle"></i> Add New Product
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Specs</th>
                            <th>Price</th>
                            <th>Category</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $p): ?>
                            <tr>
                                <td><?php echo $p['id']; ?></td>
                                <td>
                                    <?php if (!empty($p['img_link'])): ?>
                                        <img src="<?php echo $p['img_link']; ?>" alt="<?php echo $p['name']; ?>" width="50" class="img-thumbnail">
                                    <?php else: ?>
                                        <div class="bg-light text-center" style="width: 50px; height: 50px; line-height: 50px;">
                                            <i class="bi bi-image"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($p['name']); ?></td>
                                <td>
                                    <small>
                                        <strong>CPU:</strong> <?php echo htmlspecialchars($p['processor']); ?><br>
                                        <strong>RAM:</strong> <?php echo htmlspecialchars($p['ram']); ?><br>
                                        <strong>Storage:</strong> <?php echo htmlspecialchars($p['storage']); ?>
                                    </small>
                                </td>
                                <td><?php echo formatPrice($p['price']); ?></td>
                                <td><?php echo htmlspecialchars($p['category']); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="?action=edit&id=<?php echo $p['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="javascript:void(0);" onclick="confirmDelete(<?php echo $p['id']; ?>)" class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                        <a href="../product-detail.php?id=<?php echo $p['id']; ?>" target="_blank" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="7" class="text-center">No products found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination controls -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>

                        <?php
                        // Show pagination with ellipsis
                        $show_pages = 5; // Number of pages to show around current page
                        $start_page = max(1, $page - floor($show_pages / 2));
                        $end_page = min($total_pages, $start_page + $show_pages - 1);

                        // Adjust start page if end page is maxed out
                        if ($end_page == $total_pages) {
                            $start_page = max(1, $end_page - $show_pages + 1);
                        }

                        // Always show first page
                        if ($start_page > 1) {
                            echo '<li class="page-item"><a class="page-link" href="?page=1">1</a></li>';
                            if ($start_page > 2) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                        }

                        // Show middle pages
                        for ($i = $start_page; $i <= $end_page; $i++) {
                            echo '<li class="page-item ' . ($i == $page ? 'active' : '') . '">';
                            echo '<a class="page-link" href="?page=' . $i . '">' . $i . '</a>';
                            echo '</li>';
                        }

                        // Always show last page
                        if ($end_page < $total_pages) {
                            if ($end_page < $total_pages - 1) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                            echo '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . '">' . $total_pages . '</a></li>';
                        }
                        ?>

                        <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function confirmDelete(id) {
            if (confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
                window.location.href = '?action=delete&id=' + id;
            }
        }
    </script>

<?php
} elseif ($action == 'add' || $action == 'edit') {
    // Add/Edit Form
?>

    <!-- Display error message if any -->
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header">
            <i class="bi bi-pencil-square me-1"></i>
            <?php echo $action == 'add' ? 'Add New Product' : 'Edit Product'; ?>
        </div>
        <div class="card-body">
            <form method="post" action="?action=<?php echo $action; ?><?php echo $action == 'edit' ? '&id=' . $id : ''; ?>">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Product Name</label>
                        <input type="text" class="form-control" id="name" name="name"
                            value="<?php echo $action == 'edit' ? htmlspecialchars($product['name']) : ''; ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-control" id="category" name="category">
                            <option value="Gaming" <?php echo ($action == 'edit' && $product['category'] == 'Gaming') ? 'selected' : ''; ?>>Gaming</option>
                            <option value="Business" <?php echo ($action == 'edit' && $product['category'] == 'Business') ? 'selected' : ''; ?>>Business</option>
                            <option value="Student" <?php echo ($action == 'edit' && $product['category'] == 'Student') ? 'selected' : ''; ?>>Student</option>
                            <option value="Premium" <?php echo ($action == 'edit' && $product['category'] == 'Premium') ? 'selected' : ''; ?>>Premium</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="processor" class="form-label">Processor</label>
                        <input type="text" class="form-control" id="processor" name="processor"
                            value="<?php echo $action == 'edit' ? htmlspecialchars($product['processor']) : ''; ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="ram" class="form-label">RAM</label>
                        <input type="text" class="form-control" id="ram" name="ram"
                            value="<?php echo $action == 'edit' ? htmlspecialchars($product['ram']) : ''; ?>" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="storage" class="form-label">Storage</label>
                        <input type="text" class="form-control" id="storage" name="storage"
                            value="<?php echo $action == 'edit' ? htmlspecialchars($product['storage']) : ''; ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="display" class="form-label">Display</label>
                        <input type="text" class="form-control" id="display" name="display"
                            value="<?php echo $action == 'edit' ? htmlspecialchars($product['display']) : ''; ?>">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="price" class="form-label">Price</label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="text" class="form-control" id="price" name="price"
                                value="<?php echo $action == 'edit' ? htmlspecialchars($product['price']) : ''; ?>" required>
                        </div>
                        <div class="form-text">Enter price in Rupees (₹). It will be converted to VND on display.</div>
                    </div>
                    <div class="col-md-6">
                        <label for="img_link" class="form-label">Image URL</label>
                        <input type="text" class="form-control" id="img_link" name="img_link"
                            value="<?php echo $action == 'edit' ? htmlspecialchars($product['img_link']) : ''; ?>">
                        <div class="form-text">Enter a URL to the product image.</div>
                    </div>
                </div>

                <?php if ($action == 'edit' && !empty($product['img_link'])): ?>
                    <div class="mb-3">
                        <label class="form-label">Current Image</label>
                        <div>
                            <img src="<?php echo $product['img_link']; ?>" alt="Product" class="img-thumbnail" style="max-height: 150px;">
                        </div>
                    </div>
                <?php endif; ?>

                <div class="mb-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i>
                        <?php echo $action == 'add' ? 'Add Product' : 'Update Product'; ?>
                    </button>
                    <a href="?action=list" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

<?php
}

include 'footer.php';
?>