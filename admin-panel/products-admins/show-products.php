<?php require "../layouts/header.php"; ?>
<?php require "../../config/config.php"; ?>

<?php
// Admin session check
if (!isset($_SESSION['adminname'])) {
    header("location: " . ADMINURL . "/admins/login-admins.php");
    exit;
}

// Fetch all products with category info
$query = $conn->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id");
$query->execute();
$products = $query->fetchAll(PDO::FETCH_OBJ);

// Fetch categories for edit modal dropdown
$catQuery = $conn->query("SELECT * FROM categories");
$catQuery->execute();
$categories = $catQuery->fetchAll(PDO::FETCH_OBJ);
?>

<div class="row">
  <div class="col">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title mb-4 d-inline">Products</h5>
        <a href="<?php echo ADMINURL; ?>/products-admins/create-products.php" class="btn btn-primary mb-4 float-right">Create Product</a>

        <table class="table table-bordered">
          <thead>
            <tr>
              <th>#</th>
              <th>Name</th>
              <th>Price</th>
              <th>Description</th>
              <th>Category</th>
              <th>Language</th>
              <th>Image</th>
              <th>File</th>
              <th>Status</th>
              <th>Edit</th>
              <th>Delete</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($products as $product): ?>
              <tr>
                <td><?php echo $product->id; ?></td>
                <td><?php echo htmlspecialchars($product->name); ?></td>
                <td><?php echo number_format($product->price, 2); ?></td>
                <td>
                    <?php 
                      $shortDesc = strip_tags($product->description);
                      echo strlen($shortDesc) > 50 ? substr($shortDesc, 0, 50) . '...' : $shortDesc;
                    ?>
                  </td>

                <td><?php echo htmlspecialchars($product->category_name); ?></td>
                <td><?php echo htmlspecialchars($product->language); ?></td>
                <td>
                  <?php if ($product->image): ?>
                    <img src="<?php echo ADMINURL . "/products-admins/images/" . $product->image; ?>" width="50" alt="Product Image">
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($product->file): ?>
                    <a href="<?php echo ADMINURL . "/products-admins/books/" . $product->file; ?>" target="_blank">View File</a>
                  <?php endif; ?>
                </td>
                <td>
                  <!-- Status toggle form -->
                  <form method="POST" action="<?php echo ADMINURL; ?>/products-admins/status-product.php" style="display:inline;">
                    <input type="hidden" name="id" value="<?php echo $product->id; ?>">
                    <button type="submit" class="btn btn-<?php echo $product->status ? 'success' : 'secondary'; ?>">
                      <?php echo $product->status ? 'Active' : 'Inactive'; ?>
                    </button>
                  </form>
                </td>
                <td>
                  <!-- Edit button triggers modal -->
                  <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#editModal-<?php echo $product->id; ?>">
                    Edit
                  </button>
                </td>
                <td>
                  <!-- Delete form -->
                  <form method="POST" action="<?php echo ADMINURL; ?>/products-admins/delete-product.php" onsubmit="return confirm('Are you sure you want to delete this product?');">
                    <input type="hidden" name="id" value="<?php echo $product->id; ?>">
                    <button type="submit" class="btn btn-danger">Delete</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Edit Modals -->
<?php foreach ($products as $product): ?>
  <div class="modal fade" id="editModal-<?php echo $product->id; ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel-<?php echo $product->id; ?>" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <form method="POST" action="<?php echo ADMINURL; ?>/products-admins/edit-product.php" enctype="multipart/form-data">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Edit Product - ID <?php echo $product->id; ?></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span>&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="id" value="<?php echo $product->id; ?>">

            <div class="form-group">
              <label>Name</label>
              <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($product->name); ?>" required>
            </div>

            <div class="form-group">
              <label>Price</label>
              <input type="number" step="0.01" name="price" class="form-control" value="<?php echo $product->price; ?>" required>
            </div>

            <div class="form-group">
              <label>Description</label>
              <textarea name="description" class="form-control" rows="3" required><?php echo htmlspecialchars($product->description); ?></textarea>
            </div>

            <div class="form-group">
              <label>Category</label>
              <select name="category_id" class="form-control" required>
                <?php foreach ($categories as $category): ?>
                  <option value="<?php echo $category->id; ?>" <?php if ($category->id == $product->category_id) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($category->name); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group">
              <label>Language</label>
              <input type="text" name="language" class="form-control" value="<?php echo htmlspecialchars($product->language); ?>" required>
            </div>

            <div class="form-group">
              <label>Image (leave empty to keep current)</label>
              <input type="file" name="image" class="form-control" />
              <?php if ($product->image): ?>
                <small>Current: <?php echo htmlspecialchars($product->image); ?></small>
              <?php endif; ?>
            </div>

            <div class="form-group">
              <label>File (leave empty to keep current)</label>
              <input type="file" name="file" class="form-control" />
              <?php if ($product->file): ?>
                <small>Current: <?php echo htmlspecialchars($product->file); ?></small>
              <?php endif; ?>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Save Changes</button>
          </div>
        </div>
      </form>
    </div>
  </div>
<?php endforeach; ?>

<?php require "../layouts/footer.php"; ?>
