<?php
require_once('Database.php');

class USSD
{
  private $conn;

  function __construct()
  {

    try {
       $writeDB = DB::connectWriteDB();
       $readDB = DB::connectReadDB();
       $this->conn = $writeDB;
     }
     catch(PDOException $ex) {
       error_log("USSD Connection Error: ".$ex, 0);
       exit;
     }
  }

// send the response function
  public function sendResponse($output){
  	print_r($output);
    exit;
  }

// welcome get main menu
public function get_main_menu(){
  	$message  = "Welcome to Irembo Finance. Please choose an option below to proceed. \n";
  	$message .= "1. Savings \n";
  	$message .= "2. Withdraw \n";
  	$message .= "3. Account Balance \n";
  	$message .= "4. Share Balance \n";
  	$message .= "5. Loan Balance \n";
  	$message .= "6. Change Pin \n";
  	$action = 'request';
  	$output = "responseString=" . urlencode($message) . "&action=" . $action;
  	return $output;
  }


  public function get_account_saving_menu(){
       $message  = "Enter Account Number To Save e.g. 1***1*****1 \n";
       // $message .= "0.Back 00.Main Menu";
       $message .= "00.Main Menu";
            $action = 'request';
            $output = "responseString=" . urlencode($message) . "&action=" . $action;
            return $output;
     }

 public function get_account_withdraw_menu(){
       $message  = "Enter Account Number To Withdraw e.g. 1***1*****1 \n";
       // $message .= "0.Back 00.Main Menu";
       $message .= "00.Main Menu";
            $action = 'request';
            $output = "responseString=" . urlencode($message) . "&action=" . $action;
            // $output = "";
            // $message  = "Hello, please visit your SACCO or branch to allow mobile withdraws. \n";
            // $action = 'end';
            // $output = "responseString=" . urlencode($message) . "&action=" . $action;
            return $output;
     }


public function get_account_balance_menu(){
       $message  = "Enter Account Number To Check Balance e.g. 1***1*****1 \n";
       // $message .= "0.Back 00.Main Menu";
       $message .= "00.Main Menu";
            $action = 'request';
            $output = "responseString=" . urlencode($message) . "&action=" . $action;
            return $output;
     }

public function get_account_share_balance_menu(){
       $message  = "Enter Account Number To Check Share Balance e.g. 1***1*****1 \n";
       // $message .= "0.Back 00.Main Menu";
       $message .= "00.Main Menu";
            $action = 'request';
            $output = "responseString=" . urlencode($message) . "&action=" . $action;
            return $output;
     }

public function get_account_loan_balance_menu(){
       $message  = "Enter Account Number To Loan Balance e.g. 1***1*****1 \n";
       // $message .= "0.Back 00.Main Menu";
       $message .= "00.Main Menu";
            $action = 'request';
            $output = "responseString=" . urlencode($message) . "&action=" . $action;
            return $output;
     }

public function get_account_pin_menu(){
        $message  = "Enter Account Number To Change PIN e.g. 1***1*****1 \n";
        // $message .= "0.Back 00.Main Menu";
        $message .= "00.Main Menu";
            $action = 'request';
            $output = "responseString=" . urlencode($message) . "&action=" . $action;
            return $output;
     }

public function account_saving_amount_menu(){
      $message  = "Enter Amount To Save \n";
      // $message .= "0.Back 00.Main Menu";
      $message .= "00.Main Menu";
      $action = 'request';
      $output = "responseString=" . urlencode($message) . "&action=" . $action;
      return $output;
     }

public function account_withdraw_amount_menu(){
      // $message  = "Enter Amount To Withdraw \n";
      // // $message .= "0.Back 00.Main Menu";
      // $message .= "00.Main Menu";
      // $action = 'request';
      // $output = "responseString=" . urlencode($message) . "&action=" . $action;
        // $output = "";
            $message  = "Hello, please visit your SACCO or branch to allow mobile withdraws. \n";
            $action = 'end';
            $output = "responseString=" . urlencode($message) . "&action=" . $action;
      return $output;
    }

public function account_view_balance_pin_menu(){
      $message  = "Please enter your account PIN \n";
      // $message .= "0.Back 00.Main Menu";
      $message .= "00.Main Menu";
      $action = 'request';
      $output = "responseString=" . urlencode($message) . "&action=" . $action;
      return $output;
    }


public function get_account_information($account){
  $res = array();
  try {
    $getAccountData = $this->conn->prepare('SELECT * from members,saccos where members.saccos_sacco_id = saccos.sacco_id and members_account_number=:account');
    $getAccountData->bindParam(':account', $account, PDO::PARAM_INT);
    $execute_result = $getAccountData->execute();
    if(!$execute_result) {
      $res["success"] = false;
      error_log("get account data execute failed: (" . $getAccountData->errno . ") " . $getAccountData->error, 0);
    } else {
      $res["success"] = true;
      $res['count'] = $getAccountData->rowCount();
      $res['data'] = $getAccountData->fetch(PDO::FETCH_ASSOC);
    }
  } catch (PDOException $e) {
    $res["success"] = false;
    error_log("get account data error: ".$e, 0);
  }
  return $res;
}

public function deposit_confirmation_menu($amount, $account, $paymentDataInfo, $paymentDataSacco){
  $message  = "Saving of UGX ".$amount."  to Account Number ".$account." (".$paymentDataInfo.") in ".$paymentDataSacco."\n";
  $message .= "1. Confirm \n";
  $message .= "2. Cancel \n";
  // $message .= "0.Back 00.Main Menu";
  $message .= "00.Main Menu";
  $action = 'request';
  $output = "responseString=" . urlencode($message) . "&action=" . $action;
  return $output;
}

public function displayAccountBalances($type ,$amount, $sacconame){
  $amount =  number_format($amount);
  $message  = "Your ${type} account balance is UGX {$amount}. Thank you for saving with {$sacconame} \n";
  $action = 'end';
  $output = "responseString=" . urlencode($message) . "&action=" . $action;
  return $output;
}

 // register new transaction
 public function NewTransaction($account, $transaction_reference, $contact, $amount, $sacco, $narrative="ussd transaction", $charge, $transactionID){
  $res = array();
  try {
    $registerTransaction = $this->conn->prepare("INSERT INTO desposit_transactions(
    deposit_amount, desposit_notes, deposit_contact, members_member_id, saccos_sacco_id,
    deposit_method,deposit_external_ref, deposit_narrative,desposit_status,desposit_charge,desposit_reference)
    VALUES(:amount, 'ussd banking', :contact, :account, :sacco, 'ussd', :external_ref, :narrative, 'pending',:charge, :transid)");
    $registerTransaction->bindParam(':external_ref', $transaction_reference, PDO::PARAM_STR);
    $registerTransaction->bindParam(':account', $account, PDO::PARAM_INT);
    $registerTransaction->bindParam(':narrative', $narrative, PDO::PARAM_STR);
    $registerTransaction->bindParam(':contact', $contact, PDO::PARAM_INT);
    $registerTransaction->bindParam(':amount', $amount, PDO::PARAM_INT);
    $registerTransaction->bindParam(':sacco', $sacco, PDO::PARAM_INT);
    $registerTransaction->bindParam(':charge', $charge, PDO::PARAM_INT);
    $registerTransaction->bindParam(':transid', $transactionID, PDO::PARAM_INT);
    $registerTransaction->execute();
    $transactionCount = $registerTransaction->rowCount();
    if ($transactionCount === 0) {
      $res["success"] = false;
    } else {
      $res["success"] = true;
    }
  } catch (PDOException $e) {
    $res["success"] = false;
    error_log("register ussd transaction session error: ".$e, 0);
  }
  return $res;
}

  // confirm payment menu
 public function confirmed_payment_menu(){
    $message  = "A payment prompt has been sent to you. Enter your mobile money PIN to confirm payment. Thank for using iRembo Finance. \n";
    $action = 'end';
    $output = "responseString=" . urlencode($message) . "&action=" . $action;
    return $output;
  }

  // invalid response
  public function invalidResponse($message = "Unbale to process request"){
    $action = "end";
  	$output = "responseString=" . urlencode($message) . "&action=" . $action;
  	return $output;
  }

  // check if session exists
  public function check_session($transactionId, $contact){
    $res = array();
    try {
      $checkTransactionId = $this->conn->prepare('SELECT * from ussd_sessions where ussd_session_transaction_id = :transaction_id AND msisdn=:msisdn');
      $checkTransactionId->bindParam(':transaction_id', $transactionId, PDO::PARAM_STR);
      $checkTransactionId->bindParam(':msisdn', $contact, PDO::PARAM_STR);
      $execute_result = $checkTransactionId->execute();
      if(!$execute_result) {
        $res["success"] = false;
        // error_log("register ussd session execute failed: (" . $resgisterUssdSession->errno . ") " . $resgisterUssdSession->error, 0);
      } else {
        $res["success"] = true;
        $res['count'] = $checkTransactionId->rowCount();
      }
    } catch (PDOException $e) {
      $res["success"] = false;
      // error_log("check ussd session exception error: ".$e, 0);
    }
    return $res;
  }

  // get session data
  public function get_session_data($transactionId, $contact){
    $res = array();
    try {
      $getSessionData = $this->conn->prepare('SELECT * from ussd_sessions where ussd_session_transaction_id=:transaction_id AND msisdn=:msisdn');
    	$getSessionData->bindParam(':transaction_id', $transactionId, PDO::PARAM_STR);
    	$getSessionData->bindParam(':msisdn', $contact, PDO::PARAM_STR);
    	$execute_result = $getSessionData->execute();
      if(!$execute_result) {
        $res["success"] = false;
        error_log("get ussd session data execute failed: (" . $getSessionData->errno . ") " . $getSessionData->error, 0);
      } else {
        $res["success"] = true;
        $res['count'] = $getSessionData->rowCount();
        $res['data'] = $getSessionData->fetch(PDO::FETCH_ASSOC);
      }
    } catch (PDOException $e) {
      $res["success"] = false;
      error_log("get ussd session data error: ".$e, 0);
    }
  	return $res;
  }

  // register ussd session
  public function register_ussd_session($transactionId, $contact, $currentLevel = 0){
    $res = array();
    try {
      $registerUssdSession = $this->conn->prepare('INSERT into ussd_sessions(ussd_session_transaction_id, msisdn, current_level) values(:transaction_id, :msisdn, :current_level)');
    	$registerUssdSession->bindParam(':transaction_id', $transactionId, PDO::PARAM_STR);
    	$registerUssdSession->bindParam(':msisdn', $contact, PDO::PARAM_STR);
    	$registerUssdSession->bindParam(':current_level', $currentLevel, PDO::PARAM_STR);
      $execute_result = $registerUssdSession->execute();
      if(!$execute_result) {
        $res["success"] = false;
        error_log("register ussd session execute failed: (" . $registerUssdSession->errno . ") " . $registerUssdSession->error, 0);
      } else {
        $res["success"] = true;
      }

    } catch (PDOException $e) {
      $res["success"] = false;
      error_log("register ussd session error: ".$e, 0);
    }
    return $res;
  }

  // register ussd session
  public function updateSession($transaction_id, $contact, $cl, $pl, $nextStep='', $sessionAction='', $actionCode='' , $amount=0.00){
    $res = array();
  	try {
      $updateSession = $this->conn->prepare('UPDATE ussd_sessions SET current_level=:current_level, previous_level=:previous_level, next_step=:nextStep, action=:sessionAction, code=:actionCode, amount=:amount where ussd_session_transaction_id=:transaction_id AND msisdn=:msisdn');
    	$updateSession->bindParam(':current_level', $cl, PDO::PARAM_INT);
    	$updateSession->bindParam(':previous_level', $pl, PDO::PARAM_INT);
    	$updateSession->bindParam(':nextStep', $nextStep, PDO::PARAM_STR);
    	$updateSession->bindParam(':msisdn', $contact, PDO::PARAM_STR);
    	$updateSession->bindParam(':transaction_id', $transaction_id, PDO::PARAM_STR);
      $updateSession->bindParam(':sessionAction', $sessionAction, PDO::PARAM_STR);
      $updateSession->bindParam(':actionCode', $actionCode, PDO::PARAM_STR);
      $updateSession->bindParam(':amount', $amount, PDO::PARAM_INT);
    	$execute_result = $updateSession->execute();
    	$updateSessionCount = $updateSession->rowCount();
      if(!$execute_result || $updateSessionCount === 0) {
        $res["success"] = false;
        error_log("update ussd session execute failed: (" . $updateSession->errno . ") " . $updateSession->error, 0);
      } else {
        $res["success"] = true;
      }
  	}
  	catch(PDOException $ex) {
      $res["success"] = false;
      error_log("register ussd session error: ".$ex, 0);
  	}
    return $res;
  }

  // register ussd request
  public function register_ussd_request($transactionId, $serviceCode, $contact, $userInput){
    $res = array();
    try {
      $registerRequest = $this->conn->prepare('INSERT into ussd_requests(session_transaction_id,
        msisdn, ussd_service_code, user_input) values(:transaction_id, :msisdn, :service_code, :user_input)');
    	$registerRequest->bindParam(':transaction_id', $transactionId, PDO::PARAM_STR);
    	$registerRequest->bindParam(':service_code', $serviceCode, PDO::PARAM_STR);
    	$registerRequest->bindParam(':msisdn', $contact, PDO::PARAM_STR);
    	$registerRequest->bindParam(':user_input', $userInput, PDO::PARAM_STR);
      $execute_result = $registerRequest->execute();
      if(!$execute_result) {
        $res["success"] = false;
        error_log("register ussd request execute failed: (" . $registerRequest->errno . ") " . $registerRequest->error, 0);
      } else {
        $res["success"] = true;
      }
    } catch (PDOException $e) {
      $res["success"] = false;
      error_log("register ussd session error: ".$e, 0);
    }
    return $res;
  }

}
