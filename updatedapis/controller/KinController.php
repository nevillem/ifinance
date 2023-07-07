<?php
require_once('../core/initialize.php');
require_once('Database.php');
require_once('../model/nextofkin.php');
require_once('../model/Response.php');
// require_once('../model/Image.php');
// require_once('../core/classes/Account.php');



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
if (array_key_exists("nkinid",$_GET)) {
  // get  id from query string
  $nkinid = $_GET['nkinid'];
  //check to see if  id in query string is not empty and is number, if not return json error
  if($nkinid == '' || !is_numeric($nkinid)) {
    $response = new Response();
    $response->setHttpStatusCode(400);
    $response->setSuccess(false);
    $response->addMessage("next of kin cannot be blank or must be numeric");
    $response->send();
    exit;
  }

  // if request is a GET, e.g. get member
  if($_SERVER['REQUEST_METHOD'] === 'GET') {
    // attempt to query the database
    try {
      // create db query
      // ADD AUTH TO QUERY
      $query = $readDB->prepare('SELECT * from next_of_kin where kin_id = :nkin and saccos_sacco_id = :saccoid');
      $query->bindParam(':nkin', $nkinid, PDO::PARAM_INT);
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

      // create empty array to store s
      $nKinArray = array();

      // for each row returned - should be just one
      while($row = $query->fetch(PDO::FETCH_ASSOC)) {
        // create new  object
        $member = new NextOfKin(
          $row['kin_id'],$row['members_id'], $row['first_name'],$row['midle_name'], $row['last_name'], $row['phone_number'], $row['gender'],
          $row['email'], $row['address'], $row['date_of_birth'], $row['date_registered'], $row['identification'],
          $row['relationship'], $row['inheritence']);

        // create  and store in array for return in json data
        $nKinArray[] = $member->returnNextOFKinAsArray();
      }
      // bundle s and rows returned into an array to return in the json data
      $returnData = array();
      $returnData['rows_returned'] = $rowCount;
      $returnData['nKin'] = $nKinArray;


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
    catch(NextOfKinException $ex) {
      $response = new Response();
      $response->setHttpStatusCode(500);
      $response->setSuccess(false);
      $response->addMessage($ex->getMessage());
      $response->send();
      exit;
    }
    catch(NextOfKinException $ex) {
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
      $response->addMessage("Failed to get next of kin");
      $response->send();
      exit;
    }
  }
  // else if request if a DELETE e.g. delete
  elseif($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // attempt to query the database
    try {
      // ADD AUTH TO QUERY

      // once all images are deleted then delete the actual
      $query = $writeDB->prepare('DELETE from next_of_kin where kin_id = :nkin and saccos_sacco_id = :saccoid');
      $query->bindParam(':nkin', $nkinid, PDO::PARAM_INT);
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $query->execute();

      // get row count
      $rowCount = $query->rowCount();

      if($rowCount === 0) {
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("next of kin not found");
        $response->send();
        exit;
      }

      // set up response for successful return
      $response = new Response();
      $response->setHttpStatusCode(200);
      $response->setSuccess(true);
      $response->addMessage("next of kin deleted");
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
      $response->addMessage("Failed to delete next of kin");
      $response->send();
      exit;
    }
  }
  elseif($_SERVER['REQUEST_METHOD'] === 'PATCH')  {
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

      $first_name_updated = false;
      $lastname_updated = false;
      $midlename_updated = false;
      $gender_updated = false;
      $identification_updated=false;
      $dob_updated = false;
      $contact_updated = false;
      $email_updated = false;
      $relationship_updated = false;
      $inheritence_percent_updated=false;
      $address_updated = false;
      // create blank query fields string to append each field to
      $queryFields = "";

      // check if title exists in PATCH
      if(isset($jsonData->firstname)) {
        // set title field updated to true
        $firstname_updated = true;
        // add title field to query field string
        $queryFields .= "first_name = :firstname, ";
      }

      // check if deadline exists in PATCH
      if(isset($jsonData->lastname)) {
        // set deadline field updated to true
        $lastname_updated = true;
        // add deadline field to query field string
        $queryFields .= "last_name = :lastname, ";
      }
      if(isset($jsonData->midlename)) {
        // set deadline field updated to true
        $midlename_updated = true;
        // add deadline field to query field string
        $queryFields .= "midle_name = :midlename, ";
      }
      // check if completed exists in PATCH
      if(isset($jsonData->gender)) {
        // set completed field updated to true
        $gender_updated = true;
        // add completed field to query field string
        $queryFields .= "gender = :gender, ";
      }
      // check if completed exists in PATCH
      if(isset($jsonData->relationship)) {
        // set completed field updated to true
        $relationship_updated = true;
        // add completed field to query field string
        $queryFields .= "relationship = :relationship, ";
      }
      if(isset($jsonData->identification)) {
        // set completed field updated to true
        $identification_updated = true;
        $queryFields .= "identification = :identification, ";
        // add completed field to query field string
      }
      // check if completed exists in PATCH
      if(isset($jsonData->dob)) {
        // set completed field updated to true
        $dob_updated = true;
        // add completed field to query field string
        $queryFields .= "date_of_birth = :dob, ";
      }
      if(isset($jsonData->inheritance)) {
        // set completed field updated to true
        $inheritence_percent_updated = true;
        // add completed field to query field string
        $queryFields .= "inheritence = :inheritance, ";
      }
      // check if completed exists in PATCH
      if(isset($jsonData->contact)) {
        // set completed field updated to true
        $contact_updated = true;
        // add completed field to query field string
        $queryFields .= "phone_number = :contact, ";
      }
      // check if completed exists in PATCH
      if(isset($jsonData->address)) {
        // set completed field updated to true
        $address_updated = true;
        // add completed field to query field string
        $queryFields .= "address = :address, ";
      }
      // check if completed exists in PATCH
      if(isset($jsonData->email)) {
        // set completed field updated to true
        $email_updated = true;
        // add completed field to query field string
        $queryFields .= "email = :email, ";
      }

      // remove the right hand comma and trailing space
      $queryFields = rtrim($queryFields, ", ");

      // check if any  fields supplied in JSON
      if($firstname_updated === false && $lastname_updated === false && $midlename_updated === false
      && $gender_update===false && $gender_update===false   && $email_updated === false
      && $dob_updated === false && $relationship_updated === false  && $contact_updated === false
      && $inheritence_percent_updated === false   &&  $identification_updated === false
        && $address_updated === false) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("No next of kin fields provided");
        $response->send();
        exit;
      }
      // ADD AUTH TO QUERY
      // create db query to get  from database to update - use master db
      $query = $writeDB->prepare('SELECT * from next_of_kin where kin_id  = :nkin
        and saccos_sacco_id  = :saccoid');
      $query->bindParam(':nkin', $nkinid, PDO::PARAM_INT);
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
        $response->addMessage("No next of kin found to update");
        $response->send();
        exit;
      }

      // for each row returned - should be just one
      while($row = $query->fetch(PDO::FETCH_ASSOC)) {
        // create new  object
        $member = new NextOfKin(
          $row['kin_id'],$row['members_id'],
           $row['first_name'],$row['midle_name'],
            $row['last_name'], $row['phone_number'],
             $row['gender'],
          $row['email'], $row['address'],
          $row['date_of_birth'],
           $row['date_registered'],
           $row['identification'],
          $row['relationship'],
          $row['inheritence']);
       }
      // ADD AUTH TO QUERY
      // create the query string including any query fields
      $queryString = "update next_of_kin set ".$queryFields." where kin_id  = :nkin
      and saccos_sacco_id  = :saccoid";
      // prepare the query
      $query = $writeDB->prepare($queryString);

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
      if($midlename_updated === true) {
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

      if($gender_updated === true) {
        // set  object title to given value (checks for valid input)
        $member->setGender($jsonData->gender);
        // get the value back as the object could be handling the return of the value differently to
        // what was provided
        $up_gender = $member->getGender();
        // bind the parameter of the new value from the object to the query (prevents SQL injection)
        $query->bindParam(':gender', $up_gender, PDO::PARAM_STR);
      }
      if($relationship_updated === true) {
        // set  object title to given value (checks for valid input)
        $member->setRelationship($jsonData->relationship);
        // get the value back as the object could be handling the return of the value differently to
        // what was provided
        $up_relationship = $member->getRelationship();
        // bind the parameter of the new value from the object to the query (prevents SQL injection)
        $query->bindParam(':relationship', $up_relationship, PDO::PARAM_STR);
      }
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
      if($inheritence_percent_updated === true) {
        // set  object title to given value (checks for valid input)
        $member->setInheritance($jsonData->inheritance);
        // get the value back as the object could be handling the return of the value differently to
        // what was provided
        $up_inheritance = $member->getInheritance();
        // bind the parameter of the new value from the object to the query (prevents SQL injection)
        $query->bindParam(':inheritance', $up_inheritance, PDO::PARAM_STR);
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
      if($contact_updated === true) {
        // set  object title to given value (checks for valid input)
        $member->setContact($jsonData->contact);
        // get the value back as the object could be handling the return of the value differently to
        // what was provided
        $up_contact = $member->getContact();
        // bind the parameter of the new value from the object to the query (prevents SQL injection)
        $query->bindParam(':contact', $up_contact, PDO::PARAM_STR);
      }

      // bind the id provided in the query string
      $query->bindParam(':nkin', $nkinid, PDO::PARAM_INT);
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
        $response->addMessage("next nof kin not updated - given values may be the same as the stored values");
        $response->send();
        exit;
      }
      // ADD AUTH TO QUERY
      // create db query to return the newly edited  - connect to master database
      $query = $writeDB->prepare('SELECT * from next_of_kin where kin_id = :nkin and saccos_sacco_id = :saccoid');
      $query->bindParam(':nkin', $nkinid, PDO::PARAM_INT);
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
        $response->addMessage("No update next of kin found");
        $response->send();
        exit;
      }
      // create  array to store returned s
      $nextofkinArray = array();

      // for each row returned
      while($row = $query->fetch(PDO::FETCH_ASSOC)) {
        // $id,$account, $groupname,$chairperson, $contact,$email, $address, $doj, $branch,$status,
        // $type,$identification
        // get  Images for given , store them in array to pass to
        // create new  object for each row
        $member = new NextOfKin(
          $row['kin_id'],$row['members_id'], $row['first_name'],$row['midle_name'], $row['last_name'], $row['phone_number'], $row['gender'],
          $row['email'], $row['address'], $row['date_of_birth'], $row['date_registered'], $row['identification'],
          $row['relationship'], $row['inheritence']);

        // create  and store in array for return in json data
        $nextofkinArray[] = $member->returnNextOFKinAsArray();

      }
      // bundle s and rows returned into an array to return in the json data
      $returnData = array();
      $returnData['rows_returned'] = $rowCount;
      $returnData['NextOfKin'] = $nextofkinArray;

      // set up response for successful return
      $response = new Response();
      $response->setHttpStatusCode(200);
      $response->setSuccess(true);
      $response->addMessage("next of kin updated updated");
      $response->setData($returnData);
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
      $response->addMessage("Failed to update next of kin - check your data for errors".$ex);
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

  // if request is a GET e.g. get next of kin
  if($_SERVER['REQUEST_METHOD'] === 'GET') {

    // attempt to query the database
    try {
      // ADD AUTH TO QUERY
      // create db query
      $query = $readDB->prepare('SELECT * from next_of_kin where saccos_sacco_id = :saccoid ORDER BY kin_id DESC');
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $query->execute();

      // get row count
      $rowCount = $query->rowCount();

      // create empty array to store s
      $nKinArray = array();

      // for each row returned - should be just one
      while($row = $query->fetch(PDO::FETCH_ASSOC)) {
        // create new  object
        $member = new NextOfKin(
          $row['kin_id'],$row['members_id'], $row['first_name'],$row['midle_name'], $row['last_name'], $row['phone_number'], $row['gender'],
          $row['email'], $row['address'], $row['date_of_birth'], $row['date_registered'], $row['identification'],
          $row['relationship'], $row['inheritence']);

        // create  and store in array for return in json data
        $nKinArray[] = $member->returnNextOFKinAsArray();
      }
      // bundle s and rows returned into an array to return in the json data
      $returnData = array();
      $returnData['rows_returned'] = $rowCount;
      $returnData['nKin'] = $nKinArray;

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
      if(!isset($jsonData->memberid) ||
       !isset($jsonData->firstname)
       || !isset($jsonData->lastname)
          || !isset($jsonData->contact)
        //   || !isset($jsonData->gender)
        //   || !isset($jsonData->status)
          // || !isset($jsonData->address)
          // || !isset($jsonData->dob)
          // || !isset($jsonData->doj)
          || !isset($jsonData->relationship)
          // || !isset($jsonData->inheritance)
        ) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        (!isset($jsonData->memberid) ? $response->addMessage("member field is mandatory and must be provided") : false);
        (!isset($jsonData->firstname) ? $response->addMessage("firstname field is mandatory and must be provided") : false);
        (!isset($jsonData->lastname) ? $response->addMessage("lastname field is mandatory and must be provided") : false);
        (!isset($jsonData->contact) ? $response->addMessage("contact field is mandatory and must be provided") : false);
        // (!isset($jsonData->gender) ? $response->addMessage("gender field is mandatory and must be provided") : false);
        // (!isset($jsonData->status)? $response->addMessage("status field is mandatory and must be provided") : false);
        // (!isset($jsonData->address) ? $response->addMessage("address field is mandatory and must be provided") : false);
        // (!isset($jsonData->dob) ? $response->addMessage("date of birth field is mandatory and must be provided") : false);
        // (!isset($jsonData->doj) ? $response->addMessage("date of joining field is mandatory and must be provided") : false);
        (!isset($jsonData->relationship) ? $response->addMessage("relationship field is mandatory and must be provided") : false);
        // (!isset($jsonData->inheritance) ? $response->addMessage("inheritance field is mandatory and must be provided") : false);
        $response->send();
        exit;
      }

      // create new next of kin with data, if non mandatory fields not provided then set to null
      $newMember = new NextOfKin(null,$jsonData->memberid, $jsonData->firstname,
      (isset($jsonData->midlename) ? $jsonData->midlename : null), $jsonData->lastname,
      $jsonData->contact, (isset($jsonData->gender) ? $jsonData->gender : null),
      (isset($jsonData->email) ? $jsonData->email : null),
      (isset($jsonData->address) ? $jsonData->address : null),
       (isset($jsonData->dob) ? $jsonData->dob : null),
       (isset($jsonData->doj) ? $jsonData->doj : null),
       (isset($jsonData->identification)?$jsonData->identification:null),
        $jsonData->relationship,
        (isset($jsonData->inheritance)?$jsonData->inheritance:null));

      // get values and store them in variables
      $memberid = $newMember->getMemberID();
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
      $relationship = $newMember->getRelationship();
      $inheritance = $newMember->getInheritance();

      // create db query to get newly created  - get from master db not read slave as replication may be too slow for successful read
      $query = $writeDB->prepare('SELECT * from next_of_kin where first_name = :firstname AND last_name=:lastname ');
      $query->bindParam(':firstname', $firstname, PDO::PARAM_INT);
      $query->bindParam(':lastname', $lastname, PDO::PARAM_INT);
      $query->execute();

      // get row count
      $rowCount = $query->rowCount();

      // make sure that the new  was returned
      if($rowCount > 0) {
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(409);
        $response->setSuccess(false);
        $response->addMessage("next of kin with these names exists");
        $response->send();
        exit;
      }
      // create db query
      $query = $writeDB->prepare('insert into next_of_kin (`first_name`, `midle_name`, `last_name`,`gender`, `relationship`,
      `identification`, `date_of_birth`, `inheritence`,`phone_number`, `email`, `address`, `members_id`, `saccos_sacco_id`, `date_registered`)
       values (:firstname,:midlename, :lastname,:gender,:relationship,:identification,:dob,:inheritance,:contact,:email,:address,:memberid,:saccoid,:doj)');
      $query->bindParam(':firstname', $firstname, PDO::PARAM_STR);
      $query->bindParam(':midlename', $midlename, PDO::PARAM_STR);
      $query->bindParam(':lastname', $lastname, PDO::PARAM_STR);
      $query->bindParam(':relationship', $relationship, PDO::PARAM_STR);
      $query->bindParam(':identification', $identification, PDO::PARAM_STR);
      $query->bindParam(':dob', $dob, PDO::PARAM_STR);
      $query->bindParam(':inheritance', $inheritance, PDO::PARAM_STR);
      $query->bindParam(':contact', $contact, PDO::PARAM_STR);
      $query->bindParam(':email', $email, PDO::PARAM_STR);
      $query->bindParam(':address', $address, PDO::PARAM_STR);
      $query->bindParam(':memberid', $memberid, PDO::PARAM_STR);
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $query->bindParam(':doj', $doj, PDO::PARAM_STR);
      $query->bindParam(':gender', $gender, PDO::PARAM_STR);

      $query->execute();

      // get row count
      $rowCount = $query->rowCount();

      // check if row was actually inserted, PDO exception should have caught it if not.
      if($rowCount === 0) {
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage("Failed to create next of kin");
        $response->send();
        exit;
      }

      // get last  id so we can return the  in the json
      $lastID = $writeDB->lastInsertId();
      // ADD AUTH TO QUERY

      // create db query to get newly created  - get from master db not read slave as replication may be too slow for successful read
      $query = $writeDB->prepare('SELECT * from next_of_kin where kin_id = :id');
      $query->bindParam(':id', $lastID, PDO::PARAM_INT);
    //   $query->bindParam(':userid', $returned_id, PDO::PARAM_INT);
      $query->execute();

      // get row count
      $rowCount = $query->rowCount();

      // make sure that the new  was returned
      if($rowCount === 0) {
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage("Failed to retrieve next of kin after creation");
        $response->send();
        exit;
      }

      // create empty array to store s
      $nKinArray = array();

      // for each row returned - should be just one
      while($row = $query->fetch(PDO::FETCH_ASSOC)) {
        // create new  object
        $member = new NextOfKin(
          $row['kin_id'],$row['members_id'], $row['first_name'],$row['midle_name'], $row['last_name'], $row['phone_number'], $row['gender'],
          $row['email'], $row['address'], $row['date_of_birth'], $row['date_registered'], $row['identification'],
          $row['relationship'], $row['inheritence']);

        // create  and store in array for return in json data
        $nKinArray[] = $member->returnNextOFKinAsArray();
      }
      // bundle s and rows returned into an array to return in the json data
      $returnData = array();
      $returnData['rows_returned'] = $rowCount;
      $returnData['nKin'] = $nKinArray;

      //set up response for successful return
      $response = new Response();
      $response->setHttpStatusCode(201);
      $response->setSuccess(true);
      $response->addMessage("Next of kin added");
      $response->setData($returnData);
      $response->send();
      exit;
    }
    // if  fails to create due to data types, missing fields or invalid data then send error json
    catch(NextOfKinException $ex) {
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
      $response->addMessage("Failed to insert next of kin into database - check submitted data for errors");
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
