<?php

require_once __DIR__. '/initialize.php';
require_once 'yopayments/vendor/autoload.php';
require_once 'credentials.php';

$db_connection = new Database();
$conn = $db_connection->dbConnection();

if (isset($_POST)):
  $yoAPI = new YoAPI($username, $password);
$response = $yoAPI->receive_payment_failure_notification();
  if($response['is_verified']):
    $to = 'nevillemwije@gmail.com';
		$from = "Deposit IPN";
		$subject = $transaction_info['account_number'];
       mail($to, $subject, 'A Deposit payment of '.$response['amount'].' to '.$transaction_info['account_number'].'/= has failed');

  endif;
endif;
