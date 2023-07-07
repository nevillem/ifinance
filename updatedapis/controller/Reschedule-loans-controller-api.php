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

// END OF AUTH SCRIPT

// within this if/elseif statement, it is important to get the correct order (if query string GET param is used in multiple routes)

// check if taskid is in the url e.g. /tasks/1
if (array_key_exists("rescheduledid",$_GET)) {
  // get task id from query string
  $loanappid = $_GET['rescheduledid'];
  //check to see if task id in query string is not empty and is number, if not return json error
  if($loanappid == '' || !is_numeric($loanappid)) {
    $response = new Response();
    $response->setHttpStatusCode(400);
    $response->setSuccess(false);
    $response->addMessage("loan rescheduled id cannot be blank or must be numeric");
    $response->send();
    exit;
  }

  // if request is a GET, e.g. get transaction
  if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $query = $readDB->prepare('SELECT * from loan_applications,loan_product_settings,loans_disbursed,
      members, users WHERE loan_applications.members_member_id = members.member_id
       AND NOT EXISTS(SELECT loan_payment.loan_payment_disbursedid FROM loan_payment
         WHERE loan_payment.loan_payment_disbursedid =loans_disbursed.loan_id)
       AND loan_applications.loan_product_product_id=loan_product_settings.sloan_product_id
       AND loan_applications.users_user_id = users.user_id
       AND loans_disbursed.loan_applications_loan_app_id =loan_applications.loan_app_id
       AND loans_disbursed.loan_id=:loandisbursedid
       AND loanapplicationtype ="individual"
       AND loan_applications.saccos_sacco_id = :saccoid');
    $query->bindParam(':loandisbursedid', $loanappid, PDO::PARAM_INT);
    $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
    $query->execute();

    // get row count
    $rowCount = $query->rowCount();

    if($rowCount === 0) {
      // set up response for unsuccessful return
      $response = new Response();
      $response->setHttpStatusCode(404);
      $response->setSuccess(false);
      $response->addMessage("loan application not found for this member");
      $response->send();
      exit;
    }
    // create array to store returned task
    $transactionArray = array();

   $row = $query->fetch(PDO::FETCH_ASSOC);
      extract($row);
      $loan_app = array(
        "id" => $loan_app_id,
        "account" => $members_account_number,
        "firstname" => $member_fname,
        "lastname" => $member_lname,
        "loanproduct" => $name_of_loan_product,
        "amountoffered" => number_format($offered_amount),
        "applicationnumber" => $loan_app_number,
        "status" => $loan_app_status,
        "interest_rate"=>$interest_rate,
        "loanratetype"=>$loan_rate_type,
        "loan_processing_fees"=>$loan_processing_fees,
        "loanapplicationdate" => $loan_app_timestamp
      );

     $scheduleArray = array();

     switch ($amornitization_interval) {
       case 'annually':
       if ($loan_rate_type ==='reducing balance') {
         $rate = $interest_rate/100;
         $amount= $offered_amount;
         //loan period in days
        $grace_period=$grace_period;
         // loan period in months
         $loantenure= $tenure_period/12;
         // $payment = ($rate /(1-(pow((1+$rate),-($loantenure)))))*$amount;
         // (Rn * A) / [1 - (1 +Rn)power-N ]
         $payment = $amount * $rate * (pow(1 + $rate, $loantenure) / (pow(1 + $rate, $loantenure) - 1));
         // $payment = (($rate * $amount) / (1 - (pow(1 + $rate, - $loantenure))));
         // echo $payment * 12;
         $datetime=date ( 'Y-m-d' , strtotime ($loan_app_timestamp . ' + '.$grace_period.' days' ));
         $time = new DateTime($datetime);
         $totalint=0;
         $totalloanamt=0;
         for ($i=1; $i <= $loantenure; $i++) {
           $input=  str_pad($i, 4, "0", STR_PAD_LEFT);
           $time->modify('+1 year');
            $date = $time->format('Y-m-d');
           // Payment1 / pow((1+Rn),(1+N-n))
           // $pp = $payment / (round(pow((1+$rate), (1+$loantenure-$i)), 7));
           $pp = $payment / (pow((1+$rate), (1+$loantenure-$i)));
           // To calculate the Interest paid:
           // Payment1 - PP1
           $int = $payment - $pp;
           // To calculate the LOB
           // (INT1/ Rn) – PP1
           $lob = ($int / $rate) - $pp;
           //total principal paid + interest
           $totalloanamt += $pp +$int;
           $totalint +=$int;
           $schedule= array("installmentno"=>$input,
           "paymentdate"=>$date,"totalprincipalamtpaid"=>number_format($payment),
           "principalamountpaid"=>number_format($pp),
            "principalinterestpaid"=>number_format($int), "loan_balance"=>number_format(abs($lob)));
           $scheduleArray[]=$schedule;
         }
       }
       elseif ($loan_rate_type ==='straight line') {
         $rate = $interest_rate/100;
         $amount= $offered_amount;
         //loan period in days
         $grace_period=$grace_period;
         // loan period in months
         $loantenure= $tenure_period/12;
         // $payment = ($rate /(1-(pow((1+$rate),-($loantenure)))))*$amount;
         // (Rn * A) / [1 - (1 +Rn)power-N ]
         // $payment = $amount * $rate * (pow(1 + $rate, $loantenure) / (pow(1 + $rate, $loantenure) - 1));
         $minrate = ceil(($amount*$rate)/10) *10;
         $pp = ceil(($amount/$loantenure)/10) *10;
         // $payment = (($rate * $amount) / (1 - (pow(1 + $rate, - $loantenure))));
         // echo $payment * 12;
         $datetime=date ( 'Y-m-d' , strtotime ($loan_app_timestamp . ' + '.$grace_period.' days' ));
         $time = new DateTime($datetime);
         $totalint=0;
         $totalloanamt=0;
         for ($i=1; $i <= $loantenure; $i++) {
           $input=  str_pad($i, 4, "0", STR_PAD_LEFT);
           $time->modify('+1 year');
           $date = $time->format('Y-m-d');
           $payment= $minrate + $pp;
           $amount =$amount-$pp;
           $totalloanamt += $payment;
           $totalint +=$minrate;
           $schedule= array("installmentno"=>$input,
           "paymentdate"=>$date,"totalprincipalamtpaid"=>number_format($payment),
           "principalamountpaid"=>number_format($pp),
            "principalinterestpaid"=>number_format($minrate),
            "loan_balance"=>number_format($amount >0? $amount:0));
           $scheduleArray[]=$schedule;
         }
       }
       break;
       case 'monthly':
       if ($loan_rate_type ==='reducing balance') {
         $rate = $interest_rate/1200;
         $amount= $offered_amount;
         //loan period in days
         $grace_period=$grace_period;
         // loan period in months
         $loantenure= $tenure_period;
         // $payment = ($rate /(1-(pow((1+$rate),-($loantenure)))))*$amount;
         // (Rn * A) / [1 - (1 +Rn)power-N ]
         $payment = $amount * $rate * (pow(1 + $rate, $loantenure) / (pow(1 + $rate, $loantenure) - 1));
         // $payment = (($rate * $amount) / (1 - (pow(1 + $rate, - $loantenure))));
         // echo $payment * 12;
         $datetime=date ( 'Y-m-d' , strtotime ($loan_app_timestamp .' + '.$grace_period.' days' ));
         $time = new DateTime($datetime);
         $totalint=0;
         $totalloanamt=0;
         for ($i=1; $i <= $loantenure; $i++) {
           $input=  str_pad($i, 4, "0", STR_PAD_LEFT);
           $time->modify('+1 months');
            $date = $time->format('Y-m-d');
           // Payment1 / pow((1+Rn),(1+N-n))
           // $pp = $payment / (round(pow((1+$rate), (1+$loantenure-$i)), 7));
           $pp = $payment / (pow((1+$rate), (1+$loantenure-$i)));
           // To calculate the Interest paid:
           // Payment1 - PP1
           $int = $payment - $pp;
           // To calculate the LOB
           // (INT1/ Rn) – PP1
           $lob = ($int / $rate) - $pp;
           //total principal paid + interest
           $totalloanamt += $pp +$int;
           $totalint +=$int;
           $schedule= array("installmentno"=>$input,
           "paymentdate"=>$date,"totalprincipalamtpaid"=>number_format($payment),"principalamountpaid"=>number_format($pp),
            "principalinterestpaid"=>number_format($int), "loan_balance"=>number_format(abs($lob)));
           $scheduleArray[]=$schedule;
         }
       }
       elseif ($loan_rate_type ==='straight line') {
         $rate = $interest_rate/1200;
         $amount= $offered_amount;
         //loan period in days
         $grace_period=$grace_period;
         // loan period in months
         $loantenure= $tenure_period;
         $minrate = ceil(($amount*$rate)/10) *10;
         $pp = ceil(($amount/$loantenure)/10) *10;
         $datetime=date ( 'Y-m-d' , strtotime ($loan_app_timestamp . ' + '.$grace_period.' days' ));
         $time = new DateTime($datetime);
         $totalint=0;
         $totalloanamt=0;
         for ($i=1; $i <= $loantenure; $i++) {
           $input=  str_pad($i, 4, "0", STR_PAD_LEFT);
           $time->modify('+1 months');
            $date = $time->format('Y-m-d');
            $payment= $minrate + $pp;
            $amount =$amount-$pp;
            $totalloanamt += $payment;
            $totalint +=$minrate;
            $schedule= array("installmentno"=>$input,
            "paymentdate"=>$date,"totalprincipalamtpaid"=>number_format($payment),
            "principalamountpaid"=>number_format($pp),
             "principalinterestpaid"=>number_format($minrate),
             "loan_balance"=>number_format($amount >0? $amount:0));
              $scheduleArray[]=$schedule;
         }
       }
       break;
       case 'weekly';
       if ($loan_rate_type ==='reducing balance') {
        $rate = $interest_rate/5200;
         $amount= $offered_amount;
         //loan period in days
         $grace_period=$grace_period;
         // loan period in months
         $loantenure= $tenure_period * 4;
         // $payment = ($rate /(1-(pow((1+$rate),-($loantenure)))))*$amount;
         // (Rn * A) / [1 - (1 +Rn)power-N ]
         $payment = $amount * $rate * (pow(1 + $rate, $loantenure) / (pow(1 + $rate, $loantenure) - 1));
         $datetime=date ( 'Y-m-d' , strtotime ($loan_app_timestamp . ' + '.$grace_period.' days' ));
         $time = new DateTime($datetime);
         $totalint=0;
         $totalloanamt=0;
         for ($i=1; $i <= $loantenure; $i++) {
           $input=  str_pad($i, 4, "0", STR_PAD_LEFT);
           $time->modify('+7 days');
            $date = $time->format('Y-m-d');
           // Payment1 / pow((1+Rn),(1+N-n))
           // $pp = $payment / (round(pow((1+$rate), (1+$loantenure-$i)), 7));
           $pp = $payment / (pow((1+$rate), (1+$loantenure-$i)));
           // To calculate the Interest paid:
           // Payment1 - PP1
           $int = $payment - $pp;
           // To calculate the LOB
           // (INT1/ Rn) – PP1
           $lob = ($int / $rate) - $pp;
           //total principal paid + interest
           $totalloanamt += $pp +$int;
           $totalint +=$int;
           $schedule= array("installmentno"=>$input,
           "paymentdate"=>$date,"totalprincipalamtpaid"=>number_format($payment),
           "principalamountpaid"=>number_format($pp),
            "principalinterestpaid"=>number_format($int), "loan_balance"=>number_format(abs($lob)));
           $scheduleArray[]=$schedule;
         }
       }
       elseif ($loan_rate_type ==='straight line') {
         $rate = $interest_rate/5200;
          $amount= $offered_amount;
          //loan period in days
          $grace_period=$grace_period;
          // loan period in months
          $loantenure= $tenure_period * 4;
          $minrate = ceil(($amount*$rate)/10) *10;
          $pp = ceil(($amount/$loantenure)/10) *10;
         // $payment = (($rate * $amount) / (1 - (pow(1 + $rate, - $loantenure))));
         // echo $payment * 12;
         $datetime=date ( 'Y-m-d' , strtotime ($loan_app_timestamp . ' + '.$grace_period.' days' ));
         $time = new DateTime($datetime);
         $totalint=0;
         $totalloanamt=0;
         for ($i=1; $i <= $loantenure; $i++) {
           $input=  str_pad($i, 4, "0", STR_PAD_LEFT);
           $time->modify('+1 months');
            $date = $time->format('Y-m-d');
            $payment= $minrate + $pp;
            $amount =$amount-$pp;
            $totalloanamt += $payment;
            $totalint +=$minrate;
            $schedule= array("installmentno"=>$input,
            "paymentdate"=>$date,"totalprincipalamtpaid"=>number_format($payment),
            "principalamountpaid"=>number_format($pp),
             "principalinterestpaid"=>number_format($minrate),
             "loan_balance"=>number_format($amount >0? $amount:0));
              $scheduleArray[]=$schedule;
         }
       }
       break;
       case 'daily';
       if ($loan_rate_type ==='reducing balance') {
        $rate = $interest_rate/36500;
         $amount= $offered_amount;
         //loan period in days
         $grace_period=$grace_period;
         // loan period in months
         $loantenure= $tenure_period * 28;
         // $payment = ($rate /(1-(pow((1+$rate),-($loantenure)))))*$amount;
         // (Rn * A) / [1 - (1 +Rn)power-N ]
         $payment = $amount * $rate * (pow(1 + $rate, $loantenure) / (pow(1 + $rate, $loantenure) - 1));
         // $payment = (($rate * $amount) / (1 - (pow(1 + $rate, - $loantenure))));
         // echo $payment * 12;
         $datetime=date ( 'Y-m-d' , strtotime ($loan_app_timestamp . ' + '.$grace_period.' days' ));
         $time = new DateTime($datetime);
         $totalint=0;
         $totalloanamt=0;
         for ($i=1; $i <= $loantenure; $i++) {
           $input=  str_pad($i, 4, "0", STR_PAD_LEFT);
           $time->modify('+1 day');
            $date = $time->format('Y-m-d');
           // Payment1 / pow((1+Rn),(1+N-n))
           // $pp = $payment / (round(pow((1+$rate), (1+$loantenure-$i)), 7));
           $pp = $payment / (pow((1+$rate), (1+$loantenure-$i)));
           // To calculate the Interest paid:
           // Payment1 - PP1
           $int = $payment - $pp;
           // To calculate the LOB
           // (INT1/ Rn) – PP1
           $lob = ($int / $rate) - $pp;
           //total principal paid + interest
           $totalloanamt += $pp +$int;
           $totalint +=$int;
           $schedule= array("installmentno"=>$input,
           "paymentdate"=>$date,"totalprincipalamtpaid"=>number_format($payment),
           "principalamountpaid"=>number_format($pp),
            "principalinterestpaid"=>number_format($int), "loan_balance"=>number_format(abs($lob)));
           $scheduleArray[]=$schedule;
         }
       }
       elseif ($loan_rate_type ==='straight line') {
         $rate = $interest_rate/36500;
          $amount= $offered_amount;
          //loan period in days
          $grace_period=$grace_period;
          // loan period in months
          $loantenure= $tenure_period * 28;
         // $payment = ($rate /(1-(pow((1+$rate),-($loantenure)))))*$amount;
         // (Rn * A) / [1 - (1 +Rn)power-N ]
         $minrate = ceil(($amount*$rate)/10) *10;
         $pp = ceil(($amount/$loantenure)/10) *10;
         $datetime=date ( 'Y-m-d' , strtotime ($loan_app_timestamp . ' + '.$grace_period.' days' ));
         $time = new DateTime($datetime);
         $totalint=0;
         $totalloanamt=0;
         for ($i=1; $i <= $loantenure; $i++) {
           $input=  str_pad($i, 4, "0", STR_PAD_LEFT);
           $time->modify('+1 day');
            $date = $time->format('Y-m-d');
            $payment= $minrate + $pp;
            $amount =$amount-$pp;
            $totalloanamt += $payment;
            $totalint +=$minrate;
            $schedule= array("installmentno"=>$input,
            "paymentdate"=>$date,"totalprincipalamtpaid"=>number_format($payment),
            "principalamountpaid"=>number_format($pp),
             "principalinterestpaid"=>number_format($minrate),
             "loan_balance"=>number_format($amount >0? $amount:0));
              $scheduleArray[]=$schedule;
         }
       }
       break;
     }
     $loan_app['totalloanamt']= number_format($totalloanamt);
     $loan_app['totalinterest']= number_format($totalint);
     $loan_app['loanpaymentschedule']= $scheduleArray;
     $transactionArray[] = $loan_app;

     // bundle rows returned into an array to return in the json data
     $returnData = array();
     $returnData['rows_returned'] = $rowCount;
     $returnData['loanapplication'] = $transactionArray;

     // set up response for successful return
     $response = new Response();
     $response->setHttpStatusCode(200);
     $response->setSuccess(true);
     $response->toCache(true);
     $response->setData($returnData);
     $response->send();
     exit;
   }

  // if any other request method apart from GET, PATCH, DELETE is used then return 405 method not allowed
  else {
    $response = new Response();
    $response->setHttpStatusCode(405);
    $response->setSuccess(false);
    $response->addMessage("Request method not allowed");
    $response->send();
    exit;
  }
}
// handle getting all tasks or creating a new one
elseif(empty($_GET)) {

  // if request is a GET e.g. get members
  if($_SERVER['REQUEST_METHOD'] === 'GET') {

    // attempt to query the database
    try {
      // ADD AUTH TO QUERY
      $query = $readDB->prepare('SELECT * FROM  members
        where members.member_id IN (SELECT loan_applications.members_member_id FROM loan_applications)
        AND members.saccos_sacco_id = :saccoid
        AND member_type="individual"');
      // $query->bindParam(':userid', $returned_id, PDO::PARAM_INT);
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $query->execute();
      // get row count
      $rowCount = $query->rowCount();
      // create array to store returned task
      $membersArray = array();
      while($row = $query->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $members = array(
          "id" => $member_id,
          "account" => $members_account_number,
          "firstname" => $member_fname,
          "lastname" => $member_lname,
        );
        $loanquery = $readDB->prepare('SELECT * from loan_applications,loan_product_settings, loans_disbursed
          where loan_applications.members_member_id = :memberid
           AND loan_applications.loan_product_product_id=loan_product_settings.sloan_product_id
           AND loans_disbursed.loan_applications_loan_app_id =loan_applications.loan_app_id
           AND loan_applications.users_user_id = :userid
           AND loans_disbursed.loan_status="rescheduled"
            AND loanapplicationtype ="individual"
            and loan_applications.saccos_sacco_id = :saccoid order by loan_app_id DESC');
        $loanquery->bindParam(':memberid', $member_id, PDO::PARAM_INT);
        $loanquery->bindParam(':userid', $returned_id, PDO::PARAM_INT);
        $loanquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
        $loanquery->execute();

        // create array to store returned task
        $transactionArray = array();
        while($row = $loanquery->fetch(PDO::FETCH_ASSOC)) {
          extract($row);
          $loan_app = array(
            "id" => $loan_app_id,
            "loanid" => $loan_app_number,
            "loanproduct" => $name_of_loan_product,
            "amountappliedfor" => $loan_app_amount,
            "amountoffered" => $offered_amount,
            "amornitization"=>$amornitization_interval,
            "graceperiod"=>$grace_period." days",
            "loantenure"=>$tenure_period." months",
            "datedisbursed"=>$loan_disbursed_date,
            "totalloanpay" => $loan_balance,
            "totalinterest" => $total_interest,
            "loantype" => $loan_type,
            "status" => $loan_app_status,
            "timestamp" => $loan_app_date,
            "date"=> $loan_app_date
          );
         $transactionArray[] = $loan_app;
        }
       $members['loanapplications'] = $transactionArray;
       $membersArray[] = $members;
      }

      // bundle rows returned into an array to return in the json data
      $returnData = array();
      $returnData['rows_returned'] = $rowCount;
      $returnData['loansdisbursed'] = $membersArray;

      // set up response for successful return
      $response = new Response();
      $response->setHttpStatusCode(200);
      $response->setSuccess(true);
      $response->toCache(true);
      $response->setData($returnData);
      $response->send();
      exit;
    }
    catch(PDOException $ex) {
      error_log("Database Query Error: ".$ex, 0);
      $response = new Response();
      $response->setHttpStatusCode(500);
      $response->setSuccess(false);
      $response->addMessage("Failed to get transaction".$ex);
      $response->send();
      exit;
    }
  }
  // else if request is a POST e.g. create member
  elseif($_SERVER['REQUEST_METHOD'] === 'POST') {

    // create task
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

      // get POST request body as the POSTed data will be JSON format
      $rawPostData = file_get_contents('php://input');

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
      if(!isset($jsonData->loandisbursedid) || !is_numeric($jsonData->loandisbursedid)||empty($jsonData->loandisbursedid)
      || !isset($jsonData->numberalofinstallments)||!is_numeric($jsonData->numberalofinstallments)|| empty($jsonData->numberalofinstallments)
       ) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        (!isset($jsonData->loandisbursedid) ? $response->addMessage("loan disbursed field is mandatory and must be provided") : false);
        (empty($jsonData->loandisbursedid) ? $response->addMessage("loan disbursed field must not be empty") : false);
        (!is_numeric($jsonData->loandisbursedid) ? $response->addMessage("loan disbursed id field must be numeric") : false);
        (!isset($jsonData->numberalofinstallments) ? $response->addMessage("number of installments field is mandatory and must be provided") : false);
        (empty($jsonData->numberalofinstallments) ? $response->addMessage("number of installments field must not be empty") : false);
        (!is_numeric($jsonData->numberalofinstallments) ? $response->addMessage("number of installments field value must be numeric") : false);
        $response->send();
        exit;
      }

      $_applicationid = (int)$jsonData->loandisbursedid;
      $noinstallments = $jsonData->numberalofinstallments;
      $transactionID = getGUIDnoHash();
      $transID = getGUIDnoHash();
        $loandisbursedcheckQuery = $readDB->prepare('SELECT * FROM loans_disbursed
         where loan_id=:disbursedID');
        // $query->bindParam(':amt', $amount, PDO::PARAM_INT);
        $loandisbursedcheckQuery->bindParam(':disbursedID', $_applicationid, PDO::PARAM_INT);
        $loandisbursedcheckQuery->execute();
        $rowCountw = $loandisbursedcheckQuery->rowCount();

        if ($rowCountw ==0) {
          $response = new Response();
          $response->setHttpStatusCode(404);
          $response->setSuccess(false);
          $response->addMessage("Loan not yet disbursed");
          $response->send();
          exit;
      }

      $disbursedrow = $loandisbursedcheckQuery->fetch(PDO::FETCH_ASSOC);
      $_amount= $disbursedrow['amount_disbursed'];
      $datedisbursed= $disbursedrow['loan_disbursed_date'];

      $checkloannotpaid=$readDB->prepare('SELECT DISTINCT(loan_id) FROM loans_disbursed,loan_payment_schedule
       WHERE  NOT EXISTS (SELECT loan_payment.loan_payment_disbursedid FROM loan_payment WHERE
       loan_payment.loan_payment_disbursedid=loans_disbursed.loan_id)
       AND loan_payment_schedule.loan_active_loan_id=loans_disbursed.loan_id
       AND loan_id=:id');
      // $query->bindParam(':amt', $amount, PDO::PARAM_INT);
      $checkloannotpaid->bindParam(':id', $_applicationid, PDO::PARAM_INT);
      $checkloannotpaid->execute();
      $checkloannotpaidrowCountw = $checkloannotpaid->rowCount();

      if ($checkloannotpaidrowCountw ==0) {
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("this loan cannot be rescheduled");
        $response->send();
        exit;
      }
      $loandisbursedrow = $checkloannotpaid->fetch(PDO::FETCH_ASSOC);
      $disbursedID= $loandisbursedrow['loan_id'];

      $checkappQuery = $readDB->prepare('SELECT * from loan_applications,loan_product_settings,
         members, users, loans_disbursed
         WHERE loan_applications.members_member_id = members.member_id
         AND loan_applications.loan_product_product_id=loan_product_settings.sloan_product_id
         AND loan_applications.loan_app_id=loans_disbursed.loan_applications_loan_app_id
         AND loan_applications.users_user_id = users.user_id
         AND loan_id = :id
         AND loanapplicationtype ="individual"
         AND loan_applications.saccos_sacco_id = :saccoid');
      // $query->bindParam(':amt', $amount, PDO::PARAM_INT);
      $checkappQuery->bindParam(':id', $_applicationid, PDO::PARAM_INT);
      $checkappQuery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $checkappQuery->execute();
      $approw = $checkappQuery->fetch(PDO::FETCH_ASSOC);
      $loanapplication = $approw['loan_applications_loan_app_id'];
      $loan_status = $approw['loan_app_status'];
      $amortization= $approw['amornitization_interval'];
      $loanratetype=$approw['loan_rate_type'];
      $_memberaccountid= $approw['members_member_id'];
      $rate = $approw['interest_rate'];
      $graceperiod= $approw['grace_period'];
      $loantenure= $noinstallments;
      $totalint=0;
      $totalloanamt=0;
      $effectiveDate1 = strtotime("+ ".$graceperiod." days", strtotime($datedisbursed));
      $finaldatedisbursed = strftime ( '%Y-%m-%d' , $effectiveDate1);

        // select account to receive funds
        $member_toquery = $readDB->prepare('SELECT * from members, saccos
          WHERE members.saccos_sacco_id = saccos.sacco_id
        AND member_id =:member');
        $member_toquery->bindParam(':member', $_memberaccountid, PDO::PARAM_INT);
        $member_toquery->execute();
        $rowCount = $member_toquery->rowCount();
        if ($rowCount === 0) {
          $response = new Response();
          $response->setHttpStatusCode(404);
          $response->setSuccess(false);
          $response->addMessage("account not found");
          $response->send();
          exit;
        }
        $row = $member_toquery->fetch(PDO::FETCH_ASSOC);
        $firstname = $row['member_fname'];
        $lastname = $row['member_lname'];
        $accountContact = $row['member_contact'];
        // $AccountBalance = (int) $row['total_deposit'];
        // $memberID = (int) $row['member_accounts_member_id'];
        $AccountNumber = $row['members_account_number'];
        $saccoName = $row['sacco_short_name'];
        $saccoEmail = $row['sacco_email'];
        $sacco_sms_status = $row['sacco_sms_status'];
        $sacco_email_status = $row['sacco_email_status'];
        // make the new account balance
        // $newAccountBalance = $AccountBalance + $_amount;
      try {
      $writeDB->beginTransaction();
      $updateloanappquery = $writeDB->prepare('UPDATE loan_applications set tenure_period = :noinstallments
         WHERE loan_app_id=:id AND saccos_sacco_id =:saccoid');
      $updateloanappquery->bindParam(':noinstallments', $noinstallments, PDO::PARAM_INT);
      $updateloanappquery->bindParam(':id', $loanapplication, PDO::PARAM_INT);
      $updateloanappquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $updateloanappquery->execute();
      $rowUpdateLoanAppCount = $updateloanappquery->rowCount();
      if($rowUpdateLoanAppCount === 0){
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("you cannot update loan installments with same installments");
        $response->send();
        exit;
      }
      $_open ="rescheduled";
      $updateloandisbquery = $writeDB->prepare('UPDATE loans_disbursed set loan_status = :status
         WHERE loan_id=:id');
      $updateloandisbquery->bindParam(':status', $_open, PDO::PARAM_INT);
      $updateloandisbquery->bindParam(':id', $_applicationid, PDO::PARAM_INT);
      $updateloandisbquery->execute();
      $rowUpdateLoanDisCount = $updateloandisbquery->rowCount();
      if($rowUpdateLoanDisCount === 0){
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("this loan cannot be rescheduled again");
        $response->send();
        exit;
      }
      $updateloanschedulebquery = $writeDB->prepare('UPDATE loan_payment_schedule
        set loan_payment_status = :status
         WHERE loan_active_loan_id=:id');
      $updateloanschedulebquery->bindParam(':status', $_open, PDO::PARAM_INT);
      $updateloanschedulebquery->bindParam(':id', $disbursedID, PDO::PARAM_INT);
      $updateloanschedulebquery->execute();
      $rowUpdateLoanscheduleCount = $updateloanschedulebquery->rowCount();
      if($rowUpdateLoanscheduleCount === 0){
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("there was an issue updating loan payment reschedule");
        $response->send();
        exit;
      }
       switch ($amortization) {
         case 'annually':
         if ($loanratetype ==='reducing balance') {
         $annualrate = $rate/100;
         $years =$loantenure/12;
         $payment = $_amount * $annualrate * (pow(1 + $annualrate, $years) / (pow(1 + $annualrate, $years) - 1));
         for ($i=1; $i <=$years ; $i++) {
         // $input=  str_pad($i, 4, "0", STR_PAD_LEFT);
         $finaldatedisbursed = strtotime("+ 1 year", strtotime($finaldatedisbursed));
         $finaldatedisbursed = strftime ( '%Y-%m-%d' , $finaldatedisbursed);
         $pp = $payment / (pow((1+$annualrate), (1+$years-$i)));
         $int = $payment - $pp;
         $lob = ($int / $annualrate) - $pp;
         //total principal paid + interest
         $totalloanamt += $pp +$int;
         $totalint +=$int;
         // return an array to allow to display the schedule
         $_pprincipal= ceil(($pp/10) *10);
         $_interestpayment= ceil(($int/10) *10) ;
         $_loanbalance= ceil(($lob/10) *10) >0?ceil(($lob/10) *10):0;

         $loanschedulequery = $writeDB->prepare('INSERT into loan_payment_schedule(
           loan_payment_period, loan_payment_date,loan_total_paid_principal,
         loan_payment_principal, loan_payment_interest, loan_payment_amount, loan_payment_transaction_id,
          loan_active_loan_id,loan_payment_schedule_saccoid)
          values (:period, :dates,:totalprinciapalpaid, :principalpayment, :interest, :loanbalance, :transloanid, :loan_id,:saccoid)');
          $loanschedulequery->execute (array(
           ":period" => $i,
           ":interest" => $_interestpayment,
           ":totalprinciapalpaid" => $payment,
           ":principalpayment" => $_pprincipal,
           ":loanbalance" => $_loanbalance,
           ":dates" => $finaldatedisbursed,
           ":transloanid"=>$transID,
           ":loan_id"=>$disbursedID,
           ":saccoid"=>$returned_saccoid
         ));
         }
         }
         elseif ($loanratetype ==='straight line') {
           // code...
           $annualrate = $rate/100;
           $years =$loantenure/12;
           $minrate = ceil(($_amount* $annualrate)/10) *10;
           $pp = ceil(($_amount/$years)/10) *10;

           for ($i=1; $i <=$years ; $i++) {
             $finaldatedisbursed = strtotime("+ 1 year", strtotime($finaldatedisbursed));
             $finaldatedisbursed = strftime ( '%Y-%m-%d' , $finaldatedisbursed);
             $payment= $minrate + $pp;
             $_amount =$_amount-$pp;
             $totalloanamt += $payment;
             $totalint +=$minrate;
             // return an array to allow to display the schedule
             $_pprincipal= $pp;
             $_interestpayment= $minrate;
             $_loanbalance=ceil(($_amount/10) *10) >0?ceil(($_amount/10) *10):0;

             $loanschedulequery = $writeDB->prepare('INSERT into loan_payment_schedule(
               loan_payment_period, loan_payment_date,loan_total_paid_principal,
             loan_payment_principal, loan_payment_interest, loan_payment_amount, loan_payment_transaction_id,
              loan_active_loan_id,loan_payment_schedule_saccoid)
              values (:period, :dates,:totalprinciapalpaid, :principalpayment, :interest, :loanbalance, :transloanid, :loan_id,:saccoid)');
              $loanschedulequery->execute (array(
               ":period" => $i,
               ":interest" => $_interestpayment,
               ":totalprinciapalpaid" => $payment,
               ":principalpayment" => $_pprincipal,
               ":loanbalance" => $_loanbalance,
               ":dates" => $finaldatedisbursed,
               ":transloanid"=>$transID,
               ":loan_id"=>$disbursedID,
               ":saccoid"=>$returned_saccoid
             ));

           }
         }
         break;
         case 'monthly':
         if ($loanratetype ==='reducing balance') {
           $monthlyrate = $rate/1200;
           $payment = $_amount * $monthlyrate * (pow(1 + $monthlyrate, $loantenure) / (pow(1 + $monthlyrate, $loantenure) - 1));
           for ($i=1; $i <=$loantenure ; $i++) {
             $finaldatedisbursed = strtotime("+ 1 months", strtotime($finaldatedisbursed));
             $finaldatedisbursed = strftime ( '%Y-%m-%d' , $finaldatedisbursed);
             $pp = $payment / (pow((1+$monthlyrate), (1+$loantenure-$i)));
             $int = $payment - $pp;
             $lob = ($int / $monthlyrate) - $pp;
             //total principal paid + interest
             $totalloanamt += $pp +$int;
             $totalint +=$int;
             // return an array to allow to display the schedule
             $_pprincipal=ceil(($pp/10) *10);
             $_interestpayment= ceil(($int/10) *10);
             $_loanbalance= ceil(($lob/10) *10) >0?ceil(($lob/10) *10):0;
            $loanschedulequery = $writeDB->prepare('INSERT into loan_payment_schedule(
               loan_payment_period, loan_payment_date,loan_total_paid_principal,
             loan_payment_principal, loan_payment_interest, loan_payment_amount, loan_payment_transaction_id,
              loan_active_loan_id,loan_payment_schedule_saccoid)
              values (:period, :dates,:totalprinciapalpaid, :principalpayment, :interest, :loanbalance, :transloanid, :loan_id,:saccoid)');
              $loanschedulequery->execute (array(
               ":period" => $i,
               ":interest" => $_interestpayment,
               ":totalprinciapalpaid" => $payment,
               ":principalpayment" => $_pprincipal,
               ":loanbalance" => $_loanbalance,
               ":dates" => $finaldatedisbursed,
               ":transloanid"=>$transID,
               ":loan_id"=>$disbursedID,
               ":saccoid"=>$returned_saccoid
             ));

           }
        }
        elseif ($loanratetype ==='straight line') {
          // code...
          $monthlyrate = $rate/1200;
          $minrate = ceil(($_amount* $loantenure)/10) *10;
          $pp = ceil(($_amount/$loantenure)/10) *10;

          for ($i=1; $i <=$loantenure ; $i++) {
            $finaldatedisbursed = strtotime("+ 1 months", strtotime($finaldatedisbursed));
            $finaldatedisbursed = strftime ( '%Y-%m-%d' , $finaldatedisbursed);
            $payment= $minrate + $pp;
            $_amount =$_amount-$pp;
            $totalloanamt += $payment;
            $totalint +=$minrate;
            // return an array to allow to display the schedule
            $_pprincipal= $pp;
            $_interestpayment= $minrate;
            $_loanbalance=ceil(($_amount/10) *10) >0?ceil(($_amount/10) *10):0;

            $loanschedulequery = $writeDB->prepare('INSERT into loan_payment_schedule(
              loan_payment_period, loan_payment_date,loan_total_paid_principal,
            loan_payment_principal, loan_payment_interest, loan_payment_amount, loan_payment_transaction_id,
             loan_active_loan_id,loan_payment_schedule_saccoid)
             values (:period, :dates,:totalprinciapalpaid, :principalpayment, :interest, :loanbalance, :transloanid, :loan_id,:saccoid)');
             $loanschedulequery->execute (array(
              ":period" => $i,
              ":interest" => $_interestpayment,
              ":totalprinciapalpaid" => $payment,
              ":principalpayment" => $_pprincipal,
              ":loanbalance" => $_loanbalance,
              ":dates" => $finaldatedisbursed,
              ":transloanid"=>$transID,
              ":loan_id"=>$disbursedID,
              ":saccoid"=>$returned_saccoid
            ));
          }
        }
        break;
        case "weekly";
        if ($loanratetype ==='reducing balance') {
          $weeklyrate = $rate/5200;
          $loantenure= $loantenure * 4;
          $payment = $_amount * $weeklyrate * (pow(1 + $weeklyrate, $loantenure) / (pow(1 + $weeklyrate, $loantenure) - 1));
          for ($i=1; $i <=$loantenure ; $i++) {
            $finaldatedisbursed = strtotime("+ 7 days", strtotime($finaldatedisbursed));
            $finaldatedisbursed = strftime ( '%Y-%m-%d' , $finaldatedisbursed);
            $pp = $payment / (pow((1+$weeklyrate), (1+$loantenure-$i)));
            $int = $payment - $pp;
            $lob = ($int / $weeklyrate) - $pp;
            //total principal paid + interest
            $totalloanamt += $pp +$int;
            $totalint +=$int;
            // return an array to allow to display the schedule
            $_pprincipal= ceil(($pp/10) *10);
            $_interestpayment=  ceil(($int/10) *10);
            $_loanbalance= ceil(($lob/10) *10) >0?ceil(($lob/10) *10):0;
            $loanschedulequery = $writeDB->prepare('INSERT into loan_payment_schedule(
              loan_payment_period, loan_payment_date,loan_total_paid_principal,
            loan_payment_principal, loan_payment_interest, loan_payment_amount, loan_payment_transaction_id,
             loan_active_loan_id,loan_payment_schedule_saccoid)
             values (:period, :dates,:totalprinciapalpaid, :principalpayment, :interest, :loanbalance, :transloanid, :loan_id,:saccoid)');
             $loanschedulequery->execute (array(
              ":period" => $i,
              ":interest" => $_interestpayment,
              ":totalprinciapalpaid" => $payment,
              ":principalpayment" => $_pprincipal,
              ":loanbalance" => $_loanbalance,
              ":dates" => $finaldatedisbursed,
              ":transloanid"=>$transID,
              ":loan_id"=>$disbursedID,
              ":saccoid"=>$returned_saccoid
            ));
          }
       }
       elseif ($loanratetype ==='straight line') {
         // code...
         $dailyrate = $rate/5200;
         $loantenure= $loantenure * 4;
         $minrate = ceil(($_amount* $loantenure)/10) *10;
         $pp = ceil(($_amount/$loantenure)/10) *10;

         for ($i=1; $i <=$loantenure ; $i++) {
           $finaldatedisbursed = strtotime("+ 1 months", strtotime($finaldatedisbursed));
           $finaldatedisbursed = strftime ( '%Y-%m-%d' , $finaldatedisbursed);
           $payment= $minrate + $pp;
           $_amount =$_amount-$pp;
           $totalloanamt += $payment;
           $totalint +=$minrate;
           // return an array to allow to display the schedule
           $_pprincipal= $pp;
           $_interestpayment= $minrate;
           $_loanbalance=ceil(($_amount/10) *10) >0?ceil(($_amount/10) *10):0;

           $loanschedulequery = $writeDB->prepare('INSERT into loan_payment_schedule(
             loan_payment_period, loan_payment_date,loan_total_paid_principal,
           loan_payment_principal, loan_payment_interest, loan_payment_amount, loan_payment_transaction_id,
            loan_active_loan_id,loan_payment_schedule_saccoid)
            values (:period, :dates,:totalprinciapalpaid, :principalpayment, :interest, :loanbalance, :transloanid, :loan_id,:saccoid)');
            $loanschedulequery->execute (array(
             ":period" => $i,
             ":interest" => $_interestpayment,
             ":totalprinciapalpaid" => $payment,
             ":principalpayment" => $_pprincipal,
             ":loanbalance" => $_loanbalance,
             ":dates" => $finaldatedisbursed,
             ":transloanid"=>$transID,
             ":loan_id"=>$disbursedID,
             ":saccoid"=>$returned_saccoid
           ));
         }
       }
        break;
        case "daily";
        if ($loanratetype ==='reducing balance') {
          $dailyrate = $rate/36500;
          $loantenure= $loantenure * 28;
          $payment = $_amount * $dailyrate * (pow(1 + $dailyrate, $loantenure) / (pow(1 + $dailyrate, $loantenure) - 1));
          for ($i=1; $i <=$loantenure ; $i++) {
            $finaldatedisbursed = strtotime("+ 1 days", strtotime($finaldatedisbursed));
            $finaldatedisbursed = strftime ( '%Y-%m-%d' , $finaldatedisbursed);
            $pp = $payment / (pow((1+$dailyrate), (1+$loantenure-$i)));
            $int = $payment - $pp;
            $lob = ($int / $dailyrate) - $pp;
            //total principal paid + interest
            $totalloanamt += $pp +$int;
            $totalint +=$int;
            // return an array to allow to display the schedule
            $_pprincipal= ceil(($pp/10) *10);
            $_interestpayment=  ceil(($int/10) *10);
            $_loanbalance= ceil(($lob/10) *10) >0?ceil(($lob/10) *10):0;
            $loanschedulequery = $writeDB->prepare('INSERT into loan_payment_schedule(
              loan_payment_period, loan_payment_date,loan_total_paid_principal,
            loan_payment_principal, loan_payment_interest, loan_payment_amount, loan_payment_transaction_id,
             loan_active_loan_id,loan_payment_schedule_saccoid)
             values (:period, :dates,:totalprinciapalpaid, :principalpayment, :interest, :loanbalance, :transloanid, :loan_id,:saccoid)');
             $loanschedulequery->execute (array(
              ":period" => $i,
              ":interest" => $_interestpayment,
              ":totalprinciapalpaid" => $payment,
              ":principalpayment" => $_pprincipal,
              ":loanbalance" => $_loanbalance,
              ":dates" => $finaldatedisbursed,
              ":transloanid"=>$transID,
              ":loan_id"=>$disbursedID,
              ":saccoid"=>$returned_saccoid
            ));
          }
       }
       elseif ($loanratetype ==='straight line') {
         // code...
         $dailyrate = $rate/36500;
         $loantenure= $loantenure * 4;
         $minrate = ceil(($_amount* $loantenure)/10) *10;
         $pp = ceil(($_amount/$loantenure)/10) *10;

         for ($i=1; $i <=$loantenure ; $i++) {
           $finaldatedisbursed = strtotime("+ 1 months", strtotime($finaldatedisbursed));
           $finaldatedisbursed = strftime ( '%Y-%m-%d' , $finaldatedisbursed);
           $payment= $minrate + $pp;
           $_amount =$_amount-$pp;
           $totalloanamt += $payment;
           $totalint +=$minrate;
           // return an array to allow to display the schedule
           $_pprincipal= $pp;
           $_interestpayment= $minrate;
           $_loanbalance=ceil(($_amount/10) *10) >0?ceil(($_amount/10) *10):0;
           $loanschedulequery = $writeDB->prepare('INSERT into loan_payment_schedule(
             loan_payment_period, loan_payment_date,loan_total_paid_principal,
           loan_payment_principal, loan_payment_interest, loan_payment_amount, loan_payment_transaction_id,
            loan_active_loan_id,loan_payment_schedule_saccoid)
            values (:period, :dates,:totalprinciapalpaid, :principalpayment, :interest, :loanbalance, :transloanid, :loan_id,:saccoid)');
            $loanschedulequery->execute (array(
             ":period" => $i,
             ":interest" => $_interestpayment,
             ":totalprinciapalpaid" => $payment,
             ":principalpayment" => $_pprincipal,
             ":loanbalance" => $_loanbalance,
             ":dates" => $finaldatedisbursed,
             ":transloanid"=>$transID,
             ":loan_id"=>$disbursedID,
             ":saccoid"=>$returned_saccoid
           ));
         }
       }
        break;

      }
      // loan applications approve
      $updateloandisbursequery = $writeDB->prepare('UPDATE loans_disbursed set loan_balance = :amount,
        total_interest=:interest
        where loan_id  = :loanid');
        $updateloandisbursequery->bindParam(':interest', $totalint, PDO::PARAM_STR);
      $updateloandisbursequery->bindParam(':amount', $totalloanamt, PDO::PARAM_STR);
      $updateloandisbursequery->bindParam(':loanid', $_applicationid, PDO::PARAM_INT);
      $updateloandisbursequery->execute();
      $rowsiCount = $updateloandisbursequery->rowCount();
      if ($rowsiCount === 0 ) {
        // return the data to the schedule
        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage('Error updating disbursed total interest and balance');
        $response->send();
        exit;
      }

      //commit the change
      $writeDB->commit();

    }catch (PDOException $ex) {
        $writeDB->rollBack();
        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage("There was an issue making the transaction".$ex);
        $response->send();
        exit;
      }

              // account info array
              $TransactionArray = array(
                "number" => $AccountNumber,
                "firstname" => $firstname,
                "lastname" => $lastname,
                "newloanamt" => number_format(($totalloanamt+$totalint),2),
                "totalloanamt"=> number_format($totalloanamt,2),
                "totalinterest"=> number_format($totalint,2),
              );
          // send SMS and email
          $newAmount = number_format(($_amount),0,'.',',');
          //date and time generation
          $postdate = new DateTime();
          // set date for kampala
          $postdate->setTimezone(new DateTimeZone('Africa/Nairobi'));
          //formulate the new date
          $date = $postdate->format('Y-m-d H:i:s');

        $message = "Hello (".$firstname." " .$lastname."), your loan has been rescheduled, your new loan payment amount is (".number_format(($totalloanamt),2).") ";
        if ($sacco_sms_status === 'on') {
          // code...
          insertSMSDB($writeDB, $message, $accountContact, $returned_saccoid);
        }
        // insert sms into the database
        if ( $sacco_email_status === 'on') {
          // code...
          insertEMAILDB($writeDB, $message, $saccoEmail, $returned_saccoid);
        }
        // insert email into the database
      // bundle tasks and rows returned into an array to return in the json data
      $returnData = array();
      $returnData['rows_returned'] = $rowCount;
      $returnData['transaction'] = $TransactionArray;
      //set up response for successful return
      $response = new Response();
      $response->setHttpStatusCode(201);
      $response->setSuccess(true);
      $response->setData($returnData);
      $response->send();
      exit;
    }
    // if error with sql query return a json error
    catch(PDOException $ex) {
      error_log("Database Query Error: ".$ex, 0);
      $response = new Response();
      $response->setHttpStatusCode(500);
      $response->setSuccess(false);
      $response->addMessage("failed to disburse loan $ex");
      $response->send();
      exit;
    }
  }
  // if any other request method apart from GET or POST is used then return 405 method not allowed
  else {
    $response = new Response();
    $response->setHttpStatusCode(405);
    $response->setSuccess(false);
    $response->addMessage("Request method not allowed");
    $response->send();
    exit;
  }
}
// return 404 error if endpoint not available
else {
  $response = new Response();
  $response->setHttpStatusCode(404);
  $response->setSuccess(false);
  $response->addMessage("Endpoint not found");
  $response->send();
  exit;
}
