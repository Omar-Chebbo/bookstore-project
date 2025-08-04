<?php require "../includes/header.php"; ?>
<?php require "../config/config.php"; ?>
<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/Exception.php';
require '../PHPMailer/PHPMailer.php';
require '../PHPMailer/SMTP.php';



if (isset($_SESSION['username'])) {
    header("Location: " . APPURL . "/");
    exit;
}

if (isset($_POST['submit'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $birthdate = $_POST['birthdate'];
    $country = $_POST['country'];

    $uppercase = preg_match('@[A-Z]@', $password);
    $specialChars = preg_match('@[^\w]@', $password);

    if (empty($username) || empty($email) || empty($password) || empty($birthdate) || empty($country)) {
        echo "<script>alert('All fields are required');</script>";
    } elseif (strlen($password) < 8 || !$uppercase || !$specialChars) {
        echo "<script>alert('Password must be at least 8 characters with uppercase and special character');</script>";
    } else {
        // Check if email or username exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email OR username = :username");
        $stmt->execute([
            ':email' => $email,
            ':username' => $username
        ]);

        if ($stmt->rowCount() > 0) {
            echo "<script>alert('Username or email already exists');</script>";
        } else {
            $verification_code = rand(100000, 999999);

            // Send email
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'projecti439fr@gmail.com';
                $mail->Password   = 'cxlmovrdaceztlby';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('projecti439fr@gmail.com', 'Book Store');
                $mail->addAddress($email, $username);
                $mail->isHTML(true);
                $mail->Subject = 'Email Verification Code';
                $mail->Body    = "Your verification code is: <b>$verification_code</b>";

                $mail->send();

            } catch (Exception $e) {
                echo "<script>alert('Failed to send email. Try again.');</script>";
                exit;
            }

            $_SESSION['pending_user'] = [
                'username' => $username,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'birthdate' => $birthdate,
                'country' => $country,
                'code' => $verification_code
            ];

            header("Location: verify.php");
            exit;
        }
    }
}
?>

<!-- Register Form -->
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <form class="form-control mt-5" method="post" action="register.php">  
                <h4 class="text-center mt-3">Register</h4>

                <!-- Username -->
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>

                <!-- Email -->
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>

                <!-- Password -->
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                    <small class="text-muted">At least 8 characters, one uppercase, one special character</small>
                </div>

                <!-- Birthdate -->
                <div class="mb-3">
                    <label class="form-label">Birthdate</label>
                    <input type="date" name="birthdate" class="form-control" required>
                </div>

                <!-- Country -->
                <div class="mb-3">
                    <label class="form-label">Country</label>
                    <select name="country" class="form-control" required>
                        <option value="">-- Select Country --</option>
                        <option value="Lebanon">Lebanon</option>
                        <option value="United States">United States</option>
                        <option value="Canada">Canada</option>
                        <option value="France">France</option>
                        <option value="Germany">Germany</option>
                        <option value="UK">UK</option>
                        <!-- Add more countries as needed -->
                    </select>
                </div>

                <!-- Register Button -->
                <button name="submit" class="w-100 btn btn-lg btn-primary mt-2" type="submit">Register</button>

                <!-- Login Link -->
                <p class="text-center mt-3 mb-3">
                    Already have an account? <a href="login.php">Login here</a>
                </p>
            </form>
        </div>
    </div>
</div>

<?php require "../includes/footer.php"; ?>
