<?php
require "../layouts/header.php";
require "../../config/config.php";

// Ensure only logged-in admins can access
if (!isset($_SESSION['admin_id'], $_SESSION['admin_role'])) {
    header("Location: " . ADMINURL . "/admins/login-admins.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_id = $_POST['admin_id'] ?? null;
    $new_status = $_POST['new_status'] ?? null;
    $currentUserId = $_SESSION['admin_id'];

    // Validate input
    if (!$admin_id || !in_array($new_status, ['0', '1'], true)) {
        echo "<script>alert('Invalid request'); window.history.back();</script>";
        exit();
    }

    // Prevent changes to Admin #1
    if ($admin_id == 1) {
        echo "<script>alert('Cannot change status of Admin #1'); window.history.back();</script>";
        exit();
    }

    // Prevent admin from changing their own status
    if ($admin_id == $currentUserId) {
        echo "<script>alert('You cannot change your own status'); window.history.back();</script>";
        exit();
    }

    try {
        $stmt = $conn->prepare("UPDATE admins SET status = :status WHERE id = :id");
        $stmt->execute([
            ':status' => $new_status,
            ':id' => $admin_id
        ]);

        header("Location: admins.php");
        exit();

    } catch (PDOException $e) {
        echo "<script>alert('Database error: " . $e->getMessage() . "'); window.history.back();</script>";
        exit();
    }

} else {
    header("Location: admins.php");
    exit();
}
?>
