<?php require "../includes/header.php"; ?>
<?php require "../config/config.php"; ?>
<?php 
session_start(); // You forgot this

if (isset($_SESSION['username'])) {
    header("location: " . APPURL . "");
    exit;
}

if (isset($_POST['submit'])) {
    if (empty($_POST['email']) || empty($_POST['password'])) {
        echo "<script>alert('One or more inputs are empty');</script>";
    } else {
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Secure: Use prepared statement
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if ($user["is_banned"] != 0) {
                echo "<script>alert('This user has been banned');</script>";
            } elseif (password_verify($password, $user['mypassword'])) {
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_id'] = $user['id'];
                header("location: " . APPURL . "/");
                exit;
            } else {
                echo "<script>alert('Password or email is incorrect');</script>";
            }
        } else {
            echo "<script>alert('Password or email is incorrect');</script>";
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <form class="form-control mt-5" method="post" action="login.php">
            <h4 class="text-center mt-3">Login</h4>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="mb-3 text-end">
                <a href="forgot-password.php" class="text-decoration-none">Forgot Password?</a>
            </div>

            <button class="w-100 btn btn-lg btn-primary mt-2 mb-3" name="submit" type="submit">Login</button>

            <div class="text-center">
                <small>
                    Don't have an account?
                    <a href="register.php" class="text-decoration-none">Register</a>
                </small>
            </div>
        </form>
    </div>
</div>

<?php require "../includes/footer.php"; ?>
