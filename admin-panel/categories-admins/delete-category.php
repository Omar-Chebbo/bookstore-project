<?php
require "../layouts/header.php";
require "../../config/config.php"; ?>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id = intval($_POST['id']);

        if (!$id) {
            throw new Exception("Invalid category ID.");
        }

        // Step 1: Unlink this category from products
        $updateProducts = $conn->prepare("UPDATE products SET category_id = 0 WHERE category_id = :id");
        $updateProducts->execute([":id" => $id]);

        // Step 2: Delete the category
        $delete = $conn->prepare("DELETE FROM categories WHERE id = :id");
        $delete->execute([":id" => $id]);

        header("Location: " . ADMINURL . "/categories-admins/show-categories.php");
        exit;

    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>Error deleting category: " . $e->getMessage() . "</div>";
    }
} else {
    header("Location: " . ADMINURL . "/categories-admins/show-categories.php");
    exit;
}
?>
