<?php

require_once('../core/initialize.php');
require_once('Database.php');
require_once('../model/Response.php');

// note: don't cache login or token http requests/responses
// (our response model defaults to no cache unless specifically set)
// attempt to set up connections to db connections
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

    if ($_SERVER['REQUEST_METHOD'] === 'POST'):
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

        // check if patch request contains  data in body as these are mandatory
        if(!isset($jsonData->oldpass) || !isset($jsonData->newpass)):
          $response = new Response();
          $response->setHttpStatusCode(400);
          $response->setSuccess(false);
          (!isset($jsonData->oldpass) ? $response->addMessage("Old password field is mandatory and must be provided") : false);
          (!isset($jsonData->newpass) ? $response->addMessage("New Password  field is mandatory and must be provided") : false);
          $response->send();
          exit;
        endif;
          $oldpass = $jsonData->oldpass;
          $newpass = $jsonData->newpass;

          $query = $readDB->prepare('select user_password from users where user_id = :id');
          $query->bindParam(':id', $returned_id, PDO::PARAM_INT);
          $query->execute();

          $rowCount = $query->rowCount();
          if ($rowCount === 0):
            $response = new Response();
            $response->setHttpStatusCode(404);
            $response->setSuccess(false);
            $response->addMessage("no sacco found");
            $response->send();
          endif;

          $row = $query->fetch(PDO::FETCH_ASSOC);
           if (!password_verify($oldpass, $row['user_password'])):
             $response = new Response();
             $response->setHttpStatusCode(406);
             $response->setSuccess(false);
             $response->addMessage("incorrect old password");
             $response->send();
             exit;
            endif;
            $pass_hash = password_hash($newpass, PASSWORD_DEFAULT);
            $query = $writeDB->prepare('update users set user_password = :password where user_id = :id');
            $query->bindParam(':password', $pass_hash, PDO::PARAM_STR);
            $query->bindParam(':id', $returned_id, PDO::PARAM_INT);
            $query->execute();
            $rowCount = $query->rowCount();

            if ($rowCount === 0 ):
              $response = new Response();
              $response->setHttpStatusCode(400);
              $response->setSuccess(false);
              $response->addMessage("error updating");
              $response->send();
            endif;

            $response = new Response();
            $response->setHttpStatusCode(201);
            $response->setSuccess(true);
            $response->addMessage("Password updated success");
            $response->send();
      } catch (PDOException $ex) {
        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage("interenal server error");
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
