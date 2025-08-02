<?php
require "../../config/config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    try {
        $id = intval($_POST['id']);

        // Get current status
        $stmt = $conn->prepare("SELECT status FROM products WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $product = $stmt->fetch(PDO::FETCH_OBJ);

        if ($product) {
            // Toggle status
            $newStatus = $product->status ? 0 : 1;

            $update = $conn->prepare("UPDATE products SET status = :status WHERE id = :id");
            $update->execute([':status' => $newStatus, ':id' => $id]);
        }

        header("Location: show-products.php");
        exit;

    } catch (PDOException $e) {
        echo "Error updating product status: " . $e->getMessage();
    }
} else {
    header("Location: show-products.php");
    exit;
}
?>
