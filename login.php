<?php
session_start();
require_once 'database.php';

$error = '';
$success = '';

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $user = loginUser($username, $password);

    if ($user) {
        $_SESSION['user'] = $user;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid username or password';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Laptop Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" size="32" href="https://i.imgur.com/YB9TNuG.png" data-hid="63b4eb3">

    <style>
    body {
        background: #f0f2f5;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .login-container {
        background: white;
        border-radius: 16px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 960px;
        display: flex;
        overflow: hidden;
    }

    .login-image {
        width: 50%;
        background: #f5f5f5;
    }

    .login-form {
        width: 50%;
        padding: 48px;
    }

    .form-title {
        font-size: 28px;
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 8px;
    }

    .form-subtitle {
        color: #666;
        margin-bottom: 32px;
    }

    .form-label {
        font-weight: 500;
        color: #1a1a1a;
        margin-bottom: 8px;
    }

    .form-control {
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 12px 16px;
        height: auto;
        font-size: 15px;
    }

    .form-control:focus {
        border-color: #1a73e8;
        box-shadow: 0 0 0 2px rgba(26, 115, 232, 0.2);
    }

    .input-group-text {
        background: #f8f9fa;
        border: 1px solid #e0e0e0;
        border-radius: 8px 0 0 8px;
        color: #666;
    }

    .remember-forgot {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: 16px 0 24px;
    }

    .form-check-label {
        color: #666;
        font-size: 14px;
    }

    .forgot-link {
        color: #1a73e8;
        text-decoration: none;
        font-size: 14px;
    }

    .btn-login {
        background: #1a73e8;
        border: none;
        border-radius: 8px;
        padding: 12px;
        font-weight: 500;
        width: 100%;
        margin-bottom: 24px;
    }

    .btn-login:hover {
        background: #1557b0;
    }

    .divider {
        text-align: center;
        margin: 24px 0;
        position: relative;
    }

    .divider::before,
    .divider::after {
        content: '';
        position: absolute;
        top: 50%;
        width: calc(50% - 80px);
        height: 1px;
        background: #e0e0e0;
    }

    .divider::before {
        left: 0;
    }

    .divider::after {
        right: 0;
    }

    .divider span {
        background: white;
        padding: 0 16px;
        color: #666;
        font-size: 14px;
        text-transform: uppercase;
    }

    .btn-google {
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 12px;
        width: 100%;
        color: #666;
        font-weight: 500;
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-google:hover {
        background: #f8f9fa;
    }

    .register-link {
        text-align: center;
        margin-top: 24px;
        font-size: 14px;
        color: #666;
    }

    .register-link a {
        color: #1a73e8;
        text-decoration: none;
        font-weight: 500;
    }

    @media (max-width: 768px) {
        .login-image {
            display: none;
        }

        .login-form {
            width: 100%;
            padding: 32px;
        }
    }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-image"><img src="https://ableproadmin.com/assets/images/authentication/img-auth-sideimg.jpg"
                alt="Login Image" width="100%" height="100%"></div>
        <div class="login-form">
            <h1 class="form-title">Welcome Back!</h1>
            <p class="form-subtitle">Please login to your account</p>

            <?php if ($error): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error; ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label">Username or Email</label>
                    <input type="text" class="form-control" name="username" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-control" name="password" required>
                </div>

                <div class="remember-forgot">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                    <a href="#" class="forgot-link">Forgot Password?</a>
                </div>

                <button type="submit" name="login" class="btn btn-login text-white">LOGIN</button>
            </form>

            <div class="divider">
                <span>OR CONTINUE WITH</span>
            </div>

            <button class="btn btn-google">
                <img src="https://www.google.com/favicon.ico" alt="Google" width="18" height="18">
                Login with Google
            </button>

            <div class="register-link">
                Don't have an account? <a href="register.php">Register</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>