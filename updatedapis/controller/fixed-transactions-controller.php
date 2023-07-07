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
  $query = $writeDB->prepare('select user_id, branches_branch_id, access_token_expiry,
   user_status, saccos_sacco_id, user_fullname, user_login_attempts
   from sessions_users, users where sessions_users.users_user_id = users.user_id and access_token = :accesstoken');
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
      $query = $readDB->prepare('SELECT * from deposit_fixed, members,member_accounts, accounts where
       deposit_fixed.members_member_id = members.member_id
       -- AND member_type="individual"
       AND accounts.accounts_id=member_accounts.member_accounts_account_id
       AND accounts.account_types="fixed"
       AND deposit_fixed_id = :transactionid
       AND deposit_fixed.members_member_id = member_accounts.member_accounts_member_id
       AND member_accounts.member_accounts_id = deposit_fixed.deposit_fixed_account_member_id
       AND members.member_id=member_accounts.member_accounts_member_id
       and deposit_fixed.saccos_sacco_id = :saccoid
       order by deposit_fixed_id DESC');
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
      $transactionArray = array();

      while($row = $query->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $deposit = array(
          "id" => $deposit_fixed_id,
          "account" => $members_account_number,
          "firstname" => $member_fname,
          "lastname" => $member_lname,
          "amount" => $deposit_fixed_amount,
          "deposit_method"=>$deposit_method,
          "expected" => $deposit_fixed_expected,
          "startdate" => $date_fixed,
          "number_of_months" => $number_of_months,
          "accountname" => $account_name,
          "transactionID" => $deposit_fixed_ref,
          "timestamp" => $deposit_fixed_timestamp,
          "teller" => $returned_name,
          "status" => $deposit_fixed_status
        );
       $transactionArray[] = $deposit;
      }

      // bundle rows returned into an array to return in the json data
      $returnData = array();
      $returnData['rows_returned'] = $rowCount;
      $returnData['transaction'] = $transactionArray;

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
      $query = $readDB->prepare('SELECT * from deposit_fixed, members,member_accounts, accounts where
       deposit_fixed.members_member_id = members.member_id
       -- AND member_type="individual"
       AND accounts.accounts_id=member_accounts.member_accounts_account_id
       AND accounts.account_types="fixed"
       AND deposit_fixed.members_member_id = member_accounts.member_accounts_member_id
       AND member_accounts.member_accounts_id = deposit_fixed.deposit_fixed_account_member_id
       AND members.member_id=member_accounts.member_accounts_member_id
       and deposit_fixed.saccos_sacco_id = :saccoid
       order by deposit_fixed_id DESC');
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
         "id" => $deposit_fixed_id,
         "account" => $members_account_number,
         "firstname" => $member_fname,
         "lastname" => $member_lname,
         "amount" => $deposit_fixed_amount,
         "deposit_method"=>$deposit_method,
         "expected" => $deposit_fixed_expected,
         "startdate" => $date_fixed,
         "number_of_months" => $number_of_months,
         "accountname" => $account_name,
         "transactionID" => $deposit_fixed_ref,
         "timestamp" => $deposit_fixed_timestamp,
         "teller" => $returned_name,
         "status" => $deposit_fixed_status
       );
       $transactionArray[] = $deposit;
      }

      // bundle rows returned into an array to return in the json data
      $returnData = array();
      $returnData['rows_returned'] = $rowCount;
      $returnData['fixedtransactions'] = $transactionArray;

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
      $response->addMessage("Failed to get transaction");
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
      if(!isset($jsonData->membersid)
          || !isset($jsonData->member_account)
          || !isset($jsonData->amount)
          || !isset($jsonData->payment_date)
          || !isset($jsonData->period)
          || !isset($jsonData->mop)
          || !isset($jsonData->startdate)
          || !isset($jsonData->pincode)

          || empty($jsonData->membersid)
          || empty($jsonData->member_account)
          || empty($jsonData->amount)
          || empty($jsonData->payment_date)
          || empty($jsonData->period)
          || empty($jsonData->mop)
          || empty($jsonData->startdate)
          || empty($jsonData->pincode)

          || !is_numeric($jsonData->pincode)
          || !is_numeric($jsonData->period)
          || !is_numeric($jsonData->membersid)
          ||!is_numeric($jsonData->amount)
        ) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        (!isset($jsonData->membersid) ? $response->addMessage("members id field is mandatory and must be provided") : false);
        (!isset($jsonData->member_account) ? $response->addMessage("member account field is mandatory and must be provided") : false);
        (!isset($jsonData->amount) ? $response->addMessage("amount field is mandatory and must be provided") : false);
        (!isset($jsonData->payment_date) ? $response->addMessage("payment date field is mandatory and must be provided") : false);
        (!isset($jsonData->period) ? $response->addMessage("period to fix field is mandatory and must be provided") : false);
        (!isset($jsonData->mop) ? $response->addMessage("payment method field is mandatory and must be provided") : false);
        (!isset($jsonData->startdate) ? $response->addMessage("star date field is mandatory and must be provided") : false);
        (!isset($jsonData->pincode) ? $response->addMessage("pincode field is mandatory and must be provided") : false);

        (empty($jsonData->membersid) ? $response->addMessage("members id field must not be empty") : false);
        (empty($jsonData->member_account) ? $response->addMessage("member account field must not be empty") : false);
        (empty($jsonData->amount) ? $response->addMessage("amount field must not be empty") : false);
        (empty($jsonData->payment_date) ? $response->addMessage("payment date field must not be empty") : false);
        (empty($jsonData->period) ? $response->addMessage("period to fix field must not be empty") : false);
        (empty($jsonData->mop) ? $response->addMessage("payment method field must not be empty") : false);
        (empty($jsonData->startdate) ? $response->addMessage("star date field must not be empty") : false);
        (empty($jsonData->pincode) ? $response->addMessage("pincode field must not be empty") : false);

        (!is_numeric($jsonData->pincode) ? $response->addMessage("invalid pincode type") : false);
        (!is_numeric($jsonData->period) ? $response->addMessage("invalid period type") : false);
        (!is_numeric($jsonData->membersid) ? $response->addMessage("invalid member id") : false);
        (!is_numeric($jsonData->amount) ? $response->addMessage("invalid amount type") : false);
        $response->send();
        exit;
      }

      $account = $jsonData->membersid;
      // $memberaccount = $jsonData->member_account;
      $amount = (int)$jsonData->amount;
      $period = $jsonData->period;
      $startdate = $jsonData->payment_date;
      $member_account= $jsonData->member_account;
      $pincode = (int) $jsonData->pincode;
      $paymentmethod = $jsonData->mop;
      $notes=$jsonData->notes;
      $bank = isset($jsonData->sacco_group) ? $jsonData->sacco_group : '';

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
      $query = $readDB->prepare('SELECT * from members, saccos,member_accounts
      WHERE members.saccos_sacco_id = saccos.sacco_id
      AND member_accounts_id =:member_account
      AND members.member_id=member_accounts.member_accounts_member_id
      AND member_id = :account');
      $query->bindParam(':account', $account, PDO::PARAM_INT);
      $query->bindParam(':member_account', $member_account, PDO::PARAM_INT);
      $query->execute();
      $rowCount= $query->rowCount();

      if ($rowCount === 0) {
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("no member accounts found");
        $response->send();
        exit;
      }

      $row = $query->fetch(PDO::FETCH_ASSOC);

      $account_status = $row['account_status'];
      $firstname = $row['member_fname'];
      $lastname = $row['member_lname'];
      $accountContact = $row['member_contact'];
      $accountTypeID = $row['member_accounts_account_id'];
      $memberID = $row['member_id'];
      $AccountBalance = (int) $row['total_deposit'];
      $AccountNumber = $row['members_account_number'];
      $saccoName = $row['sacco_short_name'];
      $saccoEmail = $row['sacco_email'];
      $sacco_sms_status = $row['sacco_sms_status'];
      $sacco_email_status = $row['sacco_email_status'];

      if ($account_status !== 'active') {
        $response = new Response();
        $response->setHttpStatusCode(403);
        $response->setSuccess(false);
        $response->addMessage("account has been blocked");
        $response->send();
        exit;
      }
      $queryAccount = $writeDB->prepare('SELECT * from accounts
        where account_types ="fixed" and  accounts_id = :accountype');
      $queryAccount->bindParam(':accountype', $accountTypeID, PDO::PARAM_INT);
      $queryAccount->execute();

      // get row count
      $rowAccountCount = $queryAccount->rowCount();

      // make sure that the new task was returned
      if($rowAccountCount === 0) {
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage("There are no fixed accounts found");
        $response->send();
        exit;
      }
      $rowAccount = $queryAccount->fetch(PDO::FETCH_ASSOC);
      $accountsid = $rowAccount['accounts_id'];
      $openingbalance = $rowAccount['opening_balance'];

      // select account properties
      $query = $readDB->prepare('SELECT fixed_account_id,interest_rate_per_annum,interest_calculation_mode,
      interest_earned_interval from fixed_deposit_account_settings
       where  fixed_deposit_account_saccoid = :saccoid and fixed_account_id = :account');
       $query->bindParam(':account', $accountTypeID, PDO::PARAM_INT);
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $query->execute();
      $rowCount = $query->rowCount();

      if ($rowCount === 0) {
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("no settings found for this account");
        $response->send();
        exit;
      }
      $fixedsettings = $query->fetch(PDO::FETCH_ASSOC);
      $interestperannum= $fixedsettings['interest_rate_per_annum'];
      $interestcalmode= $fixedsettings['interest_calculation_mode'];
      $interestinterval= $fixedsettings['interest_earned_interval'];
      $p=$amount;
      $r=$interestperannum;
      $ti=$period;   //yea$rly
      $t=12;
      $si=0;
      $expectedAmount=0;
      //convert months into years
      $years= $period/$t;
      $finalrate= $r/100;

      if ($interestcalmode==='simple') {
        //interest
        $i= $p*$finalrate*$years;
        // $t=$t/$ti;
        // $si = ($p*$r*$t)/100;
        //simple interest
        $expectedAmount=  $p +$i;
      }
      else {
        //compound interest
       $expectedAmount=round($p * pow(1 + $finalrate/($t),$t*$years),2);
      }

      $transactionID = getGUIDnoHash();
      // $start = strtotime($startdate);
      // $end = strtotime($enddate);
      // $days = ceil(abs($end - $start) / 86400);
      // $expectedNeutralAmount = $amount + ($amount*(($percentage/100)/30)*$days);
      // $expectedAmount = floor($expectedNeutralAmount/100) * 100;
      try {
      $writeDB->beginTransaction();
      $query = $writeDB->prepare('insert into deposit_fixed
      (deposit_fixed_amount,number_of_months,date_fixed,deposit_method,deposit_fixed_ref,notes,bankaccount,
      members_member_id,branches_branch_id,saccos_sacco_id,users_user_id,deposit_fixed_expected,deposit_fixed_account_member_id )
      values(:amount, :nomonths, :startdate,:mop, :transID,:notes,:bank, :member,:branch, :saccoid, :userid, :expected,:member_account)');
      $query->bindParam(':amount', $amount, PDO::PARAM_INT);
      $query->bindParam(':nomonths', $period, PDO::PARAM_STR);
      $query->bindParam(':startdate', $startdate, PDO::PARAM_STR);
      $query->bindParam(':mop', $paymentmethod, PDO::PARAM_STR);
      $query->bindParam(':transID', $transactionID, PDO::PARAM_STR);
      $query->bindParam(':notes', $notes, PDO::PARAM_STR);
      $query->bindParam(':bank', $bank, PDO::PARAM_STR);
      $query->bindParam(':member', $memberID, PDO::PARAM_INT);
      $query->bindParam(':member_account', $member_account, PDO::PARAM_INT);
      $query->bindParam(':branch', $returned_branch_id, PDO::PARAM_INT);
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $query->bindParam(':userid', $returned_id, PDO::PARAM_INT);
      $query->bindParam(':expected', $expectedAmount, PDO::PARAM_INT);
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
      // make the new account balance
      $newAccountBalance = $AccountBalance + $expectedAmount;
      // update the member account balance
      $updateBalance = $writeDB->prepare('UPDATE member_accounts set total_deposit = :amount
         WHERE member_accounts_account_id =:accountype AND member_accounts_member_id=:memberid');
      $updateBalance->bindParam(':amount', $newAccountBalance, PDO::PARAM_INT);
      $updateBalance->bindParam(':accountype', $accountTypeID, PDO::PARAM_INT);
      $updateBalance->bindParam(':memberid', $account, PDO::PARAM_INT);
      $updateBalance->execute();
      $rowUpdateBalanceCount = $updateBalance->rowCount();

      if($rowUpdateBalanceCount === 0){
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("there was an issue updating the account balance");
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
      $query = $writeDB->prepare('SELECT * from deposit_fixed,users
        where deposit_fixed.users_user_id = users.user_id and  deposit_fixed_id = :id');
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
        $response->addMessage("Failed to retrieve member after creation");
        $response->send();
        exit;
      }
      // last deposit insert in the account
      $DepositArray = array();

      while($row = $query->fetch(PDO::FETCH_ASSOC)):
                extract($row);
               $Deposit = array(
                 "id" => $deposit_fixed_id,
                 "account" => $AccountNumber,
                 "firstname" => $firstname,
                 "lastname" => $lastname,
                 "amount" => $deposit_fixed_amount,
                 "startdate" => $date_fixed,
                 "number_of_months" => $number_of_months,
                 "deposit_method" => $deposit_method,
                 "transactionID" => $deposit_fixed_ref,
                 "timestamp" => $deposit_fixed_timestamp
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

        $message = "A saving of UGX ".$newAmount." has been made to A/C: ".$AccountNumber." (".$firstname." " .$lastname.") (Fixed Deposit) in ".$saccoName.". TxID: ".$deposit_fixed_ref. ". Date: ".$deposit_fixed_timestamp. ".\n Expected returns: UGX ".number_format(($expectedAmount),0,'.',',');
        // insert sms into the database
        insertSMSDB($writeDB, $message, $accountContact, $returned_saccoid);
        // insert email into the database
        insertEMAILDB($writeDB, $message, $saccoEmail, $returned_saccoid);
      // bundle tasks and rows returned into an array to return in the json data
      $returnData = array();
      $returnData['rows_returned'] = $rowCount;
      $returnData['fixedtransactions'] = $TransactionArray;
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
      $response->addMessage("failed to create deposit");
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
