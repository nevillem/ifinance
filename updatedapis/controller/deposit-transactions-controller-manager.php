<?php

/* The above code is including the files that are needed for the script to run. */
require_once('../core/initialize.php');
require_once('Database.php');
require_once('../model/Response.php');


// attempt to set up connections to read and write db connections
/* Connecting to the database. */
try {
  $writeDB = DB::connectWriteDB();
  $readDB = DB::connectReadDB();
}
catch(PDOException $ex) {
  // log connection error for troubleshooting and return a json error response
/* The above code is checking if the connection to the database is successful. If it is not successful,
it will log the error and send a response to the user. */
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
/* Checking if the HTTP_AUTHORIZATION is set and if it is not set, it is setting the HTTP status code
to 401 and sending a response. */
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
if (array_key_exists("transactionid",$_GET)) {
  // get task id from query string
  $transactionid = $_GET['transactionid'];
  //check to see if task id in query string is not empty and is number, if not return json error
  if($transactionid == '' || !is_numeric($transactionid)) {
    $response = new Response();
    $response->setHttpStatusCode(400);
    $response->setSuccess(false);
    $response->addMessage("transactionID cannot be blank or must be numeric");
    $response->send();
    exit;
  }

  // if request is a GET, e.g. get transaction
  if($_SERVER['REQUEST_METHOD'] === 'GET') {
    // attempt to query the database
    try {
      // create db query
      // ADD AUTH TO QUERY
      $query = $readDB->prepare('SELECT * from desposit_transactions, members,member_accounts, users, accounts
        WHERE desposit_transactions.members_member_id = members.member_id
        AND member_accounts.member_accounts_id = desposit_transactions.deposit_account_member_id
        AND desposit_transactions.members_member_id  = member_accounts.member_accounts_member_id
        AND desposit_transactions.users_user_id = users.user_id
        AND member_type="individual"
        AND accounts.account_types="saving"
        AND accounts.accounts_id=member_accounts.member_accounts_account_id
        AND members.member_id=member_accounts.member_accounts_member_id
        AND desposit_transactions.deposit_id =:transactionid
        AND desposit_transactions.saccos_sacco_id = :saccoid ORDER BY deposit_id ASC');
      $query->bindParam(':transactionid', $transactionid, PDO::PARAM_INT);
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
  		$query->execute();

      // get row count
      $rowCount = $query->rowCount();

      if($rowCount === 0) {
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("transaction not found");
        $response->send();
        exit;
      }

      // create array to store returned task
      $transactionSingleArray = array();

      while($row = $query->fetch(PDO::FETCH_ASSOC)) {
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
       $transactionSingleArray[] = $deposit;
      }

      // bundle rows returned into an array to return in the json data
      $returnData = array();
      $returnData['rows_returned'] = $rowCount;
      $returnData['transaction'] = $transactionSingleArray;

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
  // else if request if a DELETE e.g. delete
  elseif($_SERVER['REQUEST_METHOD'] === 'DELETE') {}
  // handle updating task
  elseif($_SERVER['REQUEST_METHOD'] === 'PATCH') {}
  // if any other request method apart from GET, PATCH, DELETE is used then return 405 method not allowed
  else {
    $response = new Response();
    $response->setHttpStatusCode(405);
    $response->setSuccess(false);
    $response->addMessage("Request method not allowed");
    $response->send();
    exit;
  }
}
// handle getting all tasks or creating a new one
elseif(empty($_GET)) {

  // if request is a GET e.g. get members
  if($_SERVER['REQUEST_METHOD'] === 'GET') {

    // attempt to query the database
    try {
      // ADD AUTH TO QUERY
      $query = $readDB->prepare('SELECT * from desposit_transactions, members,
        member_accounts, accounts, users
        WHERE desposit_transactions.members_member_id = members.member_id
        AND member_accounts.member_accounts_id = desposit_transactions.deposit_account_member_id
        AND desposit_transactions.users_user_id  = users.user_id
        AND desposit_transactions.members_member_id  = member_accounts.member_accounts_member_id
        AND member_type="individual"
        AND accounts.account_types="saving"
        AND accounts.accounts_id=member_accounts.member_accounts_account_id
        AND members.member_id=member_accounts.member_accounts_member_id
        AND desposit_transactions.saccos_sacco_id = :saccoid order by deposit_id DESC');
      // $query->bindParam(':userid', $returned_id, PDO::PARAM_INT);
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $query->execute();

      // get row count
      $rowCount = $query->rowCount();
      // create array to store returned task
      $transactionArray = array();

      while($row = $query->fetch(PDO::FETCH_ASSOC)) {
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

      // bundle rows returned into an array to return in the json data
      $returnData = array();
      $returnData['rows_returned'] = $rowCount;
      $returnData['transactions'] = $transactionArray;

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
      $response->addMessage("Failed to get transaction $ex");
      $response->send();
      exit;
    }
  }
  // else if request is a POST e.g. create member
  elseif($_SERVER['REQUEST_METHOD'] === 'POST') {

    // create task
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

      // check if post request contains data in body as these are mandatory
      if(!isset($jsonData->account)|| !isset($jsonData->member_account) || !isset($jsonData->amount)
      || !isset($jsonData->mop)|| !isset($jsonData->dop)|| !isset($jsonData->pincode)
          || !isset($jsonData->deposited) || empty($jsonData->account) || empty($jsonData->member_account) || empty($jsonData->amount)
          || empty($jsonData->deposited) || empty($jsonData->pincode)) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        (!isset($jsonData->account) ? $response->addMessage("member field is mandatory and must be provided") : false);
        (!isset($jsonData->member_account) ? $response->addMessage("member account field is mandatory and must be provided") : false);
        (!isset($jsonData->amount) ? $response->addMessage("amount field is mandatory and must be provided") : false);
        (!isset($jsonData->mop) ? $response->addMessage("mode of payment field is mandatory and must be provided") : false);
        (!isset($jsonData->dop) ? $response->addMessage("date of payment field is mandatory and must be provided") : false);
        (!isset($jsonData->deposited) ? $response->addMessage("deposited field is mandatory and must be provided") : false);
        (!isset($jsonData->pincode) ? $response->addMessage("pincode field is mandatory and must be provided") : false);
        (empty($jsonData->account) ? $response->addMessage("account field must not be empty") : false);
        (empty($jsonData->member_account) ? $response->addMessage("member account field must not empty") : false);
        (empty($jsonData->amount) ? $response->addMessage("amount field must not be empty") : false);
        (empty($jsonData->deposited) ? $response->addMessage("deposited field must not be empty") : false);
        (empty($jsonData->pincode) ? $response->addMessage("pincode field must not be empty") : false);
        $response->send();
        exit;
      }

      $account = $jsonData->account;
      $member_account = $jsonData->member_account;
      $amount = (int)$jsonData->amount;
      $deposited = $jsonData->deposited;
      $notes = $jsonData->notes;
      $bank = isset($jsonData->bank) ? $jsonData->bank : '';
      $pincode = (int) $jsonData->pincode;
      $transactionMethod = $jsonData->mop;
      $date_of_payment = $jsonData->dop;

      // check whether the pin is valid
      $query = $readDB->prepare('select user_pincode from users where user_id = :userid');
      $query->bindParam(':userid', $returned_id, PDO::PARAM_INT);
      $query->execute();
      $rowCount = $query->rowCount();

      if ($rowCount === 0) {
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("user not found");
        $response->send();
        exit;
      }

      // get  row returned
      $row = $query->fetch(PDO::FETCH_ASSOC);
      // return the row of pin
      $returned_pin = $row['user_pincode'];
      //verify the pincode
      if(!password_verify($pincode, $returned_pin)){
        // send response
        $response = new Response();
        $response->setHttpStatusCode(401);
        $response->setSuccess(false);
        $response->addMessage("your pincode is wrong");
        $response->send();
        exit;
      }

      // select account properties
      $memberquery = $readDB->prepare('SELECT * from members, saccos,member_accounts
      WHERE members.saccos_sacco_id = saccos.sacco_id
      AND member_id = :account');
      $memberquery->bindParam(':account', $account, PDO::PARAM_INT);
      $memberquery->execute();
      $memberRowCount = $memberquery->rowCount();

      if ($memberRowCount === 0) {
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("member not found");
        $response->send();
        exit;
      }

      $query = $readDB->prepare('SELECT * from members, saccos,member_accounts
        WHERE members.saccos_sacco_id = saccos.sacco_id
        AND member_accounts_id =:member_account
        AND member_id=member_accounts_member_id
        AND member_id = :account');
        $query->bindParam(':account', $account, PDO::PARAM_INT);
        $query->bindParam(':member_account', $member_account, PDO::PARAM_INT);
        $query->execute();
        $rowCount = $query->rowCount();

        if ($rowCount === 0) {
          $response = new Response();
          $response->setHttpStatusCode(404);
          $response->setSuccess(false);
          $response->addMessage("member acount not found");
          $response->send();
          exit;
        }
      $row = $query->fetch(PDO::FETCH_ASSOC);

      $account_status = $row['account_status'];
      $firstname = $row['member_fname'];
      $lastname = $row['member_lname'];
      $accountContact = $row['member_contact'];
      $memberID = $row['member_id'];
      $AccountBalance = (int) $row['total_deposit'];
      $AccountNumber = $row['members_account_number'];
      $saccoName = $row['sacco_short_name'];
      $saccoEmail = $row['sacco_email'];
      $sacco_sms_status = $row['sacco_sms_status'];
      $sacco_email_status = $row['sacco_email_status'];
      $maccountsid = $row['member_accounts_account_id'];

      if ($account_status !== 'active') {
        $response = new Response();
        $response->setHttpStatusCode(403);
        $response->setSuccess(false);
        $response->addMessage("account has been blocked");
        $response->send();
        exit;
      }

      $accountquery = $readDB->prepare('SELECT * from accounts   WHERE accounts_id =:account ');
      $accountquery->bindParam(':account', $maccountsid, PDO::PARAM_INT);
      $accountquery->execute();
      $rowaccountCount = $accountquery->rowCount();

        if ($rowaccountCount === 0) {
          $response = new Response();
          $response->setHttpStatusCode(404);
          $response->setSuccess(false);
          $response->addMessage("account not found");
          $response->send();
          exit;
        }
        $rowAccount = $accountquery->fetch(PDO::FETCH_ASSOC);
        $accountsid = $rowAccount['accounts_id'];
        $openingbalance = $rowAccount['opening_balance'];

      $transactionID = getGUIDnoHash();
      $transactionStatus = 'successful';
      // $transactionMethod = 'cash';
      $despositCharge = (int) 0;
      // make the new account balance
      $newAccountBalance = $AccountBalance + $amount;
      try {
      $writeDB->beginTransaction();
      $query = $writeDB->prepare('INSERT into desposit_transactions
      (deposit_amount,
        desposit_timestamp,
        desposit_balance,
        desposit_notes,
        desposit_person,
        desposit_charge,
      desposit_trans_id,
      desposit_status,
      deposit_method,
      members_member_id,
      saccos_sacco_id,
      users_user_id,branches_branch_id,deposit_account_member_id, deposit_bank_account)
      values(:amount,:dop,:balance, :notes, :person, :charge, :transID, :status,
       :method, :member, :saccoid, :userid,:branch,:member_account,:bank)');

       $query->bindParam(':amount', $amount, PDO::PARAM_INT);
      $query->bindParam(':dop', $date_of_payment, PDO::PARAM_INT);
      $query->bindParam(':balance', $newAccountBalance, PDO::PARAM_INT);
      $query->bindParam(':notes', $notes, PDO::PARAM_STR);
      $query->bindParam(':person', $deposited, PDO::PARAM_STR);
      $query->bindParam(':charge', $despositCharge, PDO::PARAM_INT);
      $query->bindParam(':transID', $transactionID, PDO::PARAM_STR);
      $query->bindParam(':status', $transactionStatus, PDO::PARAM_STR);
      $query->bindParam(':method', $transactionMethod, PDO::PARAM_STR);
      $query->bindParam(':member', $memberID, PDO::PARAM_INT);
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $query->bindParam(':userid', $returned_id, PDO::PARAM_INT);
      $query->bindParam(':branch', $returned_branch_id, PDO::PARAM_INT);
      $query->bindParam(':member_account', $member_account, PDO::PARAM_INT);
      $query->bindParam(':bank', $bank, PDO::PARAM_INT);
      $query->execute();

      // get row count
      $rowCount = $query->rowCount();
      // check if row was actually inserted, PDO exception should have caught it if not.
      if($rowCount === 0) {
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage("failed to make transaction");
        $response->send();
        exit;
      }

      // get last task id so we can return the Task in the json
      $lastID = $writeDB->lastInsertId();
      // update the member account balance
      $updateBalance = $writeDB->prepare('UPDATE member_accounts set total_deposit = :amount
         WHERE member_accounts_id=:member_account AND member_accounts_member_id=:memberid');
      $updateBalance->bindParam(':amount', $newAccountBalance, PDO::PARAM_INT);
      $updateBalance->bindParam(':member_account', $member_account, PDO::PARAM_INT);
      $updateBalance->bindParam(':memberid', $account, PDO::PARAM_INT);
      $updateBalance->execute();
      $rowUpdateBalanceCount = $updateBalance->rowCount();

      if($rowUpdateBalanceCount === 0){
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("there was an issue updating the account balanceee");
        $response->send();
        exit;
      }
      $newopeningbalance = $openingbalance + $amount;
      $updateaccount = $writeDB->prepare('UPDATE accounts set opening_balance = :amount
         WHERE accounts_id=:id');
      $updateaccount->bindParam(':amount', $newopeningbalance, PDO::PARAM_INT);
      $updateaccount->bindParam(':id', $accountsid, PDO::PARAM_INT);
      $updateaccount->execute();
      $rowUpdateAccountCount = $updateaccount->rowCount();
      if($rowUpdateAccountCount === 0){
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("there was an issue updating the account opening balance");
        $response->send();
        exit;
      }
      //commit the change
      $writeDB->commit();

    }catch (PDOException $ex) {
        $writeDB->rollBack();
        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage("There was an issue making the transaction".$ex);
        $response->send();
        exit;
      }

      // create db query to get newly created - get from master db not read slave as replication may be too slow for successful read
      $query = $writeDB->prepare('SELECT * from desposit_transactions,users
        WHERE desposit_transactions.users_user_id = users.user_id and  deposit_id = :id');
      $query->bindParam(':id', $lastID, PDO::PARAM_INT);
      $query->execute();

      // get row count
      $rowCount = $query->rowCount();

      // make sure that the new task was returned
      if($rowCount === 0) {
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage("Failed to retrieve diposit transaction after creation");
        $response->send();
        exit;
      }
      // last deposit insert in the account
      $DepositArray = array();

      while($row = $query->fetch(PDO::FETCH_ASSOC)):
                extract($row);
               $Deposit = array(
               "id" => $deposit_id,
               "amount" => $deposit_amount,
               "current_balance" => $desposit_balance,
               "deposited" => $desposit_person,
               "notes" => $desposit_notes,
               "charge" => $desposit_charge,
               "method" => $deposit_method,
               "status" => $desposit_status,
               "teller" => $user_fullname,
               "transactionID" => $desposit_trans_id,
               "timestamp" => $desposit_timestamp,
               );
               $DepositArray[] = $Deposit;
              endwhile;
              // account info array
              $TransactionArray = array(
                "number" => $AccountNumber,
                "balance" => $newAccountBalance,
                "firstname" => $firstname,
                "lastname" => $lastname,
                "deposit" => $DepositArray
          );
          // send SMS and email
          $newAmount = number_format(($amount),0,'.',',');
          $newAccountsBalance = number_format(($newAccountBalance),0,'.',',');

          //date and time generation
          $postdate = new DateTime();
          // set date for kampala
          $postdate->setTimezone(new DateTimeZone('Africa/Nairobi'));
          //formulate the new date
          $date = $postdate->format('Y-m-d H:i:s');

        $message = "A saving of UGX ".$newAmount." has been made to A/C: ".$AccountNumber." (".$firstname." " .$lastname.") in ".$saccoName.". TxID: ".$desposit_trans_id. ". Date: ".$date. ".\nNew balance: UGX ".$newAccountsBalance;
        if ($sacco_sms_status === 'on') {
          // code...
          insertSMSDB($writeDB, $message, $accountContact, $returned_saccoid);
        }
        // insert sms into the database
        if ( $sacco_email_status === 'on') {
          // code...
          insertEMAILDB($writeDB, $message, $saccoEmail, $returned_saccoid);
        }
        // insert email into the database
      // bundle tasks and rows returned into an array to return in the json data
      $returnData = array();
      $returnData['rows_returned'] = $rowCount;
      $returnData['transaction'] = $TransactionArray;
      //set up response for successful return
      $response = new Response();
      $response->setHttpStatusCode(201);
      $response->setSuccess(true);
      $response->setData($returnData);
      $response->send();
      exit;
    }
    // if error with sql query return a json error
    catch(PDOException $ex) {
      error_log("Database Query Error: ".$ex, 0);
      $response = new Response();
      $response->setHttpStatusCode(500);
      $response->setSuccess(false);
      $response->addMessage("failed to create deposit $ex");
      $response->send();
      exit;
    }
  }
  // if any other request method apart from GET or POST is used then return 405 method not allowed
  else {
    $response = new Response();
    $response->setHttpStatusCode(405);
    $response->setSuccess(false);
    $response->addMessage("Request method not allowed");
    $response->send();
    exit;
  }
}
// return 404 error if endpoint not available
else {
  $response = new Response();
  $response->setHttpStatusCode(404);
  $response->setSuccess(false);
  $response->addMessage("Endpoint not found");
  $response->send();
  exit;
}
