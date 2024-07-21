<?php
// Enable CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

// Ensure that the request method is OPTIONS for preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit();
}

// Custom error handler to capture and log errors
function customErrorHandler($severity, $message, $file, $line) {
    // Only handle errors of severity E_ERROR or above
    if (!(error_reporting() & $severity)) {
        // This error code is not included in error_reporting
        return;
    }
    // Log error details
    error_log("Error [$severity]: $message in $file on line $line");
    // Optionally, send a generic error message to the client
    echo json_encode(['message' => 'An error occurred, please try again later.']);
    exit();
}

// Set the custom error handler
set_error_handler("customErrorHandler");

// Logging input data for debugging
error_log('Received input: ' . file_get_contents('php://input'));

require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    // Parse JSON input from frontend
    $input = json_decode(file_get_contents('php://input'), true);

    // Check for JSON parsing errors
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input');
    }

    // Logging parsed input for debugging
    error_log('Parsed input: ' . print_r($input, true));

    // Validate input
    if (!isset($input['name'], $input['email'], $input['message'])) {
        throw new Exception('Missing required fields');
    }

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

    $mail->setFrom(getenv('SMTP_USERNAME'), 'Matemu');
    $mail->addAddress(getenv('SMTP_USERNAME'));

    $mail->isHTML(true);
    $mail->Subject = 'New Contact Form Submission';
    $mail->Body    = "Name: $name<br>Email: $email<br>Message: $message";

    // Send email
    $mail->send();
    echo json_encode(['message' => 'Message has been sent']);
} catch (Exception $e) {
    error_log("Exception caught: {$e->getMessage()}");
    echo json_encode(['message' => "Message could not be sent. Error: {$e->getMessage()}"]);
}