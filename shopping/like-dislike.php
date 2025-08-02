<?php
session_start();
require "../config/config.php";

if (!isset($_SESSION['user_id'], $_POST['comment_id'], $_POST['action'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
    exit;
}

$user_id = $_SESSION['user_id'];
$comment_id = $_POST['comment_id'];
$action = $_POST['action']; // 'like' or 'dislike'

// Check if user already has an interaction for this comment
$check = $conn->prepare("SELECT type FROM interactions WHERE user_id = :user_id AND comment_id = :comment_id");
$check->execute([
    ':user_id' => $user_id,
    ':comment_id' => $comment_id
]);
$existing = $check->fetch(PDO::FETCH_ASSOC);

// If same action → remove interaction (toggle)
if ($existing && $existing['type'] === $action) {
    $delete = $conn->prepare("DELETE FROM interactions WHERE user_id = :user_id AND comment_id = :comment_id");
    $delete->execute([
        ':user_id' => $user_id,
        ':comment_id' => $comment_id
    ]);
} else {
    // Insert or update interaction
    if ($existing) {
        $update = $conn->prepare("UPDATE interactions SET type = :type WHERE user_id = :user_id AND comment_id = :comment_id");
        $update->execute([
            ':type' => $action,
            ':user_id' => $user_id,
            ':comment_id' => $comment_id
        ]);
    } else {
        $insert = $conn->prepare("INSERT INTO interactions (user_id, comment_id, type) VALUES (:user_id, :comment_id, :type)");
        $insert->execute([
            ':user_id' => $user_id,
            ':comment_id' => $comment_id,
            ':type' => $action
        ]);
    }
}

// Recount likes and dislikes
$likes = $conn->prepare("SELECT COUNT(*) FROM interactions WHERE comment_id = :comment_id AND type = 'like'");
$likes->execute([':comment_id' => $comment_id]);
$likes_count = $likes->fetchColumn();

$dislikes = $conn->prepare("SELECT COUNT(*) FROM interactions WHERE comment_id = :comment_id AND type = 'dislike'");
$dislikes->execute([':comment_id' => $comment_id]);
$dislikes_count = $dislikes->fetchColumn();


echo json_encode([
    'status' => 'success',
    'likes_count' => $likes_count,
    'dislikes_count' => $dislikes_count
]);
?>