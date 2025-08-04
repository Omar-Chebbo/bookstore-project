<?php
// toggle-ban.php
require '../../config/config.php';

header('Content-Type: application/json');

try {
    // Check for POST id
    if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
        throw new Exception("Invalid or missing user ID.");
    }

    $userId = (int) $_POST['id'];

    // 1. Get current ban status
    $stmt = $conn->prepare("SELECT is_banned FROM users WHERE id = :id");
    $stmt->execute([':id' => $userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception("User not found.");
    }

    // 2. Toggle ban status
    $newStatus = $user['is_banned'] == 1 ? 0 : 1;

    $update = $conn->prepare("UPDATE users SET is_banned = :new_status WHERE id = :id");
    $update->execute([
        ':new_status' => $newStatus,
        ':id' => $userId
    ]);

    echo json_encode([
        'success' => true,
        'newStatus' => $newStatus
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}