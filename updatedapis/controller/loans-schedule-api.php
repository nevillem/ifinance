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

  if($_SERVER['REQUEST_METHOD'] !== 'POST') {
      // set up response for unsuccessful request
      $response = new Response();
      $response->setHttpStatusCode(405);
      $response->setSuccess(false);
      $response->addMessage("Request method not allowed");
      $response->send();
      exit;
    }

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
      // check the raw data whether its valid json
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
      if(!isset($jsonData->amount) || !isset($jsonData->date)
          || !isset($jsonData->type) ||
          empty($jsonData->amount) || empty($jsonData->date) || empty($jsonData->type)) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        (!isset($jsonData->amount) ? $response->addMessage("amount field is mandatory and must be provided") : false);
        (!isset($jsonData->date) ? $response->addMessage("date field is mandatory and must be provided") : false);
        (!isset($jsonData->type) ? $response->addMessage("type field is mandatory and must be provided") : false);
        (empty($jsonData->date) ? $response->addMessage("date field must not be empty") : false);
        (empty($jsonData->amount) ? $response->addMessage("amount field must not be empty") : false);
        (empty($jsonData->type) ? $response->addMessage("type field must not be empty") : false);
        $response->send();
        exit;
      }
      // store data in a a dynamic variable
      $amount = $jsonData->amount;
      $date = $jsonData->date;
      $type = $jsonData->type;


      $query = $readDB->prepare('select * from loan_settings where loan_settings_id = :loanid and saccos_sacco_id = :saccoid');
      $query->bindParam(':loanid', $type, PDO::PARAM_INT);
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $query->execute();

      // perform a row count  from the queried ID
      $rowCount = $query->rowCount();
      // make a row count to check whether some data has been retrived
      if ($rowCount === 0) {
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("loan type does not exist");
        $response->send();
        exit;
      }
      // pick the picked data to the variable
      $row = $query->fetch(PDO::FETCH_ASSOC);
      // assign variable to the data
      $loan_name = $row['loan_settings_name'];
      $loan_interest = $row['loan_setting_interest'];
      $loan_peroid = $row['loan_setting_period'];
      $loan_penalty = $row['loan_setting_penalty'];
      $loan_frequency = $row['loan_setting_frequency'];
      $loan_service = $row['loan_setting_service_fee'];
      $loan_notes = $row['loan_setting_notes'];

      // perform the logic to do the schedule interms joins
      // we use switch cases to inorder to be able to unify the weeks or days or months
      switch ($loan_frequency) {
        // case 1 of daily schedule
        case 'monthly':
          // daily switch statment to calaculate the amonentization schedule of loan
          $rate = $loan_interest / 100;
          // payment rate pow method
          $payment = (($rate * $amount) / (1 - (pow(1 + $rate, - $loan_peroid))));
          // create and empty array in the carry
          $scheduleArray = array();
          // make the count
          $count = 0;
          // make a loop for the for statement without a map
          for ($i = 0; $i < $loan_peroid; $i++) {
            $due_dates[] = $date;
            $time = date('Y-m-d', strtotime('+1 month', strtotime($date)));
            $date  = $time;
            $minrate = $amount * $rate;
            $minpay =  $payment - $minrate;
            $amount -=$minpay;
            $count = $count + 1;
            // return an array to allow to display the schedule
            $amon_loan = array(
              "period" => $count,
              "minrate" => round($minrate, -1),
              "minpay" => round($minpay, -1),
              "amount" => round($amount, -1),
              "date" => $date
                   );
            $scheduleArray[] = $amon_loan;
          }
          // return the data to the schedule
          $response = new Response();
          $response->setHttpStatusCode(201);
          $response->setSuccess(false);
          $response->setData($scheduleArray);
          $response->send();
          exit;
          break;
          // case 2 we go for the weekly
          case 'weekly':
          // $scheduleArray = 'development';
          // daily switch statment to calaculate the amonentization schedule of loan
          $rate = $loan_interest / 100;
          // payment rate pow method
          $payment = (($rate * $amount) / (1 - (pow(1 + $rate, - $loan_peroid))));
          // create and empty array in the carry
          $scheduleArray = array();
          // make the count
          $count = 0;
          // make a loop for the for statement without a map
          for ($i = 0; $i < $loan_peroid; $i++) {
            $due_dates[] = $date;
            $time = date('Y-m-d', strtotime('+1 week', strtotime($date)));
            $date  = $time;
            $minrate = $amount * $rate;
            $minpay =  $payment - $minrate;
            $amount -=$minpay;
            $count = $count + 1;
            // return an array to allow to display the schedule
            $amon_loan = array(
              "period" => $count,
              "minrate" => round($minrate, -1),
              "minpay" => round($minpay, -1),
              "amount" => round($amount, -1),
              "date" => $date
                   );
            $scheduleArray[] = $amon_loan;
          }
          // return the data to the schedule
          $response = new Response();
          $response->setHttpStatusCode(201);
          $response->setSuccess(false);
          $response->setData($scheduleArray);
          $response->send();
          exit;
            break;
             // case 3 we try and embark loans to be paid onetime
            case 'onetime':
            $scheduleArray = 'development';
            // return the data to the schedule
            $response = new Response();
            $response->setHttpStatusCode(423);
            $response->setSuccess(false);
            $response->addMessage('service error, please conntact admin');
            $response->send();
            exit;
            break;
              // case 4 we try and embark loans to be paid daily
            case 'daily':
            // $scheduleArray = 'development';
            // daily switch statment to calaculate the amonentization schedule of loan
            $rate = $loan_interest / 100;
            // payment rate pow method
            $payment = (($rate * $amount) / (1 - (pow(1 + $rate, - $loan_peroid))));
            // create and empty array in the carry
            $scheduleArray = array();
            // make the count
            $count = 0;
            // make a loop for the for statement without a map
            for ($i = 0; $i < $loan_peroid; $i++) {
              $due_dates[] = $date;
              $time = date('Y-m-d', strtotime('+1 day', strtotime($date)));
              $date  = $time;
              $minrate = $amount * $rate;
              $minpay =  $payment - $minrate;
              $amount -=$minpay;
              $count = $count + 1;
              // return an array to allow to display the schedule
              $amon_loan = array(
                "period" => $count,
                "minrate" => round($minrate, -1),
                "minpay" => round($minpay, -1),
                "amount" => round($amount, -1),
                "date" => $date
                     );
              $scheduleArray[] = $amon_loan;
            }
            // return the data to the schedule
            $response = new Response();
            $response->setHttpStatusCode(201);
            $response->setSuccess(false);
            $response->setData($scheduleArray);
            $response->send();
            exit;
              break;

        default:
        // a service error has occured now
        $response = new Response();
        $response->setHttpStatusCode(423);
        $response->setSuccess(false);
        $response->addMessage("A service error has occured");
        $response->send();
        exit;
          break;
      }
