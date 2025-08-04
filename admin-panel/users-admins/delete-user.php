<?php
require '../../config/config.php';

header('Content-Type: application/json');

if (!isset($_POST['id'])) {
    echo json_encode(['success' => false, 'error' => 'No ID']);
    exit;
}

$id = $_POST['id'];

$stmt = $conn->prepare("DELETE FROM users WHERE id = :id");

$success = $stmt->execute([":id" => $id]);

echo json_encode(['success' => $success]);?>