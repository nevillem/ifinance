<?php

require_once('../core/initialize.php');
require_once('Database.php');
require_once('../model/Response.php');
require_once('../model/User.php');
require_once('../model/Branches.php');


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
  if($returned_sacco_active != 'active' && $returned_sacco_active != 'inactive'):
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
// check if userid is in the url e.g. /user/1

if (array_key_exists("userid",$_GET)):
  // get user id from query string
  $userid = $_GET['userid'];

  //check to see if user id in query string is not empty and is number, if not return json error
  if($userid == '' || !is_numeric($userid)):
    $response = new Response();
    $response->setHttpStatusCode(400);
    $response->setSuccess(false);
    $response->addMessage("user ID cannot be blank or must be numeric");
    $response->send();
    exit;
  endif;
  /**
   * @OA\GET(
   *   path="/irembo_version_control/API/users/{userid}", tags={"users-sacco"},
   *    summary ="Get user by ID - EndPoint",
   *     @OA\MediaType(
    *         mediaType="application/json"
    *     ),
    *   @OA\Parameter(
    *      name="authorization_token",
    *     in="header",
    *     required=true,
    *     @OA\Schema(type="string"),
    *     style = "simple"
    *   ),
    *   @OA\Parameter(
   *      name="userid",
   *     in="path",
   *     required=true,
   *     @OA\Schema(type="integer")
   *   ),
   *     @OA\Response(response="201", description="returns users"),
   *     @OA\Response(response="400", description="You have received a bad response"),
   *     @OA\Response(response="401", description="unauthorised please try again"),
   *     @OA\Response(response="404", description="Not found"),
   *     @OA\Response(response="500", description="An internal server error")
   * )
   */
  // if request is a GET, e.g. get user
  if($_SERVER['REQUEST_METHOD'] === 'GET'):
    // attempt to query the database
    try {
      // create db query
      // ADD AUTH TO QUERY
      $query = $readDB->prepare('select user_id, user_fullname, user_contact, user_email, user_password, user_status, user_role, branches_branch_id, users.saccos_sacco_id as saccoid, branch_name from users, branches where  branches.branch_id = users.branches_branch_id and user_id = :userid and users.saccos_sacco_id = :saccoid');
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $query->bindParam(':userid', $userid, PDO::PARAM_INT);
  		$query->execute();

      // get row count
      $rowCount = $query->rowCount();

      // create user array to store returned user object
      $userArray = array();

      if($rowCount === 0):
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("user not found");
        $response->send();
        exit;
      endif;

      // for each row returned
      while($row = $query->fetch(PDO::FETCH_ASSOC)):
        // create new User object for each row
        $user = new User($row['user_id'], $row['user_fullname'], $row['user_email'], $row['user_password'], $row['user_contact'], $row['user_status'], $row['user_role'], $row['branches_branch_id'], $row['saccoid'], $row['branch_name']);
        // create User and store in array for return in json data
  	    $userArray[] = $user->returnUserAsArray();
      endwhile;

      // bundle users and rows returned into an array to return in the json data
      $returnData = array();
      $returnData['rows_returned'] = $rowCount;
      $returnData['user'] = $userArray;

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
    catch(UserException $ex) {
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
      $response->addMessage("Failed to get User");
      $response->send();
      exit;
    }
    /**
     * @OA\DELETE(
     *   path="/irembo_version_control/API/users/{userid}", tags={"users-sacco"},
     *    summary ="Delete User - EndPoint",
     *     @OA\MediaType(
      *         mediaType="application/json"
      *     ),
      *   @OA\Parameter(
      *      name="authorization_token",
      *     in="header",
      *     required=true,
      *     @OA\Schema(type="string"),
      *     style = "simple"
      *   ),
      *   @OA\Parameter(
     *      name="userid",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *     @OA\Response(response="200", description="Delete that Specific User"),
     *     @OA\Response(response="400", description="You have received a bad response"),
     *     @OA\Response(response="401", description="unauthorised please try again"),
     *     @OA\Response(response="404", description="Not found"),
     *     @OA\Response(response="500", description="An internal server error")
     * )
     */
  // else if request if a DELETE e.g. delete user
  elseif($_SERVER['REQUEST_METHOD'] === 'DELETE'):
    // attempt to query the database
    try {
      // ADD AUTH TO QUERY
      $query = $writeDB->prepare('delete from users where user_id = :userid and saccos_sacco_id = :saccoid');
      $query->bindParam(':userid', $userid, PDO::PARAM_INT);
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $query->execute();

      // get row count
      $rowCount = $query->rowCount();

      if($rowCount === 0):
              // set up response for unsuccessful return
              $response = new Response();
              $response->setHttpStatusCode(404);
              $response->setSuccess(false);
              $response->addMessage("User not found");
              $response->send();
              exit;
      else:
              $response = new Response();
              $response->setHttpStatusCode(200);
              $response->setSuccess(true);
              $response->addMessage("User deleted");
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
      $response->addMessage("Failed to delete Users - Attached Info");
      $response->send();
      exit;
    }
    /**
     * @OA\PATCH(
     *   path="/irembo_version_control/API/users/{userid}", tags={"users-sacco"},
     *    summary = "Update Single User",
     *   @OA\Parameter(
      *      name="authorization_token",
      *     in="header",
      *     required=true,
      *     @OA\Schema(type="string")
      *   ),
      *   @OA\Parameter(
     *      name="userid",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *  @OA\RequestBody(
     *     @OA\MediaType(
      *         mediaType="application/json",
      *     @OA\Schema(
      *       @OA\Property(property="fullname", type="string"),
      *       @OA\Property(property="password", type="string"),
      *       @OA\Property(property="username", type="string"),
      *       @OA\Property(property="status", type="string"),
      *       @OA\Property(property="role", type="string"),
      *       @OA\Property(property="branch", type="integer")
    *         )
    *       )
    *     ),
     *     @OA\Response(response="200", description="Session has been renewed"),
     *     @OA\Response(response="400", description="You have received a bad response"),
     *     @OA\Response(response="401", description="unauthorised please try again"),
     *     @OA\Response(response="404", description="Not found"),
     *     @OA\Response(response="500", description="An internal server error")
     * )
     */
  // handle updating user
  elseif($_SERVER['REQUEST_METHOD'] === 'PATCH'):
    // update user
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

      // set user field updated to false initially
      $name = false;
      $password = false;
      $branchid = false;
      $status = false;
      $contact = false;
      $role = false;

      // create blank query fields string to append each field to
      $queryFields = "";

      // check if name exists in PATCH
      if(isset($jsonData->name)):
        // set name field updated to true
        $name = true;
        // add name field to query field string
        $queryFields .= "user_fullname = :fullname, ";
      endif;

      // check if password exists in PATCH
      if(isset($jsonData->password)):
        // set password field updated to true
        $password = true;
        // add password field to query field string
        $queryFields .= "user_password = :password, ";
      endif;

      // check if status exists in PATCH
      if(isset($jsonData->status)):
        // set status field updated to true
        $status = true;
        // add status field to query field string
        $queryFields .= "user_status = :status, ";
      endif;

      // check if branch exists in PATCH
      if(isset($jsonData->branchid)):
        // set branch field updated to true
        $branchid = true;
        // add branch field to query field string
        $queryFields .= "branches_branch_id = :branch, ";
      endif;

      // check if contact exists in PATCH
      if(isset($jsonData->contact)):
        // set contact field updated to true
        $contact = true;
        // add contact field to query field string
        $queryFields .= "user_contact = :contact, ";
      endif;
      // check if contact exists in PATCH
      if(isset($jsonData->role)):
        // set contact field updated to true
        $role = true;
        // add contact field to query field string
        $queryFields .= "user_role = :role, ";
      endif;

      // remove the right hand comma and trailing space
      $queryFields = rtrim($queryFields, ", ");

      // check if any user fields supplied in JSON
      if($name === false && $password === false && $status === false && $branchid === false && $contact ===false && $role ===false):
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("no user fields provided");
        $response->send();
        exit;
      endif;
      // ADD AUTH TO QUERY
      // create db query to get user from database to update - use master db
      $query = $readDB->prepare('select user_id, user_fullname, user_email, user_password, user_contact, user_status, user_role, branches_branch_id, branch_name, users.saccos_sacco_id as saccoid from users, branches where branches.branch_id = users.branches_branch_id and user_id = :userid and users.saccos_sacco_id = :saccoid');
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $query->bindParam(':userid', $userid, PDO::PARAM_INT);
      $query->execute();

      // get row count
      $rowCount = $query->rowCount();

      // make sure that the user exists for a given user id
      if($rowCount === 0):
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("no user found to update");
        $response->send();
        exit;
      endif;


      // for each row returned - should be just one
      while($row = $query->fetch(PDO::FETCH_ASSOC)):
        // create new user object
        $user = new User($row['user_id'], $row['user_fullname'], $row['user_email'], $row['user_password'], $row['user_contact'], $row['user_status'], $row['user_role'], $row['branches_branch_id'],$row['saccoid'], $row['branch_name']);
      endwhile;

      // ADD AUTH TO QUERY
      // create the query string including any query fields
      $queryString = "UPDATE users set ".$queryFields." where user_id = :userid and saccos_sacco_id = :saccoid";
      // prepare the query
      $query = $writeDB->prepare($queryString);

      // if name has been provided
      if($name === true):
        // set user object name to given value (checks for valid input)
        $user->setName($jsonData->name);
        // get the value back as the object could be handling the return of the value differently to
        // what was provided
        $up_name = $user->getName();
        // bind the parameter of the new value from the object to the query (prevents SQL injection)
        $query->bindParam(':fullname', $up_name, PDO::PARAM_STR);
      endif;

      // if password has been provided
      if($password === true):
        // set user object address to given value (checks for valid input)
        $user->setPassword($jsonData->password);
        // get the value back as the object could be handling the return of the value differently to
        // what was provided
        $up_password = $user->getPassword();
        // hash the password using alog
        $_hashed_password = password_hash($up_password, PASSWORD_DEFAULT);
        // bind the parameter of the new value from the object to the query (prevents SQL injection)
        $query->bindParam(':password', $_hashed_password, PDO::PARAM_STR);
      endif;

      // if username has been provided
      if($status === true):
        // set user object code to given value (checks for valid input)
        $user->setStatus($jsonData->status);
        // get the value back as the object could be handling the return of the value differently to
        // what was provided
        $up_status = $user->getStatus();
        // bind the parameter of the new value from the object to the query (prevents SQL injection)
        $query->bindParam(':status', $up_status, PDO::PARAM_STR);
      endif;

      // if branch has been provided
      if($branchid === true):
        // set user object branch to given value (checks for valid input)
        $user->setBranchID($jsonData->branchid);
        // get the value back as the object could be handling the return of the value differently to
        // what was provided
        $up_branch = $user->getBranchID();
        // bind the parameter of the new value from the object to the query (prevents SQL injection)
        $query->bindParam(':branch', $up_branch, PDO::PARAM_INT);
      endif;

      // if status has been provided
      if($contact === true):
        // set user object status to given value (checks for valid input)
        $user->setContact($jsonData->contact);
        // get the value back as the object could be handling the return of the value differently to
        // what was provided
        $up_contact = $user->getContact();
        // bind the parameter of the new value from the object to the query (prevents SQL injection)
        $query->bindParam(':contact', $up_contact, PDO::PARAM_STR);
      endif;

      if($role === true):
        // set user object status to given value (checks for valid input)
        $user->setRole($jsonData->role);
        // get the value back as the object could be handling the return of the value differently to
        // what was provided
        $up_role = $user->getRole();
        // bind the parameter of the new value from the object to the query (prevents SQL injection)
        $query->bindParam(':role', $up_role, PDO::PARAM_STR);
      endif;

      // bind the saccoid id provided in the query string
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      // bind the user id returned
      $query->bindParam(':userid', $userid, PDO::PARAM_INT);
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
        $response->addMessage("user not updated - given values may be the same as the stored values");
        $response->send();
        exit;
      endif;
      // ADD AUTH TO QUERY
      // create db query to return the newly edited user - connect to master database
      $query = $writeDB->prepare('select user_id, user_fullname, user_email, user_password, user_contact, user_status, user_role, branches_branch_id,branch_name, users.saccos_sacco_id as saccoid from users, branches where branches.branch_id = users.branches_branch_id and user_id = :userid and users.saccos_sacco_id = :saccoid');
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $query->bindParam(':userid', $userid, PDO::PARAM_INT);
      $query->execute();

      // get row count
      $rowCount = $query->rowCount();

      // check if user was found
      if($rowCount === 0):
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("no user found");
        $response->send();
        exit;
      endif;
      // create user array to store returned users
      $userArray = array();
      // for each row returned
      while($row = $query->fetch(PDO::FETCH_ASSOC)):
      // create new user object for each row returned
        $user = new User($row['user_id'], $row['user_fullname'], $row['user_email'], $row['user_password']= null, $row['user_contact'], $row['user_status'], $row['user_role'], $row['branches_branch_id'],$row['saccoid'], $row['branch_name']);
        // create user and store in array for return in json data
        $userArray[] = $user->returnUserAsArray();
      endwhile;
      // bundle user and rows returned into an array to return in the json data
      $returnData = array();
      $returnData['rows_returned'] = $rowCount;
      $returnData['user'] = $userArray;

      // set up response for successful return
      $response = new Response();
      $response->setHttpStatusCode(200);
      $response->setSuccess(true);
      $response->addMessage("user has been updated");
      $response->setData($returnData);
      $response->send();
      exit;
    }
    catch(UserException $ex) {
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
      $response->addMessage("Failed to update user - check your data for errors" );
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
// get users that are active that have submitted a completed filter
elseif(array_key_exists("status",$_GET)):

  // get status from query string
  $status = $_GET['status'];

  // check to see if user in query string is either active or inactive
  if($status !== "active" || $status !== "inactive"):
    $response = new Response();
    $response->setHttpStatusCode(400);
    $response->setSuccess(false);
    $response->addMessage("Status filter must be inactive or active");
    $response->send();
    exit;
  endif;
  /**
   * @OA\GET(
   *   path="/irembo_version_control/API/users/{status}", tags={"users-sacco"},
   *    summary ="Get users by status- EndPoint",
   *     @OA\MediaType(
    *         mediaType="application/json"
    *     ),
    *   @OA\Parameter(
    *      name="authorization_token",
    *     in="header",
    *     required=true,
    *     @OA\Schema(type="string"),
    *     style = "simple"
    *   ),
    *   @OA\Parameter(
   *      name="status",
   *     in="path",
   *     required=true,
   *     @OA\Schema(type="string")
   *   ),
   *     @OA\Response(response="201", description="returns users"),
   *     @OA\Response(response="400", description="You have received a bad response"),
   *     @OA\Response(response="401", description="unauthorised please try again"),
   *     @OA\Response(response="404", description="Not found"),
   *     @OA\Response(response="500", description="An internal server error")
   * )
   */
  if($_SERVER['REQUEST_METHOD'] === 'GET'):
    // attempt to query the database
    try {
      // ADD AUTH TO QUERY
      // create db query
      $query = $readDB->prepare('select user_id, user_fullname, user_email, user_password, user_contact, user_status, user_role, branches_branch_id, branch_name, users.saccos_sacco_id as saccoid from users, branches where branches.branch_id = users.branches_branch_id and users.saccos_sacco_id = :saccoid');
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
  		$query->execute();

      // get row count
      $rowCount = $query->rowCount();

      // create user array to store returned users
      $userArray = array();

      // for each row returned
      while($row = $query->fetch(PDO::FETCH_ASSOC)):
        // create new user object for each row
        $user = new User($row['user_id'], $row['user_fullname'], $row['user_email'], $row['user_password'] = null, $row['user_contact'], $row['user_status'], $row['user_role'], $row['branches_branch_id'], $row['saccoid'], $row['branch_name']);
        // create users and store in array for return in json data
  	    $userArray[] = $user->returnUserAsArray();
      endwhile;

      // bundle user and rows returned into an array to return in the json data
      $returnData = array();
      $returnData['rows_returned'] = $rowCount;
      $returnData['users'] = $userArray;

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
    catch(UserException $ex) {
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
      $response->addMessage("Failed to get user");
      $response->send();
      exit;
    }
  // if any other request method apart from GET is used then return 405 method not allowed
else:
    $response = new Response();
    $response->setHttpStatusCode(405);
    $response->setSuccess(false);
    $response->addMessage("Request method not allowed");
    $response->send();
    exit;
endif;
// handle getting all users page of 20 at a time
elseif(array_key_exists("page",$_GET)):
  /**
   * @OA\GET(
   *   path="/irembo_version_control/API/users/page/{page}", tags={"users-sacco"},
   *    summary ="Get User interms of pagination pages - EndPoint",
   *     @OA\MediaType(
    *         mediaType="application/json"
    *     ),
    *   @OA\Parameter(
    *      name="authorization_token",
    *     in="header",
    *     required=true,
    *     @OA\Schema(type="string"),
    *     style = "simple"
    *   ),
    *   @OA\Parameter(
   *      name="page",
   *     in="path",
   *     required=true,
   *     @OA\Schema(type="integer")
   *   ),
   *     @OA\Response(response="201", description="returns users in pages"),
   *     @OA\Response(response="400", description="You have received a bad response"),
   *     @OA\Response(response="401", description="unauthorised please try again"),
   *     @OA\Response(response="404", description="Not found"),
   *     @OA\Response(response="500", description="An internal server error")
   * )
   */
    // if request is a GET e.g. get users by page i.e. update
  if($_SERVER['REQUEST_METHOD'] === 'GET'):
  // if any other request method apart from GET is used then return 405 method not allowed
else:
    $response = new Response();
    $response->setHttpStatusCode(405);
    $response->setSuccess(false);
    $response->addMessage("Request method not allowed");
    $response->send();
    exit;
endif;

// handle getting all user or creating a new one
elseif(empty($_GET)):
  /**
   * @OA\GET(
   *   path="/irembo_version_control/API/users", tags={"users-sacco"},
   *    summary = "Get all sacco Users",
   *   @OA\Parameter(
    *      name="authorization_token",
    *     in="header",
    *     required=true,
    *     @OA\Schema(type="string")
    *   ),
   *     @OA\Response(response="200", description="Session has been renewed"),
   *     @OA\Response(response="400", description="You have received a bad response"),
   *     @OA\Response(response="401", description="unauthorised please try again"),
   *     @OA\Response(response="404", description="Not found"),
   *     @OA\Response(response="500", description="An internal server error")
   * )
   */
  // if request is a GET e.g. get users
  if($_SERVER['REQUEST_METHOD'] === 'GET'):

    // attempt to query the database
    try {
      // ADD AUTH TO QUERY
      // create db query
      $query = $readDB->prepare('select user_id, user_fullname, user_email, user_password, user_contact, user_status, user_role, branches_branch_id, branch_name, users.saccos_sacco_id as saccoid from users, branches where branches.branch_id = users.branches_branch_id and users.saccos_sacco_id = :saccoid ORDER BY user_id DESC');
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $query->execute();

      // get row count
      $rowCount = $query->rowCount();

      // create user array to store returned users
      $userArray = array();

      // for each row returned
      while($row = $query->fetch(PDO::FETCH_ASSOC)):
        // create new user object for each row
        $user = new User($row['user_id'], $row['user_fullname'], $row['user_email'], $row['user_password'] = null, $row['user_contact'], $row['user_status'], $row['user_role'], $row['branches_branch_id'], $row['saccoid'], $row['branch_name']);
        // create user and store in array for return in json data
        $userArray[] = $user->returnUserAsArray();
      endwhile;

      // bundle user and rows returned into an array to return in the json data
      $returnData = array();
      $returnData['rows_returned'] = $rowCount;
      $returnData['users'] = $userArray;

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
    catch(UserException $ex) {
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
      $response->addMessage("Failed to get users");
      $response->send();
      exit;
    }
    /**
     * @OA\POST(
     *   path="/irembo_version_control/API/users", tags={"users-sacco"},
     *    summary = "create users under a sacco",
     *   @OA\Parameter(
      *      name="authorization_token",
      *     in="header",
      *     required=true,
      *     @OA\Schema(type="string")
      *   ),
     *  @OA\RequestBody(
     *     @OA\MediaType(
      *         mediaType="application/json",
      *     @OA\Schema(
      *       @OA\Property(property="name", type="string"),
      *       @OA\Property(property="username", type="string"),
      *       @OA\Property(property="status", type="string"),
      *       @OA\Property(property="role", type="string"),
      *       @OA\Property(property="branch", type="integer")
    *         )
    *       )
    *     ),
     *     @OA\Response(response="200", description="User has been registered"),
     *     @OA\Response(response="400", description="You have received a bad response"),
     *     @OA\Response(response="401", description="unauthorised please try again"),
     *     @OA\Response(response="404", description="Not found"),
     *     @OA\Response(response="500", description="An internal server error")
     * )
     */
  // else if request is a POST e.g. create user
  elseif($_SERVER['REQUEST_METHOD'] === 'POST'):

    // create a user by the sacco main branch
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
      if(!isset($jsonData->name) || !isset($jsonData->username) || !isset($jsonData->branchid) || !isset($jsonData->status) || !isset($jsonData->role) || !isset($jsonData->contact)):
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        (!isset($jsonData->name) ? $response->addMessage("name field is mandatory and must be provided") : false);
        (!isset($jsonData->username) ? $response->addMessage("username field is mandatory and must be provided") : false);
        (!isset($jsonData->branchid) ? $response->addMessage("branch is mandatory and must be provided") : false);
        (!isset($jsonData->status) ? $response->addMessage("status field is mandatory and must be provided") : false);
        (!isset($jsonData->role) ? $response->addMessage("role field is mandatory and must be provided") : false);
        (!isset($jsonData->contact) ? $response->addMessage("contact field is mandatory and must be provided") : false);
        $response->send();
        exit;
      endif;

      // check whether the user exists for sure
      $query = $readDB->prepare('select user_id, user_email, saccos_sacco_id from users where user_email = :email and saccos_sacco_id = :saccoid');
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $query->bindParam(':email', $jsonData->username, PDO::PARAM_STR);
      $query->execute();

      // get row count
      $rowCount = $query->rowCount();
      if($rowCount >= 1):
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(409);
        $response->setSuccess(false);
        $response->addMessage("Duplicate user in this sacco found");
        $response->send();
        exit;
      endif;

      $defaultpassword = default_password();
      $hashedpassword = password_hash($defaultpassword, PASSWORD_DEFAULT);
      // create new user with data, if non mandatory fields not provided then set to null
      $newUser = new User(null, $jsonData->name, $jsonData->username, $hashedpassword, $jsonData->contact, $jsonData->status, $jsonData->role, $jsonData->branchid,null,null);
      // get name, username, role, password, status and store them in variables
      $name = $newUser->getName();
      $username = $newUser->getUsername();
      $role = $newUser->getRole();
      $status = $newUser->getStatus();
      $password = $newUser->getPassword();
      $branchid = $newUser->getBranchID();
      $contact = $newUser->getContact();
      // ADD AUTH TO QUERY
      // create db query
      $pincode = rand(1001,9999);
      $pass_hash = password_hash($pincode, PASSWORD_DEFAULT);
      $query = $writeDB->prepare('insert into users (user_fullname, user_email, user_password, user_status, user_role, user_contact, branches_branch_id, saccos_sacco_id, user_pincode) values (:name, :username, :password, :status, :role, :contact, :branchid, :saccoid, :pincode)');
      $query->bindParam(':name', $name, PDO::PARAM_STR);
      $query->bindParam(':username', $username, PDO::PARAM_STR);
      $query->bindParam(':password', $password, PDO::PARAM_STR);
      $query->bindParam(':pincode', $pass_hash, PDO::PARAM_STR);
      $query->bindParam(':status', $status, PDO::PARAM_STR);
      $query->bindParam(':role', $role, PDO::PARAM_STR);
      $query->bindParam(':branchid', $branchid, PDO::PARAM_INT);
      $query->bindParam(':contact', $contact, PDO::PARAM_INT);
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
        $response->addMessage("Failed to create User");
        $response->send();
        exit;
      endif;

      // get last user id so we can return the user in the json
      $lastUserID = $writeDB->lastInsertId();
      //insert into thedb
      // insert sms into the database
      // insertSMSDB($writeDB, $message, $accountContact, $returned_saccoid);
      $message = "Dear ".$name.", Welcome to iRembo Finance, your Login password is  ".$defaultpassword. " and Transaction Pincode is ".$pincode."\nPlease DO NOT share this information with anyone and nobody from iRembo Finance will ask for this information. Kindly update your pin and password upon login. For more information contact us a support@irembofinance.com.\nUse the link below to login \n https://mfis.irembofinance.com/login";
      // insert email into the database
      insertEMAILDB($writeDB, $message, $username, $returned_saccoid);
      // create db query to get newly created user - get from master db not read slave as replication may be too slow for successful read
      $query = $writeDB->prepare('select user_id, user_fullname, user_email, user_password, user_contact, user_status, user_role, branches_branch_id, branch_name, users.saccos_sacco_id as saccoid from users, branches where branches.branch_id = users.branches_branch_id and users.saccos_sacco_id = :saccoid and user_id= :userid');
      $query->bindParam(':userid', $lastUserID, PDO::PARAM_INT);
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $query->execute();

      // get row count
      $rowCount = $query->rowCount();

      // make sure that the new user was returned
      if($rowCount === 0):
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage("Failed to retrieve user after creation");
        $response->send();
        exit;
      endif;

      // create empty array to store user
      $userArray = array();

      // for each row returned - should be just one
      while($row = $query->fetch(PDO::FETCH_ASSOC)):
        // create new user object
        $user = new User($row['user_id'], $row['user_fullname'], $row['user_email'], $row['user_password'], $row['user_contact'], $row['user_status'], $row['user_role'], $row['branches_branch_id'], $row['saccoid'], $row['branch_name']);
        // create user and store in array for return in json data
        $userArray[] = $user->returnUserAsArray();
      endwhile;
      // bundle users and rows returned into an array to return in the json data
      $returnData = array();
      $returnData['rows_returned'] = $rowCount;
      $returnData['user'] = $userArray;

      //set up response for successful return
      $response = new Response();
      $response->setHttpStatusCode(201);
      $response->setSuccess(true);
      $response->addMessage("user created");
      $response->setData($returnData);
      $response->send();
      exit;
    }
    // if user fails to create due to data types, missing fields or invalid data then send error json
    catch(UserException $ex) {
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
      $response->addMessage("Failed to insert user into database - check submitted data for errors");
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
// return 404 error if endpoint not available
else:
  $response = new Response();
  $response->setHttpStatusCode(404);
  $response->setSuccess(false);
  $response->addMessage("Endpoint not found");
  $response->send();
  exit;
endif;
