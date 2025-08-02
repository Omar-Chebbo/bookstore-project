<?php require "../layouts/header.php" ?>
<?php require "../../config/config.php" ?>

<?php


if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $select = $conn->prepare("SELECT * FROM products WHERE id = :id");
    $select->execute([':id' => $id]);

    $images = $select->fetch(PDO::FETCH_OBJ);

    unlink("images/" . $images->image);
    $delete = $conn->prepare("DELETE FROM products WHERE id = :id");
    $delete->execute([':id' => $id]);

    header("Location: " . ADMINURL . "/products-admins/show-products.php");
    exit;
} else {
    header("Location: https://bookstore.kesug.com/I439-PROJECT/404.php");
    exit;
}
?>