<?php
// Enable CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

// Ensure that the request method is OPTIONS for preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    // Parse JSON input from frontend
    $input = json_decode(file_get_contents('php://input'), true);
    if ($input === null) {
        throw new Exception("Invalid JSON");
    }

    $name = htmlspecialchars($input['name'] ?? '');
    $email = htmlspecialchars($input['email'] ?? '');
    $message = htmlspecialchars($input['message'] ?? '');

    // Log the incoming data
    error_log("Received data: " . json_encode($input));

    // Check if all required fields are present
    if (empty($name) || empty($email) || empty($message)) {
        throw new Exception("Missing required fields");
    }

    // Configure PHPMailer
    $mail->isSMTP();
    $mail->Host = getenv('SMTP_HOST');
    $mail->SMTPAuth = true;
    $mail->Username = getenv('SMTP_USERNAME');
    $mail->Password = getenv('SMTP_PASSWORD');
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom(getenv('SMTP_USERNAME'), 'Matemu');
    $mail->addAddress(getenv('SMTP_USERNAME'));

    $mail->isHTML(true);
    $mail->Subject = 'New Contact Form Submission';
    $mail->Body    = "Name: $name<br>Email: $email<br>Message: $message";

    // Send email
    $mail->send();
    echo json_encode(['message' => 'Message has been sent']);
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    echo json_encode(['message' => "Message could not be sent. Error: " . $e->getMessage()]);
}
?>
