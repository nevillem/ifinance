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

// check if id is in the url e.g. /s/1
if (array_key_exists("accountid",$_GET)) {
  // get  id from query string
  $accountid = $_GET['accountid'];
  //check to see if  id in query string is not empty and is number, if not return json error
  if($accountid == '' || !is_numeric($accountid)) {
    $response = new Response();
    $response->setHttpStatusCode(400);
    $response->setSuccess(false);
    $response->addMessage("AccountID cannot be blank or must be numeric");
    $response->send();
    exit;
  }

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // create
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
      if(!isset($jsonData->mindate) || !isset($jsonData->maxdate) || empty($jsonData->maxdate) || empty($jsonData->maxdate)) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        (!isset($jsonData->mindate) ? $response->addMessage("mindate field is mandatory and must be provided") : false);
        (!isset($jsonData->maxdate) ? $response->addMessage("maxdate field is mandatory and must be provided") : false);
        (empty($jsonData->maxdate) ? $response->addMessage("maxdate field cannot be empty") : false);
        (empty($jsonData->maxdate) ? $response->addMessage("maxdate field cannot be empty") : false);
        $response->send();
        exit;
      }
      // initial variables for data pick
        $maxdate =  $jsonData->maxdate;
        $mindate =  $jsonData->mindate;
        // echo json_encode($maxdate);
      // generate the info statement document
      $query = $readDB->prepare('SELECT * from desposit_transactions where desposit_timestamp between :mindate and :maxdate and members_member_id = :id and desposit_status="successful" order by deposit_id desc');
      $query->bindParam(':id', $accountid, PDO::PARAM_INT);
      $query->bindParam(':mindate', $mindate, PDO::PARAM_STR);
      $query->bindParam(':maxdate', $maxdate, PDO::PARAM_STR);
      $query->execute();

      // $rowCount = $query->rowCount();
      // if ($rowCount === 0) {
      //   $response = new Response();
      //   $response->setHttpStatusCode(404);
      //   $response->setSuccess(false);
      //   $response->addMessage("no deposit transactions found");
      //   $response->send();
      //   exit;
      // }

      $DepositArray = array();
      while($row = $query->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
       $Deposittransactions = array(
         "amount" => $deposit_amount,
         "balance" => $desposit_balance,
         "date" => $desposit_timestamp,
         "notes" => $desposit_notes,
         "person" => $desposit_person,
         "reference" => $desposit_trans_id,
         "charge" => $desposit_charge,
         "method" => $deposit_method
       );
       $DepositArray[] = $Deposittransactions;
     }
     $query = $readDB->prepare('SELECT * from withdrawal_transactions where withdraw_timestamp between :mindate and :maxdate and members_member_id = :id and withdraw_status = "successful" order by withdraw_id desc');
     $query->bindParam(':id', $accountid, PDO::PARAM_INT);
     $query->bindParam(':mindate', $mindate, PDO::PARAM_STR);
     $query->bindParam(':maxdate', $maxdate, PDO::PARAM_STR);
     $query->execute();

     // $rowCount = $query->rowCount();
     // if ($rowCount === 0) {
     //   $response = new Response();
     //   $response->setHttpStatusCode(404);
     //   $response->setSuccess(false);
     //   $response->addMessage("no deposit transactions found");
     //   $response->send();
     //   exit;
     // }

     $WithdrawArray = array();
     while($row = $query->fetch(PDO::FETCH_ASSOC)) {
       extract($row);
      $Withdrawtransactions = array(
        "amount" => $withdraw_amount,
        "balance" => $withdrawal_balance,
        "date" => $withdraw_timestamp,
        "notes" => $withdraw_notes,
        "person" => $withdraw_person,
        "reference" => $withdraw_trans_id,
        "charge" => $withdraw_charge,
        "method" => $withdraw_method
      );
      $WithdrawArray[] = $Withdrawtransactions;
    }
      // generate the info statement document
      $query = $readDB->prepare('select * from members,saccos
      where members.saccos_sacco_id = saccos.sacco_id and member_id = :id');
      $query->bindParam(':id', $accountid, PDO::PARAM_INT);
      $query->execute();

      $rowCount = $query->rowCount();
      if ($rowCount === 0) {
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("no data found");
        $response->send();
        exit;
      }
      $infoArray = array();
      while($row = $query->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
       $info = array(
         "name" => $sacco_name,
         "contact" => $sacco_contact,
         "address" => $sacco_address,
         "email" => $sacco_email,
         "mindate"=>$mindate,
         "maxdate"=>$maxdate,
         "logosacco"=>$maxdate,
         "accountfname" => $member_fname,
         "accountlname" => $member_lname,
         "accountemail" => $member_email,
         "accountcontact" => $member_contact,
         "accountaddress" => $member_address,
         "accountbalance" => $members_account_volunteer,
         "accountnumber" => $members_account_number,
         "deposits" => $DepositArray,
         "withdraws" => $WithdrawArray
       );
       $infoArray[] = $info;
     }

      // bundle s and rows returned into an array to return in the json data
      $returnData = array();
      $returnData['rows_returned'] = $rowCount;
      $returnData['statement'] = $infoArray;
      $response = new Response();
      $response->setHttpStatusCode(201);
      $response->setSuccess(true);
      $response->addMessage("data retrived");
      $response->setData($returnData);
      $response->send();
      exit;
    }
    // if  fails to create due to data types, missing fields or invalid data then send error json
    catch(PDOException $ex) {
      $response = new Response();
      $response->setHttpStatusCode(500);
      $response->setSuccess(false);
      $response->addMessage('failed to pick data'.$ex);
      $response->send();
      exit;
    }
  }
}
