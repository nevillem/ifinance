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
if (array_key_exists("collateralid",$_GET)) {
  // get task id from query string
  $collateralid = $_GET['collateralid'];
  //check to see if task id in query string is not empty and is number, if not return json error
  if($collateralid == '' || !is_numeric($collateralid)) {
    $response = new Response();
    $response->setHttpStatusCode(400);
    $response->setSuccess(false);
    $response->addMessage("collateral cannot be blank or must be numeric");
    $response->send();
    exit;
  }

  // if request is a GET, e.g. get transaction
  if($_SERVER['REQUEST_METHOD'] === 'GET') {
    // attempt to query the database
    try {
      // create db query
      // ADD AUTH TO QUERY

      $query = $readDB->prepare('SELECT `collateralid`, `collateral_name`, `collateral_member_id`,
         `registration_serial_no`, `collateral_value`, `extra_notes`,`member_fname`, `member_mname`,
         `member_lname`, `member_contact`, `members_account_number` from collaterals_tb, members
         WHERE collaterals_tb.collateral_member_id = members.member_id
         AND collaterals_tb.collateralid=:collateralid
         AND collaterals_tb.collateral_saccoid = :saccoid');
      $query->bindParam(':collateralid', $collateralid, PDO::PARAM_INT);
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
  		$query->execute();

      // get row count
      $rowCount = $query->rowCount();

      if($rowCount === 0) {
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("collateral not found");
        $response->send();
        exit;
      }

      // create array to store returned task
      $collateralArray = array();

      while($row = $query->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
       $collateral = array(
         "id" => $collateralid,
         "name" => $collateral_name,
         "account" => $members_account_number,
         "firstname" => $member_fname,
         "midlename" => $member_mname,
         "lastname" => $member_lname,
         "registrationno" => $registration_serial_no,
         "valueprice" => $collateral_value,
         "notes" => $extra_notes,
       );
       $collateralArray[] = $collateral;
      }

      // bundle rows returned into an array to return in the json data
      $returnData = array();
      $returnData['rows_returned'] = $rowCount;
      $returnData['collateral'] = $collateralArray;

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
      $query = $writeDB->prepare('DELETE from collaterals_tb where collateralid = :collateralid');
      $query->bindParam(':collateralid', $collateralid, PDO::PARAM_INT);
      $query->execute();

      // get row count
      $rowCount = $query->rowCount();

      if($rowCount === 0):
              // set up response for unsuccessful return
              $response = new Response();
              $response->setHttpStatusCode(404);
              $response->setSuccess(false);
              $response->addMessage("collateral not found");
              $response->send();
              exit;
      else:
              $response = new Response();
              $response->setHttpStatusCode(200);
              $response->setSuccess(true);
              $response->addMessage("collateral deleted");
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
      $response->addMessage("Failed to delete collateral- Attached Info");
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
            $memberUpdate = false;
            $collateralnameUpdate=false;
            $registrationnoUpdate=false;
            $valueUpdate=false;

            // create blank query fields string to append each field to
            $queryFields = "";

            // check if name exists in PATCH
            if(isset($jsonData->member)):
              // set title field updated to true
              $memberUpdate = true;
              // add name field to query field string
              $queryFields .= "collateral_member_id = :member, ";
            endif;
            // check if name exists in PATCH
            if(isset($jsonData->collateralname)):
              // set title field updated to true
              $collateralnameUpdate = true;
              // add name field to query field string
              $queryFields .= "collateral_name = :collateralname, ";
            endif;
            // check if name exists in PATCH
            if(isset($jsonData->registrationno)):
              // set title field updated to true
              $registrationnoUpdate = true;
              // add name field to query field string
              $queryFields .= "registration_serial_no = :registrationno, ";
            endif;
            // check if name exists in PATCH
            if(isset($jsonData->valueprice)):
              // set title field updated to true
              $valueUpdate = true;
              // add name field to query field string
              $queryFields .= "collateral_value = :valueprice, ";
            endif;
            // remove the right hand comma and trailing space
            $queryFields = rtrim($queryFields, ", ");

            // check if any branch fields supplied in JSON
            if($memberUpdate === false && $collateralnameUpdate === false && $registrationnoUpdate === false&& $valueUpdate === false):
              $response = new Response();
              $response->setHttpStatusCode(400);
              $response->setSuccess(false);
              $response->addMessage("No fields provided");
              $response->send();
              exit;
            endif;
            // ADD AUTH TO QUERY
            // create db query to get branch from database to update - use master db
            $query = $writeDB->prepare('select * from collaterals_tb where collateralid = :id');
            $query->bindParam(':id', $collateralid, PDO::PARAM_INT);
            $query->execute();

            // get row count
            $rowCount = $query->rowCount();

            // make sure that the branch exists for a given branch id
            if($rowCount === 0):
              // set up response for unsuccessful return
              $response = new Response();
              $response->setHttpStatusCode(404);
              $response->setSuccess(false);
              $response->addMessage("No collateral found to update");
              $response->send();
              exit;
            endif;

            // create the query string including any query fields
            $queryString = "UPDATE collaterals_tb set ".$queryFields." where collateralid = :id";
            // prepare the query
            $query = $writeDB->prepare($queryString);

            // if name has been provided
            if($memberUpdate === true):
              // bind the parameter of the new value from the object to the query (prevents SQL injection)
              $query->bindParam(':member', $jsonData->member, PDO::PARAM_STR);
            endif;
            // if name has been provided
            if($collateralnameUpdate === true):
              // bind the parameter of the new value from the object to the query (prevents SQL injection)
              $query->bindParam(':collateralname', $jsonData->collateralname, PDO::PARAM_STR);
            endif;
            // if name has been provided
            if($registrationnoUpdate === true):
              // bind the parameter of the new value from the object to the query (prevents SQL injection)
              $query->bindParam(':registrationno', $jsonData->registrationno, PDO::PARAM_STR);
            endif;
            // if name has been provided
            if($valueUpdate === true):
              // bind the parameter of the new value from the object to the query (prevents SQL injection)
              $query->bindParam(':valueprice', $jsonData->valueprice, PDO::PARAM_STR);
            endif;
            // bind the Branch id provided in the query string
            $query->bindParam(':id', $collateralid, PDO::PARAM_INT);
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
              $response->addMessage("collateral not updated - given values may be the same as the stored values");
              $response->send();
              exit;
            endif;

            // ADD AUTH TO QUERY
            // create db query to return the newly edited branch - connect to master database
            $query = $writeDB->prepare('select * from collaterals_tb where collateralid = :collateralid');
            $query->bindParam(':collateralid', $collateralid, PDO::PARAM_INT);
            $query->execute();

            // get row count
            $rowCount = $query->rowCount();

            // check if branch was found
            if($rowCount === 0):
              // set up response for unsuccessful return
              $response = new Response();
              $response->setHttpStatusCode(404);
              $response->setSuccess(false);
              $response->addMessage("No collateral found");
              $response->send();
              exit;
            endif;
            // create branch array to store returned branches
            $collateralArray = array();

            while($row = $query->fetch(PDO::FETCH_ASSOC)) {
              extract($row);

              $collateral = array(
                "id" => $collateralid,
                "name" => $collateral_name,
                "registrationno" => $registration_serial_no,
                "valueprice" => $collateral_value,
                "notes" => $extra_notes
              );
              $collateralArray[] = $collateral;
            }
            // bundle branch and rows returned into an array to return in the json data
            $returnData = array();
            $returnData['rows_returned'] = $rowCount;
            $returnData['collateral'] = $collateralArray;

            // set up response for successful return
            $response = new Response();
            $response->setHttpStatusCode(201);
            $response->setSuccess(true);
            $response->addMessage("Collateral updated");
            $response->setData($returnData);
            $response->send();
            exit;
          } catch (PDOException $ex) {
            // set up response for unsuccessful request
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage("error updating collateral");
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
      $query = $readDB->prepare('SELECT * FROM members
         WHERE member_id  IN(SELECT collaterals_tb.collateral_member_id FROM collaterals_tb
           WHERE members.member_id= collaterals_tb.collateral_member_id)
           AND saccos_sacco_id   = :saccoid AND member_type="individual"');
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

        $collateralquery = $readDB->prepare('SELECT `collateralid`, `collateral_name`, `collateral_member_id`,
           `registration_serial_no`, `collateral_value`, `extra_notes` from collaterals_tb
           WHERE collaterals_tb.collateral_member_id = :id
           AND collaterals_tb.collateral_saccoid = :saccoid');
        $collateralquery->bindParam(':id', $member_id, PDO::PARAM_INT);
        $collateralquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
        $collateralquery->execute();

        // create array to store returned task
        $collateralArray = array();

        while($row = $collateralquery->fetch(PDO::FETCH_ASSOC)) {
          extract($row);
          $collateral = array(
            "id" => $collateralid,
            "name" => $collateral_name,
            "registrationno" => $registration_serial_no,
            "valueprice" => $collateral_value,
            "notes" => $extra_notes,
          );
         $collateralArray[] = $collateral;
        }
       $members['collaterals'] = $collateralArray;
       $membersArray[] = $members;
      }

      // bundle rows returned into an array to return in the json data
      $returnData = array();
      $returnData['rows_returned'] = $rowCount;
      $returnData['members'] = $membersArray;

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
      $response->addMessage("Failed to get collateral".$ex);
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
      if(!isset($jsonData->memberid) || !is_numeric($jsonData->memberid)||empty($jsonData->memberid)
      || !isset($jsonData->collateralname) || empty($jsonData->collateralname)
      || !isset($jsonData->serialnumber) || empty($jsonData->serialnumber)
      || !isset($jsonData->valueprice) || empty($jsonData->valueprice)||!is_numeric($jsonData->valueprice)
       ) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        (!isset($jsonData->memberid) ? $response->addMessage("member field is mandatory and must be provided") : false);
        (empty($jsonData->memberid) ? $response->addMessage("member field must not be empty") : false);
        (!is_numeric($jsonData->memberid) ? $response->addMessage("member id field must be numeric") : false);
        (!isset($jsonData->collateralname) ? $response->addMessage("collateral name field is mandatory and must be provided") : false);
        (empty($jsonData->collateralname) ? $response->addMessage("collateral name field must not be empty") : false);
        (!isset($jsonData->serialnumber) ? $response->addMessage("serial number field is mandatory and must be provided") : false);
        (empty($jsonData->serialnumber) ? $response->addMessage("serial number field must not be empty") : false);
        (!isset($jsonData->valueprice) ? $response->addMessage("value price field is mandatory and must be provided") : false);
        (empty($jsonData->valueprice) ? $response->addMessage("value price field must not be empty") : false);
        (!is_numeric($jsonData->valueprice) ? $response->addMessage("value price field value must be numeric") : false);
        $response->send();
        exit;
      }

      $_memberid = (int)$jsonData->memberid;
      $_collateralname = $jsonData->collateralname;
      $_serialnumber = $jsonData->serialnumber;
      $_amount = (int)$jsonData->valueprice;
      $_notes = $jsonData->entranotice;
      try {
        $query = $readDB->prepare('SELECT * from collaterals_tb where registration_serial_no = :serialnumber AND
        collateral_saccoid=:saccoid');
        $query->bindParam(':serialnumber', $_serialnumber, PDO::PARAM_INT);
        $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
        $query->execute();
        $rowCount = $query->rowCount();

        if ($rowCount !== 0) {
          $response = new Response();
          $response->setHttpStatusCode(409);
          $response->setSuccess(false);
          $response->addMessage("member collateral already exists");
          $response->send();
          exit;
        }
      $writeDB->beginTransaction();
      $query = $writeDB->prepare('INSERT into collaterals_tb
      (`collateral_name`, `collateral_member_id`,
         `registration_serial_no`, `collateral_value`, `extra_notes`, `collateral_saccoid`)
      values(:name, :member,:registrationno,:amount, :notes, :saccoid)');
      $query->bindParam(':name', $_collateralname, PDO::PARAM_INT);
      $query->bindParam(':member', $_memberid, PDO::PARAM_INT);
      $query->bindParam(':registrationno', $_serialnumber, PDO::PARAM_INT);
      $query->bindParam(':amount', $_amount, PDO::PARAM_INT);
      $query->bindParam(':notes', $_notes, PDO::PARAM_INT);
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $query->execute();

      // get row count
      $rowCount = $query->rowCount();
      // check if row was actually inserted, PDO exception should have caught it if not.
      if($rowCount === 0) {
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage("failed to collateral");
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
        $response->addMessage("There was an issue making the saving collateral".$ex);
        $response->send();
        exit;
      }

      $query = $writeDB->prepare('SELECT `collateralid`, `collateral_name`, `collateral_member_id`,
         `registration_serial_no`, `collateral_value`, `extra_notes`,`member_fname`, `member_mname`,
         `member_lname`, `member_contact`, `members_account_number` from collaterals_tb, members
         WHERE collaterals_tb.collateral_member_id = members.member_id
         AND collaterals_tb.collateralid=:id');
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
    $collateralArray = array();
      while($row = $query->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $collateral = array(
          "id" => $collateralid,
          "name" => $collateral_name,
          "account" => $members_account_number,
          "firstname" => $member_fname,
          "midlename" => $member_mname,
          "lastname" => $member_lname,
          "registrationno" => $registration_serial_no,
          "valueprice" => $collateral_value,
          "notes" => $extra_notes,
      );
        $collateralArray[] = $collateral;
      }
      // bundle branch and rows returned into an array to return in the json data
      $returnData = array();
      $returnData['rows_returned'] = $rowCount;
      $returnData['collateral'] = $collateralArray;

      // set up response for successful return
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
      $response->addMessage("failed to save collateral");
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
