<?php require "config/config.php"; ?>
<?php require "includes/header.php"; ?>
<?php


if(!isset($_SERVER['HTTP_REFERER'])){
  header("location: ".APPURL."/");
}

$select = $conn->query("SELECT * FROM cart WHERE user_id = '$_SESSION[user_id]'");
$select->execute();
$allProducts = $select->fetchAll(PDO::FETCH_OBJ);

$delete = $conn->prepare("DELETE FROM cart WHERE user_id = :user_id");
$delete->execute(['user_id' => $_SESSION['user_id']]);



$zipname = 'books.zip';
$zip = new ZipArchive;
$zip->open($zipname, ZipArchive::CREATE);
foreach ($allProducts as $product) {
  $zip->addFile("admin-panel/products-admins/books/" . $product->pro_file);
}
$zip->close();



header('Content-Type: application/zip');
header('Content-disposition: attachment; filename=' . $zipname);
header('Content-Length: ' . filesize($zipname));
readfile($zipname);

?>




