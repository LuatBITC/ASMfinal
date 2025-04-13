<?php
session_start();

// Clear admin session variables
unset($_SESSION['admin']);
unset($_SESSION['admin_username']);

// Redirect to login page
header('Location: ../admin-login.php');
exit;
