<?php
require "../../config/config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id = intval($_POST['id']);
        $name = trim($_POST['name']);
        $price = floatval($_POST['price']);
        $description = trim($_POST['description']);
        $language = trim($_POST['language']);
        $category_id = intval($_POST['category_id']);

        // Begin building SQL and params
        $sql = "UPDATE products SET name = :name, price = :price, description = :description, language = :language, category_id = :category_id";
        $params = [
            ':name' => $name,
            ':price' => $price,
            ':description' => $description,
            ':language' => $language,
            ':category_id' => $category_id,
            ':id' => $id
        ];

        // Handle image upload if any
        if (!empty($_FILES['image']['name'])) {
            $image = basename($_FILES['image']['name']);
            $imageDir = "images/" . $image;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $imageDir)) {
                $sql .= ", image = :image";
                $params[':image'] = $image;
            }
        }

        // Handle file upload if any
        if (!empty($_FILES['file']['name'])) {
            $file = basename($_FILES['file']['name']);
            $fileDir = "books/" . $file;
            if (move_uploaded_file($_FILES['file']['tmp_name'], $fileDir)) {
                $sql .= ", file = :file";
                $params[':file'] = $file;
            }
        }

        $sql .= " WHERE id = :id";

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        header("Location: show-products.php");
        exit;

    } catch (PDOException $e) {
        echo "Error editing product: " . $e->getMessage();
    }
} else {
    header("Location: show-products.php");
    exit;
}
?>
