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
if (array_key_exists("loanappid",$_GET)) {
  // get task id from query string
  $loanappid = $_GET['loanappid'];
  //check to see if task id in query string is not empty and is number, if not return json error
  if($loanappid == '' || !is_numeric($loanappid)) {
    $response = new Response();
    $response->setHttpStatusCode(400);
    $response->setSuccess(false);
    $response->addMessage("loan application id cannot be blank or must be numeric");
    $response->send();
    exit;
  }
  // if request is a GET, e.g. get transaction
  if($_SERVER['REQUEST_METHOD'] === 'GET') {
    // attempt to query the database
    try {
      $query = $readDB->prepare('SELECT * from loan_applications,loan_product_settings, members, users
         WHERE loan_applications.members_member_id = members.member_id
         AND loan_applications.loan_product_product_id=loan_product_settings.sloan_product_id
         AND loan_applications.users_user_id = users.user_id
         AND loan_app_id = :loanid
         AND loanapplicationtype ="group"
         AND loan_app_status ="approved"
         AND loan_applications.saccos_sacco_id = :saccoid');
      $query->bindParam(':loanid', $loanappid, PDO::PARAM_INT);
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $query->execute();

      // get row count
      $rowCount = $query->rowCount();

      if($rowCount === 0) {
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("this loan is not approved loan for disbursement");
        $response->send();
        exit;
      }
      // create array to store returned task
      $transactionArray = array();

     $row = $query->fetch(PDO::FETCH_ASSOC);
        extract($row);
        $loan_app = array(
          "id" => $loan_app_id,
          "account" => $members_account_number,
          "firstname" => $member_fname,
          "lastname" => $member_lname,
          "loanproduct" => $name_of_loan_product,
          "loanamountoffered" => number_format($offered_amount,2),
          "applicationnumber" => $loan_app_number,
          "status" => $loan_app_status,
          "interest_rate"=>$interest_rate,
          "loanratetype"=>$loan_rate_type,
          "loan_processing_fees"=>$loan_processing_fees,
          "loanapplicationdate" => $loan_app_timestamp
        );

       $scheduleArray = array();

       //
       // $loan_app['totalloanamt']= number_format($totalloanamt);
       // $loan_app['totalinterest']= number_format($totalint);
       // $loan_app['loanpaymentschedule']= $scheduleArray;
       $transactionArray[] = $loan_app;

       // bundle rows returned into an array to return in the json data
       $returnData = array();
       $returnData['rows_returned'] = $rowCount;
       $returnData['loanapplication'] = $transactionArray;

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
}
// handle getting all tasks or creating a new one
elseif(empty($_GET)) {

  // if request is a GET e.g. get members
  if($_SERVER['REQUEST_METHOD'] === 'GET') {

    // attempt to query the database
    try {
      // ADD AUTH TO QUERY
      $query = $readDB->prepare('SELECT * FROM  members
        where members.member_id IN (SELECT loan_applications.members_member_id FROM loan_applications)
        AND members.saccos_sacco_id = :saccoid
        AND member_type="group"');
      // $query->bindParam(':userid', $returned_id, PDO::PARAM_INT);
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $query->execute();
      // get row count
      $rowCount = $query->rowCount();
      // create array to store returned task
      $membersArray = array();
      while($row = $query->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $members = array(
          "id" => $member_id,
          "account" => $members_account_number,
          "firstname" => $member_fname,
          "lastname" => $member_lname,
        );
        $loanquery = $readDB->prepare('SELECT * from loan_applications,loan_product_settings, loans_disbursed
          where loan_applications.members_member_id = :memberid
           AND loan_applications.loan_product_product_id=loan_product_settings.sloan_product_id
           AND loans_disbursed.loan_applications_loan_app_id =loan_applications.loan_app_id
           AND loan_applications.users_user_id = :userid
            AND loanapplicationtype ="group"
            and loan_applications.saccos_sacco_id = :saccoid order by loan_app_id DESC');
        $loanquery->bindParam(':memberid', $member_id, PDO::PARAM_INT);
        $loanquery->bindParam(':userid', $returned_id, PDO::PARAM_INT);
        $loanquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
        $loanquery->execute();

        // create array to store returned task
        $transactionArray = array();
        while($row = $loanquery->fetch(PDO::FETCH_ASSOC)) {
          extract($row);
          $loan_app = array(
            "id" => $loan_app_id,
            "loanid" => $loan_app_number,
            "loanproduct" => $name_of_loan_product,
            "amountappliedfor" => $offered_amount,
            "amountoffered" => $offered_amount,
            "amornitization"=>$amornitization_interval,
            "graceperiod"=>$grace_period." days",
            "loantenure"=>$tenure_period." months",
            "datedisbursed"=>$loan_disbursed_date,
            "totalloanpay" => $loan_balance,
            "totalinterest" => $total_interest,
            "loantype" => $loan_type,
            "status" => $loan_app_status,
            "timestamp" => $loan_app_date,
            "date"=> $loan_app_date
          );
         $transactionArray[] = $loan_app;
        }
       $members['loanapplications'] = $transactionArray;
       $membersArray[] = $members;
      }

      // bundle rows returned into an array to return in the json data
      $returnData = array();
      $returnData['rows_returned'] = $rowCount;
      $returnData['loansdisbursed'] = $membersArray;

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
      if(!isset($jsonData->loanappid) || !is_numeric($jsonData->loanappid)||empty($jsonData->loanappid)
      || !isset($jsonData->amount)||!is_numeric($jsonData->amount)|| empty($jsonData->amount)
      || !isset($jsonData->accountfrom)||!is_numeric($jsonData->accountfrom)|| empty($jsonData->accountfrom)
      || !isset($jsonData->datedisbursed) || empty($jsonData->datedisbursed)
      || !isset($jsonData->mop) || empty($jsonData->mop)
      || !isset($jsonData->memberaccountid) || empty($jsonData->memberaccountid)||!is_numeric($jsonData->memberaccountid)
       ) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        (!isset($jsonData->loanappid) ? $response->addMessage("loan application field is mandatory and must be provided") : false);
        (empty($jsonData->loanappid) ? $response->addMessage("loan application field must not be empty") : false);
        (!is_numeric($jsonData->loanappid) ? $response->addMessage("loan application id field must be numeric") : false);
        (!isset($jsonData->amount) ? $response->addMessage("disbursed amount field is mandatory and must be provided") : false);
        (empty($jsonData->amount) ? $response->addMessage("disbursed amount field must not be empty") : false);
        (!is_numeric($jsonData->amount) ? $response->addMessage("disbursed amount field value must be numeric") : false);
        (!isset($jsonData->accountfrom) ? $response->addMessage("account to disburse from field is mandatory and must be provided") : false);
        (empty($jsonData->accountfrom) ? $response->addMessage("account to disburse field must not be empty") : false);
        (!is_numeric($jsonData->accountfrom) ? $response->addMessage("account to disburse field value must be numeric") : false);
        (!isset($jsonData->datedisbursed) ? $response->addMessage("date disbursed field is mandatory and must be provided") : false);
        (empty($jsonData->datedisbursed) ? $response->addMessage("date disbursed field must not be empty") : false);
        (!isset($jsonData->mop) ? $response->addMessage("mode of payment period field is mandatory and must be provided") : false);
        (empty($jsonData->mop) ? $response->addMessage("mode of payment field must not be empty") : false);
        (!isset($jsonData->memberaccountid) ? $response->addMessage("member accountid period field is mandatory and must be provided") : false);
        (empty($jsonData->memberaccountid) ? $response->addMessage("member account field must not be empty") : false);
        (!is_numeric($jsonData->memberaccountid) ? $response->addMessage("member account field value must be numeric") : false);
        $response->send();
        exit;
      }

      $_applicationid = (int)$jsonData->loanappid;
      $_amount = (int)$jsonData->amount;
      $datedisbursed= $jsonData->datedisbursed;
      $_accountdisbursefrom = (int)$jsonData->accountfrom;
      $_memberaccountid =$jsonData->memberaccountid;
      $_transactionMethod =$jsonData->mop;
      $bank = isset($jsonData->bank) ? $jsonData->bank : '';
      $_notes = $jsonData->notes;
      $transactionID = getGUIDnoHash();
      $transID = getGUIDnoHash();
      $disbursedamt = (int)$jsonData->amount;

      $transactionStatus = 'successful';
      // $transactionMethod = 'cash';
      $despositCharge = (int) 0;

        $loandisbursedcheckQuery = $readDB->prepare('SELECT * FROM loans_disbursed
         where loan_applications_loan_app_id=:id');
        // $query->bindParam(':amt', $amount, PDO::PARAM_INT);
        $loandisbursedcheckQuery->bindParam(':id', $_applicationid, PDO::PARAM_INT);
        $loandisbursedcheckQuery->execute();
        $rowCountw = $loandisbursedcheckQuery->rowCount();

        if ($rowCountw !==0) {
          $response = new Response();
          $response->setHttpStatusCode(404);
          $response->setSuccess(false);
          $response->addMessage("Loan already disbursed");
          $response->send();
          exit;
      }
      $checkappQuery = $readDB->prepare('SELECT * from loan_applications,loan_product_settings,
         members, users
         WHERE loan_applications.members_member_id = members.member_id
         AND loan_applications.loan_product_product_id=loan_product_settings.sloan_product_id
         AND loan_applications.users_user_id = users.user_id
         AND loan_app_id = :id
         AND loanapplicationtype ="group"
         AND loan_applications.saccos_sacco_id = :saccoid');
      // $query->bindParam(':amt', $amount, PDO::PARAM_INT);
      $checkappQuery->bindParam(':id', $_applicationid, PDO::PARAM_INT);
      $checkappQuery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $checkappQuery->execute();
      $approw = $checkappQuery->fetch(PDO::FETCH_ASSOC);
      $loan_status = $approw['loan_app_status'];
      $amortization= $approw['amornitization_interval'];
      $loanratetype=$approw['loan_rate_type'];
      $rate = $approw['interest_rate'];
      $graceperiod= $approw['grace_period'];
      $loantenure= $approw['tenure_period'];
      $totalint=0;
      $totalloanamt=0;
      $effectiveDate1 = strtotime("+ ".$graceperiod." days", strtotime($datedisbursed));
      $finaldatedisbursed = strftime ( '%Y-%m-%d' , $effectiveDate1);

      if ($loan_status =='rejected' || $loan_status =='pending'|| $loan_status =='processed'
      || $loan_status=='disbursed') {
        // code...
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        ($loan_status =='rejected' ? $response->addMessage("this loan was rejected") : false);
        ($loan_status =='pending' ? $response->addMessage("this loan is not processed yet") : false);
        ($loan_status =='processed' ? $response->addMessage("this loan is already proccessed") : false);
        ($loan_status =='disbursed' ? $response->addMessage("this loan is already disbursed") : false);
        $response->send();
        exit;
      }
      //check in the newsafe
      $accountquery = $readDB->prepare('SELECT * from accounts   WHERE accounts_id =:account ');
      $accountquery->bindParam(':account', $_accountdisbursefrom, PDO::PARAM_INT);
      $accountquery->execute();
      $rowaccountCount = $accountquery->rowCount();

      if ($rowaccountCount === 0) {
      $response = new Response();
      $response->setHttpStatusCode(404);
      $response->setSuccess(false);
      $response->addMessage("sacco account to transact funds from not found");
      $response->send();
      exit;
      }
        $rowAccount = $accountquery->fetch(PDO::FETCH_ASSOC);
        $accountsid = $rowAccount['accounts_id'];
        $openingbalance = $rowAccount['opening_balance'];
      // echo  $openingbalance;
        if ($_amount > $openingbalance) {
          $response = new Response();
          $response->setHttpStatusCode(400);
          $response->setSuccess(false);
          $response->addMessage("Not enough funds on this sacco account to complete the transaction");
          $response->send();
          exit;
         }
        // select account to receive funds
        $member_toquery = $readDB->prepare('SELECT * from members, saccos,member_accounts,loan_applications
        WHERE members.saccos_sacco_id = saccos.sacco_id
        AND members.member_id =loan_applications.members_member_id
        AND members.member_id = member_accounts.member_accounts_member_id
        AND member_accounts.member_accounts_id =:member_account
        AND loan_applications.loan_app_id=:loanappid');
        $member_toquery->bindParam(':member_account', $_memberaccountid, PDO::PARAM_INT);
        $member_toquery->bindParam(':loanappid', $_applicationid, PDO::PARAM_INT);
        $member_toquery->execute();
        $rowCount = $member_toquery->rowCount();
        if ($rowCount === 0) {
          $response = new Response();
          $response->setHttpStatusCode(404);
          $response->setSuccess(false);
          $response->addMessage("account to deposit loans on not found");
          $response->send();
          exit;
        }
        $memberrow = $member_toquery->fetch(PDO::FETCH_ASSOC);
        $account_status = $memberrow['account_status'];
        $firstname = $memberrow['member_fname'];
        $lastname = $memberrow['member_lname'];
        $accountContact = $memberrow['member_contact'];
        $AccountBalance = (int) $memberrow['total_deposit'];
        $memberID = (int) $memberrow['member_accounts_member_id'];
        $AccountNumber = $memberrow['members_account_number'];
        $saccoName = $memberrow['sacco_short_name'];
        $saccoEmail = $memberrow['sacco_email'];
        $sacco_sms_status = $memberrow['sacco_sms_status'];
        $sacco_email_status = $memberrow['sacco_email_status'];
        // make the new account balance
        $newAccountBalance = $AccountBalance + $_amount;
      try {
      $writeDB->beginTransaction();
      $query = $writeDB->prepare('INSERT into desposit_transactions
       (deposit_amount,
        desposit_timestamp,
        desposit_balance,
        desposit_notes,
        desposit_person,
        desposit_charge, desposit_trans_id, desposit_status,deposit_method,
        members_member_id,saccos_sacco_id,
      users_user_id,branches_branch_id,deposit_account_member_id, deposit_bank_account)
      values(:amount,:dop,:balance, :notes, :person, :charge, :transID, :status,
       :method, :member, :saccoid, :userid,:branch,:member_account,:bank)');
      $query->bindParam(':amount', $_amount, PDO::PARAM_INT);
      $query->bindParam(':dop', $datedisbursed, PDO::PARAM_INT);
      $query->bindParam(':balance', $newAccountBalance, PDO::PARAM_INT);
      $query->bindParam(':notes', $_notes, PDO::PARAM_STR);
      $query->bindParam(':person', $returned_name, PDO::PARAM_STR);
      $query->bindParam(':charge', $despositCharge, PDO::PARAM_INT);
      $query->bindParam(':transID', $transactionID, PDO::PARAM_STR);
      $query->bindParam(':status', $transactionStatus, PDO::PARAM_STR);
      $query->bindParam(':method', $_transactionMethod, PDO::PARAM_STR);
      $query->bindParam(':member', $memberID, PDO::PARAM_INT);
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $query->bindParam(':userid', $returned_id, PDO::PARAM_INT);
      $query->bindParam(':branch', $returned_branch_id, PDO::PARAM_INT);
      $query->bindParam(':member_account', $_memberaccountid, PDO::PARAM_INT);
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
      $updateBalance->bindParam(':member_account', $_memberaccountid, PDO::PARAM_INT);
      $updateBalance->bindParam(':memberid', $memberID, PDO::PARAM_INT);
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
      $newopeningbalance = $openingbalance - $_amount;
      $updateaccount = $writeDB->prepare('UPDATE accounts set opening_balance = :amountbal
         WHERE accounts_id=:id');
      $updateaccount->bindParam(':amountbal', $newopeningbalance, PDO::PARAM_INT);
      $updateaccount->bindParam(':id', $_accountdisbursefrom, PDO::PARAM_INT);
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

      $updateloanappquery = $writeDB->prepare('UPDATE loan_applications set offered_amount = :amount
         WHERE loan_app_id=:id');
      $updateloanappquery->bindParam(':amount', $_amount, PDO::PARAM_INT);
      $updateloanappquery->bindParam(':id', $_applicationid, PDO::PARAM_INT);
      $updateloanappquery->execute();
      $rowUpdateLoanAppCount = $updateloanappquery->rowCount();
      if($rowUpdateLoanAppCount === 0){
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("there was an issue updating the loan offered");
        $response->send();
        exit;
      }
      $_open ="open";
      $_statuss ="disbursed";
      $loandisbursedquery = $writeDB->prepare('INSERT into loans_disbursed
       (`loan_applications_loan_app_id`,`loan_disbursed_date`,`modeofpayment`,`account_disburse_to`,
         `account_disburse_from`, `amount_disbursed`, `loan_status`,`loan_balance`,`deposit_bank_account`,
         `disbursed_notes`, `users_user_id`, `saccos_sacco_id`,`branches_branch_id`)
      values(:loanapplication,:dop,:method,:account,:accountfrom,:amount,:status,
      :balance, :bank,:notes, :userid,:saccoid,:branch)');
      $loandisbursedquery->bindParam(':loanapplication', $_applicationid, PDO::PARAM_INT);
      $loandisbursedquery->bindParam(':dop', $datedisbursed, PDO::PARAM_INT);
      $loandisbursedquery->bindParam(':method', $_transactionMethod, PDO::PARAM_STR);
      $loandisbursedquery->bindParam(':account', $_memberaccountid, PDO::PARAM_STR);
      $loandisbursedquery->bindParam(':accountfrom', $_accountdisbursefrom, PDO::PARAM_INT);
      $loandisbursedquery->bindParam(':amount', $_amount, PDO::PARAM_INT);
      $loandisbursedquery->bindParam(':status', $_open, PDO::PARAM_STR);
      $loandisbursedquery->bindParam(':balance', $_amount, PDO::PARAM_INT);
      $loandisbursedquery->bindParam(':bank', $bank, PDO::PARAM_INT);
      $loandisbursedquery->bindParam(':notes', $_notes, PDO::PARAM_STR);
      $loandisbursedquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $loandisbursedquery->bindParam(':userid', $returned_id, PDO::PARAM_INT);
      $loandisbursedquery->bindParam(':branch', $returned_branch_id, PDO::PARAM_INT);
      $loandisbursedquery->execute();
      $disbursedID= $writeDB->lastInsertId();
      // get row count
      $rowloansDisbursedCount = $loandisbursedquery->rowCount();
      if($rowloansDisbursedCount === 0){
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("there was an issue disbursing loans");
        $response->send();
        exit;
      }
        // loan applications approve
        $updatequery = $writeDB->prepare('UPDATE loan_applications set loan_app_status = :status
          where loan_app_id = :loanid');
        $updatequery->bindParam(':status', $_statuss, PDO::PARAM_STR);
        $updatequery->bindParam(':loanid', $_applicationid, PDO::PARAM_INT);
        $updatequery->execute();
        $rowsCount = $updatequery->rowCount();
        if ($rowsCount === 0 ) {
          // return the data to the schedule
          $response = new Response();
          $response->setHttpStatusCode(500);
          $response->setSuccess(false);
          $response->addMessage('Error updating loan application status');
          $response->send();
          exit;
        }


        $transaction_type='loan withdraw';
        $accounttransactquery = $writeDB->prepare('INSERT into  account_transactions
         ( `account_transact_id`, `transact_amount`, `transaction_date`,
           `transaction_type`, `transact_user_id`, `transact_b ranch_id`, `transact_sacco_id` )
        values(:accountfrom,:amount,:dop,:loanwithdraw, :userid,:branch,:saccoid)');
        $accounttransactquery->bindParam(':accountfrom', $_accountdisbursefrom, PDO::PARAM_INT);
        $accounttransactquery->bindParam(':amount', $_amount, PDO::PARAM_INT);
        $accounttransactquery->bindParam(':dop', $datedisbursed, PDO::PARAM_INT);
        $accounttransactquery->bindParam(':loanwithdraw', $transaction_type, PDO::PARAM_STR);
        $accounttransactquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
        $accounttransactquery->bindParam(':userid', $returned_id, PDO::PARAM_INT);
        $accounttransactquery->bindParam(':branch', $returned_branch_id, PDO::PARAM_INT);
        $accounttransactquery->execute();
        $rowsaccountsCount = $accounttransactquery->rowCount();

        if ($rowsaccountsCount === 0 ) {
          // return the data to the schedule
          $response = new Response();
          $response->setHttpStatusCode(500);
          $response->setSuccess(false);
          $response->addMessage('eror saving sacco account transaction');
          $response->send();
          exit;
        }

      // loan applications approve
      $updateloandisbursequery = $writeDB->prepare('UPDATE loans_disbursed set loan_balance = :amount,
        total_interest=:interest
        where loan_applications_loan_app_id  = :loanid');
        $updateloandisbursequery->bindParam(':interest', $totalint, PDO::PARAM_STR);
      $updateloandisbursequery->bindParam(':amount', $totalloanamt, PDO::PARAM_STR);
      $updateloandisbursequery->bindParam(':loanid', $_applicationid, PDO::PARAM_INT);
      $updateloandisbursequery->execute();
      $rowsiCount = $updateloandisbursequery->rowCount();
      if ($rowsiCount === 0 ) {
        // return the data to the schedule
        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage('Error updating disbursed total interest and balance');
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
                "loandeposit" => $DepositArray
          );
          // send SMS and email
          $newAmount = number_format(($disbursedamt),0,'.',',');
          $newAccountsBalance = number_format(($newAccountBalance),0,'.',',');

          //date and time generation
          $postdate = new DateTime();
          // set date for kampala
          $postdate->setTimezone(new DateTimeZone('Africa/Nairobi'));
          //formulate the new date
          $date = $postdate->format('Y-m-d H:i:s');

        $message = "Hello, your group loan UGX ".$newAmount." has been disbursed to A/C: ".$AccountNumber." (".$firstname." " .$lastname.") in ".$saccoName.". TxID: ".$desposit_trans_id. ". Date: ".$date. ".\nNew balance: UGX ".$newAccountsBalance;
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
      $response->addMessage("failed to disburse loan $ex");
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
