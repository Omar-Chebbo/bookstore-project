<?php require "../layouts/header.php" ?>
<?php require "../../config/config.php" ?>

<?php
$select = $conn->query("SELECT * FROM admins");
$select->execute();
$admins = $select->fetchAll(PDO::FETCH_OBJ);
?>

<!-- ✅ ADD CONTAINER TO FIX LAYOUT ISSUES -->
<div class="container mt-5">
  <div class="row">
    <div class="col">
      <div class="card p-3 pt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h5 class="card-title mb-0">Admins</h5>
          <a href="<?php echo ADMINURL; ?>/admins/create-admins.php" class="btn btn-primary">Create Admins</a>
        </div>
        <table class="table">
          <thead>
            <tr>
              <th scope="col">#</th>
              <th scope="col">Admin Name</th>
              <th scope="col">Email</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($admins as $admin): ?>
              <tr>
                <th scope="row"><?php echo $admin->id ?></th>
                <td><?php echo $admin->adminname ?></td>
                <td><?php echo $admin->email ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>


<?php require "../layouts/footer.php"; ?>