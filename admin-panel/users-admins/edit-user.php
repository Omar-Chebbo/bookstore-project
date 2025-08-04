<?php 
require '../../config/config.php';
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
$errors = [];

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $country = trim($_POST['country']);
    $birthdate = trim($_POST['birthdate']);
    $is_banned = isset($_POST['is_banned']) ? 1 : 0;

    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $update_password = !empty($password) || !empty($confirmPassword);

    // Validate
    if (strlen($username) < 3) {
        $errors[] = "Username must be at least 3 characters.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    if ($birthdate && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthdate)) {
        $errors[] = "Birthdate must be in YYYY-MM-DD format.";
    }

    if ($update_password) {
        if (empty($password) || empty($confirmPassword)) {
            $errors[] = "Both password fields must be filled.";
        } elseif ($password !== $confirmPassword) {
            $errors[] = "Passwords do not match.";
        } else {
            if (strlen($password) < 8) {
                $errors[] = "Password must be at least 8 characters.";
            }
            if (!preg_match('/[A-Z]/', $password)) {
                $errors[] = "Password must include at least one uppercase letter.";
            }
            if (!preg_match('/[\W_]/', $password)) {
                $errors[] = "Password must include at least one special character.";
            }
        }
    }

    // If no errors, perform update
    if (empty($errors)) {
        try {
            if ($update_password) {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $updateStmt = $conn->prepare("
                    UPDATE users 
                    SET username = :username, 
                        email = :email,
                        country = :country,
                        birthdate = :birthdate,
                        mypassword = :password, 
                        is_banned = :is_banned 
                    WHERE id = :id
                ");
                $updateStmt->execute([
                    ':username' => $username,
                    ':email' => $email,
                    ':country' => $country ?: null,
                    ':birthdate' => $birthdate ?: null,
                    ':password' => $hashed,
                    ':is_banned' => $is_banned,
                    ':id' => $user_id
                ]);
            } else {
                $updateStmt = $conn->prepare("
                    UPDATE users 
                    SET username = :username, 
                        email = :email,
                        country = :country,
                        birthdate = :birthdate,
                        is_banned = :is_banned 
                    WHERE id = :id
                ");
                $updateStmt->execute([
                    ':username' => $username,
                    ':email' => $email,
                    ':country' => $country ?: null,
                    ':birthdate' => $birthdate ?: null,
                    ':is_banned' => $is_banned,
                    ':id' => $user_id
                ]);
            }

            // Redirect to show-users.php after successful update
            header("Location: " . ADMINURL . "/users-admins/show-users.php");
            exit;

        } catch (PDOException $e) {
            $message = '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
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

          <?= $message ?>

          <?php if ($errors): ?>
            <div class="alert alert-danger">
              <ul>
                <?php foreach ($errors as $err): ?>
                  <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>

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
              <label for="country">Country</label>
              <input 
                type="text" 
                name="country" 
                class="form-control" 
                id="country" 
                value="<?= htmlspecialchars($user->Country) ?>"
              >
            </div>

            <div class="form-group">
              <label for="birthdate">Birthdate</label>
              <input 
                type="date" 
                name="birthdate" 
                class="form-control" 
                id="birthdate" 
                value="<?= htmlspecialchars($user->Birthdate) ?>"
              >
            </div>

            <hr>
            <h6>Change Password (optional)</h6>
            <div class="form-group">
              <label for="password">New Password</label>
              <input 
                type="password" 
                name="password" 
                class="form-control" 
                id="password"
                placeholder="Leave blank to keep current"
              >
            </div>

            <div class="form-group">
              <label for="confirm_password">Confirm New Password</label>
              <input 
                type="password" 
                name="confirm_password" 
                class="form-control" 
                id="confirm_password"
              >
              <small class="form-text text-muted">
                Must be ≥8 characters, include an uppercase letter and a special character.
              </small>
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
