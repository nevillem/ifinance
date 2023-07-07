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


  if (array_key_exists('billid', $_GET)) {

    $billid = $_GET['billid'];

    if($billid == '' || !is_numeric($billid)) {
      $response = new Response();
      $response->setHttpStatusCode(400);
      $response->setSuccess(false);
      $response->addMessage("vendors ID cannot be blank or must be numeric");
      $response->send();
      exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
      // we pick the orders name and products under that orders
      try {
        $paid= 'paid';
        $accountsquery = $writeDB->prepare('SELECT *
           from bills_tb,accounts,sacco_vendors
          WHERE vendors_id=bill_vendor_id AND expense_account_id=accounts_id
          AND bill_id =:billid
          AND bill_status !=:paid
          AND bill_saccoid  = :saccoid');
        $accountsquery->bindParam(':paid', $paid, PDO::PARAM_STR);
        $accountsquery->bindParam(':billid', $billid, PDO::PARAM_STR);
        $accountsquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_STR);
        $accountsquery->execute();

        $rowCount = $accountsquery->rowCount();
        if($rowCount === 0) {
          // set up response for unsuccessful return
          $response = new Response();
          $response->setHttpStatusCode(404);
          $response->setSuccess(false);
          $response->addMessage("sacco bill id not found");
          $response->send();
          exit;
        }
        $billArray = array();
          while($row = $accountsquery->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $bill = array(
              "id" => $vendors_id,
              "firstname" => $firstname,
              "lastname" => $lastname,
              "companyname" => $company_name,
              "billnumber" => $bill_number,
              "account" => $account_name,
              "amount" => $bill_amount,
              "duedate" => $duedate
          );
            $billArray[] = $bill;
          }

        $returnData = array();
        $returnData['rows_returned'] = $rowCount;
        $returnData['bill'] = $billArray;
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
        $response->addMessage("failed to get sacco bill");
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
          $accountsquery = $writeDB->prepare('SELECT * from sacco_vendors WHERE vendors_saccoid = :saccoid');
          $accountsquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_STR);
          $accountsquery->execute();

          $rowsCount = $accountsquery->rowCount();
          if($rowCount === 0) {
            // set up response for unsuccessful return
            $response = new Response();
            $response->setHttpStatusCode(404);
            $response->setSuccess(false);
            $response->addMessage("vendors not found");
            $response->send();
            exit;
          }
          $vendorsArray = array();
            while($row = $accountsquery->fetch(PDO::FETCH_ASSOC)) {
              extract($row);
              $vendors = array(
                "id" => $vendors_id,
                "firstname" => $firstname,
                "lastname" => $lastname,
                "companyname" => $company_name,
                "contact" => $vendors_contact,
                "email" => $vendors_email,
                "address" => $vendors_address
            );
            $billsquery = $writeDB->prepare('SELECT * from bills_tb,accounts where
            expense_account_id=accounts_id AND bill_saccoid = :saccoid
              AND bill_vendor_id=:vendorsid');
            $billsquery->bindParam(':vendorsid', $vendors_id, PDO::PARAM_STR);
            $billsquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_STR);
            $billsquery->execute();
            $billArray = array();
              while($row = $billsquery->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $bill = array(
                  "id" => $bill_id,
                  "billnumber" => $bill_number,
                  "account" => $account_name,
                  "amount" => $bill_amount,
                  "duedate" => $duedate,
                  "transdate" => $transactiondate,
                  "notes" => $bill_notes
              );
                $billArray[] = $bill;
              }
              $vendors['bills']=$billArray;
              $vendorsArray[] = $vendors;
            }

          $returnData = array();
          $returnData['rows_returned'] = $rowsCount;
          $returnData['vendors'] = $vendorsArray;
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
          // check to make sure that sacco name email and password are not empty and less than 255 long
          if(!isset($jsonData->vendor) || empty($jsonData->vendor)|| !is_numeric($jsonData->vendor)
          || !isset($jsonData->expenseaccount) || empty($jsonData->expenseaccount) || !is_numeric($jsonData->expenseaccount)
          || !isset($jsonData->amount) || empty($jsonData->amount) || !is_numeric($jsonData->amount)
          || !isset($jsonData->transdate) || empty($jsonData->transdate)
          || !isset($jsonData->duedate) || empty($jsonData->duedate)
          || !isset($jsonData->billnumber) || empty($jsonData->billnumber)):
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            (!isset($jsonData->vendor)? $response->addMessage("vendor field is mandatory and must be provided") : false);
            (empty($jsonData->vendor)? $response->addMessage("vendor field cannot be blank") : false);
            (!is_numeric($jsonData->vendor)? $response->addMessage("expense account field must be numeric") : false);
            (!isset($jsonData->expenseaccount)? $response->addMessage("expense account field is mandatory and must be provided") : false);
            (empty($jsonData->expenseaccount)? $response->addMessage("expense account field cannot be blank") : false);
            (!is_numeric($jsonData->expenseaccount)? $response->addMessage("expense account field must be numeric") : false);
            (!isset($jsonData->amount)? $response->addMessage("eamountt field is mandatory and must be provided") : false);
            (empty($jsonData->amount)? $response->addMessage("amount field cannot be blank") : false);
            (!is_numeric($jsonData->amount)? $response->addMessage("amount field must be numeric") : false);
            (!isset($jsonData->transdate)? $response->addMessage("transaction date field is mandatory and must be provided") : false);
            (empty($jsonData->transdate)? $response->addMessage("transaction date field cannot be blank") : false);
            (!isset($jsonData->duedate)? $response->addMessage("due date field is mandatory and must be provided") : false);
            (empty($jsonData->duedate)? $response->addMessage("due date field cannot be blank") : false);
            (!isset($jsonData->billnumber)? $response->addMessage("bill number field is mandatory and must be provided") : false);
            (empty($jsonData->billnumber)? $response->addMessage("bill number field cannot be blank") : false);
            $response->send();
            exit;
          endif;

          try{
            // $rowCount=0;

            // $lastID=0;
            $_vendor = trim($jsonData->vendor);
            $_account = trim($jsonData->expenseaccount);
            $_amount = trim($jsonData->amount);
            $_transdate = trim($jsonData->transdate);
            $_duedate = $jsonData->duedate;
            $_notes = $jsonData->notes;
            $_billnumber = $jsonData->billnumber;
            $query = $readDB->prepare('SELECT * from bills_tb where bill_number = :billnumber');
            $query->bindParam(':billnumber', $_billnumber, PDO::PARAM_INT);
            $query->execute();
            $rowCount = $query->rowCount();

            if ($rowCount !== 0) {
              $response = new Response();
              $response->setHttpStatusCode(409);
              $response->setSuccess(false);
              $response->addMessage("bill with this number already exists");
              $response->send();
              exit;
            }

              $query = $writeDB->prepare('INSERT into bills_tb(
                `bill_number`,
              `bill_vendor_id`,
               `expense_account_id`,
                `bill_amount`,
               `duedate`,
               `transactiondate`,
                `bill_notes`,
                 `bill_saccoid`
              ) values (:billnumber,:vendor, :account,:amount,:transdate,:duedate,:notes, :saccoid)');
              $query->bindParam(':billnumber', $_billnumber, PDO::PARAM_STR);
              $query->bindParam(':vendor', $_vendor, PDO::PARAM_STR);
              $query->bindParam(':account', $_account, PDO::PARAM_STR);
              $query->bindParam(':amount', $_amount, PDO::PARAM_STR);
              $query->bindParam(':transdate', $_transdate, PDO::PARAM_STR);
              $query->bindParam(':duedate', $_duedate, PDO::PARAM_STR);
              $query->bindParam(':notes', $_notes, PDO::PARAM_STR);
              $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
              $query->execute();
              $lastID = $writeDB->lastInsertId();
              $rowCount = $query->rowCount();
              if ($rowCount === 0) {
                $response = new Response();
                $response->setHttpStatusCode(500);
                $response->setSuccess(false);
                $response->addMessage("internal server error");
                $response->send();
                exit;
              }

              $query = $writeDB->prepare('SELECT * from bills_tb,accounts,sacco_vendors where
                vendors_id=bill_vendor_id AND expense_account_id=accounts_id AND  bill_id = :id');
              $query->bindParam(':id', $lastID, PDO::PARAM_STR);
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
            $billArray = array();
              while($row = $query->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $bill = array(
                  "id" => $vendors_id,
                  "firstname" => $firstname,
                  "lastname" => $lastname,
                  "companyname" => $company_name,
                  "account" => $account_name,
                  "billnumber" => $bill_number,
                  "amount" => $bill_amount,
                  "duedate" => $duedate
              );
                $billArray[] = $bill;
              }
              // bundle branch and rows returned into an array to return in the json data
              $returnData = array();
              $returnData['rows_returned'] = $rowCount;
              $returnData['bill'] = $billArray;

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
