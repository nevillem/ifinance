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
// make response objects
//  function to send response
function sendResponse($statusCode, $success, $message = null, $toCache = false, $data = null) {
  // set up response object
  $response = new Response();
  $response->setHttpStatusCode($statusCode);
  $response->setSuccess($success);
  // check if message has been supplied, if so then add it to the response
  if($message != null) {
    $response->addMessage($message);
  }
  $response->toCache($toCache);
  // check if data has been supplied, if so then add it to the response
  if($data != null) {
    $response->setData($data);
  }
  $response->send();
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      // check to make sure the imagefile field has been provided in the request body
      // and that the file has uploaded ok (error of 0 means OK)
    if(!isset($_FILES['logo']) || $_FILES['logo']['error'] !== 0) {
        // send json error response using function
        sendResponse(500, false, "Image file upload unsuccessful");
      }

        $target_dir = "../uploads/";
        $temp = explode(".", $_FILES["logo"]["name"]);
        $newfilename = round(microtime(true)) . '.' . end($temp);
        $target_file = $target_dir . basename($newfilename);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

          $check = getimagesize($_FILES["logo"]["tmp_name"]);
          if($check === false) {
          sendResponse(400, false, 'this file is not an image');
          $uploadOk = 0;
          }
          // Check if file already exists
          if (file_exists($target_file)) {
            sendResponse(409, false, 'File already exists');
          $uploadOk = 0;
          }

          // Check file size
          if ($_FILES["logo"]["size"] > 500000) {
            sendResponse(400, false, 'Check your image size should be 500kBs');
          $uploadOk = 0;
          }

          // Allow certain file formats
          if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
          && $imageFileType != "gif" ) {
            sendResponse(400, false, 'wrong file type');
          $uploadOk = 0;
          }

          // Check if $uploadOk is set to 0 by an error
          if ($uploadOk == 0) {
            sendResponse(500, false, 'sorry images wasnot uploaded');

          // if everything is ok, try to upload file
          } else {
          if (move_uploaded_file($_FILES["logo"]["tmp_name"], $target_file)) {
            $data = "https://api.v4.irembofinance.com/uploads/". htmlspecialchars( basename($newfilename));
            $query = $writeDB->prepare('update saccos set sacco_logo = :url where sacco_id = :saccoid');
            $query->bindParam(':url',$data,PDO::PARAM_STR);
            $query->bindParam(':saccoid',$returned_saccoid,PDO::PARAM_INT);
            $query->execute();
            $rowCount = $query->rowCount();
            if ($rowCount === 0) {
            sendResponse(500, false, 'there is issue registering the url');
            }
            sendResponse(201, true, 'success image uploaded', false, $data);
          } else {
            sendResponse(500, false, 'Sorry, there was an error uploading your file.');
          }
          }
        }
      if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            try {
                  $query = $writeDB->prepare('select sacco_logo from saccos where sacco_id = :id');
                  $query->bindParam(':id', $returned_saccoid, PDO::PARAM_INT);
                  $query->execute();

                  $rowCount = $query->rowCount();
                  if ($rowCount === 0) {
                    sendResponse(404, false, 'sacco not found');
                  }

                  $row = $query->fetch(PDO::FETCH_ASSOC);
                  $returnData = array();
                  $returnData['logo'] = $row['sacco_logo'];
                  sendResponse(200, true, '', false, $returnData);


            } catch (PDOException $ex) {
              sendResponse(500, false, 'Sorry, there was an getting file.');

            }

      }
