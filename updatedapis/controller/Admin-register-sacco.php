<?php
require_once('../core/initialize.php');
require_once('Database.php');
require_once('../model/Response.php');
require_once('../core/classes/SaccoID.php');


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
  $query = $writeDB->prepare('SELECT admin_id, access_token_expiry, admin_status, admin_admin_id,
  admin_fullname,user_login_attempts from sessions_general_admin, general_admin
  where sessions_general_admin.admin_admin_id = general_admin.admin_id and access_token = :accesstoken');
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

  //save returned details into variables
  $returned_id = $row['admin_id'];
  $returned_name = $row['admin_fullname'];
  $returned_accesstokenexpiry = $row['access_token_expiry'];
  $returned_active = $row['admin_status'];
  $returned_loginattempts = $row['user_login_attempts'];
  $returned_saccoid = $row['admin_admin_id'];
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
  $response->addMessage("There was an issue authenticating - please try again $ex");
  $response->send();
  exit;
}

// check if taskid is in the url e.g. /tasks/1
if (array_key_exists("memberid",$_GET)) { }
elseif(empty($_GET)) {
  // if request is a GET e.g. get members
  if($_SERVER['REQUEST_METHOD'] === 'GET') {
    // attempt to query the database
    try {
      // ADD AUTH TO QUERY
      // create db query
      $query = $readDB->prepare('SELECT `sacco_id`, `sacco_name`, `sacco_contact`,
        `sacco_email`, `sacco_password`, `sacco_code`, `sacco_status`,
         `sacco_short_name`, `sacco_reg_date`, `sacco_logo`, `sacco_login_attempts`,
          `sacco_address`, `sacco_sms_status`, `sacco_email_status`, `sacco_notification_emails` FROM saccos');
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $query->execute();
      // get row count
      $rowCount = $query->rowCount();
      // create  array to store returned s
      $saccosArray = array();
      // for each row returned
      while($row = $query->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        // create new  object for each row
        $saccos = array(
          "id"=>$sacco_id,
          "name"=>$sacco_name,
          "email"=>$sacco_email,
          "address"=>$sacco_address,
          "contact"=>$sacco_contact,
          "code"=>$sacco_code,
          "code"=>$sacco_code,
          "sacco_status"=>$sacco_email_status,

       );
        // create  and store in array for return in json data
        $saccosArray[] = $saccos;

      }

      // bundle s and rows returned into an array to return in the json data
      $returnData = array();
      $returnData['rows_returned'] = $rowCount;
      $returnData['saccos'] = $saccosArray;

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
    catch(PDOException $ex) {
      $response = new Response();
      $response->setHttpStatusCode(500);
      $response->setSuccess(false);
      $response->addMessage($ex);
      $response->send();
      exit;
    }
    catch(PDOException $ex) {
      error_log("Database Query Error: ".$ex, 0);
      $response = new Response();
      $response->setHttpStatusCode(500);
      $response->setSuccess(false);
      $response->addMessage("Failed to get saccos");
      $response->send();
      exit;
    }
  }
  // if any other request method apart from GET or POST is used then return 405 method not allowed
  if($_SERVER['REQUEST_METHOD'] === 'POST') {
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

    // get POST request body as the POSTed data will be JSON format
    $rawPostData = file_get_contents('php://input');

    if(!$jsonData = json_decode($rawPostData)):
      // set up response for unsuccessful request
      $response = new Response();
      $response->setHttpStatusCode(400);
      $response->setSuccess(false);
      $response->addMessage("Request body is not valid JSON");
      $response->send();
      exit;
    endif;

    // check if post request contains saccoemail, sacconame and saccopassword in body as they are mandatory
    if(!isset($jsonData->name) || !isset($jsonData->email) || !isset($jsonData->password)
    || !isset($jsonData->contact)|| !isset($jsonData->status)|| !isset($jsonData->address)):
      $response = new Response();
      $response->setHttpStatusCode(400);
      $response->setSuccess(false);
      // add message to message array where necessary
      (!isset($jsonData->name) ? $response->addMessage("sacco name  not supplied") : false);
      (!isset($jsonData->email) ? $response->addMessage("sacco email not supplied") : false);
      (!isset($jsonData->shortname) ? $response->addMessage("sacco shortname not supplied") : false);
      (!isset($jsonData->contact) ? $response->addMessage("sacco contact not supplied") : false);
      (!isset($jsonData->password) ? $response->addMessage("Password not supplied") : false);
      (!isset($jsonData->status) ? $response->addMessage("sacco status not supplied") : false);
      (!isset($jsonData->address) ? $response->addMessage("sacco address not supplied") : false);
      $response->send();
      exit;
    endif;

    // check to make sure that sacco name email and password are not empty and less than 255 long
    if(strlen($jsonData->name) < 1 || strlen($jsonData->name) > 255 || strlen($jsonData->email) < 1
    || strlen($jsonData->email) > 255 || strlen($jsonData->shortname) < 1 || strlen($jsonData->shortname)  >100
    || strlen($jsonData->contact) < 1 || strlen($jsonData->contact)  >15 || !is_numeric($jsonData->contact)  >15
    || strlen($jsonData->password) < 1 || strlen($jsonData->password) > 100
    || strlen($jsonData->status) < 6 || strlen($jsonData->status) > 8
    || strlen($jsonData->address) < 1 || strlen($jsonData->address) > 300):
      $response = new Response();
      $response->setHttpStatusCode(400);
      $response->setSuccess(false);
      (strlen($jsonData->name) < 1 ? $response->addMessage("sacco name cannot be blank") : false);
      (strlen($jsonData->name) > 255 ? $response->addMessage("sacco name cannot be greater than 255 characters") : false);
      (strlen($jsonData->email) < 1 ? $response->addMessage("Email cannot be blank") : false);
      (strlen($jsonData->email) > 255 ? $response->addMessage("Email cannot be greater than 255 characters") : false);
      (strlen($jsonData->shortname) < 1 ? $response->addMessage("shortname cannot be blank") : false);
      (strlen($jsonData->shortname) > 100 ? $response->addMessage("shortname cannot be greater than 255 characters") : false);
      (strlen($jsonData->status) < 6 ? $response->addMessage("sacco status cannot be less than 6 characters") : false);
      (strlen($jsonData->status) > 8 ? $response->addMessage("sacco status cannot be greater than 8 characters") : false);
      (strlen($jsonData->contact) < 1 ? $response->addMessage("Sacco contact cannot be blank") : false);
      (strlen($jsonData->contact) > 15 ? $response->addMessage("Sacco contact cannot be greater than 15 characters") : false);
      (!is_numeric($jsonData->contact) ? $response->addMessage("Sacco contact must be numerical") : false);
      (strlen($jsonData->password) < 1 ? $response->addMessage("Password cannot be blank") : false);
      (strlen($jsonData->password) > 100 ? $response->addMessage("Password cannot be greater than 100 characters") : false);
      (strlen($jsonData->address) < 1 ? $response->addMessage("address cannot be blank") : false);
      (strlen($jsonData->address) > 300 ? $response->addMessage("address cannot be greater than 300 characters") : false);
      $response->send();
      exit;
    endif;
    if (!filter_var($jsonData->email, FILTER_VALIDATE_EMAIL) || !preg_match("@[0-9]@",$jsonData->password) || !preg_match("@[A-Z]@",$jsonData->password) || !preg_match("@[^\w]@",$jsonData->password) || !preg_match("@[a-z]@",$jsonData->password)):
      $response = new Response();
      $response->setHttpStatusCode(400);
      $response->setSuccess(false);
      (!filter_var($jsonData->email, FILTER_VALIDATE_EMAIL) ? $response->addMessage("Invalid email address") : false);
      (!preg_match("@[0-9]@",$jsonData->password) ? $response->addMessage("Password must contain a number") : false);
      (!preg_match("@[A-Z]@",$jsonData->password) ? $response->addMessage("Password must include a uppercase character") : false);
      (!preg_match("@[^\w]@",$jsonData->password) ? $response->addMessage("Password must include a special character") : false);
      (!preg_match("@[a-z]@",$jsonData->password) ? $response->addMessage("Password must include a lowercase character "): false);
      $response->send();
      exit;
    endif;

    if(isset($jsonData->status) ? (strtolower($jsonData->status) !== 'active' && strtolower($jsonData->status) !== 'inactive') : null) {
      $response = new Response();
      $response->addMessage("Wrong status, it should be active or inactive");
      $response->setHttpStatusCode(400);
      $response->setSuccess(false);
      $response->send();
      exit;
    }
    // trim any leading and trailing blank spaces from email and sacco only - password may contain a leading or trailing space
    $_name = trim($jsonData->name);
    $_email = trim($jsonData->email);
    $_password = $jsonData->password;
    $_contact = $jsonData->contact;
    $_status = $jsonData->status;
    $_shortname = $jsonData->shortname;
    $_address = $jsonData->address;
    $saccocode = new Sacconumber();
    $_code = $saccocode->create_sacco_code();

    try {
      // create db query
      $query = $writeDB->prepare('SELECT sacco_id from saccos where sacco_email = :email');
      $query->bindParam(':email', $_email, PDO::PARAM_STR);
      $query->execute();

      // get row count
      $rowCount = $query->rowCount();

      if($rowCount !== 0):
        // set up response for username already exists
        $response = new Response();
        $response->setHttpStatusCode(409);
        $response->setSuccess(false);
        $response->addMessage("sacco already exists");
        $response->send();
        exit;
      endif;

      // hash the password to store in the DB as plain text password stored in DB is bad practice
      $_hashed_password = password_hash($_password, PASSWORD_DEFAULT);

      // create db query to create user
      $query = $writeDB->prepare('INSERT into saccos (sacco_name, sacco_email,sacco_code,
        sacco_status,sacco_contact,sacco_short_name,sacco_address,
         sacco_password)
      values (:name, :email,:saccocode, :status, :contact,:shortname,:address,:password)');
      $query->bindParam(':name', $_name, PDO::PARAM_STR);
      $query->bindParam(':saccocode', $_code, PDO::PARAM_STR);
      $query->bindParam(':email', $_email, PDO::PARAM_STR);
      $query->bindParam(':contact', $_contact, PDO::PARAM_STR);
      $query->bindParam(':status', $_status, PDO::PARAM_STR);
      $query->bindParam(':shortname', $_shortname, PDO::PARAM_STR);
      $query->bindParam(':address', $_address, PDO::PARAM_STR);
      $query->bindParam(':password', $_hashed_password, PDO::PARAM_STR);
      $query->execute();
      // get row count
      $rowCount = $query->rowCount();

      if($rowCount === 0):
        // set up response for error
        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage("There was an error creating the user account - please try again");
        $response->send();
        exit;
      endif;

      // $message = "Dear ".$_name.", Welcome to iRembo Finance, your Login password is  ".$defaultpassword. " and Transaction Pincode is ".$pincode."\nPlease DO NOT share this information with anyone and nobody from iRembo Finance will ask for this information. Kindly update your pin and password upon login. For more information contact us a support@irembofinance.com.\nUse the link below to login \n https://live.irembofinance.com/auth";
      // // insert email into the database
      // insertEMAILDB($writeDB, $message, $username, $returned_saccoid);

      $response = new Response();
      $response->setHttpStatusCode(201);
      $response->setSuccess(true);
      $response->addMessage("sacco has been created");
      // $response->setData($rowCount);
      $response->send();
      exit;
  }
  catch(PDOException $ex) {
    $response = new Response();
    $response->setHttpStatusCode(500);
    $response->setSuccess(false);
    $response->addMessage($ex);
    $response->send();
    exit;
  }
}
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
