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

if (array_key_exists("loanid",$_GET)) {
  // get task id from query string
  $loanid = $_GET['loanid'];
  //check to see if task id in query string is not empty and is number, if not return json error
  if($loanid == '' || !is_numeric($loanid)) {
    $response = new Response();
    $response->setHttpStatusCode(400);
    $response->setSuccess(false);
    $response->addMessage("loanid cannot be blank or must be numeric");
    $response->send();
    exit;
  }

  // if request is a GET, e.g. get transaction
  if($_SERVER['REQUEST_METHOD'] === 'GET') {
    // attempt to query the database
    try {
      // create db query
      // ADD AUTH TO QUERY

      $query = $readDB->prepare('SELECT * from loan_applications,loan_product_settings, members, users
         WHERE loan_applications.members_member_id = members.member_id
         AND loan_applications.loan_product_product_id=loan_product_settings.sloan_product_id
         AND loan_applications.users_user_id = users.user_id and loan_app_id = :loanid
         AND loanapplicationtype ="group"
         AND loan_app_status="cancelled"
         AND member_type="group"
         AND loan_applications.saccos_sacco_id = :saccoid');
      $query->bindParam(':loanid', $loanid, PDO::PARAM_INT);
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
      $transactionArray = array();

      while($row = $query->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
       $loan_app = array(
         "id" => $loan_app_id,
         "account" => $members_account_number,
         "firstname" => $member_fname,
         "lastname" => $member_lname,
         "loanproduct" => $name_of_loan_product,
         "loantype" => $loan_type,
         "amount" => $loan_app_amount,
         "loanID" => $loan_app_number,
         "status" => $loan_app_status,
         "timestamp" => $loan_app_date
       );
       $transactionArray[] = $loan_app;
      }

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
elseif(empty($_GET)){
        // get the user profile data
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {

        try {
          $query = $readDB->prepare('SELECT * FROM  members
            where members.member_id IN (SELECT loan_applications.members_member_id FROM loan_applications)
            AND members.saccos_sacco_id = :saccoid
            AND member_type="group"');
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
            $loanquery = $readDB->prepare('SELECT * from loan_applications,loan_product_settings
              where loan_applications.members_member_id = :memberid
               AND loan_applications.loan_product_product_id=loan_product_settings.sloan_product_id
                and loan_applications.users_user_id = :userid
                AND loanapplicationtype ="group"
                AND loan_app_status="cancelled"
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
                "amount" => $loan_app_amount,
                "loanproduct" => $name_of_loan_product,
                "loantype" => $loan_type,
                "loanid" => $loan_app_number,
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
          $returnData['loans'] = $membersArray;
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

          if (!isset($jsonData->cancel_action) || empty($jsonData->cancel_action)
          || !isset($jsonData->loanappid)||!is_numeric($jsonData->loanappid)|| empty($jsonData->loanappid)
          || !isset($jsonData->reason) || empty($jsonData->reason)):
          $response = new Response();
          $response->setHttpStatusCode(404);
          $response->setSuccess(false);
          (!isset($jsonData->cancel_action) ? $response->addMessage("cancelling action field is mandatory and must be provided") : false);
          (empty($jsonData->cancel_action) ? $response->addMessage("cancelling action field must not be empty") : false);
          (!isset($jsonData->loanappid) ? $response->addMessage("loan application id field is mandatory and must be provided") : false);
          (empty($jsonData->loanappid) ? $response->addMessage("loan application id field must not be empty") : false);
          (!is_numeric($jsonData->loanappid) ? $response->addMessage("loan application id field value must be numeric") : false);
          (!isset($jsonData->reason) ? $response->addMessage("cancelling reason field is mandatory and must be provided") : false);
          (empty($jsonData->reason) ? $response->addMessage("cancelling reason field must not be empty") : false);
          $response->send();
          exit;
          endif;
          $cancelled= $jsonData->cancel_action;
          $reason= $jsonData->reason;
          $loanid= $jsonData->loanappid;
          $datecanselled= date("Y-m-d H:i:s");

          $query = $readDB->prepare('SELECT * from loan_applications where loan_app_id  = :loanid');
          $query->bindParam(':loanid', $loanid, PDO::PARAM_INT);
          $query->execute();
          // loan requistion for the monitor
          $row = $query->fetch(PDO::FETCH_ASSOC);

          $loan_status = $row['loan_app_status'];
          if ($loan_status=='disbursed'):
            $response = new Response();
            $response->setHttpStatusCode(404);
            $response->setSuccess(false);
            $response->addMessage("Sorry, this loan is already disbursed and it cannot be cancelled");
            $response->send();
            exit;
          endif;

          switch ($cancelled) {
            case 'entire loan':
            $cancel="cancelled";
            if ($loan_status =='cancelled' || $loan_status =='rejected'|| $loan_status =='disbursed') {
              $response = new Response();
              $response->setHttpStatusCode(404);
              $response->setSuccess(false);
              ($loan_status =='cancelled' ? $response->addMessage("Sorry, this loan is already cancelled") : false);
              ($loan_status =='rejected' ? $response->addMessage("Sorry, this loan was rejected") : false);
              ($loan_status =='disbursed' ? $response->addMessage("Sorry, this loan is already disbursed") : false);
              $response->send();
              exit;
            }
            $updatequery = $writeDB->prepare('UPDATE loan_applications set loan_app_status = :status
              where loan_app_id = :loanid');
              $updatequery->bindParam(':status', $cancel, PDO::PARAM_STR);
              $updatequery->bindParam(':loanid', $loanid, PDO::PARAM_INT);
              $updatequery->execute();
              $rowCount = $updatequery->rowCount();
              if ($rowCount === 0 ) {
                $response = new Response();
                $response->setHttpStatusCode(500);
                $response->setSuccess(false);
                $response->addMessage('error updating loan application');
                $response->send();
                exit;
              }
              $query = $writeDB->prepare('INSERT INTO loan_cancelled(loanapp_id, reason_cancelled,
              date_cancelled, loan_cancelled_saccoid) values (:loanid,:reason,:datte, :saccoid)');
              $query->bindParam(':datte', $datecanselled, PDO::PARAM_INT);
              $query->bindParam(':reason', $reason, PDO::PARAM_INT);
              $query->bindParam(':loanid', $loanid, PDO::PARAM_INT);
              $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
              $query->execute();
              // return the data to the schedule
              $query = $readDB->prepare('SELECT * from loan_applications,loan_product_settings, members, users,
                saccos WHERE members.saccos_sacco_id = saccos.sacco_id
                AND loan_applications.members_member_id = members.member_id
                 AND loan_applications.loan_product_product_id=loan_product_settings.sloan_product_id
                 AND loan_applications.users_user_id = users.user_id
                 AND loan_app_id = :loanid
                 AND loanapplicationtype ="group"
                 AND loan_applications.saccos_sacco_id = :saccoid');
              $query->bindParam(':loanid', $loanid, PDO::PARAM_INT);
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

              $message = "Hello, your loan application of UGX ".$newAmount." has been cancelled entirely. Reason:".$jsonData->reason." . Loan ID ".$loan_app_number. ". Date: ".$date;
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
              $response->addMessage('Loan has been cancelled');
              $response->send();
              exit;
              break;
              case 'loan processing':
              if ($loan_status =='pending' || $loan_status =='rejected' || $loan_status =='approved'|| $loan_status =='cancelled') {
                $response = new Response();
                $response->setHttpStatusCode(404);
                $response->setSuccess(false);
                ($loan_status =='pending' ? $response->addMessage("Sorry, this loan is still pending") : false);
                ($loan_status =='rejected' ? $response->addMessage("Sorry, this loan was rejected") : false);
                ($loan_status =='approved' ? $response->addMessage("Sorry, this loan is already approved") : false);
                ($loan_status =='cancelled' ? $response->addMessage("Sorry, this loan is already cancelled") : false);
                $response->send();
                exit;
              }
              $pending="pending";
              $updatequery = $writeDB->prepare('UPDATE loan_applications set loan_app_status = :status
                where loan_app_id = :loanid');
                $updatequery->bindParam(':status', $pending, PDO::PARAM_STR);
                $updatequery->bindParam(':loanid', $loanid, PDO::PARAM_INT);
                $updatequery->execute();
                $rowCount = $updatequery->rowCount();
                if ($rowCount === 0 ) {
                  $response = new Response();
                  $response->setHttpStatusCode(500);
                  $response->setSuccess(false);
                  $response->addMessage('error updating loan application');
                  $response->send();
                  exit;
                }
                $query = $writeDB->prepare('INSERT INTO loan_cancelled(loanapp_id, reason_cancelled,
                date_cancelled, loan_cancelled_saccoid) values (:loanid,:reason,:datte, :saccoid)');
                $query->bindParam(':datte', $datecanselled, PDO::PARAM_INT);
                $query->bindParam(':reason', $reason, PDO::PARAM_INT);
                $query->bindParam(':loanid', $loanid, PDO::PARAM_INT);
                $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
                $query->execute();
                // return the data to the schedule
                $query = $readDB->prepare('SELECT * from loan_applications,loan_product_settings, members, users,
                  saccos WHERE members.saccos_sacco_id = saccos.sacco_id
                  AND loan_applications.members_member_id = members.member_id
                   AND loan_applications.loan_product_product_id=loan_product_settings.sloan_product_id
                   AND loan_applications.users_user_id = users.user_id
                   AND loan_app_id = :loanid
                   AND loanapplicationtype ="group"
                   AND loan_applications.saccos_sacco_id = :saccoid');
                $query->bindParam(':loanid', $loanid, PDO::PARAM_INT);
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

                $message = "Hello, your loan processing of UGX ".$newAmount." has been cancelled. Reason:".$jsonData->reason." . Loan ID ".$loan_app_number. ". Date: ".$date;
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
                $response->addMessage('Loan proccessing has been cancelled');
                $response->send();
                exit;
              break;
              case 'loan approval':
              // code...
              if ($loan_status =='pending' || $loan_status =='rejected'
              || $loan_status =='processed'|| $loan_status =='cancelled') {
                $response = new Response();
                $response->setHttpStatusCode(404);
                $response->setSuccess(false);
                ($loan_status =='pending' ? $response->addMessage("Sorry, this loan is still pending") : false);
                ($loan_status =='rejected' ? $response->addMessage("Sorry, this loan was rejected") : false);
                ($loan_status =='processed' ? $response->addMessage("Sorry, this loan is still on processing") : false);
                ($loan_status =='cancelled' ? $response->addMessage("Sorry, this loan is already cancelled") : false);
                $response->send();
                exit;
              }
              $proccessed="processed";
              $updatequery = $writeDB->prepare('UPDATE loan_applications set loan_app_status = :status
                where loan_app_id = :loanid');
                $updatequery->bindParam(':status', $proccessed, PDO::PARAM_STR);
                $updatequery->bindParam(':loanid', $loanid, PDO::PARAM_INT);
                $updatequery->execute();
                $rowCount = $updatequery->rowCount();
                if ($rowCount === 0 ) {
                  $response = new Response();
                  $response->setHttpStatusCode(500);
                  $response->setSuccess(false);
                  $response->addMessage('error updating loan application');
                  $response->send();
                  exit;
                }
                $query = $writeDB->prepare('INSERT INTO loan_cancelled(loanapp_id, reason_cancelled,
                date_cancelled, loan_cancelled_saccoid) values (:loanid,:reason,:datte, :saccoid)');
                $query->bindParam(':datte', $datecanselled, PDO::PARAM_INT);
                $query->bindParam(':reason', $reason, PDO::PARAM_INT);
                $query->bindParam(':loanid', $loanid, PDO::PARAM_INT);
                $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
                $query->execute();
                // return the data to the schedule
                $query = $readDB->prepare('SELECT * from loan_applications,loan_product_settings, members, users,
                  saccos WHERE members.saccos_sacco_id = saccos.sacco_id
                  AND loan_applications.members_member_id = members.member_id
                   AND loan_applications.loan_product_product_id=loan_product_settings.sloan_product_id
                   AND loan_applications.users_user_id = users.user_id
                   AND loan_app_id = :loanid
                   AND loanapplicationtype ="group"
                   AND loan_applications.saccos_sacco_id = :saccoid');
                $query->bindParam(':loanid', $loanid, PDO::PARAM_INT);
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

                $message = "Hello, your loan approval of UGX ".$newAmount." has been cancelled. Reason:".$jsonData->reason." . Loan ID ".$loan_app_number. ". Date: ".$date;
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
                $response->addMessage('Loan approval has been cancelled');
                $response->send();
                exit;
              break;
              // case 'loan disbursement':
              // // code...
              // break;
              default:
              $response = new Response();
              $response->setHttpStatusCode(404);
              $response->setSuccess(false);
              $response->addMessage('the cansell action sent is not allowed');
              $response->send();
              exit;
              break;
            }
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
