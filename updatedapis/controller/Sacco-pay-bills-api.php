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


  if (array_key_exists('paybillid', $_GET)) {

    $paybillid = $_GET['paybillid'];

    if($paybillid == '' || !is_numeric($paybillid)) {
      $response = new Response();
      $response->setHttpStatusCode(400);
      $response->setSuccess(false);
      $response->addMessage("pay bill ID cannot be blank or must be numeric");
      $response->send();
      exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
      // we pick the orders name and products under that orders
      try {
        // create db query
        $billsquery = $writeDB->prepare('SELECT * from pay_bill WHERE pay_bill_sacco_id = :saccoid
          AND pay_bill_id =:paybillid');
        $billsquery->bindParam(':paybillid', $paybillid, PDO::PARAM_STR);
        $billsquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_STR);
        $billsquery->execute();

        $rowCount = $billsquery->rowCount();
        if($rowCount === 0) {
          // set up response for unsuccessful return
          $response = new Response();
          $response->setHttpStatusCode(404);
          $response->setSuccess(false);
          $response->addMessage("sacco bill payment id not found");
          $response->send();
          exit;
        }
        $billpaymentArray = array();
          while($row = $billsquery->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $billpayment = array(
              "id" => $pay_bill_id,
              "accounttospendfrom"=>$account_to_spend_from,
              "amount" => $amount,
              "transdate" => $transdate,
              "balance"=>$balance,
              "mop"=>$mop,
              "notes" => $notes
          );
            $billpaymentArray[] = $billpayment;
          }

        $returnData = array();
        $returnData['rows_returned'] = $rowCount;
        $returnData['billpayment'] = $billpaymentArray;
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
        $response->addMessage("failed to get sacco paid bill");
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
          $billsquery = $writeDB->prepare('SELECT * from bills_tb,accounts,sacco_vendors where
          expense_account_id=accounts_id AND bill_saccoid = :saccoid  AND vendors_id=bill_vendor_id');
          $billsquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_STR);
          $billsquery->execute();

          $rowsCount = $billsquery->rowCount();
          $billsArray = array();
            while($row = $billsquery->fetch(PDO::FETCH_ASSOC)) {
              extract($row);
              $bills = array(
                "id" => $bill_id,
                "firstname" => $firstname,
                "lastname" => $lastname,
                "companyname" => $company_name,
                "contact" => $vendors_contact,
                "email" => $vendors_email,
                "address" => $vendors_address,
                "amount" => $bill_amount,
                "duedate" => $duedate,
                "transdate" => $transactiondate
            );
            $pbillsquery = $writeDB->prepare('SELECT * from pay_bill WHERE pay_bill_sacco_id = :saccoid
              AND bill_bill_id=:billid');
            $pbillsquery->bindParam(':billid', $bill_id, PDO::PARAM_STR);
            $pbillsquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_STR);
            $pbillsquery->execute();
            $billpaymentArray = array();
              while($row = $pbillsquery->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $billpayment = array(
                  "id" => $pay_bill_id,
                  "accounttospendfrom"=>$account_to_spend_from,
                  "amount" => $amount,
                  "transdate" => $transdate,
                  "balance"=>$balance,
                  "mop"=>$mop,
                  "notes" => $notes
              );
                $billpaymentArray[] = $billpayment;
              }
              $bills['billpaymets']=$billpaymentArray;
              $billsArray[] = $bills;
            }

          $returnData = array();
          $returnData['rows_returned'] = $rowsCount;
          $returnData['bills'] = $billsArray;
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
          if(!isset($jsonData->accounttospendfrom) || empty($jsonData->accounttospendfrom)|| !is_numeric($jsonData->accounttospendfrom)
          || !isset($jsonData->mop) || empty($jsonData->mop)
          || !isset($jsonData->amount) || empty($jsonData->amount) || !is_numeric($jsonData->amount)
          || !isset($jsonData->transdate) || empty($jsonData->transdate)
          || !isset($jsonData->billid) || empty($jsonData->billid) ||! is_numeric($jsonData->billid)):

            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            (!isset($jsonData->accounttospendfrom)? $response->addMessage("account to spend from field is mandatory and must be provided") : false);
            (empty($jsonData->accounttospendfrom)? $response->addMessage("account to spend from field cannot be blank") : false);
            (!is_numeric($jsonData->accounttospendfrom)? $response->addMessage("account to spend from field must be numeric") : false);
            (!isset($jsonData->mop)? $response->addMessage("mop field is mandatory and must be provided") : false);
            (empty($jsonData->mop)? $response->addMessage("mop field cannot be blank") : false);
            (!isset($jsonData->amount)? $response->addMessage("eamountt field is mandatory and must be provided") : false);
            (empty($jsonData->amount)? $response->addMessage("amount field cannot be blank") : false);
            (!is_numeric($jsonData->amount)? $response->addMessage("amount field must be numeric") : false);
            (!isset($jsonData->transdate)? $response->addMessage("transaction date field is mandatory and must be provided") : false);
            (empty($jsonData->transdate)? $response->addMessage("transaction date field cannot be blank") : false);
            (!isset($jsonData->billid)? $response->addMessage("bill id field is mandatory and must be provided") : false);
            (empty($jsonData->billid)? $response->addMessage("bill id field cannot be blank") : false);
            (!is_numeric($jsonData->billid)? $response->addMessage("bill id field must be numeric") : false);
            $response->send();
            exit;
          endif;

          try{
            // $rowCount=0;
            // $lastID=0;
            $_accounttospendfrom=$jsonData->accounttospendfrom;
            $_mop=$jsonData->mop;
            $_amount=$jsonData->amount;
            $_transdate=$jsonData->transdate;
            $_billid=$jsonData->billid;
            $_notes = $jsonData->notes;

            $query = $writeDB->prepare('SELECT * from bills_tb, sacco_vendors where bill_id = :id
              AND bill_vendor_id=vendors_id');
            $query->bindParam(':id', $_billid, PDO::PARAM_STR);
            $query->execute();
           $row = $query->fetch(PDO::FETCH_ASSOC);
           $billid = $row['bill_id'];
           $fname = $row['firstname'];
           $lname = $row['lastname'];
           $cname = $row['company_name'];
           $_billnumber = $row['bill_number'];
           $transactiondate = $row['transactiondate'];
           $duedate = $row['duedate'];
           $bamount=$row['bill_amount'];
           $status=$row['bill_status'];

           if ($status==='paid') {
             $response = new Response();
             $response->setHttpStatusCode(404);
             $response->setSuccess(false);
             $response->addMessage("This bill is already pain");
             $response->send();
             exit;
           }

           $billpayquery = $writeDB->prepare('SELECT sum(amount) as totalsum from pay_bill WHERE bill_bill_id = :id');
           $billpayquery->bindParam(':id', $_billid, PDO::PARAM_STR);
           $billpayquery->execute();
           $payrow = $billpayquery->fetch(PDO::FETCH_ASSOC);
           $paidamount=$payrow['totalsum'];
           $finaltotal=  $paidamount+ $_amount;
           // balance
           $_balance= $bamount-$finaltotal;
           if ($bamount==$finaltotal || $bamount < $finaltotal){
             $paid_status='paid';
             $billstatusquery = $writeDB->prepare('UPDATE bills_tb set bill_status = :paid
                WHERE bill_id=:id');
             $billstatusquery->bindParam(':paid', $paid_status, PDO::PARAM_INT);
             $billstatusquery->bindParam(':id', $_billid, PDO::PARAM_INT);
             $billstatusquery->execute();
             $rowbillstatus = $billstatusquery->rowCount();
             if($rowbillstatus === 0){
               // set up response for unsuccessful return
               $response = new Response();
               $response->setHttpStatusCode(400);
               $response->setSuccess(false);
               $response->addMessage("there was an issue updating the bill status");
               $response->send();
               exit;
             }
           }

          // check balance on the account
          $expaccountquery = $writeDB->prepare('SELECT * from accounts WHERE accounts_id = :id');
          $expaccountquery->bindParam(':id', $_accounttospendfrom, PDO::PARAM_STR);
          $expaccountquery->execute();
          $rowws= $expaccountquery->rowCount();
          if($rowws === 0){
            // set up response for unsuccessful return
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage("account to spend from not found");
            $response->send();
            exit;
          }
          $exprow = $expaccountquery->fetch(PDO::FETCH_ASSOC);
          $expbalance=$exprow['opening_balance'];
          $_account= $exprow['account_name'];
          if ($_amount > $expbalance) {
            // code...
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage("not enough funds on this account to complete transaction");
            $response->send();
            exit;
          }
          $newaccountbal=$expbalance-$_amount;

           try{
           $writeDB->beginTransaction();
           $query = $writeDB->prepare('INSERT into pay_bill(
             `account_to_spend_from`,
             `transdate`,
             `mop`,
             `amount`,
             `bill_bill_id`,
             `balance`,
             `notes`,
             `pay_bill_sacco_id`
           ) values (:expenseaccount,:transdate, :mop,:amount,:billid,:balance,:notes, :saccoid)');
              $query->bindParam(':expenseaccount', $_accounttospendfrom, PDO::PARAM_STR);
              $query->bindParam(':transdate', $_transdate, PDO::PARAM_STR);
              $query->bindParam(':mop', $_mop, PDO::PARAM_STR);
              $query->bindParam(':amount', $_amount, PDO::PARAM_STR);
              $query->bindParam(':billid', $_billid, PDO::PARAM_STR);
              $query->bindParam(':balance', $_balance, PDO::PARAM_STR);
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
              $query = $writeDB->prepare('UPDATE accounts set opening_balance = :amount
                where account_sacco_id = :id AND accounts_id=:account');
              $query->bindParam(':amount', $newaccountbal, PDO::PARAM_INT);
              $query->bindParam(':account', $_accounttospendfrom, PDO::PARAM_INT);
              $query->bindParam(':id', $returned_saccoid, PDO::PARAM_INT);
              $query->execute();
              $rowCount = $query->rowCount();

              if($rowCount === 0){
                // set up response for unsuccessful return
                $response = new Response();
                $response->setHttpStatusCode(400);
                $response->setSuccess(false);
                $response->addMessage("there was a problem updating balance");
                $response->send();
                exit;
              }
              //commit the change
            $writeDB->commit();
            }
            catch (PDOException $ex) {
              $writeDB->rollBack();
              $response = new Response();
              $response->setHttpStatusCode(500);
              $response->setSuccess(false);
              $response->addMessage("There was an issue making the transaction".$ex);
              $response->send();
              exit;
            }
            $query = $writeDB->prepare('SELECT pay_bill_id,amount,mop,balance,transdate from pay_bill where  pay_bill_id = :id');
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
          $pbillArray = array();
            while($row = $query->fetch(PDO::FETCH_ASSOC)) {
              extract($row);
              $pay = array(
                "id" => $pay_bill_id ,
                "billnumber" => $_billnumber,
                "payamount" => $amount,
                "modeofpayment" => $mop,
                "balance" => $balance,
                "paymenttransdate" => $transdate,
            );
              $pbillArray[] = $pay;
            }
            $billArray = array(
              "id" => $billid ,
              "firstname" => $fname,
              "lastname" => $lname,
              "transdate" => $transactiondate,
              "duedate" => $duedate,
              "billamount" => $bamount,
              "billpayment" => $pbillArray
            );
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
