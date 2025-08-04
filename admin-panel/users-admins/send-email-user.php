<?php


require "../../config/config.php";

// Include PHPMailer classes manually using your specified path
require '../../PHPMailer/Exception.php';
require '../../PHPMailer/PHPMailer.php';
require '../../PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');
session_start();

// Check admin login
if (!isset($_SESSION['adminname'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Validate POST data
$user_id = $_POST['user_id'] ?? null;
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

if (!$user_id || !$subject || !$message) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

try {
    // Fetch user info
    $stmt = $conn->prepare("SELECT email, username FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['success' => false, 'error' => 'User not found']);
        exit;
    }

    $mail = new PHPMailer(true);

    
    $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'projecti439fr@gmail.com';
        $mail->Password   = 'cxlmovrdaceztlby';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('projecti439fr@gmail.com', 'Book Store');
    $mail->addAddress($user['email'], $user['username']);

    
    $mail->isHTML(false);
    $mail->Subject = $subject;

    $fullMessage = "Hello {$user['username']},\n\n{$message}\n\nBest regards,\nBook Store Team ";
    $mail->Body = $fullMessage;

    
    $mail->send();

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => "Mailer Error: {$mail->ErrorInfo}"]);
}
