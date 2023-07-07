<?php
require_once('../core/initialize.php');
require_once('Database.php');
require_once('../model/Member.php');
require_once('../model/Response.php');
require_once('../model/Image.php');

// function to get  images for given  id and user, returns an array of images
function retrieveImages($dbConn, $memberid, $returned_saccoid) {

  // ADD AUTH TO QUERY
  // create db query to get  images
  $imageQuery = $dbConn->prepare('SELECT tblimages.id, tblimages.title, tblimages.filename, tblimages.mimetype, tblimages.members_member_id from tblimages, members where members.member_id = :id and members.saccos_sacco_id = :saccoid and tblimages.members_member_id = members.member_id');
  $imageQuery->bindParam(':id', $memberid, PDO::PARAM_INT);
  $imageQuery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
  $imageQuery->execute();

  // create image array to store returned images
  $imageArray = array();

  // while there are images in the table for this
  while($imageRow = $imageQuery->fetch(PDO::FETCH_ASSOC)) {
    // create new image object from what has been returned from database
    $image = new Image($imageRow['id'], $imageRow['title'], $imageRow['filename'], $imageRow['mimetype'], $imageRow['members_member_id']);
    // create image and store in array for return in json data
    $imageArray[] = $image->returnImageAsArray();
  }

  return $imageArray;
}


// function to get  next of kin for given  id and user, returns an array of images
function retrieveNextOfKin($dbConn, $memberid, $returned_saccoid) {
  // ADD AUTH TO QUERY
  // create db query to get  images
  $nextokinquery = $dbConn->prepare('SELECT kin_id, first_name, midle_name, last_name, gender, relationship, identification,
    date_of_birth, inheritence,phone_number, email, address, members_id, saccos_sacco_id, date_registered
     FROM next_of_kin
    WHERE saccos_sacco_id = :saccoid and members_id =:id');
  $nextokinquery->bindParam(':id', $memberid, PDO::PARAM_INT);
  $nextokinquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
  $nextokinquery->execute();

  // create image array to store returned images
  $nextofkinArray = array();

  // while there are images in the table for this
  while($nextOfKinRow = $nextokinquery->fetch(PDO::FETCH_ASSOC)) {
    // ($id,$memberid, $firstname,$midlename, $lastname, $contact, $gender, $email, $address,
  	// $dob, $doj, $identification, $relationship, $inheritance)
    // create new image object from what has been returned from database
    $nextofkin = new NextOfKin($nextOfKinRow['kin_id'], $nextOfKinRow['members_id'], $nextOfKinRow['first_name'],
     $nextOfKinRow['midle_name'],$nextOfKinRow['last_name'], $nextOfKinRow['phone_number'],$nextOfKinRow['gender'],
      $nextOfKinRow['email'],$nextOfKinRow['address'],$nextOfKinRow['date_of_birth'],$nextOfKinRow['date_registered'],$nextOfKinRow['identification'],
     $nextOfKinRow['relationship'],$nextOfKinRow['inheritence']);
    // create image and store in array for return in json data
    $nextofkinArray[] = $nextofkin->returnNextOFKinAsArray();
  }

  return $nextofkinArray;
}

// function to get  next of kin for given  id and user, returns an array of images
function retrieveAccounts($dbConn, $memberid, $returned_saccoid) {
  // create db query to get  images
  $accountsQuery = $dbConn->prepare('SELECT member_accounts_id,account_name,accounts_id,
    account_code,member_accounts_date_opened from accounts,member_accounts
    WHERE accounts_id=member_accounts_account_id
    AND member_accounts_sacco_id = :saccoid and member_accounts_member_id =:id');
  $accountsQuery->bindParam(':id', $memberid, PDO::PARAM_INT);
  $accountsQuery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
  $accountsQuery->execute();
  // create image array to store returned images
  $accountsArray = array();
  // while there are images in the table for this
  while($accountsRow = $accountsQuery->fetch(PDO::FETCH_ASSOC)) {
    extract($accountsRow);
    $accountsData = array(
      "accounts_id" => $accounts_id,
      "name" => $account_name,
      "code" => $account_code,
      "dateopened" => $member_accounts_date_opened,
    );
    // create image and store in array for return in json data
    $accountsArray[] = $accountsData;
  }

  return $accountsArray;
}

function retrieveGroups($dbConn, $memberaccount, $returned_saccoid){
  $groupQuery = $dbConn->prepare('SELECT * from group_members,members
    WHERE group_account_id =member_id
    AND group_account_saccoid = :saccoid
    and group_member_account =:account');
  $groupQuery->bindParam(':account', $memberaccount, PDO::PARAM_INT);
  $groupQuery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
  $groupQuery->execute();
  // create image array to store returned images
  $groupsArray = array();
  // while there are images in the table for this
  while($groupRows = $groupQuery->fetch(PDO::FETCH_ASSOC)) {
    extract($groupRows);
    $groupsData = array(
      "groupid" => $member_id,
      "groupname" => $member_fname,
    );
    // create image and store in array for return in json data
    $groupsArray[] = $groupsData;
  }
return $groupsArray;
}

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
// check if taskid is in the url e.g. /tasks/1
if (array_key_exists("memberid",$_GET)) { }
elseif(empty($_GET)) {

  // if request is a GET e.g. get members
  // if request is a GET e.g. get members
  if($_SERVER['REQUEST_METHOD'] === 'GET') {

    // attempt to query the database
    try {
      // ADD AUTH TO QUERY
      // create db query
      $query = $readDB->prepare('SELECT * from members where saccos_sacco_id = :saccoid
        AND member_type="individual" ORDER BY member_id DESC');
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $query->execute();

      // get row count
      $rowCount = $query->rowCount();

      // create  array to store returned s
      $memberArray = array();

      // for each row returned
      while($row = $query->fetch(PDO::FETCH_ASSOC)) {

        // get  Images for given , store them in array to pass to
        $imageArray = retrieveImages($readDB, $row['member_id'], $returned_saccoid);
        $membergroupsArray = retrieveGroups($readDB, $row['members_account_number'], $returned_saccoid);

        // create new  object for each row
        $member = new Member(
          $row['member_id'], $row['member_fname'],$row['member_mname'],$row['member_lname'], $row['member_contact'], $row['member_gender'],
          $row['member_email'], $row['member_address'], $row['member_date_birth'], $row['member_join_date'], $row['member_identification'],
          $row['member_status'], $row['member_type'], $row['members_account_number'],$row['member_employment_status'], $row['member_gross_income'],
          $row['member_marital_status'],$membergroupsArray, $row['member_attach'],
          $row['members_account_fixed'],
          $row['members_account_compuslaory'], $row['member_account_shares'], $imageArray
       );
        // create  and store in array for return in json data
        $memberArray[] = $member->returnMemberAsArray();

      }

      // bundle s and rows returned into an array to return in the json data
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
    catch(PDOException $ex) {
      $response = new Response();
      $response->setHttpStatusCode(500);
      $response->setSuccess(false);
      $response->addMessage($ex);
      $response->send();
      exit;
    }
    // if error with image object  return a json error
    catch(ImageException $ex) {
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
  // if any other request method apart from GET or POST is used then return 405 method not allowed
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
