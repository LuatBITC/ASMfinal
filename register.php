<?php
session_start();
require_once 'database.php';

$error = '';
$success = '';

if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = $_POST['full_name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    if ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        $result = registerUser($username, $email, $password, $full_name, $phone, $address);

        if ($result === 'username_exists') {
            $error = 'Username already exists';
        } elseif ($result === 'email_exists') {
            $error = 'Email already exists';
        } elseif ($result) {
            $success = 'Registration successful! Please login.';
        } else {
            $error = 'Registration failed. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Laptop Store</title>
    <link rel="icon" type="image/png" size="32" href="https://i.imgur.com/YB9TNuG.png" data-hid="63b4eb3">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f0f2f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .register-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 960px;
            display: flex;
            overflow: hidden;
        }

        .register-image {
            width: 50%;
            background: #f5f5f5;
        }

        .register-form {
            width: 50%;
            padding: 48px;
            overflow-y: auto;
            max-height: 100vh;
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

        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }

        .btn-register {
            background: #1a73e8;
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-weight: 500;
            width: 100%;
            margin-bottom: 24px;
            color: white;
        }

        .btn-register:hover {
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

        .login-link {
            text-align: center;
            margin-top: 24px;
            font-size: 14px;
            color: #666;
        }

        .login-link a {
            color: #1a73e8;
            text-decoration: none;
            font-weight: 500;
        }

        .alert {
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 24px;
            font-size: 14px;
        }

        .password-strength {
            height: 4px;
            border-radius: 2px;
            margin-top: 8px;
            transition: all 0.3s ease;
        }

        .password-hint {
            color: #666;
            font-size: 12px;
            margin-top: 4px;
        }

        @media (max-width: 768px) {
            .register-image {
                display: none;
            }

            .register-form {
                width: 100%;
                padding: 32px;
            }
        }
    </style>
</head>

<body>
    <div class="register-container">
        <div class="register-image">
            <img src="https://ableproadmin.com/assets/images/authentication/img-auth-sideimg.jpg" alt="Register Image" width="100%" height="100%">
        </div>
        <div class="register-form">
            <h1 class="form-title">Create Account</h1>
            <p class="form-subtitle">Please fill in the form to continue</p>

            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="registerForm">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" class="form-control" name="username" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" class="form-control" name="full_name">
                </div>

                <div class="mb-3">
                    <label class="form-label">Phone</label>
                    <input type="tel" class="form-control" name="phone">
                </div>

                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <textarea class="form-control" name="address" rows="3"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-control" name="password" id="password" required>
                    <div class="password-strength bg-secondary opacity-25"></div>
                    <small class="password-hint">Password should be at least 8 characters long</small>
                </div>

                <div class="mb-4">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" name="confirm_password" required>
                </div>

                <button type="submit" name="register" class="btn btn-register">
                    Create Account
                </button>
            </form>

            <div class="divider">
                <span>OR CONTINUE WITH</span>
            </div>

            <button class="btn btn-google">
                <img src="https://www.google.com/favicon.ico" alt="Google" width="18" height="18">
                Sign up with Google
            </button>

            <div class="login-link">
                Already have an account? <a href="login.php">Login</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password strength indicator
        document.getElementById('password').addEventListener('input', function(e) {
            const password = e.target.value;
            const strength = document.querySelector('.password-strength');
            let width = 0;
            let color = 'bg-danger';

            if (password.length > 0) {
                width = 25;
                if (password.length >= 8) width = 50;
                if (/[A-Z]/.test(password)) width += 15;
                if (/[0-9]/.test(password)) width += 15;
                if (/[^A-Za-z0-9]/.test(password)) width += 20;

                if (width >= 80) color = 'bg-success';
                else if (width >= 50) color = 'bg-warning';
            }

            strength.style.width = width + '%';
            strength.className = `password-strength ${color}`;
        });

        // Form validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.querySelector('input[name="password"]').value;
            const confirm = document.querySelector('input[name="confirm_password"]').value;

            if (password !== confirm) {
                e.preventDefault();
                alert('Passwords do not match!');
            }
        });
    </script>
</body>

</html>