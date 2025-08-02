<?php 
require "../layouts/header.php";
require "../../config/config.php"; ?>

<?php
session_start();

// Only admins allowed
if (!isset($_SESSION['adminname'])) {
    header("location: " . ADMINURL . "/admins/login-admins.php");
    exit;
}

// Only handle POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("location: " . ADMINURL . "/categories-admins/show-categories.php");
    exit;
}

if (isset($_POST['id'], $_POST['name'], $_POST['description'])) {
    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);

    // Check for optional image upload
    $imageName = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if (in_array($ext, $allowedExtensions)) {
            $imageName = uniqid() . '.' . $ext;
            $uploadPath = __DIR__ . "/images/" . $imageName;

            if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                echo "<div class='alert alert-danger'>Failed to upload image.</div>";
                exit;
            }
        } else {
            echo "<div class='alert alert-danger'>Invalid image format. Allowed: jpg, jpeg, png, gif, webp.</div>";
            exit;
        }
    }

    try {
        if ($imageName) {
            // Update with image
            $stmt = $conn->prepare("UPDATE categories SET name = :name, description = :description, image = :image WHERE id = :id");
            $stmt->execute([
                ":name" => $name,
                ":description" => $description,
                ":image" => $imageName,
                ":id" => $id,
            ]);
        } else {
            // Update without image
            $stmt = $conn->prepare("UPDATE categories SET name = :name, description = :description WHERE id = :id");
            $stmt->execute([
                ":name" => $name,
                ":description" => $description,
                ":id" => $id,
            ]);
        }

        header("location: " . ADMINURL . "/categories-admins/show-categories.php");
        exit;
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Database error: " . $e->getMessage() . "</div>";
        exit;
    }
} else {
    echo "<div class='alert alert-danger'>Invalid form submission.</div>";
}
?>
