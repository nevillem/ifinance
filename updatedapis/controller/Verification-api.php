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
/**
 * @OA\POST(
 *   path="/irembo_version_control/API/verify", tags={"vi-sacco"},
 *    summary = "Verify a new sacco, EndPoint",
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
  *       @OA\Property(property="otp", type="integer")
*         )
*       )
*     ),
 *     @OA\Response(response="201", description="sacco main branch has been verified"),
 *     @OA\Response(response="400", description="You have received a bad response"),
 *     @OA\Response(response="401", description="unauthorised please try again"),
 *     @OA\Response(response="404", description="Not found"),
 *     @OA\Response(response="500", description="An internal server error")
 * )
 */
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

if($_SERVER['REQUEST_METHOD'] === 'POST'):

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
    $rawPatchdata = file_get_contents('php://input');

    if(!$jsonData = json_decode($rawPatchdata)):
      // set up response for unsuccessful request
      $response = new Response();
      $response->setHttpStatusCode(400);
      $response->setSuccess(false);
      $response->addMessage("Request body is not valid JSON");
      $response->send();
      exit;
    endif;

    // check if patch request contains access token
    if(!isset($jsonData->otp) || strlen($jsonData->otp) < 1):
      $response = new Response();
      $response->setHttpStatusCode(400);
      $response->setSuccess(false);
      (!isset($jsonData->otp) ? $response->addMessage("Otp  not supplied") : false);
      (strlen($jsonData->otp) < 1 ? $response->addMessage("Otp cannot be blank") : false);
      $response->send();
      exit;
    endif;
    //assign value to the otp sent
    $_code = (int) trim($jsonData->otp);

try {
  // create db query to check access token is equal to the one provided
  $query = $readDB->prepare('SELECT sacco_id, sacco_name, sacco_status, saccos_sacco_id, access_token_expiry, access_token from sessions, saccos where sessions.saccos_sacco_id = saccos.sacco_id and access_token = :accesstoken');
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

  // check if access token has expired
  if(strtotime($returned_accesstokenexpiry) < time()):
    $response = new Response();
    $response->setHttpStatusCode(401);
    $response->setSuccess(false);
    $response->addMessage("Access token has expired");
    $response->send();
    exit;
  endif;
  // check if OTP supplied is valid or invalid
  $query = $readDB->prepare('SELECT verification_code, saccos_sacco_id, verification_status FROM verification WHERE verification_code = :code AND saccos_sacco_id = :id AND verification_status= "notused" ');
  $query->bindParam(':code', $_code, PDO::PARAM_INT);
  $query->bindParam(':id', $returned_saccoid, PDO::PARAM_INT);
  $query->execute();
  $rowCount = $query->rowCount();
  if($rowCount === 0):
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(401);
        $response->setSuccess(false);
        $response->addMessage("No otp found");
        $response->send();
        exit;
      endif;
  // update the otp tables
  $query = $writeDB->prepare('UPDATE verification SET verification_status = "used" WHERE saccos_sacco_id = :id');
  $query->bindParam(':id', $returned_saccoid, PDO::PARAM_INT);
  $query->execute();

  $query = $writeDB->prepare('UPDATE saccos SET sacco_status = "active" WHERE sacco_id = :id');
  $query->bindParam(':id', $returned_saccoid, PDO::PARAM_INT);
  $query->execute();

  $query = $readDB->prepare('SELECT sacco_name, sacco_status FROM saccos WHERE sacco_id = :id');
  $query->bindParam(':id', $returned_saccoid, PDO::PARAM_INT);
  $query->execute();

  // get returned row
  $row = $query->fetch(PDO::FETCH_ASSOC);
  // delete the sessions
  $query = $writeDB->prepare('DELETE FROM sessions WHERE saccos_sacco_id = :id');
  $query->bindParam(':id', $returned_saccoid, PDO::PARAM_INT);
  $query->execute();

    // build response data array which contains basic user details
    $returnData = array();
    $returnData['saccoid'] = $returned_saccoid;
    $returnData['sacconame'] = $row['sacco_name'];
    $returnData['saccostatus'] = $row['sacco_status'];

    $response = new Response();
    $response->setHttpStatusCode(201);
    $response->setSuccess(true);
    $response->addMessage("Account Verified Proceed to Login");
    $response->setData($returnData);
    $response->send();
    exit;
}
catch(PDOException $ex) {
  $response = new Response();
  $response->setHttpStatusCode(500);
  $response->setSuccess(false);
  $response->addMessage("There was an issue verify - please try again");
  $response->send();
  exit;
}
elseif($_SERVER['REQUEST_METHOD'] === 'PATCH'):

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
  try {
    // create db query to check access token is equal to the one provided
    $query = $readDB->prepare('SELECT sacco_id, sacco_name, sacco_email, access_token_expiry, access_token from sessions, saccos where sessions.saccos_sacco_id = saccos.sacco_id and access_token = :accesstoken');
    $query->bindParam(':accesstoken', $accesstoken, PDO::PARAM_STR);
    $query->execute();

    // get row count
    $rowCount = $query->rowCount();
    if($rowCount === 0):
      // set up response for unsuccessful log out response
      $response = new Response();
      $response->setHttpStatusCode(402);
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
    $_email = $row['sacco_email'];
    $_name = $row['sacco_name'];
    // check if access token has expired
    if(strtotime($returned_accesstokenexpiry) < time()):
      $response = new Response();
      $response->setHttpStatusCode(401);
      $response->setSuccess(false);
      $response->addMessage("Access token has expired");
      $response->send();
      exit;
    endif;

    // update the otp tables
    $query = $writeDB->prepare('UPDATE verification SET verification_status = "used" WHERE saccos_sacco_id = :id');
    $query->bindParam(':id', $returned_saccoid, PDO::PARAM_INT);
    $query->execute();

    $_code = otpfunction();

    $query = $writeDB->prepare('INSERT into verification (verification_code, saccos_sacco_id) values (:code, :id)');
    $query->bindParam(':code', $_code, PDO::PARAM_INT);
    $query->bindParam(':id', $returned_saccoid, PDO::PARAM_INT);
    $query->execute();
    $rowCount = $query->rowCount();

    if($rowCount === 0):
      // set up response for error
      $response = new Response();
      $response->setHttpStatusCode(500);
      $response->setSuccess(false);
      $response->addMessage("Error Creating an OTP - please try again");
      $response->send();
      exit;
    endif;
      // build response after sending email
      send_otp_email($_code, $_email, $_name);
      $returnData = array();
      $returnData['saccoid'] = $returned_saccoid;
      $returnData['code'] = $_code;
      $returnData['name'] = $_name;
      $returnData['email'] = $_email;
      $response = new Response();
      $response->setHttpStatusCode(201);
      $response->setSuccess(true);
      $response->addMessage("Otp resent successfully");
      $response->setData($returnData);
      $response->send();
      exit;
  }
  catch(PDOException $ex) {
    $response = new Response();
    $response->setHttpStatusCode(500);
    $response->setSuccess(false);
    $response->addMessage("There was an issuing verifycode - please try again");
    $response->send();
    exit;
  }
else:
  $response = new Response();
  $response->setHttpStatusCode(405);
  $response->setSuccess(false);
  $response->addMessage("Request Method not allowed");
  $response->send();
  exit;
endif;
