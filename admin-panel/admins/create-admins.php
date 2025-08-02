<?php require "../layouts/header.php" ?>
<?php require "../../config/config.php" ?>

<?php
if (!isset($_SESSION['adminname'])) {
  header("location: " . ADMINURL . "/login-admins.php");
  exit();
}

if (isset($_POST['submit'])) {
  if (empty($_POST['adminname']) || empty($_POST['email']) || empty($_POST['password'])) {
    echo "<script>alert('One or more inputs are empty');</script>";
  } else {
    $adminname = $_POST['adminname'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $insert = $conn->prepare("INSERT INTO admins (adminname, email, mypassword) VALUES (:adminname, :email, :mypassword)");
    $insert->execute([
      ':adminname'   => $adminname,
      ':email'       => $email,
      ':mypassword'  => password_hash($password, PASSWORD_DEFAULT),
    ]);

    header("location: " . ADMINURL . "/admins/admis.php");
    exit();
  }
}
?>


  <div class="row">
    <div class="col-md-8 offset-md-2">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title mb-5 d-inline">Create Admins</h5>
          <form method="POST" action="create-admins.php">
            <div class="form-outline mb-4">
              <input type="text" name="adminname" class="form-control" placeholder="Username" />
            </div>
            <div class="form-outline mb-4">
              <input type="email" name="email" class="form-control" placeholder="Email" />
            </div>
            <div class="form-outline mb-4">
              <input type="password" name="password" class="form-control" placeholder="Password" />
            </div>
            <button type="submit" name="submit" class="btn btn-primary mb-4">Create</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require "../layouts/footer.php" ?>
