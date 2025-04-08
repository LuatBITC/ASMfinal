<?php
require_once 'database.php';
require_once 'mail_config.php';

// Lấy thông tin từ URL
$name = htmlspecialchars($_GET['name']);
$email = htmlspecialchars($_GET['email']);
$message = htmlspecialchars($_GET['message']);

// Nội dung email
$subject = 'Liên hệ mới từ ' . $name;
$email_content = "<h2>Bạn có một liên hệ mới</h2>";
$email_content .= "<p><strong>Tên:</strong> $name</p>";
$email_content .= "<p><strong>Email:</strong> $email</p>";
$email_content .= "<p><strong>Nội dung:</strong></p>";
$email_content .= "<p>$message</p>";

try {
    // Gửi email
    if (sendMail('lutnguyen2004@gmail.com', $subject, $email_content)) {
        // Lưu vào database
        $stmt = $pdo->prepare("INSERT INTO contacts (name, email, message) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $message]);

        // Chuyển hướng về trang contact với thông báo thành công
        header("Location: contact.php?success=1");
        exit();
    } else {
        throw new Exception("Không thể gửi email");
    }
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    header("Location: contact.php?error=1");
    exit();
}
