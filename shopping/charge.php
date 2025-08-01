<?php require "../includes/header.php"; ?>
<?php require "../config/config.php"; ?>
<?php require '../vendor/autoload.php'; 

// Load TCPDF
require_once '../tcpdf/tcpdf.php';

//  Load PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../PHPMailer/Exception.php';
require '../PHPMailer/PHPMailer.php';
require '../PHPMailer/SMTP.php';
?>

<?php
if (!isset($_SESSION['username'])) {
  header("loaction: " . APPURL . "/");
}

/* at the top of 'check.php' */
if ($_SERVER['REQUEST_METHOD'] == 'GET' && realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME'])) {
  /* 
           Up to you which header to send, some prefer 404 even if 
           the files does exist for security
        */
  header('HTTP/1.0 403 Forbidden', TRUE, 403);
  die(header('location: ' .APPURL.''));
}

if (isset($_POST['email'])) {
  \Stripe\Stripe::setApiKey($secret_key);




  $charge = \Stripe\Charge::create([
    'source' => $_POST['stripeToken'],
    'amount' => (int)((float)$_SESSION['price'] * 100),
    'currency' => 'usd',
  ]);

  if (empty($_POST['email']) || empty($_POST['username']) || empty($_POST['fname']) || empty($_POST['lname'])) {
    echo "<script>alert('One or more inputs are empty');</script>";
  } else {
    $email = $_POST['email'];
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $token = $_POST['stripeToken'];
    $price = $_SESSION['price'];
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'];

    $insert = $conn->prepare("INSERT INTO orders (email, fname, lname, token, price, user_id,username)
                              VALUES (:email, :fname, :lname, :token, :price, :user_id, :username)");

    $insert->execute([
      ':email' => $email,
      ':fname' => $fname,
      ':lname' => $lname,
      ':token' => $token,
      ':price' => $price,
      ':user_id' => $user_id,
      ':username' => $username
    ]);
     //  Get cart items
    $cartItemsStmt = $conn->prepare("SELECT * FROM cart WHERE user_id = :user_id");
    $cartItemsStmt->execute([':user_id' => $user_id]);
    $cartItems = $cartItemsStmt->fetchAll(PDO::FETCH_ASSOC);

    //  Generate PDF invoice
    $pdf = new TCPDF();
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Write(0, "INVOICE", '', 0, 'C', true, 0, false, false, 0);
    $pdf->Ln(5);
    $pdf->Write(0, "Name: $fname $lname", '', 0, 'L', true, 0, false, false, 0);
    $pdf->Write(0, "Email: $email", '', 0, 'L', true, 0, false, false, 0);
    $date = date('F j, Y');
    $pdf->Write(0, "Date: $date", '', 0, 'L', true, 0, false, false, 0);

    $pdf->Ln(5);

    // Invoice Table
    $html = '<table border="1" cellpadding="5">
    <thead>
      <tr>
        <th>Product</th>
        <th>Price</th>
        <th>Qty</th>
        <th>Total</th>
      </tr>
    </thead>
    <tbody>';

    $total = 0;
    foreach ($cartItems as $item) {
        $lineTotal = $item['pro_price'] * $item['pro_amount'];
        $total += $lineTotal;
        $html .= "<tr>
            <td>{$item['pro_name']}</td>
            <td>\${$item['pro_price']}</td>
            <td>{$item['pro_amount']}</td>
            <td>\${$lineTotal}</td>
        </tr>";
    }

    $html .= "<tr>
        <td colspan='3' align='right'><strong>Total</strong></td>
        <td><strong>\$$total</strong></td>
    </tr></tbody></table>";

    $pdf->writeHTML($html, true, false, false, false, '');

    // Save to file
    $invoicePath = realpath(__DIR__ . '/../invoices') . '/invoice_' . uniqid() . '.pdf';

    $pdf->Output($invoicePath, 'F');

    // Send Email with PHPMailer
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; 
        $mail->SMTPAuth = true;
        $mail->Username = 'projecti439fr@gmail.com'; 
        $mail->Password = 'cxlmovrdaceztlby';   
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('projecti439fr@gmail.com', 'Book Store');
        $mail->addAddress($email, "$fname $lname");

        $mail->Subject = "Your Invoice from Book Store";
        $mail->Body = "Hello $fname,\n\nThank you for your order. Please find your invoice attached.";
        $mail->addAttachment($invoicePath);

        $mail->send();
    } catch (Exception $e) {
        error_log("Email sending failed: {$mail->ErrorInfo}");
    }

   
  }
  header("Location: " . APPURL . "/shopping/thankyou.php");
  exit;
}










//echo "paid";
?>
