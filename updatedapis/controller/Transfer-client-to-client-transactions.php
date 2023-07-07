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
if (array_key_exists("clienttransid",$_GET)) {
  // get task id from query string
  $clienttransid = $_GET['clienttransid'];
  //check to see if task id in query string is not empty and is number, if not return json error
  if($clienttransid == '' || !is_numeric($clienttransid)) {
    $response = new Response();
    $response->setHttpStatusCode(400);
    $response->setSuccess(false);
    $response->addMessage("client tranfer id cannot be blank or must be numeric");
    $response->send();
    exit;
  }

  // if request is a GET, e.g. get transaction
  if($_SERVER['REQUEST_METHOD'] === 'GET') {
    $transfer_type='client to client';
    // attempt to query the database
    try {
      // create db query
      // ADD AUTH TO QUERY
      $transquery = $readDB->prepare('SELECT * from withdrawal_transactions, members,member_accounts,accounts,client_to_client_transfer
        where withdrawal_transactions.members_member_id = members.member_id
        and withdrawal_transactions.users_user_id = :userid
        AND withdrawal_transactions.withdraw_member_account_id=member_accounts.member_accounts_id
        AND member_accounts.member_accounts_account_id=accounts.accounts_id
        AND client_to_client_transfer.transfer_from_member_account =withdrawal_transactions.withdraw_member_account_id
        AND client_to_client_transfer.transfer_from_member_account =member_accounts.member_accounts_id
        AND client_to_client_transfer.client_transfer_withdraw_id =withdrawal_transactions.withdraw_id
        and withdrawal_transactions.saccos_sacco_id = :saccoid
        AND client_to_client_transfer.client_to_client_transfer_id=:transferid
        AND client_to_client_transfer.transfer_type=:transfer_type
        order by withdraw_id DESC');
        $transquery->bindParam(':transfer_type', $transfer_type, PDO::PARAM_INT);
        $transquery->bindParam(':transferid', $clienttransid, PDO::PARAM_INT);
        $transquery->bindParam(':userid', $returned_id, PDO::PARAM_INT);
        $transquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
        $transquery->execute();

        // get row count
        $rowCount = $transquery->rowCount();
        // create array to store returned task
        $ClientTransferFromArray = array();

        while($row = $transquery->fetch(PDO::FETCH_ASSOC)):
                  extract($row);
                 $Withdraw = array(
                   "id" => $withdraw_id,
                   "account" => $members_account_number,
                   "accountname" => $account_name,
                   "balance" => $total_deposit,
                   "firstname" => $member_fname,
                   "lastname" => $member_lname,
                   "amount" => $withdraw_amount,
                   "withdraw" => $withdraw_person,
                   "notes" => $withdraw_notes,
                   "charge" => $withdraw_charge,
                   "method" => $withdraw_method,
                   "status" => $withdraw_status,
                   "teller" => $returned_name,
                   "transactionID" => $withdraw_trans_id,
                   "timestamp" => $withdraw_timestamp,
                 );
                 $ClientTransferFromArray[] = $Withdraw;
                endwhile;
                //client transfer to

                $depositquery = $writeDB->prepare('SELECT * FROM desposit_transactions,
                   members,member_accounts, accounts, client_to_client_transfer, users
                   WHERE desposit_transactions.users_user_id = users.user_id
                AND client_to_client_transfer.deposit_trans_id  =desposit_transactions.deposit_id
                AND accounts.accounts_id=member_accounts.member_accounts_account_id
                AND desposit_transactions.members_member_id = member_accounts.member_accounts_member_id
                AND member_accounts.member_accounts_id = desposit_transactions.deposit_account_member_id
                AND members.member_id=member_accounts.member_accounts_member_id
                AND client_to_client_transfer.client_to_client_transfer_id=:transferid
                AND client_to_client_transfer.transfer_type=:transfer_type
                AND desposit_transactions.saccos_sacco_id = :saccoid  ORDER BY desposit_timestamp DESC');
                $depositquery->bindParam(':transfer_type', $transfer_type, PDO::PARAM_INT);
                $depositquery->bindParam(':transferid', $clienttransid, PDO::PARAM_INT);
                $depositquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
                $depositquery->execute();

                // get row count
                $depositRowCount = $depositquery->rowCount();

                // make sure that the new task was returned
                if($depositRowCount === 0) {
                  // set up response for unsuccessful return
                  $response = new Response();
                  $response->setHttpStatusCode(500);
                  $response->setSuccess(false);
                  $response->addMessage("Failed to retrieve deposit tranfers");
                  $response->send();
                  exit;
                }
                $ClientTransferToArray=array();
                while($row = $depositquery->fetch(PDO::FETCH_ASSOC)) {
                  extract($row);
                 $deposit = array(
                   "id" => $deposit_id,
                   "account" => $members_account_number,
                   "accountname" => $account_name,
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
                 $ClientTransferToArray[] = $deposit;
                }
      $transactionArray = array();
                // account info array
      $TransactionArray = array(
                  // "number" => $AccountNumber,
                  // "balance" => $newAccountBalance,
                  // "firstname" => $firstname,
                  // "lastname" => $lastname,
      "clientstransferfrom" => $ClientTransferFromArray,
      "clientstransferto" => $ClientTransferToArray
      );
      // bundle rows returned into an array to return in the json data
      $returnData = array();
      // $returnData['rows_returned'] = $rowCount;
      $returnData['transactions'] = $TransactionArray;

      // set up response for successful return
      $response = new Response();
      $response->setHttpStatusCode(200);
      $response->setSuccess(true);
      $response->toCache(false);
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
    $transfer_type='client to client';

    // attempt to query the database
    try {
      // ADD AUTH TO QUERY
      $transquery = $readDB->prepare('SELECT * from withdrawal_transactions, members,member_accounts,accounts,client_to_client_transfer
        where withdrawal_transactions.members_member_id = members.member_id
        and withdrawal_transactions.users_user_id = :userid
        AND withdrawal_transactions.withdraw_member_account_id=member_accounts.member_accounts_id
        AND member_accounts.member_accounts_account_id=accounts.accounts_id
        AND client_to_client_transfer.transfer_from_member_account =withdrawal_transactions.withdraw_member_account_id
        AND client_to_client_transfer.transfer_from_member_account =member_accounts.member_accounts_id
        AND client_to_client_transfer.client_transfer_withdraw_id =withdrawal_transactions.withdraw_id
        and withdrawal_transactions.saccos_sacco_id = :saccoid
        AND client_to_client_transfer.transfer_type=:transfer_type
        order by withdraw_id DESC');
        $transquery->bindParam(':transfer_type', $transfer_type, PDO::PARAM_INT);
        $transquery->bindParam(':userid', $returned_id, PDO::PARAM_INT);
        $transquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
        $transquery->execute();

        // get row count
        $rowCount = $transquery->rowCount();
        // create array to store returned task
        $ClientTransferFromArray = array();

        while($row = $transquery->fetch(PDO::FETCH_ASSOC)):
                  extract($row);
                 $Withdraw = array(
                   "id" => $withdraw_id,
                   "account" => $members_account_number,
                   "accountname" => $account_name,
                   "balance" => $total_deposit,
                   "firstname" => $member_fname,
                   "lastname" => $member_lname,
                   "amount" => $withdraw_amount,
                   "withdraw" => $withdraw_person,
                   "notes" => $withdraw_notes,
                   "charge" => $withdraw_charge,
                   "method" => $withdraw_method,
                   "status" => $withdraw_status,
                   "teller" => $returned_name,
                   "transactionID" => $withdraw_trans_id,
                   "timestamp" => $withdraw_timestamp,
                 );
                 $ClientTransferFromArray[] = $Withdraw;
                endwhile;
                //client transfer to

                $depositquery = $writeDB->prepare('SELECT * FROM desposit_transactions,
                   members,member_accounts, accounts, client_to_client_transfer, users
                   WHERE desposit_transactions.users_user_id = users.user_id
                AND client_to_client_transfer.deposit_trans_id  =desposit_transactions.deposit_id
                AND accounts.accounts_id=member_accounts.member_accounts_account_id
                AND desposit_transactions.members_member_id = member_accounts.member_accounts_member_id
                AND member_accounts.member_accounts_id = desposit_transactions.deposit_account_member_id
                AND members.member_id=member_accounts.member_accounts_member_id
                AND client_to_client_transfer.transfer_type=:transfer_type
                AND desposit_transactions.saccos_sacco_id = :saccoid  ORDER BY desposit_timestamp DESC');

                $depositquery->bindParam(':transfer_type', $transfer_type, PDO::PARAM_INT);
                $depositquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
                $depositquery->execute();

                // get row count
                $depositRowCount = $depositquery->rowCount();

                // make sure that the new task was returned
                if($depositRowCount === 0) {
                  // set up response for unsuccessful return
                  $response = new Response();
                  $response->setHttpStatusCode(500);
                  $response->setSuccess(false);
                  $response->addMessage("Failed to retrieve deposit tranfers");
                  $response->send();
                  exit;
                }
                $ClientTransferToArray=array();
                while($row = $depositquery->fetch(PDO::FETCH_ASSOC)) {
                  extract($row);
                 $deposit = array(
                   "id" => $deposit_id,
                   "account" => $members_account_number,
                   "accountname" => $account_name,
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
                 $ClientTransferToArray[] = $deposit;
                }
      $transactionArray = array();
                // account info array
      $TransactionArray = array(
                  // "number" => $AccountNumber,
                  // "balance" => $newAccountBalance,
                  // "firstname" => $firstname,
                  // "lastname" => $lastname,
      "clientstransferfrom" => $ClientTransferFromArray,
      "clientstransferto" => $ClientTransferToArray
      );
      // bundle rows returned into an array to return in the json data
      $returnData = array();
      // $returnData['rows_returned'] = $rowCount;
      $returnData['transactions'] = $TransactionArray;

      // set up response for successful return
      $response = new Response();
      $response->setHttpStatusCode(200);
      $response->setSuccess(true);
      $response->toCache(false);
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
      if(!isset($jsonData->account_from)
      ||!isset($jsonData->member_account_from)
      ||!isset($jsonData->account)
      || !isset($jsonData->member_account)
      || !isset($jsonData->amount)
      || !isset($jsonData->mop)
      || !isset($jsonData->dow)
      // || !isset($jsonData->withdraw)
      || !isset($jsonData->pincode)

      || empty($jsonData->account_from)
      || empty($jsonData->member_account_from)
      || empty($jsonData->account)
      || empty($jsonData->amount)
      || empty($jsonData->dow)
      || empty($jsonData->mop)
      || empty($jsonData->pincode)
      ||!is_numeric($jsonData->amount)
      ||!is_numeric($jsonData->account)
      ||!is_numeric($jsonData->member_account)
      ||!is_numeric($jsonData->account_from)
      ||!is_numeric($jsonData->member_account_from)
    ) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        (!isset($jsonData->account_from) ? $response->addMessage("account transfer from field is mandatory and must be provided") : false);
        (!isset($jsonData->member_account_from) ? $response->addMessage("member account from field is mandatory and must be provided") : false);
        (!isset($jsonData->account) ? $response->addMessage("account field is mandatory and must be provided") : false);
        (!isset($jsonData->member_account) ? $response->addMessage("member account field is mandatory and must be provided") : false);
        (!isset($jsonData->amount) ? $response->addMessage("amount field is mandatory and must be provided") : false);
        (!isset($jsonData->dow) ? $response->addMessage("date of withdraw field is mandatory and must be provided") : false);
        (!isset($jsonData->mop) ? $response->addMessage("mod of payment field is mandatory and must be provided") : false);
        (!isset($jsonData->pincode) ? $response->addMessage("pincode field is mandatory and must be provided") : false);
        (empty($jsonData->account) ? $response->addMessage("account field must not be empty") : false);
        (empty($jsonData->member_account) ? $response->addMessage("member account field must not be empty") : false);
        (empty($jsonData->amount) ? $response->addMessage("amount field must not be empty") : false);
        (empty($jsonData->dow) ? $response->addMessage("date of withdraw field must not be empty") : false);
        (empty($jsonData->mop) ? $response->addMessage("mode of payment field must not be empty") : false);
        (empty($jsonData->pincode) ? $response->addMessage("pincode field must not be empty") : false);
        (!is_numeric($jsonData->account) ? $response->addMessage("Invalid member type") : false);
        (!is_numeric($jsonData->member_account) ? $response->addMessage("Invalid member to account type") : false);
        (!is_numeric($jsonData->amount) ? $response->addMessage("Invalid amount type") : false);
        (!is_numeric($jsonData->account_from) ? $response->addMessage("Invalid account from type") : false);
        (!is_numeric($jsonData->member_account_from) ? $response->addMessage("Invalid member account transfer from type") : false);
        $response->send();
        exit;
      }

      $memberfrom = $jsonData->account_from;
      $memberaccountfrom = $jsonData->member_account_from;
      $account = $jsonData->account;
      $member_account = $jsonData->member_account;
      $amount = (int)$jsonData->amount;
      // $withdraw = $jsonData->withdraw;
      $notes = $jsonData->notes;
      $dow = $jsonData->dow;
      $transactionMethod =$jsonData->mop;
      $pincode =$jsonData->pincode;
      // $bank = $jsonData->bank;
      $transfer_type='client to client';

      if ($memberaccountfrom === $member_account) {
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("you cannot make transfers using same account");
        $response->send();
        exit;
      }

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
      $withdrawchargequery = $readDB->prepare('SELECT * from system_default_settings where default_saccoid = :saccoid');
      $withdrawchargequery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $withdrawchargequery->execute();
      $withdrawrowCount = $withdrawchargequery->rowCount();

      if ($withdrawrowCount === 0) {
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("deafult withdraw charge account not found");
        $response->send();
        exit;
      }
      //get account
      $withdrawchargerow = $withdrawchargequery->fetch(PDO::FETCH_ASSOC);
      // withdraw charge account
      $withdrawchargeacc = $withdrawchargerow['withdraws_account'];
      $withdrawcidquery = $readDB->prepare('SELECT accounts_id, account_name,opening_balance from
        accounts where account_name = :accountname
        AND account_sacco_id =:saccoid');
      $withdrawcidquery->bindParam(':accountname', $withdrawchargeacc, PDO::PARAM_INT);
      $withdrawcidquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $withdrawcidquery->execute();
      $withdrawrowCount = $withdrawcidquery->rowCount();
      if ($withdrawrowCount === 0) {
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("the default set account does not exist in accounts table");
        $response->send();
        exit;
      }
      // get withdraw charge account
      //get account
      $chargerow = $withdrawcidquery->fetch(PDO::FETCH_ASSOC);
      $withdrawaccountid = $chargerow['accounts_id'];
      $wopeningbalance = $chargerow['opening_balance'];

      // select account properties
      // $query = $readDB->prepare('select * from members, saccos where members.saccos_sacco_id = saccos.sacco_id and member_id = :account');
      // $query->bindParam(':account', $account, PDO::PARAM_INT);
      // $query->execute();
      // $rowCount = $query->rowCount();
      $query = $readDB->prepare('SELECT * from members, saccos,member_accounts
      WHERE members.saccos_sacco_id = saccos.sacco_id AND member_accounts_id =:member_account
      AND member_id = :account');
      $query->bindParam(':account', $memberfrom, PDO::PARAM_INT);
      $query->bindParam(':member_account', $memberaccountfrom, PDO::PARAM_INT);
      $query->execute();
      $rowCount = $query->rowCount();

      if ($rowCount === 0) {
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("member not found");
        $response->send();
        exit;
      }

      $row = $query->fetch(PDO::FETCH_ASSOC);

      $account_status = $row['member_status'];
      $firstname = $row['member_fname'];
      $lastname = $row['member_lname'];
      $accountContact = $row['member_contact'];
      $accountTypeID = $row['member_accounts_account_id'];
      $memberID = $row['member_id'];
      $AccountBalance =  (int) $row['total_deposit'];
      $AccountNumber = $row['members_account_number'];
      $saccoName = $row['sacco_short_name'];
      $saccoEmail = $row['sacco_email'];
      $saccoWithdrawBalance = $row['sacco_profit_withdraw'];
      $sacco_sms_status = $row['sacco_sms_status'];
      $sacco_email_status = $row['sacco_email_status'];
      $deposit_deposited =$firstname.' '.$lastname;
      if ($account_status !== 'active') {
        $response = new Response();
        $response->setHttpStatusCode(403);
        $response->setSuccess(false);
        $response->addMessage("account has been blocked");
        $response->send();
        exit;
      }
        $query = $readDB->prepare('SELECT accounts_id, account_name from accounts where accounts_id = :id');
        $query->bindParam(':id', $accountTypeID, PDO::PARAM_INT);
        $query->execute();
        $rowCount = $query->rowCount();

        if ($rowCount === 0) {
          $response = new Response();
          $response->setHttpStatusCode(404);
          $response->setSuccess(false);
          $response->addMessage("account not found");
          $response->send();
          exit;
        }
        //get account
        $row = $query->fetch(PDO::FETCH_ASSOC);
        $accountid = $row['accounts_id'];
        $accountname = $row['account_name'];

        $queryWithdraw = $readDB->prepare('SELECT account_id, minimumbalance,withdrawcharge,modeofdeduction FROM
           withdrawsettings where account_id = :id AND  '.$amount.' BETWEEN amountfrom AND amountto');
        // $query->bindParam(':amt', $amount, PDO::PARAM_INT);
        $queryWithdraw->bindParam(':id', $accountTypeID, PDO::PARAM_INT);
        $queryWithdraw->execute();
        $rowCountw = $queryWithdraw->rowCount();

        if ($rowCountw === 0) {
          $response = new Response();
          $response->setHttpStatusCode(404);
          $response->setSuccess(false);
          $response->addMessage("withdraw setting not found");
          $response->send();
          exit;
        }
        $rowWithdraw = $queryWithdraw->fetch(PDO::FETCH_ASSOC);
        $minimalBalance = (int) $rowWithdraw['minimumbalance'];
        $AccountWithdrawCharge = (int) $rowWithdraw['withdrawcharge'];
        $modeofdeduction= $rowWithdraw['modeofdeduction'];
        $AccountWithdrawCharge=0;
        if($modeofdeduction==='percentage'){
        $withdrawPercentage = (int) $rowWithdraw['withdrawcharge'];
        $AccountWithdrawCharge =$withdrawPercentage/100 *$amount;
        }
        else{
        $AccountWithdrawCharge = (int) $rowWithdraw['withdrawcharge'];
        }
        $AccountCheckBalance = $minimalBalance + $AccountWithdrawCharge + $amount;
        $AccountCheckWithdrawAmount =  $AccountWithdrawCharge + $amount;
        if ($AccountBalance < $AccountCheckBalance) {
          $response = new Response();
          $response->setHttpStatusCode(400);
          $response->setSuccess(false);
          $response->addMessage("Not enough funds on client account to make transaction");
          $response->send();
          exit;
        }
        //check in the newsafe
        $accountquery = $readDB->prepare('SELECT * from accounts   WHERE accounts_id =:account ');
        $accountquery->bindParam(':account', $accountTypeID, PDO::PARAM_INT);
        $accountquery->execute();
        $rowaccountCount = $accountquery->rowCount();

        if ($rowaccountCount === 0) {
            $response = new Response();
            $response->setHttpStatusCode(404);
            $response->setSuccess(false);
            $response->addMessage("sacco/safe account to transact funds from not found");
            $response->send();
            exit;
          }

          $rowAccount = $accountquery->fetch(PDO::FETCH_ASSOC);
          $accountsid = $rowAccount['accounts_id'];
          $openingbalance = $rowAccount['opening_balance'];
        // echo  $openingbalance;
          if ($AccountCheckWithdrawAmount > $openingbalance) {
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage("Not enough funds in the safe to complete the transaction");
            $response->send();
            exit;
          }
        // select account to receive funds
        $member_toquery = $readDB->prepare('SELECT * from members, saccos,member_accounts
        WHERE members.saccos_sacco_id = saccos.sacco_id AND member_accounts_id =:member_account
        AND member_id = :account');
        $member_toquery->bindParam(':account', $account, PDO::PARAM_INT);
        $member_toquery->bindParam(':member_account', $member_account, PDO::PARAM_INT);
        $member_toquery->execute();
        $rowCount = $member_toquery->rowCount();

        if ($rowCount === 0) {
          $response = new Response();
          $response->setHttpStatusCode(404);
          $response->setSuccess(false);
          $response->addMessage("account to deposit on not found");
          $response->send();
          exit;
        }
        //deposit account details
        $roww = $member_toquery->fetch(PDO::FETCH_ASSOC);

        $deposit_account_status = $roww['account_status'];
        $deposit_firstname = $roww['member_fname'];
        $deposit_lastname = $roww['member_lname'];
        $deposit_accountContact = $roww['member_contact'];
        $deposit_memberID = $roww['member_id'];
        $deposit_AccountBalance = (int) $roww['total_deposit'];
        $deposit_AccountNumber = $roww['members_account_number'];
        if ($deposit_account_status !== 'active') {
          $response = new Response();
          $response->setHttpStatusCode(403);
          $response->setSuccess(false);
          $response->addMessage("account has been blocked");
          $response->send();
          exit;
        }

        // make the new account balance
        $newAccountBalance = $AccountBalance - ($amount + $AccountWithdrawCharge);
        //safe account balancee update
        $newsafeBalance=$openingbalance-($amount + $AccountWithdrawCharge);
        $withdrawchargebalance= $wopeningbalance + $AccountWithdrawCharge;

        $transactionID = getGUIDnoHash();
        $transactionStatus = 'successful';

        //deposit Settings
        $deposit_transactionID = getGUIDnoHash();
        // $deposit_transactionStatus = 'successful';
        $deposit_despositCharge = (int) 0;
        // make the new account balance
        $deposit_newAccountBalance = $deposit_AccountBalance + $amount;

        $withdraw=$firstname.' '.$lastname;
      try {
      $writeDB->beginTransaction();
      $query = $writeDB->prepare('INSERT into withdrawal_transactions
      (withdraw_amount,withdraw_timestamp,withdrawal_balance,withdraw_notes,withdraw_person,withdraw_charge,
      withdraw_trans_id,withdraw_status,withdraw_method,members_member_id,
      saccos_sacco_id,users_user_id,branches_branch_id,withdraw_member_account_id)
      values(:amount,:dateodwithdraw,:balance, :notes, :person, :charge, :transID, :status, :method, :member,
       :saccoid, :userid,:branch,:member_account)');
       $query->bindParam(':amount',$amount, PDO::PARAM_INT);
      $query->bindParam(':dateodwithdraw',$dow, PDO::PARAM_INT);
      $query->bindParam(':notes', $notes, PDO::PARAM_STR);
      $query->bindParam(':person', $withdraw, PDO::PARAM_STR);
      $query->bindParam(':charge', $AccountWithdrawCharge, PDO::PARAM_INT);
      $query->bindParam(':transID',$transactionID, PDO::PARAM_STR);
      $query->bindParam(':status', $transactionStatus, PDO::PARAM_STR);
      $query->bindParam(':method', $transactionMethod, PDO::PARAM_STR);
      $query->bindParam(':member', $memberID, PDO::PARAM_INT);
      $query->bindParam(':balance', $newAccountBalance, PDO::PARAM_INT);
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $query->bindParam(':userid', $returned_id, PDO::PARAM_INT);
      $query->bindParam(':branch', $returned_branch_id, PDO::PARAM_INT);
      $query->bindParam(':member_account', $memberaccountfrom, PDO::PARAM_INT);
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
        $query = $writeDB->prepare('UPDATE accounts set opening_balance = :amount
          where account_sacco_id = :id AND accounts_id=:account');
        $query->bindParam(':amount', $newsafeBalance, PDO::PARAM_INT);
        $query->bindParam(':account', $accountTypeID, PDO::PARAM_INT);
        $query->bindParam(':id', $returned_saccoid, PDO::PARAM_INT);
        $query->execute();
        $rowCount = $query->rowCount();

        if($rowCount === 0){
          // set up response for unsuccessful return
          $response = new Response();
          $response->setHttpStatusCode(400);
          $response->setSuccess(false);
          $response->addMessage("there was a problem updating safe balance");
          $response->send();
          exit;
        }
      //commit the change
      $query = $writeDB->prepare('UPDATE member_accounts set total_deposit = :amount
        WHERE member_accounts_id=:member_account AND member_accounts_member_id=:memberid');
        $query->bindParam(':amount', $newAccountBalance, PDO::PARAM_INT);
        $query->bindParam(':member_account', $memberaccountfrom, PDO::PARAM_INT);
        $query->bindParam(':memberid', $memberfrom, PDO::PARAM_INT);
        $query->execute();

        $rowCount = $query->rowCount();
        if($rowCount === 0){
          // set up response for unsuccessful return
          $response = new Response();
          $response->setHttpStatusCode(400);
          $response->setSuccess(false);
          $response->addMessage("there was an issue the transaction reversed");
          $response->send();
          exit;
        }
        $depositquery = $writeDB->prepare('INSERT into desposit_transactions
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
            users_user_id,branches_branch_id,deposit_account_member_id)
            values(:amount,:dop,:balance, :notes, :person, :charge, :transID, :status,
              :method, :member, :saccoid, :userid,:branch,:member_account)');
              $depositquery->bindParam(':amount', $amount, PDO::PARAM_INT);
              $depositquery->bindParam(':dop', $dow, PDO::PARAM_INT);
              $depositquery->bindParam(':balance', $deposit_newAccountBalance, PDO::PARAM_INT);
              $depositquery->bindParam(':notes', $deposit_notes, PDO::PARAM_STR);
              $depositquery->bindParam(':person', $withdraw, PDO::PARAM_STR);
              $depositquery->bindParam(':charge', $deposit_despositCharge, PDO::PARAM_INT);
              $depositquery->bindParam(':transID', $deposit_transactionID, PDO::PARAM_STR);
              $depositquery->bindParam(':status', $transactionStatus, PDO::PARAM_STR);
              $depositquery->bindParam(':method', $transactionMethod, PDO::PARAM_STR);
              $depositquery->bindParam(':member', $deposit_memberID, PDO::PARAM_INT);
              $depositquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
              $depositquery->bindParam(':userid', $returned_id, PDO::PARAM_INT);
              $depositquery->bindParam(':branch', $returned_branch_id, PDO::PARAM_INT);
              $depositquery->bindParam(':member_account', $member_account, PDO::PARAM_INT);
              $depositquery->execute();
              $deposit_lastinsert=$writeDB->lastInsertId();
              $transfer_type='client to client';
              // check client to client Transfer
              $clientQuery = $writeDB->prepare('INSERT into client_to_client_transfer
              (`transfer_from_member_account`,
              `transfer_from_member_id`,
              `deposit_trans_id`, `client_transfer_withdraw_id`,`transfer_type`)
              VALUES(:member_account,:member, :id,:widthdraw,:transfer_type )
              ');

              $clientQuery->bindParam(':member', $memberfrom, PDO::PARAM_INT);
              $clientQuery->bindParam(':member_account', $memberaccountfrom, PDO::PARAM_INT);
              $clientQuery->bindParam(':id', $deposit_lastinsert, PDO::PARAM_INT);
              $clientQuery->bindParam(':transfer_type', $transfer_type, PDO::PARAM_INT);
              $clientQuery->bindParam(':widthdraw', $lastID, PDO::PARAM_INT);
              $clientQuery->execute();

              $updateBalance = $writeDB->prepare('UPDATE member_accounts set total_deposit = :amount
              WHERE member_accounts_id=:member_account AND member_accounts_member_id=:memberid');
              $updateBalance->bindParam(':amount', $deposit_newAccountBalance, PDO::PARAM_INT);
              $updateBalance->bindParam(':member_account', $member_account, PDO::PARAM_INT);
              $updateBalance->bindParam(':memberid', $deposit_memberID, PDO::PARAM_INT);
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
              $newopeningbalance = $newsafeBalance + $amount;
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
              // withdraw charge account
              $wquery = $writeDB->prepare('UPDATE accounts set opening_balance = :amount
                where account_sacco_id = :id AND accounts_id=:account');
              $wquery->bindParam(':amount', $withdrawchargebalance, PDO::PARAM_INT);
              $wquery->bindParam(':account', $withdrawaccountid, PDO::PARAM_INT);
              $wquery->bindParam(':id', $returned_saccoid, PDO::PARAM_INT);
              $wquery->execute();
              $wrowCount = $wquery->rowCount();
              if($wrowCount === 0){
                // set up response for unsuccessful return
                $response = new Response();
                $response->setHttpStatusCode(400);
                $response->setSuccess(false);
                $response->addMessage("there was a problem updating withdraw charge balance");
                $response->send();
                exit;
              }
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
      $query = $writeDB->prepare('SELECT * FROM withdrawal_transactions,
      client_to_client_transfer, members, users,member_accounts,accounts
      WHERE withdrawal_transactions.members_member_id = members.member_id
      AND withdrawal_transactions.withdraw_member_account_id=member_accounts.member_accounts_id
      AND withdrawal_transactions.members_member_id=client_to_client_transfer.transfer_from_member_id
      AND  withdrawal_transactions.users_user_id = users.user_id
      AND members.member_id=client_to_client_transfer.transfer_from_member_id
      AND members.member_id=member_accounts.member_accounts_member_id
      AND member_accounts.member_accounts_account_id=accounts.accounts_id
      AND client_to_client_transfer.transfer_from_member_account =withdrawal_transactions.withdraw_member_account_id
      AND client_to_client_transfer.transfer_from_member_account =member_accounts.member_accounts_id
      AND client_to_client_transfer.client_transfer_withdraw_id =withdrawal_transactions.withdraw_id
      AND client_to_client_transfer.client_transfer_withdraw_id =:ids
      AND  withdraw_id = :id AND withdrawal_transactions.saccos_sacco_id = :saccoid
      AND client_to_client_transfer.transfer_type=:transfer_type
      ORDER BY withdraw_timestamp DESC');
      $query->bindParam(':transfer_type', $transfer_type, PDO::PARAM_INT);
      $query->bindParam(':id', $lastID, PDO::PARAM_INT);
      $query->bindParam(':ids', $lastID, PDO::PARAM_INT);
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $query->execute();

      // get row count
      $rowCount = $query->rowCount();
      // make sure that the new task was returned
      if($rowCount === 0) {
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage("Failed to retrieve withdraw transaction after creation");
        $response->send();
        exit;
      }
      // last deposit insert in the account
      $ClientTransferFromArray = array();

      while($row = $query->fetch(PDO::FETCH_ASSOC)):
                extract($row);
               $Withdraw = array(
                 "id" => $withdraw_id,
                 "account" => $members_account_number,
                 "accountname" => $account_name,
                 "balance" => $total_deposit,
                 "firstname" => $member_fname,
                 "lastname" => $member_lname,
                 "amount" => $withdraw_amount,
                 "withdraw" => $withdraw_person,
                 "notes" => $withdraw_notes,
                 "charge" => $withdraw_charge,
                 "method" => $withdraw_method,
                 "status" => $withdraw_status,
                 "teller" => $user_fullname,
                 "transactionID" => $withdraw_trans_id,
                 "timestamp" => $withdraw_timestamp,
               );
               $ClientTransferFromArray[] = $Withdraw;
              endwhile;
              //client transfer to
              $depositquery = $writeDB->prepare('SELECT * FROM desposit_transactions,
                 members,member_accounts, accounts, client_to_client_transfer, users
                 WHERE desposit_transactions.users_user_id = users.user_id
              AND client_to_client_transfer.deposit_trans_id  =desposit_transactions.deposit_id
              AND accounts.accounts_id=member_accounts.member_accounts_account_id
              AND desposit_transactions.members_member_id = member_accounts.member_accounts_member_id
              AND member_accounts.member_accounts_id = desposit_transactions.deposit_account_member_id
              AND members.member_id=member_accounts.member_accounts_member_id
              AND desposit_transactions.saccos_sacco_id = :saccoid
              AND client_to_client_transfer.transfer_type=:transfer_type
              AND   deposit_id = :id ORDER BY desposit_timestamp DESC');
              $depositquery->bindParam(':transfer_type', $transfer_type, PDO::PARAM_INT);
              $depositquery->bindParam(':id', $deposit_lastinsert, PDO::PARAM_INT);
              $depositquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
              $depositquery->execute();

              // get row count
              $depositRowCount = $depositquery->rowCount();

              // make sure that the new task was returned
              if($depositRowCount === 0) {
                // set up response for unsuccessful return
                $response = new Response();
                $response->setHttpStatusCode(500);
                $response->setSuccess(false);
                $response->addMessage("Failed to retrieve deposit transaction after creation");
                $response->send();
                exit;
              }
              $ClientTransferToArray=array();
              while($row = $depositquery->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
               $deposit = array(
                 "id" => $deposit_id,
                 "account" => $members_account_number,
                 "accountname" => $account_name,
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
               $ClientTransferToArray[] = $deposit;
              }
              $transactionArray = array();
              // account info array
              $TransactionArray = array(
                // "number" => $AccountNumber,
                // "balance" => $newAccountBalance,
                // "firstname" => $firstname,
                // "lastname" => $lastname,
                "clienttransferfrom" => $ClientTransferFromArray,
                "clienttransferto" => $ClientTransferToArray
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
        $message = "A withdraw of UGX ".$newAmount." has been made from A/C: ".$AccountNumber." (".$firstname." " .$lastname.") in ".$saccoName.". TxID: ".$withdraw_trans_id. ". Date: ".$date. ".\nNew balance: UGX ".$newAccountsBalance;
        $depositmessage = "A saving of UGX ".$newAmount." has been made to A/C: ".$deposit_AccountNumber." (".$deposit_firstname." " .$deposit_lastname.") in ".$saccoName." has been made from A/C: ".$AccountNumber." (".$firstname." " .$lastname.")"." TxID: ".$deposit_transactionID. ". Date: ".$date. ".\nNew balance: UGX ".$deposit_newAccountBalance;

        $smsArray=[$message,$depositmessage];
        $smsContactArray=[$accountContact,$deposit_accountContact];

        // $emailArray = array('' => , );
        // insert sms into the database
        if ($sacco_sms_status === 'on' ) {
          for ($i=0; $i <count($smsArray) ; $i++) {
          insertSMSDB($writeDB, $smsArray[$i], $smsContactArray[$i], $returned_saccoid);
          }
        }
        // insert email into the database
        if ($sacco_email_status === 'on') {
          // code...
          for ($x=0; $x <count($smsArray) ; $x++) {
          insertEMAILDB($writeDB, $smsArray[$x], $saccoEmail, $returned_saccoid);
          }
          // insertEMAILDB($writeDB, $depositmessage, $saccoEmail, $returned_saccoid);
        }
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
      $response->addMessage("failed to create withdraw $ex");
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
