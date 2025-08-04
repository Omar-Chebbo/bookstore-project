<?php


require "../../config/config.php";

// Include PHPMailer classes manually with your specified path
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
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

if (!$subject || !$message) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

try {
    // Fetch all users' emails and usernames
    $stmt = $conn->prepare("SELECT email, username FROM users");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$users) {
        echo json_encode(['success' => false, 'error' => 'No users found']);
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

    $errors = [];

    foreach ($users as $user) {
        try {
            $mail->clearAddresses();
            $mail->addAddress($user['email'], $user['username']);

            $mail->isHTML(false);
            $mail->Subject = $subject;

            $fullMessage = "Hello {$user['username']},\n\n{$message}\n\nBest regards,\nBook Store Team ";
            $mail->Body = $fullMessage;

            $mail->send();

        } catch (Exception $e) {
            $errors[] = $user['email'];
           
        }
    }

    if (count($errors) > 0) {
        echo json_encode([
            'success' => false,
            'error' => 'Failed to send to: ' . implode(', ', $errors),
        ]);
    } else {
        echo json_encode(['success' => true]);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
