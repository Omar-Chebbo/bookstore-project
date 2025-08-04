<?php
// send-gift-user.php
// Generates a one-off coupon for a single user and emails it via PHPMailer.

require "../../config/config.php";          
require "../../PHPMailer/Exception.php";
require "../../PHPMailer/PHPMailer.php";
require "../../PHPMailer/SMTP.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

try {
    // Support both JSON and form-encoded POST
    $input = $_SERVER['CONTENT_TYPE'] === 'application/json'
        ? json_decode(file_get_contents('php://input'), true)
        : $_POST;

    $userId      = filter_var($input['user_id'] ?? null, FILTER_VALIDATE_INT);
    $usageLimit  = filter_var($input['usage_limit'] ?? null, FILTER_VALIDATE_INT);
    $percentage  = filter_var($input['percentage'] ?? null, FILTER_VALIDATE_INT);

    if (!$userId || !$usageLimit || !$percentage) {
        throw new Exception('Invalid input.');
    }
    if ($usageLimit < 1 || $usageLimit > 2) {
        throw new Exception('Usage limit must be 1 or 2.');
    }
    if ($percentage < 1 || $percentage > 10) {
        throw new Exception('Percentage must be between 1 and 10.');
    }

    // Fetch user email & name
    $stmt = $conn->prepare("SELECT email, username FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_OBJ);
    if (!$user) {
        throw new Exception('User not found.');
    }

    // Generate coupon code
    $code       = strtoupper(bin2hex(random_bytes(4))); // e.g. "A1B2C3D4"
    $expiresAt  = date('Y-m-d', strtotime('+7 days'));
    $createdAt  = date('Y-m-d H:i:s');
    $status     = 1;

    // Insert coupon into database
    $stmt = $conn->prepare("
        INSERT INTO coupons (code, percentage, usage_limit, expires_at, created_at, status)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$code, $percentage, $usageLimit, $expiresAt, $createdAt, $status]);

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
        $mail->addAddress($user->email, $user->username);
        $mail->Subject = 'You’ve Got a Gift Coupon!';
        $mail->isHTML(true);
        $mail->Body = "
            <p>Hi {$user->username},</p>
            <p>As one of our favorite customers, we’re excited to share this special gift coupon with you!<br>Here it is: 🎉</p>
            <ul>
              <li><strong>Code:</strong> {$code}</li>
              <li><strong>Discount:</strong> {$percentage}% off</li>
              <li><strong>Usage limit:</strong> {$usageLimit} time(s)</li>
              <li><strong>Expires at:</strong> {$expiresAt}</li>
            </ul>
            <p>Enjoy your reading!</p>
        ";
        $mail->send();
    } catch (Exception $e) {
        throw new Exception("Mailer Error: {$mail->ErrorInfo}");
    }

    echo json_encode(['success' => true, 'code' => $code]);

} catch (Exception $e) {
    http_response_code(400);
    error_log("Gift Error: " . $e->getMessage()); // Optional: log it
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage()
    ]);
}
