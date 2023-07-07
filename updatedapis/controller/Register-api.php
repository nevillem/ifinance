<?php

require_once('../core/initialize.php');
require_once('Database.php');
require_once('../model/Response.php');
require_once('../core/classes/SaccoID.php');


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
/**
 * @OA\POST(
 *   path="/irembo_version_control/API/signup", tags={"vi-sacco"},
 *    summary = "Generate a new sacco, EndPoint",
 *  @OA\RequestBody(
 *     @OA\MediaType(
  *         mediaType="application/json",
  *     @OA\Schema(
  *       @OA\Property(property="name", type="string"),
  *        @OA\Property(property="email", type="string"),
  *       @OA\Property(property="password", type="string"),
*         )
*       )
*     ),
 *     @OA\Response(response="201", description="sacco main branch has been started"),
 *     @OA\Response(response="400", description="You have received a bad response"),
 *     @OA\Response(response="401", description="unauthorised please try again"),
 *     @OA\Response(response="404", description="Not found"),
 *     @OA\Response(response="500", description="An internal server error")
 * )
 */

// check to make sure the request is POST only - else exit with error response
if($_SERVER['REQUEST_METHOD'] !== 'POST'):
  $response = new Response();
  $response->setHttpStatusCode(405);
  $response->setSuccess(false);
  $response->addMessage("Request method not allowed");
  $response->send();
  exit;
endif;

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
if(!isset($jsonData->name) || !isset($jsonData->email) || !isset($jsonData->password)|| !isset($jsonData->contact)):
  $response = new Response();
  $response->setHttpStatusCode(400);
  $response->setSuccess(false);
  // add message to message array where necessary
  (!isset($jsonData->name) ? $response->addMessage("sacco name  not supplied") : false);
  (!isset($jsonData->email) ? $response->addMessage("sacco email not supplied") : false);
  (!isset($jsonData->shortname) ? $response->addMessage("sacco shortname not supplied") : false);
  (!isset($jsonData->contact) ? $response->addMessage("sacco contact not supplied") : false);
  (!isset($jsonData->password) ? $response->addMessage("Password not supplied") : false);
  $response->send();
  exit;
endif;

// check to make sure that sacco name email and password are not empty and less than 255 long
if(strlen($jsonData->name) < 1 || strlen($jsonData->name) > 255 || strlen($jsonData->email) < 1
|| strlen($jsonData->email) > 255 || strlen($jsonData->shortname) < 1 || strlen($jsonData->shortname)  >100
|| strlen($jsonData->contact) < 1 || strlen($jsonData->contact)  >15|| !is_numeric($jsonData->contact)  >15
|| strlen($jsonData->password) < 1 || strlen($jsonData->password) > 100):
  $response = new Response();
  $response->setHttpStatusCode(400);
  $response->setSuccess(false);
  (strlen($jsonData->name) < 1 ? $response->addMessage("sacco name cannot be blank") : false);
  (strlen($jsonData->name) > 255 ? $response->addMessage("sacco name cannot be greater than 255 characters") : false);
  (strlen($jsonData->email) < 1 ? $response->addMessage("Email cannot be blank") : false);
  (strlen($jsonData->email) > 255 ? $response->addMessage("Email cannot be greater than 255 characters") : false);
  (strlen($jsonData->shortname) < 1 ? $response->addMessage("shortname cannot be blank") : false);
  (strlen($jsonData->shortname) > 100 ? $response->addMessage("shortname cannot be greater than 255 characters") : false);
  (strlen($jsonData->contact) < 1 ? $response->addMessage("Sacco contact cannot be blank") : false);
  (strlen($jsonData->contact) > 15 ? $response->addMessage("Sacco contact cannot be greater than 15 characters") : false);
  (!is_numeric($jsonData->contact) ? $response->addMessage("Sacco contact must be numerical") : false);
  (strlen($jsonData->password) < 1 ? $response->addMessage("Password cannot be blank") : false);
  (strlen($jsonData->password) > 100 ? $response->addMessage("Password cannot be greater than 100 characters") : false);
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

// trim any leading and trailing blank spaces from email and sacco only - password may contain a leading or trailing space
$_name = trim($jsonData->name);
$_email = trim($jsonData->email);
$_password = $jsonData->password;
$_contact = $jsonData->contact;
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
  $query = $writeDB->prepare('INSERT into saccos (sacco_name, sacco_email,sacco_code,sacco_contact, sacco_password)
  values (:name, :email,:saccocode,:contact :password)');
  $query->bindParam(':name', $_name, PDO::PARAM_STR);
  $query->bindParam(':saccocode', $_code, PDO::PARAM_STR);
  $query->bindParam(':email', $_email, PDO::PARAM_STR);
  $query->bindParam(':contact', $_contact, PDO::PARAM_STR);
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

  // get last user id so we can return the user id in the json
  $lastUserID = $writeDB->lastInsertId();
  // create an otp code to send to the user
  $_code = otpfunction();
  // insert otp into the verification tables
  $query = $writeDB->prepare('INSERT into verification (verification_code, saccos_sacco_id) values (:code, :id)');
  $query->bindParam(':code', $_code, PDO::PARAM_INT);
  $query->bindParam(':id', $lastUserID, PDO::PARAM_INT);
  $query->execute();

  try {
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

  $query = $writeDB->prepare('insert into sessions (saccos_sacco_id, access_token, access_token_expiry, refresh_token, refresh_token_expiry)
   values (:userid, :accesstoken, date_add(:date, INTERVAL :accesstokenexpiryseconds SECOND), :refreshtoken, date_add(:date2, INTERVAL :refreshtokenexpiryseconds SECOND))');
    // bind the user id
    $query->bindParam(':userid', $lastUserID, PDO::PARAM_INT);
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
    // run the query
    $query->execute();
    // get last session id so we can return the session id in the json
    $lastSessionID = $writeDB->lastInsertId();
    // build response data array which contains the access token and refresh tokens
    $returnData = array();
    $returnData['saccoid'] = $lastUserID;
    $returnData['name'] = $_name;
    $returnData['email'] = $_email;
    $returnData['session_id'] = intval($lastSessionID);
    $returnData['access_token'] = $accesstoken;
    $returnData['access_token_expires_in'] = $access_token_expiry_seconds;
    $returnData['refresh_token'] = $refreshtoken;
    $returnData['refresh_token_expires_in'] = $refresh_token_expiry_seconds;

  } catch (PDOException $ex) {
    $response = new Response();
   $response->setHttpStatusCode(500);
   $response->setSuccess(false);
   $response->addMessage("There was an issue issuing the access token");
   $response->send();
   exit;
  }
  // send otp email to sacco
  // send_otp_email($_code, $_email, $_name);
  $response = new Response();
  $response->setHttpStatusCode(201);
  $response->setSuccess(true);
  $response->addMessage("sacco has been created");
  $response->setData($returnData);
  $response->send();
  exit;
}
catch(PDOException $ex) {
  $response = new Response();
  $response->setHttpStatusCode(500);
  $response->setSuccess(false);
  $response->addMessage("There was an issue creating a sacco  account - please try again");
  $response->send();
  exit;
}
