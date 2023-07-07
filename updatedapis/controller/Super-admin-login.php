<?php
require_once('../core/initialize.php');
require_once('Database.php');
require_once('../model/Response.php');

// note: don't cache login or token http requests/responses
// (our response model defaults to no cache unless specifically set)
// attempt to set up connections to db connections
try {
  $writeDB = DB::connectWriteDB();
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
// check if sessionid is in the url e.g. /usersession/1
if (array_key_exists("sessionid",$_GET)):
  // get sessions id from query string
  $sessionid = $_GET['sessionid'];

  // check to see if sessions id in query string is not empty and is number, if not return json error
  if($sessionid == '' || !is_numeric($sessionid)):
    $response = new Response();
    $response->setHttpStatusCode(400);
    $response->setSuccess(false);
    ($sessionid == '' ? $response->addMessage("Session ID cannot be blank") : false);
    (!is_numeric($sessionid) ? $response->addMessage("Session ID must be numeric") : false);
    $response->send();
    exit;
  endif;

  // check to see if access token is provided in the HTTP Authorization header and that the value is longer than 0 chars
  // 401 error is for authentication failed or has not yet been provided
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
  // if request is a DELETE, e.g. delete session
  if($_SERVER['REQUEST_METHOD'] === 'DELETE'):
    // attempt to query the database to check token details - use write connection as it needs to be synchronous for token
    try {
      // create db query to delete session where access token is equal to the one provided (leave other sessions active)
      // doesn't matter about if access token has expired as we are deleting the session
      $query = $writeDB->prepare('delete from sessions_general_admin where session_id = :sessionid
      and access_token = :accesstoken');
      $query->bindParam(':sessionid', $sessionid, PDO::PARAM_INT);
      $query->bindParam(':accesstoken', $accesstoken, PDO::PARAM_STR);
      $query->execute();

      // get row count
      $rowCount = $query->rowCount();

      if($rowCount === 0):
        // set up response for unsuccessful log out response
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("Failed to log out of this session using access token provided");
        $response->send();
        exit;
      endif;

      // build response data array which contains the session id that has been deleted (logged out)
      $returnData = array();
      $returnData['session_id'] = intval($sessionid);
      // send successful response for log out
      $response = new Response();
      $response->setHttpStatusCode(200);
      $response->setSuccess(true);
      $response->setData($returnData);
      $response->send();
      exit;
    }
    catch(PDOException $ex) {
      $response = new Response();
      $response->setHttpStatusCode(500);
      $response->setSuccess(false);
      $response->addMessage("There was an issue logging out - please try again");
      $response->send();
      exit;
    }
  // if request is a PATCH, e.g. renew access token
  elseif($_SERVER['REQUEST_METHOD'] === 'PATCH'):

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
    $rawPatchdata = file_get_contents('php://input');

    if(!$jsonData = json_decode($rawPatchdata)):
      // set up response for unsuccessful request
      $response = new Response();
      $response->setHttpStatusCode(400);
      $response->setSuccess(false);
      $response->addMessage("Request body is not valid JSON");
      $response->send();
      exit;
    endif;

    // check if patch request contains access token
    if(!isset($jsonData->refresh_token) || strlen($jsonData->refresh_token) < 1):
      $response = new Response();
      $response->setHttpStatusCode(400);
      $response->setSuccess(false);
      (!isset($jsonData->refresh_token) ? $response->addMessage("Refresh Token not supplied") : false);
      (strlen($jsonData->refresh_token) < 1 ? $response->addMessage("Refresh Token cannot be blank") : false);
      $response->send();
      exit;
    endif;

    // attempt to query the database to check token details - use write connection as it needs to be synchronous for token
    try {

      $refreshtoken = $jsonData->refresh_token;

      // get user record for provided session id, access AND refresh token
      // create db query to retrieve user details from provided access and refresh token
      $query = $writeDB->prepare('SELECT sessions_general_admin.session_id as sessionid,
         sessions_general_admin.admin_admin_id as userid, access_token, refresh_token, user_status,
          user_login_attempts, access_token_expiry, refresh_token_expiry from sessions, general_admin
           where general_admin.admin_id = sessions_general_admin.admin_admin_id
           and sessions_general_admin.session_id = :sessionid
           and sessions_general_admin.access_token = :accesstoken
           and sessions_general_admin.refresh_token = :refreshtoken');
      $query->bindParam(':sessionid', $sessionid, PDO::PARAM_INT);
      $query->bindParam(':accesstoken', $accesstoken, PDO::PARAM_STR);
      $query->bindParam(':refreshtoken', $refreshtoken, PDO::PARAM_STR);
      $query->execute();

      // get row count
      $rowCount = $query->rowCount();

      if($rowCount === 0):
        // set up response for unsuccessful access token refresh attempt
        $response = new Response();
        $response->setHttpStatusCode(401);
        $response->setSuccess(false);
        $response->addMessage("Access Token or Refresh Token is incorrect for session id");
        $response->send();
        exit;
      endif;

      // get returned row
      $row = $query->fetch(PDO::FETCH_ASSOC);

      // save returned details into variables
      $returned_sessionid = $row['sessionid'];
      $returned_userid = $row['userid'];
      $returned_accesstoken = $row['access_token'];
      $returned_refreshtoken = $row['refresh_token'];
      $returned_user_activity = $row['user_status'];
      $returned_loginattempts = $row['user_login_attempts'];
      $returned_accesstokenexpiry = $row['access_token_expiry'];
      $returned_refreshtokenexpiry = $row['refresh_token_expiry'];

      // check if account is active
      if($returned_user_activity !== 'active' && $returned_user_activity !== 'inactive' ):
        $response = new Response();
        $response->setHttpStatusCode(401);
        $response->setSuccess(false);
        $response->addMessage("User account is not active");
        $response->send();
        exit;
      endif;

      // check if account is locked out
      if($returned_loginattempts >= 3):
        $response = new Response();
        $response->setHttpStatusCode(401);
        $response->setSuccess(false);
        $response->addMessage("User account is currently locked out");
        $response->send();
        exit;
      endif;

      // check if refresh token has expired
      if(strtotime($returned_refreshtokenexpiry) < time()):
        $response = new Response();
        $response->setHttpStatusCode(401);
        $response->setSuccess(false);
        $response->addMessage("Refresh token has expired - please log in again");
        $response->send();
        exit;
      endif;
      //date and time generation
      $postdate = new DateTime();
      // set date for kampala
      $postdate->setTimezone(new DateTimeZone('Africa/Nairobi'));
      //formulate the new date
      $date = $postdate->format('Y-m-d H:i:s');
      // generate access token
      // use 24 random bytes to generate a token then encode this as base64
      // suffix with unix time stamp to guarantee uniqueness (stale tokens)
      $accesstoken = base64_encode(bin2hex(openssl_random_pseudo_bytes(24)).time());

      // generate refresh token
      // use 24 random bytes to generate a refresh token then encode this as base64
      // suffix with unix time stamp to guarantee uniqueness (stale tokens)
      $refreshtoken = base64_encode(bin2hex(openssl_random_pseudo_bytes(24)).time());

      // set access token and refresh token expiry in seconds (access token 20 minute lifetime and refresh token 14 days lifetime)
      // send seconds rather than date/time as this is not affected by timezones
      $access_token_expiry_seconds = 1200;
      $refresh_token_expiry_seconds = 1209600;

      // create the query string to update the current session row in the sessions table and set the token and refresh token as well as their expiry dates and times
      $query = $writeDB->prepare('UPDATE sessions_general_admin set access_token = :accesstoken,
       access_token_expiry = date_add(:date, INTERVAL :accesstokenexpiryseconds SECOND),
       refresh_token = :refreshtoken, refresh_token_expiry = date_add(:date2, INTERVAL :refreshtokenexpiryseconds SECOND)
       where session_id = :sessionid and users_user_id = :userid and access_token = :returnedaccesstoken
       and refresh_token = :returnedrefreshtoken');
      // bind the user id
      $query->bindParam(':userid', $returned_userid, PDO::PARAM_INT);
      // bind the session id
      $query->bindParam(':sessionid', $returned_sessionid, PDO::PARAM_INT);
      // bind the date
      $query->bindParam(':date', $date, PDO::PARAM_STR);
      // bind the date
      $query->bindParam(':date2', $date, PDO::PARAM_STR);
      // bind the access token
      $query->bindParam(':accesstoken', $accesstoken, PDO::PARAM_STR);
      // bind the access token expiry date
      $query->bindParam(':accesstokenexpiryseconds', $access_token_expiry_seconds, PDO::PARAM_INT);
      // bind the refresh token
      $query->bindParam(':refreshtoken', $refreshtoken, PDO::PARAM_STR);
      // bind the refresh token expiry date
      $query->bindParam(':refreshtokenexpiryseconds', $refresh_token_expiry_seconds, PDO::PARAM_INT);
      // bind the old access token for where clause as user could have multiple sessions
      $query->bindParam(':returnedaccesstoken', $returned_accesstoken, PDO::PARAM_STR);
      // bind the old refresh token for where clause as user could have multiple sessions
      $query->bindParam(':returnedrefreshtoken', $returned_refreshtoken, PDO::PARAM_STR);
      // run the query
      $query->execute();

      // get count of rows updated - should be 1
      $rowCount = $query->rowCount();

      // check that a row has been updated
      if($rowCount === 0):
        $response = new Response();
        $response->setHttpStatusCode(401);
        $response->setSuccess(false);
        $response->addMessage("Access token could not be refreshed - please log in again");
        $response->send();
        exit;
      endif;

      // build response data array which contains the session id, access token and refresh token
      $returnData = array();
      $returnData['session_id'] = $returned_sessionid;
      $returnData['access_token'] = $accesstoken;
      $returnData['access_token_expiry'] = $access_token_expiry_seconds;
      $returnData['refresh_token'] = $refreshtoken;
      $returnData['refresh_token_expiry'] = $refresh_token_expiry_seconds;

      $response = new Response();
      $response->setHttpStatusCode(201);
      $response->setSuccess(true);
      $response->setData($returnData);
      $response->send();
      exit;
    }
    catch(PDOException $ex) {
      $response = new Response();
      $response->setHttpStatusCode(500);
      $response->setSuccess(false);
      $response->addMessage("There was an issue refreshing access token - please log in again");
      $response->send();
      exit;
    }

  // error when not DELETE or PATCH
else:
    $response = new Response();
    $response->setHttpStatusCode(405);
    $response->setSuccess(false);
    $response->addMessage("Request method not allowed");
    $response->send();
    exit;
endif;
// handle creating new session, e.g. log in
elseif(empty($_GET)):

  // handle creating new session, e.g. logging in
  // check to make sure the request is POST only - else exit with error response
  if($_SERVER['REQUEST_METHOD'] !== 'POST'):
    $response = new Response();
    $response->setHttpStatusCode(405);
    $response->setSuccess(false);
    $response->addMessage("Request method not allowed");
    $response->send();
    exit;
  endif;

  // delay login by 1 second to slow down any potential brute force attacks
  sleep(1);

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

  // get POST request body as the POSTED data will be JSON format
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

  // check if post request contains username and password in body as they are mandatory
  if(!isset($jsonData->username) || !isset($jsonData->password)):
    $response = new Response();
    $response->setHttpStatusCode(400);
    $response->setSuccess(false);
    (!isset($jsonData->username) ? $response->addMessage("Username not supplied") : false);
    (!isset($jsonData->password) ? $response->addMessage("Password not supplied") : false);
    $response->send();
    exit;
  endif;

  // check to make sure that username and password are not empty and not greater than 255 characters
  if(strlen($jsonData->username) < 1 || strlen($jsonData->username) > 255 || strlen($jsonData->password) < 1 || strlen($jsonData->password) > 255):
    $response = new Response();
    $response->setHttpStatusCode(400);
    $response->setSuccess(false);
    (strlen($jsonData->username) < 1 ? $response->addMessage("Username cannot be blank") : false);
    (strlen($jsonData->username) > 255 ? $response->addMessage("Username must be less than 255 characters") : false);
    (strlen($jsonData->password) < 1 ? $response->addMessage("Password cannot be blank") : false);
    (strlen($jsonData->password) > 255 ? $response->addMessage("Password must be less than 255 characters") : false);
    $response->send();
    exit;
  endif;

  // attempt to query the database to check user details - use write connection as it needs to be synchronous for password/token
  try {
    $username = $jsonData->username;
    $password = $jsonData->password;
    // create db query
    $query = $writeDB->prepare('SELECT * from general_admin where admin_username = :username');
    $query->bindParam(':username', $username, PDO::PARAM_STR);
    $query->execute();

    // get row count
    $rowCount = $query->rowCount();

    if($rowCount === 0):
      // set up response for unsuccessful login attempt - obscure what is incorrect by saying username or password is wrong
      $response = new Response();
      $response->setHttpStatusCode(401);
      $response->setSuccess(false);
      $response->addMessage("Username or password is incorrect");
      $response->send();
      exit;
    endif;

    // get first row returned
    $row = $query->fetch(PDO::FETCH_ASSOC);

    // save returned details into variables
    $returned_id = $row['admin_id'];
    $returned_fullname = $row['admin_fullname'];
    $returned_username = $row['admin_username'];
    $returned_password = $row['admin_password'];
    $returned_active = $row['admin_status'];
    $returned_loginattempts = $row['user_login_attempts'];

    // check if account is active
    if($returned_active !== 'active' && $returned_active !== 'inactive'):
      $response = new Response();
      $response->setHttpStatusCode(401);
      $response->setSuccess(false);
      $response->addMessage("user account is not active");
      $response->send();
      exit;
    endif;

    // check if account is locked out
    if($returned_loginattempts >= 3):
      $response = new Response();
      $response->setHttpStatusCode(401);
      $response->setSuccess(false);
      $response->addMessage("user account is currently locked out");
      $response->send();
      exit;
    endif;

    // check if password is the same using the hash
    if(!password_verify($password, $returned_password)):
      // create the query to increment attempts figure
      $query = $writeDB->prepare('update general_admin set user_login_attempts = user_login_attempts+1
      where admin_id = :id');
      // bind the user id
      $query->bindParam(':id', $returned_id, PDO::PARAM_INT);
      // run the query
    	$query->execute();

      // send response
      $response = new Response();
      $response->setHttpStatusCode(401);
      $response->setSuccess(false);
      $response->addMessage("Username or password is incorrect");
      $response->send();
      exit;
    endif;

    // generate access token
    // use 24 random bytes to generate a token then encode this as base64
    // suffix with unix time stamp to guarantee uniqueness (stale tokens)
    $accesstoken = base64_encode(bin2hex(openssl_random_pseudo_bytes(24)).time());

    // generate refresh token
    // use 24 random bytes to generate a refresh token then encode this as base64
    // suffix with unix time stamp to guarantee uniqueness (stale tokens)
    $refreshtoken = base64_encode(bin2hex(openssl_random_pseudo_bytes(24)).time());

    // set access token and refresh token expiry in seconds (access token 20 minute lifetime and refresh token 14 days lifetime)
    // send seconds rather than date/time as this is not affected by timezones
    $access_token_expiry_seconds = 1200;
    $refresh_token_expiry_seconds = 1209600;
  }
  catch(PDOException $ex) {
    $response = new Response();
    $response->setHttpStatusCode(500);
    $response->setSuccess(false);
    $response->addMessage("There was an issue logging in - please try again".$ex);
    $response->send();
    exit;
  }
  // new try catch as this is a transaction so should include roll back if error
  try {
    // start transaction as two queries should run one after the other
    $writeDB->beginTransaction();
    // create the query string to reset attempts figure after successful login
    $query = $writeDB->prepare('update general_admin set user_login_attempts = 0 where admin_id = :id');
    // bind the user id
    $query->bindParam(':id', $returned_id, PDO::PARAM_INT);
    // run the query
    $query->execute();

    //date and time generation
    $postdate = new DateTime();
    // set date for kampala
    $postdate->setTimezone(new DateTimeZone('Africa/Nairobi'));
    //formulate the new date
    $date = $postdate->format('Y-m-d H:i:s');

    // create the query string to insert new session into sessions table and set the token and refresh token as well as their expiry dates and times
    $query = $writeDB->prepare('insert into sessions_general_admin (admin_admin_id, access_token, access_token_expiry, refresh_token, refresh_token_expiry) values (:userid, :accesstoken, date_add(:date2, INTERVAL :accesstokenexpiryseconds SECOND), :refreshtoken, date_add(:date, INTERVAL :refreshtokenexpiryseconds SECOND))');
    // bind the user id
    $query->bindParam(':userid', $returned_id, PDO::PARAM_INT);
    // bind the access token
    $query->bindParam(':accesstoken', $accesstoken, PDO::PARAM_STR);
    // bind the date
    $query->bindParam(':date', $date, PDO::PARAM_STR);
    // bind the date
    $query->bindParam(':date2', $date, PDO::PARAM_STR);
    // bind the access token expiry date
    $query->bindParam(':accesstokenexpiryseconds', $access_token_expiry_seconds, PDO::PARAM_INT);
    // bind the refresh token
    $query->bindParam(':refreshtoken', $refreshtoken, PDO::PARAM_STR);
    // bind the refresh token expiry date
    $query->bindParam(':refreshtokenexpiryseconds', $refresh_token_expiry_seconds, PDO::PARAM_INT);
    // run the query
    $query->execute();

    // get last session id so we can return the session id in the json
    $lastSessionID = $writeDB->lastInsertId();

    // commit new row and updates if successful
    $writeDB->commit();

    // build response data array which contains the access token and refresh tokens
    $returnData = array();
    $returnData['session_id'] = intval($lastSessionID);
    $returnData['access_token'] = $accesstoken;
    $returnData['access_token_expires_in'] = $access_token_expiry_seconds;
    $returnData['refresh_token'] = $refreshtoken;
    $returnData['refresh_token_expires_in'] = $refresh_token_expiry_seconds;
    $returnData['user_status'] = $returned_active;

    $response = new Response();
    $response->setHttpStatusCode(201);
    $response->setSuccess(true);
    $response->setData($returnData);
    $response->send();
    exit;
  }
  catch(PDOException $ex) {
    // roll back update/insert if error
    $writeDB->rollBack();
    $response = new Response();
    $response->setHttpStatusCode(500);
    $response->setSuccess(false);
    $response->addMessage("There was an issue logging in - please try again");
    $response->send();
    exit;
  }
// return 404 error if endpoint not available
else:
  $response = new Response();
  $response->setHttpStatusCode(404);
  $response->setSuccess(false);
  $response->addMessage("Endpoint not found");
  $response->send();
  exit;
endif;
