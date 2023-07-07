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
if (array_key_exists("loanstatus",$_GET)):
  // get settings from query string
  $loanstatus = $_GET['loanstatus'];
  //check to see if loan status in query string is not empty and is number, if not return json error
  if($loanstatus == '' || !is_string($loanstatus)):
    $response = new Response();
    $response->setHttpStatusCode(400);
    $response->setSuccess(false);
    $response->addMessage("setting cannot be blank and must be string");
    $response->send();
    exit;
  endif;

  switch ($loanstatus) {
  case 'proccessloan':
if (array_key_exists("memberid",$_GET)) {
  // get task id from query string
  $memberid = $_GET['memberid'];
  //check to see if task id in query string is not empty and is number, if not return json error
  if($memberid == '' || !is_numeric($memberid)) {
    $response = new Response();
    $response->setHttpStatusCode(400);
    $response->setSuccess(false);
    $response->addMessage("memberid cannot be blank and must be numeric");
    $response->send();
    exit;
  }

  // if request is a GET, e.g. get transaction
  if($_SERVER['REQUEST_METHOD'] === 'GET') {
    // attempt to query the database
    try {
      // create db query
      // ADD AUTH TO QUERY
      $query = $readDB->prepare('SELECT * FROM  members
        where members.member_id IN (SELECT loan_applications.members_member_id
          FROM loan_applications WHERE loan_app_status="pending")
        AND members.saccos_sacco_id = :saccoid
        AND member_type="individual"
        AND member_id=:memberid
        ');
      $query->bindParam(':memberid', $memberid, PDO::PARAM_INT);
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $query->execute();
      // get row count
      $rowCount = $query->rowCount();
      if($rowCount === 0) {
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("Data not found");
        $response->send();
        exit;
      }
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
            AND loanapplicationtype ="individual"
            AND loan_app_status="pending"
            and loan_applications.saccos_sacco_id = :saccoid order by loan_app_id DESC');
        $loanquery->bindParam(':memberid', $member_id, PDO::PARAM_INT);
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
      $returnData['member'] = $membersArray;

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
// return 404 error if endpoint not available
else {
  $response = new Response();
  $response->setHttpStatusCode(404);
  $response->setSuccess(false);
  $response->addMessage("Endpoint not found");
  $response->send();
  exit;
}
break;
case 'approveloans':
  // code...
  if (array_key_exists("memberid",$_GET)) {
    // get task id from query string
    $memberid = $_GET['memberid'];
    //check to see if task id in query string is not empty and is number, if not return json error
    if($memberid == '' || !is_numeric($memberid)) {
      $response = new Response();
      $response->setHttpStatusCode(400);
      $response->setSuccess(false);
      $response->addMessage("memberid cannot be blank and must be numeric");
      $response->send();
      exit;
    }

    // if request is a GET, e.g. get transaction
    if($_SERVER['REQUEST_METHOD'] === 'GET') {
      // attempt to query the database
      try {
        // create db query
        // ADD AUTH TO QUERY
        $query = $readDB->prepare('SELECT * FROM  members
          where members.member_id IN (SELECT loan_applications.members_member_id
          FROM loan_applications WHERE loan_app_status="processed")
          AND members.saccos_sacco_id = :saccoid
          AND member_type="individual"
          AND member_id=:memberid
          ');
        $query->bindParam(':memberid', $memberid, PDO::PARAM_INT);
        $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
        $query->execute();
        // get row count
        $rowCount = $query->rowCount();
        if($rowCount === 0) {
          // set up response for unsuccessful return
          $response = new Response();
          $response->setHttpStatusCode(404);
          $response->setSuccess(false);
          $response->addMessage("Data not found");
          $response->send();
          exit;
        }
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
              AND loanapplicationtype ="individual"
              AND loan_app_status="processed"
              and loan_applications.saccos_sacco_id = :saccoid order by loan_app_id DESC');
          $loanquery->bindParam(':memberid', $member_id, PDO::PARAM_INT);
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
        $returnData['member'] = $membersArray;

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
  // return 404 error if endpoint not available
  else {
    $response = new Response();
    $response->setHttpStatusCode(404);
    $response->setSuccess(false);
    $response->addMessage("Endpoint not found");
    $response->send();
    exit;
  }
break;
case 'rejectloans':
  // code...
  if (array_key_exists("memberid",$_GET)) {
    // get task id from query string
    $memberid = $_GET['memberid'];
    //check to see if task id in query string is not empty and is number, if not return json error
    if($memberid == '' || !is_numeric($memberid)) {
      $response = new Response();
      $response->setHttpStatusCode(400);
      $response->setSuccess(false);
      $response->addMessage("memberid cannot be blank and must be numeric");
      $response->send();
      exit;
    }

    // if request is a GET, e.g. get transaction
    if($_SERVER['REQUEST_METHOD'] === 'GET') {
      // attempt to query the database
      try {
        // create db query
        // ADD AUTH TO QUERY
        $query = $readDB->prepare('SELECT * FROM  members
          where members.member_id IN (SELECT loan_applications.members_member_id
          FROM loan_applications WHERE loan_app_status="processed")
          AND members.saccos_sacco_id = :saccoid
          AND member_type="individual"
          AND member_id=:memberid
          ');
        $query->bindParam(':memberid', $memberid, PDO::PARAM_INT);
        $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
        $query->execute();
        // get row count
        $rowCount = $query->rowCount();
        if($rowCount === 0) {
          // set up response for unsuccessful return
          $response = new Response();
          $response->setHttpStatusCode(404);
          $response->setSuccess(false);
          $response->addMessage("Data not found");
          $response->send();
          exit;
        }
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
              AND loanapplicationtype ="individual"
              AND loan_app_status="processed"
              and loan_applications.saccos_sacco_id = :saccoid order by loan_app_id DESC');
          $loanquery->bindParam(':memberid', $member_id, PDO::PARAM_INT);
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
        $returnData['member'] = $membersArray;

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
  // return 404 error if endpoint not available
  else {
    $response = new Response();
    $response->setHttpStatusCode(404);
    $response->setSuccess(false);
    $response->addMessage("Endpoint not found");
    $response->send();
    exit;
  }
break;
case 'cancelloan':
  // code...
  if (array_key_exists("memberid",$_GET)) {
    // get task id from query string
    $memberid = $_GET['memberid'];
    //check to see if task id in query string is not empty and is number, if not return json error
    if($memberid == '' || !is_numeric($memberid)) {
      $response = new Response();
      $response->setHttpStatusCode(400);
      $response->setSuccess(false);
      $response->addMessage("memberid cannot be blank and must be numeric");
      $response->send();
      exit;
    }

    // if request is a GET, e.g. get transaction
    if($_SERVER['REQUEST_METHOD'] === 'GET') {
      // attempt to query the database
      try {
        // create db query
        // ADD AUTH TO QUERY
        $query = $readDB->prepare('SELECT * FROM  members
          where members.member_id IN (SELECT loan_applications.members_member_id
          FROM loan_applications WHERE loan_app_status !="pending"
          AND loan_app_status !="disbursed")
          AND members.saccos_sacco_id = :saccoid
          AND member_type="individual"
          AND member_id=:memberid');
        $query->bindParam(':memberid', $memberid, PDO::PARAM_INT);
        $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
        $query->execute();
        // get row count
        $rowCount = $query->rowCount();
        if($rowCount === 0) {
          // set up response for unsuccessful return
          $response = new Response();
          $response->setHttpStatusCode(404);
          $response->setSuccess(false);
          $response->addMessage("Data not found");
          $response->send();
          exit;
        }
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
              AND loanapplicationtype ="individual"
              AND loan_app_status !="pending"
              AND loan_app_status !="disbursed"
              and loan_applications.saccos_sacco_id = :saccoid order by loan_app_id DESC');
          $loanquery->bindParam(':memberid', $member_id, PDO::PARAM_INT);
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
        $returnData['member'] = $membersArray;

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
  // return 404 error if endpoint not available
  else {
    $response = new Response();
    $response->setHttpStatusCode(404);
    $response->setSuccess(false);
    $response->addMessage("Endpoint not found");
    $response->send();
    exit;
  }
break;
case 'disbursedloans':
  // code...
  if (array_key_exists("memberid",$_GET)) {
    // get task id from query string
    $memberid = $_GET['memberid'];
    //check to see if task id in query string is not empty and is number, if not return json error
    if($memberid == '' || !is_numeric($memberid)) {
      $response = new Response();
      $response->setHttpStatusCode(400);
      $response->setSuccess(false);
      $response->addMessage("memberid cannot be blank and must be numeric");
      $response->send();
      exit;
    }

    // if request is a GET, e.g. get transaction
    if($_SERVER['REQUEST_METHOD'] === 'GET') {
      // attempt to query the database
      try {
        // create db query
        // ADD AUTH TO QUERY
        $query = $readDB->prepare('SELECT * FROM  members
          where members.member_id IN (SELECT loan_applications.members_member_id
          FROM loan_applications WHERE loan_app_status ="approved")
          AND members.saccos_sacco_id = :saccoid
          AND member_type="individual"
          AND member_id=:memberid
          ');
        $query->bindParam(':memberid', $memberid, PDO::PARAM_INT);
        $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
        $query->execute();
        // get row count
        $rowCount = $query->rowCount();
        if($rowCount === 0) {
          // set up response for unsuccessful return
          $response = new Response();
          $response->setHttpStatusCode(404);
          $response->setSuccess(false);
          $response->addMessage("Data not found");
          $response->send();
          exit;
        }
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
              AND loanapplicationtype ="individual"
              AND loan_app_status = "approved"
              and loan_applications.saccos_sacco_id = :saccoid order by loan_app_id DESC');
          $loanquery->bindParam(':memberid', $member_id, PDO::PARAM_INT);
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
        $returnData['member'] = $membersArray;

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
  // return 404 error if endpoint not available
  else {
    $response = new Response();
    $response->setHttpStatusCode(404);
    $response->setSuccess(false);
    $response->addMessage("Endpoint not found");
    $response->send();
    exit;
  }
break;
case 'rescheduleloans':
  // code...
  if (array_key_exists("memberid",$_GET)) {
    // get task id from query string
    $memberid = $_GET['memberid'];
    //check to see if task id in query string is not empty and is number, if not return json error
    if($memberid == '' || !is_numeric($memberid)) {
      $response = new Response();
      $response->setHttpStatusCode(400);
      $response->setSuccess(false);
      $response->addMessage("memberid cannot be blank and must be numeric");
      $response->send();
      exit;
    }

    // if request is a GET, e.g. get transaction
    if($_SERVER['REQUEST_METHOD'] === 'GET') {
      // attempt to query the database
      try {
        // create db query
        // ADD AUTH TO QUERY
        $query = $readDB->prepare('SELECT * FROM  members
          where members.member_id IN (SELECT loan_applications.members_member_id
          FROM loan_applications WHERE loan_app_status ="disbursed")
          AND members.saccos_sacco_id = :saccoid
          AND member_type="individual"
          AND member_id=:memberid');
        $query->bindParam(':memberid', $memberid, PDO::PARAM_INT);
        $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
        $query->execute();
        // get row count
        $rowCount = $query->rowCount();
        if($rowCount === 0) {
          // set up response for unsuccessful return
          $response = new Response();
          $response->setHttpStatusCode(404);
          $response->setSuccess(false);
          $response->addMessage("Data not found");
          $response->send();
          exit;
        }
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
          $loanquery = $readDB->prepare('SELECT * from loan_applications,loan_product_settings,loans_disbursed
            where loan_applications.members_member_id = :memberid
            AND NOT EXISTS(SELECT loan_payment.loan_payment_disbursedid FROM loan_payment
             WHERE loan_payment.loan_payment_disbursedid =loans_disbursed.loan_id)
             AND loan_applications.loan_product_product_id=loan_product_settings.sloan_product_id
             AND loans_disbursed.loan_applications_loan_app_id =loan_applications.loan_app_id
             AND loanapplicationtype ="individual"
             AND loan_applications.loan_app_status = "disbursed"
             AND loan_applications.saccos_sacco_id = :saccoid order by loan_app_id DESC');
          $loanquery->bindParam(':memberid', $member_id, PDO::PARAM_INT);
          $loanquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
          $loanquery->execute();

          // create array to store returned task
          $transactionArray = array();

          while($row = $loanquery->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $loan_app = array(
              "id" => $loan_id ,
              "amount" => $offered_amount,
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
        $returnData['member'] = $membersArray;

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
  // return 404 error if endpoint not available
  else {
    $response = new Response();
    $response->setHttpStatusCode(404);
    $response->setSuccess(false);
    $response->addMessage("Endpoint not found");
    $response->send();
    exit;
  }
break;
default:
$response = new Response();
$response->setHttpStatusCode(423);
$response->setSuccess(false);
$response->addMessage("service error - Attached Info");
$response->send();
exit;
}
else:
$response = new Response();
$response->setHttpStatusCode(417);
$response->setSuccess(false);
$response->addMessage("Beyond your limit not found");
$response->send();
exit;
endif;
