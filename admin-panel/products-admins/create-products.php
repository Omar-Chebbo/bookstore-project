<?php
require "../../config/config.php";

// Fetch categories for the select dropdown
$select = $conn->query("SELECT * FROM categories");
$select->execute();
$categories = $select->fetchAll(PDO::FETCH_OBJ);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    try {
        // Validate required inputs
        if (empty($_POST['name']) || empty($_POST['description']) || empty($_POST['price'])) {
            throw new Exception("One or more required fields are empty.");
        }

        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = floatval($_POST['price']);
        $category_id = intval($_POST['category_id']);
        $language = trim($_POST['language']);

        // Handle image upload
        $image = null;
        if (!empty($_FILES['image']['name'])) {
            $image = basename($_FILES['image']['name']);
            $dir_image = "images/" . $image;
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $dir_image)) {
                throw new Exception("Failed to upload image.");
            }
        }

        // Handle file upload
        $file = null;
        if (!empty($_FILES['file']['name'])) {
            $file = basename($_FILES['file']['name']);
            $dir_file = "books/" . $file;
            if (!move_uploaded_file($_FILES['file']['tmp_name'], $dir_file)) {
                throw new Exception("Failed to upload file.");
            }
        }

        // Insert into database
        $insert = $conn->prepare("INSERT INTO products (name, price, description, image, file, category_id, language, status) 
                                  VALUES (:name, :price, :description, :image, :file, :category_id, :language, 1)");
        $insert->execute([
            ':name'        => $name,
            ':price'       => $price,
            ':description' => $description,
            ':image'       => $image,
            ':file'        => $file,
            ':category_id' => $category_id,
            ':language'    => $language
        ]);

        // Redirect after success
        header("Location: show-products.php");
        exit;

    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
    } catch (PDOException $e) {
        $errorMessage = "Database error: " . $e->getMessage();
    }
}
?>

<?php require "../layouts/header.php"; ?>

<div class="container mt-5">
    <h2>Create Product</h2>
    <?php if (!empty($errorMessage)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($errorMessage); ?></div>
    <?php endif; ?>

    <form method="POST" action="create-products.php" enctype="multipart/form-data">
        <div class="form-group">
            <label>Name *</label>
            <input type="text" name="name" class="form-control" placeholder="Product name" required />
        </div>

        <div class="form-group">
            <label>Price *</label>
            <input type="number" step="0.01" name="price" class="form-control" placeholder="Price" required />
        </div>

        <div class="form-group">
            <label>Description *</label>
            <textarea name="description" class="form-control" placeholder="Description" rows="3" required></textarea>
        </div>

        <div class="form-group">
            <label>Category *</label>
            <select name="category_id" class="form-control" required>
                <option value="">-- Select Category --</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category->id ?>"><?php echo htmlspecialchars($category->name); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Language</label>
            <input type="text" name="language" class="form-control" placeholder="e.g. English, Arabic" />
        </div>

        <div class="form-group">
            <label>Image</label>
            <input type="file" name="image" class="form-control" />
        </div>

        <div class="form-group">
            <label>File</label>
            <input type="file" name="file" class="form-control" />
        </div>

        <button type="submit" name="submit" class="btn btn-primary">Create Product</button>
        <a href="show-products.php" class="btn btn-secondary">Back to Products</a>
    </form>
</div>

<?php require "../layouts/footer.php"; ?>
