<?php
require_once '../database.php';
include 'header.php';

// Get some statistics for the dashboard
$total_products = 0;
$total_users = 0;
$total_orders = 0;
$recent_orders = [];

try {
    // Count total products
    $stmt = $pdo->query("SELECT COUNT(*) FROM laptops");
    $total_products = $stmt->fetchColumn();

    // Count total users (create a function if not exists)
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM users");
        $total_users = $stmt->fetchColumn();
    }

    // Count total orders (create a function if not exists)
    $stmt = $pdo->query("SHOW TABLES LIKE 'orders'");
    if ($stmt->rowCount() > 0) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM orders");
        $total_orders = $stmt->fetchColumn();

        // Get the column information to find the date column
        $stmt = $pdo->query("DESCRIBE orders");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Look for date column - could be order_date, created_at, date, etc.
        $date_column = 'id'; // Default fallback to id if no date column found
        $possible_date_columns = ['order_date', 'created_at', 'date', 'timestamp'];

        foreach ($possible_date_columns as $col) {
            if (in_array($col, $columns)) {
                $date_column = $col;
                break;
            }
        }

        // Get recent orders using the found date column or default to id
        $stmt = $pdo->query("SELECT * FROM orders ORDER BY $date_column DESC LIMIT 5");
        $recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    // Handle database errors
    echo "Database error: " . $e->getMessage();
}
?>

<div class="row">
    <!-- Statistics Cards -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Products</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_products; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-laptop fs-2 text-primary opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Users</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_users; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-people fs-2 text-success opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Orders</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_orders; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-cart fs-2 text-info opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Revenue (Month)</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">₫10.000.000</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-currency-dollar fs-2 text-warning opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart Row -->
<div class="row">
    <div class="col-xl-8 col-lg-7">
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-bar-chart-line me-1"></i>
                Monthly Revenue
            </div>
            <div class="card-body">
                <canvas id="revenueChart" width="100%" height="40"></canvas>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-lg-5">
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-pie-chart me-1"></i>
                Product Categories
            </div>
            <div class="card-body">
                <canvas id="categoryChart" width="100%" height="50"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent Orders Row -->
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-table me-1"></i>
                Recent Orders
            </div>
            <div class="card-body">
                <?php if (!empty($recent_orders)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_orders as $order): ?>
                                    <tr>
                                        <td>#<?php echo $order['id']; ?></td>
                                        <td><?php echo $order['full_name']; ?></td>
                                        <td><?php echo date('d M Y', strtotime($order['created_at'])); ?></td>
                                        <td><?php echo number_format($order['total_amount'], 0, ',', '.') . ' ₫'; ?></td>
                                        <td>
                                            <?php
                                            $status_class = '';
                                            switch ($order['status']) {
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
                                            <span
                                                class="badge <?php echo $status_class; ?>"><?php echo ucfirst($order['status']); ?></span>
                                        </td>
                                        <td>
                                            <a href="orders.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">No orders found.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    // Chart initialization
    document.addEventListener('DOMContentLoaded', function() {
        // Monthly Revenue Chart
        var ctx = document.getElementById('revenueChart').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov',
                    'Dec'
                ],
                datasets: [{
                    label: 'Revenue (in million VND)',
                    data: [12, 19, 14, 15, 18, 21, 22, 25, 17, 16, 23, 25],
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    pointBackgroundColor: 'rgba(54, 162, 235, 1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Product Categories Chart
        var ctxPie = document.getElementById('categoryChart').getContext('2d');
        var pieChart = new Chart(ctxPie, {
            type: 'doughnut',
            data: {
                labels: ['Gaming', 'Business', 'Student', 'Premium'],
                datasets: [{
                    data: [35, 30, 25, 10],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    });
</script>

<?php include 'footer.php'; ?>