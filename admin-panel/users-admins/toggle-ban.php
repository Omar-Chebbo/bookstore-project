<?php
require '../../config/config.php';
require '../../PHPMailer/PHPMailer.php';
require '../../PHPMailer/SMTP.php';
require '../../PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

try {
    if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
        throw new Exception("Invalid or missing user ID.");
    }

    $userId = (int) $_POST['id'];

    // 1. Get user info
    $stmt = $conn->prepare("SELECT username, email, is_banned FROM users WHERE id = :id");
    $stmt->execute([':id' => $userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception("User not found.");
    }

    $newStatus = $user['is_banned'] == 1 ? 0 : 1;

    // 2. Update ban status
    $update = $conn->prepare("UPDATE users SET is_banned = :new_status WHERE id = :id");
    $update->execute([
        ':new_status' => $newStatus,
        ':id' => $userId
    ]);

    // 3. Send ban email if user is banned
    if ($newStatus == 1) {
        $mail = new PHPMailer(true);
        try {
                // SMTP setup (adjust as needed)
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'projecti439fr@gmail.com';
            $mail->Password   = 'cxlmovrdaceztlby';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('projecti439fr@gmail.com', 'Book Store');
            $mail->addAddress($user['email'], $user['username']);
            $mail->isHTML(true);
            $mail->Subject = "Account Banned Notification";
            $mail->Body = "
                <p>Dear <strong>{$user['username']}</strong>,</p>
                <p>We regret to inform you that your account has been <strong>banned</strong>.</p>
                <p>If you believe this is a mistake, please contact support.</p>
                <p>Regards,<br>Bookstore Team</p>
            ";
            $mail->send();
        } catch (Exception $e) {
            // You can log the error here if needed
        }
    }

    echo json_encode([
        'success' => true,
        'newStatus' => $newStatus
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
