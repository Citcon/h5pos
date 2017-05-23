<?php
$bcc = 'invoice-master@citcon-inc.com';
// Multiple recipients
$to = $_POST['to']; // note the comma
$cc = $_POST['cc'];
$from_name = $_POST['from_name'];
$from_email = $_POST['from_email'];

// Subject
$subject = $_POST['subject'];

// Message
$message = $_POST['message'];

error_log($message);

// To send HTML mail, the Content-type header must be set
$headers[] = 'MIME-Version: 1.0';
$headers[] = 'Content-type: text/html; charset=iso-8859-1';

// Additional headers
$headers[] = "To: $to";
$headers[] = "Cc: $cc";
$headers[] = "Bcc: $bcc";
$headers[] = "From: $from_name <$from_email>";

// Mail it
mail($to, $subject, $message, implode("\r\n", $headers));
?>
