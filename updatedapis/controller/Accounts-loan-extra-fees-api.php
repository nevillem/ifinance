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

  if (array_key_exists('loanextrafeeid', $_GET)) {

    $loanextrafeeid = $_GET['loanextrafeeid'];

    if($loanextrafeeid == '' || !is_numeric($loanextrafeeid)) {
      $response = new Response();
      $response->setHttpStatusCode(400);
      $response->setSuccess(false);
      $response->addMessage("loan extra fee payment accounts ID cannot be blank or must be numeric");
      $response->send();
      exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
      // we pick the orders name and products under that orders
      try {
        // create db query
          // create  and store in array for return in json data

            $loanrolloverExtrafeesquery = $writeDB->prepare('SELECT `accounts_to_add_extra_feesid`,`account_name`, `account_code`
              from accounts_to_add_extra_fees, accounts
              WHERE accounts_add_fees_id=accounts_id
              AND accounts_to_add_extra_feesid=:loanextrafeeid
              AND accounts_to_add_extra_fees_saccoid=:saccoid');
              $loanrolloverExtrafeesquery->bindParam(':loanextrafeeid', $loanextrafeeid, PDO::PARAM_STR);
              $loanrolloverExtrafeesquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_STR);
              $loanrolloverExtrafeesquery->execute();

          $loanExtrafeesaccountArray=array();
          while($loanrolloverrow = $loanrolloverExtrafeesquery->fetch(PDO::FETCH_ASSOC)) {
            extract($loanrolloverrow);
            $loan_rollover_setting = array(
              "id" => $accounts_to_add_extra_feesid ,
              "account_name" => $account_name,
              "account_code" => $account_code,
            );
          $loanExtrafeesaccountArray[] = $loan_rollover_setting;
          }

          $returnData = array();
          $returnData['rows_returned'] = $rowCount;
          $returnData['loan_extrafees_account'] = $loanExtrafeesaccountArray;
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
        $response->addMessage("failed to get loan extra fee payment account $ex");
        $response->send();
        exit;
      }

    } elseif($_SERVER['REQUEST_METHOD'] === 'DELETE'){
      try {
        // ADD AUTH TO QUERY
        $query = $writeDB->prepare('DELETE from  accounts_to_add_extra_fees
          where  accounts_to_add_extra_feesid=:loanextrafeeid
        AND accounts_to_add_extra_fees_saccoid  = :saccoid');
        $query->bindParam(':loanextrafeeid', $loanextrafeeid, PDO::PARAM_STR);
        $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
        $query->execute();

        // get row count
        $rowCount = $query->rowCount();

        if($rowCount === 0):
                // set up response for unsuccessful return
                $response = new Response();
                $response->setHttpStatusCode(404);
                $response->setSuccess(false);
                $response->addMessage("loan extra fee payment accounts setting  not found");
                $response->send();
                exit;
        else:
                $response = new Response();
                $response->setHttpStatusCode(200);
                $response->setSuccess(true);
                $response->addMessage("loan extra fee payment accounts setting  deleted");
                $response->send();
                exit;
      endif;
      }
      catch(PDOException $ex) {
        // rollback transactions if any outstanding transactions are present
        if($writeDB->inTransaction()):
          $writeDB->rollBack();
        endif;
        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage("Failed to delete loan extra fee payment accounts setting - Attached Info");
        $response->send();
        exit;
      }

    }
    elseif($_SERVER['REQUEST_METHOD'] === 'PATCH'){
      try{
        // check request's content type header is JSON
        if($_SERVER['CONTENT_TYPE'] !== 'application/json'):
          // set up response for unsuccessful request
          $response = new Response();
          $response->setHttpStatusCode(400);
          $response->setSuccess(false);
          $response->addMessage("Content Type header not set to JSON");
          $response->send();
          exit;
        endif;

        // get PATCH request body as the PATCHed data will be JSON format
        $rawPatchData = file_get_contents('php://input');

        if(!$jsonData = json_decode($rawPatchData)):
          // set up response for unsuccessful request
          $response = new Response();
          $response->setHttpStatusCode(400);
          $response->setSuccess(false);
          $response->addMessage("Request body is not valid JSON");
          $response->send();
          exit;
        endif;

        $accountid = false;
        // check if name exists in PATCH
        if(isset($jsonData->account)):
          // set title field updated to true
          $accountid = true;
          // add name field to query field string
          $queryFields .= "accounts_add_fees_id  = :account, ";
        endif;
        // remove the right hand comma and trailing space
        $queryFields = rtrim($queryFields, ", ");

        // check if any branch fields supplied in JSON
        if($accountid === false):
          $response = new Response();
          $response->setHttpStatusCode(400);
          $response->setSuccess(false);
          $response->addMessage("No fields provided");
          $response->send();
          exit;
        endif;
        // create db query to get branch from database to update - use master db
        $query = $writeDB->prepare('SELECT * from  accounts_to_add_extra_fees
           where accounts_to_add_extra_feesid  = :loanextrafeeid
        AND accounts_to_add_extra_fees_saccoid  = :saccoid');
        $query->bindParam(':loanextrafeeid', $loanextrafeeid, PDO::PARAM_STR);
        $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
        $query->execute();

        // get row count
        $rowCount = $query->rowCount();

        // make sure that the branch exists for a given branch id
        if($rowCount === 0):
          // set up response for unsuccessful return
          $response = new Response();
          $response->setHttpStatusCode(404);
          $response->setSuccess(false);
          $response->addMessage("No accounts found to add extra loan fees");
          $response->send();
          exit;
        endif;
        // create the query string including any query fields
        $queryString = "UPDATE  accounts_to_add_extra_fees set ".$queryFields." where accounts_add_fees_id   = :id";
        // prepare the query
        $query = $writeDB->prepare($queryString);

        // if name has been provided
        if($accountid === true):
          // bind the parameter of the new value from the object to the query (prevents SQL injection)
          $query->bindParam(':account', $jsonData->account, PDO::PARAM_STR);
        endif;
        $query->bindParam(':loanextrafeeid', $loanextrafeeid, PDO::PARAM_STR);
        $query->execute();
        if($rowCount === 0):
          // set up response for unsuccessful return
          $response = new Response();
          $response->setHttpStatusCode(400);
          $response->setSuccess(false);
          $response->addMessage("not updated - given values may be the same as the stored values");
          $response->send();
          exit;
        endif;
        $returnData = array();
        $returnData['rows_returned'] = $rowCount;
        // set up response for successful return
        $response = new Response();
        $response->setHttpStatusCode(200);
        $response->setSuccess(true);
        $response->addMessage("account has been updated");
        $response->setData($returnData);
        $response->send();
        exit;
      }
      catch(PDOException $ex) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("failed to update");
        $response->send();
        exit;
      }
      // if error with sql query return a json error
      catch(PDOException $ex) {
        error_log("Database Query Error: ".$ex, 0);
        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage("Failed to update account - check your data for errors" . $ex);
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


  } elseif(empty($_GET)){
        // get the user profile data
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {

        try {
          $loanrolloverExtrafeesquery = $writeDB->prepare('SELECT `accounts_to_add_extra_feesid`,`account_name`, `account_code`
            from accounts_to_add_extra_fees, accounts
            WHERE accounts_add_fees_id=accounts_id
            AND accounts_to_add_extra_fees_saccoid=:saccoid');
            $loanrolloverExtrafeesquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_STR);
            $loanrolloverExtrafeesquery->execute();

        $loanExtrafeesaccountArray=array();
        while($loanrolloverrow = $loanrolloverExtrafeesquery->fetch(PDO::FETCH_ASSOC)) {
          extract($loanrolloverrow);
          $loan_rollover_setting = array(
            "id" => $accounts_to_add_extra_feesid ,
            "account_name" => $account_name,
            "account_code" => $account_code,
          );
        $loanExtrafeesaccountArray[] = $loan_rollover_setting;
        }

        $returnData = array();
        $returnData['rows_returned'] = $rowCount;
        $returnData['loan_extrafees_accounts'] = $loanExtrafeesaccountArray;
            $response = new Response();
            $response->setHttpStatusCode(200);
            $response->setSuccess(true);
            $response->toCache(true);
            $response->setData($returnData);
            $response->send();
            exit;
        } catch (PDOException $ex) {
          // error_log("query error: ${ex}", 3 ,"../../app/logs/error.log");
          $response = new Response();
          $response->setHttpStatusCode(500);
          $response->setSuccess(false);
          $response->addMessage("internal server error $ex");
          $response->send();
          exit;
        }

    }
    elseif($_SERVER['REQUEST_METHOD'] === 'POST'){

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

          // pick the data in
          // get POST request body as the posted data will be JSON format
          $rawPostData = file_get_contents('php://input');

          if(!$jsonData = json_decode($rawPostData)){
            // set up response for unsuccessful request
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage("Request body is not valid JSON");
            $response->send();
            exit;
          }

          // check if post request contains data in body as these are mandatory
          if(!isset($jsonData->accounts )||empty($jsonData->accounts )
        ) {
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            (!isset($jsonData->accounts ) ? $response->addMessage("accounts is mandatory and must be provided") : false);
            $response->send();
            exit;
          }

          for ($i=0; $i <count($jsonData->accounts); $i++) {
            // code...
            $accountts=$jsonData->accounts[$i];
            $query = $writeDB->prepare('INSERT INTO accounts_to_add_extra_fees(
              `accounts_add_fees_id`,`accounts_to_add_extra_fees_saccoid`)
              values (:accountts,:saccoid)');
              $query->bindParam(':accountts', $accountts, PDO::PARAM_STR);
              $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
              $query->execute();
              $rowCount = $query->rowCount();
              $lastID = $writeDB->lastInsertId();
          }

              if ($rowCount === 0) {
                $response = new Response();
                $response->setHttpStatusCode(500);
                $response->setSuccess(false);
                $response->addMessage("internal server error");
                $response->send();
                exit;
              }

              $loanrolloverquery = $writeDB->prepare('SELECT `accounts_to_add_extra_feesid`, `account_name`, `account_code`
                from accounts_to_add_extra_fees, accounts
                WHERE accounts_add_fees_id=accounts_id
                AND accounts_to_add_extra_feesid=:id');
                $loanrolloverquery->bindParam(':id', $lastID, PDO::PARAM_STR);
                $loanrolloverquery->execute();

            $loanExtrafeesaccountArray=array();
            while($loanrolloverrow = $loanrolloverquery->fetch(PDO::FETCH_ASSOC)) {
              extract($loanrolloverrow);
              $loan_rollover_setting = array(
                "id" => $accounts_to_add_extra_feesid ,
                "account_name" => $account_name,
                "account_code" => $account_code,
              );
            $loanExtrafeesaccountArray[] = $loan_rollover_setting;
            }

            $returnData = array();
            $returnData['rows_returned'] = $rowCount;
            $returnData['loan_extrafees_account'] = $loanExtrafeesaccountArray;
              // set up response for successful return
              $response = new Response();
              $response->setHttpStatusCode(201);
              $response->setSuccess(true);
              $response->setData($returnData);
              $response->send();
              exit;

        } catch (PDOException $ex) {
          // error_log("query error: ${ex}", 3 ,"../../app/logs/error.log");
          $response = new Response();
          $response->setHttpStatusCode(500);
          $response->setSuccess(false);
          $response->addMessage("internal server error $ex");
          $response->send();
          exit;
        }

    } else {
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
