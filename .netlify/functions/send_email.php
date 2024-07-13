// functions/send_email.php
<?php
// Enable CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Ensure that the request method is OPTIONS for preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit();
}

require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    // Parse JSON input from frontend
    $input = json_decode(file_get_contents('php://input'), true);
    $name = htmlspecialchars($input['name']);
    $email = htmlspecialchars($input['email']);
    $message = htmlspecialchars($input['message']);

    // Configure PHPMailer
    $mail->isSMTP();
    $mail->Host = getenv('SMTP_HOST');
    $mail->SMTPAuth = true;
    $mail->Username = getenv('SMTP_USERNAME');
    $mail->Password = getenv('SMTP_PASSWORD');
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom(getenv('SMTP_USERNAME'), 'Your Name');
    $mail->addAddress(getenv('SMTP_USERNAME'));

    $mail->isHTML(true);
    $mail->Subject = 'New Contact Form Submission';
    $mail->Body    = "Name: $name<br>Email: $email<br>Message: $message";

    // Send email
    $mail->send();
    echo json_encode(['message' => 'Message has been sent']);
} catch (Exception $e) {
    echo json_encode(['message' => "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"]);
}
?>
