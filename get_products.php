<?php
require_once 'database.php';

header('Content-Type: application/json');

try {
    $products = getAllLaptops();
    echo json_encode($products);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
