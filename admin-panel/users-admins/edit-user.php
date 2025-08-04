<?php
require '../../config/config.php'; // Adjust path if needed
require "../layouts/header.php";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid user ID.");
}

$user_id = (int)$_GET['id'];

// Fetch user
$stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute([':id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_OBJ);

if (!$user) {
    die("User not found.");
}

$message = '';

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $is_banned = isset($_POST['is_banned']) ? 1 : 0;
    $update_password = !empty($_POST['password']);

    try {
        if ($update_password) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $updateStmt = $conn->prepare("
                UPDATE users SET username = :username, email = :email, mypassword = :password, is_banned = :is_banned WHERE id = :id
            ");
            $updateStmt->execute([
                ':username' => $username,
                ':email' => $email,
                ':password' => $password,
                ':is_banned' => $is_banned,
                ':id' => $user_id
            ]);
        } else {
            $updateStmt = $conn->prepare("
                UPDATE users SET username = :username, email = :email, is_banned = :is_banned WHERE id = :id
            ");
            $updateStmt->execute([
                ':username' => $username,
                ':email' => $email,
                ':is_banned' => $is_banned,
                ':id' => $user_id
            ]);
        }

        $message = '<div class="alert alert-success">User updated successfully.</div>';

        // Refresh user data after update
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute([':id' => $user_id]);
        $user = $stmt->fetch(PDO::FETCH_OBJ);

    } catch (PDOException $e) {
        $message = '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit User</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
</head>
<body>

<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card shadow">
        <div class="card-body">
          <h5 class="card-title d-inline">Edit User: <?= htmlspecialchars($user->username) ?></h5>
          <a href="<?= ADMINURL ?>/users-admins/show-users.php" class="btn btn-secondary float-right">Back to Users</a>
          <hr>

          <?php if (!empty($message)) echo $message; ?>

          <form method="POST" action="">
            <div class="form-group">
              <label for="username">Username</label>
              <input 
                type="text" 
                name="username" 
                class="form-control" 
                id="username" 
                value="<?= htmlspecialchars($user->username) ?>" 
                required
              >
            </div>

            <div class="form-group">
              <label for="email">Email</label>
              <input 
                type="email" 
                name="email" 
                class="form-control" 
                id="email" 
                value="<?= htmlspecialchars($user->email) ?>" 
                required
              >
            </div>

            <div class="form-group">
              <label for="password">Password (leave blank to keep current)</label>
              <input 
                type="password" 
                name="password" 
                class="form-control" 
                id="password"
              >
            </div>

            <div class="form-check mb-3">
              <input 
                type="checkbox" 
                name="is_banned" 
                class="form-check-input" 
                id="is_banned"
                <?= $user->is_banned ? 'checked' : '' ?>
              >
              <label class="form-check-label" for="is_banned">Is Banned</label>
            </div>

            <button type="submit" class="btn btn-primary">Update User</button>
          </form>

        </div>
      </div>
    </div>
  </div>
</div>

</body>
</html>