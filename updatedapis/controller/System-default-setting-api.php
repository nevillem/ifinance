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

  if (array_key_exists('defaultid', $_GET)) {

    $defaultid = $_GET['defaultid'];

    if($defaultid == '' || !is_numeric($defaultid)) {
      $response = new Response();
      $response->setHttpStatusCode(400);
      $response->setSuccess(false);
      $response->addMessage("system default ID cannot be blank or must be numeric");
      $response->send();
      exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
      // we pick the orders name and products under that orders
      try {
        // create db query
        $systemdefaultquery = $writeDB->prepare('SELECT *
           from system_default_settings
          WHERE system_defaultid=:defaultid AND  default_saccoid = :saccoid');
        $systemdefaultquery->bindParam(':defaultid', $defaultid, PDO::PARAM_STR);
        $systemdefaultquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_STR);
        $systemdefaultquery->execute();

        $rowCount = $systemdefaultquery->rowCount();
        if($rowCount === 0) {
          // set up response for unsuccessful return
          $response = new Response();
          $response->setHttpStatusCode(404);
          $response->setSuccess(false);
          $response->addMessage("system default setting id not found");
          $response->send();
          exit;
        }

        $systemdefaultArray=array();
        while($row = $systemdefaultquery->fetch(PDO::FETCH_ASSOC)) {
          extract($row);
          $systemdefault = array(
            "id" => $system_defaultid ,
            "currency" => $currency,
            "loans_account" => $loans_account,
            "extrafeesaccount"=>$extra_fees_account,
            "shares_account" => $shares_account,
            "loaninterestaccount" => $loan_interest_account,
            "numberofloanstoguarantee" => $numberofloanstoguarantee,
            "withdraws_account" => $withdraws_account,
            "accounting_period" => $accounting_period,
        );
        $systemdefaultArray[]=$systemdefault;
        }

        $returnData = array();
        $returnData['rows_returned'] = $rowCount;
        $returnData['systemdefaultsettings'] = $systemdefaultArray;
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
        $response->addMessage("failed to get system default setting");
        $response->send();
        exit;
      }

    } elseif($_SERVER['REQUEST_METHOD'] === 'DELETE'){
      try {
        // ADD AUTH TO QUERY

        $query = $writeDB->prepare('DELETE from system_default_settings where paymentmethodid = :defaults
        AND default_saccoid = :saccoid');
        $query->bindParam(':defaults', $defaultid, PDO::PARAM_STR);
        $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
        $query->execute();

        // get row count
        $rowCount = $query->rowCount();

        if($rowCount === 0):
                // set up response for unsuccessful return
                $response = new Response();
                $response->setHttpStatusCode(404);
                $response->setSuccess(false);
                $response->addMessage("default setting  not found");
                $response->send();
                exit;
        else:
                $response = new Response();
                $response->setHttpStatusCode(200);
                $response->setSuccess(true);
                $response->addMessage("default setting deleted");
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
        $response->addMessage("Failed to delete default setting - Attached Info");
        $response->send();
        exit;
      }

    } elseif($_SERVER['REQUEST_METHOD'] === 'PATCH'){
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

      $currency = false;
      $loans_account=false;
      $extra_fees_account=false;
      $shares_account=false;
      $loan_interest_account=false;
      $numberofloanstoguarantee=false;
      $withdraws_account=false;
      $accounting_period=false;
      // create blank query fields string to append each field to
      $queryFields = "";
      // check if name exists in PATCH
      if(isset($jsonData->currency)):
        // set title field updated to true
        $currency = true;
        // add name field to query field string
        $queryFields .= "currency  = :currency, ";
      endif;
      // check if name exists in PATCH
      if(isset($jsonData->loans_account)):
        // set title field updated to true
        $loans_account = true;
        // add name field to query field string
        $queryFields .= "loans_account  = :loans_account, ";
      endif;
      if(isset($jsonData->extrafeesaccount)):
        // set title field updated to true
        $extra_fees_account = true;
        // add name field to query field string
        $queryFields .= "extra_fees_account  = :extrafeesaccount, ";
      endif;

      if(isset($jsonData->shares_account)):
        // set title field updated to true
        $shares_account = true;
        // add name field to query field string
        $queryFields .= "shares_account  = :shares_account, ";
      endif;
      if(isset($jsonData->accounting_period)):
        // set title field updated to true
        $accounting_period = true;
        // add name field to query field string
        $queryFields .= "accounting_period  = :accounting_period, ";
      endif;
      if(isset($jsonData->loan_interest_account)):
        // set title field updated to true
        $loan_interest_account = true;
        // add name field to query field string
        $queryFields .= "loan_interest_account  = :loan_interest_account, ";
      endif;
      if(isset($jsonData->numberofloanstoguarantee)):
        // set title field updated to true
        $numberofloanstoguarantee = true;
        // add name field to query field string
        $queryFields .= "numberofloanstoguarantee  = :numberofloanstoguarantee, ";
      endif;
      if(isset($jsonData->withdraws_account)):
        // set title field updated to true
        $withdraws_account = true;
        // add name field to query field string
        $queryFields .= "withdraws_account  = :withdraws_account, ";
      endif;
      if(isset($jsonData->accounting_period)):
        // set title field updated to true
        $accounting_period = true;
        // add name field to query field string
        $queryFields .= "accounting_period  = :accounting_period, ";
      endif;
      // remove the right hand comma and trailing space

      $queryFields = rtrim($queryFields, ", ");

      // check if any branch fields supplied in JSON
      if($currency === false  && $shares_account === false
      && extra_fees_account===false && loans_account===false
       && $accounting_period === false && $loan_interest_account === false
       && $numberofloanstoguarantee === false && $withdraws_account === false
       && $accounting_period === false):
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("No fields provided");
        $response->send();
        exit;
      endif;
      // ADD AUTH TO QUERY
      // create db query to get branch from database to update - use master db
      $query = $writeDB->prepare('SELECT * from system_default_settings WHERE
         system_defaultid  = :defaultid
      AND default_saccoid = :saccoid');
      $query->bindParam(':defaultid', $defaultid, PDO::PARAM_STR);
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $query->execute();
      $rowCount = $query->rowCount();

      // make sure that the branch exists for a given branch id
      if($rowCount === 0):
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("No default setting found to update");
        $response->send();
        exit;
      endif;
      // create the query string including any query fields
      $queryString = "UPDATE system_default_settings set ".$queryFields." where system_defaultid  = :id";
      // prepare the query
      $query = $writeDB->prepare($queryString);
      // if name has been provided
      if($currency === true):
        // bind the parameter of the new value from the object to the query (prevents SQL injection)
        $query->bindParam(':currency', $jsonData->currency, PDO::PARAM_STR);
      endif;
      if($loans_account === true):
        // bind the parameter of the new value from the object to the query (prevents SQL injection)
        $query->bindParam(':loans_account', $jsonData->loans_account, PDO::PARAM_STR);
      endif;
      if($extra_fees_account === true):
        // bind the parameter of the new value from the object to the query (prevents SQL injection)
        $query->bindParam(':extrafeesaccount', $jsonData->extrafeesaccount, PDO::PARAM_STR);
      endif;
      if($shares_account === true):
        // bind the parameter of the new value from the object to the query (prevents SQL injection)
        $query->bindParam(':shares_account', $jsonData->shares_account, PDO::PARAM_STR);
      endif;
      if($accounting_period === true):
        // bind the parameter of the new value from the object to the query (prevents SQL injection)
        $query->bindParam(':accounting_period', $jsonData->accounting_period, PDO::PARAM_STR);
      endif;
      if($loan_interest_account === true):
        // bind the parameter of the new value from the object to the query (prevents SQL injection)
        $query->bindParam(':loan_interest_account', $jsonData->loan_interest_account, PDO::PARAM_STR);
      endif;
      if($numberofloanstoguarantee === true):
        // bind the parameter of the new value from the object to the query (prevents SQL injection)
        $query->bindParam(':numberofloanstoguarantee', $jsonData->numberofloanstoguarantee, PDO::PARAM_STR);
      endif;
      if($withdraws_account === true):
        // bind the parameter of the new value from the object to the query (prevents SQL injection)
        $query->bindParam(':withdraws_account', $jsonData->withdraws_account, PDO::PARAM_STR);
      endif;
      if($accounting_period === true):
        // bind the parameter of the new value from the object to the query (prevents SQL injection)
        $query->bindParam(':accounting_period', $jsonData->accounting_period, PDO::PARAM_STR);
      endif;
      $query->bindParam(':id', $defaultid, PDO::PARAM_STR);
      $query->execute();

      // get affected row count
      $rowCount = $query->rowCount();
      // check if row was actually updated, could be that the given values are the same as the stored values
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
      // $returnData['cropType'] = $cropTypeArray;

      // set up response for successful return
      $response = new Response();
      $response->setHttpStatusCode(200);
      $response->setSuccess(true);
      $response->addMessage("system default settings method has been updated");
      $response->setData($returnData);
      $response->send();
      exit;

    }
    catch(PDOException $ex) {
      $response = new Response();
      $response->setHttpStatusCode(400);
      $response->setSuccess(false);
      $response->addMessage("failed to update $ex");
      $response->send();
      exit;
    }
    // if error with sql query return a json error
    catch(PDOException $ex) {
      error_log("Database Query Error: ".$ex, 0);
      $response = new Response();
      $response->setHttpStatusCode(500);
      $response->setSuccess(false);
      $response->addMessage("Failed to update system default settings - check your data for errors" . $ex);
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

          $systemdefaultquery = $writeDB->prepare('SELECT * from system_default_settings
             WHERE default_saccoid = :saccoid');
          $systemdefaultquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_STR);
          $systemdefaultquery->execute();

          $rowCount = $systemdefaultquery->rowCount();
          // if($rowCount === 0) {
          //   // set up response for unsuccessful return
          //   $response = new Response();
          //   $response->setHttpStatusCode(404);
          //   $response->setSuccess(false);
          //   $response->addMessage("system default setting id not found");
          //   $response->send();
          //   exit;
          // }
          $systemdefaultArray=array();
          while($row = $systemdefaultquery->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $systemdefault = array(
              "id" => $system_defaultid ,
              "currency" => $currency,
              "loans_account" => $loans_account,
              "extrafeesaccount"=>$extra_fees_account,
              "shares_account" => $shares_account,
              "loaninterestaccount" => $loan_interest_account,
              "numberofloanstoguarantee" => $numberofloanstoguarantee,
              "withdraws_account" => $withdraws_account,
              "accounting_period" => $accounting_period,
          );
          $systemdefaultArray[]=$systemdefault;
          }

          $returnData = array();
          $returnData['rows_returned'] = $rowCount;
          $returnData['systemdefaultsettings'] = $systemdefaultArray;
            // set up response for successful return
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
          if(!isset($jsonData->currency)||empty($jsonData->currency)
          ||!isset($jsonData->loans_account)||empty($jsonData->loans_account)
          ||!isset($jsonData->extra_fees_account)||empty($jsonData->extra_fees_account)
          ||!isset($jsonData->shares_account)||empty($jsonData->shares_account)
          ||!isset($jsonData->loan_interest_account)||empty($jsonData->loan_interest_account)
          ||!isset($jsonData->numberofloanstoguarantee)||empty($jsonData->numberofloanstoguarantee)
          ||!is_numeric($jsonData->numberofloanstoguarantee)
          ||!isset($jsonData->withdraws_account)||empty($jsonData->withdraws_account)
          ||!isset($jsonData->accounting_period)
          ||empty($jsonData->accounting_period)
          ) {
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            (!isset($jsonData->currency) ? $response->addMessage("currency is mandatory and must be provided") : false);
            (empty($jsonData->currency) ? $response->addMessage("currency field must not be empty") : false);
            (!isset($jsonData->extra_fees_account) ? $response->addMessage("loan extra fees account  is mandatory and must be provided") : false);
            (empty($jsonData->extra_fees_account) ? $response->addMessage("loan extra fees field must not be empty") : false);
            (!isset($jsonData->loans_account) ? $response->addMessage("loans account  is mandatory and must be provided") : false);
            (empty($jsonData->loans_account) ? $response->addMessage("loans account field must not be empty") : false);
            (!isset($jsonData->shares_account) ? $response->addMessage("shares account  is mandatory and must be provided") : false);
            (empty($jsonData->shares_account) ? $response->addMessage("shares account field must not be empty") : false);
            (!isset($jsonData->loan_interest_account) ? $response->addMessage("loan interest account  is mandatory and must be provided") : false);
            (empty($jsonData->loan_interest_account) ? $response->addMessage("loan interest account field must not be empty") : false);
            (!isset($jsonData->numberofloanstoguarantee) ? $response->addMessage("number of allowed loan guarantees  is mandatory and must be provided") : false);
            (empty($jsonData->numberofloanstoguarantee) ? $response->addMessage("number of allowed loan guarantees field must not be empty") : false);
            (!is_numeric($jsonData->numberofloanstoguarantee) ? $response->addMessage("number of guarantors field must be numeric") : false);
            (!isset($jsonData->withdraws_account) ? $response->addMessage("withdraws account  is mandatory and must be provided") : false);
            (empty($jsonData->withdraws_account) ? $response->addMessage("withdraws account field must not be empty") : false);
            (!isset($jsonData->accounting_period) ? $response->addMessage("accounting period is mandatory and must be provided") : false);
            (empty($jsonData->accounting_period) ? $response->addMessage("accounting period field must not be empty") : false);
            $response->send();
            exit;
          }

          $accquery = $writeDB->prepare('SELECT * from accounts
            where account_sacco_id=:saccoid
            AND account_name =:interestacc');
            $accquery->bindParam(':interestacc', $jsonData->loan_interest_account, PDO::PARAM_STR);
            $accquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_STR);
            $accquery->execute();

            $accrowCount = $accquery->rowCount();
            if ($accrowCount === 0) {
              $response = new Response();
              $response->setHttpStatusCode(500);
              $response->setSuccess(false);
              $response->addMessage("loan interest account sent does not exists in the accounts chart, try again");
              $response->send();
              exit;
            }
            $loaniaccquery = $writeDB->prepare('SELECT * from accounts
              where account_sacco_id=:saccoid
              AND account_name =:sharesaccount');
              $loaniaccquery->bindParam(':sharesaccount', $jsonData->shares_account, PDO::PARAM_STR);
              $loaniaccquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_STR);
              $loaniaccquery->execute();
              $loanoaccrowCount = $loaniaccquery->rowCount();
              if ($loanoaccrowCount === 0) {
                $response = new Response();
                $response->setHttpStatusCode(500);
                $response->setSuccess(false);
                $response->addMessage("shares account sent does not exists in the accounts chart, try again");
                $response->send();
                exit;
              }

            $wccquery = $writeDB->prepare('SELECT * from accounts
              where account_sacco_id=:saccoid
              AND account_name =:withdrawcharge');
              $wccquery->bindParam(':withdrawcharge', $jsonData->withdraws_account, PDO::PARAM_STR);
              $wccquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_STR);
              $wccquery->execute();
              $wccrowCount = $wccquery->rowCount();
              if ($wccrowCount === 0) {
                $response = new Response();
                $response->setHttpStatusCode(500);
                $response->setSuccess(false);
                $response->addMessage("Withdraw charge account sent does not exists in the accounts chart, try again");
                $response->send();
                exit;
              }
              $laccquery = $writeDB->prepare('SELECT * from accounts
                where account_sacco_id=:saccoid
                AND account_name =:loansaccount');
                $laccquery->bindParam(':loansaccount', $jsonData->loans_account, PDO::PARAM_STR);
                $laccquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_STR);
                $laccquery->execute();
                $laccrowCount = $laccquery->rowCount();
                if ($laccrowCount === 0) {
                  $response = new Response();
                  $response->setHttpStatusCode(500);
                  $response->setSuccess(false);
                  $response->addMessage("Default loan account sent does not exists in the accounts chart, try again");
                  $response->send();
                  exit;
                }
                $laccquery = $writeDB->prepare('SELECT * from accounts
                  where account_sacco_id=:saccoid
                  AND account_name =:extafeesaccount');
                  $laccquery->bindParam(':extafeesaccount', $jsonData->extra_fees_account, PDO::PARAM_STR);
                  $laccquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_STR);
                  $laccquery->execute();
                  $laccrowCount = $laccquery->rowCount();
                  if ($laccrowCount === 0) {
                    $response = new Response();
                    $response->setHttpStatusCode(500);
                    $response->setSuccess(false);
                    $response->addMessage("Default loan extra charge sent does not exists in the accounts chart, try again");
                    $response->send();
                    exit;
                  }

            $query = $writeDB->prepare('SELECT * from system_default_settings
              where default_saccoid=:default_saccoid');
            $query->bindParam(':default_saccoid', $returned_saccoid, PDO::PARAM_STR);
            $query->execute();

            $rowCount = $query->rowCount();
            if ($rowCount > 0) {
              $response = new Response();
              $response->setHttpStatusCode(500);
              $response->setSuccess(false);
              $response->addMessage("default settings already set for this sacco");
              $response->send();
              exit;
            }
              $query = $writeDB->prepare('INSERT INTO system_default_settings(`currency`, `shares_account`,
                `loan_interest_account`,`extra_fees_account`, `loans_account`, `numberofloanstoguarantee`,
              `withdraws_account`, `accounting_period`, `default_saccoid`)
               values (:currency,:shares_account,:loan_interest_account,:extrafeesacc,:loansaccount,:numberofloanstoguarantee,
                :withdraws_account,:accounting_period, :saccoid)');
               $query->bindParam(':currency', $jsonData->currency, PDO::PARAM_STR);
               $query->bindParam(':loansaccount', $jsonData->loans_account, PDO::PARAM_STR);
               $query->bindParam(':extrafeesacc', $jsonData->extra_fees_account, PDO::PARAM_STR);
               $query->bindParam(':shares_account', $jsonData->shares_account, PDO::PARAM_STR);
               $query->bindParam(':loan_interest_account', $jsonData->loan_interest_account, PDO::PARAM_STR);
               $query->bindParam(':numberofloanstoguarantee', $jsonData->numberofloanstoguarantee, PDO::PARAM_STR);
               $query->bindParam(':withdraws_account', $jsonData->withdraws_account, PDO::PARAM_STR);
               $query->bindParam(':accounting_period', $jsonData->accounting_period, PDO::PARAM_STR);
              $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
              $query->execute();
              $rowCount = $query->rowCount();

              if ($rowCount === 0) {
                $response = new Response();
                $response->setHttpStatusCode(500);
                $response->setSuccess(false);
                $response->addMessage("internal server error");
                $response->send();
                exit;
              }
              $lastID = $writeDB->lastInsertId();

              $query = $writeDB->prepare('SELECT * from system_default_settings
                where system_defaultid  = :id');
              $query->bindParam(':id', $lastID, PDO::PARAM_STR);
              $query->execute();

              $rowCount = $query->rowCount();
              if ($rowCount === 0) {
                $response = new Response();
                $response->setHttpStatusCode(500);
                $response->setSuccess(false);
                $response->addMessage("failed to retrieve system default id after creation");
                $response->send();
                exit;
              }
              $systemdefaultArray=array();
              while($row = $query->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $systemdefault = array(
                  "id" => $system_defaultid ,
                  "currency" => $currency,
                  "loans_account" => $loans_account,
                  "extrafeesaccount"=>$extra_fees_account,
                  "shares_account" => $shares_account,
                  "loaninterestaccount" => $loan_interest_account,
                  "numberofloanstoguarantee" => $numberofloanstoguarantee,
                  "withdraws_account" => $withdraws_account,
                  "accounting_period" => $accounting_period,
              );
              $systemdefaultArray[]=$systemdefault;
              }

              $returnData = array();
              $returnData['rows_returned'] = $rowCount;
              $returnData['systemdefaultsettings'] = $systemdefaultArray;
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
          $response->addMessage("internal server error");
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
