<?php require "../layouts/header.php"; ?>
<?php require "../../config/config.php"; ?>

<?php
if (isset($_POST['submit'])) {

  if (empty($_POST['email']) || empty($_POST['password'])) {
    echo "<script>alert('One or more inputs are empty');</script>";
  } else {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    try {
      $login = $conn->prepare("SELECT * FROM admins WHERE email = :email");
      $login->execute([':email' => $email]);
      $fetch = $login->fetch(PDO::FETCH_ASSOC);

      if ($fetch && password_verify($password, $fetch['mypassword'])) {

        //  Prevent login if account is revoked
        if ((int)$fetch['status'] !== 1) {
          echo "<script>alert('Your account has been revoked. Contact a manager.');</script>";
          exit;
        }

        //  Set session
        $_SESSION['adminname'] = $fetch['adminname'];
        $_SESSION['admin_id'] = $fetch['id'];
        $_SESSION['admin_role'] = $fetch['role'];

        header("Location: " . ADMINURL);
        exit;

      } else {
        echo "<script>alert('Email or password is incorrect');</script>";
      }

    } catch (PDOException $e) {
      die("Database error: " . $e->getMessage());
    }
  }
}
?>

<!-- Login Form -->
<div class="row">
  <div class="col">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title mt-5">Login</h5>
        <form method="POST" action="login-admins.php">

          <!-- Email -->
          <div class="form-outline mb-4">
            <input type="email" name="email" class="form-control" placeholder="Email" required />
          </div>

          <!-- Password -->
          <div class="form-outline mb-4">
            <input type="password" name="password" class="form-control" placeholder="Password" required />
          </div>

          <!-- Submit -->
          <button type="submit" name="submit" class="btn btn-primary mb-4">Login</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require "../layouts/footer.php"; ?>
