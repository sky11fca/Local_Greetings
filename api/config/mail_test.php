<?php
$to = 'test@example.test';
$subject = 'PHP mail() Test';
$message = 'This is a test email';
$headers = 'From: no-reply@example.test' . "\r\n" .
    'Reply-To: no-reply@example.test' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

$result = mail($to, $subject, $message, $headers);
echo $result ? 'Email sent successfully' : 'Failed to send email';
print_r(error_get_last());