<?php

require_once('../core/initialize.php');
require_once('Database.php');
require_once('../model/Response.php');
require_once('../model/Settings.php');

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
  $query = $writeDB->prepare('select sacco_id, access_token_expiry, sacco_status, sacco_login_attempts from sessions, saccos where sessions.saccos_sacco_id = saccos.sacco_id and access_token = :accesstoken');
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
  $returned_saccoid = $row['sacco_id'];
  $returned_accesstokenexpiry = $row['access_token_expiry'];
  $returned_sacco_active = $row['sacco_status'];
  $returned_loginattempts = $row['sacco_login_attempts'];

  // check if account is active
  if($returned_sacco_active != 'active'):
    $response = new Response();
    $response->setHttpStatusCode(401);
    $response->setSuccess(false);
    $response->addMessage("sacco account is not active");
    $response->send();
    exit;
  endif;

  // check if account is locked out
  if($returned_loginattempts >= 3):
    $response = new Response();
    $response->setHttpStatusCode(401);
    $response->setSuccess(false);
    $response->addMessage("sacco account is currently locked out");
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
// check if id is in the url e.g. /setting/1

if (array_key_exists("type",$_GET)):
  // get settings from query string
  $type = $_GET['type'];
  //check to see if settings in query string is not empty and is number, if not return json error
  if($type == '' || !is_string($type)):
    $response = new Response();
    $response->setHttpStatusCode(400);
    $response->setSuccess(false);
    $response->addMessage("type cannot be blank or must be string");
    $response->send();
    exit;
  endif;

  switch ($type) {
                  case 'members':

            if($_SERVER['REQUEST_METHOD'] === 'GET'):

                // attempt to query the database
                try {
                  // ADD AUTH TO QUERY
                  // create db query
                  $query = $readDB->prepare('select * from sms where  saccos_sacco_id = :saccoid order by id desc');
                  $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
                  $query->execute();

                  // get row count
                  $rowCount = $query->rowCount();
                  // create  array to store returned
                  $smsArray = array();
                  // for each row returned
                  while($row = $query->fetch(PDO::FETCH_ASSOC)):
                    extract($row);
                   $sms = array(
                   "id" => $id,
                   "contact" => $contact,
                   "message" => crc32($message),
                   "status" => $status,
                   "timestamp" => $timestamp
                   );
                   $smsArray[] = $sms;
                  endwhile;
                  // bundle build and rows returned into an array to return in the json data
                  $returnData = array();
                  $returnData['rows_returned'] = $rowCount;
                  $returnData['sms'] = $smsArray;

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
                  $response->addMessage("Failed to get share");
                  $response->send();
                  exit;
                }
        // else if request is a POST e.g. create
            elseif($_SERVER['REQUEST_METHOD'] === 'POST'):
                  // create a  by the
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

                    // get POST request body as the posted data will be JSON format
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

                    // check if post request contains  data in body as these are mandatory
                    if(!isset($jsonData->message) || empty($jsonData->message)):
                      $response = new Response();
                      $response->setHttpStatusCode(400);
                      $response->setSuccess(false);
                      (!isset($jsonData->message) ? $response->addMessage("message field is mandatory and must be provided") : false);
                      (empty($jsonData->message) ? $response->addMessage("message field is must not be empty") : false);
                    $response->send();
                      exit;
                    endif;

                    // check whether the  exists for sure
                    $query = $readDB->prepare('select member_contact from members where saccos_sacco_id = :saccoid');
                    $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
                    $query->execute();

                    $rowCount = $query->rowCount();

                    while($row = $query->fetch(PDO::FETCH_ASSOC)):
                      $contact[] = '256'.$row['member_contact'];
                    endwhile;

                    $query = $writeDB->prepare('insert into sms(contact,message,saccos_sacco_id) values (:contact,:message, :saccoid)');

                    foreach($contact as $contact) {
                      $query->execute(array(
                          "message" => $jsonData->message,
                          "contact" => $contact,
                          "saccoid" => $returned_saccoid
                      ));
                      }

                    //set up response for successful return
                    $response = new Response();
                    $response->setHttpStatusCode(201);
                    $response->setSuccess(true);
                    $response->addMessage("sms sent successfully");
                    $response->send();
                    exit;
                  }
                  // if error with sql query return a json error
                  catch(PDOException $ex) {
                    error_log("Database Query Error: ".$ex, 0);
                    $response = new Response();
                    $response->setHttpStatusCode(500);
                    $response->setSuccess(false);
                    $response->addMessage("Failed to send sms ");
                    $response->send();
                    exit;
                  }
                // if any other request method apart from GET or POST is used then return 405 method not allowed
                else:
                    $response = new Response();
                    $response->setHttpStatusCode(405);
                    $response->setSuccess(false);
                    $response->addMessage("Request method not allowed");
                    $response->send();
                    exit;
                  endif;
                break;
                  case 'staff':

                              if($_SERVER['REQUEST_METHOD'] === 'GET'):

                                  // attempt to query the database
                                  try {
                                    // ADD AUTH TO QUERY
                                    // create db query
                                    $query = $readDB->prepare('select * from sms where  saccos_sacco_id = :saccoid order by id desc');
                                    $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
                                    $query->execute();

                                    // get row count
                                    $rowCount = $query->rowCount();

                                    // create  array to store returned
                                    $smsArray = array();
                                    $smsArray['sms'] = array();
                                    // for each row returned
                                    while($row = $query->fetch(PDO::FETCH_ASSOC)):
                                      extract($row);
                                     $sms = array(
                                     "id" => $id,
                                     "contact" => $contact,
                                     "message" => $message,
                                     "status" => $status,
                                     "timestamp" => $timestamp
                                     );
                                     array_push($smsArray['sms'], $smsArray);
                                    endwhile;
                                    // bundle build and rows returned into an array to return in the json data
                                    $returnData = array();
                                    $returnData['rows_returned'] = $rowCount;
                                    $returnData['data'] = $smsArray;

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
                                    $response->addMessage("Failed to get share");
                                    $response->send();
                                    exit;
                                  }
                          // else if request is a POST e.g. create
                              elseif($_SERVER['REQUEST_METHOD'] === 'POST'):
                                    // create a  by the
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

                                      // get POST request body as the posted data will be JSON format
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

                                      // check if post request contains  data in body as these are mandatory
                                      if(!isset($jsonData->message) || empty($jsonData->message)):
                                        $response = new Response();
                                        $response->setHttpStatusCode(400);
                                        $response->setSuccess(false);
                                        (!isset($jsonData->message) ? $response->addMessage("message field is mandatory and must be provided") : false);
                                        (empty($jsonData->message) ? $response->addMessage("message field is must not be empty") : false);
                                      $response->send();
                                        exit;
                                      endif;

                                      // check whether the  exists for sure
                                      $query = $readDB->prepare('select user_contact from users where saccos_sacco_id = :saccoid');
                                      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
                                      $query->execute();

                                      $rowCount = $query->rowCount();
                                      $contact = array();
                                      while($row = $query->fetch(PDO::FETCH_ASSOC)):
                                        $contacts[] = '256'.$row['user_contact'];
                                      endwhile;
                                      $query = $writeDB->prepare('insert into sms(contact,message,saccos_sacco_id) values (:contact,:message, :saccoid)');

                                      foreach ($contacts as $contact) {
                                        $query->execute(array(
                                            "message" => $jsonData->message,
                                            "contact" => $contact,
                                            "saccoid" => $returned_saccoid
                                        ));
                                        }

                                      //set up response for successful return
                                      $response = new Response();
                                      $response->setHttpStatusCode(201);
                                      $response->setSuccess(true);
                                      $response->addMessage("sms sent successfully");
                                      $response->send();
                                      exit;
                                    }
                                    // if error with sql query return a json error
                                    catch(PDOException $ex) {
                                      error_log("Database Query Error: ".$ex, 0);
                                      $response = new Response();
                                      $response->setHttpStatusCode(500);
                                      $response->setSuccess(false);
                                      $response->addMessage("Failed to send sms ");
                                      $response->send();
                                      exit;
                                    }
                                  // if any other request method apart from GET or POST is used then return 405 method not allowed
                                  else:
                                      $response = new Response();
                                      $response->setHttpStatusCode(405);
                                      $response->setSuccess(false);
                                      $response->addMessage("Request method not allowed");
                                      $response->send();
                                      exit;
                                    endif;
                                  break;
                        case 'market':

                            if($_SERVER['REQUEST_METHOD'] === 'POST'):
                              // create a  by the
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

                                // get POST request body as the posted data will be JSON format
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

                                // check if post request contains  data in body as these are mandatory
                                if(!isset($jsonData->message) || empty($jsonData->message) || !isset($jsonData->contacts) || empty($jsonData->contacts)):
                                  $response = new Response();
                                  $response->setHttpStatusCode(400);
                                  $response->setSuccess(false);
                                  (!isset($jsonData->message) ? $response->addMessage("message field is mandatory and must be provided") : false);
                                  (empty($jsonData->message) ? $response->addMessage("message field is must not be empty") : false);
                                  (!isset($jsonData->contacts) ? $response->addMessage("contacts field is mandatory and must be provided") : false);
                                  (empty($jsonData->contacts) ? $response->addMessage("contacts field is must not be empty") : false);
                                $response->send();
                                  exit;
                                endif;

                                $contact = explode(',', $jsonData->contacts);
                                $countryCode = 256;
                                // $number = preg_replace('/^0?/', '+'.$countryCode, $contact);
                                $query = $writeDB->prepare('insert into sms(contact,message,saccos_sacco_id) values (:contact,:message, :saccoid)');

                                foreach ($contact as $contacts) {
                                  $query->execute(array(
                                      "message" => $jsonData->message,
                                      "contact" => preg_replace('/^0?/', '+'.'256', $contacts),
                                      "saccoid" => $returned_saccoid
                                  ));
                                  }

                                //set up response for successful return
                                $response = new Response();
                                $response->setHttpStatusCode(201);
                                $response->setSuccess(true);
                                $response->addMessage("sms sent successfully");
                                $response->send();
                                exit;
                              }
                              // if error with sql query return a json error
                              catch(PDOException $ex) {
                                error_log("Database Query Error: ".$ex, 0);
                                $response = new Response();
                                $response->setHttpStatusCode(500);
                                $response->setSuccess(false);
                                $response->addMessage("Failed to send sms ");
                                $response->send();
                                exit;
                              }
                            // if any other request method apart from GET or POST is used then return 405 method not allowed
                            else:
                                $response = new Response();
                                $response->setHttpStatusCode(405);
                                $response->setSuccess(false);
                                $response->addMessage("Request method not allowed");
                                $response->send();
                                exit;
                              endif;
                              break;
                        default:
                        $response = new Response();
                        $response->setHttpStatusCode(404);
                        $response->setSuccess(false);
                        $response->addMessage("Endpoint not found - Attached Info");
                        $response->send();
                        exit;

            }
endif;
