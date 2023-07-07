<?php

require_once __DIR__. '/initialize.php';
// require_once 'yopayments/vendor/autoload.php';
require('YoAPI.php');
require_once 'credentials.php';

$db_connection = new Database();
$conn = $db_connection->dbConnection();
  
if (isset($_POST)):

  $yoAPI = new YoAPI($username, $password);
  $response = $yoAPI->receive_payment_notification();
  if($response['is_verified']):
    // mail('ibigega23@gmail.com', 'testing', 'Testing message');
    $checkTransaction = $conn->prepare('SELECT * FROM deposit_withdrawal_transactions INNER JOIN accounts ON deposit_withdrawal_transactions.deposit_withdrawal_transaction_account_id = accounts.account_id INNER JOIN members ON members.member_id = accounts.account_member_id INNER JOIN saccos ON deposit_withdrawal_transactions.deposit_withdrawal_transaction_sacco_id = saccos.sacco_id WHERE deposit_withdrawal_transactions.deposit_withdrawal_transaction_external_ref=:external_ref');
    $checkTransaction->bindValue(':external_ref', $response['external_ref'], PDO::PARAM_STR);
    $checkTransaction->execute();
    $rowCount = $checkTransaction->rowCount();
    if($rowCount === 0):
      mail($to, $subject, 'Invalid external reference');
    endif;
    $transaction_info = $checkTransaction->fetch(PDO::FETCH_ASSOC);
    $to = 'nevillemwije@ahuriire.com,fr.tusiime@ahuriire.com,david@ahuriire.com,'.$transaction_info['sacco_email'];
		$from = "Deposit IPN";
		$subject = $transaction_info['account_number'];
		
		$amount1 = $response['amount'];
			if ($amount1 > 0 && $amount1 <= 2500):
							    $amount = $amount1 - 250;
							    elseif ($amount1 >= 2501 && $amount1 <= 15000):
							        $amount = $amount1 - 500;
							        elseif($amount1 >= 15001 && $amount1 <= 45000):
							            $amount = $amount1 - 750;
							            elseif($amount1 >= 45001 && $amount1 <= 125000):
							                $amount = $amount1 - 1000;
							                elseif($amount1 >= 125001 && $amount1 <= 500000):
							                $amount = $amount1 - 1500;
							                elseif($amount1 >= 500001 && $amount1 <= 1000000):
							                $amount = $amount1 - 1750;
							                 elseif($amount1 >= 1000001 && $amount1 <= 2000000):
							                $amount = $amount1 - 2000;
							                endif;

      $amounttoberegistered = $amount;
      $new_account_balance =  $transaction_info['account_balance'] + $amounttoberegistered;

      $updateTransaction = $conn->prepare('UPDATE deposit_withdrawal_transactions SET deposit_withdrawal_transaction_status="successful", deposit_withdrawal_transaction_charge ="50",deposit_withdrawal_transaction_response=NOW(), deposit_withdrawal_transaction_network_ref=:network_ref WHERE deposit_withdrawal_transaction_external_ref=:external_ref AND deposit_transaction_id=:transaction_id');
	    $updateTransaction->bindValue(':external_ref', $response['external_ref'], PDO::PARAM_STR);
	    $updateTransaction->bindValue(':network_ref', $response['network_ref'], PDO::PARAM_STR);
	    $updateTransaction->bindValue(':transaction_id', $transaction_info['deposit_transaction_id'], PDO::PARAM_INT);
      $updateTransaction->execute();

      $updateAccount = $conn->prepare('UPDATE accounts SET account_balance=:account_balance WHERE account_id=:id');
       $updateAccount->bindValue(':id', $transaction_info['account_id'], PDO::PARAM_INT);
       $updateAccount->bindValue(':account_balance', $new_account_balance, PDO::PARAM_INT);
       $updateAccount->execute();

       mail($to, $subject, 'A Deposit payment of UGX '.$amount.' to '.$transaction_info['sacco_name'].' on account number: '.$transaction_info['account_number'].' has been made successfully with network reference '.$response['network_ref'].' and external reference '.$response['external_ref']. '.');
       $number = $response['msisdn'];
       $message = 'Hello, you have made a Saving of UGX '.$amount.' to '.$transaction_info['sacco_name'].' on Account Number: '.$transaction_info['account_number'].'('.$transaction_info['member_fname'].' '.$transaction_info['member_lname'].') on '.$response['date_time'].'. Thank you for saving with us.';
       $message_type = 'info';
       send_sms_dynamic($number, $message, $message_type);
        
       $_message = 'Hello '.$transaction_info['member_fname'].' '.$transaction_info['member_lname'].', a Saving of UGX '.$amount.' has been made to your Account: '.$transaction_info['account_number'].' in '.$transaction_info['sacco_name'].' on '.$response['date_time'].'. Your new account balance is UGX '.$new_account_balance.'';
       $_number= $transaction_info['member_contact'];
       $_message_type = 'info';
       send_sms_dynamic_($_number, $_message, $_message_type);
  endif;
endif;
