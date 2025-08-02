<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name    = $_POST['name']    ?? '';
    $email   = $_POST['email']   ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';

    $mail = new PHPMailer(true);

    try {
        // ── SMTP settings ────────────────────────
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'projecti439fr@gmail.com';   // your Gmail
        $mail->Password   = 'cxlmovrdaceztlby';          // App‑Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // ── Addresses ────────────────────────────
        $mail->setFrom($email, $name);                  // visitor
        $mail->addAddress('projecti439fr@gmail.com');   // you
        $mail->addReplyTo($email, $name);

        // ── Content ───────────────────────────────
        $mail->Subject = $subject;
        $mail->Body    = "Name: $name\nEmail: $email\n\nMessage:\n$message";

        // ── Send ─────────────────────────────────
        $mail->send();

        // ✔ Success → back to contact page
        header('Location: contact.php?sent=1');
        exit;

    } catch (Exception $e) {
        // ✖ Failure → back to contact page with error flag
        header('Location: contact.php?sent=0');
        exit;
    }

} else {
    // Block direct access
    header('HTTP/1.1 403 Forbidden');
    exit('Forbidden');
}
?>
