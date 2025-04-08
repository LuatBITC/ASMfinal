<?php
require_once 'components/header.php';
require_once 'database.php';

$success_message = '';
$error_message = '';

if (isset($_GET['success'])) {
    $success_message = 'Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi sớm nhất có thể.';
}

if (isset($_GET['error'])) {
    $error_message = 'Đã có lỗi xảy ra khi gửi liên hệ. Vui lòng thử lại sau.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $message = htmlspecialchars($_POST['message']);

    if (empty($name) || empty($email) || empty($message)) {
        $error_message = 'Vui lòng điền đầy đủ thông tin.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Email không hợp lệ.';
    } else {
        // Chuyển hướng đến script gửi email
        header("Location: send_email.php?name=$name&email=$email&message=$message");
        exit();
    }
}
?>

<main class="cart-content bg-light py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h2 class="mb-4">Liên hệ với chúng tôi</h2>

            <?php if ($success_message): ?>
                <div class="alert alert-success"><?= $success_message ?></div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-danger"><?= $error_message ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label for="name" class="form-label">Họ và tên</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>

                <div class="mb-3">
                    <label for="message" class="form-label">Nội dung</label>
                    <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Gửi liên hệ</button>
            </form>
        </div>
    </div>
</main>

<?php include 'components/footer.php'; ?>