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
  $returned_saccoid = $row['saccos_sacco_id'];
  $returned_accesstokenexpiry = $row['access_token_expiry'];
  $returned_sacco_active = $row['user_status'];
  $returned_loginattempts = $row['user_login_attempts'];

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

if (array_key_exists("setting",$_GET)):
  // get settings from query string
  $setting = $_GET['setting'];
  //check to see if settings in query string is not empty and is number, if not return json error
  if($setting == '' || !is_string($setting)):
    $response = new Response();
    $response->setHttpStatusCode(400);
    $response->setSuccess(false);
    $response->addMessage("setting cannot be blank or must be string");
    $response->send();
    exit;
  endif;

  switch ($setting) {
                  case 'accounts':
  if (array_key_exists("value", $_GET)):
              // get  from query string
        $accountid = $_GET['value'];

    //check to see if id in query string is not empty and is number, if not return json error
        if($accountid == '' || !is_numeric($accountid)):
          $response = new Response();
          $response->setHttpStatusCode(400);
          $response->setSuccess(false);
          $response->addMessage("account info cannot be blank or must be string");
          $response->send();
          exit;
        endif;

              if($_SERVER['REQUEST_METHOD'] === 'GET'):
                // attempt to query the database
                try {
                  // create db query
                  // ADD AUTH TO QUERY
                  $query = $readDB->prepare('select * from account_types where saccos_sacco_id = :saccoid and account_type_id = :accountid');
                  $query->bindParam(':accountid', $accountid, PDO::PARAM_INT);
                  $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
                  $query->execute();

                  // get row count
                  $rowCount = $query->rowCount();

                  // create catalog array to store returned  object
                  $accountArray = array();

                  if($rowCount === 0):
                    // set up response for unsuccessful return
                    $response = new Response();
                    $response->setHttpStatusCode(404);
                    $response->setSuccess(false);
                    $response->addMessage("account not found for particular info");
                    $response->send();
                    exit;
                  endif;

                  // for each row returned
                  while($row = $query->fetch(PDO::FETCH_ASSOC)):
                    $account = new Account($row['account_type_id'], $row['account_type_name'], $row['account_type_desc'], $row['account_type_withdraw_charge'], $row['account_type_minimal_balance']);
                    $accountArray[] = $account->returnAccountAsArray();
                  endwhile;

                  // bundle s and rows returned into an array to return in the json data
                  $returnData = array();
                  $returnData['rows_returned'] = $rowCount;
                  $returnData['accounts'] = $accountArray;

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
                catch(AccountException $ex) {
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
                  $response->addMessage("Failed to get accounts");
                  $response->send();
                  exit;
                }
                // else if request if a DELETE e.g. delete
                elseif($_SERVER['REQUEST_METHOD'] === 'DELETE'):
                  // attempt to query the database
                  try {
                    // ADD AUTH TO QUERY
                    $query = $writeDB->prepare('delete from account_types where account_type_id = :accountid and saccos_sacco_id = :saccoid');
                    $query->bindParam(':accountid', $accountid, PDO::PARAM_INT);
                    $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
                    $query->execute();

                    // get row count
                    $rowCount = $query->rowCount();

                    if($rowCount === 0):
                            // set up response for unsuccessful return
                            $response = new Response();
                            $response->setHttpStatusCode(404);
                            $response->setSuccess(false);
                            $response->addMessage("account type not found, please try again");
                            $response->send();
                            exit;
                    else:
                            $response = new Response();
                            $response->setHttpStatusCode(201);
                            $response->setSuccess(true);
                            $response->addMessage("account has been deleted");
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
                    $response->addMessage("Failed to delete account - Attached Info");
                    $response->send();
                    exit;
                }
      elseif($_SERVER['REQUEST_METHOD'] === 'PATCH'):
                  // update
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

                    // set  field updated to false initially
                    $name = false;
                    $charge = false;
                    $balance = false;
                    $describe = false;
                        // create blank query fields string to append each field to
                    $queryFields = "";

                    // check if  exists in PATCH
                    if(isset($jsonData->name)):
                      // set  field updated to true
                      $name = true;
                      // add  field to query field string
                      $queryFields .= "account_type_name = :name, ";
                    endif;
                    // check if  exists in PATCH
                    if(isset($jsonData->charge)):
                      // set  field updated to true
                      $charge = true;
                      // add  field to query field string
                      $queryFields .= "account_type_withdraw_charge = :charge, ";
                    endif;
                    // check if  exists in PATCH
                    if(isset($jsonData->describe)):
                      // set  field updated to true
                      $describe = true;
                      // add  field to query field string
                      $queryFields .= "account_type_desc = :describe, ";
                    endif;
                    // check if  exists in PATCH
                    if(isset($jsonData->balance)):
                      // set  field updated to true
                      $balance = true;
                      // add  field to query field string
                      $queryFields .= "account_type_minimal_balance = :balance, ";
                    endif;
                    // remove the right hand comma and trailing space
                    $queryFields = rtrim($queryFields, ", ");

                    // check if any fields supplied in JSON
                    if($name === false && $describe === false && $charge === false && $balance === false):
                      $response = new Response();
                      $response->setHttpStatusCode(400);
                      $response->setSuccess(false);
                      $response->addMessage("no fields provided");
                      $response->send();
                      exit;
                    endif;
                    // ADD AUTH TO QUERY
                    // create db query to get  from database to update - use master db
                    $query = $writeDB->prepare('select * from account_types where account_type_id = :accountid and saccos_sacco_id = :saccoid');
                    $query->bindParam(':accountid', $accountid, PDO::PARAM_INT);
                    $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
                    $query->execute();

                    // get row count
                    $rowCount = $query->rowCount();

                    // make sure that the  exists for a given  id
                    if($rowCount === 0):
                      // set up response for unsuccessful return
                      $response = new Response();
                      $response->setHttpStatusCode(404);
                      $response->setSuccess(false);
                      $response->addMessage("no account type found to update");
                      $response->send();
                      exit;
                    endif;

                    // for each row returned - should be just one
                    while($row = $query->fetch(PDO::FETCH_ASSOC)):
                      $account = new Account($row['account_type_id'], $row['account_type_name'], $row['account_type_desc'], $row['account_type_withdraw_charge'], $row['account_type_minimal_balance']);
                    endwhile;

                    // create the query string including any query fields
                    $queryString = "UPDATE account_types set ".$queryFields." where account_type_id = :accountid and saccos_sacco_id = :saccoid";
                    // prepare the query
                    $query = $writeDB->prepare($queryString);

                    // if field has been provided
                    if($name === true):
                      // set  object  to given value (checks for valid input)
                      $account->setName($jsonData->name);
                      // get the value back as the object could be handling the return of the value differently to
                      // what was provided
                      $up_name = $account->getName();
                      // bind the parameter of the new value from the object to the query (prevents SQL injection)
                      $query->bindParam(':name', $up_name, PDO::PARAM_STR);
                    endif;
                    // if field has been provided
                    if($charge === true):
                      // set  object  to given value (checks for valid input)
                      $account->setCharge($jsonData->charge);
                      // get the value back as the object could be handling the return of the value differently to
                      // what was provided
                      $up_charge = $account->getCharge();
                      // bind the parameter of the new value from the object to the query (prevents SQL injection)
                      $query->bindParam(':charge', $up_charge, PDO::PARAM_STR);
                    endif;

                    // if field has been provided
                    if($balance === true):
                      // set  object  to given value (checks for valid input)
                      $account->setBalance($jsonData->balance);
                      // get the value back as the object could be handling the return of the value differently to
                      // what was provided
                      $up_balance = $account->getBalance();
                      // bind the parameter of the new value from the object to the query (prevents SQL injection)
                      $query->bindParam(':balance', $up_balance, PDO::PARAM_STR);
                    endif;

                    // if field has been provided
                    if($describe === true):
                      // set  object  to given value (checks for valid input)
                      $account->setDescribe($jsonData->describe);
                      // get the value back as the object could be handling the return of the value differently to
                      // what was provided
                      $up_describe = $account->getDescribe();
                      // bind the parameter of the new value from the object to the query (prevents SQL injection)
                      $query->bindParam(':describe', $up_describe, PDO::PARAM_STR);
                    endif;


                    // bind the  id provided in the query string
                    $query->bindParam(':accountid', $accountid, PDO::PARAM_INT);
                    // bind the  id returned
                    $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
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
                      $response->addMessage("not updated - given values may be the same as the stored values");
                      $response->send();
                      exit;
                    endif;

                    // ADD AUTH TO QUERY
                    // create db query to return the newly edited  - connect to master database
                    $query = $writeDB->prepare('select * from account_types where account_type_id = :accountid and saccos_sacco_id = :saccoid ');
                    $query->bindParam(':accountid', $accountid, PDO::PARAM_INT);
                    $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
                    $query->execute();

                    // get row count
                    $rowCount = $query->rowCount();

                    // check if  was found
                    if($rowCount === 0):
                      // set up response for unsuccessful return
                      $response = new Response();
                      $response->setHttpStatusCode(404);
                      $response->setSuccess(false);
                      $response->addMessage("no share type found");
                      $response->send();
                      exit;
                    endif;
                    // create  array to store returned
                    $accountArray = array();
                    // for each row returned
                    while($row = $query->fetch(PDO::FETCH_ASSOC)):
                      $account = new Account($row['account_type_id'], $row['account_type_name'], $row['account_type_desc'], $row['account_type_withdraw_charge'], $row['account_type_minimal_balance']);
                      $accountArray[] = $account->returnAccountAsArray();
                    endwhile;
                    // bundle  and rows returned into an array to return in the json data
                    $returnData = array();
                    $returnData['rows_returned'] = $rowCount;
                    $returnData['account'] = $accountArray;

                    // set up response for successful return
                    $response = new Response();
                    $response->setHttpStatusCode(201);
                    $response->setSuccess(true);
                    $response->addMessage("share type has been updated");
                    $response->setData($returnData);
                    $response->send();
                    exit;
                  }
                  catch(AccountException $ex) {
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
                    $response->addMessage("Failed to update account - check your data for errors" );
                    $response->send();
                    exit;
                  }

                // if any other request method apart from GET, PATCH, DELETE is used then return 405 method not allowed
              else:
                  $response = new Response();
                  $response->setHttpStatusCode(405);
                  $response->setSuccess(false);
                  $response->addMessage("Request method not allowed");
                  $response->send();
                  exit;
              endif;


    // if empty array in the get function
    elseif(empty($_GET['value'])):
              if($_SERVER['REQUEST_METHOD'] === 'GET'):

                // attempt to query the database
                try {
                  // ADD AUTH TO QUERY
                  // create db query
                  $query = $readDB->prepare('select * from account_types where  saccos_sacco_id = :saccoid order by account_type_id desc');
                  $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
                  $query->execute();

                  // get row count
                  $rowCount = $query->rowCount();
                  // check if  was found
                  if($rowCount === 0):
                    // set up response for unsuccessful return
                    $response = new Response();
                    $response->setHttpStatusCode(404);
                    $response->setSuccess(false);
                    $response->addMessage("no accounts found");
                    $response->send();
                    exit;
                  endif;
                  // create  array to store returned
                  $accountArray = array();
                  // for each row returned
                  while($row = $query->fetch(PDO::FETCH_ASSOC)):
                    $account = new Account($row['account_type_id'], $row['account_type_name'], $row['account_type_desc'], $row['account_type_withdraw_charge'], $row['account_type_minimal_balance']);
                    $accountArray[] = $account->returnAccountAsArray();
                  endwhile;
                  // bundle build and rows returned into an array to return in the json data
                  $returnData = array();
                  $returnData['rows_returned'] = $rowCount;
                  $returnData['accounts'] = $accountArray;

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
                catch(AccountException $ex) {
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
                    if(!isset($jsonData->name) || !isset($jsonData->charge) || !isset($jsonData->describe) || !isset($jsonData->balance)):
                      $response = new Response();
                      $response->setHttpStatusCode(400);
                      $response->setSuccess(false);
                      (!isset($jsonData->name) ? $response->addMessage("name field is mandatory and must be provided") : false);
                      (!isset($jsonData->charge) ? $response->addMessage("charge field is mandatory and must be provided") : false);
                      (!isset($jsonData->describe) ? $response->addMessage("description field is mandatory and must be provided") : false);
                      (!isset($jsonData->balance) ? $response->addMessage("balance field is mandatory and must be provided") : false);
                      $response->send();
                      exit;
                    endif;

                    // check whether the  exists for sure
                    $query = $readDB->prepare('select * from account_types where account_type_name = :name and saccos_sacco_id = :saccoid');
                    $query->bindParam(':name', $jsonData->name, PDO::PARAM_STR);
                    $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
                    $query->execute();

                    // get row count
                    $rowCount = $query->rowCount();
                    if($rowCount > 0):
                      // set up response for unsuccessful return
                      $response = new Response();
                      $response->setHttpStatusCode(409);
                      $response->setSuccess(false);
                      $response->addMessage("duplicate account type found");
                      $response->send();
                      exit;
                    endif;

                  // create  with data, if non mandatory fields not provided then set to null
                    $account = new Account(null, $jsonData->name, $jsonData->describe, $jsonData->charge, $jsonData->balance);
                    // get name, store them in variables
                    $name = $account->getName();
                    $describe = $account->getDescribe();
                    $balance = $account->getBalance();
                    $charge = $account->getCharge();
                    // ADD AUTH TO QUERY
                    // create db query
                    $query = $writeDB->prepare('insert into account_types
                     (account_type_name,account_type_desc,account_type_withdraw_charge,account_type_minimal_balance,saccos_sacco_id)
                     values (:name,:describe,:charge,:balance, :saccoid)');
                     $query->bindParam(':name', $name, PDO::PARAM_STR);
                     $query->bindParam(':describe', $describe, PDO::PARAM_STR);
                     $query->bindParam(':charge', $charge, PDO::PARAM_STR);
                     $query->bindParam(':balance', $balance, PDO::PARAM_STR);
                     $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
                     $query->execute();

                    // get row count
                    $rowCount = $query->rowCount();
                    // check if row was actually inserted, PDO exception should have caught it if not.
                    if($rowCount === 0):
                      // set up response for unsuccessful return
                      $response = new Response();
                      $response->setHttpStatusCode(500);
                      $response->setSuccess(false);
                      $response->addMessage("Failed to create account type");
                      $response->send();
                      exit;
                    endif;

                    // get last  id so we can return the  in the json
                    $lastID = $writeDB->lastInsertId();
                    // ADD AUTH TO QUERY
                    // create db query to get newly created object - get from master db not read slave as replication may be too slow for successful read
                    $query = $writeDB->prepare('select * from account_types where account_type_id =:id and saccos_sacco_id = :saccoid');
                    $query->bindParam(':id', $lastID, PDO::PARAM_INT);
                    $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
                    $query->execute();

                    // get row count
                    $rowCount = $query->rowCount();

                    // make sure that the new  was returned
                    if($rowCount === 0):
                      // set up response for unsuccessful return
                      $response = new Response();
                      $response->setHttpStatusCode(500);
                      $response->setSuccess(false);
                      $response->addMessage("Failed to retrieve accounttype after creation");
                      $response->send();
                      exit;
                    endif;

                    // create empty array to store
                    $accountArray = array();

                    // create  array to store returned
                    $accountArray = array();
                    // for each row returned
                    while($row = $query->fetch(PDO::FETCH_ASSOC)):
                      $account = new Account($row['account_type_id'], $row['account_type_name'], $row['account_type_desc'], $row['account_type_withdraw_charge'], $row['account_type_minimal_balance']);
                      $accountArray[] = $account->returnAccountAsArray();
                    endwhile;
                    // bundle build and rows returned into an array to return in the json data
                    $returnData = array();
                    $returnData['rows_returned'] = $rowCount;
                    $returnData['account'] = $accountArray;

                    //set up response for successful return
                    $response = new Response();
                    $response->setHttpStatusCode(201);
                    $response->setSuccess(true);
                    $response->addMessage("account type created");
                    $response->setData($returnData);
                    $response->send();
                    exit;
                  }
                  // if  fails to create due to data types, missing fields or invalid data then send error json
                  catch(AccountException $ex) {
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
                    $response->addMessage("Failed to insert account into database - check submitted data for errors");
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
              else:
                $response = new Response();
                $response->setHttpStatusCode(404);
                $response->setSuccess(false);
                $response->addMessage("Suited Endpoint not found");
                $response->send();
                exit;
            endif;
          break;

          case 'shares':
if (array_key_exists("value", $_GET)):
      // get  from query string
    $shareid = $_GET['value'];

    //check to see if id in query string is not empty and is number, if not return json error
    if($shareid == '' || !is_numeric($shareid)):
      $response = new Response();
      $response->setHttpStatusCode(400);
      $response->setSuccess(false);
      $response->addMessage("share cannot be blank or must be string");
      $response->send();
      exit;
    endif;

      if($_SERVER['REQUEST_METHOD'] === 'GET'):
        // attempt to query the database
        try {
          // create db query
          // ADD AUTH TO QUERY
          $query = $readDB->prepare('select * from share_settings where saccos_sacco_id = :saccoid and share_id = :shareid');
          $query->bindParam(':shareid', $shareid, PDO::PARAM_INT);
          $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
          $query->execute();

          // get row count
          $rowCount = $query->rowCount();

          // create catalog array to store returned  object
          $AccountArray = array();

          if($rowCount === 0):
            // set up response for unsuccessful return
            $response = new Response();
            $response->setHttpStatusCode(404);
            $response->setSuccess(false);
            $response->addMessage("share type not found for particular info");
            $response->send();
            exit;
          endif;

          // for each row returned
          while($row = $query->fetch(PDO::FETCH_ASSOC)):
            $share = new Share($row['share_id'], $row['share_name'], $row['share_price'], $row['share_limit']);
            $shareArray[] = $share->returnShareAsArray();
          endwhile;

          // bundle s and rows returned into an array to return in the json data
          $returnData = array();
          $returnData['rows_returned'] = $rowCount;
          $returnData['share'] = $shareArray;

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
        catch(ShareException $ex) {
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
          $response->addMessage("Failed to get share type");
          $response->send();
          exit;
        }
        // else if request if a DELETE e.g. delete
        elseif($_SERVER['REQUEST_METHOD'] === 'DELETE'):
          // attempt to query the database
          try {
            // ADD AUTH TO QUERY
            $query = $writeDB->prepare('delete from share_settings where share_id = :shareid and saccos_sacco_id = :saccoid');
            $query->bindParam(':shareid', $shareid, PDO::PARAM_INT);
            $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
            $query->execute();

            // get row count
            $rowCount = $query->rowCount();

            if($rowCount === 0):
                    // set up response for unsuccessful return
                    $response = new Response();
                    $response->setHttpStatusCode(404);
                    $response->setSuccess(false);
                    $response->addMessage("share type not found, please try again");
                    $response->send();
                    exit;
            else:
                    $response = new Response();
                    $response->setHttpStatusCode(201);
                    $response->setSuccess(true);
                    $response->addMessage("share type has been deleted");
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
            $response->addMessage("Failed to delete account - Attached Info");
            $response->send();
            exit;
        }
elseif($_SERVER['REQUEST_METHOD'] === 'PATCH'):
          // update
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

            // set  field updated to false initially
            $name = false;
            $price = false;
            $limit = false;
                // create blank query fields string to append each field to
            $queryFields = "";

            // check if  exists in PATCH
            if(isset($jsonData->name)):
              // set  field updated to true
              $name = true;
              // add  field to query field string
              $queryFields .= "share_name = :name, ";
            endif;
            // check if  exists in PATCH
            if(isset($jsonData->price)):
              // set  field updated to true
              $price = true;
              // add  field to query field string
              $queryFields .= "share_price = :price, ";
            endif;
            // check if  exists in PATCH
            if(isset($jsonData->limit)):
              // set  field updated to true
              $limit = true;
              // add  field to query field string
              $queryFields .= "share_limit = :limit, ";
            endif;
            // remove the right hand comma and trailing space
            $queryFields = rtrim($queryFields, ", ");

            // check if any fields supplied in JSON
            if($name === false && $price === false && $limit === false):
              $response = new Response();
              $response->setHttpStatusCode(400);
              $response->setSuccess(false);
              $response->addMessage("no fields provided");
              $response->send();
              exit;
            endif;
            // ADD AUTH TO QUERY
            // create db query to get  from database to update - use master db
            $query = $writeDB->prepare('select * from share_settings where share_id = :shareid and saccos_sacco_id = :saccoid');
            $query->bindParam(':shareid', $shareid, PDO::PARAM_INT);
            $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
            $query->execute();

            // get row count
            $rowCount = $query->rowCount();

            // make sure that the  exists for a given  id
            if($rowCount === 0):
              // set up response for unsuccessful return
              $response = new Response();
              $response->setHttpStatusCode(404);
              $response->setSuccess(false);
              $response->addMessage("no share type found to update");
              $response->send();
              exit;
            endif;

            // for each row returned - should be just one
            while($row = $query->fetch(PDO::FETCH_ASSOC)):
              $share = new Share($row['share_id'], $row['share_name'], $row['share_price'], $row['share_limit']);
            endwhile;

            // create the query string including any query fields
            $queryString = "UPDATE share_settings set ".$queryFields." where share_id = :shareid and saccos_sacco_id = :saccoid";
            // prepare the query
            $query = $writeDB->prepare($queryString);

            // if field has been provided
            if($name === true):
              // set  object  to given value (checks for valid input)
              $share->setName($jsonData->name);
              // get the value back as the object could be handling the return of the value differently to
              // what was provided
              $up_name = $share->getName();
              // bind the parameter of the new value from the object to the query (prevents SQL injection)
              $query->bindParam(':name', $up_name, PDO::PARAM_STR);
            endif;
            // if field has been provided
            if($price === true):
              // set  object  to given value (checks for valid input)
              $share->setPrice($jsonData->price);
              // get the value back as the object could be handling the return of the value differently to
              // what was provided
              $up_price = $share->getPrice();
              // bind the parameter of the new value from the object to the query (prevents SQL injection)
              $query->bindParam(':price', $up_price, PDO::PARAM_STR);
            endif;

            // if field has been provided
            if($limit === true):
              // set  object  to given value (checks for valid input)
              $share->setLimit($jsonData->limit);
              // get the value back as the object could be handling the return of the value differently to
              // what was provided
              $up_limit = $share->getLimit();
              // bind the parameter of the new value from the object to the query (prevents SQL injection)
              $query->bindParam(':limit', $up_limit, PDO::PARAM_STR);
            endif;

            // bind the  id provided in the query string
            $query->bindParam(':shareid', $shareid, PDO::PARAM_INT);
            // bind the  id returned
            $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
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
              $response->addMessage("not updated - given values may be the same as the stored values");
              $response->send();
              exit;
            endif;

            // ADD AUTH TO QUERY
            // create db query to return the newly edited  - connect to master database
            $query = $writeDB->prepare('select * from share_settings where share_id = :shareid and saccos_sacco_id = :saccoid ');
            $query->bindParam(':shareid', $shareid, PDO::PARAM_INT);
            $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
            $query->execute();

            // get row count
            $rowCount = $query->rowCount();

            // check if  was found
            if($rowCount === 0):
              // set up response for unsuccessful return
              $response = new Response();
              $response->setHttpStatusCode(404);
              $response->setSuccess(false);
              $response->addMessage("no share type found");
              $response->send();
              exit;
            endif;
            // create  array to store returned
            $shareArray = array();
            // for each row returned
            while($row = $query->fetch(PDO::FETCH_ASSOC)):
              $share = new Share($row['share_id'], $row['share_name'], $row['share_price'], $row['share_limit']);
              $shareArray[] = $share->returnShareAsArray();
            endwhile;
            // bundle  and rows returned into an array to return in the json data
            $returnData = array();
            $returnData['rows_returned'] = $rowCount;
            $returnData['share'] = $shareArray;

            // set up response for successful return
            $response = new Response();
            $response->setHttpStatusCode(201);
            $response->setSuccess(true);
            $response->addMessage("account has been updated");
            $response->setData($returnData);
            $response->send();
            exit;
          }
          catch(ShareException $ex) {
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
            $response->addMessage("Failed to update share type - check your data for errors" );
            $response->send();
            exit;
          }

        // if any other request method apart from GET, PATCH, DELETE is used then return 405 method not allowed
      else:
          $response = new Response();
          $response->setHttpStatusCode(405);
          $response->setSuccess(false);
          $response->addMessage("Request method not allowed");
          $response->send();
          exit;
      endif;


// if empty array in the get function
elseif(empty($_GET['value'])):
      if($_SERVER['REQUEST_METHOD'] === 'GET'):

        // attempt to query the database
        try {
          // ADD AUTH TO QUERY
          // create db query
          $query = $readDB->prepare('select * from share_settings where  saccos_sacco_id = :saccoid order by share_id desc');
          $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
          $query->execute();

          // get row count
          $rowCount = $query->rowCount();
          // check if  was found
          if($rowCount === 0):
            // set up response for unsuccessful return
            $response = new Response();
            $response->setHttpStatusCode(404);
            $response->setSuccess(false);
            $response->addMessage("no share types found");
            $response->send();
            exit;
          endif;
          // create  array to store returned
          $shareArray = array();
          // for each row returned
          while($row = $query->fetch(PDO::FETCH_ASSOC)):
            $share = new Share($row['share_id'], $row['share_name'], $row['share_price'], $row['share_limit']);
            $shareArray[] = $share->returnShareAsArray();
          endwhile;
          // bundle  and rows returned into an array to return in the json data
          $returnData = array();
          $returnData['rows_returned'] = $rowCount;
          $returnData['shares'] = $shareArray;

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
        catch(ShareException $ex) {
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
          $response->addMessage("Failed to get account type");
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
            if(!isset($jsonData->name) || !isset($jsonData->price) || !isset($jsonData->limit)):
              $response = new Response();
              $response->setHttpStatusCode(400);
              $response->setSuccess(false);
              (!isset($jsonData->name) ? $response->addMessage("name field is mandatory and must be provided") : false);
              (!isset($jsonData->price) ? $response->addMessage("price field is mandatory and must be provided") : false);
              (!isset($jsonData->limit) ? $response->addMessage("limit field is mandatory and must be provided") : false);
              $response->send();
              exit;
            endif;

            // check whether the  exists for sure
            $query = $readDB->prepare('select * from share_settings where share_name = :name and saccos_sacco_id = :saccoid');
            $query->bindParam(':name', $jsonData->name, PDO::PARAM_STR);
            $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
            $query->execute();

            // get row count
            $rowCount = $query->rowCount();
            if($rowCount > 0):
              // set up response for unsuccessful return
              $response = new Response();
              $response->setHttpStatusCode(409);
              $response->setSuccess(false);
              $response->addMessage("duplicate share type found");
              $response->send();
              exit;
            endif;

          // create  with data, if non mandatory fields not provided then set to null
            $share = new Share(null, $jsonData->name, $jsonData->price, $jsonData->limit);
            // get name, store them in variables
            $name = $share->getName();
            $price = $share->getPrice();
            $limit = $share->getLimit();

            // ADD AUTH TO QUERY
            // create db query
            $query = $writeDB->prepare('insert into share_settings
             (share_name,share_price,share_limit,saccos_sacco_id)
             values (:name,:price,:limit, :saccoid)');
             $query->bindParam(':name', $name, PDO::PARAM_STR);
             $query->bindParam(':price', $price, PDO::PARAM_INT);
             $query->bindParam(':limit', $limit, PDO::PARAM_INT);
             $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
             $query->execute();

            // get row count
            $rowCount = $query->rowCount();
            // check if row was actually inserted, PDO exception should have caught it if not.
            if($rowCount === 0):
              // set up response for unsuccessful return
              $response = new Response();
              $response->setHttpStatusCode(500);
              $response->setSuccess(false);
              $response->addMessage("Failed to create share type");
              $response->send();
              exit;
            endif;

            // get last  id so we can return the  in the json
            $lastID = $writeDB->lastInsertId();
            // ADD AUTH TO QUERY
            // create db query to get newly created object - get from master db not read slave as replication may be too slow for successful read
            $query = $writeDB->prepare('select * from share_settings where share_id =:id and saccos_sacco_id = :saccoid');
            $query->bindParam(':id', $lastID, PDO::PARAM_INT);
            $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
            $query->execute();

            // get row count
            $rowCount = $query->rowCount();

            // make sure that the new  was returned
            if($rowCount === 0):
              // set up response for unsuccessful return
              $response = new Response();
              $response->setHttpStatusCode(500);
              $response->setSuccess(false);
              $response->addMessage("Failed to retrieve share type after creation");
              $response->send();
              exit;
            endif;

            // create  array to store returned
            $shareArray = array();
            // for each row returned
            while($row = $query->fetch(PDO::FETCH_ASSOC)):
              $share = new Share($row['share_id'], $row['share_name'], $row['share_price'], $row['share_limit']);
              $shareArray[] = $share->returnShareAsArray();
            endwhile;
            // bundle  and rows returned into an array to return in the json data
            $returnData = array();
            $returnData['rows_returned'] = $rowCount;
            $returnData['share'] = $shareArray;

            //set up response for successful return
            $response = new Response();
            $response->setHttpStatusCode(201);
            $response->setSuccess(true);
            $response->addMessage("share type created");
            $response->setData($returnData);
            $response->send();
            exit;
          }
          // if  fails to create due to data types, missing fields or invalid data then send error json
          catch(ShareException $ex) {
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
            $response->addMessage("Failed to insert share type into database - check submitted data for errors");
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
      else:
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("Suited Endpoint not found");
        $response->send();
        exit;
    endif;
    break;

    case 'loans':
    if (array_key_exists("value", $_GET)):
                // get  from query string
          $loanid = $_GET['value'];

      //check to see if id in query string is not empty and is number, if not return json error
          if($loanid == '' || !is_numeric($loanid)):
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage("loan id info cannot be blank or must be string");
            $response->send();
            exit;
          endif;

                if($_SERVER['REQUEST_METHOD'] === 'GET'):
                  // attempt to query the database
                  try {
                    // create db query
                    // ADD AUTH TO QUERY
                    $query = $readDB->prepare('select * from loan_settings where saccos_sacco_id = :saccoid and loan_settings_id = :loanid');
                    $query->bindParam(':loanid', $loanid, PDO::PARAM_INT);
                    $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
                    $query->execute();

                    // get row count
                    $rowCount = $query->rowCount();

                    // create  array to store returned  object
                    $loanArray = array();

                    if($rowCount === 0):
                      // set up response for unsuccessful return
                      $response = new Response();
                      $response->setHttpStatusCode(404);
                      $response->setSuccess(false);
                      $response->addMessage("loan type not found for particular info");
                      $response->send();
                      exit;
                    endif;

                    // for each row returned
                    while($row = $query->fetch(PDO::FETCH_ASSOC)):
                      $loan = new Loan($row['loan_settings_id'], $row['loan_settings_name'], $row['loan_setting_interest'], $row['loan_setting_period'], $row['loan_setting_penalty'],$row['loan_setting_frequency'],$row['loan_setting_service_fee'],$row['loan_setting_notes']);
                      $loanArray[] = $loan->returnLoanAsArray();
                    endwhile;

                    // bundle s and rows returned into an array to return in the json data
                    $returnData = array();
                    $returnData['rows_returned'] = $rowCount;
                    $returnData['loan'] = $loanArray;

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
                  catch(LoanException $ex) {
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
                    $response->addMessage("Failed to get loan settings");
                    $response->send();
                    exit;
                  }
                  // else if request if a DELETE e.g. delete
                  elseif($_SERVER['REQUEST_METHOD'] === 'DELETE'):
                    // attempt to query the database
                    try {
                      // ADD AUTH TO QUERY
                      $query = $writeDB->prepare('delete from loan_settings where loan_settings_id = :loanid and saccos_sacco_id = :saccoid');
                      $query->bindParam(':loanid', $loanid, PDO::PARAM_INT);
                      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
                      $query->execute();

                      // get row count
                      $rowCount = $query->rowCount();

                      if($rowCount === 0):
                              // set up response for unsuccessful return
                              $response = new Response();
                              $response->setHttpStatusCode(404);
                              $response->setSuccess(false);
                              $response->addMessage("loan type not found, please try again");
                              $response->send();
                              exit;
                      else:
                              $response = new Response();
                              $response->setHttpStatusCode(201);
                              $response->setSuccess(true);
                              $response->addMessage("loan has been deleted");
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
                      $response->addMessage("Failed to delete loan - Attached Info");
                      $response->send();
                      exit;
                  }
        elseif($_SERVER['REQUEST_METHOD'] === 'PATCH'):
                    // update
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

                      // set  field updated to false initially
                      $name = false;
                      $interest = false;
                      $period = false;
                      $penalty = false;
                      $frequency = false;
                      $fee = false;
                      $notes = false;
                          // create blank query fields string to append each field to
                      $queryFields = "";

                      // check if  exists in PATCH
                      if(isset($jsonData->name)):
                        // set  field updated to true
                        $name = true;
                        // add  field to query field string
                        $queryFields .= "loan_settings_name = :name, ";
                      endif;
                      // check if  exists in PATCH
                      if(isset($jsonData->interest)):
                        // set  field updated to true
                        $interest = true;
                        // add  field to query field string
                        $queryFields .= "loan_setting_interest = :interest, ";
                      endif;
                      // check if  exists in PATCH
                      if(isset($jsonData->period)):
                        // set  field updated to true
                        $period = true;
                        // add  field to query field string
                        $queryFields .= "loan_setting_period = :period, ";
                      endif;
                      // check if  exists in PATCH
                      if(isset($jsonData->penalty)):
                        // set  field updated to true
                        $penalty = true;
                        // add  field to query field string
                        $queryFields .= "loan_setting_penalty = :penalty, ";
                      endif;
                      // check if  exists in PATCH
                      if(isset($jsonData->frequency)):
                        // set  field updated to true
                        $frequency = true;
                        // add  field to query field string
                        $queryFields .= "loan_setting_frequency = :frequency, ";
                      endif;
                      // check if  exists in PATCH
                      if(isset($jsonData->fee)):
                        // set  field updated to true
                        $fee = true;
                        // add  field to query field string
                        $queryFields .= "loan_setting_service_fee = :fee, ";
                      endif;
                      // check if  exists in PATCH
                      if(isset($jsonData->notes)):
                        // set  field updated to true
                        $notes = true;
                        // add  field to query field string
                        $queryFields .= "loan_setting_notes = :notes, ";
                      endif;
                      // remove the right hand comma and trailing space
                      $queryFields = rtrim($queryFields, ", ");

                      // check if any fields supplied in JSON
                      if($name === false && $interest === false && $period === false && $penalty === false && $frequency === false && $fee === false && $notes === false):
                        $response = new Response();
                        $response->setHttpStatusCode(400);
                        $response->setSuccess(false);
                        $response->addMessage("no fields provided");
                        $response->send();
                        exit;
                      endif;

                      // create db query to get  from database to update - use master db
                      $query = $writeDB->prepare('select * from loan_settings where loan_settings_id = :loanid and saccos_sacco_id = :saccoid');
                      $query->bindParam(':loanid', $loanid, PDO::PARAM_INT);
                      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
                      $query->execute();

                      // get row count
                      $rowCount = $query->rowCount();

                      // make sure that the  exists for a given  id
                      if($rowCount === 0):
                        // set up response for unsuccessful return
                        $response = new Response();
                        $response->setHttpStatusCode(404);
                        $response->setSuccess(false);
                        $response->addMessage("no loan type found to update");
                        $response->send();
                        exit;
                      endif;

                      // for each row returned - should be just one
                      while($row = $query->fetch(PDO::FETCH_ASSOC)):
                        $loan = new Loan($row['loan_settings_id'], $row['loan_settings_name'], $row['loan_setting_interest'], $row['loan_setting_period'], $row['loan_setting_penalty'],$row['loan_setting_frequency'],$row['loan_setting_service_fee'],$row['loan_setting_notes']);
                      endwhile;

                      // create the query string including any query fields
                      $queryString = "UPDATE loan_settings set ".$queryFields." where loan_settings_id = :loanid and saccos_sacco_id = :saccoid";
                      // prepare the query
                      $query = $writeDB->prepare($queryString);

                      // if field has been provided
                      if($name === true):
                        // set  object  to given value (checks for valid input)
                        $loan->setName($jsonData->name);
                        // get the value back as the object could be handling the return of the value differently to
                        // what was provided
                        $up_name = $loan->getName();
                        // bind the parameter of the new value from the object to the query (prevents SQL injection)
                        $query->bindParam(':name', $up_name, PDO::PARAM_STR);
                      endif;
                      // if field has been provided
                      if($interest === true):
                        // set  object  to given value (checks for valid input)
                        $loan->setInterest($jsonData->interest);
                        // get the value back as the object could be handling the return of the value differently to
                        // what was provided
                        $up_interest = $loan->getInterest();
                        // bind the parameter of the new value from the object to the query (prevents SQL injection)
                        $query->bindParam(':interest', $up_interest, PDO::PARAM_STR);
                      endif;

                      // if field has been provided
                      if($period === true):
                        // set  object  to given value (checks for valid input)
                        $loan->setPeriod($jsonData->period);
                        // get the value back as the object could be handling the return of the value differently to
                        // what was provided
                        $up_period = $loan->getPeriod();
                        // bind the parameter of the new value from the object to the query (prevents SQL injection)
                        $query->bindParam(':period', $up_period, PDO::PARAM_STR);
                      endif;

                      // if field has been provided
                      if($penalty === true):
                        // set  object  to given value (checks for valid input)
                        $loan->setPenalty($jsonData->penalty);
                        // get the value back as the object could be handling the return of the value differently to
                        // what was provided
                        $up_penalty = $loan->getPenalty();
                        // bind the parameter of the new value from the object to the query (prevents SQL injection)
                        $query->bindParam(':penalty', $up_penalty, PDO::PARAM_STR);
                      endif;

                      // if field has been provided
                      if($frequency === true):
                        // set  object  to given value (checks for valid input)
                        $loan->setFrequency($jsonData->frequency);
                        // get the value back as the object could be handling the return of the value differently to
                        // what was provided
                        $up_frequency = $loan->getFrequency();
                        // bind the parameter of the new value from the object to the query (prevents SQL injection)
                        $query->bindParam(':frequency', $up_frequency, PDO::PARAM_STR);
                      endif;
                      // if field has been provided
                      if($fee === true):
                        // set  object  to given value (checks for valid input)
                        $loan->setFee($jsonData->fee);
                        // get the value back as the object could be handling the return of the value differently to
                        // what was provided
                        $up_fee = $loan->getFee();
                        // bind the parameter of the new value from the object to the query (prevents SQL injection)
                        $query->bindParam(':fee', $up_fee, PDO::PARAM_STR);
                      endif;
                      // if field has been provided
                      if($notes === true):
                        // set  object  to given value (checks for valid input)
                        $loan->setNotes($jsonData->notes);
                        // get the value back as the object could be handling the return of the value differently to
                        // what was provided
                        $up_notes = $loan->getNotes();
                        // bind the parameter of the new value from the object to the query (prevents SQL injection)
                        $query->bindParam(':notes', $up_notes, PDO::PARAM_STR);
                      endif;

                      // bind the  id provided in the query string
                      $query->bindParam(':loanid', $loanid, PDO::PARAM_INT);
                      // bind the  id returned
                      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
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
                        $response->addMessage("not updated - given values may be the same as the stored values");
                        $response->send();
                        exit;
                      endif;

                      // ADD AUTH TO QUERY
                      // create db query to return the newly edited  - connect to master database
                      $query = $writeDB->prepare('select * from loan_settings where loan_settings_id = :loanid and saccos_sacco_id = :saccoid ');
                      $query->bindParam(':loanid', $loanid, PDO::PARAM_INT);
                      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
                      $query->execute();

                      // get row count
                      $rowCount = $query->rowCount();

                      // check if  was found
                      if($rowCount === 0):
                        // set up response for unsuccessful return
                        $response = new Response();
                        $response->setHttpStatusCode(404);
                        $response->setSuccess(false);
                        $response->addMessage("no loan type found");
                        $response->send();
                        exit;
                      endif;
                      // create  array to store returned
                      $loanArray = array();
                      // for each row returned
                      while($row = $query->fetch(PDO::FETCH_ASSOC)):
                        $loan = new Loan($row['loan_settings_id'], $row['loan_settings_name'], $row['loan_setting_interest'], $row['loan_setting_period'], $row['loan_setting_penalty'],$row['loan_setting_frequency'],$row['loan_setting_service_fee'],$row['loan_setting_notes']);
                        $loanArray[] = $loan->returnLoanAsArray();
                      endwhile;
                      // bundle  and rows returned into an array to return in the json data
                      $returnData = array();
                      $returnData['rows_returned'] = $rowCount;
                      $returnData['loan'] = $loanArray;

                      // set up response for successful return
                      $response = new Response();
                      $response->setHttpStatusCode(201);
                      $response->setSuccess(true);
                      $response->addMessage("loan type has been updated");
                      $response->setData($returnData);
                      $response->send();
                      exit;
                    }
                    catch(LoanException $ex) {
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
                      $response->addMessage("Failed to update loan - check your data for errors" );
                      $response->send();
                      exit;
                    }

                  // if any other request method apart from GET, PATCH, DELETE is used then return 405 method not allowed
                else:
                    $response = new Response();
                    $response->setHttpStatusCode(405);
                    $response->setSuccess(false);
                    $response->addMessage("Request method not allowed");
                    $response->send();
                    exit;
                endif;


      // if empty array in the get function
      elseif(empty($_GET['value'])):
                if($_SERVER['REQUEST_METHOD'] === 'GET'):

                  // attempt to query the database
                  try {
                    // ADD AUTH TO QUERY
                    // create db query
                    $query = $readDB->prepare('select * from loan_settings where  saccos_sacco_id = :saccoid order by loan_settings_id desc');
                    $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
                    $query->execute();

                    // get row count
                    $rowCount = $query->rowCount();
                    // check if  was found
                    if($rowCount === 0):
                      // set up response for unsuccessful return
                      $response = new Response();
                      $response->setHttpStatusCode(404);
                      $response->setSuccess(false);
                      $response->addMessage("loan  types found");
                      $response->send();
                      exit;
                    endif;
                    // create  array to store returned
                    $loanArray = array();
                    // for each row returned
                    // for each row returned
                    while($row = $query->fetch(PDO::FETCH_ASSOC)):
                      $loan = new Loan($row['loan_settings_id'], $row['loan_settings_name'], $row['loan_setting_interest'], $row['loan_setting_period'], $row['loan_setting_penalty'],$row['loan_setting_frequency'],$row['loan_setting_service_fee'],$row['loan_setting_notes']);
                      $loanArray[] = $loan->returnLoanAsArray();
                    endwhile;

                    // bundle s and rows returned into an array to return in the json data
                    $returnData = array();
                    $returnData['rows_returned'] = $rowCount;
                    $returnData['loans'] = $loanArray;

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
                  catch(LoanException $ex) {
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
                      if(!isset($jsonData->name) || !isset($jsonData->interest) || !isset($jsonData->period) || !isset($jsonData->penalty)
                        || !isset($jsonData->frequency) || !isset($jsonData->fee)):
                        $response = new Response();
                        $response->setHttpStatusCode(400);
                        $response->setSuccess(false);
                        (!isset($jsonData->name) ? $response->addMessage("name field is mandatory and must be provided") : false);
                        (!isset($jsonData->interest) ? $response->addMessage("interest field is mandatory and must be provided") : false);
                        (!isset($jsonData->period) ? $response->addMessage("period field is mandatory and must be provided") : false);
                        (!isset($jsonData->penalty) ? $response->addMessage("penalty field is mandatory and must be provided") : false);
                        (!isset($jsonData->frequency) ? $response->addMessage("frequency field is mandatory and must be provided") : false);
                        (!isset($jsonData->fee) ? $response->addMessage("fee field is mandatory and must be provided") : false);
                        $response->send();
                        exit;
                      endif;

                      // check whether the  exists for sure
                      $query = $readDB->prepare('select * from loan_settings where loan_settings_name = :name and saccos_sacco_id = :saccoid');
                      $query->bindParam(':name', $jsonData->name, PDO::PARAM_STR);
                      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
                      $query->execute();

                      // get row count
                      $rowCount = $query->rowCount();
                      if($rowCount > 0):
                        // set up response for unsuccessful return
                        $response = new Response();
                        $response->setHttpStatusCode(409);
                        $response->setSuccess(false);
                        $response->addMessage("duplicate loan type found");
                        $response->send();
                        exit;
                      endif;

                    // create  with data, if non mandatory fields not provided then set to null
                      $loan = new Loan(null, $jsonData->name, $jsonData->interest, $jsonData->period, $jsonData->penalty, $jsonData->frequency, $jsonData->fee, (isset($jsonData->notes) ? $jsonData->notes : null));
                      // get name, store them in variables
                      $name = $loan->getName();
                      $interest = $loan->getInterest();
                      $period = $loan->getPeriod();
                      $penalty = $loan->getPenalty();
                      $frequency = $loan->getFrequency();
                      $fee = $loan->getFee();
                      $notes = $loan->getNotes();

                      // create db query
                      $query = $writeDB->prepare('insert into loan_settings
                       (loan_settings_name,loan_setting_interest,loan_setting_penalty,
                       loan_setting_period,loan_setting_frequency,loan_setting_service_fee,loan_setting_notes,saccos_sacco_id)
                       values (:name, :interest, :penalty, :period, :frequency, :fee, :notes, :saccoid)');
                       $query->bindParam(':name', $name, PDO::PARAM_STR);
                       $query->bindParam(':interest', $interest, PDO::PARAM_STR);
                       $query->bindParam(':penalty', $penalty, PDO::PARAM_STR);
                       $query->bindParam(':period', $period, PDO::PARAM_INT);
                       $query->bindParam(':frequency', $frequency, PDO::PARAM_STR);
                       $query->bindParam(':fee', $fee, PDO::PARAM_INT);
                       $query->bindParam(':notes', $notes, PDO::PARAM_STR);
                       $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
                       $query->execute();

                      // get row count
                      $rowCount = $query->rowCount();
                      // check if row was actually inserted, PDO exception should have caught it if not.
                      if($rowCount === 0):
                        // set up response for unsuccessful return
                        $response = new Response();
                        $response->setHttpStatusCode(500);
                        $response->setSuccess(false);
                        $response->addMessage("Failed to create loan type");
                        $response->send();
                        exit;
                      endif;

                      // get last  id so we can return the  in the json
                      $lastID = $writeDB->lastInsertId();
                      // ADD AUTH TO QUERY
                      // create db query to get newly created object - get from master db not read slave as replication may be too slow for successful read
                      $query = $writeDB->prepare('select * from loan_settings where loan_settings_id =:id and saccos_sacco_id = :saccoid');
                      $query->bindParam(':id', $lastID, PDO::PARAM_INT);
                      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
                      $query->execute();

                      // get row count
                      $rowCount = $query->rowCount();

                      // make sure that the new  was returned
                      if($rowCount === 0):
                        // set up response for unsuccessful return
                        $response = new Response();
                        $response->setHttpStatusCode(500);
                        $response->setSuccess(false);
                        $response->addMessage("Failed to retrieve loantype after creation");
                        $response->send();
                        exit;
                      endif;

                      // create  array to store returned
                      $loanArray = array();

                      while($row = $query->fetch(PDO::FETCH_ASSOC)):
                        $loan = new Loan($row['loan_settings_id'], $row['loan_settings_name'], $row['loan_setting_interest'], $row['loan_setting_period'], $row['loan_setting_penalty'],$row['loan_setting_frequency'],$row['loan_setting_service_fee'],$row['loan_setting_notes']);
                        $loanArray[] = $loan->returnLoanAsArray();
                      endwhile;

                      // bundle s and rows returned into an array to return in the json data
                      $returnData = array();
                      $returnData['rows_returned'] = $rowCount;
                      $returnData['loan'] = $loanArray;

                      //set up response for successful return
                      $response = new Response();
                      $response->setHttpStatusCode(201);
                      $response->setSuccess(true);
                      $response->addMessage("loan type created");
                      $response->setData($returnData);
                      $response->send();
                      exit;
                    }
                    // if  fails to create due to data types, missing fields or invalid data then send error json
                    catch(LoanException $ex) {
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
                      $response->addMessage("Failed to insert account into database - check submitted data for errors");
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
                else:
                  $response = new Response();
                  $response->setHttpStatusCode(404);
                  $response->setSuccess(false);
                  $response->addMessage("Suited Endpoint not found");
                  $response->send();
                  exit;
              endif;
            break;

            case 'capital':
            if (array_key_exists("value", $_GET)):
                        // get  from query string
                  $capitalid = $_GET['value'];

              //check to see if id in query string is not empty and is number, if not return json error
                  if($capitalid == '' || !is_numeric($capitalid)):
                    $response = new Response();
                    $response->setHttpStatusCode(400);
                    $response->setSuccess(false);
                    $response->addMessage("capital id info cannot be blank or must be string");
                    $response->send();
                    exit;
                  endif;

                        if($_SERVER['REQUEST_METHOD'] === 'GET'):
                          // attempt to query the database
                          try {
                            // create db query
                            // ADD AUTH TO QUERY
                            $query = $readDB->prepare('select * from capital_settings where saccos_sacco_id = :saccoid and capital_id = :capitalid');
                            $query->bindParam(':capitalid', $capitalid, PDO::PARAM_INT);
                            $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
                            $query->execute();

                            // get row count
                            $rowCount = $query->rowCount();

                            // create  array to store returned  object
                            $capitalArray = array();

                            if($rowCount === 0):
                              // set up response for unsuccessful return
                              $response = new Response();
                              $response->setHttpStatusCode(404);
                              $response->setSuccess(false);
                              $response->addMessage("capital  not found for particular info");
                              $response->send();
                              exit;
                            endif;

                            // for each row returned
                            while($row = $query->fetch(PDO::FETCH_ASSOC)):
                              $capital = new Capital($row['capital_id'], $row['capital_name'], $row['capital_amount'], $row['capital_date']);
                              $capitalArray[] = $capital->returnCapitalAsArray();
                            endwhile;

                            // bundle s and rows returned into an array to return in the json data
                            $returnData = array();
                            $returnData['rows_returned'] = $rowCount;
                            $returnData['capital'] = $capitalArray;

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
                          catch(CapitalException $ex) {
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
                            $response->addMessage("Failed to get capital settings");
                            $response->send();
                            exit;
                          }
                          // else if request if a DELETE e.g. delete
                          elseif($_SERVER['REQUEST_METHOD'] === 'DELETE'):
                            // attempt to query the database

                          elseif($_SERVER['REQUEST_METHOD'] === 'PATCH'):
                          // if any other request method apart from GET, PATCH, DELETE is used then return 405 method not allowed
                        else:
                            $response = new Response();
                            $response->setHttpStatusCode(405);
                            $response->setSuccess(false);
                            $response->addMessage("Request method not allowed");
                            $response->send();
                            exit;
                        endif;


              // if empty array in the get function
              elseif(empty($_GET['value'])):
                        if($_SERVER['REQUEST_METHOD'] === 'GET'):

                          // attempt to query the database
                          try {
                            // ADD AUTH TO QUERY
                            // create db query
                            $query = $readDB->prepare('select * from capital_settings where  saccos_sacco_id = :saccoid order by capital_id desc');
                            $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
                            $query->execute();

                            // get row count
                            $rowCount = $query->rowCount();
                            // check if  was found
                            if($rowCount === 0):
                              // set up response for unsuccessful return
                              $response = new Response();
                              $response->setHttpStatusCode(404);
                              $response->setSuccess(false);
                              $response->addMessage("capital types found");
                              $response->send();
                              exit;
                            endif;
                            // create  array to store returned
                            $capitalArray = array();
                            // for each row returned
                            // for each row returned
                            while($row = $query->fetch(PDO::FETCH_ASSOC)):
                              $capital = new Capital($row['capital_id'], $row['capital_name'], $row['capital_amount'], $row['capital_date']);
                              $capitalArray[] = $capital->returnCapitalAsArray();
                            endwhile;

                            // bundle s and rows returned into an array to return in the json data
                            $returnData = array();
                            $returnData['rows_returned'] = $rowCount;
                            $returnData['capital'] = $capitalArray;

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
                          catch(CapitalException $ex) {
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
                              if(!isset($jsonData->name) || !isset($jsonData->amount) || !isset($jsonData->date)):
                                $response = new Response();
                                $response->setHttpStatusCode(400);
                                $response->setSuccess(false);
                                (!isset($jsonData->name) ? $response->addMessage("name field is mandatory and must be provided") : false);
                                (!isset($jsonData->amount) ? $response->addMessage("amount field is mandatory and must be provided") : false);
                                (!isset($jsonData->date) ? $response->addMessage("date field is mandatory and must be provided") : false);
                                $response->send();
                                exit;
                              endif;

                              // check whether the  exists for sure
                              $query = $readDB->prepare('select * from capital_settings where capital_name = :name and saccos_sacco_id = :saccoid');
                              $query->bindParam(':name', $jsonData->name, PDO::PARAM_STR);
                              $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
                              $query->execute();

                              // get row count
                              $rowCount = $query->rowCount();
                              if($rowCount > 0):
                                // set up response for unsuccessful return
                                $response = new Response();
                                $response->setHttpStatusCode(409);
                                $response->setSuccess(false);
                                $response->addMessage("duplicate capital type found");
                                $response->send();
                                exit;
                              endif;

                            // create  with data, if non mandatory fields not provided then set to null
                              $capital = new Capital(null, $jsonData->name, $jsonData->amount, $jsonData->date);
                              // get name, store them in variables
                              $name = $capital->getName();
                              $amount = $capital->getAmount();
                              $date = $capital->getDate();

                              // create db query
                              $query = $writeDB->prepare('insert into capital_settings
                               (capital_name,capital_amount,capital_date,saccos_sacco_id)
                               values (:name, :amount, :date, :saccoid)');
                               $query->bindParam(':name', $name, PDO::PARAM_STR);
                               $query->bindParam(':amount', $amount, PDO::PARAM_STR);
                               $query->bindParam(':date', $date, PDO::PARAM_STR);
                               $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
                               $query->execute();

                              // get row count
                              $rowCount = $query->rowCount();
                              // check if row was actually inserted, PDO exception should have caught it if not.
                              if($rowCount === 0):
                                // set up response for unsuccessful return
                                $response = new Response();
                                $response->setHttpStatusCode(500);
                                $response->setSuccess(false);
                                $response->addMessage("Failed to create capital type");
                                $response->send();
                                exit;
                              endif;

                              // get last  id so we can return the  in the json
                              $lastID = $writeDB->lastInsertId();
                              // ADD AUTH TO QUERY
                              // create db query to get newly created object - get from master db not read slave as replication may be too slow for successful read
                              $query = $writeDB->prepare('select * from capital_settings where capital_id =:id and saccos_sacco_id = :saccoid');
                              $query->bindParam(':id', $lastID, PDO::PARAM_INT);
                              $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
                              $query->execute();

                              // get row count
                              $rowCount = $query->rowCount();

                              // make sure that the new  was returned
                              if($rowCount === 0):
                                // set up response for unsuccessful return
                                $response = new Response();
                                $response->setHttpStatusCode(500);
                                $response->setSuccess(false);
                                $response->addMessage("Failed to retrieve capital after creation");
                                $response->send();
                                exit;
                              endif;

                              // create  array to store returned
                              $capitalArray = array();

                              while($row = $query->fetch(PDO::FETCH_ASSOC)):
                                $capital = new Capital($row['capital_id'], $row['capital_name'], $row['capital_amount'], $row['capital_date']);
                                $capitalArray[] = $capital->returnCapitalAsArray();
                              endwhile;

                              // bundle s and rows returned into an array to return in the json data
                              $returnData = array();
                              $returnData['rows_returned'] = $rowCount;
                              $returnData['capital'] = $capitalArray;

                              //set up response for successful return
                              $response = new Response();
                              $response->setHttpStatusCode(201);
                              $response->setSuccess(true);
                              $response->addMessage("capital created");
                              $response->setData($returnData);
                              $response->send();
                              exit;
                            }
                            // if  fails to create due to data types, missing fields or invalid data then send error json
                            catch(CapitalException $ex) {
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
                              $response->addMessage("Failed to insert capital into database - check submitted data for errors");
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
                        else:
                          $response = new Response();
                          $response->setHttpStatusCode(404);
                          $response->setSuccess(false);
                          $response->addMessage("Suited Endpoint not found");
                          $response->send();
                          exit;
                      endif;
                    break;
                    case 'inpense':
                    if (array_key_exists("value", $_GET)):
                                // get  from query string
                          $inpenseid = $_GET['value'];

                      //check to see if id in query string is not empty and is number, if not return json error
                          if($inpenseid == '' || !is_numeric($inpenseid)):
                            $response = new Response();
                            $response->setHttpStatusCode(400);
                            $response->setSuccess(false);
                            $response->addMessage("inpense id info cannot be blank or must be string");
                            $response->send();
                            exit;
                          endif;

                                if($_SERVER['REQUEST_METHOD'] === 'GET'):
                                  // attempt to query the database
                                  try {
                                    // create db query
                                    // ADD AUTH TO QUERY
                                    $query = $readDB->prepare('select * from income_expenses where saccos_sacco_id = :saccoid and income_expense_id = :inpenseid');
                                    $query->bindParam(':inpenseid', $inpenseid, PDO::PARAM_INT);
                                    $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
                                    $query->execute();

                                    // get row count
                                    $rowCount = $query->rowCount();

                                    // create  array to store returned  object
                                    $inpenseArray = array();

                                    if($rowCount === 0):
                                      // set up response for unsuccessful return
                                      $response = new Response();
                                      $response->setHttpStatusCode(404);
                                      $response->setSuccess(false);
                                      $response->addMessage("inpense not found for particular info");
                                      $response->send();
                                      exit;
                                    endif;

                                    // for each row returned
                                    while($row = $query->fetch(PDO::FETCH_ASSOC)):
                                      $inpense = new Inpense($row['income_expense_id'], $row['income_expense_name'], $row['income_expense_type']);
                                      $inpenseArray[] = $inpense->returnInpenseAsArray();
                                    endwhile;

                                    // bundle s and rows returned into an array to return in the json data
                                    $returnData = array();
                                    $returnData['rows_returned'] = $rowCount;
                                    $returnData['inpense'] = $inpenseArray;

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
                                  catch(InpenseException $ex) {
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
                                    $response->addMessage("Failed to get loan settings");
                                    $response->send();
                                    exit;
                                  }
                                  // else if request if a DELETE e.g. delete
                                  elseif($_SERVER['REQUEST_METHOD'] === 'DELETE'):
                                    // attempt to query the database
                                    try {
                                      // ADD AUTH TO QUERY
                                      $query = $writeDB->prepare('delete from income_expenses where income_expense_id = :inpenseid and saccos_sacco_id = :saccoid');
                                      $query->bindParam(':inpenseid', $inpenseid, PDO::PARAM_INT);
                                      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
                                      $query->execute();

                                      // get row count
                                      $rowCount = $query->rowCount();

                                      if($rowCount === 0):
                                              // set up response for unsuccessful return
                                              $response = new Response();
                                              $response->setHttpStatusCode(404);
                                              $response->setSuccess(false);
                                              $response->addMessage("inpense type not found, please try again");
                                              $response->send();
                                              exit;
                                      else:
                                              $response = new Response();
                                              $response->setHttpStatusCode(201);
                                              $response->setSuccess(true);
                                              $response->addMessage("loan has been deleted");
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
                                      $response->addMessage("Failed to delete inpense - Attached Info");
                                      $response->send();
                                      exit;
                                  }
                        elseif($_SERVER['REQUEST_METHOD'] === 'PATCH'):
                                    // update
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

                                      // set  field updated to false initially
                                      $name = false;
                                          // create blank query fields string to append each field to
                                      $queryFields = "";

                                      // check if  exists in PATCH
                                      if(isset($jsonData->name)):
                                        // set  field updated to true
                                        $name = true;
                                        // add  field to query field string
                                        $queryFields .= "income_expense_name = :name, ";
                                      endif;

                                      // remove the right hand comma and trailing space
                                      $queryFields = rtrim($queryFields, ", ");

                                      // check if any fields supplied in JSON
                                      if($name === false):
                                        $response = new Response();
                                        $response->setHttpStatusCode(400);
                                        $response->setSuccess(false);
                                        $response->addMessage("no fields provided");
                                        $response->send();
                                        exit;
                                      endif;

                                      // create db query to get  from database to update - use master db
                                      $query = $writeDB->prepare('select * from income_expenses where income_expense_id = :inpenseid and saccos_sacco_id = :saccoid');
                                      $query->bindParam(':inpenseid', $inpenseid, PDO::PARAM_INT);
                                      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
                                      $query->execute();

                                      // get row count
                                      $rowCount = $query->rowCount();

                                      // make sure that the  exists for a given  id
                                      if($rowCount === 0):
                                        // set up response for unsuccessful return
                                        $response = new Response();
                                        $response->setHttpStatusCode(404);
                                        $response->setSuccess(false);
                                        $response->addMessage("no inpense type found to update");
                                        $response->send();
                                        exit;
                                      endif;

                                      // for each row returned - should be just one
                                      while($row = $query->fetch(PDO::FETCH_ASSOC)):
                                        $inpense = new Inpense($row['income_expense_id'], $row['income_expense_name'], $row['income_expense_type']);
                                      endwhile;

                                      // create the query string including any query fields
                                      $queryString = "UPDATE income_expenses set ".$queryFields." where income_expense_id = :inpenseid and saccos_sacco_id = :saccoid";
                                      // prepare the query
                                      $query = $writeDB->prepare($queryString);

                                      // if field has been provided
                                      if($name === true):
                                        // set  object  to given value (checks for valid input)
                                        $inpense->setName($jsonData->name);
                                        // get the value back as the object could be handling the return of the value differently to
                                        // what was provided
                                        $up_name = $inpense->getName();
                                        // bind the parameter of the new value from the object to the query (prevents SQL injection)
                                        $query->bindParam(':name', $up_name, PDO::PARAM_STR);
                                      endif;

                                      // bind the  id provided in the query string
                                      $query->bindParam(':inpenseid', $inpenseid, PDO::PARAM_INT);
                                      // bind the  id returned
                                      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
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
                                        $response->addMessage("not updated - given values may be the same as the stored values");
                                        $response->send();
                                        exit;
                                      endif;

                                      // ADD AUTH TO QUERY
                                      // create db query to return the newly edited  - connect to master database
                                      $query = $writeDB->prepare('select * from income_expenses where income_expense_id = :inpenseid and saccos_sacco_id = :saccoid ');
                                      $query->bindParam(':inpenseid', $inpenseid, PDO::PARAM_INT);
                                      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
                                      $query->execute();

                                      // get row count
                                      $rowCount = $query->rowCount();

                                      // check if  was found
                                      if($rowCount === 0):
                                        // set up response for unsuccessful return
                                        $response = new Response();
                                        $response->setHttpStatusCode(404);
                                        $response->setSuccess(false);
                                        $response->addMessage("no inpense type found");
                                        $response->send();
                                        exit;
                                      endif;
                                      // create  array to store returned
                                      $inpenseArray = array();
                                      // for each row returned
                                      while($row = $query->fetch(PDO::FETCH_ASSOC)):
                                        $inpense = new Inpense($row['income_expense_id'], $row['income_expense_name'], $row['income_expense_type']);
                                        $inpenseArray[] = $inpense->returnInpenseAsArray();
                                      endwhile;
                                      // bundle  and rows returned into an array to return in the json data
                                      $returnData = array();
                                      $returnData['rows_returned'] = $rowCount;
                                      $returnData['inpense'] = $inpenseArray;

                                      // set up response for successful return
                                      $response = new Response();
                                      $response->setHttpStatusCode(201);
                                      $response->setSuccess(true);
                                      $response->addMessage("loan type has been updated");
                                      $response->setData($returnData);
                                      $response->send();
                                      exit;
                                    }
                                    catch(InpenseException $ex) {
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
                                      $response->addMessage("Failed to update inpense - check your data for errors" );
                                      $response->send();
                                      exit;
                                    }

                                  // if any other request method apart from GET, PATCH, DELETE is used then return 405 method not allowed
                                else:
                                    $response = new Response();
                                    $response->setHttpStatusCode(405);
                                    $response->setSuccess(false);
                                    $response->addMessage("Request method not allowed");
                                    $response->send();
                                    exit;
                                endif;

                                elseif(empty($_GET['value']) && array_key_exists("type",$_GET)):
                                          $type = $_GET['type'];
                                          // check to see if status in query string is either income or expense
                                          if($type !== "income" && $type !== "expense"):
                                            $response = new Response();
                                            $response->setHttpStatusCode(400);
                                            $response->setSuccess(false);
                                            $response->addMessage("value must be expense or income");
                                            $response->send();
                                            exit;
                                          endif;
                                          if ($_SERVER['REQUEST_METHOD'] === 'GET'):
                                          try {

                                            // create db query
                                            $query = $readDB->prepare('select * from income_expenses where income_expense_type like :type and  saccos_sacco_id = :saccoid order by income_expense_id desc');
                                            $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
                                            $query->bindParam(':type', $type, PDO::PARAM_INT);
                                            $query->execute();

                                            // get row count
                                            $rowCount = $query->rowCount();
                                            // check if  was found
                                            if($rowCount === 0):
                                              // set up response for unsuccessful return
                                              $response = new Response();
                                              $response->setHttpStatusCode(404);
                                              $response->setSuccess(false);
                                              $response->addMessage("inpense types found");
                                              $response->send();
                                              exit;
                                            endif;
                                            // create  array to store returned
                                            $inpenseArray = array();
                                            // for each row returned
                                            // for each row returned
                                            while($row = $query->fetch(PDO::FETCH_ASSOC)):
                                              $inpense = new Inpense($row['income_expense_id'], $row['income_expense_name'], $row['income_expense_type']);
                                              $inpenseArray[] = $inpense->returnInpenseAsArray();
                                            endwhile;

                                            // bundle s and rows returned into an array to return in the json data
                                            $returnData = array();
                                            $returnData['rows_returned'] = $rowCount;
                                            $returnData['inpenses'] = $inpenseArray;

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
                                          catch(InpenseException $ex) {
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
                                            $response->addMessage("Failed to get inpense");
                                            $response->send();
                                            exit;
                                          }
                                        else:
                                            $response = new Response();
                                            $response->setHttpStatusCode(405);
                                            $response->setSuccess(false);
                                            $response->addMessage("Request method not allowed");
                                            $response->send();
                                            exit;
                                        endif;

                      // if empty array in the get function
                      elseif(empty($_GET['value'])):
                                if($_SERVER['REQUEST_METHOD'] === 'GET'):

                                  // attempt to query the database
                                  try {

                                    // create db query
                                    $query = $readDB->prepare('select * from income_expenses where  saccos_sacco_id = :saccoid order by income_expense_id desc');
                                    $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
                                    $query->execute();

                                    // get row count
                                    $rowCount = $query->rowCount();
                                    // check if  was found
                                    if($rowCount === 0):
                                      // set up response for unsuccessful return
                                      $response = new Response();
                                      $response->setHttpStatusCode(404);
                                      $response->setSuccess(false);
                                      $response->addMessage("inpense types found");
                                      $response->send();
                                      exit;
                                    endif;
                                    // create  array to store returned
                                    $inpenseArray = array();
                                    // for each row returned
                                    // for each row returned
                                    while($row = $query->fetch(PDO::FETCH_ASSOC)):
                                      $inpense = new Inpense($row['income_expense_id'], $row['income_expense_name'], $row['income_expense_type']);
                                      $inpenseArray[] = $inpense->returnInpenseAsArray();
                                    endwhile;

                                    // bundle s and rows returned into an array to return in the json data
                                    $returnData = array();
                                    $returnData['rows_returned'] = $rowCount;
                                    $returnData['inpenses'] = $inpenseArray;

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
                                  catch(InpenseException $ex) {
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
                                    $response->addMessage("Failed to get inpense");
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
                                      if(!isset($jsonData->name) || !isset($jsonData->type)):
                                        $response = new Response();
                                        $response->setHttpStatusCode(400);
                                        $response->setSuccess(false);
                                        (!isset($jsonData->name) ? $response->addMessage("name field is mandatory and must be provided") : false);
                                        (!isset($jsonData->type) ? $response->addMessage("type field is mandatory and must be provided") : false);
                                        $response->send();
                                        exit;
                                      endif;

                                      // check whether the  exists for sure
                                      $query = $readDB->prepare('select * from income_expenses where income_expense_name = :name and saccos_sacco_id = :saccoid');
                                      $query->bindParam(':name', $jsonData->name, PDO::PARAM_STR);
                                      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
                                      $query->execute();

                                      // get row count
                                      $rowCount = $query->rowCount();
                                      if($rowCount > 0):
                                        // set up response for unsuccessful return
                                        $response = new Response();
                                        $response->setHttpStatusCode(409);
                                        $response->setSuccess(false);
                                        $response->addMessage("duplicate inpense type found");
                                        $response->send();
                                        exit;
                                      endif;

                                    // create  with data, if non mandatory fields not provided then set to null
                                      $inpense = new Inpense(null, $jsonData->name, $jsonData->type);
                                      // get name, store them in variables
                                      $name = $inpense->getName();
                                      $type = $inpense->getType();

                                      // create db query
                                      $query = $writeDB->prepare('insert into income_expenses
                                       (income_expense_name,income_expense_type,saccos_sacco_id)
                                       values (:name, :type, :saccoid)');
                                       $query->bindParam(':name', $name, PDO::PARAM_STR);
                                       $query->bindParam(':type', $type, PDO::PARAM_STR);
                                       $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
                                       $query->execute();

                                      // get row count
                                      $rowCount = $query->rowCount();
                                      // check if row was actually inserted, PDO exception should have caught it if not.
                                      if($rowCount === 0):
                                        // set up response for unsuccessful return
                                        $response = new Response();
                                        $response->setHttpStatusCode(500);
                                        $response->setSuccess(false);
                                        $response->addMessage("Failed to create type");
                                        $response->send();
                                        exit;
                                      endif;

                                      // get last  id so we can return the  in the json
                                      $lastID = $writeDB->lastInsertId();
                                      // ADD AUTH TO QUERY
                                      // create db query to get newly created object - get from master db not read slave as replication may be too slow for successful read
                                      $query = $writeDB->prepare('select * from income_expenses where income_expense_id =:id and saccos_sacco_id = :saccoid');
                                      $query->bindParam(':id', $lastID, PDO::PARAM_INT);
                                      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
                                      $query->execute();

                                      // get row count
                                      $rowCount = $query->rowCount();

                                      // make sure that the new  was returned
                                      if($rowCount === 0):
                                        // set up response for unsuccessful return
                                        $response = new Response();
                                        $response->setHttpStatusCode(500);
                                        $response->setSuccess(false);
                                        $response->addMessage("Failed to retrieve inpense after creation");
                                        $response->send();
                                        exit;
                                      endif;

                                      // create  array to store returned
                                      $inpenseArray = array();

                                      while($row = $query->fetch(PDO::FETCH_ASSOC)):
                                        $inpense = new Inpense($row['income_expense_id'], $row['income_expense_name'], $row['income_expense_type']);
                                        $inpenseArray[] = $inpense->returnInpenseAsArray();
                                      endwhile;

                                      // bundle s and rows returned into an array to return in the json data
                                      $returnData = array();
                                      $returnData['rows_returned'] = $rowCount;
                                      $returnData['inpense'] = $inpenseArray;

                                      //set up response for successful return
                                      $response = new Response();
                                      $response->setHttpStatusCode(201);
                                      $response->setSuccess(true);
                                      $response->addMessage("Inpense type created");
                                      $response->setData($returnData);
                                      $response->send();
                                      exit;
                                    }
                                    // if  fails to create due to data types, missing fields or invalid data then send error json
                                    catch(InpenseException $ex) {
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
                                      $response->addMessage("Failed to insert inpense into database - check submitted data for errors");
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
                                else:
                                  $response = new Response();
                                  $response->setHttpStatusCode(404);
                                  $response->setSuccess(false);
                                  $response->addMessage("Suited Endpoint not found");
                                  $response->send();
                                  exit;
                              endif;
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
