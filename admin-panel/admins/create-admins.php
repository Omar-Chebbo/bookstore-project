<?php require "../layouts/header.php"; ?>
<?php require "../../config/config.php"; ?>

<?php
session_start();

//  Ensure only logged-in admins can access
if (!isset($_SESSION['adminname']) || $_SESSION['role'] === 'Employee') {
  header("Location: " . ADMINURL);
  exit;
}

if (isset($_POST['submit'])) {
  
  if (
    empty($_POST['adminname']) ||
    empty($_POST['email']) ||
    empty($_POST['password']) ||
    empty($_POST['role'])
  ) {
    echo "<script>alert('All fields are required');</script>";
  } else {
    try {
      $adminname = trim($_POST['adminname']);
      $email = trim($_POST['email']);
      $password = $_POST['password'];
      $role = $_POST['role'];

      
      $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

      
      $insert = $conn->prepare("INSERT INTO admins (adminname, email, mypassword, role) VALUES (:adminname, :email, :mypassword, :role)");
      $insert->execute([
        ':adminname' => $adminname,
        ':email' => $email,
        ':mypassword' => $hashedPassword,
        ':role' => $role
      ]);

      
      header("Location: " . ADMINURL . "/admins/admins.php");
      exit;

    } catch (PDOException $e) {
      die("Database error: " . $e->getMessage());
    }
  }
}
?>

<!--  Create Admin Form -->
<div class="row">
  <div class="col-md-8 offset-md-2">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title mb-5 d-inline">Create Admin</h5>
        <form method="POST" action="create-admins.php">

          <!-- Admin Name -->
          <div class="form-outline mb-4">
            <input type="text" name="adminname" class="form-control" placeholder="Username" />
          </div>

          <!-- Email -->
          <div class="form-outline mb-4">
            <input type="email" name="email" class="form-control" placeholder="Email" />
          </div>

          <!-- Password -->
          <div class="form-outline mb-4">
            <input type="password" name="password" class="form-control" placeholder="Password" />
          </div>

          <!-- Role Selection -->
          <div class="form-outline mb-4">
            <select name="role" class="form-control">
              <option value="">Select Role</option>
              <option value="Manager">Manager</option>
              <option value="Employee">Employee</option>
            </select>
          </div>

          <!-- Submit -->
          <button type="submit" name="submit" class="btn btn-primary mb-4">Create</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require "../layouts/footer.php"; ?>
