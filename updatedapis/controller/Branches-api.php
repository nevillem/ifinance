<?php

require_once('../core/initialize.php');
require_once('Database.php');
require_once('../model/Response.php');
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
// check if branchid is in the url e.g. /branch/1

if (array_key_exists("branchid",$_GET)):
  // get branch id from query string
  $branchid = $_GET['branchid'];

  //check to see if branch id in query string is not empty and is number, if not return json error
  if($branchid == '' || !is_numeric($branchid)):
    $response = new Response();
    $response->setHttpStatusCode(400);
    $response->setSuccess(false);
    $response->addMessage("branch ID cannot be blank or must be numeric");
    $response->send();
    exit;
  endif;
  /**
   * @OA\GET(
   *   path="/irembo_version_control/API/branches/{branchid}", tags={"branches-sacco"},
   *    summary ="Get Branch By ID - EndPoint",
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
   *      name="branchid",
   *     in="path",
   *     required=true,
   *     @OA\Schema(type="integer")
   *   ),
   *     @OA\Response(response="201", description="returns branches"),
   *     @OA\Response(response="400", description="You have received a bad response"),
   *     @OA\Response(response="401", description="unauthorised please try again"),
   *     @OA\Response(response="404", description="Not found"),
   *     @OA\Response(response="500", description="An internal server error")
   * )
   */
  // if request is a GET, e.g. get branch
  if($_SERVER['REQUEST_METHOD'] === 'GET'):
    // attempt to query the database
    try {
      // create db query
      // ADD AUTH TO QUERY
      $query = $readDB->prepare('select branch_id, branch_name, branch_code, branch_address, branch_status from branches where branch_id = :branchid and saccos_sacco_id = :saccoid');
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $query->bindParam(':branchid', $branchid, PDO::PARAM_INT);
  		$query->execute();

      // get row count
      $rowCount = $query->rowCount();

      // create branch array to store returned branch
      $branchArray = array();

      if($rowCount === 0):
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("branch not found");
        $response->send();
        exit;
      endif;

      // for each row returned
      while($row = $query->fetch(PDO::FETCH_ASSOC)):
        // create new branch object for each row
        $branch = new Branch($row['branch_id'], $row['branch_name'], $row['branch_address'], $row['branch_code'], $row['branch_status']);
        // create branch and store in array for return in json data
  	    $branchArray[] = $branch->returnBranchAsArray();
      endwhile;

      // bundle branches and rows returned into an array to return in the json data
      $returnData = array();
      $returnData['rows_returned'] = $rowCount;
      $returnData['branch'] = $branchArray;

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
    catch(BranchException $ex) {
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
      $response->addMessage("Failed to get Branch");
      $response->send();
      exit;
    }
    /**
     * @OA\DELETE(
     *   path="/irembo_version_control/API/branches/{branchid}", tags={"branches-sacco"},
     *    summary ="Delete Branch - EndPoint",
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
     *      name="branchid",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *     @OA\Response(response="200", description="Delete that Specific branch"),
     *     @OA\Response(response="400", description="You have received a bad response"),
     *     @OA\Response(response="401", description="unauthorised please try again"),
     *     @OA\Response(response="404", description="Not found"),
     *     @OA\Response(response="500", description="An internal server error")
     * )
     */
  // else if request if a DELETE e.g. delete branch
  elseif($_SERVER['REQUEST_METHOD'] === 'DELETE'):
    // attempt to query the database
    try {
      // ADD AUTH TO QUERY
      $query = $writeDB->prepare('delete from branches where branch_id = :branchid and saccos_sacco_id = :saccoid');
      $query->bindParam(':branchid', $branchid, PDO::PARAM_INT);
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $query->execute();

      // get row count
      $rowCount = $query->rowCount();

      if($rowCount === 0):
              // set up response for unsuccessful return
              $response = new Response();
              $response->setHttpStatusCode(404);
              $response->setSuccess(false);
              $response->addMessage("Branch not found");
              $response->send();
              exit;
      else:
              $response = new Response();
              $response->setHttpStatusCode(200);
              $response->setSuccess(true);
              $response->addMessage("Branch deleted");
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
      $response->addMessage("Failed to delete Branch - Attached Info");
      $response->send();
      exit;
    }
    /**
     * @OA\PATCH(
     *   path="/irembo_version_control/API/branches/{branchid}", tags={"branches-sacco"},
     *    summary = "Update branch single",
     *   @OA\Parameter(
      *      name="authorization_token",
      *     in="header",
      *     required=true,
      *     @OA\Schema(type="string")
      *   ),
      *   @OA\Parameter(
     *      name="branchid",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *  @OA\RequestBody(
     *     @OA\MediaType(
      *         mediaType="application/json",
      *     @OA\Schema(
      *       @OA\Property(property="name", type="string"),
      *       @OA\Property(property="address", type="string"),
      *       @OA\Property(property="code", type="integer"),
      *       @OA\Property(property="status", type="string")
    *         )
    *       )
    *     ),
     *     @OA\Response(response="200", description="Branch updated"),
     *     @OA\Response(response="400", description="You have received a bad response"),
     *     @OA\Response(response="401", description="unauthorised please try again"),
     *     @OA\Response(response="404", description="Not found"),
     *     @OA\Response(response="500", description="An internal server error")
     * )
     */
  // handle updating branch
  elseif($_SERVER['REQUEST_METHOD'] === 'PATCH'):
    // update branch
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

      // set branch field updated to false initially
      $name = false;
      $address = false;
      $code = false;
      $status = false;

      // create blank query fields string to append each field to
      $queryFields = "";

      // check if name exists in PATCH
      if(isset($jsonData->name)):
        // set title field updated to true
        $name = true;
        // add name field to query field string
        $queryFields .= "branch_name = :name, ";
      endif;

      // check if address exists in PATCH
      if(isset($jsonData->address)):
        // set Address field updated to true
        $address = true;
        // add address field to query field string
        $queryFields .= "branch_address = :address, ";
      endif;

      // check if code exists in PATCH
      if(isset($jsonData->code)):
        // set deadline field updated to true
        $code = true;
        // add code field to query field string
        $queryFields .= "branch_code = :code, ";
      endif;

      // check if status exists in PATCH
      if(isset($jsonData->status)):
        // set status field updated to true
        $status = true;
        // add status field to query field string
        $queryFields .= "branch_status = :status, ";
      endif;

      // remove the right hand comma and trailing space
      $queryFields = rtrim($queryFields, ", ");

      // check if any branch fields supplied in JSON
      if($name === false && $address === false && $code === false && $status === false):
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("No Branch fields provided");
        $response->send();
        exit;
      endif;
      // ADD AUTH TO QUERY
      // create db query to get branch from database to update - use master db
      $query = $writeDB->prepare('select branch_id, branch_name, branch_code, branch_address, branch_status from branches where branch_id = :branchid and saccos_sacco_id = :saccoid');
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $query->bindParam(':branchid', $branchid, PDO::PARAM_INT);
      $query->execute();

      // get row count
      $rowCount = $query->rowCount();

      // make sure that the branch exists for a given branch id
      if($rowCount === 0):
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("No Branch found to update");
        $response->send();
        exit;
      endif;

      // for each row returned - should be just one
      while($row = $query->fetch(PDO::FETCH_ASSOC)):
        // create new branch object
        $branch = new Branch($row['branch_id'], $row['branch_name'], $row['branch_address'], $row['branch_code'], $row['branch_status']);
      endwhile;
      // ADD AUTH TO QUERY
      // create the query string including any query fields
      $queryString = "UPDATE branches set ".$queryFields." where branch_id = :branchid and saccos_sacco_id = :saccoid";
      // prepare the query
      $query = $writeDB->prepare($queryString);

      // if name has been provided
      if($name === true):
        // set branch object name to given value (checks for valid input)
        $branch->setName($jsonData->name);
        // get the value back as the object could be handling the return of the value differently to
        // what was provided
        $up_name = $branch->getName();
        // bind the parameter of the new value from the object to the query (prevents SQL injection)
        $query->bindParam(':name', $up_name, PDO::PARAM_STR);
      endif;

      // if address has been provided
      if($address === true):
        // set branch object address to given value (checks for valid input)
        $branch->setAddress($jsonData->address);
        // get the value back as the object could be handling the return of the value differently to
        // what was provided
        $up_address = $branch->getAddress();
        // bind the parameter of the new value from the object to the query (prevents SQL injection)
        $query->bindParam(':address', $up_address, PDO::PARAM_STR);
      endif;

      // if deadline has been provided
      if($code === true):
        // set branch object code to given value (checks for valid input)
        $branch->setCode($jsonData->code);
        // get the value back as the object could be handling the return of the value differently to
        // what was provided
        $up_code = $branch->getCode();
        // bind the parameter of the new value from the object to the query (prevents SQL injection)
        $query->bindParam(':code', $up_code, PDO::PARAM_INT);
      endif;

      // if status has been provided
      if($status === true):
        // set branch object status to given value (checks for valid input)
        $branch->setStatus($jsonData->status);
        // get the value back as the object could be handling the return of the value differently to
        // what was provided
        $up_status = $branch->getStatus();
        // bind the parameter of the new value from the object to the query (prevents SQL injection)
        $query->bindParam(':status', $up_status, PDO::PARAM_STR);
      endif;

      // bind the Branch id provided in the query string
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      // bind the branch id returned
      $query->bindParam(':branchid', $branchid, PDO::PARAM_INT);
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
        $response->addMessage("Branch not updated - given values may be the same as the stored values");
        $response->send();
        exit;
      endif;
      // ADD AUTH TO QUERY
      // create db query to return the newly edited branch - connect to master database
      $query = $writeDB->prepare('select branch_id, branch_name, branch_code, branch_address, branch_status from branches where branch_id = :branchid and saccos_sacco_id = :saccoid');
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $query->bindParam(':branchid', $branchid, PDO::PARAM_INT);
      $query->execute();

      // get row count
      $rowCount = $query->rowCount();

      // check if branch was found
      if($rowCount === 0):
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("No Branch found");
        $response->send();
        exit;
      endif;
      // create branch array to store returned branches
      $branchArray = array();

      // for each row returned
      while($row = $query->fetch(PDO::FETCH_ASSOC)):
      // create new branch object for each row returned
        $branch = new Branch($row['branch_id'], $row['branch_name'], $row['branch_address'], $row['branch_code'], $row['branch_status']);
        // create branch and store in array for return in json data
        $branchArray[] = $branch->returnBranchAsArray();
      endwhile;
      // bundle branch and rows returned into an array to return in the json data
      $returnData = array();
      $returnData['rows_returned'] = $rowCount;
      $returnData['branch'] = $branchArray;

      // set up response for successful return
      $response = new Response();
      $response->setHttpStatusCode(200);
      $response->setSuccess(true);
      $response->addMessage("Branch updated");
      $response->setData($returnData);
      $response->send();
      exit;
    }
    catch(BranchException $ex) {
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
      $response->addMessage("Failed to update branch - check your data for errors");
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
// get branches that are active that have submitted a status filter
elseif(array_key_exists("status",$_GET)):

  // get status from query string
  $status = $_GET['status'];

  // check to see if status in query string is either active or inactive
  if($status !== "active" && $status !== "inactive"):
    $response = new Response();
    $response->setHttpStatusCode(400);
    $response->setSuccess(false);
    $response->addMessage("Status filter must be inactive or active");
    $response->send();
    exit;
  endif;
  /**
   * @OA\GET(
   *   path="/irembo_version_control/API/branches/{status}", tags={"branches-sacco"},
   *    summary ="Get branch by status- EndPoint",
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
   *     @OA\Response(response="201", description="returns branches"),
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
      $query = $readDB->prepare('SELECT branch_id, branch_name, branch_code, branch_address, branch_status from branches where branch_status like :status and saccos_sacco_id = :saccoid');
      $query->bindParam(':status', $status, PDO::PARAM_STR);
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
  		$query->execute();

      // get row count
      $rowCount = $query->rowCount();

      // create branch array to store returned branches
      $branchArray = array();

      // for each row returned
      while($row = $query->fetch(PDO::FETCH_ASSOC)):
        // create new branch object for each row
        $branch = new Branch($row['branch_id'], $row['branch_name'], $row['branch_address'], $row['branch_code'], $row['branch_status']);
        // create branch and store in array for return in json data
  	    $branchArray[] = $branch->returnBranchAsArray();
      endwhile;

      // bundle branch and rows returned into an array to return in the json data
      $returnData = array();
      $returnData['rows_returned'] = $rowCount;
      $returnData['branches'] = $branchArray;

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
    catch(BranchException $ex) {
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
      $response->addMessage("Failed to get branch");
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
// handle getting all branches page of 20 at a time
elseif(array_key_exists("page",$_GET)):
  /**
   * @OA\GET(
   *   path="/irembo_version_control/API/branches/page/{page}", tags={"branches-sacco"},
   *    summary ="Get Branch pages - EndPoint",
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
   *     @OA\Response(response="201", description="returns branches"),
   *     @OA\Response(response="400", description="You have received a bad response"),
   *     @OA\Response(response="401", description="unauthorised please try again"),
   *     @OA\Response(response="404", description="Not found"),
   *     @OA\Response(response="500", description="An internal server error")
   * )
   */
    // if request is a GET e.g. get branches
  if($_SERVER['REQUEST_METHOD'] === 'GET'):

    // get page id from query string
    $page = $_GET['page'];

    //check to see if page id in query string is not empty and is number, if not return json error
    if($page == '' || !is_numeric($page)):
      $response = new Response();
      $response->setHttpStatusCode(400);
      $response->setSuccess(false);
      $response->addMessage("Page number cannot be blank and must be numeric");
      $response->send();
      exit;
    endif;

    // set limit to 20 per page
    $limitPerPage = 20;

    // attempt to query the database
    try {
      // ADD AUTH TO QUERY

      // get total number of branches for sacco
      // create db query
      $query = $readDB->prepare('SELECT count(brnach_id) as totalNoOfBranches from branches where saccos_sacco_id = :saccoid');
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $query->execute();

      // get row for count total
      $row = $query->fetch(PDO::FETCH_ASSOC);

      $branchCount = intval($row['totalNoOfBranches']);

      // get number of pages required for total results use ceil to round up
      $numOfPages = ceil($branchCount/$limitPerPage);

      // if no rows returned then always allow page 1 to show a successful response with 0 branches
      if($numOfPages == 0):
        $numOfPages = 1;
      endif;

      // if passed in page number is greater than total number of pages available or page is 0 then 404 error - page not found
      if($page > $numOfPages || $page == 0):
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("Page not found");
        $response->send();
        exit;
      endif;

      // set offset based on current page, e.g. page 1 = offset 0, page 2 = offset 20
      $offset = ($page == 1 ?  0 : (20*($page-1)));

      // ADD AUTH TO QUERY
      // get rows for page
      // create db query
      $query = $readDB->prepare('SELECT branch_id, branch_name, branch_code, branch_address, branch_status from branches where saccos_sacco_id = :saccoid limit :pglimit OFFSET :offset');
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $query->bindParam(':pglimit', $limitPerPage, PDO::PARAM_INT);
      $query->bindParam(':offset', $offset, PDO::PARAM_INT);
      $query->execute();

      // get row count
      $rowCount = $query->rowCount();

      // create branch array to store returned branchs
      $branchArray = array();

      // for each row returned
      while($row = $query->fetch(PDO::FETCH_ASSOC)):
        // create new branch object for each row
        $branch = new Branch($row['branch_id'], $row['branch_name'], $row['branch_address'], $row['branch_code'], $row['branch_status']);
        // create branch and store in array for return in json data
        $branchArray[] = $branch->returnBranchAsArray();
      endwhile;

      // bundle branch and rows returned into an array to return in the json data
      $returnData = array();
      $returnData['rows_returned'] = $rowCount;
      $returnData['total_rows'] = $branchCount;
      $returnData['total_pages'] = $numOfPages;
      // if passed in page less than total pages then return true
      ($page < $numOfPages ? $returnData['has_next_page'] = true : $returnData['has_next_page'] = false);
      // if passed in page greater than 1 then return true
      ($page > 1 ? $returnData['has_previous_page'] = true : $returnData['has_previous_page'] = false);
      $returnData['branches'] = $branchArray;

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
    catch(BranchException $ex) {
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
      $response->addMessage("Failed to get Branches");
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

// handle getting all branch or creating a new one
elseif(empty($_GET)):
  /**
   * @OA\GET(
   *   path="/irembo_version_control/API/branches", tags={"branches-sacco"},
   *    summary ="Get Branch  - EndPoint",
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
   *     @OA\Response(response="201", description="returns all branches"),
   *     @OA\Response(response="400", description="You have received a bad response"),
   *     @OA\Response(response="401", description="unauthorised please try again"),
   *     @OA\Response(response="404", description="Not found"),
   *     @OA\Response(response="500", description="An internal server error")
   * )
   */
  // if request is a GET e.g. get branches
  if($_SERVER['REQUEST_METHOD'] === 'GET'):

    // attempt to query the database
    try {
      // ADD AUTH TO QUERY
      // create db query
      $query = $readDB->prepare('SELECT branch_id, branch_name, branch_code, branch_address, branch_status from branches where saccos_sacco_id = :saccoid ORDER BY branch_id DESC');
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $query->execute();

      // get row count
      $rowCount = $query->rowCount();

      // create branch array to store returned branchs
      $branchArray = array();

      // for each row returned
      while($row = $query->fetch(PDO::FETCH_ASSOC)):
        // create new branch object for each row
        $branch = new Branch($row['branch_id'], $row['branch_name'], $row['branch_address'], $row['branch_code'], $row['branch_status']);
        // create branch and store in array for return in json data
        $branchArray[] = $branch->returnBranchAsArray();
      endwhile;

      // bundle branch and rows returned into an array to return in the json data
      $returnData = array();
      $returnData['rows_returned'] = $rowCount;
      $returnData['branches'] = $branchArray;

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
    catch(BranchException $ex) {
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
      $response->addMessage("Failed to get branches");
      $response->send();
      exit;
    }
    /**
     * @OA\POST(
     *   path="/irembo_version_control/API/branches", tags={"branches-sacco"},
     *    summary = "create new branches under the sacco",
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
      *       @OA\Property(property="address", type="string"),
      *       @OA\Property(property="code", type="integer"),
      *       @OA\Property(property="status", type="string")
    *         )
    *       )
    *     ),
     *     @OA\Response(response="200", description="Branch has been registered"),
     *     @OA\Response(response="400", description="You have received a bad response"),
     *     @OA\Response(response="401", description="unauthorised please try again"),
     *     @OA\Response(response="404", description="Not found"),
     *     @OA\Response(response="500", description="An internal server error")
     * )
     */
  // else if request is a POST e.g. create branch
  elseif($_SERVER['REQUEST_METHOD'] === 'POST'):

    // create branch
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

      // check if post request contains  data in body as these are mandatory
      if(!isset($jsonData->name) || !isset($jsonData->address) || !isset($jsonData->code) || !isset($jsonData->status)):
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        (!isset($jsonData->name) ? $response->addMessage("Name field is mandatory and must be provided") : false);
        (!isset($jsonData->address) ? $response->addMessage("Address field is mandatory and must be provided") : false);
        (!isset($jsonData->code) ? $response->addMessage("Code field is mandatory and must be provided") : false);
        (!isset($jsonData->status) ? $response->addMessage("Status field is mandatory and must be provided") : false);
        $response->send();
        exit;
      endif;

      // create new branch with data, if non mandatory fields not provided then set to null
      $newBranch = new Branch(null, $jsonData->name, $jsonData->address, $jsonData->code, $jsonData->status);
      // get name, address, code, status and store them in variables
      $name = $newBranch->getName();
      $address = $newBranch->getAddress();
      $code = $newBranch->getCode();
      $status = $newBranch->getStatus();

      // ADD AUTH TO QUERY
      // create db query
      $query = $writeDB->prepare('insert into branches (branch_name, branch_code, branch_address, branch_status, saccos_sacco_id) values (:name, :code, :address, :status, :saccoid)');
      $query->bindParam(':name', $name, PDO::PARAM_STR);
      $query->bindParam(':address', $address, PDO::PARAM_STR);
      $query->bindParam(':code', $code, PDO::PARAM_STR);
      $query->bindParam(':status', $status, PDO::PARAM_STR);
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
        $response->addMessage("Failed to create Branch");
        $response->send();
        exit;
      endif;

      // get last branch id so we can return the branch in the json
      $lastBranchID = $writeDB->lastInsertId();
      // ADD AUTH TO QUERY
      // create db query to get newly created branch - get from master db not read slave as replication may be too slow for successful read
      $query = $writeDB->prepare('SELECT branch_id, branch_name, branch_code, branch_address, branch_status from branches where branch_id =:branchid and saccos_sacco_id = :saccoid');
      $query->bindParam(':branchid', $lastBranchID, PDO::PARAM_INT);
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $query->execute();

      // get row count
      $rowCount = $query->rowCount();

      // make sure that the new branch was returned
      if($rowCount === 0):
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage("Failed to retrieve Branch after creation");
        $response->send();
        exit;
      endif;

      // create empty array to store branch
      $branchArray = array();

      // for each row returned - should be just one
      while($row = $query->fetch(PDO::FETCH_ASSOC)):
        // create new branch object
        $branch = new Branch($row['branch_id'], $row['branch_name'], $row['branch_address'], $row['branch_code'], $row['branch_status']);
        // create branch and store in array for return in json data
        $branchArray[] = $branch->returnBranchAsArray();
      endwhile;
      // bundle branches and rows returned into an array to return in the json data
      $returnData = array();
      $returnData['rows_returned'] = $rowCount;
      $returnData['branch'] = $branchArray;

      //set up response for successful return
      $response = new Response();
      $response->setHttpStatusCode(201);
      $response->setSuccess(true);
      $response->addMessage("Branch created");
      $response->setData($returnData);
      $response->send();
      exit;
    }
    // if branch fails to create due to data types, missing fields or invalid data then send error json
    catch(BranchException $ex) {
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
      $response->addMessage("Failed to insert branch into database - check submitted data for errors");
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
