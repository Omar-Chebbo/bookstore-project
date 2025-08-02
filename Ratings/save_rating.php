<?php
session_start();
require './config/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'You must be logged in to rate']);
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = $_POST['product_id'] ?? null;
$rating = $_POST['rating'] ?? null;

// Validate input
if (!$product_id || !$rating || !in_array($rating, [1,2,3,4,5])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

// Check if rating exists
$sqlCheck = "SELECT id FROM ratings WHERE user_id = :user_id AND product_id = :product_id";
$stmtCheck = $conn->prepare($sqlCheck);
$stmtCheck->execute([':user_id' => $user_id, ':product_id' => $product_id]);
$existing = $stmtCheck->fetch();

if ($existing) {
    // Update rating
    $sqlUpdate = "UPDATE ratings SET rating = :rating, updated_at = NOW() WHERE id = :id";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->execute([':rating' => $rating, ':id' => $existing->id]);
} else {
    // Insert new rating
    $sqlInsert = "INSERT INTO ratings (user_id, product_id, rating) VALUES (:user_id, :product_id, :rating)";
    $stmtInsert = $conn->prepare($sqlInsert);
    $stmtInsert->execute([':user_id' => $user_id, ':product_id' => $product_id, ':rating' => $rating]);
}

echo json_encode(['success' => true]);
