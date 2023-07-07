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
      $returnData['grouploanapplication'] = $transactionArray;

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
  // else if request if a DELETE e.g. delete
  elseif($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // attempt to query the database
    try {
      // ADD AUTH TO QUERY
      $status="approved";
      $query = $writeDB->prepare('DELETE from loan_applications where loan_app_id = :loanid AND loan_app_status !=:status');
      $query->bindParam(':status', $status, PDO::PARAM_INT);
      $query->bindParam(':loanid', $loanid, PDO::PARAM_INT);
      $query->execute();

      // get row count
      $rowCount = $query->rowCount();

      if($rowCount === 0):
              // set up response for unsuccessful return
              $response = new Response();
              $response->setHttpStatusCode(404);
              $response->setSuccess(false);
              $response->addMessage("loan application not found");
              $response->send();
              exit;
      else:
              $response = new Response();
              $response->setHttpStatusCode(200);
              $response->setSuccess(true);
              $response->addMessage("loan application deleted");
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
      $response->addMessage("Failed to delete loan application - Attached Info");
      $response->send();
      exit;
    }
  }
  // handle updating task

  elseif($_SERVER['REQUEST_METHOD'] === 'PATCH'){
          try {
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

            // update the new note
            // set branch field updated to false initially
            $amount = false;
            $amornitizationinterval=false;
            $graceperiod=false;
            $tenure=false;
            $loanproduct=false;

            // create blank query fields string to append each field to
            $queryFields = "";

            // check if name exists in PATCH
            if(isset($jsonData->amount)):
              // set title field updated to true
              $amount = true;
              // add name field to query field string
              $queryFields .= "loan_app_amount = :amount, ";
            endif;
            // check if name exists in PATCH
            if(isset($jsonData->amornitizationinterval)):
              // set title field updated to true
              $amornitizationinterval = true;
              // add name field to query field string
              $queryFields .= "amornitization_interval = :amornitizationinterval, ";
            endif;
            // check if name exists in PATCH
            if(isset($jsonData->graceperiod)):
              // set title field updated to true
              $graceperiod = true;
              // add name field to query field string
              $queryFields .= "grace_period = :graceperiod, ";
            endif;
            // check if name exists in PATCH
            if(isset($jsonData->tenure)):
              // set title field updated to true
              $tenure = true;
              // add name field to query field string
              $queryFields .= "tenure = :tenure, ";
            endif;
            // check if name exists in PATCH
            if(isset($jsonData->loanproduct)):
              // set title field updated to true
              $loanproduct = true;
              // add name field to query field string
              $queryFields .= "loan_product_product_id = :loanproduct, ";
            endif;
            // remove the right hand comma and trailing space
            $queryFields = rtrim($queryFields, ", ");

            // check if any branch fields supplied in JSON
            if($amount === false):
              $response = new Response();
              $response->setHttpStatusCode(400);
              $response->setSuccess(false);
              $response->addMessage("No amount fields provided");
              $response->send();
              exit;
            endif;
            // ADD AUTH TO QUERY
            // create db query to get branch from database to update - use master db
            $query = $writeDB->prepare('select * from loan_applications where loan_app_id = :id');
            $query->bindParam(':id', $loanid, PDO::PARAM_INT);
            $query->execute();

            // get row count
            $rowCount = $query->rowCount();

            // make sure that the branch exists for a given branch id
            if($rowCount === 0):
              // set up response for unsuccessful return
              $response = new Response();
              $response->setHttpStatusCode(404);
              $response->setSuccess(false);
              $response->addMessage("No loan app found to update");
              $response->send();
              exit;
            endif;

            // create the query string including any query fields
            $queryString = "UPDATE loan_applications set ".$queryFields." where loan_app_id = :id";
            // prepare the query
            $query = $writeDB->prepare($queryString);

            // if name has been provided
            if($amount === true):
              // bind the parameter of the new value from the object to the query (prevents SQL injection)
              $query->bindParam(':amount', $jsonData->amount, PDO::PARAM_STR);
            endif;
            // if name has been provided
            if($amornitizationinterval === true):
              // bind the parameter of the new value from the object to the query (prevents SQL injection)
              $query->bindParam(':amornitizationinterval', $jsonData->amornitizationinterval, PDO::PARAM_STR);
            endif;
            // if name has been provided
            if($graceperiod === true):
              // bind the parameter of the new value from the object to the query (prevents SQL injection)
              $query->bindParam(':graceperiod', $jsonData->graceperiod, PDO::PARAM_STR);
            endif;
            // if name has been provided
            if($tenure === true):
              // bind the parameter of the new value from the object to the query (prevents SQL injection)
              $query->bindParam(':tenure', $jsonData->tenure, PDO::PARAM_STR);
            endif;
            // if name has been provided
            if($loanproduct === true):
              // bind the parameter of the new value from the object to the query (prevents SQL injection)
              $query->bindParam(':loanproduct', $jsonData->loanproduct, PDO::PARAM_STR);
            endif;
            // bind the Branch id provided in the query string
            $query->bindParam(':id', $loanid, PDO::PARAM_INT);
            // run the query
            $query->execute();

            // get affected row count
            $rowCount = $query->rowCount();
            // echo $rowCount;
            // check if row was actually updated, could be that the given values are the same as the stored values
            if($rowCount === 0):
              // set up response for unsuccessful return
              $response = new Response();
              $response->setHttpStatusCode(400);
              $response->setSuccess(false);
              $response->addMessage("loan application not updated - given values may be the same as the stored values");
              $response->send();
              exit;
            endif;

            // ADD AUTH TO QUERY
            // create db query to return the newly edited branch - connect to master database
            $query = $writeDB->prepare('select * from loan_applications where loan_app_id = :loanid');
            $query->bindParam(':loanid', $loanid, PDO::PARAM_INT);
            $query->execute();

            // get row count
            $rowCount = $query->rowCount();

            // check if branch was found
            if($rowCount === 0):
              // set up response for unsuccessful return
              $response = new Response();
              $response->setHttpStatusCode(404);
              $response->setSuccess(false);
              $response->addMessage("No loan app found");
              $response->send();
              exit;
            endif;
            // create branch array to store returned branches
            $loanArray = array();

            while($row = $query->fetch(PDO::FETCH_ASSOC)) {
              extract($row);

              $loan_app = array(
                "id" => $loan_app_id,
                "amount" => $loan_app_amount,
                "loanID" => $loan_app_number,
                "status" => $loan_app_status,
                "timestamp" => $loan_app_date
              );
              $loanArray[] = $loan_app;
            }
            // bundle branch and rows returned into an array to return in the json data
            $returnData = array();
            $returnData['rows_returned'] = $rowCount;
            $returnData['grouploanapplication'] = $loanArray;

            // set up response for successful return
            $response = new Response();
            $response->setHttpStatusCode(201);
            $response->setSuccess(true);
            $response->addMessage("loan application updated");
            $response->setData($returnData);
            $response->send();
            exit;
          } catch (PDOException $ex) {
            // set up response for unsuccessful request
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage("error updating loan");
            $response->send();
            exit;
          }

        }
        elseif($_SERVER['REQUEST_METHOD'] === 'PUT') {
        try {
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
          if(!isset($jsonData->status) || empty($jsonData->status)) {
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            (!isset($jsonData->status) ? $response->addMessage("status field is mandatory and must be provided") : false);
            (empty($jsonData->status) ? $response->addMessage("status field must not be empty") : false);
            $response->send();
            exit;
          }
          $status = $jsonData->status;
          // set the loans to applications
          $query = $readDB->prepare('SELECT * from loan_applications WHERE
              loan_app_id  = :loanid
             AND loanapplicationtype ="group"');
          $query->bindParam(':loanid', $loanid, PDO::PARAM_INT);
          $query->execute();
          // loan requistion for the monitor
          $row = $query->fetch(PDO::FETCH_ASSOC);
          extract($row);
          $loan_status =$loan_app_status;

          if ($loan_status =='pending' ||$loan_status =='rejected' || $loan_status =='approved'
          || $loan_status=='disbursed') {
            $response = new Response();
            $response->setHttpStatusCode(404);
            $response->setSuccess(false);
            ($loan_status =='pending' ? $response->addMessage("you must process this loan first") : false);
            ($loan_status =='rejected' ? $response->addMessage("this loan was rejected") : false);
            ($loan_status =='approved' ? $response->addMessage("this loan is already approved") : false);
            // ($loan_status =='processed' ? $response->addMessage("this loan is already proccessed") : false);
            ($loan_status =='disbursed' ? $response->addMessage("this loan is already disbursed") : false);
            $response->send();
            exit;
          }
          if ($status === 'approved') {
            try{
              // loan applications approve
            $updatequery = $writeDB->prepare('UPDATE loan_applications set loan_app_status = :status
              where loan_app_id = :loanid');
            $updatequery->bindParam(':status', $status, PDO::PARAM_STR);
            $updatequery->bindParam(':loanid', $loanid, PDO::PARAM_INT);
            $updatequery->execute();
            $rowCount = $updatequery->rowCount();
            if ($rowCount === 0 ) {
              // return the data to the schedule
              $response = new Response();
              $response->setHttpStatusCode(500);
              $response->setSuccess(false);
              $response->addMessage('error updating loan application');
              $response->send();
              exit;
            }
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

            $message = "Hello, your group loan application of UGX ".$newAmount." has been approved, you will be notified once the loan is disbursed. Loan ID ".$loan_app_number. ". Date: ".$date;
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
            // return the data to the schedule
            $response = new Response();
            $response->setHttpStatusCode(201);
            $response->setSuccess(true);
            $response->addMessage('Loan has been approved');
            $response->send();
            exit;
          }
          catch(PDOException $ex) {
            error_log("Database Query Error: ".$ex, 0);
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage("Failed to update - check your data for errors".$ex);
            $response->send();
            exit;
          }
        }
        if ($status === 'rejected') {
          try{
            // loan applications approve
          $updatequery = $writeDB->prepare('UPDATE loan_applications set loan_app_status = :status
            where loan_app_id = :loanid');
          $updatequery->bindParam(':status', $status, PDO::PARAM_STR);
          $updatequery->bindParam(':loanid', $loanid, PDO::PARAM_INT);
          $updatequery->execute();
          $rowCount = $updatequery->rowCount();
          if ($rowCount === 0 ) {
            // return the data to the schedule
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage('error updating loan application');
            $response->send();
            exit;
          }
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

          $message = "Hello, your group loan application of UGX ".$newAmount." has been rejected. Loan ID ".$loan_app_number. ". Date: ".$date;
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
          // return the data to the schedule
          $response = new Response();
          $response->setHttpStatusCode(201);
          $response->setSuccess(true);
          $response->addMessage('Loan has been rejected');
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
          $response->addMessage("Failed to update - check your data for errors".$ex);
          $response->send();
          exit;
        }
      }
      else {
      // code...
      $response = new Response();
      $response->setHttpStatusCode(400);
      $response->setSuccess(false);
      $response->addMessage("wrong status");
      $response->send();
      exit;
      }

        }
        catch(PDOException $ex) {
          error_log("Database Query Error: ".$ex, 0);
          $response = new Response();
          $response->setHttpStatusCode(500);
          $response->setSuccess(false);
          $response->addMessage("Failed to update - check your data for errors".$ex);
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
       $members['grouploanapplications'] = $transactionArray;
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
      if(!isset($jsonData->groupid) || !is_numeric($jsonData->groupid)||empty($jsonData->groupid)
      || !isset($jsonData->amount)||!is_numeric($jsonData->amount)|| empty($jsonData->amount)
      || !isset($jsonData->loanproduct)||!is_numeric($jsonData->loanproduct)|| empty($jsonData->loanproduct)
      || !isset($jsonData->dateapplied) || empty($jsonData->dateapplied)
      || !isset($jsonData->graceperiod) || empty($jsonData->graceperiod)||!is_numeric($jsonData->graceperiod)
      || !isset($jsonData->tenureperiod)|| empty($jsonData->tenureperiod)||!is_numeric($jsonData->tenureperiod)
      || !isset($jsonData->amornitizationinterval) || empty($jsonData->amornitizationinterval)
      || !isset($jsonData->reason)|| empty($jsonData->reason)
       ) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        (!isset($jsonData->groupid) ? $response->addMessage("group field is mandatory and must be provided") : false);
        (empty($jsonData->groupid) ? $response->addMessage("group field must not be empty") : false);
        (!is_numeric($jsonData->groupid) ? $response->addMessage("group id field must be numeric") : false);
        (!isset($jsonData->amount) ? $response->addMessage("amount field is mandatory and must be provided") : false);
        (empty($jsonData->amount) ? $response->addMessage("amount field must not be empty") : false);
        (!is_numeric($jsonData->amount) ? $response->addMessage("amount field value must be numeric") : false);
        (!isset($jsonData->loanproduct) ? $response->addMessage("loantype field is mandatory and must be provided") : false);
        (empty($jsonData->loanproduct) ? $response->addMessage("loantype field must not be empty") : false);
        (!is_numeric($jsonData->loanproduct) ? $response->addMessage("loanproduct field value must be numeric") : false);
        (!isset($jsonData->dateapplied) ? $response->addMessage("date field is mandatory and must be provided") : false);
        (empty($jsonData->dateapplied) ? $response->addMessage("date field must not be empty") : false);
        (!isset($jsonData->tenureperiod) ? $response->addMessage("tenure period field is mandatory and must be provided") : false);
        (empty($jsonData->tenureperiod) ? $response->addMessage("tenure period field must not be empty") : false);
        (!is_numeric($jsonData->tenureperiod) ? $response->addMessage("tenure period field value must be numeric") : false);
        (!isset($jsonData->graceperiod) ? $response->addMessage("loan grace period field is mandatory and must be provided") : false);
        (empty($jsonData->graceperiod) ? $response->addMessage("grace period field must not be empty") : false);
        (!is_numeric($jsonData->graceperiod) ? $response->addMessage("grace period field value must be numeric") : false);
        (!isset($jsonData->amornitizationinterval) ? $response->addMessage("amornitization interval field is mandatory and must be provided") : false);
        (empty($jsonData->amornitizationinterval) ? $response->addMessage("amornitization interval field must not be empty") : false);
        (!isset($jsonData->reason) ? $response->addMessage("reason for a loan field is mandatory and must be provided") : false);
        (empty($jsonData->reason) ? $response->addMessage("reason for loan  field must not be empty") : false);
        $response->send();
        exit;
      }

      $_memberid = (int)$jsonData->groupid;
      $amount = (int)$jsonData->amount;
      $_date = $jsonData->dateapplied;
      $_loanproduct = (int) $jsonData->loanproduct;
      $graceperiod = (int) $jsonData->graceperiod;
      $tenureperiod = (int) $jsonData->tenureperiod;
      $amornitizationinterval =$jsonData->amornitizationinterval;
      $reason = $jsonData->reason;
      $applicationtype = 'group';
      $loandatetime= date('Y-m-d H:i:s');
      $loanappnumber = getGUIDnoHash();

      try {
        $loansettingquery = $readDB->prepare('SELECT * FROM loan_product_settings
         where sloan_product_id=:id');
        // $query->bindParam(':amt', $amount, PDO::PARAM_INT);
        $loansettingquery->bindParam(':id', $_loanproduct, PDO::PARAM_INT);
        $loansettingquery->execute();
        $rowCountw = $loansettingquery->rowCount();

        if ($rowCountw === 0) {
          $response = new Response();
          $response->setHttpStatusCode(404);
          $response->setSuccess(false);
          $response->addMessage("loan product setting does not exist found");
          $response->send();
          exit;
      }
      $rowloanproduct = $loansettingquery->fetch(PDO::FETCH_ASSOC);
      $loanminmumamt= $rowloanproduct['minmum_amount'];
      $loanmmaxmumamt= $rowloanproduct['maxmum_amount'];
      if ($loanminmumamt > $amount) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("Applied amount cannot be less than the minmum loan amount");
        $response->send();
        exit;
      }
      if ($amount > $loanmmaxmumamt) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("Applied amount cannot be greater than the maxmum loan amount");
        $response->send();
        exit;
      }

      $writeDB->beginTransaction();
      $query = $writeDB->prepare('INSERT into loan_applications
      (`loan_app_number`, `loan_product_product_id`, `members_member_id`,
       `loan_app_date`, `loan_app_amount`, `amornitization_interval`,
       `grace_period`, `tenure_period`, `reason`, `users_user_id`,
        `branches_branch_id`, `saccos_sacco_id`,`loanapplicationtype`,`loan_app_timestamp`)
      values(:loan_number, :loan_product,:account,:loan_app_date, :loan_amount,  :amonization,
      :graceperiod,:tenureperiod,:reason,:userid,:branch, :saccoid,:type,:loandatetime)');
      $query->bindParam(':loan_number', $loanappnumber, PDO::PARAM_INT);
      $query->bindParam(':loan_product', $_loanproduct, PDO::PARAM_INT);
      $query->bindParam(':account', $_memberid, PDO::PARAM_INT);
      $query->bindParam(':loan_app_date', $_date, PDO::PARAM_STR);
      $query->bindParam(':loan_amount', $amount, PDO::PARAM_INT);
      $query->bindParam(':amonization', $amornitizationinterval, PDO::PARAM_INT);
      $query->bindParam(':graceperiod', $graceperiod, PDO::PARAM_INT);
      $query->bindParam(':tenureperiod', $tenureperiod, PDO::PARAM_INT);
      $query->bindParam(':reason', $reason, PDO::PARAM_INT);
      $query->bindParam(':type', $applicationtype, PDO::PARAM_INT);
      $query->bindParam(':loandatetime', $loandatetime, PDO::PARAM_INT);
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $query->bindParam(':userid', $returned_id, PDO::PARAM_INT);
      $query->bindParam(':branch', $returned_branch_id, PDO::PARAM_INT);
      $query->execute();

      // get row count
      $rowCount = $query->rowCount();
      // check if row was actually inserted, PDO exception should have caught it if not.
      if($rowCount === 0) {
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage("failed to register group loan application");
        $response->send();
        exit;
      }

      // get last task id so we can return the Task in the json
      $lastID = $writeDB->lastInsertId();

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
      // select account properties
      $query = $readDB->prepare('select * from members, saccos where members.saccos_sacco_id = saccos.sacco_id and member_id = :account');
      $query->bindParam(':account', $_memberid, PDO::PARAM_INT);
      $query->execute();
      $rowCount = $query->rowCount();

      if ($rowCount === 0) {
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("member not found");
        $response->send();
        exit;
      }
      $row = $query->fetch(PDO::FETCH_ASSOC);

      $firstname = $row['member_fname'];
      $lastname = $row['member_lname'];
      $accountContact = $row['member_contact'];
      $saccoName = $row['sacco_short_name'];
      $saccoEmail = $row['sacco_email'];
      $accountNumber =  $row['members_account_number'];

      // create db query to get newly created - get from master db not read slave as replication may be too slow for successful read
      $query = $writeDB->prepare('select * from loan_applications where loan_app_id = :id');
      $query->bindParam(':id', $lastID, PDO::PARAM_INT);
      $query->execute();

      // get row count
      $rowCount = $query->rowCount();

      // make sure that the new task was returned
      if($rowCount === 0) {
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage("Failed to retrieve loan after creation");
        $response->send();
        exit;
      }
      // last deposit insert in the account
      $loanArray = array();

      while($row = $query->fetch(PDO::FETCH_ASSOC)):
                extract($row);
               $loanapp = array(
               "id" => $loan_app_id,
               "amount" => $loan_app_amount,
               "loanid" => $loan_app_number,
               "status" => $loan_app_status,
               "date" => $loan_app_date,
               "loanprodut" => $loan_product_product_id,
               "timestamp" => $loan_app_timestamp,
               );
               $loanArray[] = $loanapp;
              endwhile;
              // account info array

          //date and time generation
          $postdate = new DateTime();
          // set date for kampala
          $postdate->setTimezone(new DateTimeZone('Africa/Nairobi'));
          //formulate the new date
          $date = $postdate->format('Y-m-d H:i:s');

          $newAmount = number_format(($amount),0,'.',',');

        $message = "Hello, your group loan application of UGX ".$newAmount." is under review; you will be notified once the loan is processed. Loan ID ".$loanappnumber. ". Date: ".$date;
        // insert sms into the database
        insertSMSDB($writeDB, $message, $accountContact, $returned_saccoid);
        // insert email into the database
        insertEMAILDB($writeDB, $message, $saccoEmail, $returned_saccoid);
      // bundle tasks and rows returned into an array to return in the json data
      $returnData = array();
      $returnData['rows_returned'] = $rowCount;
      $returnData['grouploanapplication'] = $loanArray;
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
      $response->addMessage("failed to loan application");
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
