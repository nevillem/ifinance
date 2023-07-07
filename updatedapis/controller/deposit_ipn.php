<?php

require_once('Database.php');
require_once('../model/Response.php');
require_once('../core/initialize.php');

require('YoAPI.php');
require_once ('../core/credentials.php');

// attempt to set up connections to read and write db connections
try {
  $writeDB = DB::connectWriteDB();
  $readDB = DB::connectReadDB();
}
catch(PDOException $ex) {
  // log connection error for troubleshooting and return a json error response
  error_log("Connection Error: ".$ex, 0);
  $response = new Response();
  $response->setHttpStatusCode(500);
  $response->setSuccess(false);
  $response->addMessage("Database connection error");
  $response->send();
  exit;
}

if (isset($_POST)):

  $yoAPI = new YoAPI($username, $password);
  $response = $yoAPI->receive_payment_notification();
  if($response['is_verified']):
    // mail('ibigega23@gmail.com,nevillemwije@gmail.com', 'IPN result', 'The IPN has been success');
    $checkTransaction = $readDB->prepare('SELECT * FROM desposit_transactions, members, saccos
      where desposit_transactions.members_member_id = members.member_id and
      desposit_transactions.saccos_sacco_id = saccos.sacco_id
      and desposit_transactions.deposit_external_ref=:external_ref');
    $checkTransaction->bindParam(':external_ref', $response['external_ref'], PDO::PARAM_STR);
    $checkTransaction->execute();
    $rowCount = $checkTransaction->rowCount();
    if($rowCount === 0):
      mail('nevillemwije@gmail.com', "Reference Error", 'Invalid external reference');
    endif;
    $transaction_info = $checkTransaction->fetch(PDO::FETCH_ASSOC);
    $to = 'nevillemwije@ahuriire.com,fr.tusiime@ahuriire.com,david@ahuriire.com,'.$transaction_info['sacco_email'];
    // 	$from = "notify@irembofinance.com";
	$headers = "From: notify@irembofinance.com";
    $subject = $transaction_info['members_account_number'];
        $charge = $transaction_info['desposit_charge'];
		$amount = $response['amount'];
		$amount = (int)$amount - (int)$charge;
// 			if ($amount > 0 && $amount <= 2500):
// 						$charge = 230;
// 					    $amount = $amount - $charge;
// 					    elseif ($amount >= 2501 && $amount <= 5000):
// 									$charge = 290;
// 						 $amount = $amount - $charge;
// 						 elseif($amount >= 5001 && $amount <= 15000):
// 											$charge = 1050;
// 						     $amount = $amount - $charge;
// 						     elseif($amount >= 15001 && $amount <= 30000):
// 													$charge = 1150;
// 							  $amount = $amount - $charge;
// 							  elseif($amount >= 30001 && $amount <= 45000):
// 															$charge = 1250;
// 							  $amount = $amount - $charge;
// 							  elseif($amount >= 45001 && $amount <= 60000):
// 															$charge = 1400;
// 							  $amount = $amount - $charge;
// 							   elseif($amount >= 60001 && $amount <= 125000):
// 															 $charge = 1610;
// 															 $amount = $amount - $charge;
//                                 elseif($amount >= 125001 && $amount <= 250000):
// 														$charge = 2000;
// 														$amount = $amount - $charge;
// 							    	 elseif($amount >= 250001 && $amount <= 500000):
// 															 $charge = 2600;
// 															 $amount = $amount - $charge;
// 										elseif($amount >= 500001 && $amount <= 1000000):
// 															 $charge = 6700;
// 															 $amount = $amount - $charge;
// 											elseif($amount >= 1000001 && $amount <= 2000000):
// 															 $charge = 11500;
// 															 $amount = $amount - $charge;
// 												elseif($amount >= 2000001 && $amount <= 4000000):
// 															 $charge = 11500;
// 															 $amount = $amount - $charge;
// 												    elseif($amount >= 4000001 && $amount <= 5000000):
// 															 $charge = 11500;
// 															 $amount = $amount - $charge;
// 													    elseif($amount > 5000000):
// 														$charge = 50000;
// 														$amount = $amount - $charge;
// 							  endif;
      $date =date('Y-m-d');
      $new_account_balance =  (int)$transaction_info['members_account_volunteer'] + (int)$amount;

      $updateTransaction = $writeDB->prepare('UPDATE desposit_transactions SET desposit_status = "successful", deposit_response = NOW(), desposit_balance = :balance ,deposit_network_ref = :network_ref WHERE deposit_external_ref = :external_ref AND deposit_id = :depositid');
	    $updateTransaction->bindParam(':external_ref', $response['external_ref'], PDO::PARAM_STR);
      $updateTransaction->bindParam(':network_ref', $response['network_ref'], PDO::PARAM_STR);
	    $updateTransaction->bindParam(':balance', $new_account_balance, PDO::PARAM_INT);
	    $updateTransaction->bindParam(':depositid', $transaction_info['deposit_id'], PDO::PARAM_INT);
      $updateTransaction->execute();

      $updateAccount = $writeDB->prepare('UPDATE members SET members_account_volunteer = :balance WHERE member_id=:id');
       $updateAccount->bindParam(':id', $transaction_info['member_id'], PDO::PARAM_INT);
       $updateAccount->bindParam(':balance', $new_account_balance, PDO::PARAM_INT);
       $updateAccount->execute();

       mail($to, $subject, 'A deposit payment of UGX '.$amount.' to '.$transaction_info['sacco_name'].' on A/C no: '.$transaction_info['members_account_number'].' has been made successfully with network reference '.$response['network_ref'].' and external reference '.$response['external_ref']. '.', $headers);
       $number_two = substr($response['msisdn'], 3);
       $number = $transaction_info['member_contact'];
       // $message = 'A saving of UGX '.$amount.' hasto '.$transaction_info['members_account_number'].' on Account Number: '.$transaction_info['account_number'].'('.$transaction_info['member_fname'].' '.$transaction_info['member_lname'].') on '.$response['date_time'].'. Thank you for saving with us.';
       //date and time generation
       $postdate = new DateTime();
       // set date for kampala
       $postdate->setTimezone(new DateTimeZone('Africa/Nairobi'));
       //formulate the new date
       $date = $postdate->format('Y-m-d H:i:s');
       $amount = number_format(($amount),0,'.',',');
       $new_account_balance = number_format(($new_account_balance),0,'.',',');
     $message = "A saving of UGX ".$amount." has been made to A/C: ".$transaction_info['members_account_number']." (".$transaction_info['member_fname']." " .$transaction_info['member_lname'].") in ".$transaction_info['sacco_name'].". TxID: ".$transaction_info['desposit_reference']. ". Date: ".$date. ".\nNew balance: UGX ".$new_account_balance;
     // insert sms into the database
     $message_two = "You have made saving of UGX ".$amount." has been made to A/C: ".$transaction_info['members_account_number']." (".$transaction_info['member_fname']." " .$transaction_info['member_lname'].") in ".$transaction_info['sacco_name'].". TxID: ".$transaction_info['desposit_reference']. ". On: ".$date;

     insertSMSDB($writeDB, $message, $number, $transaction_info['sacco_id']);
     insertSMSDB($writeDB, $message_two, $number_two, $transaction_info['sacco_id']);
     // insert email into the database
     insertEMAILDB($writeDB, $message, $transaction_info['sacco_email'], $transaction_info['sacco_id']);

  endif;
endif;
