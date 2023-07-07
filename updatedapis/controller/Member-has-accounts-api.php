<?php
require_once('../core/initialize.php');
require_once('Database.php');
require_once('../model/Response.php');

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


  if (array_key_exists('memberaccountsid', $_GET)) {

    $memberaccountsid = $_GET['memberaccountsid'];

    if($memberaccountsid == '' || !is_numeric($memberaccountsid)) {
      $response = new Response();
      $response->setHttpStatusCode(400);
      $response->setSuccess(false);
      $response->addMessage("member accounts ID cannot be blank or must be numeric");
      $response->send();
      exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
      // we pick the orders name and products under that orders
      try {
        // create db query
        $accountsquery = $writeDB->prepare('SELECT member_accounts_id, member_fname, member_mname, member_lname,
          member_contact,members_account_number,account_name,account_code
           from accounts,member_accounts,members
          WHERE member_accounts_member_id=member_id AND member_accounts_sacco_id = :saccoid
          AND member_accounts_id =:member_accounts_id
          AND members.member_id=member_accounts.member_accounts_member_id
          AND accounts_id=member_accounts_account_id AND member_type="individual"');
        $accountsquery->bindParam(':member_accounts_id', $memberaccountsid, PDO::PARAM_STR);
        $accountsquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_STR);
        $accountsquery->execute();

        $rowCount = $accountsquery->rowCount();
        if($rowCount === 0) {
          // set up response for unsuccessful return
          $response = new Response();
          $response->setHttpStatusCode(404);
          $response->setSuccess(false);
          $response->addMessage("member account id not found");
          $response->send();
          exit;
        }
        $accountsArray=array();
        while($rowaccount = $accountsquery->fetch(PDO::FETCH_ASSOC)) {
          extract($rowaccount);
          $accounts = array(
            "id" => $member_accounts_id ,
            "member_fname" => $member_fname.' '.$member_mname.' '.$member_lname,
            "member_account" => $members_account_number,
            "account" => $account_name,
            "code" => $account_code,
        );
        $accountsArray[]=$accounts;
        }

        $returnData = array();
        $returnData['rows_returned'] = $rowCount;
        $returnData['accounts'] = $accountsArray;
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
        $response->addMessage("failed to get member acounts");
        $response->send();
        exit;
      }

    } elseif($_SERVER['REQUEST_METHOD'] === 'DELETE'){
      try {
        // ADD AUTH TO QUERY
        $query = $writeDB->prepare('DELETE from member_accounts where member_accounts_id = :memberaccountsid
        AND member_accounts_sacco_id = :saccoid');
        $query->bindParam(':memberaccountsid', $memberaccountsid, PDO::PARAM_STR);
        $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
        $query->execute();

        // get row count
        $rowCount = $query->rowCount();

        if($rowCount === 0):
                // set up response for unsuccessful return
                $response = new Response();
                $response->setHttpStatusCode(404);
                $response->setSuccess(false);
                $response->addMessage("member account not found");
                $response->send();
                exit;
        else:
                $response = new Response();
                $response->setHttpStatusCode(200);
                $response->setSuccess(true);
                $response->addMessage("member account deleted");
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
        $response->addMessage("Failed to delete memeber account - Attached Info");
        $response->send();
        exit;
      }

    } elseif($_SERVER['REQUEST_METHOD'] === 'PATCH'){
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

      $member = false;
      $account = false;
      // create blank query fields string to append each field to
      $queryFields = "";

      // check if name exists in PATCH
      if(isset($jsonData->memberid)):
        // set title field updated to true
        $member = true;
        // add name field to query field string
        $queryFields .= "member_accounts_member_id  = :member, ";
      endif;

      // check if address exists in PATCH
      if(isset($jsonData->account_id)):
        // set Address field updated to true
        $account = true;
        // add address field to query field string
        $queryFields .= "member_accounts_account_id  = :account, ";
      endif;
      // remove the right hand comma and trailing space
      $queryFields = rtrim($queryFields, ", ");

      // check if any branch fields supplied in JSON
      if($member === false && $account === false):
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("No fields provided");
        $response->send();
        exit;
      endif;
      // ADD AUTH TO QUERY
      // create db query to get branch from database to update - use master db
      $query = $writeDB->prepare('SELECT * from member_accounts where member_accounts_id = :memberaccountsid
      AND member_accounts_sacco_id = :saccoid');
      $query->bindParam(':memberaccountsid', $memberaccountsid, PDO::PARAM_STR);
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $query->execute();

      // get row count
      $rowCount = $query->rowCount();

      // make sure that the branch exists for a given branch id
      if($rowCount === 0):
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("No member acount found to update");
        $response->send();
        exit;
      endif;
      // create the query string including any query fields
      $queryString = "UPDATE member_accounts set ".$queryFields." where member_accounts_id  = :id";
      // prepare the query
      $query = $writeDB->prepare($queryString);

      // if name has been provided
      if($member === true):
        // bind the parameter of the new value from the object to the query (prevents SQL injection)
        $query->bindParam(':member', $jsonData->memberid, PDO::PARAM_STR);
      endif;

      // if name has been provided
      if($account === true):
        // bind the parameter of the new value from the object to the query (prevents SQL injection)
        $query->bindParam(':account', $jsonData->account_id, PDO::PARAM_STR);
      endif;
      $query->bindParam(':id', $memberaccountsid, PDO::PARAM_STR);
      $query->execute();
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
      $response->addMessage("member account has been updated");
      $response->setData($returnData);
      $response->send();
      exit;

    }
    catch(PDOException $ex) {
      $response = new Response();
      $response->setHttpStatusCode(400);
      $response->setSuccess(false);
      $response->addMessage("failed to update");
      $response->send();
      exit;
    }
    // if error with sql query return a json error
    catch(PDOException $ex) {
      error_log("Database Query Error: ".$ex, 0);
      $response = new Response();
      $response->setHttpStatusCode(500);
      $response->setSuccess(false);
      $response->addMessage("Failed to update member accounts - check your data for errors" . $ex);
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

  }
   elseif(empty($_GET)){
        // get the user profile data
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {

        try {
          $query = $writeDB->prepare('SELECT member_id, member_fname, member_mname, member_lname,
            member_contact, member_gender, member_email,member_address,member_identification,members_account_number
             from members where saccos_sacco_id =:saccoid');
          $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
          $query->execute();

          $rowCount = $query->rowCount();
          $memberArray = array();
          while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $members = array(
              "id" => $member_id,
              "member_fname" => $member_fname,
              "member_mname" => $member_mname,
              "member_lname" => $member_lname,
              "member_contact" =>  $member_contact,
              "member_gender" =>  $member_gender
          );
          $accountsquery = $writeDB->prepare('SELECT accounts_id,account_name,account_code
            from accounts,member_accounts, members
            WHERE member_accounts_sacco_id = :saccoid AND member_accounts_member_id =:memberid
            AND members.member_id=member_accounts.member_accounts_member_id
            AND accounts_id=member_accounts_account_id AND member_type="individual"');
          $accountsquery->bindParam(':memberid', $member_id, PDO::PARAM_STR);
          $accountsquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_STR);
          $accountsquery->execute();

          $accountsArray=array();
          while($rowaccount = $accountsquery->fetch(PDO::FETCH_ASSOC)) {
            extract($rowaccount);
            $accounts = array(
              "id" => $accounts_id,
              "code" => $account_code,
              "name" => $account_name,
          );
          $accountsArray[]=$accounts;
          }
          $members['accounts'] = $accountsArray;
          $memberArray[] = $members;
          }
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
          if(!isset($jsonData->member_id) || !isset($jsonData->accounts_attached)|| !isset($jsonData->doj)) {
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            (!isset($jsonData->member_id) ? $response->addMessage("member  is mandatory and must be provided") : false);
            (empty($jsonData->member_id) ? $response->addMessage("member field must not be empty") : false);
            (!isset($jsonData->doj) ? $response->addMessage("date of oppening field is mandatory and must be provided") : false);
            (empty($jsonData->doj) ? $response->addMessage("date of oppening field must not be empty") : false);
            (sizeof($jsonData->accounts_attached)==0 ? $response->addMessage("accounts field is empty") : false);
            $response->send();
            exit;
          }
          try{
            // $rowCount=0;
            // $lastID=0;
            $query = $writeDB->prepare('SELECT * from members where member_id = :id');
            $query->bindParam(':id', $jsonData->member_id, PDO::PARAM_STR);
            $query->execute();

            $rowCount = $query->rowCount();
            if ($rowCount === 0) {
              $response = new Response();
              $response->setHttpStatusCode(500);
              $response->setSuccess(false);
              $response->addMessage("member with an id $jsonData->member_id does not exist");
              $response->send();
              exit;
            }
            for ($i=0; $i < count($jsonData->accounts_attached); $i++) {
              $countsatt=$jsonData->accounts_attached[$i];
              $query = $writeDB->prepare('SELECT * from member_accounts where member_accounts_account_id = :memberaccounts
              AND member_accounts_member_id=:memberid');
              $query->bindParam(':memberaccounts', $countsatt, PDO::PARAM_STR);
              $query->bindParam(':memberid', $jsonData->member_id, PDO::PARAM_STR);
              $query->execute();

              $rowCount = $query->rowCount();
              if ($rowCount > 0) {
                $response = new Response();
                $response->setHttpStatusCode(400);
                $response->setSuccess(false);
                $response->addMessage("some of the accounts already exists for this member");
                $response->send();
                exit;
              }
               $accountA= $jsonData->accounts_attached[$i];
              $query = $writeDB->prepare('INSERT into member_accounts(
               `member_accounts_member_id`,
               `member_accounts_account_id`,
                `member_accounts_sacco_id`,
                `member_accounts_date_opened`
              ) values (:member_id, :account, :saccoid,:doj)');
              $query->bindParam(':member_id', $jsonData->member_id, PDO::PARAM_STR);
              $query->bindParam(':account', $accountA, PDO::PARAM_STR);
              $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
              $query->bindParam(':doj', $jsonData->doj, PDO::PARAM_STR);
              $query->execute();
              $lastID = $writeDB->lastInsertId();
              $rowCount = $query->rowCount();
              }
              if ($rowCount === 0) {
                $response = new Response();
                $response->setHttpStatusCode(500);
                $response->setSuccess(false);
                $response->addMessage("internal server error");
                $response->send();
                exit;
              }

              $query = $writeDB->prepare('SELECT * from member_accounts where member_accounts_id = :id');
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
            $memberAccountsArray = array();
              while($row = $query->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $order = array(
                  "id" => $member_accounts_id,
                  "member_id" => $member_accounts_member_id,
                  "account" => $member_accounts_account_id,
                  "dateopened" => $member_accounts_date_opened,
              );
                $memberAccountsArray[] = $order;
              }
              // bundle branch and rows returned into an array to return in the json data
              $returnData = array();
              $returnData['rows_returned'] = $rowCount;
              $returnData['memberaccount'] = $memberAccountsArray;

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

    } else {
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
