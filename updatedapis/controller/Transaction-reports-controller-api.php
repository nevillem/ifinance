<?php

require_once('../core/initialize.php');
require_once('Database.php');
require_once('../model/Response.php');


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

// BEGIN OF AUTH SCRIPT
// Authenticate user with access token
// check to see if access token is provided in the HTTP Authorization header and that the value is longer than 0 chars
// don't forget the Apache fix in .htaccess file
if(!isset($_SERVER['HTTP_AUTHORIZATION']) || strlen($_SERVER['HTTP_AUTHORIZATION']) < 1) {
  $response = new Response();
  $response->setHttpStatusCode(401);
  $response->setSuccess(false);
  (!isset($_SERVER['HTTP_AUTHORIZATION']) ? $response->addMessage("Access token is missing from the header") : false);
  (strlen($_SERVER['HTTP_AUTHORIZATION']) < 1 ? $response->addMessage("Access token cannot be blank") : false);
  $response->send();
  exit;
}

// get supplied access token from authorisation header - used for delete (log out) and patch (refresh)
$accesstoken = $_SERVER['HTTP_AUTHORIZATION'];

// attempt to query the database to check token details - use write connection as it needs to be synchronous for token
try {
  // create db query to check access token is equal to the one provided
  $query = $writeDB->prepare('select user_id, branches_branch_id, access_token_expiry, user_status, saccos_sacco_id, user_fullname, user_login_attempts from sessions_users, users where sessions_users.users_user_id = users.user_id and access_token = :accesstoken');
  $query->bindParam(':accesstoken', $accesstoken, PDO::PARAM_STR);
  $query->execute();

  // get row count
  $rowCount = $query->rowCount();

  if($rowCount === 0):
    // set up response for unsuccessful log out response
    $response = new Response();
    $response->setHttpStatusCode(401);
    $response->setSuccess(false);
    $response->addMessage("Invalid access token");
    $response->send();
    exit;
  endif;

  // get returned row
  $row = $query->fetch(PDO::FETCH_ASSOC);

  // save returned details into variables
  $returned_id = $row['user_id'];
  $returned_name = $row['user_fullname'];
  $returned_accesstokenexpiry = $row['access_token_expiry'];
  $returned_active = $row['user_status'];
  $returned_loginattempts = $row['user_login_attempts'];
  $returned_saccoid = $row['saccos_sacco_id'];
  $returned_branch_id = $row['branches_branch_id'];
  // check if account is active
  if($returned_active != 'active' && $returned_active != 'inactive'):
    $response = new Response();
    $response->setHttpStatusCode(401);
    $response->setSuccess(false);
    $response->addMessage("account is not active");
    $response->send();
    exit;
  endif;

  // check if account is locked out
  if($returned_loginattempts >= 3):
    $response = new Response();
    $response->setHttpStatusCode(401);
    $response->setSuccess(false);
    $response->addMessage("account is currently locked out");
    $response->send();
    exit;
  endif;

  // check if access token has expired
  if(strtotime($returned_accesstokenexpiry) < time()):
    $response = new Response();
    $response->setHttpStatusCode(401);
    $response->setSuccess(false);
    $response->addMessage("Access token has expired");
    $response->send();
    exit;
  endif;
}
catch(PDOException $ex) {
  $response = new Response();
  $response->setHttpStatusCode(500);
  $response->setSuccess(false);
  $response->addMessage("There was an issue authenticating - please try again");
  $response->send();
  exit;
}

// END OF AUTH SCRIPT

// within this if/elseif statement, it is important to get the correct order (if query string GET param is used in multiple routes)

// check if taskid is in the url e.g. /tasks/1
if (array_key_exists("transaction",$_GET)):
  // get settings from query string
  $transaction = $_GET['transaction'];
  //check to see if loan status in query string is not empty and is number, if not return json error
  if($transaction == '' || !is_string($transaction)):
    $response = new Response();
    $response->setHttpStatusCode(400);
    $response->setSuccess(false);
    $response->addMessage("transaction status cannot be blank and must be string");
    $response->send();
    exit;
  endif;

  switch ($transaction) {
  case 'all':
  if($_SERVER['REQUEST_METHOD'] === 'POST') {
    // attempt to query the database
    if($_SERVER['CONTENT_TYPE'] !== 'application/json') {
      // set up response for unsuccessful request
      $response = new Response();
      $response->setHttpStatusCode(400);
      $response->setSuccess(false);
      $response->addMessage("Content Type header not set to JSON");
      $response->send();
      exit;
    }

    // get POST request body as the POSTed data will be JSON format
    $rawPostData = file_get_contents('php://input');

    if(!$jsonData = json_decode($rawPostData)) {
      // set up response for unsuccessful request
      $response = new Response();
      $response->setHttpStatusCode(400);
      $response->setSuccess(false);
      $response->addMessage("Request body is not valid JSON");
      $response->send();
      exit;
    }
    //check to see if task id in query string is not empty and is number, if not return json error
    if(!isset($jsonData->startdate) || empty($jsonData->startdate)|| !validateDate($jsonData->startdate)
    || !isset($jsonData->enddate)||empty($jsonData->enddate) || !validateDate($jsonData->enddate)) {
      $response = new Response();
      $response->setHttpStatusCode(400);
      $response->setSuccess(false);
      ($jsonData->startdate)? $response->addMessage("start date cannot be blank and must be provided"):false;
      (empty($jsonData->startdate))? $response->addMessage("start date cannot be blank"):false;
      (!validateDate($jsonData->startdate))? $response->addMessage("start date not valid date format"):false;

      ($jsonData->enddate)? $response->addMessage("end date cannot be blank and must be provided provided"):false;
      (!validateDate($jsonData->enddate))? $response->addMessage("end date not valid date format"):false;
      (empty($jsonData->enddate))? $response->addMessage("end date cannot be blank"):false;
      $response->send();
      exit;
    }
    try {

      $startdate= $jsonData->startdate;
      $enddate= $jsonData->enddate;
      // create db query
      // create array to store returned task
      $saccoquery = $writeDB->prepare('SELECT * FROM saccos,system_default_settings
         WHERE default_saccoid =sacco_id AND sacco_id  = :saccoid');
      $saccoquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_STR);
      $saccoquery->execute();
      $rowCount = $saccoquery->rowCount();
      $saccoArray = array();

      while($saccorow = $saccoquery->fetch(PDO::FETCH_ASSOC)):
        extract($saccorow);
      $currency= mb_strtolower($currency);
      $sacco=  array("sacconame"=>$sacco_name,"saccologo"=>$sacco_logo,
      "address"=>$sacco_address,"email"=>$sacco_email,"currency"=>mb_strtoupper($currency));

      $accountsquery = $writeDB->prepare('SELECT * FROM accounts WHERE account_sacco_id  = :saccoid');
      $accountsquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_STR);
      $accountsquery->execute();
      $saccoAccountsArray = array();
      while($saccoaccountsrow = $accountsquery->fetch(PDO::FETCH_ASSOC)):
        extract($saccoaccountsrow);
      $accounts=  array("id"=>$accounts_id,"accountcode"=>$account_code,"accountname"=>$account_name,"accountbalance"=>$opening_balance);
      $depositsquery = $readDB->prepare('SELECT * from desposit_transactions,member_accounts,members,users
        WHERE desposit_transactions.members_member_id = members.member_id
        AND desposit_transactions.deposit_account_member_id = member_accounts.member_accounts_id
        AND member_accounts.member_accounts_account_id =:id
        AND desposit_transactions.users_user_id = users.user_id
        AND desposit_transactions.desposit_timestamp BETWEEN :startdate AND :enddate
        AND desposit_transactions.saccos_sacco_id = :saccoid
        order by desposit_timestamp DESC');
      $depositsquery->bindParam(':id', $accounts_id, PDO::PARAM_INT);
      $depositsquery->bindParam(':startdate', $startdate, PDO::PARAM_INT);
      $depositsquery->bindParam(':enddate', $enddate, PDO::PARAM_INT);
      $depositsquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $depositsquery->execute();
      $transactionArray = array();
      while($row = $depositsquery->fetch(PDO::FETCH_ASSOC)) {
          extract($row);
          $deposit = array(
            "id" => $deposit_id,
            "account" => $members_account_number,
            "balance" => $total_deposit,
            "firstname" => $member_fname,
            "lastname" => $member_lname,
            "amount" => $deposit_amount,
            "deposited" => $desposit_person,
            "notes" => $desposit_notes,
            "charge" => $desposit_charge,
            "method" => $deposit_method,
            "status" => $desposit_status,
            "teller" => $user_fullname,
            "transactionID" => $desposit_trans_id,
            "timestamp" => $desposit_timestamp,
          );
         $transactionArray[] = $deposit;
        }

      $accounts['savingstrans'] =$transactionArray;
// loans disburse account
      $loansdebtsquery = $readDB->prepare('SELECT * from account_transactions,users
        where account_transactions.account_transact_id=:id
        AND account_transactions.transaction_date BETWEEN :startdate AND :enddate
        AND account_transactions.transaction_type="loan withdraw"
        and account_transactions.transact_sacco_id = :saccoid order by transaction_date DESC');
        $loansdebtsquery->bindParam(':id', $accounts_id, PDO::PARAM_INT);
        $loansdebtsquery->bindParam(':startdate', $startdate, PDO::PARAM_INT);
        $loansdebtsquery->bindParam(':enddate', $enddate, PDO::PARAM_INT);
        $loansdebtsquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
        $loansdebtsquery->execute();
        $loanswithdrawTransArray = array();
        while($loans_trans = $loansdebtsquery->fetch(PDO::FETCH_ASSOC)):
          extract($loans_trans);
          $loanstrans = array(
            "id" => $account_transaction_id,
            "transactiontype" => "Loan disbursement",
            "amount" => $transact_amount,
            "transaction_date" => $transaction_date,
          );
          $loanswithdrawTransArray[] = $loanstrans;
        endwhile;
  // loan repayments
  $loanscreditquery = $readDB->prepare('SELECT * from account_transactions,users
    where account_transactions.account_transact_id=:id
    AND account_transactions.transaction_date BETWEEN :startdate AND :enddate
    AND account_transactions.transaction_type !="loan withdraw"
    and account_transactions.transact_sacco_id = :saccoid order by transaction_date DESC');
    $loanscreditquery->bindParam(':id', $accounts_id, PDO::PARAM_INT);
    $loanscreditquery->bindParam(':startdate', $startdate, PDO::PARAM_INT);
    $loanscreditquery->bindParam(':enddate', $enddate, PDO::PARAM_INT);
    $loanscreditquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
    $loanscreditquery->execute();
    $loansaccountscreditTransArray = array();
    while($loanscredit_trans = $loanscreditquery->fetch(PDO::FETCH_ASSOC)):
      extract($loanscredit_trans);
      $loanscredittrans = array(
        "id" => $account_transaction_id,
        "transactiontype" => $transaction_type,
        "amount" => $transact_amount,
        "transaction_date" => $transaction_date,
      );
      $loansaccountscreditTransArray[] = $loanscredittrans;
    endwhile;
      // withdraws
      $transquery = $readDB->prepare('SELECT * from withdrawal_transactions, members,member_accounts,accounts,users
        where withdrawal_transactions.members_member_id = members.member_id
        AND withdrawal_transactions.withdraw_member_account_id=member_accounts.member_accounts_id
        AND withdrawal_transactions.users_user_id  = users.user_id
        AND member_accounts.member_accounts_account_id=:id
        AND withdrawal_transactions.withdraw_timestamp BETWEEN :startdate AND :enddate
        and withdrawal_transactions.saccos_sacco_id = :saccoid order by withdraw_timestamp DESC');
        $transquery->bindParam(':id', $accounts_id, PDO::PARAM_INT);
        $transquery->bindParam(':startdate', $startdate, PDO::PARAM_INT);
        $transquery->bindParam(':enddate', $enddate, PDO::PARAM_INT);
        $transquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
        $transquery->execute();
        $wtransactionArray = array();

        while($row_trans = $transquery->fetch(PDO::FETCH_ASSOC)) {
          extract($row_trans);
          $withdraw = array(
            "id" => $withdraw_id,
            "account" => $members_account_number,
            "balance" => $total_deposit,
            "firstname" => $member_fname,
            "lastname" => $member_lname,
            "amount" => $withdraw_amount,
            "withdraw" => $withdraw_person,
            "notes" => $withdraw_notes,
            "charge" => $withdraw_charge,
            "method" => ucwords($withdraw_method),
            "status" => $withdraw_status,
            "teller" => $user_fullname,
            "account_type" => $account_name,
            "transactionID" => $withdraw_trans_id,
            "timestamp" => $withdraw_timestamp,
          );
          $wtransactionArray[] = $withdraw;
        }
      $accounts['withdrawtrans'] = $wtransactionArray;
      $accounts['loansaccowcithdrawtrans'] = $loanswithdrawTransArray;
      $accounts['loanaccountscredittrans'] = $loansaccountscreditTransArray;
      $saccoAccountsArray[]= $accounts;
      endwhile;
      $sacco['accounts'] =$saccoAccountsArray;
      $saccoArray[] = $sacco;
      endwhile;
      // bundle rows returned into an array to return in the json data
      $returnData = array();
      $returnData['rows_returned'] = $rowCount;
      $returnData['sacco'] = $saccoArray;

      // set up response for successful return
      $response = new Response();
      $response->setHttpStatusCode(200);
      $response->setSuccess(true);
      $response->toCache(true);
      $response->setData($returnData);
      $response->send();
      exit;
    }
    catch(PDOException $ex) {
      error_log("Database Query Error: ".$ex, 0);
      $response = new Response();
      $response->setHttpStatusCode(500);
      $response->setSuccess(false);
      $response->addMessage("Failed to get transaction".$ex);
      $response->send();
      exit;
    }
  }
  // if any other request method apart from GET, PATCH, DELETE is used then return 405 method not allowed
  else {
    $response = new Response();
    $response->setHttpStatusCode(405);
    $response->setSuccess(false);
    $response->addMessage("Request method not allowed");
    $response->send();
    exit;
  }
// }
// // return 404 error if endpoint not available
// else {
//   $response = new Response();
//   $response->setHttpStatusCode(404);
//   $response->setSuccess(false);
//   $response->addMessage("Endpoint not found");
//   $response->send();
//   exit;
// }
break;
case 'account':
if($_SERVER['REQUEST_METHOD'] === 'POST') {
  // attempt to query the database
  if($_SERVER['CONTENT_TYPE'] !== 'application/json') {
    // set up response for unsuccessful request
    $response = new Response();
    $response->setHttpStatusCode(400);
    $response->setSuccess(false);
    $response->addMessage("Content Type header not set to JSON");
    $response->send();
    exit;
  }

  // get POST request body as the POSTed data will be JSON format
  $rawPostData = file_get_contents('php://input');

  if(!$jsonData = json_decode($rawPostData)) {
    // set up response for unsuccessful request
    $response = new Response();
    $response->setHttpStatusCode(400);
    $response->setSuccess(false);
    $response->addMessage("Request body is not valid JSON");
    $response->send();
    exit;
  }
  //check to see if task id in query string is not empty and is number, if not return json error
  if(!isset($jsonData->startdate) || empty($jsonData->startdate)|| !validateDate($jsonData->startdate)
  || !isset($jsonData->enddate)||empty($jsonData->enddate) || !validateDate($jsonData->enddate)
  || !isset($jsonData->accountid)||empty($jsonData->accountid) || !is_numeric($jsonData->accountid)
  ) {
    $response = new Response();
    $response->setHttpStatusCode(400);
    $response->setSuccess(false);
    ($jsonData->startdate)? $response->addMessage("start date cannot be blank and must be provided"):false;
    (empty($jsonData->startdate))? $response->addMessage("start date cannot be blank"):false;
    (!validateDate($jsonData->startdate))? $response->addMessage("start date not valid date format"):false;
    ($jsonData->enddate)? $response->addMessage("end date cannot be blank and must be provided provided"):false;
    (!validateDate($jsonData->enddate))? $response->addMessage("end date not valid date format"):false;
    (empty($jsonData->enddate))? $response->addMessage("end date cannot be blank"):false;
    ($jsonData->accountid)? $response->addMessage("account cannot be blank and must be provided provided"):false;
    (!is_numeric($jsonData->accountid))? $response->addMessage("account must be an integer"):false;
    (empty($jsonData->accountid))? $response->addMessage("account cannot be blank"):false;
    $response->send();
    exit;
  }
  try {

    $startdate= $jsonData->startdate;
    $enddate= $jsonData->enddate;
    // create db query
    // create array to store returned task
    $saccoquery = $writeDB->prepare('SELECT * FROM saccos,system_default_settings
       WHERE default_saccoid =sacco_id AND sacco_id  = :saccoid');
    $saccoquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_STR);
    $saccoquery->execute();
    $rowCount = $saccoquery->rowCount();
    $saccoArray = array();

    while($saccorow = $saccoquery->fetch(PDO::FETCH_ASSOC)):
      extract($saccorow);
    $currency= mb_strtolower($currency);
    $sacco=  array("sacconame"=>$sacco_name,"saccologo"=>$sacco_logo,
    "address"=>$sacco_address,"email"=>$sacco_email,"currency"=>mb_strtoupper($currency));

    $accountsquery = $writeDB->prepare('SELECT * FROM accounts WHERE account_sacco_id  = :saccoid AND accounts_id=:accountid');
    $accountsquery->bindParam(':accountid', $jsonData->accountid, PDO::PARAM_STR);
    $accountsquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_STR);
    $accountsquery->execute();
    $saccoAccountsArray = array();
    while($saccoaccountsrow = $accountsquery->fetch(PDO::FETCH_ASSOC)):
      extract($saccoaccountsrow);
    $accounts=  array("id"=>$accounts_id,"accountcode"=>$account_code,"accountname"=>$account_name,"accountbalance"=>$opening_balance);
    $depositsquery = $readDB->prepare('SELECT * from desposit_transactions,member_accounts,members,users
      WHERE desposit_transactions.members_member_id = members.member_id
      AND desposit_transactions.deposit_account_member_id = member_accounts.member_accounts_id
      AND member_accounts.member_accounts_account_id =:id
      AND desposit_transactions.users_user_id = users.user_id
      AND desposit_transactions.desposit_timestamp BETWEEN :startdate AND :enddate
      AND desposit_transactions.saccos_sacco_id = :saccoid
      order by desposit_timestamp DESC');
    $depositsquery->bindParam(':id', $accounts_id, PDO::PARAM_INT);
    $depositsquery->bindParam(':startdate', $startdate, PDO::PARAM_INT);
    $depositsquery->bindParam(':enddate', $enddate, PDO::PARAM_INT);
    $depositsquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
    $depositsquery->execute();
    $transactionArray = array();
    while($row = $depositsquery->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $deposit = array(
          "id" => $deposit_id,
          "account" => $members_account_number,
          "balance" => $total_deposit,
          "firstname" => $member_fname,
          "lastname" => $member_lname,
          "amount" => $deposit_amount,
          "deposited" => $desposit_person,
          "notes" => $desposit_notes,
          "charge" => $desposit_charge,
          "method" => $deposit_method,
          "status" => $desposit_status,
          "teller" => $user_fullname,
          "transactionID" => $desposit_trans_id,
          "timestamp" => $desposit_timestamp,
        );
       $transactionArray[] = $deposit;
      }

    $accounts['savingstrans'] =$transactionArray;
// loans disburse account
    $loansdebtsquery = $readDB->prepare('SELECT * from account_transactions,users
      where account_transactions.account_transact_id=:id
      AND account_transactions.transaction_date BETWEEN :startdate AND :enddate
      AND account_transactions.transaction_type="loan withdraw"
      and account_transactions.transact_sacco_id = :saccoid order by transaction_date DESC');
      $loansdebtsquery->bindParam(':id', $accounts_id, PDO::PARAM_INT);
      $loansdebtsquery->bindParam(':startdate', $startdate, PDO::PARAM_INT);
      $loansdebtsquery->bindParam(':enddate', $enddate, PDO::PARAM_INT);
      $loansdebtsquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $loansdebtsquery->execute();
      $loanswithdrawTransArray = array();
      while($loans_trans = $loansdebtsquery->fetch(PDO::FETCH_ASSOC)):
        extract($loans_trans);
        $loanstrans = array(
          "id" => $account_transaction_id,
          "transactiontype" => "Loan disbursement",
          "amount" => $transact_amount,
          "transaction_date" => $transaction_date,
        );
        $loanswithdrawTransArray[] = $loanstrans;
      endwhile;
// loan repayments
$loanscreditquery = $readDB->prepare('SELECT * from account_transactions,users
  where account_transactions.account_transact_id=:id
  AND account_transactions.transaction_date BETWEEN :startdate AND :enddate
  AND account_transactions.transaction_type !="loan withdraw"
  and account_transactions.transact_sacco_id = :saccoid order by transaction_date DESC');
  $loanscreditquery->bindParam(':id', $accounts_id, PDO::PARAM_INT);
  $loanscreditquery->bindParam(':startdate', $startdate, PDO::PARAM_INT);
  $loanscreditquery->bindParam(':enddate', $enddate, PDO::PARAM_INT);
  $loanscreditquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
  $loanscreditquery->execute();
  $loansaccountscreditTransArray = array();
  while($loanscredit_trans = $loanscreditquery->fetch(PDO::FETCH_ASSOC)):
    extract($loanscredit_trans);
    $loanscredittrans = array(
      "id" => $account_transaction_id,
      "transactiontype" => $transaction_type,
      "amount" => $transact_amount,
      "transaction_date" => $transaction_date,
    );
    $loansaccountscreditTransArray[] = $loanscredittrans;
  endwhile;
    // withdraws
    $transquery = $readDB->prepare('SELECT * from withdrawal_transactions, members,member_accounts,accounts,users
      where withdrawal_transactions.members_member_id = members.member_id
      AND withdrawal_transactions.withdraw_member_account_id=member_accounts.member_accounts_id
      AND withdrawal_transactions.users_user_id  = users.user_id
      AND member_accounts.member_accounts_account_id=:id
      AND withdrawal_transactions.withdraw_timestamp BETWEEN :startdate AND :enddate
      and withdrawal_transactions.saccos_sacco_id = :saccoid order by withdraw_timestamp DESC');
      $transquery->bindParam(':id', $accounts_id, PDO::PARAM_INT);
      $transquery->bindParam(':startdate', $startdate, PDO::PARAM_INT);
      $transquery->bindParam(':enddate', $enddate, PDO::PARAM_INT);
      $transquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $transquery->execute();
      $wtransactionArray = array();

      while($row_trans = $transquery->fetch(PDO::FETCH_ASSOC)) {
        extract($row_trans);
        $withdraw = array(
          "id" => $withdraw_id,
          "account" => $members_account_number,
          "balance" => $total_deposit,
          "firstname" => $member_fname,
          "lastname" => $member_lname,
          "amount" => $withdraw_amount,
          "withdraw" => $withdraw_person,
          "notes" => $withdraw_notes,
          "charge" => $withdraw_charge,
          "method" => ucwords($withdraw_method),
          "status" => $withdraw_status,
          "teller" => $user_fullname,
          "account_type" => $account_name,
          "transactionID" => $withdraw_trans_id,
          "timestamp" => $withdraw_timestamp,
        );
        $wtransactionArray[] = $withdraw;
      }
    $accounts['withdrawtrans'] = $wtransactionArray;
    $accounts['loansaccowcithdrawtrans'] = $loanswithdrawTransArray;
    $accounts['loanaccountscredittrans'] = $loansaccountscreditTransArray;
    $saccoAccountsArray[]= $accounts;
    endwhile;
    $sacco['accounts'] =$saccoAccountsArray;
    $saccoArray[] = $sacco;
    endwhile;
    // bundle rows returned into an array to return in the json data
    $returnData = array();
    $returnData['rows_returned'] = $rowCount;
    $returnData['sacco'] = $saccoArray;

    // set up response for successful return
    $response = new Response();
    $response->setHttpStatusCode(200);
    $response->setSuccess(true);
    $response->toCache(true);
    $response->setData($returnData);
    $response->send();
    exit;
  }
  catch(PDOException $ex) {
    error_log("Database Query Error: ".$ex, 0);
    $response = new Response();
    $response->setHttpStatusCode(500);
    $response->setSuccess(false);
    $response->addMessage("Failed to get transaction".$ex);
    $response->send();
    exit;
  }
}
// if any other request method apart from GET, PATCH, DELETE is used then return 405 method not allowed
else {
  $response = new Response();
  $response->setHttpStatusCode(405);
  $response->setSuccess(false);
  $response->addMessage("Request method not allowed");
  $response->send();
  exit;
}
break;
case 'accountbalances':
if($_SERVER['REQUEST_METHOD'] === 'GET') {
  try {

    // create db query
    // create array to store returned task
    $saccoquery = $writeDB->prepare('SELECT * FROM saccos,system_default_settings
       WHERE default_saccoid =sacco_id AND sacco_id  = :saccoid');
    $saccoquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_STR);
    $saccoquery->execute();
    $rowCount = $saccoquery->rowCount();
    $saccoArray = array();

    while($saccorow = $saccoquery->fetch(PDO::FETCH_ASSOC)):
      extract($saccorow);
    $currency= mb_strtolower($currency);
    $sacco=  array("sacconame"=>$sacco_name,"saccologo"=>$sacco_logo,
    "address"=>$sacco_address,"email"=>$sacco_email,"currency"=>mb_strtoupper($currency));

    $memberquery = $readDB->prepare('SELECT * FROM members,saccos
    where members.saccos_sacco_id = saccos.sacco_id');
    $memberquery->bindParam(':id', $accountid, PDO::PARAM_INT);
    $memberquery->execute();
    $memberArray = array();
    while($members = $memberquery->fetch(PDO::FETCH_ASSOC)){
    extract($members);
    $member = array(
      "id" => $member_id,
      "account" => $members_account_number,
      "firstname" => $member_fname,
      "midlename" => $member_mname,
      "lastname" => $member_lname,
      "marital_status" => $member_marital_status,
      "gender" => $member_gender,
      "nin" => $member_identification,
    );
    $accountsquery = $writeDB->prepare('SELECT * FROM accounts, member_accounts
      WHERE account_sacco_id  = :saccoid AND member_accounts_member_id=:memberid
      AND member_accounts.member_accounts_account_id= accounts.accounts_id');
    $accountsquery->bindParam(':memberid', $member_id, PDO::PARAM_STR);
    $accountsquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_STR);
    $accountsquery->execute();
    $saccoAccountsArray = array();
    while($saccoaccountsrow = $accountsquery->fetch(PDO::FETCH_ASSOC)):
      extract($saccoaccountsrow);
    $accounts=  array("memberaccountid"=>$member_accounts_id,"accountcode"=>$account_code,"accountname"=>$account_name,
    "member_balance"=>$total_deposit);
    $saccoAccountsArray[]= $accounts;
    endwhile;
    $member['memberaccounts']=$saccoAccountsArray;
    $memberArray[]=$member;
    }
    $sacco['members'] =$memberArray;
    $saccoArray[] = $sacco;
    endwhile;
    // bundle rows returned into an array to return in the json data
    $returnData = array();
    $returnData['rows_returned'] = $rowCount;
    $returnData['sacco'] = $saccoArray;

    // set up response for successful return
    $response = new Response();
    $response->setHttpStatusCode(200);
    $response->setSuccess(true);
    $response->toCache(true);
    $response->setData($returnData);
    $response->send();
    exit;
  }
  catch(PDOException $ex) {
    error_log("Database Query Error: ".$ex, 0);
    $response = new Response();
    $response->setHttpStatusCode(500);
    $response->setSuccess(false);
    $response->addMessage("Failed to get transaction".$ex);
    $response->send();
    exit;
  }
}
// if any other request method apart from GET, PATCH, DELETE is used then return 405 method not allowed
else {
  $response = new Response();
  $response->setHttpStatusCode(405);
  $response->setSuccess(false);
  $response->addMessage("Request method not allowed");
  $response->send();
  exit;
}
break;
case 'accountstatement':
if($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    // check request's content type header is JSON
    if($_SERVER['CONTENT_TYPE'] !== 'application/json') {
      // set up response for unsuccessful request
      $response = new Response();
      $response->setHttpStatusCode(400);
      $response->setSuccess(false);
      $response->addMessage("Content Type header not set to JSON");
      $response->send();
      exit;
    }

    // get POST request body as the POSTed data will be JSON format
    $rawPostData = file_get_contents('php://input');

    if(!$jsonData = json_decode($rawPostData)) {
      // set up response for unsuccessful request
      $response = new Response();
      $response->setHttpStatusCode(400);
      $response->setSuccess(false);
      $response->addMessage("Request body is not valid JSON");
      $response->send();
      exit;
    }
    if(!isset($jsonData->startdate) || empty($jsonData->startdate)|| !validateDate($jsonData->startdate)
    || !isset($jsonData->enddate)||empty($jsonData->enddate) || !validateDate($jsonData->enddate)
    || !isset($jsonData->accountid)||empty($jsonData->accountid) || !is_numeric($jsonData->accountid)
    ) {
      $response = new Response();
      $response->setHttpStatusCode(400);
      $response->setSuccess(false);
      ($jsonData->startdate)? $response->addMessage("start date cannot be blank and must be provided"):false;
      (empty($jsonData->startdate))? $response->addMessage("start date cannot be blank"):false;
      (!validateDate($jsonData->startdate))? $response->addMessage("start date not valid date format"):false;
      ($jsonData->enddate)? $response->addMessage("end date cannot be blank and must be provided provided"):false;
      (!validateDate($jsonData->enddate))? $response->addMessage("end date not valid date format"):false;
      (empty($jsonData->enddate))? $response->addMessage("end date cannot be blank"):false;
      ($jsonData->accountid)? $response->addMessage("account cannot be blank and must be provided provided"):false;
      (!is_numeric($jsonData->accountid))? $response->addMessage("account must be an integer"):false;
      (empty($jsonData->accountid))? $response->addMessage("account cannot be blank"):false;
      $response->send();
      exit;
    }
    // create db query
    // create array to store returned task
    $saccoquery = $writeDB->prepare('SELECT * FROM saccos,system_default_settings
       WHERE default_saccoid =sacco_id AND sacco_id  = :saccoid');
    $saccoquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_STR);
    $saccoquery->execute();
    $rowCount = $saccoquery->rowCount();
    $saccoArray = array();

    while($saccorow = $saccoquery->fetch(PDO::FETCH_ASSOC)):
      extract($saccorow);
    $currency= mb_strtolower($currency);
    $sacco=  array("sacconame"=>$sacco_name,"saccologo"=>$sacco_logo,
    "address"=>$sacco_address,"email"=>$sacco_email,"currency"=>mb_strtoupper($currency));
    $memberquery = $readDB->prepare('SELECT * FROM members,saccos,member_accounts
    WHERE members.saccos_sacco_id = saccos.sacco_id
    AND members.member_id=member_accounts.member_accounts_member_id
    AND member_accounts.member_accounts_id=:id');
    $memberquery->bindParam(':id', $jsonData->accountid, PDO::PARAM_INT);
    $memberquery->execute();
    $memberArray = array();
    $memberquery->rowCount();
    while($members = $memberquery->fetch(PDO::FETCH_ASSOC)){
    extract($members);
    $member = array(
      "id" => $member_id,
      "account" => $members_account_number,
      "firstname" => $member_fname,
      "midlename" => $member_mname,
      "lastname" => $member_lname,
      "marital_status" => $member_marital_status,
      "gender" => $member_gender,
      "nin" => $member_identification,
    );
    $accountsquery = $writeDB->prepare('SELECT * FROM accounts, member_accounts
      WHERE account_sacco_id  = :saccoid AND member_accounts_id=:account
      AND member_accounts.member_accounts_account_id= accounts.accounts_id');
    $accountsquery->bindParam(':account', $jsonData->accountid, PDO::PARAM_STR);
    $accountsquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_STR);
    $accountsquery->execute();
    $saccoAccountsArray = array();
    while($saccoaccountsrow = $accountsquery->fetch(PDO::FETCH_ASSOC)):
      extract($saccoaccountsrow);
    $accounts=  array("memberaccountid"=>$member_accounts_id,"accountcode"=>$account_code,"accountname"=>$account_name,
    "member_balance"=>$total_deposit);

    $query = $readDB->prepare('SELECT * from desposit_transactions, members,
      member_accounts,users
      WHERE member_accounts.member_accounts_id = desposit_transactions.deposit_account_member_id
      AND desposit_transactions.users_user_id  = users.user_id
      AND desposit_transactions.members_member_id  = member_accounts.member_accounts_member_id
      AND members.member_id=member_accounts.member_accounts_member_id
      AND member_accounts.member_accounts_id=:memberaccount
      AND desposit_transactions.desposit_timestamp BETWEEN :startdate AND :enddate
      AND desposit_transactions.saccos_sacco_id = :saccoid order by desposit_timestamp DESC');
    $query->bindParam(':memberaccount', $jsonData->accountid, PDO::PARAM_INT);
    $query->bindParam(':startdate', $jsonData->startdate, PDO::PARAM_INT);
    $query->bindParam(':enddate', $jsonData->enddate, PDO::PARAM_INT);
    $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
    $query->execute();
    // get row count
    // create array to store returned task
    $transactionArray = array();

    while($row = $query->fetch(PDO::FETCH_ASSOC)) {
      extract($row);
     $deposit = array(
       "id" => $deposit_id,
       "amount" => $deposit_amount,
       "deposited" => $desposit_person,
       "notes" => $desposit_notes,
       "method" => $deposit_method,
       "status" => $desposit_status,
       "teller" => $user_fullname,
       "transactionID" => $desposit_trans_id,
       "timestamp" => $desposit_timestamp,
     );
     $transactionArray[] = $deposit;
    }
    // withdraws
    $transquery = $readDB->prepare('SELECT * from withdrawal_transactions,member_accounts,users
      where withdrawal_transactions.withdraw_member_account_id=member_accounts.member_accounts_id
      AND withdrawal_transactions.users_user_id  = users.user_id
      AND withdrawal_transactions.withdraw_member_account_id=:memberaccount
      AND withdrawal_transactions.withdraw_timestamp BETWEEN :startdate AND :enddate
      and withdrawal_transactions.saccos_sacco_id = :saccoid order by withdraw_timestamp DESC');
      $transquery->bindParam(':memberaccount', $jsonData->accountid, PDO::PARAM_INT);
      $transquery->bindParam(':startdate', $jsonData->startdate, PDO::PARAM_INT);
      $transquery->bindParam(':enddate', $jsonData->enddate, PDO::PARAM_INT);
      $transquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $transquery->execute();
      $wtransactionArray = array();

      while($row_trans = $transquery->fetch(PDO::FETCH_ASSOC)) {
        extract($row_trans);
        $withdraw = array(
          "id" => $withdraw_id,
          "amount" => $withdraw_amount,
          "withdraw" => $withdraw_person,
          "notes" => $withdraw_notes,
          "charge" => $withdraw_charge,
          "method" => ucwords($withdraw_method),
          "status" => $withdraw_status,
          "teller" => $user_fullname,
          "account_type" => $account_name,
          "transactionID" => $withdraw_trans_id,
          "timestamp" => $withdraw_timestamp,
        );
        $wtransactionArray[] = $withdraw;
      }
    $accounts['savingtransactions']= $transactionArray;
    $accounts['withdrawtransactions']= $wtransactionArray;
    $saccoAccountsArray[]= $accounts;
    endwhile;
    $member['memberaccounts']=$saccoAccountsArray;
    $memberArray[]=$member;
    }
    $sacco['members'] =$memberArray;
    $saccoArray[] = $sacco;
    endwhile;
    // bundle rows returned into an array to return in the json data
    $returnData = array();
    $returnData['rows_returned'] = $rowCount;
    $returnData['sacco'] = $saccoArray;

    // set up response for successful return
    $response = new Response();
    $response->setHttpStatusCode(200);
    $response->setSuccess(true);
    $response->toCache(true);
    $response->setData($returnData);
    $response->send();
    exit;
  }
  catch(PDOException $ex) {
    error_log("Database Query Error: ".$ex, 0);
    $response = new Response();
    $response->setHttpStatusCode(500);
    $response->setSuccess(false);
    $response->addMessage("Failed to get transaction".$ex);
    $response->send();
    exit;
  }
}
// if any other request method apart from GET, PATCH, DELETE is used then return 405 method not allowed
else {
  $response = new Response();
  $response->setHttpStatusCode(405);
  $response->setSuccess(false);
  $response->addMessage("Request method not allowed");
  $response->send();
  exit;
}
break;
default:
$response = new Response();
$response->setHttpStatusCode(423);
$response->setSuccess(false);
$response->addMessage("service error - Attached Info");
$response->send();
exit;
}
else:
$response = new Response();
$response->setHttpStatusCode(417);
$response->setSuccess(false);
$response->addMessage("End point not found");
$response->send();
exit;
endif;
