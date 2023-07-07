<?php

require_once('../core/initialize.php');
require_once('Database.php');
require_once('../model/Response.php');
require_once('../model/User.php');

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
// Authenticate sacco with access token
// check to see if access token is provided in the HTTP Authorization header and that the value is longer than 0 chars
// don't forget the Apache fix in .htaccess file
if(!isset($_SERVER['HTTP_AUTHORIZATION']) || strlen($_SERVER['HTTP_AUTHORIZATION']) < 1):
  $response = new Response();
  $response->setHttpStatusCode(401);
  $response->setSuccess(false);
  (!isset($_SERVER['HTTP_AUTHORIZATION']) ? $response->addMessage("Access token is missing from the header") : false);
  (strlen($_SERVER['HTTP_AUTHORIZATION']) < 1 ? $response->addMessage("Access token cannot be blank") : false);
  $response->send();
  exit;
endif;

  // get supplied access token from authorisation header - used for delete (log out) and patch (refresh)
  $accesstoken = $_SERVER['HTTP_AUTHORIZATION'];

// attempt to query the database to check token details - use write connection as it needs to be synchronous for token
try {
  // create db query to check access token is equal to the one provided
  $query = $writeDB->prepare('select user_id, access_token_expiry, user_status, saccos_sacco_id, user_login_attempts from sessions_users, users where sessions_users.users_user_id = users.user_id and access_token = :accesstoken');
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
      //check whether to pacth object
  if($_SERVER['REQUEST_METHOD'] === 'PATCH'):
    // update user
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

      // set user field updated to false initially
      $fullname = false;
      $password = false;
      $status = false;
      $contact = false;

      // create blank query fields string to append each field to
      $queryFields = "";

      // check if name exists in PATCH
      if(isset($jsonData->fullname)):
        // set name field updated to true
        $fullname = true;
        // add name field to query field string
        $queryFields .= "user_fullname = :fullname, ";
      endif;

      // check if password exists in PATCH
      if(isset($jsonData->password)):
        // set password field updated to true
        $password = true;
        // add password field to query field string
        $queryFields .= "user_password = :password, ";
      endif;

      // check if status exists in PATCH
      if(isset($jsonData->status)):
        // set status field updated to true
        $status = true;
        // add status field to query field string
        $queryFields .= "user_status = :status, ";
      endif;

      // check if contact exists in PATCH
      if(isset($jsonData->contact)):
        // set contact field updated to true
        $contact = true;
        // add contact field to query field string
        $queryFields .= "user_contact = :contact, ";
      endif;

      // remove the right hand comma and trailing space
      $queryFields = rtrim($queryFields, ", ");

      // check if any user fields supplied in JSON
      if($fullname === false && $password === false && $status === false  && $contact ===false):
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("no user fields provided");
        $response->send();
        exit;
      endif;
      // ADD AUTH TO QUERY
      // create db query to get user from database to update - use master db
      $query = $writeDB->prepare('select user_id, user_fullname, user_contact, user_email, user_password, user_status, user_role, branches_branch_id, users.saccos_sacco_id as saccoid, branch_name from users, branches where  branches.branch_id = users.branches_branch_id and user_id = :userid and users.saccos_sacco_id = :saccoid');
      $query->bindParam(':userid', $returned_id, PDO::PARAM_INT);
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $query->execute();

      // get row count
      $rowCount = $query->rowCount();

      // make sure that the user exists for a given user id
      if($rowCount === 0):
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("no user found to update");
        $response->send();
        exit;
      endif;


      // for each row returned - should be just one
      while($row = $query->fetch(PDO::FETCH_ASSOC)):
        // create new user object
        $user = new User($row['user_id'], $row['user_fullname'], $row['user_email'], $row['user_password'], $row['user_contact'], $row['user_status'], $row['user_role'], $row['branches_branch_id'],$row['saccoid'], $row['branch_name']);
      endwhile;

      // ADD AUTH TO QUERY
      // create the query string including any query fields
      $queryString = "UPDATE users set ".$queryFields." where user_id = :userid";
      // prepare the query
      $query = $writeDB->prepare($queryString);

      // if name has been provided
      if($fullname === true):
        // set user object name to given value (checks for valid input)
        $user->setName($jsonData->fullname);
        // get the value back as the object could be handling the return of the value differently to
        // what was provided
        $up_name = $user->getName();
        // bind the parameter of the new value from the object to the query (prevents SQL injection)
        $query->bindParam(':fullname', $up_name, PDO::PARAM_STR);
      endif;

      // if password has been provided
      if($password === true):
        // set user object address to given value (checks for valid input)
        $user->setPassword($jsonData->password);
        // get the value back as the object could be handling the return of the value differently to
        // what was provided
        $up_password = $user->getPassword();
        // hash the password using alog
        $_hashed_password = password_hash($up_password, PASSWORD_DEFAULT);
        // bind the parameter of the new value from the object to the query (prevents SQL injection)
        $query->bindParam(':password', $_hashed_password, PDO::PARAM_STR);
      endif;

      // if username has been provided
      if($status === true):
        // set user object code to given value (checks for valid input)
        $user->setStatus($jsonData->status);
        // get the value back as the object could be handling the return of the value differently to
        // what was provided
        $up_status = $user->getStatus();
        // bind the parameter of the new value from the object to the query (prevents SQL injection)
        $query->bindParam(':status', $up_status, PDO::PARAM_STR);
      endif;

      // if status has been provided
      if($contact === true):
        // set user object status to given value (checks for valid input)
        $user->setContact($jsonData->contact);
        // get the value back as the object could be handling the return of the value differently to
        // what was provided
        $up_contact = $user->getContact();
        // bind the parameter of the new value from the object to the query (prevents SQL injection)
        $query->bindParam(':contact', $up_contact, PDO::PARAM_STR);
      endif;

      // bind the user id returned
      $query->bindParam(':userid', $returned_id, PDO::PARAM_INT);
      // run the query
    	$query->execute();

      // get affected row count
      $rowCount = $query->rowCount();

      // check if row was actually updated, could be that the given values are the same as the stored values
      if($rowCount === 0):
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("user not updated - given values may be the same as the stored values");
        $response->send();
        exit;
      endif;
      // ADD AUTH TO QUERY
      // create db query to return the newly edited user - connect to master database
      $query = $writeDB->prepare('select user_id, user_fullname, user_email, user_password, user_contact, user_status, user_role, branches_branch_id,branch_name, users.saccos_sacco_id as saccoid from users, branches where branches.branch_id = users.branches_branch_id and user_id = :userid and users.saccos_sacco_id = :saccoid');
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $query->bindParam(':userid', $returned_id, PDO::PARAM_INT);
      $query->execute();
      // get row count
      $rowCount = $query->rowCount();

      // check if user was found
      if($rowCount === 0):
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("no user found");
        $response->send();
        exit;
      endif;
      // create user array to store returned users
      $userArray = array();
      // for each row returned
      while($row = $query->fetch(PDO::FETCH_ASSOC)):
      // create new user object for each row returned
      $user = new User($row['user_id'], $row['user_fullname'], $row['user_email'], $row['user_password']= null, $row['user_contact'], $row['user_status'], $row['user_role'], $row['branches_branch_id'],$row['saccoid'], $row['branch_name']);
        // create user and store in array for return in json data
        $userArray[] = $user->returnUserAsArray();
      endwhile;
      // bundle user and rows returned into an array to return in the json data
      $returnData = array();
      $returnData['rows_returned'] = $rowCount;
      $returnData['user'] = $userArray;

      // set up response for successful return
      $response = new Response();
      $response->setHttpStatusCode(200);
      $response->setSuccess(true);
      $response->addMessage("user has been updated");
      $response->setData($returnData);
      $response->send();
      exit;
    }
    catch(UserException $ex) {
      $response = new Response();
      $response->setHttpStatusCode(400);
      $response->setSuccess(false);
      $response->addMessage($ex->getMessage());
      $response->send();
      exit;
    }
    // if error with sql query return a json error
    catch(PDOException $ex) {
      error_log("Database Query Error: ".$ex, 0);
      $response = new Response();
      $response->setHttpStatusCode(500);
      $response->setSuccess(false);
      $response->addMessage("Failed to update user - check your data for errors" );
      $response->send();
      exit;
    }
elseif($_SERVER['REQUEST_METHOD'] === 'GET'):
  // attempt to query the database
  try {
    // create db query
    // ADD AUTH TO QUERY
    $query = $writeDB->prepare('select user_id, user_fullname, user_email, user_password, user_contact, user_status, user_role, branches_branch_id,branch_name,sacco_name, users.saccos_sacco_id as saccoid from users, branches,saccos where branches.branch_id = users.branches_branch_id and user_id = :userid and users.saccos_sacco_id = :saccoid and users.saccos_sacco_id = saccos.sacco_id');
    $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
    $query->bindParam(':userid', $returned_id, PDO::PARAM_INT);
    $query->execute();


    // get row count
    $rowCount = $query->rowCount();
    // create user array to store returned user object
    $userArray = array();

    if($rowCount === 0):
      // set up response for unsuccessful return
      $response = new Response();
      $response->setHttpStatusCode(404);
      $response->setSuccess(false);
      $response->addMessage("user not found");
      $response->send();
      exit;
    endif;

    // for each row returned
    while($row = $query->fetch(PDO::FETCH_ASSOC)):
      // create new User object for each row
      $user = new User($row['user_id'], $row['user_fullname'], $row['user_email'], $row['user_password']= null, $row['user_contact'], $row['user_status'], $row['user_role'], $row['branches_branch_id'],$row['saccoid'], $row['branch_name']);
      // create User and store in array for return in json data
      $userArray[] = $user->returnUserAsArray();
      $sacconame = $row['sacco_name'];
    endwhile;

    // bundle users and rows returned into an array to return in the json data
    $returnData = array();
    $returnData['rows_returned'] = $rowCount;
    $returnData['sacconame'] = $sacconame;
    $returnData['user'] = $userArray;
    // set up response for successful return
    $response = new Response();
    $response->setHttpStatusCode(200);
    $response->setSuccess(true);
    $response->toCache(true);
    $response->setData($returnData);
    $response->send();
    exit;
  }
  // if error with sql query return a json error
  catch(UserException $ex) {
    $response = new Response();
    $response->setHttpStatusCode(500);
    $response->setSuccess(false);
    $response->addMessage($ex->getMessage());
    $response->send();
    exit;
  }
  catch(PDOException $ex) {
    error_log("Database Query Error: ".$ex, 0);
    $response = new Response();
    $response->setHttpStatusCode(500);
    $response->setSuccess(false);
    $response->addMessage("Failed to get User");
    $response->send();
    exit;
  }
else:
  $response = new Response();
  $response->setHttpStatusCode(404);
  $response->setSuccess(false);
  $response->addMessage("Endpoint not found");
  $response->send();
  exit;
endif;
