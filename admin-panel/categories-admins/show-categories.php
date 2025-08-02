<?php require "../layouts/header.php"; ?>
<?php require "../../config/config.php"; ?>

<?php
if (!isset($_SESSION['adminname'])) {
    header("location: " . ADMINURL . "/admins/login-admins.php");
    exit;
}

try {
    $select = $conn->query("SELECT * FROM categories");
    $select->execute();
    $categories = $select->fetchAll(PDO::FETCH_OBJ);
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Database error: " . $e->getMessage() . "</div>";
    die();
}
?>

<div class="row">
  <div class="col">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title mb-4 d-inline">Categories</h5>
        <a href="<?php echo ADMINURL; ?>/categories-admins/create-category.php" class="btn btn-primary mb-4 float-right">Create Category</a>

        <table class="table table-bordered">
          <thead>
            <tr>
              <th>#</th>
              <th>Name</th>
              <th>Description</th>
              <th>Image</th>
              <th>Edit</th>
              <th>Delete</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($categories as $category): ?>
              <tr>
                <td><?= $category->id; ?></td>
                <td><?= htmlspecialchars($category->name); ?></td>
                <td>
                  <?php
                    $shortDesc = strip_tags($category->description);
                    echo strlen($shortDesc) > 50 ? substr($shortDesc, 0, 50) . '...' : $shortDesc;
                  ?>
                </td>
                <td>
                  <?php if ($category->image): ?>
                    <img src="<?= ADMINURL . "/categories-admins/images/" . htmlspecialchars($category->image); ?>" width="50">
                  <?php else: ?>
                    <span class="text-muted">No Image</span>
                  <?php endif; ?>
                </td>
                <td>
                  <button type="button" class="btn btn-warning text-white" data-toggle="modal" data-target="#editModal-<?= $category->id; ?>">
                    Edit
                  </button>
                </td>
                <td>
                  <form method="POST" action="<?= ADMINURL; ?>/categories-admins/delete-category.php" onsubmit="return confirm('Are you sure you want to delete this category?');">
                    <input type="hidden" name="id" value="<?= $category->id; ?>">
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
<?php foreach ($categories as $category): ?>
  <div class="modal fade" id="editModal-<?= $category->id; ?>" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <form method="POST" action="<?= ADMINURL; ?>/categories-admins/edit-category.php" enctype="multipart/form-data">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Edit Category - ID <?= $category->id; ?></h5>
            <button type="button" class="close" data-dismiss="modal">
              <span>&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="id" value="<?= $category->id; ?>">

            <div class="form-group">
              <label>Name</label>
              <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($category->name); ?>" required>
            </div>

            <div class="form-group">
              <label>Description</label>
              <textarea name="description" class="form-control" rows="3" required><?= htmlspecialchars($category->description); ?></textarea>
            </div>

            <div class="form-group">
              <label>Image</label>
              <input type="file" name="image" class="form-control">
              <?php if ($category->image): ?>
                <img src="<?= ADMINURL . "/categories-admins/images/" . htmlspecialchars($category->image); ?>" width="50" class="mt-2">
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
