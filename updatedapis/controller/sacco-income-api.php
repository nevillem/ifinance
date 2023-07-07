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

// END OF AUTH SCRIPT

  if (array_key_exists('incomeid', $_GET)) {

    $incomeid = $_GET['incomeid'];

    if($incomeid == '' || !is_numeric($incomeid)) {
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
        $incomequery = $writeDB->prepare('SELECT * from income_tb,accounts where
        incomeaccount =accounts_id AND income_sacco_id = :saccoid
          AND income_id  =:incomeid
          AND income_user_userid=:userid
          AND income_branch_id=:branch');
        $incomequery->bindParam(':incomeid', $incomeid, PDO::PARAM_STR);
        $incomequery->bindParam(':userid', $returned_id, PDO::PARAM_INT);
        $incomequery->bindParam(':branch', $returned_branch_id, PDO::PARAM_INT);
        $incomequery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_STR);
        $incomequery->execute();
        $incomesArray = array();

        $rowCount = $incomequery->rowCount();
        if($rowCount === 0) {
          // set up response for unsuccessful return
          $response = new Response();
          $response->setHttpStatusCode(404);
          $response->setSuccess(false);
          $response->addMessage("sacco income id not found");
          $response->send();
          exit;
        }
        $incomeArray = array();
        while($row = $incomequery->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $bill = array(
              "id" => $income_id,
              "income_category" => $income_category,
              "account" => $account_name,
              "amount" => $amount,
              "transdate" => $transdate,
              "modeofpayment" => $mop,
              "receivedfrom"=>$received_from,
              "notes" => $income_notes
          );
            $incomeArray[] = $bill;
          }

        $returnData = array();
        $returnData['rows_returned'] = $rowCount;
        $returnData['income'] = $incomeArray;
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
    elseif($_SERVER['REQUEST_METHOD'] === 'DELETE'){
    }
    elseif($_SERVER['REQUEST_METHOD'] === 'PATCH'){
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
  elseif(empty($_GET)){
        // get the user profile data
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
      try {

          $incomequery = $writeDB->prepare('SELECT * from income_tb,accounts where
          incomeaccount =accounts_id AND income_sacco_id = :saccoid
          AND income_user_userid=:userid
          AND income_branch_id=:branch');
          $incomequery->bindParam(':userid', $returned_id, PDO::PARAM_INT);
          $incomequery->bindParam(':branch', $returned_branch_id, PDO::PARAM_INT);
          $incomequery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_STR);
          $incomequery->execute();
          $rowsCount = $incomequery->rowCount();
          $incomesArray = array();
            while($row = $incomequery->fetch(PDO::FETCH_ASSOC)) {
              extract($row);
              $incomes = array(
                "id" => $income_id,
                "account" => $account_name,
                "amount" => $amount,
                "transdate" => $transdate,
                "modeofpayment" => $mop,
                "receivedfrom"=>$received_from,
                "notes" => $income_notes
            );
              $incomesArray[] = $incomes;
            }


        $returnData = array();
        $returnData['rows_returned'] = $rowsCount;
        $returnData['incomes'] = $incomesArray;
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
          if(!isset($jsonData->incomeaccount) || empty($jsonData->incomeaccount) || !is_numeric($jsonData->incomeaccount)
          || !isset($jsonData->amount) || empty($jsonData->amount) || !is_numeric($jsonData->amount)
          || !isset($jsonData->transdate) || empty($jsonData->transdate)
          || !isset($jsonData->mop) || empty($jsonData->mop)
          || !isset($jsonData->receivedfrom) || empty($jsonData->receivedfrom)
          || !isset($jsonData->notes) || empty($jsonData->notes)
          ):
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            (!isset($jsonData->incomeaccount)? $response->addMessage("income account field is mandatory and must be provided") : false);
            (empty($jsonData->incomeaccount)? $response->addMessage("income account field cannot be blank") : false);
            (!is_numeric($jsonData->incomeaccount)? $response->addMessage("income account field must be numeric") : false);
            (!isset($jsonData->amount)? $response->addMessage("amount field is mandatory and must be provided") : false);
            (empty($jsonData->amount)? $response->addMessage("amount field cannot be blank") : false);
            (!is_numeric($jsonData->amount)? $response->addMessage("amount field must be numeric") : false);
            (!isset($jsonData->transdate)? $response->addMessage("transaction date field is mandatory and must be provided") : false);
            (empty($jsonData->transdate)? $response->addMessage("transaction date field cannot be blank") : false);
            (!isset($jsonData->mop)? $response->addMessage("mode of payment field is mandatory and must be provided") : false);
            (empty($jsonData->mop)? $response->addMessage("mode of payment field cannot be blank") : false);
            (!isset($jsonData->receivedfrom)? $response->addMessage("received from field is mandatory and must be provided") : false);
            (empty($jsonData->receivedfrom)? $response->addMessage("received from field cannot be blank") : false);
            (!isset($jsonData->notes)? $response->addMessage("notes field is mandatory and must be provided") : false);
            (empty($jsonData->notes)? $response->addMessage("notes field cannot be blank") : false);
            $response->send();
            exit;
          endif;

          try{
            // $rowCount=0;

            // $lastID=0;
            $_account = trim($jsonData->incomeaccount);
            $_amount = trim($jsonData->amount);
            $_transdate = $jsonData->transdate;
            $_mop = $jsonData->mop;
            $_notes = $jsonData->notes;
            $_receivedfrom = $jsonData->receivedfrom;

            try {

              // check balance on the account
              $incomequery = $writeDB->prepare('SELECT * from accounts WHERE accounts_id = :id');
              $incomequery->bindParam(':id', $_account, PDO::PARAM_STR);
              $incomequery->execute();
              $rowws= $incomequery->rowCount();
              if($rowws === 0){
                // set up response for unsuccessful return
                $response = new Response();
                $response->setHttpStatusCode(400);
                $response->setSuccess(false);
                $response->addMessage("income account not found");
                $response->send();
                exit;
              }

              $incomerow = $incomequery->fetch(PDO::FETCH_ASSOC);
              $incomeb=$incomerow['opening_balance'];
              $newaccountbal=$incomeb + $_amount;

              $writeDB->beginTransaction();
              $query = $writeDB->prepare('INSERT into income_tb(
                `incomeaccount`,
                `amount`,
                `transdate`,
                `mop`, `received_from`, `income_notes`, `income_user_userid`,
                `income_branch_id`,`income_sacco_id`
              ) values (:account,:amount,:transdate,:mop,:receivedfrom,:notes,:userid,:branch, :saccoid)');
              $query->bindParam(':account', $_account, PDO::PARAM_STR);
              $query->bindParam(':amount', $_amount, PDO::PARAM_STR);
              $query->bindParam(':transdate', $_transdate, PDO::PARAM_STR);
              $query->bindParam(':mop', $_mop, PDO::PARAM_STR);
              $query->bindParam(':receivedfrom', $_receivedfrom, PDO::PARAM_STR);
              $query->bindParam(':notes', $_notes, PDO::PARAM_STR);
              $query->bindParam(':userid', $returned_id, PDO::PARAM_INT);
              $query->bindParam(':branch', $returned_branch_id, PDO::PARAM_INT);
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
                $query->bindParam(':account', $_account, PDO::PARAM_INT);
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


              $query = $writeDB->prepare('SELECT * from income_tb where  income_id = :id');
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
            $incomeArray = array();
              while($row = $query->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $income = array(
                  "id" => $income_id,
                  "amount" => $amount,
                  "transdate" => $transdate,
                  "mop" => $mop,
                  "receivedfrom" => $received_from,
              );
                $incomeArray[] = $income;
              }
              // bundle branch and rows returned into an array to return in the json data
              $returnData = array();
              $returnData['rows_returned'] = $rowCount;
              $returnData['income'] = $incomeArray;

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
