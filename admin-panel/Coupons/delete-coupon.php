<?php
require "../layouts/header.php";
require "../../config/config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];

    try {
        $stmt = $conn->prepare("DELETE FROM coupons WHERE id = :id");
        $success = $stmt->execute([':id' => $id]);

        if ($success) {
            header("Location: " . ADMINURL . "/Coupons/show-coupon.php");
            exit;
        } else {
            // Deletion failed
            echo "<div class='alert alert-danger'>Failed to delete the coupon. Please try again later.</div>";
        }
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Database error: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
} else {
    header("Location: https://bookstore.kesug.com/I439-PROJECT/404.php");
    exit;
}
?>