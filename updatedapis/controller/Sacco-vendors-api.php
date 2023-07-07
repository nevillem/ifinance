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

  if (array_key_exists('vendorsid', $_GET)) {

    $vendorsid = $_GET['vendorsid'];

    if($vendorsid == '' || !is_numeric($vendorsid)) {
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
        // create db query
        $accountsquery = $writeDB->prepare('SELECT *
           from sacco_vendors
          WHERE vendors_id=:vendors_id AND vendors_saccoid = :saccoid');
        $accountsquery->bindParam(':vendors_id', $vendorsid, PDO::PARAM_STR);
        $accountsquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_STR);
        $accountsquery->execute();

        $rowCount = $accountsquery->rowCount();
        if($rowCount === 0) {
          // set up response for unsuccessful return
          $response = new Response();
          $response->setHttpStatusCode(404);
          $response->setSuccess(false);
          $response->addMessage("sacco vendors id not found");
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
        $returnData['rows_returned'] = $rowCount;
        $returnData['vendor'] = $vendorsArray;
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
        $response->addMessage("failed to get sacco vendor");
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

  }  elseif(empty($_GET)){
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
              $vendorsArray[] = $vendors;
            }

          $returnData = array();
          $returnData['rows_returned'] = $rowsCount;
          $returnData['vendor'] = $vendorsArray;
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
          if(strlen($jsonData->firstname) < 1 || strlen($jsonData->firstname) > 255
          || strlen($jsonData->lastname) < 1 || strlen($jsonData->lastname) > 255
          || strlen($jsonData->companyname) < 1 || strlen($jsonData->companyname) > 255
          || strlen($jsonData->email) < 1 || strlen($jsonData->email) > 255
          || strlen($jsonData->contact) < 1 || strlen($jsonData->contact)  >15 || !is_numeric($jsonData->contact)  >15
          || strlen($jsonData->address) < 1 || strlen($jsonData->address)  >255):
          // || strlen($jsonData->password) < 1 || strlen($jsonData->password) > 100):
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            (strlen($jsonData->firstname) < 1 ? $response->addMessage("firstname cannot be blank") : false);
            (strlen($jsonData->firstname) > 255 ? $response->addMessage("firstname cannot be greater than 255 characters") : false);
            (strlen($jsonData->lastname) < 1 ? $response->addMessage("lastname cannot be blank") : false);
            (strlen($jsonData->lastname) > 255 ? $response->addMessage("lastname cannot be greater than 255 characters") : false);
            (strlen($jsonData->companyname) < 1 ? $response->addMessage("company name cannot be blank") : false);
            (strlen($jsonData->companyname) > 255 ? $response->addMessage("company name cannot be greater than 255 characters") : false);
            (strlen($jsonData->email) < 1 ? $response->addMessage("Email cannot be blank") : false);
            (strlen($jsonData->email) > 255 ? $response->addMessage("Email cannot be greater than 255 characters") : false);
            (strlen($jsonData->address) < 1 ? $response->addMessage("address cannot be blank") : false);
            (strlen($jsonData->address) > 255 ? $response->addMessage("address cannot be greater than 255 characters") : false);
            (strlen($jsonData->contact) < 1 ? $response->addMessage("Sacco contact cannot be blank") : false);
            (strlen($jsonData->contact) > 15 ? $response->addMessage("Sacco contact cannot be greater than 15 characters") : false);
            (!is_numeric($jsonData->contact) ? $response->addMessage("Sacco contact must be numerical") : false);
            $response->send();
            exit;
          endif;
          if (!filter_var($jsonData->email, FILTER_VALIDATE_EMAIL)):
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            (!filter_var($jsonData->email, FILTER_VALIDATE_EMAIL) ? $response->addMessage("Invalid email address") : false);
            $response->send();
            exit;
          endif;

          try{
            // $rowCount=0;
            // $lastID=0;
            $query = $writeDB->prepare('SELECT * from sacco_vendors where company_name = :company');
            $query->bindParam(':company', $jsonData->companyname, PDO::PARAM_STR);
            $query->execute();

            $rowCount = $query->rowCount();
            if ($rowCount > 0) {
              $response = new Response();
              $response->setHttpStatusCode(400);
              $response->setSuccess(false);
              $response->addMessage("verdor with company $jsonData->companyname already exists");
              $response->send();
              exit;
            }
            $_firstname = trim($jsonData->firstname);
            $_lastname = trim($jsonData->lastname);
            $_companyname = trim($jsonData->companyname);
            $_email = trim($jsonData->email);
            $_contact = $jsonData->contact;
            $_address = $jsonData->address;

              $query = $writeDB->prepare('INSERT into sacco_vendors(
              `firstname`,
              `lastname`,
              `company_name`,
              `vendors_contact`,
               `vendors_email`,
               `vendors_address`,
                `vendors_saccoid`
              ) values (:firstname, :lastname,:companyname,:contact,:email,:address, :saccoid)');
              $query->bindParam(':firstname', $_firstname, PDO::PARAM_STR);
              $query->bindParam(':lastname', $_lastname, PDO::PARAM_STR);
              $query->bindParam(':companyname', $_companyname, PDO::PARAM_STR);
              $query->bindParam(':contact', $_contact, PDO::PARAM_STR);
              $query->bindParam(':email', $_email, PDO::PARAM_STR);
              $query->bindParam(':address', $_address, PDO::PARAM_STR);
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

              $query = $writeDB->prepare('SELECT * from sacco_vendors where vendors_id = :id');
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
            $vendorsArray = array();
              while($row = $query->fetch(PDO::FETCH_ASSOC)) {
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
                $vendorsArray[] = $vendors;
              }
              // bundle branch and rows returned into an array to return in the json data
              $returnData = array();
              $returnData['rows_returned'] = $rowCount;
              $returnData['vendor'] = $vendorsArray;

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
