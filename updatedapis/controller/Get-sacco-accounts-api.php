<?php
require_once('../core/initialize.php');
require_once('Database.php');
require_once('../model/Response.php');

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
  $query = $writeDB->prepare('select user_id, branches_branch_id, access_token_expiry, user_status, saccos_sacco_id, user_login_attempts from sessions_users, users where sessions_users.users_user_id = users.user_id and access_token = :accesstoken');
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
  //sacco Info
  $query = $writeDB->prepare('select * from saccos where sacco_id = :id');
  $query->bindParam(':id', $returned_saccoid, PDO::PARAM_INT);
  $query->execute();
  $row = $query->fetch(PDO::FETCH_ASSOC);

  $saccoshortname = $row['sacco_short_name'];
  $sacconame = $row['sacco_name'];
  $saccocontact = $row['sacco_contact'];
  $saccoemail = $row['sacco_email'];

}
catch(PDOException $ex) {
  $response = new Response();
  $response->setHttpStatusCode(500);
  $response->setSuccess(false);
  $response->addMessage("There was an issue authenticating - please try again");
  $response->send();
  exit;
}

if  (array_key_exists('accountid', $_GET)) {
$accountid = $_GET['accountid'];

if($accountid == '' || !is_numeric($accountid)) {
  $response = new Response();
  $response->setHttpStatusCode(400);
  $response->setSuccess(false);
  $response->addMessage("account ID cannot be blank or must be numeric");
  $response->send();
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  // we pick the orders name and products under that orders
  try {
    // create db query
    $query = $writeDB->prepare('SELECT accounts_id,account_name,account_code
       from accounts,account_group,subaccountgroups
      WHERE  account_sacco_id = :saccoid
    AND accounts.sub_account_id =subaccountgroups.subaccountid
    AND account_group.account_group_id =subaccountgroups.account_group
    AND accounts_id=:accountid');
    $query->bindParam(':accountid', $accountid, PDO::PARAM_STR);
    $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_STR);
    $query->execute();

    $rowCount = $query->rowCount();
    if($rowCount === 0) {
      // set up response for unsuccessful return
      $response = new Response();
      $response->setHttpStatusCode(404);
      $response->setSuccess(false);
      $response->addMessage("accounts not found");
      $response->send();
      exit;
    }
    $accountsArray=array();
    while($rowaccount = $query->fetch(PDO::FETCH_ASSOC)) {
      extract($rowaccount);
      $accounts = array(
        "id" => $accounts_id ,
        "account" => $account_name,
        "code" => $account_code,
        "account_group_name"=>$account_group_name
    );
    $accountsArray[]=$accounts;
    }

    $returnData = array();
    $returnData['rows_returned'] = $rowCount;
    $returnData['account'] = $accountsArray;
    // set up response for successful return
    $response = new Response();
    $response->setHttpStatusCode(200);
    $response->setSuccess(true);
    $response->toCache(true);
    $response->setData($returnData);
    $response->send();
    exit;
  } catch (PDOException $ex) {
    $response = new Response();
    $response->setHttpStatusCode(500);
    $response->setSuccess(false);
    $response->addMessage("failed to getsacco acounts $ex");
    $response->send();
    exit;
  }

}
    else{
      // return a json response on method not allowed
      $response = new Response();
      $response->setHttpStatusCode(405);
      $response->setSuccess(false);
      $response->addMessage("Request method not allowed");
      $response->send();
      exit;
    }

  }
  elseif (empty($_GET)) {
    // code...
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
      // we pick the orders name and products under that orders
      try {
        // create db query
        $query = $writeDB->prepare('SELECT accounts_id,account_name,account_code,account_group_name
           from accounts,subaccountgroups,account_group
          WHERE  account_sacco_id = :saccoid
        AND accounts.sub_account_id =subaccountgroups.subaccountid
        AND account_group.account_group_id =subaccountgroups.account_group');
        $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_STR);
        $query->execute();

        $rowCount = $query->rowCount();
        $accountsArray=array();
        while($rowaccount = $query->fetch(PDO::FETCH_ASSOC)) {
          extract($rowaccount);
          $accounts = array(
            "id" => $accounts_id ,
            "account" => $account_name,
            "code" => $account_code,
            "account_group_name"=>$account_group_name
        );
        $accountsArray[]=$accounts;
        }

        $returnData = array();
        $returnData['rows_returned'] = $rowCount;
        $returnData['accounts'] = $accountsArray;
        // set up response for successful return
        $response = new Response();
        $response->setHttpStatusCode(200);
        $response->setSuccess(true);
        $response->toCache(true);
        $response->setData($returnData);
        $response->send();
        exit;
      } catch (PDOException $ex) {
        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage("failed to get sacco acounts $ex");
        $response->send();
        exit;
      }

    }
        else{
          // return a json response on method not allowed
          $response = new Response();
          $response->setHttpStatusCode(405);
          $response->setSuccess(false);
          $response->addMessage("Request method not allowed");
          $response->send();
          exit;
        }
  }
  else {
    $response = new Response();
    $response->setHttpStatusCode(404);
    $response->setSuccess(false);
    $response->addMessage("endpoint not found");
    $response->send();
  }
