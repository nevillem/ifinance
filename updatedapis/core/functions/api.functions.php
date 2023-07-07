<?php

function msg($success,$status,$message,$extra = []){
    return json_encode(array_merge([
        'success' => $success,
        'status' => $status,
        'message' => $message
    ],$extra));
}

function isnotpost(){
          $notpost = $_SERVER["REQUEST_METHOD"] != "POST";
        return $notpost;
}

function member_registration_notification($member_firstname,$member_lastname,$member_account_number,$account_name){
            $message = "Dear, ".$member_firstname."" .$member_lastname.". Welcome to " .$account_name. "your account number is:" .$member_account_number. "./n Via Irembo Finance Platform";
            return $message;
}
function requestapiposting(){
          $requestpost = $_SERVER["REQUEST_METHOD"] = "POST";
        return $requestpost;
}

function send_otp ($email, $otp_code) {
    $send_mail = array();
      try {
        $to = $email;
        $from = "otp_authenticator";
        $subject = "OTP";

        // message to send to client
        $message = 'Your Ahuriire OTP is: '.$otp_code;

        if (mail($to, $subject, $message)) {
          $send_mail['success'] = true;
        } else {
          $send_mail['success'] = false;
          $send_mail['error_message'] = "unable to send OTP";
        }

      } catch (Exception $e) {
        $send_mail['success'] = false;
        $send_mail['error_message'] = "Message could not be sent. Mailer Exception: ".$e->ErrorInfo;
    }

    return $send_mail;
  }
