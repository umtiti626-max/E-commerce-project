<?php
// Load PHPMailer
require __DIR__ . '/PHPMailer/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load credentials from CREDENTIALS.env
$envPath = __DIR__ . '/../CREDENTIALS.env';
$env = [];
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '=') !== false) {
            list($key, $val) = explode('=', trim($line), 2);
            $env[$key] = $val;
        }
    }
}
$email = $env['EMAIL'] ?? '';
$appPassword = $env['APP_PASSWORD'] ?? '';

// Validate POST fields
$name = trim($_POST['name'] ?? '');
$userEmail = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

if (!$name || !$userEmail || !$subject || !$message) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'All fields are required.']);
    exit;
}

// Send email using PHPMailer
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = $email;
    $mail->Password = $appPassword;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom($email, 'E-commerce Contact');
    $mail->addAddress($email); // Send to self
    $mail->addReplyTo($userEmail, $name);

    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body = "<strong>Name:</strong> {$name}<br><strong>Email:</strong> {$userEmail}<br><strong>Message:</strong><br>{$message}";

    $mail->send();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $mail->ErrorInfo]);
}
?>
