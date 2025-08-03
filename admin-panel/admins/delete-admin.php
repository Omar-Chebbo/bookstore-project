<?php
require "../layouts/header.php";
require "../../config/config.php";

if (!isset($_SESSION['admin_id'], $_SESSION['admin_role'])) {
    header("Location: " . ADMINURL . "/admins/login-admins.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_id = $_POST['admin_id'] ?? null;
    $currentUserId = $_SESSION['admin_id'];

    if (!$admin_id) {
        echo "<script>alert('Invalid request');window.history.back();</script>";
        exit();
    }

    // Prevent deleting Admin #1 or self
    if ($admin_id == 1) {
        echo "<script>alert('Cannot delete Admin #1');window.history.back();</script>";
        exit();
    }
    if ($admin_id == $currentUserId) {
        echo "<script>alert('You cannot delete yourself');window.history.back();</script>";
        exit();
    }

    try {
        $stmt = $conn->prepare("DELETE FROM admins WHERE id = :id");
        $stmt->execute([':id' => $admin_id]);

        header("Location: admins.php");
        exit();
    } catch (PDOException $e) {
        echo "<script>alert('Database error: " . $e->getMessage() . "');window.history.back();</script>";
    }
} else {
    header("Location: admins.php");
    exit();
}
