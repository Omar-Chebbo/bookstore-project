<?php require "../includes/header.php"; ?>
<?php require "../config/config.php"; ?>
<?php 

// If no reset session is found, redirect to forgot-password
if (!isset($_SESSION['reset_code'], $_SESSION['reset_email'], $_SESSION['reset_user_id'])) {
    header("Location: forgot-password.php");
    exit;
}

$error = "";
$success = "";

// Handle form submission
if (isset($_POST['submit'])) {
    $entered_code = trim($_POST['code']);
    $new_password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $real_code = $_SESSION['reset_code'];
    $user_id = $_SESSION['reset_user_id'];

    // Check if code matches
    if ($entered_code !== (string)$real_code) {
        $error = "Invalid reset code.";
    }
    // Check password match
    elseif ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    }
    // Check password strength
    elseif (!preg_match('/^(?=.*[A-Z])(?=.*[\W_]).{8,}$/', $new_password)) {
        $error = "Password must be at least 8 characters, include one uppercase letter and one special character.";
    } else {
        // Hash and update password
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET mypassword = :password WHERE id = :id");
        $stmt->execute([
            ':password' => $hashed,
            ':id' => $user_id
        ]);

        // Clear reset session
        unset($_SESSION['reset_code'], $_SESSION['reset_email'], $_SESSION['reset_user_id']);

        $success = "Password reset successfully. Redirecting to login...";
        header("Refresh:3; URL=login.php");
        exit;
    }
}
?>

<!-- Reset Password Form -->
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <form class="form-control p-4" method="POST">
                <h4 class="text-center mb-3">Reset Your Password</h4>

                <!-- Show success or error -->
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php elseif ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <div class="mb-3">
                    <label for="code">Enter the 6-digit code:</label>
                    <input type="text" name="code" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="password">New Password:</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="confirm_password">Confirm New Password:</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>

                <button name="submit" class="btn btn-success w-100">Reset Password</button>

                <!-- Optional link -->
                <div class="text-center mt-3">
                    <a href="login.php">Back to Login</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require "../includes/footer.php"; ?>
