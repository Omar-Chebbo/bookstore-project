<?php require "../layouts/header.php"; ?>
<?php require "../../config/config.php"; ?>

<?php
// Check admin login
if (!isset($_SESSION['adminname'])) {
    header("location: " . ADMINURL . "/admins/login-admins.php");
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $image = $_FILES['image']['name'];
    $image_tmp = $_FILES['image']['tmp_name'];
    $image_path = "images/" . basename($image);

    // Validate inputs
    if (empty($name) || empty($description) || empty($image)) {
        $errors[] = "All fields are required.";
    }

    // Insert into database if no errors
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("INSERT INTO categories (name, description, image) VALUES (:name, :description, :image)");
            $stmt->execute([
                ":name" => $name,
                ":description" => $description,
                ":image" => $image
            ]);

            // Move image to folder
            if (move_uploaded_file($image_tmp, $image_path)) {
                header("Location: " . ADMINURL . "/categories-admins/show-categories.php");
                exit;
            } else {
                $errors[] = "Failed to upload image.";
            }
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}
?>

<div class="row">
  <div class="col">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title mb-5 d-inline">Create Category</h5>

        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
              <div><?php echo $error; ?></div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <form method="POST" action="create-category.php" enctype="multipart/form-data">
          <div class="form-group mb-3">
            <label for="name">Name</label>
            <input type="text" name="name" class="form-control" placeholder="Category name" required>
          </div>

          <div class="form-group mb-3">
            <label for="description">Description</label>
            <textarea name="description" class="form-control" rows="3" placeholder="Category description" required></textarea>
          </div>

          <div class="form-group mb-4">
            <label for="image">Image</label>
            <input type="file" name="image" class="form-control" required>
          </div>

          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Create</button>
            <a href="<?php echo ADMINURL; ?>/categories-admins/show-categories.php" class="btn btn-secondary">Cancel</a>
          </div>
        </form>

      </div>
    </div>
  </div>
</div>

<?php require "../layouts/footer.php"; ?>
