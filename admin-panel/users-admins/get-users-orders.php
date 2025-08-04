<?php
require '../../config/config.php';
header('Content-Type: application/json');

try {
    if (!isset($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
        throw new Exception("Invalid user ID");
    }
    $userId = (int) $_GET['user_id'];

    $stmt = $conn->prepare("SELECT id,  created_at,price FROM orders WHERE user_id = :user_id ORDER BY created_at DESC");
    $stmt->execute([':user_id' => $userId]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'orders' => $orders
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}