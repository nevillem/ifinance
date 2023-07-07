<?php
require_once('../core/initialize.php');
require_once('Database.php');
require_once('../model/Member.php');
require_once('../model/Response.php');
require_once('../model/Image.php');
require_once('../model/nextofkin.php');
require_once('../model/accounts.php');
require_once('../model/member_account.php');
require_once('../core/classes/Account.php');

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
  $query = $writeDB->prepare('select user_id, branches_branch_id, access_token_expiry, user_status, saccos_sacco_id, user_login_attempts from sessions_users, users where sessions_users.users_user_id = users.user_id and access_token = :accesstoken');
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
  //sacco Info
  $query = $writeDB->prepare('select * from saccos where sacco_id = :id');
  $query->bindParam(':id', $returned_saccoid, PDO::PARAM_INT);
  $query->execute();
  $row = $query->fetch(PDO::FETCH_ASSOC);

  $saccoshortname = $row['sacco_short_name'];
  $sacconame = $row['sacco_name'];
  $saccocontact = $row['sacco_contact'];
  $saccoemail = $row['sacco_email'];

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

// check if id is in the url e.g. /s/1
if (array_key_exists("memberid",$_GET)) {
  // get  id from query string
  $memberid = $_GET['memberid'];
  //check to see if  id in query string is not empty and is number, if not return json error
  if($memberid == '' || !is_numeric($memberid)) {
    $response = new Response();
    $response->setHttpStatusCode(400);
    $response->setSuccess(false);
    $response->addMessage("MemberID cannot be blank or must be numeric");
    $response->send();
    exit;
  }

  // if request is a GET, e.g. get member
  if($_SERVER['REQUEST_METHOD'] === 'GET') {
    // attempt to query the database
    try {
      // create db query
      // ADD AUTH TO QUERY
      $query = $readDB->prepare('SELECT * from members where member_id = :memberid AND member_type="individual"
        and saccos_sacco_id = :saccoid');
      $query->bindParam(':memberid', $memberid, PDO::PARAM_INT);
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
  		$query->execute();

      // get row count
      $rowCount = $query->rowCount();

      // create  array to store returned
      $Array = array();

      if($rowCount === 0) {
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("Member not found");
        $response->send();
        exit;
      }

      // for each row returned
      while($row = $query->fetch(PDO::FETCH_ASSOC)) {

        // get  Images for given member, store them in array to pass to member
        $imageArray = retrieveImages($readDB, $row['member_id'], $returned_saccoid);
        $nextOfKinArrays= retrieveNextOfKin($readDB, $row['member_id'], $returned_saccoid);
        $accountsArrays= retrieveAccounts($readDB, $row['member_id'], $returned_saccoid);
        $membergroupsArray = retrieveGroups($readDB, $row['members_account_number'], $returned_saccoid);


        // create new  object for each row
        $member = new Member(
          $row['member_id'], $row['member_fname'], $row['member_mname'], $row['member_lname'], $row['member_contact'], $row['member_gender'],
          $row['member_email'], $row['member_address'], $row['member_date_birth'], $row['member_join_date'], $row['member_identification'],
          $row['member_status'], $row['member_type'], $row['members_account_number'],$row['member_employment_status'], $row['member_gross_income'],
          $row['member_marital_status'],$membergroupsArray, $row['member_attach'],
          $row['members_account_fixed'],
          $row['members_account_compuslaory'], $row['member_account_shares'],$imageArray,$nextOfKinArrays,$accountsArrays
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
    catch(MemberException $ex) {
      $response = new Response();
      $response->setHttpStatusCode(500);
      $response->setSuccess(false);
      $response->addMessage($ex->getMessage());
      $response->send();
      exit;
    }
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
      $response->addMessage("Failed to get member ");
      $response->send();
      exit;
    }
  }
  // else if request if a DELETE e.g. delete
  elseif($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // attempt to query the database
    try {
      // ADD AUTH TO QUERY
      // create db query to get  images - if  id doesnt exist then will return 0 rows
      $imageSelectQuery = $readDB->prepare('SELECT tblimages.id, tblimages.title, tblimages.filename, tblimages.mimetype, tblimages.members_member_id from tblimages, members where members.member_id = :memberid and members.saccos_sacco_id = :saccoid and tblimages.members_member_id = members.member_id');
      $imageSelectQuery->bindParam(':memberid', $memberid, PDO::PARAM_INT);
      $imageSelectQuery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $imageSelectQuery->execute();

      // while there are images in the table for this  - will not run if 0 rows are returned
      while($imageRow = $imageSelectQuery->fetch(PDO::FETCH_ASSOC)) {
        // begin transaction as we dont want the row to be deleted if the file cannot be deleted
        $writeDB->beginTransaction();
        // create new image object from what has been returned from database
        $image = new Image($imageRow['id'], $imageRow['title'], $imageRow['filename'], $imageRow['mimetype'], $imageRow['members_member_id']);

        // store image id
        $imageID = $image->getID();
        // delete all images query
        $query = $writeDB->prepare('delete tblimages from tblimages, members where tblimages.id = :imageid and tblimages.members_member_id = :memberid and tblimages.members_member_id = members.member_id and members.saccos_sacco_id = :saccoid');
        $query->bindParam(':imageid', $imageID, PDO::PARAM_INT);
        $query->bindParam(':memberid', $memberid, PDO::PARAM_INT);
        $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
        $query->execute();

        // delete the returned image
        $image->deleteImageFile();

        // commit the successful image deletion
        $writeDB->commit();
      }

      // once all images are deleted then delete the actual
      $query = $writeDB->prepare('delete from members where member_id = :memberid and saccos_sacco_id = :saccoid');
      $query->bindParam(':memberid', $memberid, PDO::PARAM_INT);
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $query->execute();

      // get row count
      $rowCount = $query->rowCount();

      if($rowCount === 0) {
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("member not found");
        $response->send();
        exit;
      }

      // set member image folder location
      $memberImageFolder = "../storage/".$memberid;

      // check to see if it exists
      if(is_dir($memberImageFolder)) {
        // if folder does exist and all files are deleted then delete the member id folder
        rmdir($memberImageFolder);
      }

      // set up response for successful return
      $response = new Response();
      $response->setHttpStatusCode(200);
      $response->setSuccess(true);
      $response->addMessage("Member deleted");
      $response->send();
      exit;
    }
    // if error with sql query return a json error
    catch(ImageException $ex) {
      // rollback transactions if any outstanding transactions are present
      if($writeDB->inTransaction()) {
        $writeDB->rollBack();
      }
      $response = new Response();
      $response->setHttpStatusCode(500);
      $response->setSuccess(false);
      $response->addMessage($ex->getMessage());
      $response->send();
      exit;
    }
    catch(PDOException $ex) {
      // rollback transactions if any outstanding transactions are present
      if($writeDB->inTransaction()) {
        $writeDB->rollBack();
      }
      $response = new Response();
      $response->setHttpStatusCode(500);
      $response->setSuccess(false);
      $response->addMessage("Failed to delete member".$ex);
      $response->send();
      exit;
    }
  }
  // handle updating
  elseif($_SERVER['REQUEST_METHOD'] === 'PATCH') {
    // update
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

      // get PATCH request body as the PATCHed data will be JSON format
      $rawPatchData = file_get_contents('php://input');

      if(!$jsonData = json_decode($rawPatchData)) {
        // set up response for unsuccessful request
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("Request body is not valid JSON");
        $response->send();
        exit;
      }

      // set  field updated to false initially
      $dob_updated = false;
      $gender_updated = false;
      $identification_updated = false;
      $status_updated = false;
      $email_updated = false;
      $doj_updated = false;
      // $attach_updated = false;
      $contact_updated = false;
      $fistname_updated = false;
      $midlename_updated = false;
      $lastname_updated = false;
      $address_updated = false;
      $employment_status_update = false;
      $gross_income_update = false;
      $gross_income_update = false;
      $marital_status_update= false;
      // create blank query fields string to append each field to
      $queryFields = "";

      // check if title exists in PATCH
      if(isset($jsonData->dob)) {
        // set title field updated to true
        $dob_updated = true;
        // add title field to query field string
        $queryFields .= "member_date_birth = :dob, ";
      }

      // check if title exists in PATCH
      if(isset($jsonData->address)) {
        // set title field updated to true
        $address_updated = true;
        // add title field to query field string
        $queryFields .= "member_address = :address, ";
      }

      // check if description exists in PATCH
      if(isset($jsonData->gender)) {
        // set description field updated to true
        $gender_updated = true;
        // add description field to query field string
        $queryFields .= "member_gender = :gender, ";
      }

      // check if deadline exists in PATCH
      if(isset($jsonData->identification)) {
        // set deadline field updated to true
        $identification_updated = true;
        // add deadline field to query field string
        $queryFields .= "member_identification = :identification, ";
      }

      // check if completed exists in PATCH
      if(isset($jsonData->status)) {
        // set completed field updated to true
        $status_updated = true;
        // add completed field to query field string
        $queryFields .= "member_status = :status, ";
      }
      // check if completed exists in PATCH
      if(isset($jsonData->email)) {
        // set completed field updated to true
        $email_updated = true;
        // add completed field to query field string
        $queryFields .= "member_email = :email, ";
      }
      // check if completed exists in PATCH
      if(isset($jsonData->doj)) {
        // set completed field updated to true
        $doj_updated = true;
        // add completed field to query field string
        $queryFields .= "member_join_date = :doj, ";
      }
      // check if completed exists in PATCH
      // if(isset($jsonData->attach)) {
      //   // set completed field updated to true
      //   $attach_updated = true;
      //   // add completed field to query field string
      //   $queryFields .= "member_attach = :attach, ";
      // }
      // check if completed exists in PATCH
      if(isset($jsonData->contact)) {
        // set completed field updated to true
        $contact_updated = true;
        // add completed field to query field string
        $queryFields .= "member_contact = :contact, ";
      }
      // check if completed exists in PATCH
      if(isset($jsonData->firstname)) {
        // set completed field updated to true
        $firstname_updated = true;
        // add completed field to query field string
        $queryFields .= "member_fname = :firstname, ";
      }
      // check if completed exists in PATCH
      if(isset($jsonData->midlename)) {
        // set completed field updated to true
        $midlename_update = true;
        // add completed field to query field string
        $queryFields .= "member_mname = :midlename, ";
      }
      // check if completed exists in PATCH
      if(isset($jsonData->contact)) {
        // set completed field updated to true
        $lastname_updated = true;
        // add completed field to query field string
        $queryFields .= "member_lname = :lastname, ";
      }
      // check if completed exists in PATCH
      if(isset($jsonData->employment_status)) {
        // set completed field updated to true
        $employment_status_update = true;
        // add completed field to query field string
        $queryFields .= "member_employment_status = :employment_status, ";
      }
      // check if completed exists in PATCH
      if(isset($jsonData->gross_income)) {
        // set completed field updated to true
        $gross_income_update = true;
        // add completed field to query field string
        $queryFields .= "member_gross_income = :gross_income, ";
      }
      // check if completed exists in PATCH
      if(isset($jsonData->marital_status)) {
        // set completed field updated to true
        $marital_status_update = true;
        // add completed field to query field string
        $queryFields .= "member_marital_status = :marital_status, ";
      }
      // remove the right hand comma and trailing space
      $queryFields = rtrim($queryFields, ", ");

      // check if any  fields supplied in JSON
      if($dob_updated === false && $gender_updated === false && $identification_updated === false && $status_updated === false
        && $email_updated === false && $doj_updated === false  && $contact_updated === false
        && $fistname_updated === false && $midlename_update === false && $lastname_updated === false
        && $address_updated === false && $employment_status_update === false && $gross_income_update === false && $marital_status_update === false) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("No member fields provided");
        $response->send();
        exit;
      }
      // ADD AUTH TO QUERY
      // create db query to get  from database to update - use master db
      $query = $writeDB->prepare('SELECT * from members where member_id = :memberid and saccos_sacco_id = :saccoid');
      $query->bindParam(':memberid', $memberid, PDO::PARAM_INT);
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $query->execute();

      // get row count
      $rowCount = $query->rowCount();

      // make sure that the  exists for a given  id
      if($rowCount === 0) {
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("No member found to update");
        $response->send();
        exit;
      }
      $emptyArray = array();
      // for each row returned - should be just one
      while($row = $query->fetch(PDO::FETCH_ASSOC)) {
        // create new  object
       $member = new Member(
         $row['member_id'], $row['member_fname'],$row['member_mname'], $row['member_lname'], $row['member_contact'], $row['member_gender'],
         $row['member_email'], $row['member_address'], $row['member_date_birth'], $row['member_join_date'], $row['member_identification'],
         $row['member_status'], $row['member_type'], $row['members_account_number'], $row['member_employment_status'], $row['member_gross_income'],
         $row['member_marital_status'],$emptyArray,$row['member_attach'], $row['members_account_fixed'],
         $row['members_account_compuslaory'], $row['member_account_shares']
      );
       }
      // ADD AUTH TO QUERY
      // create the query string including any query fields
      $queryString = "UPDATE members set ".$queryFields."
      WHERE member_id = :memberid and saccos_sacco_id = :saccoid";
      // prepare the query
      $query = $writeDB->prepare($queryString);

      // if has been provided
      if($dob_updated === true) {
        // set  object title to given value (checks for valid input)
        $member->setDob($jsonData->dob);
        // get the value back as the object could be handling the return of the value differently to
        // what was provided
        $up_dob = $member->getDob();
        // bind the parameter of the new value from the object to the query (prevents SQL injection)
        $query->bindParam(':dob', $up_dob, PDO::PARAM_STR);
      }
      // if has been provided
      if($gender_updated === true) {
        // set  object title to given value (checks for valid input)
        $member->setGender($jsonData->gender);
        // get the value back as the object could be handling the return of the value differently to
        // what was provided
        $up_gender = $member->getGender();
        // bind the parameter of the new value from the object to the query (prevents SQL injection)
        $query->bindParam(':gender', $up_gender, PDO::PARAM_STR);
      }

      // if has been provided
      if($identification_updated === true) {
        // set  object title to given value (checks for valid input)
        $member->setIDN($jsonData->identification);
        // get the value back as the object could be handling the return of the value differently to
        // what was provided
        $up_identification = $member->getIDN();
        // bind the parameter of the new value from the object to the query (prevents SQL injection)
        $query->bindParam(':identification', $up_identification, PDO::PARAM_STR);
      }

      // if has been provided
      if($email_updated === true) {
        // set  object title to given value (checks for valid input)
        $member->setEmail($jsonData->email);
        // get the value back as the object could be handling the return of the value differently to
        // what was provided
        $up_email = $member->getEmail();
        // bind the parameter of the new value from the object to the query (prevents SQL injection)
        $query->bindParam(':email', $up_email, PDO::PARAM_STR);
      }

      // if has been provided
      if($doj_updated === true) {
        // set  object title to given value (checks for valid input)
        $member->setDoj($jsonData->doj);
        // get the value back as the object could be handling the return of the value differently to
        // what was provided
        $up_doj = $member->getDoj();
        // bind the parameter of the new value from the object to the query (prevents SQL injection)
        $query->bindParam(':doj', $up_doj, PDO::PARAM_STR);
      }

      // if has been provided
      // if($attach_updated === true) {
      //   // set  object title to given value (checks for valid input)
      //   $member->setAttach($jsonData->attach);
      //   // get the value back as the object could be handling the return of the value differently to
      //   // what was provided
      //   $up_attach = $member->getAttach();
      //   // bind the parameter of the new value from the object to the query (prevents SQL injection)
      //   $query->bindParam(':attach', $up_attach, PDO::PARAM_STR);
      // }

      // if has been provided
      if($status_updated === true) {
        // set  object title to given value (checks for valid input)
        $member->setStatus($jsonData->status);
        // get the value back as the object could be handling the return of the value differently to
        // what was provided
        $up_status = $member->getStatus();
        // bind the parameter of the new value from the object to the query (prevents SQL injection)
        $query->bindParam(':status', $up_status, PDO::PARAM_STR);
      }

      // if has been provided
      if($address_updated === true) {
        // set  object title to given value (checks for valid input)
        $member->setAddress($jsonData->address);
        // get the value back as the object could be handling the return of the value differently to
        // what was provided
        $up_address = $member->getAddress();
        // bind the parameter of the new value from the object to the query (prevents SQL injection)
        $query->bindParam(':address', $up_address, PDO::PARAM_STR);
      }

      // if has been provided
      if($firstname_updated === true) {
        // set  object title to given value (checks for valid input)
        $member->setFirstname($jsonData->firstname);
        // get the value back as the object could be handling the return of the value differently to
        // what was provided
        $up_firstname = $member->getFirstname();
        // bind the parameter of the new value from the object to the query (prevents SQL injection)
        $query->bindParam(':firstname', $up_firstname, PDO::PARAM_STR);
      }
      // if has been provided
      if($midlename_update === true) {
        // set  object title to given value (checks for valid input)
        $member->setMidlename($jsonData->midlename);
        // get the value back as the object could be handling the return of the value differently to
        // what was provided
        $up_midlename = $member->getMidlename();
        // bind the parameter of the new value from the object to the query (prevents SQL injection)
        $query->bindParam(':midlename', $up_midlename, PDO::PARAM_STR);
      }
      // if has been provided
      if($lastname_updated === true) {
        // set  object title to given value (checks for valid input)
        $member->setLastname($jsonData->lastname);
        // get the value back as the object could be handling the return of the value differently to
        // what was provided
        $up_lastname = $member->getLastname();
        // bind the parameter of the new value from the object to the query (prevents SQL injection)
        $query->bindParam(':lastname', $up_lastname, PDO::PARAM_STR);
      }
      // if has been provided
      if($contact_updated === true) {
        // set  object title to given value (checks for valid input)
        $member->setContact($jsonData->contact);
        // get the value back as the object could be handling the return of the value differently to
        // what was provided
        $up_contact = $member->getContact();
        // bind the parameter of the new value from the object to the query (prevents SQL injection)
        $query->bindParam(':contact', $up_contact, PDO::PARAM_STR);
      }
      // if has been provided
      if($employment_status_update === true) {
        // set  object title to given value (checks for valid input)
        $member->setEmploymentStatus($jsonData->employment_status);
        // get the value back as the object could be handling the return of the value differently to
        // what was provided
        $up_employementStatus = $member->getEmploymentStatus();
        // bind the parameter of the new value from the object to the query (prevents SQL injection)
        $query->bindParam(':employment_status', $up_employementStatus, PDO::PARAM_STR);
      }
      // if has been provided
      if($gross_income_update === true) {
        // set  object title to given value (checks for valid input)
        $member->setGrossIncome($jsonData->gross_income);
        // get the value back as the object could be handling the return of the value differently to
        // what was provided
        $up_grossincome = $member->getGrossIncome();
        // bind the parameter of the new value from the object to the query (prevents SQL injection)
        $query->bindParam(':gross_income', $up_grossincome, PDO::PARAM_STR);
      }
      // if has been provided
      if($marital_status_update === true) {
        // set  object title to given value (checks for valid input)
        $member->setMaritalStatus($jsonData->marital_status);
        // get the value back as the object could be handling the return of the value differently to
        // what was provided
        $up_maritalStatus = $member->getMaritalStatus();
        // bind the parameter of the new value from the object to the query (prevents SQL injection)
        $query->bindParam(':marital_status', $up_maritalStatus, PDO::PARAM_STR);
      }
      // bind the id provided in the query string
      $query->bindParam(':memberid', $memberid, PDO::PARAM_INT);
      // bind the user id returned
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      // run the query
      $query->execute();

      // get affected row count
      $rowCount = $query->rowCount();

      // check if row was actually updated, could be that the given values are the same as the stored values
      if($rowCount === 0) {
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("Member not updated - given values may be the same as the stored values");
        $response->send();
        exit;
      }
      // ADD AUTH TO QUERY
      // create db query to return the newly edited  - connect to master database
      $query = $writeDB->prepare('SELECT * from members where member_id = :memberid
        AND member_type="individual"
        and saccos_sacco_id = :saccoid');
      $query->bindParam(':memberid', $memberid, PDO::PARAM_INT);
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $query->execute();

      // get row count
      $rowCount = $query->rowCount();

      // check if  was found
      if($rowCount === 0) {
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("No member found");
        $response->send();
        exit;
      }
      // create  array to store returned s
      $memberArray = array();

      // for each row returned
      while($row = $query->fetch(PDO::FETCH_ASSOC)) {

        // get  Images for given , store them in array to pass to
        $imageArray = retrieveImages($writeDB, $row['member_id'], $returned_saccoid);
        $membergroupsArray = retrieveGroups($readDB, $row['members_account_number'], $returned_saccoid);

        // create new  object for each row returned
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
      $returnData['member'] = $memberArray;

      // set up response for successful return
      $response = new Response();
      $response->setHttpStatusCode(200);
      $response->setSuccess(true);
      $response->addMessage("Member updated");
      $response->setData($returnData);
      $response->send();
      exit;
    }
    catch(ImageException $ex) {
      $response = new Response();
      $response->setHttpStatusCode(400);
      $response->setSuccess(false);
      $response->addMessage($ex->getMessage());
      $response->send();
      exit;
    }
    catch(Exception $ex) {
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
      $response->addMessage("Failed to update member - check your data for errors".$ex);
      $response->send();
      exit;
    }
  }
  // if any other request method apart from GET, PATCH, DELETE is used then return 405 method not allowed
  else {
    $response = new Response();
    $response->setHttpStatusCode(405);
    $response->setSuccess(false);
    $response->addMessage("Request method not allowed");
    $response->send();
    exit;
  }
}
// handle getting all s or creating a new one
elseif(empty($_GET)) {

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
  // else if request is a POST e.g. create member
  elseif($_SERVER['REQUEST_METHOD'] === 'POST') {

    // create
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

      // get POST request body as the POSTed data will be JSON format
      $rawPostData = file_get_contents('php://input');

      if(!$jsonData = json_decode($rawPostData)) {
        // set up response for unsuccessful request
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("Request body is not valid JSON");
        $response->send();
        exit;
      }

      // check if post request contains data in body as these are mandatory
      if(!isset($jsonData->firstname) || !isset($jsonData->lastname)
          || !isset($jsonData->contact)
          || !isset($jsonData->gender)
          || !isset($jsonData->status)
          || !isset($jsonData->address)
          || !isset($jsonData->dob)
          || !isset($jsonData->doj)
          || !isset($jsonData->identification)
          || !isset($jsonData->employment_status)
          || !isset($jsonData->gross_income)
          || !isset($jsonData->marital_status)
        ) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        (!isset($jsonData->firstname) ? $response->addMessage("firstname field is mandatory and must be provided") : false);
        (!isset($jsonData->lastname) ? $response->addMessage("lastname field is mandatory and must be provided") : false);
        (!isset($jsonData->contact) ? $response->addMessage("contact field is mandatory and must be provided") : false);
        (!isset($jsonData->gender) ? $response->addMessage("gender field is mandatory and must be provided") : false);
        (!isset($jsonData->status)? $response->addMessage("status field is mandatory and must be provided") : false);
        (!isset($jsonData->address) ? $response->addMessage("address field is mandatory and must be provided") : false);
        (!isset($jsonData->dob) ? $response->addMessage("date of birth field is mandatory and must be provided") : false);
        (!isset($jsonData->doj) ? $response->addMessage("date of joining field is mandatory and must be provided") : false);
        (!isset($jsonData->identification) ? $response->addMessage("identification field is mandatory and must be provided") : false);
        (!isset($jsonData->employment_status) ? $response->addMessage("employment status field is mandatory and must be provided") : false);
        (!isset($jsonData->gross_income) ? $response->addMessage("grosssdwsds income field is mandatory and must be provided") : false);
        (!isset($jsonData->marital_status) ? $response->addMessage("marital status field is mandatory and must be provided") : false);
        $response->send();
        exit;
      }
      $type="individual";
      // create new mmeber with data, if non mandatory fields not provided then set to null
      $newMember = new Member(
        null, $jsonData->firstname,(isset($jsonData->midlename) ? $jsonData->midlename : null), $jsonData->lastname,
      $jsonData->contact, $jsonData->gender,(isset($jsonData->email) ? $jsonData->email : null), $jsonData->address, $jsonData->dob,
      $jsonData->doj, $jsonData->identification,$jsonData->status,$type, null,$jsonData->employment_status,$jsonData->gross_income,
      $jsonData->marital_status,(isset($jsonData->sacco_group) ? $jsonData->sacco_group : null),(isset($jsonData->attach) ? $jsonData->attach : null),
      null, null, null);

      // get values and store them in variables
      $firstname = $newMember->getFirstname();
      $midlename = $newMember->getMidlename();
      $lastname = $newMember->getLastname();
      $contact = $newMember->getContact();
      $gender = $newMember->getGender();
      $address = $newMember->getAddress();
      $dob = $newMember->getDob();
      $doj = $newMember->getDoj();
      $email = $newMember->getEmail();
      $identification = $newMember->getIDN();
      $status = $newMember->getStatus();
      $type = $newMember->getType();
      $attach = $newMember->getAttach();
      $marital_status = $newMember->getMaritalStatus();
      $employment_status = $newMember->getEmploymentStatus();
      $gross_income = $newMember->getGrossIncome();
      $saccogroups = $newMember->getSaccoGroups();
      $makeaccount = new Account();
      $account = $makeaccount->create_account($returned_saccoid);

      // create db query
      $query = $writeDB->prepare('insert into members (member_fname, member_mname,member_lname, member_contact, member_gender, member_email,
      member_address,member_date_birth,member_join_date,member_identification,member_status,member_type,members_account_number,member_attach,
      users_user_id,saccos_sacco_id,branches_branch_id,member_marital_status,member_employment_status,member_gross_income	)
       values (:firstname,:midlename, :lastname,:contact,:gender,:email,:address,:dob,:doj,:identification,:status,:type,:account,
       :attach,:userid,:saccoid,:branch,:marital_status, :employment_status,:gross_income)');
      $query->bindParam(':firstname', $firstname, PDO::PARAM_STR);
      $query->bindParam(':midlename', $midlename, PDO::PARAM_STR);
      $query->bindParam(':lastname', $lastname, PDO::PARAM_STR);
      $query->bindParam(':contact', $contact, PDO::PARAM_STR);
      $query->bindParam(':gender', $gender, PDO::PARAM_STR);
      $query->bindParam(':address', $address, PDO::PARAM_STR);
      $query->bindParam(':email', $email, PDO::PARAM_STR);
      $query->bindParam(':dob', $dob, PDO::PARAM_STR);
      $query->bindParam(':doj', $doj, PDO::PARAM_STR);
      $query->bindParam(':status', $status, PDO::PARAM_STR);
      $query->bindParam(':identification', $identification, PDO::PARAM_STR);
      $query->bindParam(':type', $type, PDO::PARAM_STR);
      $query->bindParam(':attach', $attach, PDO::PARAM_STR);
      $query->bindParam(':account', $account, PDO::PARAM_STR);
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      // $query->bindParam(':category', $category, PDO::PARAM_INT);
      $query->bindParam(':userid', $returned_id, PDO::PARAM_INT);
      $query->bindParam(':branch', $returned_branch_id, PDO::PARAM_INT);
      $query->bindParam(':marital_status', $marital_status, PDO::PARAM_INT);
      $query->bindParam(':employment_status', $employment_status, PDO::PARAM_INT);
      $query->bindParam(':gross_income', $gross_income, PDO::PARAM_INT);
      $query->execute();

      // get row count
      $rowCount = $query->rowCount();

      // check if row was actually inserted, PDO exception should have caught it if not.
      if($rowCount === 0) {
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage("Failed to create member");
        $response->send();
        exit;
      }

      // get last  id so we can return the  in the json
      $lastID = $writeDB->lastInsertId();
      // ADD AUTH TO QUERY
      $datet= date('Y-m-d H:i:s');
      if (count($saccogroups) >0) {
      for ($i=0; $i < count($saccogroups); $i++) {
        // code...
        $groupid= $saccogroups[$i];
        $query = $writeDB->prepare('insert into group_members
        (`group_member_account`,
         `group_member_fname`,
         `group_member_lname`,
          `group_member_contact`,
          `group_member_gender`,
          `group_member_dob`,
           `group_member_timestamp`,
           `identification`,
            `group_account_id`,
            `group_account_saccoid`)
         values (:account,:firstname,:lastname,:contact,:gender,:dob,:doj,:identification,:id,:saccoid)');
        $query->bindParam(':account', $account, PDO::PARAM_STR);
        $query->bindParam(':firstname', $firstname, PDO::PARAM_STR);
        $query->bindParam(':lastname', $lastname, PDO::PARAM_STR);
        $query->bindParam(':contact', $contact, PDO::PARAM_STR);
        $query->bindParam(':gender', $gender, PDO::PARAM_STR);
        $query->bindParam(':dob', $dob, PDO::PARAM_STR);
        $query->bindParam(':doj', $datet, PDO::PARAM_STR);
        $query->bindParam(':identification', $identification, PDO::PARAM_STR);
        $query->bindParam(':id', $groupid, PDO::PARAM_STR);
        $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
        $query->execute();
      }
      }

      // create db query to get newly created  - get from master db not read slave as replication may be too slow for successful read
      $query = $writeDB->prepare('SELECT * from members where member_id = :id
        AND member_type="individual"
         and users_user_id = :userid');
      $query->bindParam(':id', $lastID, PDO::PARAM_INT);
      $query->bindParam(':userid', $returned_id, PDO::PARAM_INT);
      $query->execute();

      // get row count
      $rowCount = $query->rowCount();

      // make sure that the new  was returned
      if($rowCount === 0) {
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage("Failed to retrieve member after creation");
        $response->send();
        exit;
      }

      // create empty array to store s
      $memberArray = array();
      $emptyArray = array();

      // for each row returned - should be just one
      while($row = $query->fetch(PDO::FETCH_ASSOC)) {
        // create new  object
        $member = new Member(
          $row['member_id'], $row['member_fname'],$row['member_mname'], $row['member_lname'], $row['member_contact'],
           $row['member_gender'],$row['member_email'], $row['member_address'], $row['member_date_birth'],
            $row['member_join_date'], $row['member_identification'],  $row['member_status'], $row['member_type'],
            $row['members_account_number'], $row['member_employment_status'], $row['member_gross_income'],
          $row['member_marital_status'],$emptyArray,$row['member_attach'], $row['members_account_fixed'],
          $row['members_account_compuslaory'], $row['member_account_shares']
       );
       $newcontact = $row['member_contact'];
       $newname = $row['member_fname'];
        // create  and store in array for return in json data
        $memberArray[] = $member->returnMemberAsArray();
      }
      // bundle s and rows returned into an array to return in the json data
      $returnData = array();
      $returnData['rows_returned'] = $rowCount;
      $returnData['member'] = $memberArray;
      // send sms
      $message = "Hello ".$newname.", Welcome to  ".$sacconame .". Your new  A/C is ".$account. ". Thank you for choosing to save with us." ;
      // insert sms into the database
      insertSMSDB($writeDB, $message, $newcontact, $returned_saccoid);
      // insert email into the database
      insertEMAILDB($writeDB, $message, $saccoemail, $returned_saccoid);
      //set up response for successful return
      $response = new Response();
      $response->setHttpStatusCode(201);
      $response->setSuccess(true);
      $response->addMessage("Member created");
      $response->setData($returnData);
      $response->send();
      exit;
    }
    // if  fails to create due to data types, missing fields or invalid data then send error json
    catch(MemberException $ex) {
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
      $response->addMessage("Failed to insert member into database - check submitted data for errors");
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
