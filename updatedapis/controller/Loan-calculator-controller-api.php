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
if(empty($_GET)) {

if($_SERVER['REQUEST_METHOD'] === 'POST') {

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
      if(!isset($jsonData->amount)||!is_numeric($jsonData->amount)|| empty($jsonData->amount)
      || !isset($jsonData->interestrate)||!is_numeric($jsonData->interestrate)|| empty($jsonData->interestrate)
      || !isset($jsonData->loan_rate_type)|| empty($jsonData->loan_rate_type)
      || !isset($jsonData->installments)||!is_numeric($jsonData->installments)|| empty($jsonData->installments)
      || !isset($jsonData->amornitizationinterval) || empty($jsonData->amornitizationinterval)
      ) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        (!isset($jsonData->amount) ? $response->addMessage("principal amount field is mandatory and must be provided") : false);
        (empty($jsonData->amount) ? $response->addMessage("principal amount field must not be empty") : false);
        (!is_numeric($jsonData->amount) ? $response->addMessage("principal amount field value must be numeric") : false);
        (!isset($jsonData->interestrate) ? $response->addMessage("interestrate is mandatory and must be provided") : false);
        (empty($jsonData->interestrate) ? $response->addMessage("interestrate field must not be empty") : false);
        (!is_numeric($jsonData->interestrate) ? $response->addMessage("interestrate field value must be numeric") : false);
        (!isset($jsonData->loan_rate_type) ? $response->addMessage("loan rate type is mandatory and must be provided") : false);
        (empty($jsonData->loan_rate_type) ? $response->addMessage("loan rate type field must not be empty") : false);
        (!isset($jsonData->installments) ? $response->addMessage("number of monthly  field is mandatory and must be provided") : false);
        (empty($jsonData->installments) ? $response->addMessage("number of monthly  field must not be empty") : false);
        (!is_numeric($jsonData->installments) ? $response->addMessage("number of monthly  field must be numeric") : false);
        (!isset($jsonData->amornitizationinterval) ? $response->addMessage("amornitization interval field is mandatory and must be provided") : false);
        (empty($jsonData->amornitizationinterval) ? $response->addMessage("amornitization interval field must not be empty") : false);

        $response->send();
        exit;
      }

      $amount = (int)$jsonData->amount;
      $interest_rate = $jsonData->interestrate;
      $tenure_period = (int) $jsonData->installments;
      $loanratetype =  $jsonData->loan_rate_type;
      $amornitizationinterval =$jsonData->amornitizationinterval;
      $totalint=0;
      $totalloanamt=0;
      $scheduleArray = array();
      // 
      //
      // $loan_app['totalloanamt']= number_format($totalloanamt);
      // $loan_app['totalinterest']= number_format($totalint);
      // $loan_app['orgamount']= number_format((int)$jsonData->amount);
      // $loan_app['loanpaymentschedule']= $scheduleArray;
      $transactionArray[] = $loan_app;

      // bundle rows returned into an array to return in the json data
      $returnData = array();
      $returnData['rows_returned'] = $rowCount;
      $returnData['loanapplication'] = $transactionArray;

      //set up response for successful return
      $response = new Response();
      $response->setHttpStatusCode(200);
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
      $response->addMessage("failed to calculate loan $ex");
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
