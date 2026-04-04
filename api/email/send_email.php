<?php
// Function to send email
require_once __DIR__ . '/../../lib/PHPMailer-6.9.1/src/Exception.php';
require_once __DIR__ . '/../../lib/PHPMailer-6.9.1/src/PHPMailer.php';
require_once __DIR__ . '/../../lib/PHPMailer-6.9.1/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../config/env_loader.php';

function send_notification_email($to_email, $to_name, $subject, $body) {
    if(empty($to_email)) return false;
    
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = isset($_ENV['SMTP_HOST']) ? $_ENV['SMTP_HOST'] : 'smtp.gmail.com'; 
        $mail->SMTPAuth   = true;
        $mail->Port       = isset($_ENV['SMTP_PORT']) ? $_ENV['SMTP_PORT'] : 587;
        
        // Use environment variables for credentials
        $mail->Username   = isset($_ENV['SMTP_USER']) ? $_ENV['SMTP_USER'] : ''; 
        $mail->Password   = isset($_ENV['SMTP_PASS']) ? $_ENV['SMTP_PASS'] : '';    
        
        // Enable TLS encryption
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

        $mail->setFrom(isset($_ENV['SMTP_USER']) ? $_ENV['SMTP_USER'] : 'noreply@dqssa.com', 'DQSSA Appointments');
        $mail->addAddress($to_email, $to_name);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = nl2br($body);
        $mail->AltBody = strip_tags($body);

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
