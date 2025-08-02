<?php
require "../layouts/header.php";
require "../../config/config.php";


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id = $_POST['id'];
        $code = trim($_POST['code']);
        $percentage = intval($_POST['percentage']);
        $usage_limit = intval($_POST['usage_limit']);
        $expires_at = $_POST['expires_at'];

        // Prepare and execute update
        $update = $conn->prepare("
            UPDATE coupons 
            SET code = :code, 
                percentage = :percentage, 
                usage_limit = :usage_limit, 
                expires_at = :expires_at 
            WHERE id = :id
        ");
        $update->execute([
            ":code" => $code,
            ":percentage" => $percentage,
            ":usage_limit" => $usage_limit,
            ":expires_at" => $expires_at,
            ":id" => $id
        ]);

        // Redirect back on success
        header("Location: " . ADMINURL . "/Coupons/show-coupon.php");
        exit;

    } catch (PDOException $e) {
        // Log the error and show user-friendly message
        error_log("Database Error [edit-coupon.php]: " . $e->getMessage());
        echo "<div style='color: red; padding: 20px;'>An error occurred while updating the coupon. Please try again later.</div>";
    }
}
?>
