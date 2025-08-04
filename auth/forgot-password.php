<?php require "../includes/header.php"; ?>
<?php require "../config/config.php"; ?>
<?php


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../PHPMailer/Exception.php';
require '../PHPMailer/PHPMailer.php';
require '../PHPMailer/SMTP.php';



// If user is already logged in, redirect them
if (isset($_SESSION['username'])) {
    header("Location: " . APPURL . "/");
    exit;
}

$success = "";
$error = "";

// Handle form submission
if (isset($_POST['submit'])) {
    $email = trim($_POST['email']);

    // Validate empty input
    if (empty($email)) {
        $error = "Email is required.";
    } else {
        // Check if email exists in DB
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $error = "No account found with that email.";
        } else {
            // Generate a 6-digit reset code
            $reset_code = rand(100000, 999999);

            // Store code in session for verification
            $_SESSION['reset_email'] = $email;
            $_SESSION['reset_code'] = $reset_code;
            $_SESSION['reset_user_id'] = $user['id'];

            // Send the code via email using PHPMailer
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'projecti439fr@gmail.com'; // your Gmail
                $mail->Password   = 'cxlmovrdaceztlby';         // your App Password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('projecti439fr@gmail.com', 'Book Store');
                $mail->addAddress($email, $user['username']);
                $mail->isHTML(true);
                $mail->Subject = 'Password Reset Code';
                $mail->Body    = "Your password reset code is: <b>$reset_code</b>";

                $mail->send();
                $success = "A reset code has been sent to your email.";
                header("Refresh: 2; URL=reset-password.php");
                exit;
            } catch (Exception $e) {
                $error = "Failed to send reset code. Try again later.";
            }
        }
    }
}
?>

<!-- Forgot Password Form -->
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <form class="form-control p-4" method="POST">
                <h4 class="text-center mb-3">Forgot Password</h4>

                <!-- Show success or error -->
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php elseif ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <div class="mb-3">
                    <label for="email" class="form-label">Enter your email address:</label>
                    <input type="email" name="email" class="form-control" required>
                </div>

                <button type="submit" name="submit" class="btn btn-primary w-100">Send Reset Code</button>

                <!-- Navigation Links -->
                <div class="text-center mt-3">
                    <a href="login.php">Back to Login</a> |
                    <a href="register.php">Register</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require "../includes/footer.php"; ?>
