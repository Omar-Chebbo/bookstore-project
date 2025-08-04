<?php
require '../../config/config.php';
header('Content-Type: application/json');

try {
    if (!isset($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
        throw new Exception("Invalid user ID");
    }
    $userId = (int) $_GET['user_id'];

    $stmt = $conn->prepare("SELECT comment_id, content, created_at FROM comments WHERE user_id = :user_id ORDER BY created_at DESC");
    $stmt->execute([':user_id' => $userId]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'comments' => $comments
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}