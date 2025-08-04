<?php
session_start();
require '../PHPMailer/Exception.php';
require '../PHPMailer/PHPMailer.php';
require '../PHPMailer/SMTP.php';
require "../config/config.php";
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch current user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute([':id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_OBJ);

$success = "";
$error = "";

//  Update (username, country, birthdate) ----------
if (isset($_POST['update_profile'])) {
    $username = trim($_POST['username']);
    $country = trim($_POST['country']);
    $birthdate = trim($_POST['birthdate']);

    if (empty($username)) {
        $error = "Username cannot be empty.";
    } else {
        // Check if username is taken by other users
        $check = $conn->prepare("SELECT id FROM users WHERE username = :username AND id != :id");
        $check->execute([':username' => $username, ':id' => $user_id]);

        if ($check->rowCount() > 0) {
            $error = "Username already taken.";
        } else {
            // Update user profile info
            $stmt = $conn->prepare("UPDATE users SET username = :username, Country = :country, Birthdate = :birthdate WHERE id = :id");
            $stmt->execute([
                ':username' => $username,
                ':country' => $country ?: null,       
                ':birthdate' => $birthdate ?: null,   
                ':id' => $user_id,
            ]);
            $success = "Profile updated successfully.";
            $_SESSION['username'] = $username; 

            // Refresh user data
            $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
            $stmt->execute([':id' => $user_id]);
            $user = $stmt->fetch(PDO::FETCH_OBJ);
        }
    }
}

// Email Update
if (isset($_POST['start_email_update'])) {
    $new_email = trim($_POST['email']);

    if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif ($new_email === $user->email) {
        $error = "New email cannot be the same as your current email.";
    } else {
        
        $verification_code = rand(100000, 999999);

        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'projecti439fr@gmail.com';
            $mail->Password   = 'cxlmovrdaceztlby'; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('projecti439fr@gmail.com', 'Book Store');
            $mail->addAddress($new_email);
            $mail->isHTML(true);
            $mail->Subject = 'Verify your new email address';
            $mail->Body    = "Your verification code is: <b>$verification_code</b>";

            $mail->send();

            // Store new email & code in session until verified
            $_SESSION['email_update'] = [
                'new_email' => $new_email,
                'code' => $verification_code,
            ];

            $success = "Verification code sent to $new_email. Please enter the code below to confirm.";

        } catch (Exception $e) {
            $error = "Failed to send verification email. Please try again.";
        }
    }
}

// Confirm verification code & update email in DB
if (isset($_POST['confirm_email_code'])) {
    $input_code = trim($_POST['email_code']);
    if (isset($_SESSION['email_update']) && $input_code === (string)$_SESSION['email_update']['code']) {
        // Update email in database
        $stmt = $conn->prepare("UPDATE users SET email = :email WHERE id = :id");
        $stmt->execute([
            ':email' => $_SESSION['email_update']['new_email'],
            ':id' => $user_id,
        ]);

        $success = "Email updated successfully!";
        unset($_SESSION['email_update']);

        // Refresh user data
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute([':id' => $user_id]);
        $user = $stmt->fetch(PDO::FETCH_OBJ);

    } else {
        $error = "Incorrect verification code.";
    }
}

//  Password Change 
if (isset($_POST['update_password'])) {
    $old_password = $_POST['old_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
        $error = "All password fields are required.";
    } elseif (!password_verify($old_password, $user->mypassword)) {
        $error = "Old password is incorrect.";
    } elseif ($new_password !== $confirm_password) {
        $error = "New password and confirm password do not match.";
    } elseif (strlen($new_password) < 8 || !preg_match('@[A-Z]@', $new_password) || !preg_match('@[^\w]@', $new_password)) {
        $error = "New password must be at least 8 characters long, contain an uppercase letter, and a special character.";
    } else {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET mypassword = :pass WHERE id = :id");
        $stmt->execute([':pass' => $hashed, ':id' => $user_id]);
        $success = "Password updated successfully.";
    }
}

?>

<?php require "../includes/header.php"; ?>

<div class="container mt-5">
    <h3>Account Settings</h3>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <ul class="nav nav-tabs" id="settingsTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="profile-tab-btn" data-bs-toggle="tab" data-bs-target="#profile-tab" type="button" role="tab" aria-controls="profile-tab" aria-selected="true">Edit Profile</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="email-tab-btn" data-bs-toggle="tab" data-bs-target="#email-tab" type="button" role="tab" aria-controls="email-tab" aria-selected="false">Edit Email</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="password-tab-btn" data-bs-toggle="tab" data-bs-target="#password-tab" type="button" role="tab" aria-controls="password-tab" aria-selected="false">Change Password</button>
        </li>
    </ul>

    <div class="tab-content mt-3" id="settingsTabContent">
        <!-- Profile Tab -->
        <div class="tab-pane fade show active" id="profile-tab" role="tabpanel" aria-labelledby="profile-tab-btn">
            <form method="POST" novalidate>
                <div class="mb-3">
                    <label for="usernameInput" class="form-label">Username</label>
                    <input type="text" name="username" id="usernameInput" class="form-control" required value="<?= htmlspecialchars($user->username) ?>">
                </div>
                <div class="mb-3">
                    <label for="countryInput" class="form-label">Country</label>
                    <input type="text" name="country" id="countryInput" class="form-control" value="<?= htmlspecialchars($user->Country ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label for="birthdateInput" class="form-label">Birthdate</label>
                    <input type="date" name="birthdate" id="birthdateInput" class="form-control" value="<?= htmlspecialchars($user->Birthdate ?? '') ?>">
                </div>
                <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
            </form>
        </div>

        <!-- Email Tab -->
        <div class="tab-pane fade" id="email-tab" role="tabpanel" aria-labelledby="email-tab-btn">
            <?php if (!isset($_SESSION['email_update'])): ?>
                <!-- Input new email -->
                <form method="POST" novalidate>
                    <div class="mb-3">
                        <label for="emailInput" class="form-label">New Email</label>
                        <input type="email" name="email" id="emailInput" class="form-control" required value="<?= htmlspecialchars($user->email) ?>">
                    </div>
                    <button type="submit" name="start_email_update" class="btn btn-primary">Send Verification Code</button>
                </form>
            <?php else: ?>
                <!--  Enter verification code -->
                <form method="POST" novalidate>
                    <div class="mb-3">
                        <label for="emailCodeInput" class="form-label">Verification Code sent to <?= htmlspecialchars($_SESSION['email_update']['new_email']) ?></label>
                        <input type="text" name="email_code" id="emailCodeInput" class="form-control" required>
                    </div>
                    <button type="submit" name="confirm_email_code" class="btn btn-success">Verify & Update Email</button>
                </form>
            <?php endif; ?>
        </div>

        <!-- Password Tab -->
        <div class="tab-pane fade" id="password-tab" role="tabpanel" aria-labelledby="password-tab-btn">
            <form method="POST" novalidate>
                <div class="mb-3">
                    <label for="oldPasswordInput" class="form-label">Old Password</label>
                    <input type="password" name="old_password" id="oldPasswordInput" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="newPasswordInput" class="form-label">New Password</label>
                    <input type="password" name="new_password" id="newPasswordInput" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="confirmPasswordInput" class="form-label">Confirm New Password</label>
                    <input type="password" name="confirm_password" id="confirmPasswordInput" class="form-control" required>
                </div>
                <button type="submit" name="update_password" class="btn btn-primary">Change Password</button>
            </form>
        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

<?php require "../includes/footer.php"; ?>
