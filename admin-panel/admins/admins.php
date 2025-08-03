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
                  // Protect admin with id=1 from any action except himself cannot delete or revoke himself.
                  $isAdmin1 = ($admin->id == 1);
                  $isSelf = ($admin->id == $_SESSION['admin_id']);
                  $currentUserRole = $_SESSION['admin_role'];
                  $currentUserId = $_SESSION['admin_id'];

                  // Conditions to disable actions:
                  // - No one except Admin #1 can edit/delete/revoke Admin #1
                  // - Admin #1 cannot delete/revoke himself
                  // - Admin #1 can edit himself but cannot set role to Employee (handled in edit-admin.php)
                  // - Users cannot delete/revoke themselves

                  if ($isAdmin1) {
                    // Admin #1 row
                    if ($isSelf) {
                      // Admin #1 viewing himself: no delete/revoke buttons, only edit button with restrictions
                      ?>
                      <!-- Edit Button with modal -->
                      <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editModal<?= $admin->id; ?>">Edit</button>
                      <span class="text-muted ml-2">Protected</span>
                      <?php
                    } else {
                      // Other users viewing Admin #1 row: no actions allowed
                      echo "<span class='text-muted'>Protected</span>";
                    }
                  } else {
                    // For other admins, allow edit/delete/revoke but prevent users from deleting/revoking themselves
                    if (!$isSelf) {
                      // Edit button
                      ?>
                      <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editModal<?= $admin->id; ?>">Edit</button>
                      <?php
                      // Delete form
                      ?>
                      <form method="POST" action="delete-admin.php" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete this admin?');">
                        <input type="hidden" name="admin_id" value="<?= $admin->id; ?>">
                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                      </form>
                      <?php
                      // Toggle status form
                      ?>
                      <form method="POST" action="status-admin.php" class="d-inline-block">
                        <input type="hidden" name="admin_id" value="<?= $admin->id; ?>">
                        <input type="hidden" name="new_status" value="<?= $admin->status == 1 ? 0 : 1; ?>">
                        <button type="submit" class="btn btn-<?= $admin->status == 1 ? 'secondary' : 'success'; ?> btn-sm">
                          <?= $admin->status == 1 ? 'Revoke' : 'Activate'; ?>
                        </button>
                      </form>
                      <?php
                    } else {
                      // For self row, no delete/revoke buttons allowed
                      // Allow edit modal
                      ?>
                      <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editModal<?= $admin->id; ?>">Edit</button>
                      <span class="text-muted ml-2">Protected</span>
                      <?php
                    }
                  }
                  ?>
                </td>
              </tr>

              <!-- Edit Modal -->
              <?php if (!$isAdmin1 || $isSelf) : // Admin #1 can edit himself with restrictions ?>
              <div class="modal fade" id="editModal<?= $admin->id; ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel<?= $admin->id; ?>" aria-hidden="true">
                <div class="modal-dialog" role="document">
                  <form method="POST" action="edit-admin.php">
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
                          <label>Role</label>
                          <select name="role" class="form-control" required
                            <?= ($isAdmin1 && $isSelf) ? 'disabled' : ''; ?>
                          >
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
              <?php endif; ?>

            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php require "../layouts/footer.php"; ?>
