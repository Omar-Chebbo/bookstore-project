<?php require "../layouts/header.php"; ?>
<?php require "../../config/config.php"; ?>

<?php
if (!isset($_SESSION['adminname'])) {
    header("location: " . ADMINURL . "/admins/login-admins.php");
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code']);
    $percentage = intval($_POST['percentage']);
    $usage_limit = intval($_POST['usage_limit']);
    $expires_at = $_POST['expires_at'];
    $created_at = date("Y-m-d H:i:s");
    $status = 1; // active by default

    // Basic validation
    if (empty($code) || $percentage <= 0 || $percentage > 100 || $usage_limit <= 0 || empty($expires_at)) {
        $errors[] = "Please fill out all fields correctly.";
    }

    if (empty($errors)) {
        try {
            $insert = $conn->prepare("INSERT INTO coupons (code, percentage, usage_limit, expires_at, created_at, status)
                                      VALUES (:code, :percentage, :usage_limit, :expires_at, :created_at, :status)");
            $success = $insert->execute([
                ':code' => $code,
                ':percentage' => $percentage,
                ':usage_limit' => $usage_limit,
                ':expires_at' => $expires_at,
                ':created_at' => $created_at,
                ':status' => $status
            ]);

            if ($success) {
                header("Location: " . ADMINURL . "/Coupons/show-coupon.php");
                exit;
            } else {
                $errors[] = "Failed to create coupon. Please try again.";
            }
        } catch (PDOException $e) {
            $errors[] = "Database error: " . htmlspecialchars($e->getMessage());
        }
    }
}
?>

<div class="row">
  <div class="col-6 offset-3">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title mb-4">Create Coupon</h5>

        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
              <p><?php echo $error; ?></p>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <form method="POST" action="">
          <div class="form-group">
            <label for="code">Coupon Code</label>
            <input type="text" class="form-control" name="code" required>
          </div>

          <div class="form-group">
            <label for="percentage">Discount Percentage (%)</label>
            <input type="number" class="form-control" name="percentage" min="1" max="100" required>
          </div>

          <div class="form-group">
            <label for="usage_limit">Usage Limit</label>
            <input type="number" class="form-control" name="usage_limit" min="1" required>
          </div>

          <div class="form-group">
            <label for="expires_at">Expires At</label>
            <input type="date" class="form-control" name="expires_at" required>
          </div>

          <button type="submit" class="btn btn-primary mt-3">Create Coupon</button>
          <a href="show-coupon.php" class="btn btn-secondary mt-3">Cancel</a>
        </form>

      </div>
    </div>
  </div>
</div>

<?php require "../layouts/footer.php"; ?>
