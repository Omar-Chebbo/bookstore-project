<?php
require "../../config/config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    try {
        $id = intval($_POST['id']);

        $delete = $conn->prepare("DELETE FROM products WHERE id = :id");
        $delete->execute([':id' => $id]);

        header("Location: show-products.php");
        exit;

    } catch (PDOException $e) {
        echo "Error deleting product: " . $e->getMessage();
    }
} else {
    header("Location: show-products.php");
    exit;
}
?>
