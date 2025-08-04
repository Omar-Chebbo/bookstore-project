<?php
// send-gift-all.php
// Generates a bulk coupon, saves it, then emails every active (non-banned) user.

require "../../config/config.php";         
require "../../PHPMailer/Exception.php";
require "../../PHPMailer/PHPMailer.php";
require "../../PHPMailer/SMTP.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

try {
    //  Validate input
    $usageLimit = filter_input(INPUT_POST, 'usage_limit', FILTER_VALIDATE_INT);
    $percentage = filter_input(INPUT_POST, 'percentage', FILTER_VALIDATE_INT);

    if (!$usageLimit || !$percentage) {
        throw new Exception('Invalid input.');
    }

    if ($percentage < 1 || $percentage > 25) {
        throw new Exception('Percentage must be between 1 and 25.');
    }

    // Generate coupon info
    $code       = strtoupper(bin2hex(random_bytes(4)));
    $createdAt  = date('Y-m-d H:i:s');
    $expiresAt  = date('Y-m-d', strtotime('+7 days'));
    $status     = 1;

    // Insert bulk coupon
    $stmt = $conn->prepare("
        INSERT INTO coupons (code, percentage, usage_limit, expires_at, created_at, status)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$code, $percentage, $usageLimit, $expiresAt, $createdAt, $status]);

    // Fetch all active users
    $stmt = $conn->query("SELECT email, username FROM users WHERE is_banned = 0");
    $users = $stmt->fetchAll(PDO::FETCH_OBJ);
    if (empty($users)) {
        throw new Exception('No active users to email.');
    }

    // Base mail setup
    $baseMail = new PHPMailer(true);
    $baseMail->isSMTP();
    $baseMail->Host       = 'smtp.gmail.com';
    $baseMail->SMTPAuth   = true;
    $baseMail->Username   = 'projecti439fr@gmail.com';
    $baseMail->Password   = 'cxlmovrdaceztlby';
    $baseMail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $baseMail->Port       = 587;
    $baseMail->setFrom('projecti439fr@gmail.com', 'Book Store');
    $baseMail->isHTML(true);
    $baseMail->Subject = 'A Special Gift Coupon for You!';

    // Loop through users
    foreach ($users as $user) {
        try {
            $mail = clone $baseMail;
            $mail->addAddress($user->email, $user->username);

            $mail->Body = "
                <p>Hi {$user->username},</p>
                <p>We’re delighted to offer you a special limited coupon:</p>
                <ul>
                  <li><strong>Code:</strong> {$code}</li>
                  <li><strong>Discount:</strong> {$percentage}% off</li>
                  <li><strong>Expires at:</strong> {$expiresAt}</li>
                </ul>
                <p>Happy shopping!</p>
            ";

            $mail->send();
        } catch (Exception $e) {
            // Skip failed recipients but log them if needed
            error_log("Failed to send to {$user->email}: " . $e->getMessage());
        }
    }

    // Final response
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage()
    ]);
}
