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
  $query = $writeDB->prepare('select user_id, access_token_expiry, user_status, saccos_sacco_id, user_fullname, user_login_attempts from sessions_users, users where sessions_users.users_user_id = users.user_id and access_token = :accesstoken');
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
      $query = $readDB->prepare('SELECT * from shares, members, users where deposit_fixed.members_member_id = members.member_id and deposit_fixed.users_user_id = users.user_id and deposit_fixed_id = :transactionid and deposit_fixed.saccos_sacco_id = :saccoid');
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
         "expected" => $deposit_fixed_expected,
         "startdate" => $deposit_fixed_startdate,
         "enddate" => $deposit_fixed_endtime,
         "percentage" => $deposit_fixed_percentage,
         "transactionID" => $deposit_fixed_ref,
         "timestamp" => $deposit_fixed_timestamp,
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
      $query = $readDB->prepare('select * from shares, members where shares.members_member_id = members.member_id and shares.saccos_sacco_id = :saccoid order by share_id DESC');
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
         "id" => $share_id,
         "account" => $members_account_number,
         "firstname" => $member_fname,
         "lastname" => $member_lname,
         "amount" => $shares_amount,
         "units" => $share_number,
         "transid" => $share_transaction_id,
         "status" => $shares_status
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
      if(!isset($jsonData->account) || !isset($jsonData->units)|| !isset($jsonData->type) ||
        empty($jsonData->account) || empty($jsonData->units) || empty($jsonData->type)) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        (!isset($jsonData->account) ? $response->addMessage("account field is mandatory and must be provided") : false);
        (!isset($jsonData->units) ? $response->addMessage("units field is mandatory and must be provided") : false);
        (!isset($jsonData->type) ? $response->addMessage("type field is mandatory and must be provided") : false);
        (empty($jsonData->account) ? $response->addMessage("account field must not be empty") : false);
        (empty($jsonData->units) ? $response->addMessage("units field must not be empty") : false);
        (empty($jsonData->type) ? $response->addMessage("type field must not be empty") : false);
        $response->send();
        exit;
      }

      $account = $jsonData->account;
      $units = (int)$jsonData->units;
      $type = $jsonData->type;

      $query = $readDB->prepare('select * from members where member_id = :account');
      $query->bindParam(':account', $account, PDO::PARAM_INT);
      $query->execute();
      $rowCount = $query->rowCount();

      if ($rowCount === 0) {
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("account type not found");
        $response->send();
        exit;
      }
      $row = $query->fetch(PDO::FETCH_ASSOC);

      $member_balance = $row['member_account_shares'];

      // check whether the pin is valid
      $query = $readDB->prepare('select * from share_settings where share_id = :type');
      $query->bindParam(':type', $type, PDO::PARAM_INT);
      $query->execute();
      $rowCount = $query->rowCount();

      if ($rowCount === 0) {
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("share type not found");
        $response->send();
        exit;
      }

      // get  row returned
      $row = $query->fetch(PDO::FETCH_ASSOC);
      // return the row of pin
      $returned_price = $row['share_price'];
      //verify the pincode
      $share_amount = $units * $returned_price;
      $transactionID = getGUIDnoHash();

      try {
      $writeDB->beginTransaction();
      $query = $writeDB->prepare('insert into shares (share_number,share_transaction_id,shares_amount,saccos_sacco_id,members_member_id,share_settings_share_id,users_user_id)
      values(:units, :transid, :amount, :saccoid, :account, :type, :userid)');
      $query->bindParam(':amount', $share_amount, PDO::PARAM_INT);
      $query->bindParam(':units', $units, PDO::PARAM_STR);
      $query->bindParam(':account', $account, PDO::PARAM_STR);
      $query->bindParam(':transid', $transactionID, PDO::PARAM_STR);
      $query->bindParam(':userid', $returned_id, PDO::PARAM_INT);
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $query->bindParam(':type', $type, PDO::PARAM_INT);
      $query->execute();

      // get row count
      $rowCount = $query->rowCount();
      // check if row was actually inserted, PDO exception should have caught it if not.
      if($rowCount === 0) {
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage("failed to make share transaction");
        $response->send();
        exit;
      }

      // get last task id so we can return the Task in the json
      $lastID = $writeDB->lastInsertId();
      $newAccountBalance = $member_balance + $share_amount;
      // update the member account balance
      $query = $writeDB->prepare('update members set member_account_shares = :amount where member_id = :account');
      $query->bindParam(':amount', $newAccountBalance, PDO::PARAM_INT);
      $query->bindParam(':account', $account, PDO::PARAM_INT);
      $query->execute();
      $rowCount = $query->rowCount();

      if($rowCount === 0){
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("there was an issue updating the account balance");
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
      $query = $writeDB->prepare('select * from shares where share_id = :id');
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
        $response->addMessage("Failed to retrieve share after creation");
        $response->send();
        exit;
      }
      // last deposit insert in the account
      $transactionArray = array();

      while($row = $query->fetch(PDO::FETCH_ASSOC)){
                extract($row);
                $deposit = array(
                  "id" => $share_id,
                  "account" => $members_account_number,
                  "firstname" => $member_fname,
                  "lastname" => $member_lname,
                  "amount" => $shares_amount,
                  "units" => $share_number,
                  "transid" => $share_transaction_id,
                  "status" => $shares_status
                );
}
      $transactionArray[] = $deposit;
      // bundle tasks and rows returned into an array to return in the json data
      $returnData = array();
      $returnData['rows_returned'] = $rowCount;
      $returnData['transaction'] = $transactionArray;
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
      $response->addMessage("failed to create share deposit");
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
