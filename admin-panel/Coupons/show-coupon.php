<?php require "../layouts/header.php"; ?>
<?php require "../../config/config.php"; ?>

<?php
if (!isset($_SESSION['adminname'])) {
  header("location: " . ADMINURL . "/admins/login-admins.php");
  exit;
}

$select = $conn->query("SELECT * FROM coupons");
$select->execute();
$coupouns = $select->fetchAll(PDO::FETCH_OBJ);
?>

<div class="row">
  <div class="col">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title mb-4 d-inline">Coupons</h5>
        <a href="<?php echo ADMINURL; ?>/Coupons/create-coupon.php" class="btn btn-primary mb-4 float-right">Create Coupon</a>

        <table class="table">
          <thead>
            <tr>
              <th>#</th>
              <th>Code</th>
              <th>Discount (%)</th>
              <th>Usage Limit</th>
              <th>Expires At</th>
              <th>Status</th>
              <th>Edit</th>
              <th>Delete</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($coupouns as $coupon): ?>
              <tr>
                <th><?php echo $coupon->id; ?></th>
                <td><?php echo htmlspecialchars($coupon->code); ?></td>
                <td><?php echo $coupon->percentage; ?></td>
                <td><?php echo $coupon->usage_limit; ?></td>
                <td><?php echo $coupon->expires_at; ?></td>

                <!-- Toggle Status Form -->
                <td>
                  <form method="POST" action="<?php echo ADMINURL; ?>/Coupons/status-coupon.php">
                    <input type="hidden" name="id" value="<?php echo $coupon->id; ?>">
                    <input type="hidden" name="status" value="<?php echo $coupon->status; ?>">
                    <?php if ($coupon->status): ?>
                      <button type="submit" class="btn btn-danger">Unverify</button>
                    <?php else: ?>
                      <button type="submit" class="btn btn-success">Verify</button>
                    <?php endif; ?>
                  </form>
                </td>

                <!-- Edit Button -->
                <td>
                  <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#editModal-<?php echo $coupon->id; ?>">
                    Edit
                  </button>
                </td>

                <!-- Delete Form -->
                <td>
                  <form method="POST" action="<?php echo ADMINURL; ?>/Coupons/delete-coupon.php" onsubmit="return confirm('Are you sure you want to delete this coupon?');">
                    <input type="hidden" name="id" value="<?php echo $coupon->id; ?>">
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
<?php foreach ($coupouns as $coupon): ?>
  <div class="modal fade" id="editModal-<?php echo $coupon->id; ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel-<?php echo $coupon->id; ?>" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <form method="POST" action="<?php echo ADMINURL; ?>/Coupons/edit-coupon.php">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Edit Coupon</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span>&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="id" value="<?php echo $coupon->id; ?>">

            <div class="form-group">
              <label>Code</label>
              <input type="text" name="code" class="form-control" value="<?php echo htmlspecialchars($coupon->code); ?>" required>
            </div>

            <div class="form-group">
              <label>Discount (%)</label>
              <input type="number" name="percentage" class="form-control" value="<?php echo $coupon->percentage; ?>" required>
            </div>

            <div class="form-group">
              <label>Usage Limit</label>
              <input type="number" name="usage_limit" class="form-control" value="<?php echo $coupon->usage_limit; ?>" required>
            </div>

            <div class="form-group">
              <label>Expires At</label>
              <input type="date" name="expires_at" class="form-control" value="<?php echo $coupon->expires_at; ?>" required>
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
