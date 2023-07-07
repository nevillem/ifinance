<?php

require_once('../core/initialize.php');
require_once('Database.php');
require_once('../model/Response.php');
require_once('../model/accountgroup.php');

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

  if (array_key_exists('paymentmethodid', $_GET)) {

    $paymentmethodid = $_GET['paymentmethodid'];

    if($paymentmethodid == '' || !is_numeric($paymentmethodid)) {
      $response = new Response();
      $response->setHttpStatusCode(400);
      $response->setSuccess(false);
      $response->addMessage("payment method ID cannot be blank or must be numeric");
      $response->send();
      exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
      // we pick the orders name and products under that orders
      try {
        // create db query
        $accountsquery = $writeDB->prepare('SELECT *
           from paymentmethod
          WHERE paymentmethodid=:paymentmethodid AND payment_method_saccoid = :saccoid');
        $accountsquery->bindParam(':paymentmethodid', $paymentmethodid, PDO::PARAM_STR);
        $accountsquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_STR);
        $accountsquery->execute();

        $rowCount = $query->rowCount();
        if($rowCount === 0) {
          // set up response for unsuccessful return
          $response = new Response();
          $response->setHttpStatusCode(404);
          $response->setSuccess(false);
          $response->addMessage("payment method id not found");
          $response->send();
          exit;
        }
        $paymentmethodArray=array();
        while($row = $accountsquery->fetch(PDO::FETCH_ASSOC)) {
          extract($row);
          $payment_method = array(
            "id" => $paymentmethodid ,
            "paymentmethod" => $payment_method,
        );
        $paymentmethodArray[]=$payment_method;
        }

        $returnData = array();
        $returnData['rows_returned'] = $rowCount;
        $returnData['paymentmethod'] = $paymentmethodArray;
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
        $response->addMessage("failed to get member acounts");
        $response->send();
        exit;
      }

    } elseif($_SERVER['REQUEST_METHOD'] === 'DELETE'){
      try {
        // ADD AUTH TO QUERY
        $query = $writeDB->prepare('DELETE from paymentmethod where paymentmethodid = :paymentmethodid
        AND payment_method_saccoid = :saccoid');
        $query->bindParam(':paymentmethodid', $paymentmethodid, PDO::PARAM_STR);
        $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
        $query->execute();

        // get row count
        $rowCount = $query->rowCount();

        if($rowCount === 0):
                // set up response for unsuccessful return
                $response = new Response();
                $response->setHttpStatusCode(404);
                $response->setSuccess(false);
                $response->addMessage("payment method not found");
                $response->send();
                exit;
        else:
                $response = new Response();
                $response->setHttpStatusCode(200);
                $response->setSuccess(true);
                $response->addMessage("payment method deleted");
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
        $response->addMessage("Failed to delete payment method - Attached Info");
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

      $method = false;
      // create blank query fields string to append each field to
      $queryFields = "";

      // check if name exists in PATCH
      if(isset($jsonData->payment_method)):
        // set title field updated to true
        $method = true;
        // add name field to query field string
        $queryFields .= "payment_method  = :method, ";
      endif;
      // remove the right hand comma and trailing space
      $queryFields = rtrim($queryFields, ", ");

      // check if any branch fields supplied in JSON
      if($method === false):
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("No fields provided");
        $response->send();
        exit;
      endif;
      // ADD AUTH TO QUERY
      // create db query to get branch from database to update - use master db
      $query = $writeDB->prepare('SELECT * from paymentmethod where paymentmethodid = :paymentmethodid
      AND payment_method_saccoid = :saccoid');
      $query->bindParam(':paymentmethodid', $paymentmethodid, PDO::PARAM_STR);
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
        $response->addMessage("No payment method found to update");
        $response->send();
        exit;
      endif;
      // create the query string including any query fields
      $queryString = "UPDATE paymentmethod set ".$queryFields." where paymentmethodid  = :id";
      // prepare the query
      $query = $writeDB->prepare($queryString);
      // if name has been provided
      if($method === true):
        // bind the parameter of the new value from the object to the query (prevents SQL injection)
        $query->bindParam(':method', $jsonData->payment_method, PDO::PARAM_STR);
      endif;

      $query->bindParam(':id', $paymentmethodid, PDO::PARAM_STR);
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
      $response->addMessage("payment method has been updated");
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
      $response->addMessage("Failed to update payment methods - check your data for errors" . $ex);
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
          $accountsquery = $writeDB->prepare('SELECT * from paymentmethod
            WHERE payment_method_saccoid = :saccoid');
          $accountsquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_STR);
          $accountsquery->execute();

          $rowCount = $accountsquery->rowCount();

          $paymentmethodArray=array();
          while($row = $accountsquery->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $payment_method = array(
              "id" => $paymentmethodid ,
              "paymentmethod" => $payment_method,
          );
          $paymentmethodArray[]=$payment_method;
          }

          $returnData = array();
          $returnData['rows_returned'] = $rowCount;
          $returnData['paymentmethods'] = $paymentmethodArray;
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
          if(!isset($jsonData->payment_method)) {
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            (!isset($jsonData->payment_method) ? $response->addMessage("payment method  is mandatory and must be provided") : false);
            (empty($jsonData->payment_method) ? $response->addMessage("payment method field must not be empty") : false);
            $response->send();
            exit;
          }
          try{
            // $rowCount=0;
            // $lastID=0;
            $query = $writeDB->prepare('SELECT * from paymentmethod where payment_method = :paymentmethod AND payment_method_saccoid = :saccoid');
            $query->bindParam(':paymentmethod', $jsonData->payment_method, PDO::PARAM_STR);
            $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_STR);
            $query->execute();

            $rowCount = $query->rowCount();
            if ($rowCount > 0) {
              $response = new Response();
              $response->setHttpStatusCode(500);
              $response->setSuccess(false);
              $response->addMessage("this payment method $jsonData->payment_method already nexists");
              $response->send();
              exit;
            }

              $query = $writeDB->prepare('INSERT into paymentmethod(payment_method,payment_method_saccoid)
              values (:paymentmethod, :saccoid)');
              $query->bindParam(':paymentmethod', $jsonData->payment_method, PDO::PARAM_STR);
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

              $query = $writeDB->prepare('SELECT * from paymentmethod where paymentmethodid = :id');
              $query->bindParam(':id', $lastID, PDO::PARAM_STR);
              $query->execute();

              $rowCount = $query->rowCount();
              if ($rowCount === 0) {
                $response = new Response();
                $response->setHttpStatusCode(500);
                $response->setSuccess(false);
                $response->addMessage("failed to retrieve payment method after creation");
                $response->send();
                exit;
              }
              $paymentmethodArray=array();
              while($row = $query->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $payment_method = array(
                  "id" => $paymentmethodid ,
                  "paymentmethod" => $payment_method,
              );
              $paymentmethodArray[]=$payment_method;
              }

              $returnData = array();
              $returnData['rows_returned'] = $rowCount;
              $returnData['paymentmethods'] = $paymentmethodArray;
              // set up response for successful return
              $response = new Response();
              $response->setHttpStatusCode(201);
              $response->setSuccess(true);
              $response->setData($returnData);
              $response->send();
              exit;
            }
            catch (PDOException $ex) {
              // error_log("query error: ${ex}", 3 ,"../../app/logs/error.log");
              $response = new Response();
              $response->setHttpStatusCode(500);
              $response->setSuccess(false);
              $response->addMessage("internal server error $ex");
              $response->send();
              exit;
            }
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
