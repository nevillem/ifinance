<?php
require_once('../core/initialize.php');
require_once('Database.php');
require_once('../model/Response.php');
require_once('../model/groupsregistration.php');
require_once('../core/classes/Account.php');


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
if (array_key_exists("groupid",$_GET)) {
  // get  id from query string
  $groupid = $_GET['groupid'];
  //check to see if  id in query string is not empty and is number, if not return json error
  if($groupid == '' || !is_numeric($groupid)) {
    $response = new Response();
    $response->setHttpStatusCode(400);
    $response->setSuccess(false);
    $response->addMessage("groupID cannot be blank or must be numeric");
    $response->send();
    exit;
  }

  // if request is a GET, e.g. get member
  if($_SERVER['REQUEST_METHOD'] === 'GET') {
    // attempt to query the database
    try {
      // create db query
      // ADD AUTH TO QUERY
      $query = $readDB->prepare('SELECT * from members,branches
      WHERE member_id = :memberid
      AND member_type="group"
      AND branches_branch_id =branch_id
      AND members.saccos_sacco_id = :saccoid');
      $query->bindParam(':memberid', $groupid, PDO::PARAM_INT);
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
  		$query->execute();
      // members,branches where  members.saccos_sacco_id = :saccoid
      //   AND member_type="group" AND branches_branch_id =branch_id ORDER BY member_id DESC');
      // get row count
      $rowCount = $query->rowCount();

      // create  array to store returned
      $groupArray = array();

      if($rowCount === 0) {
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("group id not found");
        $response->send();
        exit;
      }

      // for each row returned
      while($row = $query->fetch(PDO::FETCH_ASSOC)) {
        // ($id, $groupname,$chairperson, $contact,$email, $address, $doj, $branch,$status,
        // $type,$identification
        $member = new GroupRegistration(
          $row['member_id'],
          $row['members_account_number'],
          $row['member_fname'],
          $row['member_lname'],
           $row['member_contact'],
          $row['member_email'],
           $row['member_address'],
           $row['member_join_date'],
           $row['branch_name'],
            $row['member_status'],
           $row['member_type'],
           $row['member_identification']
       );

        // create  and store in array for return in json data
  	    $groupArray[] = $member->returnGroupAsArray();
      }

      // bundle s and rows returned into an array to return in the json data
      $returnData = array();
      $returnData['rows_returned'] = $rowCount;
      $returnData['group'] = $groupArray;

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
    catch(GroupRegistrationException $ex) {
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
      $response->addMessage("Failed to get group ");
      $response->send();
      exit;
    }
  }
  // else if request if a DELETE e.g. delete
  elseif($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // attempt to query the database
    try {
      // ADD AUTH TO QUERY
        // begin transaction as we dont want the row to be deleted if the file cannot be deleted
        $writeDB->beginTransaction();
      // once all images are deleted then delete the actual
      $query = $writeDB->prepare('delete from members where member_id = :memberid and saccos_sacco_id = :saccoid');
      $query->bindParam(':memberid', $groupid, PDO::PARAM_INT);
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $query->execute();

      // get row count
      $rowCount = $query->rowCount();

      if($rowCount === 0) {
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("group not found");
        $response->send();
        exit;
      }

      // set up response for successful return
      $response = new Response();
      $response->setHttpStatusCode(200);
      $response->setSuccess(true);
      $response->addMessage("group deleted");
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
      $response->addMessage("Failed to delete group".$ex);
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

      $identification_updated = false;
      $status_updated = false;
      $email_updated = false;
      $doj_updated = false;
      // $attach_updated = false;
      $contact_updated = false;
      $groupname_updated = false;
      $chairperson_updated = false;
      $address_updated = false;
      // create blank query fields string to append each field to
      $queryFields = "";

      // check if title exists in PATCH
      if(isset($jsonData->address)) {
        // set title field updated to true
        $address_updated = true;
        // add title field to query field string
        $queryFields .= "member_address = :address, ";
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
      if(isset($jsonData->groupname)) {
        // set completed field updated to true
        $groupname_updated = true;
        // add completed field to query field string
        $queryFields .= "member_fname = :groupname, ";
      }
      // check if completed exists in PATCH
      if(isset($jsonData->chairperson)) {
        // set completed field updated to true
        $chairperson_updated = true;
        // add completed field to query field string
        $queryFields .= "member_lname = :chairperson, ";
      }

      // remove the right hand comma and trailing space
      $queryFields = rtrim($queryFields, ", ");

      // check if any  fields supplied in JSON
      if($identification_updated === false && $status_updated === false
        && $email_updated === false && $doj_updated === false  && $contact_updated === false
        && $groupname_updated === false &&  $chairperson_updated === false
        && $address_updated === false) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("No group fields provided");
        $response->send();
        exit;
      }
      // ADD AUTH TO QUERY
      // create db query to get  from database to update - use master db
      $query = $writeDB->prepare('SELECT * from members,branches where
        member_id = :memberid
      AND member_type="group"
      AND branches_branch_id =branch_id
      and members.saccos_sacco_id = :saccoid');
      $query->bindParam(':memberid', $groupid, PDO::PARAM_INT);
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
        $response->addMessage("No group found to update");
        $response->send();
        exit;
      }

      // for each row returned - should be just one
      while($row = $query->fetch(PDO::FETCH_ASSOC)) {
        // create new  object
        $member = new GroupRegistration(
          $row['member_id'],
          $row['members_account_number'],
          $row['member_fname'],
          $row['member_lname'],
           $row['member_contact'],
          $row['member_email'],
           $row['member_address'],
           $row['member_join_date'],
           $row['branch_name'],
            $row['member_status'],
           $row['member_type'],
           $row['member_identification']
       );
       }

       // echo"hey";
      // ADD AUTH TO QUERY
      // create the query string including any query fields
      $queryString = "UPDATE members SET ".$queryFields." where member_id = :memberid
      AND saccos_sacco_id = :saccoid";
      // prepare the query
      $query = $writeDB->prepare($queryString);
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
      if($groupname_updated === true) {
        // set  object title to given value (checks for valid input)
        $member->setGroupName($jsonData->groupname);
        // get the value back as the object could be handling the return of the value differently to
        // what was provided
        $up_groupname = $member->getGroupname();
        // bind the parameter of the new value from the object to the query (prevents SQL injection)
        $query->bindParam(':groupname', $up_groupname, PDO::PARAM_STR);
      }
      // if has been provided
      if($chairperson_updated === true) {
        // set  object title to given value (checks for valid input)
        $member->setChairperson($jsonData->chairperson);
        // get the value back as the object could be handling the return of the value differently to
        // what was provided
        $up_chairperson = $member->getGroupchairperson();
        // bind the parameter of the new value from the object to the query (prevents SQL injection)
        $query->bindParam(':chairperson', $up_chairperson, PDO::PARAM_STR);
      }


      // bind the id provided in the query string
      $query->bindParam(':memberid', $groupid, PDO::PARAM_INT);
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
        $response->addMessage("group not updated - given values may be the same as the stored values");
        $response->send();
        exit;
      }
      // ADD AUTH TO QUERY
      // create db query to return the newly edited  - connect to master database
      $query = $writeDB->prepare('SELECT * from members,branches
        where  members.saccos_sacco_id = :saccoid
        AND member_type="group"
        AND branches_branch_id =branch_id
        AND member_id = :memberid');
      $query->bindParam(':memberid', $groupid, PDO::PARAM_INT);
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
        $response->addMessage("No group found");
        $response->send();
        exit;
      }
      // create  array to store returned s
      $groupArray = array();

      // for each row returned
      while($row = $query->fetch(PDO::FETCH_ASSOC)) {
        // $id,$account, $groupname,$chairperson, $contact,$email, $address, $doj, $branch,$status,
        // $type,$identification
        // get  Images for given , store them in array to pass to
        // create new  object for each row
        $member = new GroupRegistration(
          $row['member_id'],
          $row['members_account_number'],
          $row['member_fname'],
          $row['member_lname'],
           $row['member_contact'],
          $row['member_email'],
           $row['member_address'],
           $row['member_join_date'],
           $row['branch_name'],
            $row['member_status'],
           $row['member_type'],
           $row['member_identification']
       );
        // create  and store in array for return in json data
        $groupArray[] = $member->returnGroupAsArray();

      }
      // bundle s and rows returned into an array to return in the json data
      $returnData = array();
      $returnData['rows_returned'] = $rowCount;
      $returnData['group'] = $groupArray;

      // set up response for successful return
      $response = new Response();
      $response->setHttpStatusCode(200);
      $response->setSuccess(true);
      $response->addMessage("group updated");
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
      $response->addMessage("Failed to update group - check your data for errors".$ex);
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
      $query = $readDB->prepare('SELECT * from members,branches where  members.saccos_sacco_id = :saccoid
        AND member_type="group" AND branches_branch_id =branch_id ORDER BY member_id DESC');
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $query->execute();

      // get row count
      $rowCount = $query->rowCount();

      // create  array to store returned s
      $groupArray = array();

      // for each row returned
      while($row = $query->fetch(PDO::FETCH_ASSOC)) {
        // $id,$account, $groupname,$chairperson, $contact,$email, $address, $doj, $branch,$status,
        // $type,$identification
        // get  Images for given , store them in array to pass to
        // create new  object for each row
        $member = new GroupRegistration(
          $row['member_id'],
          $row['members_account_number'],
          $row['member_fname'],
          $row['member_lname'],
           $row['member_contact'],
          $row['member_email'],
           $row['member_address'],
           $row['member_join_date'],
           $row['branch_name'],
            $row['member_status'],
           $row['member_type'],
           $row['member_identification']
       );
        // create  and store in array for return in json data
        $groupArray[] = $member->returnGroupAsArray();

      }

      // bundle s and rows returned into an array to return in the json data
      $returnData = array();
      $returnData['rows_returned'] = $rowCount;
      $returnData['groups'] = $groupArray;

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
    catch(PDOException $ex) {
      error_log("Database Query Error: ".$ex, 0);
      $response = new Response();
      $response->setHttpStatusCode(500);
      $response->setSuccess(false);
      $response->addMessage("Failed to get groups");
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
      if(!isset($jsonData->groupname)
          || !isset($jsonData->chairperson)
          || !isset($jsonData->contact)
          || !isset($jsonData->status)
          || !isset($jsonData->address)
          || !isset($jsonData->doj)
        ) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        (!isset($jsonData->groupname) ? $response->addMessage("group name field is mandatory and must be provided") : false);
        (!isset($jsonData->chairperson) ? $response->addMessage("group chairperson field is mandatory and must be provided") : false);
        (!isset($jsonData->contact) ? $response->addMessage("chairperson contact field is mandatory and must be provided") : false);
        // (!isset($jsonData->email)? $response->addMessage("email field is mandatory and must be provided") : false);
        (!isset($jsonData->status)? $response->addMessage("status field is mandatory and must be provided") : false);
        (!isset($jsonData->address) ? $response->addMessage(" group address field is mandatory and must be provided") : false);
        (!isset($jsonData->doj) ? $response->addMessage("date of registratio n field is mandatory and must be provided") : false);
        // (!isset($jsonData->identification) ? $response->addMessage("identification field is mandatory and must be provided") : false);
        $response->send();
        exit;
      }

      $type="group";
      // create new mmeber with data, if non mandatory fields not provided then set to null
      $newGroup = new GroupRegistration(null,null, $jsonData->groupname, $jsonData->chairperson,
      $jsonData->contact,(isset($jsonData->email) ? $jsonData->email : null),
       $jsonData->address, $jsonData->doj, null,$jsonData->status,$type,
       (isset($jsonData->identification) ? $jsonData->identification : null));

      // get values and store them in variables
      $groupname = $newGroup->getGroupname();
      $gchairperson = $newGroup->getGroupchairperson();
      $contact = $newGroup->getContact();
      $email = $newGroup->getEmail();
      $address = $newGroup->getAddress();
      $doj = $newGroup->getDoj();
      $identification = $newGroup->getIDN();
      $status = $newGroup->getStatus();
      $type = $newGroup->getType();
    //   $category = $newMember->getCategory();
      $makeaccount = new Account();
      $account = $makeaccount->create_account($returned_saccoid);

      // create db query
      $query = $writeDB->prepare('insert into members (member_fname, member_lname, member_contact, member_email,
      member_address,member_join_date,member_identification,member_status,member_type,members_account_number,
      users_user_id,saccos_sacco_id,branches_branch_id)
       values (:group, :chairperson,:contact,:email,:address,:doj,:identification,:status,:type,:account,
        :userid,:saccoid,:branch)');
      $query->bindParam(':group', $groupname, PDO::PARAM_STR);
      $query->bindParam(':chairperson', $gchairperson, PDO::PARAM_STR);
      $query->bindParam(':contact', $contact, PDO::PARAM_STR);
      $query->bindParam(':email', $email, PDO::PARAM_STR);
      $query->bindParam(':address', $address, PDO::PARAM_STR);
      $query->bindParam(':doj', $doj, PDO::PARAM_STR);
      $query->bindParam(':identification', $identification, PDO::PARAM_STR);
      $query->bindParam(':status', $status, PDO::PARAM_STR);
      $query->bindParam(':type', $type, PDO::PARAM_STR);
      $query->bindParam(':account', $account, PDO::PARAM_STR);
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      // $query->bindParam(':category', $category, PDO::PARAM_INT);
      $query->bindParam(':userid', $returned_id, PDO::PARAM_INT);
      $query->bindParam(':branch', $returned_branch_id, PDO::PARAM_INT);
      $query->execute();

      // get row count
      $rowCount = $query->rowCount();

      // check if row was actually inserted, PDO exception should have caught it if not.
      if($rowCount === 0) {
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage("Failed to create group");
        $response->send();
        exit;
      }

      // get last  id so we can return the  in the json
      $lastID = $writeDB->lastInsertId();
      // ADD AUTH TO QUERY
    $fname=explode(' ',$gchairperson);
    $firstname= $fname[0];
    $lastname= $fname[1];
    $grouprole='chairperson';
      $query = $writeDB->prepare('INSERT into group_members (`group_member_fname`,
        `group_member_lname`, `group_member_contact`,
         `group_roles`,`identification`, `group_account_id`, `group_account_saccoid`)
       values (:firstname, :lastname,:contact,:grouprole,:identification,:id,:saccoid)');
      $query->bindParam(':firstname', $firstname, PDO::PARAM_STR);
      $query->bindParam(':lastname', $lastname, PDO::PARAM_STR);
      $query->bindParam(':contact', $contact, PDO::PARAM_STR);
      $query->bindParam(':grouprole', $grouprole, PDO::PARAM_STR);
      $query->bindParam(':identification', $identification, PDO::PARAM_STR);
      $query->bindParam(':id', $lastID, PDO::PARAM_INT);
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      // $query->bindParam(':branch', $returned_branch_id, PDO::PARAM_INT);
      $query->execute();
      // create db query to get newly created  - get from master db not read slave as replication may be too slow for successful read
      $query = $writeDB->prepare('SELECT * from members,branches
        where  members.saccos_sacco_id = :saccoid
        AND member_type="group"
        AND member_id =:id
        AND branches_branch_id =branch_id
         and users_user_id = :userid');
      $query->bindParam(':id', $lastID, PDO::PARAM_INT);
      $query->bindParam(':userid', $returned_id, PDO::PARAM_INT);
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $query->execute();

      // get row count
      $rowCount = $query->rowCount();

      // make sure that the new  was returned
      if($rowCount === 0) {
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage("Failed to retrieve group after creation");
        $response->send();
        exit;
      }
      // create empty array to store s
      $groupArray = array();

      while($row = $query->fetch(PDO::FETCH_ASSOC)) {
        $member = new GroupRegistration(
          $row['member_id'],
          $row['members_account_number'],
          $row['member_fname'],
          $row['member_lname'],
           $row['member_contact'],
          $row['member_email'],
           $row['member_address'],
           $row['member_join_date'],
           $row['branch_name'],
            $row['member_status'],
           $row['member_type'],
           $row['member_identification']
       );
       $newcontact = $row['member_contact'];
       $newname = $row['member_fname'];
        // create  and store in array for return in json data
      $groupArray[] = $member->returnGroupAsArray();
      }
      // bundle s and rows returned into an array to return in the json data
      $returnData = array();
      $returnData['rows_returned'] = $rowCount;
      $returnData['group'] = $groupArray;
      // send sms
      $message = "Hello ".$newname.", Welcome to  ".$sacconame .". Your new group  A/C is ".$account. ". Thank you for choosing to save with us." ;
      // insert sms into the database
      insertSMSDB($writeDB, $message, $newcontact, $returned_saccoid);
      // insert email into the database
      insertEMAILDB($writeDB, $message, $saccoemail, $returned_saccoid);
      //set up response for successful return
      $response = new Response();
      $response->setHttpStatusCode(201);
      $response->setSuccess(true);
      $response->addMessage("group created");
      $response->setData($returnData);
      $response->send();
      exit;
    }
    // if  fails to create due to data types, missing fields or invalid data then send error json
    catch(GroupRegistrationException $ex) {
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
      $response->addMessage("Failed to save group - check submitted data for errors $ex");
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
