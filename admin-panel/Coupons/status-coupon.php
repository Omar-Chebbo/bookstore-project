<?php
require "../layouts/header.php";
require "../../config/config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input
    if (!isset($_POST['id'], $_POST['status'])) {
        echo "<div class='alert alert-danger'>Invalid request: Missing data.</div>";
        exit;
    }

    $id = $_POST['id'];
    $status = $_POST['status'];

    // Flip status (1 ↔ 0)
    $newStatus = $status == 1 ? 0 : 1;

    try {
        $stmt = $conn->prepare("UPDATE coupons SET status = :status WHERE id = :id");
        $success = $stmt->execute([
            ':status' => $newStatus,
            ':id' => $id
        ]);

        if ($success) {
            header("Location: " . ADMINURL . "/Coupons/show-coupon.php");
            exit;
        } else {
            echo "<div class='alert alert-danger'>Failed to update coupon status. Please try again.</div>";
        }
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Database error: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
} else {
    // Invalid request method
    header("Location: https://bookstore.kesug.com/I439-PROJECT/404.php");
    exit;
}
