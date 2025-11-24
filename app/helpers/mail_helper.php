<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

if (!function_exists('mail_helper')) {
    /**
     * Send an email using PHPMailer + Gmail SMTP.
     *
     * @param string      $name
     * @param string      $email
     * @param string      $subject
     * @param string      $message
     * @param string|null $attachmentPath
     *
     * @return bool|string
     */
    function mail_helper($name, $email, $subject, $message, $attachmentPath = null)
    {
        require_once APP_DIR . 'libraries/PHPMailer/src/Exception.php';
        require_once APP_DIR . 'libraries/PHPMailer/src/PHPMailer.php';
        require_once APP_DIR . 'libraries/PHPMailer/src/SMTP.php';

        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'micarshanearguelles@gmail.com';
            $mail->Password   = 'vxew yvlu otbv ltlr';
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            $mail->setFrom('micarshanearguelles@gmail.com', 'Barangay E-Credentials');
            $mail->addAddress($email, $name ?: 'Resident');

            if (!empty($attachmentPath) && file_exists($attachmentPath)) {
                $mail->addAttachment($attachmentPath);
            }

            $mail->isHTML(true);
            $mail->Subject = $subject;
            
            // Check if message contains HTML tags
            if (preg_match('/<html|<body|<table|<div/i', $message)) {
                // Message is already HTML formatted
                $mail->Body    = $message;
                $mail->AltBody = strip_tags($message);
            } else {
                // Plain text message - convert to HTML
                $mail->Body    = nl2br(htmlspecialchars($message));
                $mail->AltBody = strip_tags($message);
            }

            $mail->send();

            return true;
        } catch (\PHPMailer\PHPMailer\Exception $e) {
            return 'Mailer Error: ' . $mail->ErrorInfo;
        }
    }
}

