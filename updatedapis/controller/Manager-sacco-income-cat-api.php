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


  if (array_key_exists('icatid', $_GET)) {

    $icatid = $_GET['icatid'];

    if($icatid == '' || !is_numeric($icatid)) {
      $response = new Response();
      $response->setHttpStatusCode(400);
      $response->setSuccess(false);
      $response->addMessage("icome category ID cannot be blank or must be numeric");
      $response->send();
      exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
      // we pick the orders name and products under that orders
      try {
        // create db query
        $icatquery = $writeDB->prepare('SELECT *
           from  income_categories
          WHERE icatid=:icatid AND icat_sacco_id = :saccoid');
        $icatquery->bindParam(':icatid', $icatid, PDO::PARAM_STR);
        $icatquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_STR);
        $icatquery->execute();

        $rowCount = $icatquery->rowCount();
        if($rowCount === 0) {
          // set up response for unsuccessful return
          $response = new Response();
          $response->setHttpStatusCode(404);
          $response->setSuccess(false);
          $response->addMessage("sacco income category id not found");
          $response->send();
          exit;
        }
        $icatArray = array();
          while($row = $icatquery->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $cat = array(
              "id" => $icatid,
              "incomecategory" => $income_category,
          );
            $icatArray[] = $cat;
          }

        $returnData = array();
        $returnData['rows_returned'] = $rowCount;
        $returnData['category'] = $icatArray;
        // set up response for successful return
        $response = new Response();
        $response->setHttpStatusCode(200);
        $response->setSuccess(true);
        $response->toCache(true);
        $response->setData($returnData);
        $response->send();
        exit;
      } catch (PDOException $ex) {
        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage("failed to get sacco income category");
        $response->send();
        exit;
      }

    }
    elseif($_SERVER['REQUEST_METHOD'] === 'DELETE'){
      try {
        // ADD AUTH TO QUERY
      $icatquery = $writeDB->prepare('DELETE from income_categories where icatid=:icatid AND icat_sacco_id = :saccoid');
      $icatquery->bindParam(':icatid', $icatid, PDO::PARAM_STR);
      $icatquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_STR);
        $query->execute();

        // get row count
        $rowCount = $query->rowCount();

        if($rowCount === 0):
                // set up response for unsuccessful return
                $response = new Response();
                $response->setHttpStatusCode(404);
                $response->setSuccess(false);
                $response->addMessage("sacco income category id not found");
                $response->send();
                exit;
        else:
                $response = new Response();
                $response->setHttpStatusCode(200);
                $response->setSuccess(true);
                $response->addMessage("sacco income category deleted");
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
        $response->addMessage("Failed to delete sacco income caegory - Attached Info");
        $response->send();
        exit;
      }

    }
    elseif($_SERVER['REQUEST_METHOD'] === 'PATCH'){
    try{
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

      $incomecatupdate = false;

      // create blank query fields string to append each field to
      $queryFields = "";

      // check if name exists in PATCH
      if(isset($jsonData->icategory)):
        // set title field updated to true
        $incomecatupdate = true;
        // add name field to query field string
        $queryFields .= "income_category  = :category, ";
      endif;

      // remove the right hand comma and trailing space
      $queryFields = rtrim($queryFields, ", ");

      // check if any branch fields supplied in JSON
      if($incomecatupdate === false):
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("No fields provided");
        $response->send();
        exit;
      endif;
      // ADD AUTH TO QUERY
      // create db query to get branch from database to update - use master db
      $icatquery = $writeDB->prepare('SELECT * from income_categories where icatid=:icatid AND icat_sacco_id = :saccoid');
      $icatquery->bindParam(':icatid', $icatid, PDO::PARAM_STR);
      $icatquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_STR);
      $icatquery->execute();

      // get row count
      $rowCount = $icatquery->rowCount();

      // make sure that the branch exists for a given branch id
      if($rowCount === 0):
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("No sacco income category found to update");
        $response->send();
        exit;
      endif;
      // create the query string including any query fields
      $queryString = "UPDATE income_categories set ".$queryFields." where icatid  = :id";
      // prepare the query
      $icatquery = $writeDB->prepare($queryString);

      // if name has been provided
      if($incomecatupdate === true):
        // bind the parameter of the new value from the object to the query (prevents SQL injection)
        $icatquery->bindParam(':category', $jsonData->icategory, PDO::PARAM_STR);
      endif;

      $icatquery->bindParam(':id', $icatid, PDO::PARAM_STR);
      $icatquery->execute();

      $rowCount = $icatquery->rowCount();

      if($rowCount === 0):
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("not updated - given values may be the same as the stored values");
        $response->send();
        exit;
      endif;
      $returnData = array();
      $returnData['rows_returned'] = $rowCount;
      // $returnData['cropType'] = $cropTypeArray;

      // set up response for successful return
      $response = new Response();
      $response->setHttpStatusCode(200);
      $response->setSuccess(true);
      $response->addMessage("income category has been updated");
      $response->setData($returnData);
      $response->send();
      exit;

    }
    catch(PDOException $ex) {
      $response = new Response();
      $response->setHttpStatusCode(400);
      $response->setSuccess(false);
      $response->addMessage("failed to update $ex");
      $response->send();
      exit;
    }
    // if error with sql query return a json error
    catch(PDOException $ex) {
      error_log("Database Query Error: ".$ex, 0);
      $response = new Response();
      $response->setHttpStatusCode(500);
      $response->setSuccess(false);
      $response->addMessage("Failed to update income category - check your data for errors" . $ex);
      $response->send();
      exit;
    }
    }

    else{
      // return a json response on method not allowed
      $response = new Response();
      $response->setHttpStatusCode(405);
      $response->setSuccess(false);
      $response->addMessage("Request method not allowed");
      $response->send();
      exit;
    }

  } elseif(empty($_GET)){
        // get the user profile data
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {

        try {
          $icatquery = $writeDB->prepare('SELECT * from income_categories WHERE icat_sacco_id = :saccoid');
          $icatquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_STR);
          $icatquery->execute();

          $rowsCount = $icatquery->rowCount();

          $icatArray = array();
            while($row = $icatquery->fetch(PDO::FETCH_ASSOC)) {
              extract($row);
              $categories = array(
                "id" => $icatid,
                "incomecategory" => $income_category,
            );
              $icatArray[] = $categories;
            }

          $returnData = array();
          $returnData['rows_returned'] = $rowsCount;
          $returnData['categories'] = $icatArray;
            // set up response for successful return
            $response = new Response();
            $response->setHttpStatusCode(200);
            $response->setSuccess(true);
            $response->toCache(true);
            $response->setData($returnData);
            $response->send();
            exit;
        } catch (PDOException $ex) {
          // error_log("query error: ${ex}", 3 ,"../../app/logs/error.log");
          $response = new Response();
          $response->setHttpStatusCode(500);
          $response->setSuccess(false);
          $response->addMessage("internal server error $ex");
          $response->send();
          exit;
        }

    }
    elseif($_SERVER['REQUEST_METHOD'] === 'POST'){

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

          // pick the data in
          // get POST request body as the posted data will be JSON format
          $rawPostData = file_get_contents('php://input');

          if(!$jsonData = json_decode($rawPostData)){
            // set up response for unsuccessful request
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage("Request body is not valid JSON");
            $response->send();
            exit;
          }

          // check if post request contains data in body as these are mandatory
          // check to make sure that sacco name email and password are not empty and less than 255 long
          if(!isset($jsonData->category)|| empty($jsonData->category)):
          // || strlen($jsonData->password) < 1 || strlen($jsonData->password) > 100):
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            (!isset($jsonData->category)? $response->addMessage("category field is mandatory and must be provided") : false);
            (empty($jsonData->category)? $response->addMessage("category cannot be blank") : false);
            $response->send();
            exit;
          endif;

          try{
            // $rowCount=0;
            // $lastID=0;
            $query = $writeDB->prepare('SELECT * from income_categories where income_category = :category');
            $query->bindParam(':category', $jsonData->category, PDO::PARAM_STR);
            $query->execute();

            $rowCount = $query->rowCount();
            if ($rowCount > 0) {
              $response = new Response();
              $response->setHttpStatusCode(400);
              $response->setSuccess(false);
              $response->addMessage("this income category $jsonData->category already exists");
              $response->send();
              exit;
            }
            $_category = trim($jsonData->category);

              $query = $writeDB->prepare('INSERT into income_categories(
              `income_category`,
              `icat_sacco_id`
            ) values (:category, :saccoid)');
              $query->bindParam(':category', $_category, PDO::PARAM_STR);
              $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
              $query->execute();
              $lastID = $writeDB->lastInsertId();
              $rowCount = $query->rowCount();
              if ($rowCount === 0) {
                $response = new Response();
                $response->setHttpStatusCode(500);
                $response->setSuccess(false);
                $response->addMessage("internal server error");
                $response->send();
                exit;
              }

              $query = $writeDB->prepare('SELECT * from income_categories where icatid = :id');
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
              $icatArray = array();
                while($row = $query->fetch(PDO::FETCH_ASSOC)) {
                  extract($row);
                  $categories = array(
                    "id" => $icatid,
                    "incomecategory" => $income_category,
                );
                  $icatArray[] = $categories;
                }
              // bundle branch and rows returned into an array to return in the json data
              $returnData = array();
              $returnData['rows_returned'] = $rowCount;
              $returnData['category'] = $icatArray;

              // set up response for successful return
              $response = new Response();
              $response->setHttpStatusCode(201);
              $response->setSuccess(true);
              $response->setData($returnData);
              $response->send();
              exit;
            }
            catch (PDOException $ex) {
              // error_log("query error: ${ex}", 3 ,"../../app/logs/error.log");
              $response = new Response();
              $response->setHttpStatusCode(500);
              $response->setSuccess(false);
              $response->addMessage("internal server error $ex");
              $response->send();
              exit;
            }
        } catch (PDOException $ex) {
          // error_log("query error: ${ex}", 3 ,"../../app/logs/error.log");
          $response = new Response();
          $response->setHttpStatusCode(500);
          $response->setSuccess(false);
          $response->addMessage("internal server error");
          $response->send();
          exit;
        }

    }
 else {
      $response = new Response();
      $response->setHttpStatusCode(405);
      $response->setSuccess(false);
      $response->addMessage("request method not allowed");
      $response->send();
    }
  }
  else {
    $response = new Response();
    $response->setHttpStatusCode(404);
    $response->setSuccess(false);
    $response->addMessage("endpoint not found");
    $response->send();
  }
