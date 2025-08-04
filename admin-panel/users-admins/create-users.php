<?php
require '../../config/config.php'; // adjust path as needed
require "../layouts/header.php";

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $is_banned = isset($_POST['is_banned']) ? 1 : 0;

    try {
        $stmt = $conn->prepare("
            INSERT INTO users (username, email, mypassword, is_banned, created_at)
            VALUES (:username, :email, :password, :is_banned, NOW())
        ");
        $stmt->execute([
            ':username' => $username,
            ':email'    => $email,
            ':password' => $password,
            ':is_banned'=> $is_banned
        ]);
        $message = '<div class="alert alert-success">User created successfully.</div>';
    } catch (PDOException $e) {
        $message = '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Create User</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
</head>
<body>

<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card shadow">
        <div class="card-body">
          <h5 class="card-title d-inline">Create User</h5>
          <a href="<?php echo ADMINURL; ?>/users-admins/show-users.php" class="btn btn-secondary float-right">Back to Users</a>
          <hr>

          <?php if (!empty($message)) echo $message; ?>

          <form method="POST" action="create-users.php">
            <div class="form-group">
              <label for="username">Username</label>
              <input type="text" name="username" class="form-control" id="username" required>
            </div>

            <div class="form-group">
              <label for="email">Email</label>
              <input type="email" name="email" class="form-control" id="email" required>
            </div>

            <div class="form-group">
              <label for="password">Password</label>
              <input type="password" name="password" class="form-control" id="password" required>
            </div>

            <div class="form-check mb-3">
              <input type="checkbox" name="is_banned" class="form-check-input" id="is_banned">
              <label class="form-check-label" for="is_banned">Is Banned</label>
            </div>

            <button type="submit" class="btn btn-primary">Create User</button>
          </form>
          
        </div>
      </div>
    </div>
  </div>
</div>

</body>
</html>