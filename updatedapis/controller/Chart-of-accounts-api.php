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
// Authenticate sacco with access token
// check to see if access token is provided in the HTTP Authorization header and that the value is longer than 0 chars
// don't forget the Apache fix in .htaccess file
if(!isset($_SERVER['HTTP_AUTHORIZATION']) || strlen($_SERVER['HTTP_AUTHORIZATION']) < 1):
  $response = new Response();
  $response->setHttpStatusCode(401);
  $response->setSuccess(false);
  (!isset($_SERVER['HTTP_AUTHORIZATION']) ? $response->addMessage("Access token is missing from the header") : false);
  (strlen($_SERVER['HTTP_AUTHORIZATION']) < 1 ? $response->addMessage("Access token cannot be blank") : false);
  $response->send();
  exit;
endif;

  // get supplied access token from authorisation header - used for delete (log out) and patch (refresh)
  $accesstoken = $_SERVER['HTTP_AUTHORIZATION'];

// attempt to query the database to check token details - use write connection as it needs to be synchronous for token
try {
  // create db query to check access token is equal to the one provided
  $query = $writeDB->prepare('select sacco_id, access_token_expiry, sacco_status, sacco_login_attempts from sessions, saccos where sessions.saccos_sacco_id = saccos.sacco_id and access_token = :accesstoken');
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
  $returned_saccoid = $row['sacco_id'];
  $returned_accesstokenexpiry = $row['access_token_expiry'];
  $returned_sacco_active = $row['sacco_status'];
  $returned_loginattempts = $row['sacco_login_attempts'];

  // check if account is active
  if($returned_sacco_active != 'active'):
    $response = new Response();
    $response->setHttpStatusCode(401);
    $response->setSuccess(false);
    $response->addMessage("sacco account is not active");
    $response->send();
    exit;
  endif;

  // check if account is locked out
  if($returned_loginattempts >= 3):
    $response = new Response();
    $response->setHttpStatusCode(401);
    $response->setSuccess(false);
    $response->addMessage("sacco account is currently locked out");
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

  if (array_key_exists('accountgroupid', $_GET)) {

    $accountgroupid = $_GET['accountgroupid'];

    if($accountgroupid == '' || !is_numeric($accountgroupid)) {
      $response = new Response();
      $response->setHttpStatusCode(400);
      $response->setSuccess(false);
      $response->addMessage("account group id cannot be blank or must be numeric");
      $response->send();
      exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {

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

  } elseif(empty($_GET)){
        // get the user profile data
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        try {
          $query = $writeDB->prepare('SELECT`account_group_id`, `account_group_name`, `account_group_code`
             FROM account_group WHERE group_account_saccoid=:saccoid');
          $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
          $query->execute();
          $rowCount = $query->rowCount();
          $accountGroupArray = array();
          while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $accountgroups = array(
              "id" => $account_group_id,
              "accountgroup" => $account_group_name,
              "accountg_groupcode"=>$account_group_code
          );
          $subaccounts = $writeDB->prepare('SELECT `subaccountid`, `subaccount_name`, `subaccount_code` FROM subaccountgroups
             WHERE account_group=:account_group_id AND subaccount_saccoid=:saccoid');
          $subaccounts->bindParam(':account_group_id', $account_group_id, PDO::PARAM_STR);
          $subaccounts->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_STR);
          $subaccounts->execute();
          $subaccountsArray = array();
          while($subaccountsrow = $subaccounts->fetch(PDO::FETCH_ASSOC)) {
            extract($subaccountsrow);
            $subaccountsDetails = array(
              "subaccid" => $subaccountid,
              "name" => $subaccount_name,
              "code" => $subaccount_code,
          );
          $accountsQuery = $writeDB->prepare('SELECT  `accounts_id`,  `account_name`, `account_code`
            FROM  accounts WHERE sub_account_id=:subaccountid');
        $accountsQuery->bindParam(':subaccountid', $subaccountid, PDO::PARAM_STR);
        $accountsQuery->execute();
        $accountsArray=array();
        while($accountsrow = $accountsQuery->fetch(PDO::FETCH_ASSOC)) {
          extract($accountsrow);
          $accounts = array(
            "accountid" => $accounts_id,
            // "subaccount" => $subaccount_name,
            // "subaccountcode" => $subaccount_code,
            "accountname" => $account_name,
            "accountcode" => $account_code,
        );
        $accountsArray[]=$accounts;
        }
        $subaccountsDetails["accounts"]=$accountsArray;

          $subaccountsArray[]=$subaccountsDetails;
          }

          $accountgroups["subaccounts"]=$subaccountsArray;
          $accountGroupArray[] = $accountgroups;
          }
            $returnData = array();
            $returnData['rows_returned'] = $rowCount;
            $returnData['accountgroups'] = $accountGroupArray;
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
          $response->addMessage("internal server error $ex");
          $response->send();
          exit;
        }
    }
 else {
      $response = new Response();
      $response->setHttpStatusCode(405);
      $response->setSuccess(false);
      $response->addMessage("request method not allowed");
      $response->send();
    }
  }
  else {
    $response = new Response();
    $response->setHttpStatusCode(404);
    $response->setSuccess(false);
    $response->addMessage("endpoint not found");
    $response->send();
  }
