<?php require "../layouts/header.php" ?>
<?php require "../../config/config.php" ?>

<?php 
$select = $conn->query("SELECT * FROM categories");
$select->execute();
$categories = $select->fetchAll(PDO::FETCH_OBJ);
?>


  <div class="row">
    <div class="col">
      <div class="card">
        <div class="card-body">

          <!-- Header with Button -->
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="card-title mb-0">Categories</h5>
            <a href="<?php echo ADMINURL; ?>/categories-admins/create-category.php" class="btn btn-primary">
              Create Category
            </a>
          </div>

          <!-- Table -->
          <table class="table">
            <thead>
              <tr>
                <th scope="col">#</th>
                <th scope="col">Name</th>
                <th scope="col">Update</th>
                <th scope="col">Delete</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($categories as $category): ?>
                <tr>
                  <th scope="row"><?php echo $category->id ?></th>
                  <td><?php echo $category->name ?></td>
                  <td>
                    <a href="<?php echo ADMINURL; ?>/categories-admins/update-category.php?id=<?php echo $category->id ?>" class="btn btn-warning text-white">
                      Update
                    </a>
                  </td>
                  <td>
                    <a href="<?php echo ADMINURL; ?>/categories-admins/delete-categories.php?id=<?php echo $category->id ?>" class="btn btn-danger">
                      Delete
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>

        </div>
      </div>
    </div>
  </div>


<?php require "../layouts/footer.php"; ?>
