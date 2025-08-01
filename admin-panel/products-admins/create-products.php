<?php require "../layouts/header.php" ?>
<?php require "../../config/config.php" ?>

<?php

$select = $conn->query("SELECT * FROM categories");
$select->execute();
$categories = $select->fetchAll(PDO::FETCH_OBJ);

if (isset($_POST['submit'])) {
  if (empty($_POST['name']) || empty($_POST['description']) || empty($_POST['price'])) {
    echo "<script>alert('One or more inputs are empty');</script>";
  } else {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $image = $_FILES['image']['name'];
    $file = $_FILES['file']['name'];
    $category_id = $_POST['category_id'];
    $language = $_POST['language'];

    $dir_image = "images/" . basename($image);
    $dir_file = "books/" . basename($file);

    $insert = $conn->prepare("INSERT INTO products (name, price, description, image, file, category_id, language) 
                              VALUES (:name, :price, :description, :image, :file, :category_id, :language)");
    $insert->execute([
      ':name'        => $name,
      ':price'       => $price,
      ':description' => $description,
      ':image'       => $image,
      ':file'        => $file,
      ':category_id' => $category_id,
      ':language'    => $language
    ]);

    if (move_uploaded_file($_FILES['image']['tmp_name'], $dir_image) && move_uploaded_file($_FILES["file"]["tmp_name"], $dir_file)) {
      header("Location: " . ADMINURL . "/products-admins/show-products.php");
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Admin Panel</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet">
  <link href="../styles/style.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-1.11.1.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
</head>
<body>
<div id="wrapper">
  <nav class="navbar header-top fixed-top navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
      <a class="navbar-brand" href="#">LOGO</a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarText"
              aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarText">
        <ul class="navbar-nav side-nav">
          <li class="nav-item">
            <a class="nav-link" style="margin-left: 20px;" href="../index.html">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="../admins/admins.html" style="margin-left: 20px;">Admins</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="../categories-admins/show-categories.html" style="margin-left: 20px;">Categories</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="../products-admins/show-products.html" style="margin-left: 20px;">Products</a>
          </li>
        </ul>
        <ul class="navbar-nav ml-md-auto d-md-flex">
          <li class="nav-item">
            <a class="nav-link" href="../index.html">Home</a>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              username
            </a>
            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
              <a class="dropdown-item" href="#">Logout</a>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container-fluid">
    <div class="row">
      <div class="col">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title mb-5 d-inline">Create Products</h5>
            <form method="POST" action="create-products.php" enctype="multipart/form-data">

              <div class="form-outline mb-4 mt-4">
                <label>Name</label>
                <input type="text" name="name" class="form-control" placeholder="name" />
              </div>

              <div class="form-outline mb-4 mt-4">
                <label>Price</label>
                <input type="text" name="price" class="form-control" placeholder="price" />
              </div>

              <div class="form-group">
                <label for="exampleFormControlTextarea1">Description</label>
                <textarea name="description" class="form-control" placeholder="description" rows="3"></textarea>
              </div>

              <div class="form-group">
                <label for="exampleFormControlSelect1">Select Category</label>
                <select name="category_id" class="form-control" id="exampleFormControlSelect1">
                  <option>--select category--</option>
                  <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category->id ?>"><?php echo $category->name ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="form-outline mb-4 mt-4">
                <label>Language</label>
                <input type="text" name="language" class="form-control" placeholder="e.g. English, Arabic..." />
              </div>

              <div class="form-outline mb-4 mt-4">
                <label>Image</label>
                <input type="file" name="image" class="form-control" placeholder="image" />
              </div>

              <div class="form-outline mb-4 mt-4">
                <label>File</label>
                <input type="file" name="file" class="form-control" placeholder="file" />
              </div>

              <button type="submit" name="submit" class="btn btn-primary mb-4 text-center">Create</button>
            </form>
          </div>
        </div>
      </div>
    </div>

<?php require "../layouts/footer.php" ?>
