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
    <title>Laptop Store</title>
    <link rel="shortcut icon" type="image/x-icon" href="./img/logo.png">
    <link rel="stylesheet" href="styles.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <!-- Bootstrap JavaScript Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="styles.css">
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
                <a href="#" class="user-menu">
                    <i class="bi bi-person-circle"></i>
                    Xin chào, <?php echo htmlspecialchars($_SESSION['user']['username']); ?>
                </a>
                <!-- <a href="logout.php">
                    <i class="bi bi-box-arrow-right"></i>
                    Đăng xuất
                </a> -->
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