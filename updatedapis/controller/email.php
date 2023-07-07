<?php
// send_email("nevillemwije@gmail.com", "hello");
// function send_email($email, $message){
// $send_mail = array();
// try {
//
// $to = $email;
// $subject = "iRembo Finance";
// $from = "mfis@irembofinance.com";
// $reply = "noreply@irembofinance.com";
// $body = "hey";
// $headers  = 'MIME-Version: 1.0' . "\r\n";
// $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
//     //$headers = "";
// $headers .= 'From:  iRembo Finance <info@irembofinance.com>' . PHP_EOL .
//         'Reply-To: iRembo Finance <info@irembofinance.com>' . PHP_EOL .
//         'X-Mailer: PHP/' . phpversion();
// if (mail('nevillemwije@gmail.com', $subject, "hey", $headers)) {
//   $send_mail['success'] = true;
// } else {
//   $send_mail['success'] = false;
//   echo $send_mail['error_message'] = "unable to send email";
// }
//
// } catch (Exception $e) {
// $send_mail['success'] = false;
// echo $send_mail['error_message'] = "Message could not be sent. Mailer Exception: ".$e->ErrorInfo;
// }
//
// return $send_mail;
// }
send_email("nevillemwije@gmail.com", "hey");
function send_email($email, $message){
$send_mail = array();
try {
$to = $email;
$from = "mfis@irembofinance.com";
$reply = "noreply@irembofinance.com";
$subject = "iRembo Finance";
$headers  = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
// Create email headers
$headers .= 'From: '.$from."\r\n".
    'Reply-To: '.$reply."\r\n" .
    'X-Mailer: PHP/' . phpversion();
if (mail($to, $subject, $message, $headers)) {
  $send_mail['success'] = true;
} else {
  $send_mail['success'] = false;
  echo $send_mail['error_message'] = "unable to send email";
}

} catch (Exception $e) {
$send_mail['success'] = false;
echo $send_mail['error_message'] = "Message could not be sent. Mailer Exception: ".$e->ErrorInfo;
}

return $send_mail;
}
