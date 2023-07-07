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
if (array_key_exists("loanappid",$_GET)) {
  // get task id from query string
  $loanappid = $_GET['loanappid'];
  //check to see if task id in query string is not empty and is number, if not return json error
  if($loanappid == '' || !is_numeric($loanappid)) {
    $response = new Response();
    $response->setHttpStatusCode(400);
    $response->setSuccess(false);
    $response->addMessage("loan application id cannot be blank or must be numeric");
    $response->send();
    exit;
  }
  if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = $readDB->prepare('SELECT * from loan_applications,loan_product_settings, members, users
       WHERE loan_applications.members_member_id = members.member_id
       AND loan_applications.loan_product_product_id=loan_product_settings.sloan_product_id
       AND loan_applications.users_user_id = users.user_id
       AND loan_app_id = :loanid
       AND loanapplicationtype ="individual"
       AND loan_applications.saccos_sacco_id = :saccoid');
    $query->bindParam(':loanid', $loanappid, PDO::PARAM_INT);
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
        "amountappliedfor" => number_format($loan_app_amount,2),
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
         $amount= $loan_app_amount;
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
         $amount= $loan_app_amount;
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
         $amount= $loan_app_amount;
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
         $amount= $loan_app_amount;
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
         $amount= $loan_app_amount;
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
          $amount= $loan_app_amount;
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
         $amount= $loan_app_amount;
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
          $amount= $loan_app_amount;
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
   }elseif($_SERVER['REQUEST_METHOD'] === 'PATCH') {
    // attempt to query the database
    try {
      // create db query
      $query = $readDB->prepare('SELECT * from loan_applications
         WHERE loan_app_id = :loanid
         AND loanapplicationtype ="individual"
         AND loan_applications.saccos_sacco_id = :saccoid');
      $query->bindParam(':loanid', $loanappid, PDO::PARAM_INT);
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
  		$query->execute();

      // get row count
      $rowCount = $query->rowCount();

      if($rowCount === 0) {
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("loan application not found");
        $response->send();
        exit;
      }

      // create array to store returned task
     $row = $query->fetch(PDO::FETCH_ASSOC);
        extract($row);
        $loan_status= $loan_app_status;
        if ($loan_status =='rejected' || $loan_status =='approved'|| $loan_status =='processed'
        || $loan_status=='disbursed') {
          // code...
          $response = new Response();
          $response->setHttpStatusCode(404);
          $response->setSuccess(false);
          ($loan_status =='rejected' ? $response->addMessage("this loan was rejected") : false);
          ($loan_status =='approved' ? $response->addMessage("this loan is already approved") : false);
          ($loan_status =='processed' ? $response->addMessage("this loan is already proccessed") : false);
          ($loan_status =='disbursed' ? $response->addMessage("this loan is already disbursed") : false);
          $response->send();
          exit;
        }
        // loan applications approve
        $_status='processed';
        $updatequery = $writeDB->prepare('UPDATE loan_applications set loan_app_status = :status
          where loan_app_id = :loanid');
        $updatequery->bindParam(':status', $_status, PDO::PARAM_STR);
        $updatequery->bindParam(':loanid', $loanappid, PDO::PARAM_INT);
        $updatequery->execute();
        $rowsCount = $updatequery->rowCount();
        if ($rowsCount === 0 ) {
          // return the data to the schedule
          $response = new Response();
          $response->setHttpStatusCode(500);
          $response->setSuccess(false);
          $response->addMessage('Error updating loan application status');
          $response->send();
          exit;
        }
        $query = $readDB->prepare('SELECT * from loan_applications,loan_product_settings, members, users,
          saccos WHERE members.saccos_sacco_id = saccos.sacco_id
          AND loan_applications.members_member_id = members.member_id
           AND loan_applications.loan_product_product_id=loan_product_settings.sloan_product_id
           AND loan_applications.users_user_id = users.user_id
           AND loan_app_id = :loanid
           AND loanapplicationtype ="individual"
           AND loan_applications.saccos_sacco_id = :saccoid');
        $query->bindParam(':loanid', $loanappid, PDO::PARAM_INT);
        $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
        $query->execute();

       $row = $query->fetch(PDO::FETCH_ASSOC);
          extract($row);
        $newAmount = number_format(($loan_app_amount),0,'.',',');
        $accountContact = $row['member_contact'];
        $saccoEmail = $row['sacco_email'];
        $sacco_sms_status = $row['sacco_sms_status'];
        $sacco_email_status = $row['sacco_email_status'];
        //date and time generation
        $postdate = new DateTime();
        // set date for kampala
        $postdate->setTimezone(new DateTimeZone('Africa/Nairobi'));
        //formulate the new date
        $date = $postdate->format('Y-m-d H:i:s');

        $message = "Hello, your loan application of UGX ".$newAmount." has been reviewed and processed, you will be notified once the loan is approved. Loan ID ".$loan_app_number. ". Date: ".$date;
      // insert sms into the database
      if ($sacco_sms_status === 'on') {
        // code...
        insertSMSDB($writeDB, $message, $accountContact, $returned_saccoid);
      }
      // insert email into the database
      if ($sacco_email_status === 'on') {
        // code...
        insertEMAILDB($writeDB, $message, $saccoEmail, $returned_saccoid);
      }
        $response = new Response();
        $response->setHttpStatusCode(201);
        $response->setSuccess(true);
        $response->addMessage("loan application has been processed");
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

// return 404 error if endpoint not available
else {
  $response = new Response();
  $response->setHttpStatusCode(404);
  $response->setSuccess(false);
  $response->addMessage("Endpoint not found");
  $response->send();
  exit;
}
