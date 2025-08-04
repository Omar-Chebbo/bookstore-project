<?php require "../includes/header.php"; ?>
<?php 

// Redirect if no pending user
if (!isset($_SESSION['pending_user'])) {
    header("Location: register.php");
    exit;
}

if (!isset($_SESSION['attempts'])) {
    $_SESSION['attempts'] = 2;
}

$error = "";
$success = "";

if (isset($_POST['verify'])) {
    $entered_code = trim($_POST['code']);
    $real_code = $_SESSION['pending_user']['code'];

    if ($entered_code === (string)$real_code) {
        // Insert user into DB
        require "../config/config.php";

        $stmt = $conn->prepare("INSERT INTO users (username, email, mypassword, country, birthdate) 
            VALUES (:username, :email, :mypassword, :country, :birthdate)");
        $stmt->execute([
            ':username' => $_SESSION['pending_user']['username'],
            ':email' => $_SESSION['pending_user']['email'],
            ':mypassword' => $_SESSION['pending_user']['password'],
            ':country' => $_SESSION['pending_user']['country'],
            ':birthdate' => $_SESSION['pending_user']['birthdate']
        ]);

        unset($_SESSION['pending_user']);
        unset($_SESSION['attempts']);

        $success = "Your email has been verified! You can now log in.";
        header("refresh:3;url=login.php");
    } else {
        $_SESSION['attempts']--;

        if ($_SESSION['attempts'] <= 0) {
            unset($_SESSION['pending_user']);
            unset($_SESSION['attempts']);
            header("Location: register.php");
            exit;
        } else {
            $error = "Incorrect code. You have {$_SESSION['attempts']} attempt(s) left.";
        }
    }
}
?>

<div class="container mt-5">
    <form class="form-control" method="POST">
        <h4 class="text-center mt-3">Email Verification</h4>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php elseif ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if (!$success): ?>
        <div class="mb-3">
            <label>Enter the verification code sent to your email:</label>
            <input type="text" name="code" class="form-control" required>
        </div>

        <p class="text-muted text-center">
            Attempts left: <strong><?php echo $_SESSION['attempts']; ?></strong>
        </p>

        <button class="btn btn-primary w-100 mb-3" name="verify" type="submit">Verify</button>
        <?php endif; ?>
    </form>
</div>

<?php require "../includes/footer.php"; ?>
