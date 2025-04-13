<?php
// Check if admin is logged in
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Location: ../admin-login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Laptop Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --sidebar-width: 250px;
        }

        body {
            min-height: 100vh;
            background-color: #f8f9fa;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background-color: #212529;
            padding-top: 20px;
            overflow-y: auto;
        }

        .sidebar .brand {
            color: white;
            text-align: center;
            padding-bottom: 20px;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 10px 20px;
            margin-bottom: 5px;
            border-radius: 0;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .sidebar .nav-link i {
            width: 25px;
            margin-right: 10px;
        }

        .sidebar-section {
            padding: 10px 20px;
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 20px;
        }

        .top-nav {
            background-color: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 15px 30px;
            margin-bottom: 30px;
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }

        .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            font-weight: 600;
            padding: 15px 20px;
        }

        .table {
            margin-bottom: 0;
        }

        .table th {
            font-weight: 600;
            border-top: none;
        }

        .badge-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: 500;
        }

        @media (max-width: 992px) {
            .sidebar {
                width: 80px;
            }

            .sidebar .brand-name,
            .sidebar .nav-text {
                display: none;
            }

            .sidebar .nav-link {
                text-align: center;
                padding: 15px 0;
            }

            .sidebar .nav-link i {
                margin-right: 0;
                font-size: 1.2rem;
            }

            .main-content {
                margin-left: 80px;
            }

            .sidebar-section {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <div class="brand">
            <i class="bi bi-shield-lock fs-2"></i>
            <div class="brand-name">Admin Panel</div>
        </div>
        <div class="sidebar-section">Main</div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>"
                    href="index.php">
                    <i class="bi bi-speedometer2"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>"
                    href="products.php">
                    <i class="bi bi-laptop"></i>
                    <span class="nav-text">Products</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>"
                    href="orders.php">
                    <i class="bi bi-cart"></i>
                    <span class="nav-text">Orders</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-section">System</div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="logout.php">
                    <i class="bi bi-box-arrow-right"></i>
                    <span class="nav-text">Logout</span>
                </a>
            </li>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-nav">
            <h4 class="mb-0"><?php echo ucfirst(str_replace('.php', '', basename($_SERVER['PHP_SELF']))); ?></h4>
            <div class="user-info">
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
            </div>
        </div>

        <div class="container-fluid">