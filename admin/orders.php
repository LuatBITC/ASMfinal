<?php
require_once '../database.php';
include 'header.php';


// Handle actions
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';
$error = '';

// Pagination settings
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 10;
$offset = ($page - 1) * $items_per_page;

// Handle order status update
if ($action == 'update_status' && $id > 0 && isset($_POST['status'])) {
    $status = $_POST['status'];
    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        if ($stmt->execute([$status, $id])) {
            $message = "Order status updated successfully.";
        }
    } catch (PDOException $e) {
        $error = "Failed to update order status: " . $e->getMessage();
    }
    $action = 'view'; // Redirect to view details
}

// Handle order deletion
if ($action == 'delete' && $id > 0) {
    try {
        $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
        if ($stmt->execute([$id])) {
            $message = "Order successfully deleted.";
        }
    } catch (PDOException $e) {
        $error = "Failed to delete order: " . $e->getMessage();
    }
    // Redirect to list after delete
    $action = 'list';
}

// Display appropriate view based on action
if ($action == 'list') {
    // Get total orders count for pagination
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM orders");
        $total_orders = $stmt->fetchColumn();
        $total_pages = ceil($total_orders / $items_per_page);

        // Get orders for current page
        $stmt = $pdo->prepare("SELECT * FROM orders ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $stmt->bindValue(1, $items_per_page, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
        $orders = [];
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

    <!-- Order List View -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div><i class="bi bi-table me-1"></i> Order List</div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $o): ?>
                            <tr>
                                <td>#<?php echo $o['id']; ?></td>
                                <td>
                                    <?php echo htmlspecialchars($o['full_name']); ?><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($o['email']); ?></small>
                                </td>
                                <td><?php echo date('d M Y H:i', strtotime($o['created_at'])); ?></td>
                                <td><?php echo number_format($o['total_amount'], 0, ',', '.') . ' ₫'; ?></td>
                                <td><?php echo htmlspecialchars($o['payment_method']); ?></td>
                                <td>
                                    <?php
                                    $status_class = '';
                                    switch ($o['status']) {
                                        case 'pending':
                                            $status_class = 'bg-warning';
                                            break;
                                        case 'processing':
                                            $status_class = 'bg-info';
                                            break;
                                        case 'completed':
                                            $status_class = 'bg-success';
                                            break;
                                        case 'cancelled':
                                            $status_class = 'bg-danger';
                                            break;
                                    }
                                    ?>
                                    <span class="badge <?php echo $status_class; ?>"><?php echo ucfirst($o['status']); ?></span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="?action=view&id=<?php echo $o['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="javascript:void(0);" onclick="confirmDelete(<?php echo $o['id']; ?>)"
                                            class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="7" class="text-center">No orders found.</td>
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
            if (confirm('Are you sure you want to delete this order? This action cannot be undone.')) {
                window.location.href = '?action=delete&id=' + id;
            }
        }
    </script>

<?php
} elseif ($action == 'view' && $id > 0) {
    // View Order Details
    try {
        // Get order details
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            echo "<div class='alert alert-danger'>Order not found.</div>";
            include 'footer.php';
            exit;
        }

        // Get order items
        $order_items = [];
        try {
            $stmt = $pdo->prepare("SELECT oi.*, l.name as product_name 
                                   FROM order_items oi
                                   LEFT JOIN laptops l ON oi.product_id = l.id
                                   WHERE oi.order_id = ?");
            $stmt->execute([$id]);
            $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>Error with order items: " . $e->getMessage() . "</div>";
        }
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Database error: " . $e->getMessage() . "</div>";
        include 'footer.php';
        exit;
    }
?>

    <!-- Display success/error messages -->
    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-receipt me-1"></i>
                Order #<?php echo $order['id']; ?>
            </div>
            <div>
                <a href="?action=list" class="btn btn-sm btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h5>Order Details</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 30%">Order ID</th>
                                <td>#<?php echo $order['id']; ?></td>
                            </tr>
                            <tr>
                                <th>Order Date</th>
                                <td><?php echo date('d M Y H:i', strtotime($order['created_at'])); ?></td>
                            </tr>
                            <tr>
                                <th>Total Amount</th>
                                <td><?php echo number_format($order['total_amount'], 0, ',', '.') . ' ₫'; ?></td>
                            </tr>
                            <tr>
                                <th>Payment Method</th>
                                <td><?php echo htmlspecialchars($order['payment_method']); ?></td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    <form method="post" action="?action=update_status&id=<?php echo $id; ?>" class="d-flex">
                                        <select name="status" class="form-select form-select-sm">
                                            <option value="pending"
                                                <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending
                                            </option>
                                            <option value="processing"
                                                <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>Processing
                                            </option>
                                            <option value="completed"
                                                <?php echo $order['status'] == 'completed' ? 'selected' : ''; ?>>Completed
                                            </option>
                                            <option value="cancelled"
                                                <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled
                                            </option>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-primary ms-2">Update</button>
                                    </form>
                                </td>
                            </tr>
                            <?php if (!empty($order['notes'])): ?>
                                <tr>
                                    <th>Notes</th>
                                    <td><?php echo htmlspecialchars($order['notes']); ?></td>
                                </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>

                <div class="col-md-6">
                    <h5>Customer Information</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 30%">Name</th>
                                <td><?php echo htmlspecialchars($order['full_name']); ?></td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td><?php echo htmlspecialchars($order['email']); ?></td>
                            </tr>
                            <tr>
                                <th>Phone</th>
                                <td><?php echo htmlspecialchars($order['phone']); ?></td>
                            </tr>
                            <tr>
                                <th>Shipping Address</th>
                                <td><?php echo nl2br(htmlspecialchars($order['address'])); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <?php if (!empty($order_items)): ?>
                <h5>Order Items</h5>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order_items as $item): ?>
                                <tr>
                                    <td>
                                        <?php echo htmlspecialchars($item['product_name'] ?? 'Unknown Product'); ?>
                                        <small class="text-muted">(ID: <?php echo $item['product_id']; ?>)</small>
                                    </td>
                                    <td><?php echo number_format($item['price'] ?? 0, 0, ',', '.') . ' ₫'; ?></td>
                                    <td><?php echo $item['quantity'] ?? 1; ?></td>
                                    <td><?php echo number_format(($item['price'] ?? 0) * ($item['quantity'] ?? 1), 0, ',', '.') . ' ₫'; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">Total:</th>
                                <th><?php echo number_format($order['total_amount'], 0, ',', '.') . ' ₫'; ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">No order items found for this order.</div>
            <?php endif; ?>
        </div>
    </div>

<?php
}

include 'footer.php';
?>