<?php

require_once('../core/initialize.php');
require_once('Database.php');
require_once('../model/Response.php');
require_once('../model/Member.php');
require_once('../model/Image.php');


// function to get task images for given task id and user, returns an array of images
function retrieveTaskImages($dbConn, $memberid, $returned_saccoid) {

  // ADD AUTH TO QUERY
  // create db query to get task images
  $imageQuery = $dbConn->prepare('SELECT tblimages.id, tblimages.title, tblimages.filename, tblimages.mimetype, tblimages.members_member_id from tblimages, members where members.member_id = :id and members.saccos_sacco_id = :saccoid and tblimages.members_member_id = members.member_id');
  $imageQuery->bindParam(':id', $memberid, PDO::PARAM_INT);
  $imageQuery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
  $imageQuery->execute();

  // create image array to store returned images
  $imageArray = array();

  // while there are images in the table for this task
  while($imageRow = $imageQuery->fetch(PDO::FETCH_ASSOC)) {
    // create new image object from what has been returned from database
    $image = new Image($imageRow['id'], $imageRow['title'], $imageRow['filename'], $imageRow['mimetype'], $imageRow['members_member_id']);
    // create image and store in array for return in json data
    $imageArray[] = $image->returnImageAsArray();
  }

  return $imageArray;
}


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
  $query = $writeDB->prepare('select user_id, access_token_expiry, user_status, saccos_sacco_id, user_fullname, user_login_attempts from sessions_users, users where sessions_users.users_user_id = users.user_id and access_token = :accesstoken');
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
if($_SERVER['REQUEST_METHOD'] === 'GET') {

  // attempt to query the database
  try {
    // ADD AUTH TO QUERY
    // create db query
    $query = $readDB->prepare('SELECT * from members where saccos_sacco_id = :saccoid and users_user_id = :userid ORDER BY member_id DESC');
    $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
    $query->bindParam(':userid', $returned_id, PDO::PARAM_INT);
    $query->execute();

    // get row count
    $rowCount = $query->rowCount();

    // create task array to store returned tasks
    $memberArray = array();

    // for each row returned
    while($row = $query->fetch(PDO::FETCH_ASSOC)) {

      // get Task Images for given task, store them in array to pass to task
      $imageArray = retrieveTaskImages($readDB, $row['member_id'], $returned_saccoid);

      // create new task object for each row
      $member = new Member(
        $row['member_id'], $row['member_fname'], $row['member_lname'], $row['member_contact'], $row['member_gender'],
        $row['member_email'], $row['member_address'], $row['member_date_birth'], $row['member_join_date'], $row['member_identification'],
        $row['member_status'], $row['member_type'], $row['members_account_number'], $row['member_attach'], $row['members_account_volunteer'],
        $row['members_account_fixed'],
        $row['members_account_compuslaory'], $row['member_account_shares'],
        $row['account_types_account_type_id'], $imageArray
     );
      // create task and store in array for return in json data
      $memberArray[] = $member->returnMemberAsArray();
    }

    // bundle tasks and rows returned into an array to return in the json data
    $returnData = array();
    $returnData['rows_returned'] = $rowCount;
    $returnData['members'] = $memberArray;

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
  catch(MemberException $ex) {
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
    $response->addMessage("Failed to get members");
    $response->send();
    exit;
  }
}
