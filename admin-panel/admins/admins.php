<?php
require "../layouts/header.php";
require "../../config/config.php";

if (!isset($_SESSION['adminname'], $_SESSION['admin_id'], $_SESSION['admin_role'])) {
    header("location: " . ADMINURL . "/admins/login-admins.php");
    exit();
}

try {
    $stmt = $conn->query("SELECT * FROM admins ORDER BY id ASC");
    $allAdmins = $stmt->fetchAll(PDO::FETCH_OBJ);
} catch (PDOException $e) {
    echo "<script>alert('Database error: " . $e->getMessage() . "');</script>";
    exit();
}
?>

<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title d-inline">Admins</h5>
        <a href="create-admins.php" class="btn btn-primary mb-3 float-right">Create Admin</a>
        <table class="table table-bordered table-hover text-center">
          <thead>
            <tr>
              <th>ID</th>
              <th>Admin Name</th>
              <th>Email</th>
              <th>Role</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($allAdmins as $admin) : ?>
              <tr>
                <td><?= htmlspecialchars($admin->id); ?></td>
                <td><?= htmlspecialchars($admin->adminname); ?></td>
                <td><?= htmlspecialchars($admin->email); ?></td>
                <td><?= htmlspecialchars($admin->role); ?></td>
                <td>
                  <?php if ($admin->status == 1) : ?>
                    <span class="badge badge-success">Active</span>
                  <?php else : ?>
                    <span class="badge badge-danger">Revoked</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php
                  $isAdmin1 = ($admin->id == 1);
                  $isSelf = ($admin->id == $_SESSION['admin_id']);

                  if ($isAdmin1) {
                    if ($isSelf) {
                      ?>
                      <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editModal<?= $admin->id; ?>">Edit</button>
                      <span class="text-muted ml-2">Protected</span>
                      <?php
                    } else {
                      echo "<span class='text-muted'>Protected</span>";
                    }
                  } else {
                    if (!$isSelf) {
                      ?>
                      <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editModal<?= $admin->id; ?>">Edit</button>
                      <form method="POST" action="delete-admin.php" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete this admin?');">
                        <input type="hidden" name="admin_id" value="<?= $admin->id; ?>">
                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                      </form>
                      <form method="POST" action="status-admin.php" class="d-inline-block">
                        <input type="hidden" name="admin_id" value="<?= $admin->id; ?>">
                        <input type="hidden" name="new_status" value="<?= $admin->status == 1 ? 0 : 1; ?>">
                        <button type="submit" class="btn btn-<?= $admin->status == 1 ? 'secondary' : 'success'; ?> btn-sm">
                          <?= $admin->status == 1 ? 'Revoke' : 'Activate'; ?>
                        </button>
                      </form>
                      <?php
                    } else {
                      ?>
                      <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editModal<?= $admin->id; ?>">Edit</button>
                      <span class="text-muted ml-2">Protected</span>
                      <?php
                    }
                  }
                  ?>
                </td>
              </tr>

              <?php if (!$isAdmin1 || $isSelf) : ?>
              <div class="modal fade" id="editModal<?= $admin->id; ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel<?= $admin->id; ?>" aria-hidden="true">
                <div class="modal-dialog" role="document">
                  <form method="POST" action="edit-admin.php" onsubmit="return validateForm<?= $admin->id ?>()">
                    <input type="hidden" name="admin_id" value="<?= $admin->id; ?>">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title">Edit Admin</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                      </div>
                      <div class="modal-body">
                        <div class="form-group">
                          <label>Admin Name</label>
                          <input type="text" name="adminname" class="form-control" value="<?= htmlspecialchars($admin->adminname); ?>" required>
                        </div>
                        <div class="form-group">
                          <label>Email</label>
                          <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($admin->email); ?>" required>
                        </div>
                        <div class="form-group">
                          <label>New Password <small class="text-muted">(Leave blank to keep current password)</small></label>
                          <input type="password" name="password" id="password<?= $admin->id ?>" class="form-control" placeholder="Optional, min 6 characters">
                        </div>
                        <div class="form-group">
                          <label>Confirm Password</label>
                          <input type="password" name="confirm_password" id="confirm_password<?= $admin->id ?>" class="form-control" placeholder="Repeat new password">
                          <small id="error<?= $admin->id ?>" class="form-text text-danger d-none">Passwords do not match or too short.</small>
                        </div>
                        <div class="form-group">
                          <label>Role</label>
                          <select name="role" class="form-control" required <?= ($isAdmin1 && $isSelf) ? 'disabled' : ''; ?> >
                            <option value="Manager" <?= $admin->role == 'Manager' ? 'selected' : ''; ?>>Manager</option>
                            <option value="Employee" <?= $admin->role == 'Employee' ? 'selected' : ''; ?>>Employee</option>
                          </select>
                          <?php if ($isAdmin1 && $isSelf): ?>
                            <input type="hidden" name="role" value="Manager">
                            <small class="form-text text-muted">Role cannot be changed from Manager.</small>
                          <?php endif; ?>
                        </div>
                      </div>
                      <div class="modal-footer">
                        <button type="submit" name="update_admin" class="btn btn-primary">Update</button>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
              <script>
                function validateForm<?= $admin->id ?>() {
                  const password = document.getElementById('password<?= $admin->id ?>').value;
                  const confirm = document.getElementById('confirm_password<?= $admin->id ?>').value;
                  const error = document.getElementById('error<?= $admin->id ?>');

                  if (password.length > 0 || confirm.length > 0) {
                    if (password.length < 6 || password !== confirm) {
                      error.classList.remove('d-none');
                      return false;
                    }
                  }

                  error.classList.add('d-none');
                  return true;
                }
              </script>
              <?php endif; ?>

            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php require "../layouts/footer.php"; ?>
