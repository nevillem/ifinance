<?php
require_once('../core/initialize.php');
require_once('Database.php');
require_once('../model/loanproducts.php');
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

// check if id is in the url e.g. /s/1
if (array_key_exists("loanproduct",$_GET)) {
  // get  id from query string
  $loanproduct = $_GET['loanproduct'];
  //check to see if  id in query string is not empty and is number, if not return json error
  if($loanproduct == '' || !is_numeric($loanproduct)) {
    $response = new Response();
    $response->setHttpStatusCode(400);
    $response->setSuccess(false);
    $response->addMessage("LoanProductID cannot be blank or must be numeric");
    $response->send();
    exit;
  }

  // if request is a GET, e.g. get member
  if($_SERVER['REQUEST_METHOD'] === 'GET') {
    // attempt to query the database
    try {
      // create db query
      // ADD AUTH TO QUERY

      $query = $readDB->prepare('SELECT sloan_product_id,	name_of_loan_product,	loan_type,install_payment,loan_rate_type,
        interest_rate,loan_processing_fees,	minmum_amount,	maxmum_amount,number_of_guarantors,
          	can_client_be_self_guarantor,deduct_installment_beofore_disbursment,charge_penalties,
            does_interest_change_defaulted,	new_interest_rate,must_have_security	 from loan_product_settings WHERE
             sloan_product_id = :loanpid and loan_product_saccoid = :saccoid');
      $query->bindParam(':loanpid', $loanproduct, PDO::PARAM_INT);
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
  		$query->execute();

      // get row count
      $rowCount = $query->rowCount();

      // create  array to store returned
      $loanProductArray = array();

      if($rowCount === 0) {
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("loan product not found");
        $response->send();
        exit;
      }

      // for each row returned
      while($row = $query->fetch(PDO::FETCH_ASSOC)) {
        // get  Images for given member, store them in array to pass to member
        // create new  object for each row
        $loanproduct = new LoanProductSettings(
          $row['sloan_product_id'], $row['name_of_loan_product'], $row['loan_type'], $row['install_payment'],
           $row['loan_rate_type'], $row['interest_rate'],
          $row['loan_processing_fees'], $row['minmum_amount'], $row['maxmum_amount'],
           $row['number_of_guarantors'],
          $row['can_client_be_self_guarantor'], $row['deduct_installment_beofore_disbursment'],
           $row['does_interest_change_defaulted'],$row['new_interest_rate'], $row['must_have_security'],$row['charge_penalties']);
        // create  and store in array for return in json data
  	    $loanProductArray[] = $loanproduct->returnLoanProductAsArray();
      }
      // bundle s and rows returned into an array to return in the json data
      $returnData = array();
      $returnData['rows_returned'] = $rowCount;
      $returnData['loanproduct'] = $loanProductArray;

      // set up response for successful return
      $response = new Response();
      $response->setHttpStatusCode(200);
      $response->setSuccess(true);
      $response->toCache(true);
      $response->setData($returnData);
      $response->send();
      exit;
    }
    catch(LoanProductSettingsException $ex) {
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
      $response->addMessage("Failed to get loan product ");
      $response->send();
      exit;
    }
  }
  // else if request if a DELETE e.g. delete
  elseif($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // attempt to query the database
    try {

      // once all images are deleted then delete the actual
      $query = $writeDB->prepare('DELETE from loan_product_settings WHERE
       sloan_product_id = :loanpid and loan_product_saccoid = :saccoid');
      $query->bindParam(':loanpid', $loanproduct, PDO::PARAM_INT);
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $query->execute();

      // get row count
      $rowCount = $query->rowCount();

      if($rowCount === 0) {
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("loan product not found");
        $response->send();
        exit;
      }

      // set up response for successful return
      $response = new Response();
      $response->setHttpStatusCode(200);
      $response->setSuccess(true);
      $response->addMessage("loan product deleted");
      $response->send();
      exit;
    }
    // if error with sql query return a json error
    catch(LoanProductSettingsException $ex) {
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
      $response->addMessage("Failed to delete loan product".$ex);
      $response->send();
      exit;
    }
  }
  // handle updating
  elseif($_SERVER['REQUEST_METHOD'] === 'PATCH') {
    // // update
    // try {
    //   // check request's content type header is JSON
    //   if($_SERVER['CONTENT_TYPE'] !== 'application/json') {
    //     // set up response for unsuccessful request
    //     $response = new Response();
    //     $response->setHttpStatusCode(400);
    //     $response->setSuccess(false);
    //     $response->addMessage("Content Type header not set to JSON");
    //     $response->send();
    //     exit;
    //   }
    //
    //   // get PATCH request body as the PATCHed data will be JSON format
    //   $rawPatchData = file_get_contents('php://input');
    //
    //   if(!$jsonData = json_decode($rawPatchData)) {
    //     // set up response for unsuccessful request
    //     $response = new Response();
    //     $response->setHttpStatusCode(400);
    //     $response->setSuccess(false);
    //     $response->addMessage("Request body is not valid JSON");
    //     $response->send();
    //     exit;
    //   }
    //
    //   // set  field updated to false initially
    //   $dob_updated = false;
    //   $gender_updated = false;
    //   $identification_updated = false;
    //   $status_updated = false;
    //   $email_updated = false;
    //   $doj_updated = false;
    //   // $attach_updated = false;
    //   $contact_updated = false;
    //   $fistname_updated = false;
    //   $midlename_updated = false;
    //   $lastname_updated = false;
    //   $address_updated = false;
    //   $employment_status_update = false;
    //   $gross_income_update = false;
    //   $gross_income_update = false;
    //   $marital_status_update= false;
    //   // create blank query fields string to append each field to
    //   $queryFields = "";
    //
    //   // check if title exists in PATCH
    //   if(isset($jsonData->dob)) {
    //     // set title field updated to true
    //     $dob_updated = true;
    //     // add title field to query field string
    //     $queryFields .= "member_date_birth = :dob, ";
    //   }
    //
    //   // check if title exists in PATCH
    //   if(isset($jsonData->address)) {
    //     // set title field updated to true
    //     $address_updated = true;
    //     // add title field to query field string
    //     $queryFields .= "member_address = :address, ";
    //   }
    //
    //   // check if description exists in PATCH
    //   if(isset($jsonData->gender)) {
    //     // set description field updated to true
    //     $gender_updated = true;
    //     // add description field to query field string
    //     $queryFields .= "member_gender = :gender, ";
    //   }
    //
    //   // check if deadline exists in PATCH
    //   if(isset($jsonData->identification)) {
    //     // set deadline field updated to true
    //     $identification_updated = true;
    //     // add deadline field to query field string
    //     $queryFields .= "member_identification = :identification, ";
    //   }
    //
    //   // check if completed exists in PATCH
    //   if(isset($jsonData->status)) {
    //     // set completed field updated to true
    //     $status_updated = true;
    //     // add completed field to query field string
    //     $queryFields .= "member_status = :status, ";
    //   }
    //   // check if completed exists in PATCH
    //   if(isset($jsonData->email)) {
    //     // set completed field updated to true
    //     $email_updated = true;
    //     // add completed field to query field string
    //     $queryFields .= "member_email = :email, ";
    //   }
    //   // check if completed exists in PATCH
    //   if(isset($jsonData->doj)) {
    //     // set completed field updated to true
    //     $doj_updated = true;
    //     // add completed field to query field string
    //     $queryFields .= "member_join_date = :doj, ";
    //   }
    //   // check if completed exists in PATCH
    //   // if(isset($jsonData->attach)) {
    //   //   // set completed field updated to true
    //   //   $attach_updated = true;
    //   //   // add completed field to query field string
    //   //   $queryFields .= "member_attach = :attach, ";
    //   // }
    //   // check if completed exists in PATCH
    //   if(isset($jsonData->contact)) {
    //     // set completed field updated to true
    //     $contact_updated = true;
    //     // add completed field to query field string
    //     $queryFields .= "member_contact = :contact, ";
    //   }
    //   // check if completed exists in PATCH
    //   if(isset($jsonData->firstname)) {
    //     // set completed field updated to true
    //     $firstname_updated = true;
    //     // add completed field to query field string
    //     $queryFields .= "member_fname = :firstname, ";
    //   }
    //   // check if completed exists in PATCH
    //   if(isset($jsonData->midlename)) {
    //     // set completed field updated to true
    //     $midlename_update = true;
    //     // add completed field to query field string
    //     $queryFields .= "member_mname = :midlename, ";
    //   }
    //   // check if completed exists in PATCH
    //   if(isset($jsonData->contact)) {
    //     // set completed field updated to true
    //     $lastname_updated = true;
    //     // add completed field to query field string
    //     $queryFields .= "member_lname = :lastname, ";
    //   }
    //   // check if completed exists in PATCH
    //   if(isset($jsonData->employment_status)) {
    //     // set completed field updated to true
    //     $employment_status_update = true;
    //     // add completed field to query field string
    //     $queryFields .= "member_employment_status = :employment_status, ";
    //   }
    //   // check if completed exists in PATCH
    //   if(isset($jsonData->gross_income)) {
    //     // set completed field updated to true
    //     $gross_income_update = true;
    //     // add completed field to query field string
    //     $queryFields .= "member_gross_income = :gross_income, ";
    //   }
    //   // check if completed exists in PATCH
    //   if(isset($jsonData->marital_status)) {
    //     // set completed field updated to true
    //     $marital_status_update = true;
    //     // add completed field to query field string
    //     $queryFields .= "member_marital_status = :marital_status, ";
    //   }
    //   // remove the right hand comma and trailing space
    //   $queryFields = rtrim($queryFields, ", ");
    //
    //   // check if any  fields supplied in JSON
    //   if($dob_updated === false && $gender_updated === false && $identification_updated === false && $status_updated === false
    //     && $email_updated === false && $doj_updated === false  && $contact_updated === false
    //     && $fistname_updated === false && $midlename_update === false && $lastname_updated === false
    //     && $address_updated === false && $employment_status_update === false && $gross_income_update === false && $marital_status_update === false) {
    //     $response = new Response();
    //     $response->setHttpStatusCode(400);
    //     $response->setSuccess(false);
    //     $response->addMessage("No member fields provided");
    //     $response->send();
    //     exit;
    //   }
    //   // ADD AUTH TO QUERY
    //   // create db query to get  from database to update - use master db
    //   $query = $writeDB->prepare('SELECT * from members where member_id = :memberid and saccos_sacco_id = :saccoid');
    //   $query->bindParam(':memberid', $memberid, PDO::PARAM_INT);
    //   $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
    //   $query->execute();
    //
    //   // get row count
    //   $rowCount = $query->rowCount();
    //
    //   // make sure that the  exists for a given  id
    //   if($rowCount === 0) {
    //     // set up response for unsuccessful return
    //     $response = new Response();
    //     $response->setHttpStatusCode(404);
    //     $response->setSuccess(false);
    //     $response->addMessage("No member found to update");
    //     $response->send();
    //     exit;
    //   }
    //
    //   // for each row returned - should be just one
    //   while($row = $query->fetch(PDO::FETCH_ASSOC)) {
    //     // create new  object
    //    $member = new Member(
    //      $row['member_id'], $row['member_fname'],$row['member_mname'], $row['member_lname'], $row['member_contact'], $row['member_gender'],
    //      $row['member_email'], $row['member_address'], $row['member_date_birth'], $row['member_join_date'], $row['member_identification'],
    //      $row['member_status'], $row['member_type'], $row['members_account_number'], $row['member_employment_status'], $row['member_gross_income'],
    //      $row['member_marital_status'],$row['member_group'],$row['member_attach'], $row['members_account_volunteer'], $row['members_account_fixed'],
    //      $row['members_account_compuslaory'], $row['member_account_shares']
    //   );
    //    }
    //   // ADD AUTH TO QUERY
    //   // create the query string including any query fields
    //   $queryString = "update members set ".$queryFields." where member_id = :memberid and saccos_sacco_id = :saccoid";
    //   // prepare the query
    //   $query = $writeDB->prepare($queryString);
    //
    //   // if has been provided
    //   if($dob_updated === true) {
    //     // set  object title to given value (checks for valid input)
    //     $member->setDob($jsonData->dob);
    //     // get the value back as the object could be handling the return of the value differently to
    //     // what was provided
    //     $up_dob = $member->getDob();
    //     // bind the parameter of the new value from the object to the query (prevents SQL injection)
    //     $query->bindParam(':dob', $up_dob, PDO::PARAM_STR);
    //   }
    //   // if has been provided
    //   if($gender_updated === true) {
    //     // set  object title to given value (checks for valid input)
    //     $member->setGender($jsonData->gender);
    //     // get the value back as the object could be handling the return of the value differently to
    //     // what was provided
    //     $up_gender = $member->getGender();
    //     // bind the parameter of the new value from the object to the query (prevents SQL injection)
    //     $query->bindParam(':gender', $up_gender, PDO::PARAM_STR);
    //   }
    //
    //   // if has been provided
    //   if($identification_updated === true) {
    //     // set  object title to given value (checks for valid input)
    //     $member->setIDN($jsonData->identification);
    //     // get the value back as the object could be handling the return of the value differently to
    //     // what was provided
    //     $up_identification = $member->getIDN();
    //     // bind the parameter of the new value from the object to the query (prevents SQL injection)
    //     $query->bindParam(':identification', $up_identification, PDO::PARAM_STR);
    //   }
    //
    //   // if has been provided
    //   if($email_updated === true) {
    //     // set  object title to given value (checks for valid input)
    //     $member->setEmail($jsonData->email);
    //     // get the value back as the object could be handling the return of the value differently to
    //     // what was provided
    //     $up_email = $member->getEmail();
    //     // bind the parameter of the new value from the object to the query (prevents SQL injection)
    //     $query->bindParam(':email', $up_email, PDO::PARAM_STR);
    //   }
    //
    //   // if has been provided
    //   if($doj_updated === true) {
    //     // set  object title to given value (checks for valid input)
    //     $member->setDoj($jsonData->doj);
    //     // get the value back as the object could be handling the return of the value differently to
    //     // what was provided
    //     $up_doj = $member->getDoj();
    //     // bind the parameter of the new value from the object to the query (prevents SQL injection)
    //     $query->bindParam(':doj', $up_doj, PDO::PARAM_STR);
    //   }
    //
    //   // if has been provided
    //   // if($attach_updated === true) {
    //   //   // set  object title to given value (checks for valid input)
    //   //   $member->setAttach($jsonData->attach);
    //   //   // get the value back as the object could be handling the return of the value differently to
    //   //   // what was provided
    //   //   $up_attach = $member->getAttach();
    //   //   // bind the parameter of the new value from the object to the query (prevents SQL injection)
    //   //   $query->bindParam(':attach', $up_attach, PDO::PARAM_STR);
    //   // }
    //
    //   // if has been provided
    //   if($status_updated === true) {
    //     // set  object title to given value (checks for valid input)
    //     $member->setStatus($jsonData->status);
    //     // get the value back as the object could be handling the return of the value differently to
    //     // what was provided
    //     $up_status = $member->getStatus();
    //     // bind the parameter of the new value from the object to the query (prevents SQL injection)
    //     $query->bindParam(':status', $up_status, PDO::PARAM_STR);
    //   }
    //
    //   // if has been provided
    //   if($address_updated === true) {
    //     // set  object title to given value (checks for valid input)
    //     $member->setAddress($jsonData->address);
    //     // get the value back as the object could be handling the return of the value differently to
    //     // what was provided
    //     $up_address = $member->getAddress();
    //     // bind the parameter of the new value from the object to the query (prevents SQL injection)
    //     $query->bindParam(':address', $up_address, PDO::PARAM_STR);
    //   }
    //
    //   // if has been provided
    //   if($firstname_updated === true) {
    //     // set  object title to given value (checks for valid input)
    //     $member->setFirstname($jsonData->firstname);
    //     // get the value back as the object could be handling the return of the value differently to
    //     // what was provided
    //     $up_firstname = $member->getFirstname();
    //     // bind the parameter of the new value from the object to the query (prevents SQL injection)
    //     $query->bindParam(':firstname', $up_firstname, PDO::PARAM_STR);
    //   }
    //   // if has been provided
    //   if($midlename_update === true) {
    //     // set  object title to given value (checks for valid input)
    //     $member->setMidlename($jsonData->midlename);
    //     // get the value back as the object could be handling the return of the value differently to
    //     // what was provided
    //     $up_midlename = $member->getMidlename();
    //     // bind the parameter of the new value from the object to the query (prevents SQL injection)
    //     $query->bindParam(':midlename', $up_midlename, PDO::PARAM_STR);
    //   }
    //   // if has been provided
    //   if($lastname_updated === true) {
    //     // set  object title to given value (checks for valid input)
    //     $member->setLastname($jsonData->lastname);
    //     // get the value back as the object could be handling the return of the value differently to
    //     // what was provided
    //     $up_lastname = $member->getLastname();
    //     // bind the parameter of the new value from the object to the query (prevents SQL injection)
    //     $query->bindParam(':lastname', $up_lastname, PDO::PARAM_STR);
    //   }
    //   // if has been provided
    //   if($contact_updated === true) {
    //     // set  object title to given value (checks for valid input)
    //     $member->setContact($jsonData->contact);
    //     // get the value back as the object could be handling the return of the value differently to
    //     // what was provided
    //     $up_contact = $member->getContact();
    //     // bind the parameter of the new value from the object to the query (prevents SQL injection)
    //     $query->bindParam(':contact', $up_contact, PDO::PARAM_STR);
    //   }
    //   // if has been provided
    //   if($employment_status_update === true) {
    //     // set  object title to given value (checks for valid input)
    //     $member->setEmploymentStatus($jsonData->employment_status);
    //     // get the value back as the object could be handling the return of the value differently to
    //     // what was provided
    //     $up_employementStatus = $member->getEmploymentStatus();
    //     // bind the parameter of the new value from the object to the query (prevents SQL injection)
    //     $query->bindParam(':employment_status', $up_employementStatus, PDO::PARAM_STR);
    //   }
    //   // if has been provided
    //   if($gross_income_update === true) {
    //     // set  object title to given value (checks for valid input)
    //     $member->setGrossIncome($jsonData->gross_income);
    //     // get the value back as the object could be handling the return of the value differently to
    //     // what was provided
    //     $up_grossincome = $member->getGrossIncome();
    //     // bind the parameter of the new value from the object to the query (prevents SQL injection)
    //     $query->bindParam(':gross_income', $up_grossincome, PDO::PARAM_STR);
    //   }
    //   // if has been provided
    //   if($marital_status_update === true) {
    //     // set  object title to given value (checks for valid input)
    //     $member->setMaritalStatus($jsonData->marital_status);
    //     // get the value back as the object could be handling the return of the value differently to
    //     // what was provided
    //     $up_maritalStatus = $member->getMaritalStatus();
    //     // bind the parameter of the new value from the object to the query (prevents SQL injection)
    //     $query->bindParam(':marital_status', $up_maritalStatus, PDO::PARAM_STR);
    //   }
    //   // bind the id provided in the query string
    //   $query->bindParam(':memberid', $memberid, PDO::PARAM_INT);
    //   // bind the user id returned
    //   $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
    //   // run the query
    //   $query->execute();
    //
    //   // get affected row count
    //   $rowCount = $query->rowCount();
    //
    //   // check if row was actually updated, could be that the given values are the same as the stored values
    //   if($rowCount === 0) {
    //     // set up response for unsuccessful return
    //     $response = new Response();
    //     $response->setHttpStatusCode(400);
    //     $response->setSuccess(false);
    //     $response->addMessage("Member not updated - given values may be the same as the stored values");
    //     $response->send();
    //     exit;
    //   }
    //   // ADD AUTH TO QUERY
    //   // create db query to return the newly edited  - connect to master database
    //   $query = $writeDB->prepare('SELECT * from members where member_id = :memberid and saccos_sacco_id = :saccoid');
    //   $query->bindParam(':memberid', $memberid, PDO::PARAM_INT);
    //   $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
    //   $query->execute();
    //
    //   // get row count
    //   $rowCount = $query->rowCount();
    //
    //   // check if  was found
    //   if($rowCount === 0) {
    //     // set up response for unsuccessful return
    //     $response = new Response();
    //     $response->setHttpStatusCode(404);
    //     $response->setSuccess(false);
    //     $response->addMessage("No member found");
    //     $response->send();
    //     exit;
    //   }
    //   // create  array to store returned s
    //   $memberArray = array();
    //
    //   // for each row returned
    //   while($row = $query->fetch(PDO::FETCH_ASSOC)) {
    //
    //     // get  Images for given , store them in array to pass to
    //     $imageArray = retrieveImages($writeDB, $row['member_id'], $returned_saccoid);
    //
    //     // create new  object for each row returned
    //     $member = new Member(
    //       $row['member_id'], $row['member_fname'],$row['member_mname'],$row['member_lname'], $row['member_contact'], $row['member_gender'],
    //       $row['member_email'], $row['member_address'], $row['member_date_birth'], $row['member_join_date'], $row['member_identification'],
    //       $row['member_status'], $row['member_type'], $row['members_account_number'],$row['member_employment_status'], $row['member_gross_income'],
    //       $row['member_marital_status'],$row['member_group'], $row['member_attach'], $row['members_account_volunteer'],
    //       $row['members_account_fixed'],
    //       $row['members_account_compuslaory'], $row['member_account_shares'], $imageArray
    //    );
    //
    //     // create  and store in array for return in json data
    //     $memberArray[] = $member->returnMemberAsArray();
    //   }
    //   // bundle s and rows returned into an array to return in the json data
    //   $returnData = array();
    //   $returnData['rows_returned'] = $rowCount;
    //   $returnData['member'] = $memberArray;
    //
    //   // set up response for successful return
    //   $response = new Response();
    //   $response->setHttpStatusCode(200);
    //   $response->setSuccess(true);
    //   $response->addMessage("Member updated");
    //   $response->setData($returnData);
    //   $response->send();
    //   exit;
    // }
    // catch(ImageException $ex) {
    //   $response = new Response();
    //   $response->setHttpStatusCode(400);
    //   $response->setSuccess(false);
    //   $response->addMessage($ex->getMessage());
    //   $response->send();
    //   exit;
    // }
    // catch(Exception $ex) {
    //   $response = new Response();
    //   $response->setHttpStatusCode(400);
    //   $response->setSuccess(false);
    //   $response->addMessage($ex->getMessage());
    //   $response->send();
    //   exit;
    // }
    // // if error with sql query return a json error
    // catch(PDOException $ex) {
    //   error_log("Database Query Error: ".$ex, 0);
    //   $response = new Response();
    //   $response->setHttpStatusCode(500);
    //   $response->setSuccess(false);
    //   $response->addMessage("Failed to update member - check your data for errors".$ex);
    //   $response->send();
    //   exit;
    // }
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

            $query = $readDB->prepare('SELECT sloan_product_id,	name_of_loan_product,	loan_type,install_payment,loan_rate_type,
              interest_rate,loan_processing_fees,	minmum_amount,	maxmum_amount,number_of_guarantors,
                can_client_be_self_guarantor,deduct_installment_beofore_disbursment,charge_penalties,
                  does_interest_change_defaulted,	new_interest_rate,must_have_security	 from loan_product_settings WHERE loan_product_saccoid = :saccoid');
            $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
        		$query->execute();

      // get row count
      $rowCount = $query->rowCount();

      // create  array to store returned s
      $loanProductArray = array();

      // for each row returned
      // for each row returned
      while($row = $query->fetch(PDO::FETCH_ASSOC)) {
        // get  Images for given member, store them in array to pass to member
        // create new  object for each row
        $loanproduct = new LoanProductSettings(
          $row['sloan_product_id'], $row['name_of_loan_product'], $row['loan_type'], $row['install_payment'],
           $row['loan_rate_type'], $row['interest_rate'],
          $row['loan_processing_fees'], $row['minmum_amount'], $row['maxmum_amount'],
           $row['number_of_guarantors'],
          $row['can_client_be_self_guarantor'], $row['deduct_installment_beofore_disbursment'],
           $row['does_interest_change_defaulted'],$row['new_interest_rate'],$row['must_have_security'],$row['charge_penalties']);
        // create  and store in array for return in json data
  	    $loanProductArray[] = $loanproduct->returnLoanProductAsArray();
      }


      // bundle s and rows returned into an array to return in the json data
      $returnData = array();
      $returnData['rows_returned'] = $rowCount;
      $returnData['loanproducts'] = $loanProductArray;

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
      if(!isset($jsonData->product_name)
      || !isset($jsonData->loan_type)
          || !isset($jsonData->install_payment)
          || !isset($jsonData->loan_rate_type)
          || !isset($jsonData->interest_rate)
          || !isset($jsonData->loan_processing_fees)
          || !isset($jsonData->minmum_amount)
          || !isset($jsonData->maxmum_amount)
          || !isset($jsonData->number_of_guarantors)
          || !isset($jsonData->can_client_be_self_guarantor)
          || !isset($jsonData->deduct_installment_beofore_disbursment)
          || !isset($jsonData->does_interest_change_defaulted)
          || !isset($jsonData->charge_penalt)
          || empty($jsonData->charge_penalt)
          || !isset($jsonData->must_have_security)
        ) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        (!isset($jsonData->product_name) ? $response->addMessage("loan product name field is mandatory and must be provided") : false);
        (!isset($jsonData->loan_type) ? $response->addMessage("loan type field is mandatory and must be provided") : false);
        (!isset($jsonData->install_payment) ? $response->addMessage("loan installment field is mandatory and must be provided") : false);
        (!isset($jsonData->loan_rate_type) ? $response->addMessage("loan rate type field is mandatory and must be provided") : false);
        (!isset($jsonData->interest_rate)? $response->addMessage("interest rate field is mandatory and must be provided") : false);
        (!isset($jsonData->loan_processing_fees) ? $response->addMessage("loan processing fees field is mandatory and must be provided") : false);
        (!isset($jsonData->minmum_amount) ? $response->addMessage("minmum amount field is mandatory and must be provided") : false);
        (!isset($jsonData->maxmum_amount) ? $response->addMessage("maxmum amount field is mandatory and must be provided") : false);
        (!isset($jsonData->number_of_guarantors) ? $response->addMessage("number of guarantors field is mandatory and must be provided") : false);
        (!isset($jsonData->can_client_be_self_guarantor) ? $response->addMessage("Can client be self guarantor field is mandatory and must be provided") : false);
        (!isset($jsonData->deduct_installment_beofore_disbursment) ? $response->addMessage("Deduct installment beofore disbursment field is mandatory and must be provided") : false);
        (!isset($jsonData->does_interest_change_defaulted) ? $response->addMessage("Does interest change when  defaulted field is mandatory and must be provided") : false);
        (!isset($jsonData->must_have_security) ? $response->addMessage("must have security field is mandatory and must be provided") : false);
        (!isset($jsonData->charge_penalt) ? $response->addMessage("charge penalt field is mandatory and must be provided") : false);
        (empty($jsonData->charge_penalt) ? $response->addMessage("charge penalt must not be empty") : false);
        $response->send();
        exit;
      }

      // check whether the  exists for sure
      $query = $readDB->prepare('select * from loan_product_settings where name_of_loan_product = :name
      and loan_product_saccoid  = :saccoid');
      $query->bindParam(':name', $jsonData->product_name, PDO::PARAM_STR);
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $query->execute();

      // get row count
      $rowCount = $query->rowCount();
      if($rowCount > 0):
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(409);
        $response->setSuccess(false);
        $response->addMessage("loan product already exists");
        $response->send();
        exit;
      endif;


      $newLoanProduct = new LoanProductSettings(null, $jsonData->product_name,
      $jsonData->loan_type,$jsonData->install_payment,
      $jsonData->loan_rate_type,$jsonData->interest_rate, $jsonData->loan_processing_fees,
       $jsonData->minmum_amount,
       $jsonData->maxmum_amount, $jsonData->number_of_guarantors,
       $jsonData->can_client_be_self_guarantor,
       $jsonData->deduct_installment_beofore_disbursment,$jsonData->does_interest_change_defaulted,
       (isset($jsonData->new_interest_rate) ? $jsonData->new_interest_rate : null)
       , $jsonData->must_have_security, $jsonData->charge_penalt);
      // get values and store them in variables
      $productname = $newLoanProduct->getProductName();
      $loantype = $newLoanProduct->getLoanType();
      $installmentpayment = $newLoanProduct->getInstallPayment();
      $loanratetype = $newLoanProduct->getLoanRateType();
      $interestrate = $newLoanProduct->getInterestRate();
      $loanprocessingfees = $newLoanProduct->getLoanProcessingFees();
      $minmum_amount = $newLoanProduct->getMinimumAmount();
      $maximumamount = $newLoanProduct->getMaxmumAmount();
      $numberofgurantors = $newLoanProduct->getNumberOfGuarantors();
      $selfguarantor = $newLoanProduct->getClientBeSelfGuarantor();
      $deductinstallbeforedisbursment = $newLoanProduct->getDeductInstallmentBeforeDesbursment();
      $interestedchangeddefaulted= $newLoanProduct->getDoesInterestChangeDefault();
      $newinterestrate = $newLoanProduct->getNewInterestRate();
      $musthavesecurity = $newLoanProduct->getMustHaveSecurity();
      $chargepenalt = $newLoanProduct->getChargePenalt();


    //   $category = $newMember->getCategory();
      // create db query
      $query = $writeDB->prepare('insert into  loan_product_settings
      (`name_of_loan_product`, `loan_type`, `install_payment`,
      `loan_rate_type`, `interest_rate`, `loan_processing_fees`,
      `minmum_amount`, `maxmum_amount`, `number_of_guarantors`,`charge_penalties`,
       `can_client_be_self_guarantor`,
      `deduct_installment_beofore_disbursment`,
       `does_interest_change_defaulted`, `new_interest_rate`,`must_have_security`,
       `loan_product_saccoid`)
       values (:productname,:loantype, :installmentpayment,:loanratetype,:interestrate,:loanprocessingfees,
       :minmum_amount,:maximumamount,:numberofgurantors,:chargepenalt,:selfguarantor,:deductinstallbeforedisbursment,
       :interestedchangeddefaulted,
       :newinterestrate,:must_have_security,:saccoid)');
      $query->bindParam(':productname', $productname, PDO::PARAM_STR);
      $query->bindParam(':loantype', $loantype, PDO::PARAM_STR);
      $query->bindParam(':installmentpayment', $installmentpayment, PDO::PARAM_STR);
      $query->bindParam(':loanratetype', $loanratetype, PDO::PARAM_STR);
      $query->bindParam(':interestrate', $interestrate, PDO::PARAM_STR);
      $query->bindParam(':loanprocessingfees', $loanprocessingfees, PDO::PARAM_STR);
      $query->bindParam(':minmum_amount', $minmum_amount, PDO::PARAM_STR);
      $query->bindParam(':maximumamount', $maximumamount, PDO::PARAM_STR);
      $query->bindParam(':chargepenalt', $chargepenalt, PDO::PARAM_STR);
      $query->bindParam(':numberofgurantors', $numberofgurantors, PDO::PARAM_STR);
      $query->bindParam(':selfguarantor', $selfguarantor, PDO::PARAM_STR);
      $query->bindParam(':deductinstallbeforedisbursment', $deductinstallbeforedisbursment, PDO::PARAM_STR);
      $query->bindParam(':interestedchangeddefaulted', $interestedchangeddefaulted, PDO::PARAM_STR);
      $query->bindParam(':newinterestrate', $newinterestrate, PDO::PARAM_STR);
      $query->bindParam(':must_have_security', $musthavesecurity, PDO::PARAM_STR);
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $query->execute();

      // get row count
      $rowCount = $query->rowCount();

      // check if row was actually inserted, PDO exception should have caught it if not.
      if($rowCount === 0) {
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage("Failed to create loan produt");
        $response->send();
        exit;
      }

      // get last  id so we can return the  in the json
      $lastID = $writeDB->lastInsertId();
      // ADD AUTH TO QUERY

      // create db query to get newly created  - get from master db not read slave as replication may be too slow for successful read
      $query = $readDB->prepare('SELECT sloan_product_id,	name_of_loan_product,	loan_type,install_payment,
        loan_rate_type, interest_rate,loan_processing_fees,	minmum_amount,	maxmum_amount,number_of_guarantors,
          can_client_be_self_guarantor,deduct_installment_beofore_disbursment,charge_penalties,
            does_interest_change_defaulted,	new_interest_rate,must_have_security	 FROM
             loan_product_settings WHERE sloan_product_id=:id AND loan_product_saccoid = :saccoid');
      $query->bindParam(':id', $lastID, PDO::PARAM_INT);
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
        $response->addMessage("Failed to retrieve loan product after creation");
        $response->send();
        exit;
      }

        // create new  object
        $loanProductArray = array();
        // for each row returned
        // for each row returned
        while($row = $query->fetch(PDO::FETCH_ASSOC)) {
          // get  Images for given member, store them in array to pass to member
          // create new  object for each row
          $loanproduct = new LoanProductSettings(
            $row['sloan_product_id'], $row['name_of_loan_product'], $row['loan_type'], $row['install_payment'],
             $row['loan_rate_type'], $row['interest_rate'],
            $row['loan_processing_fees'], $row['minmum_amount'], $row['maxmum_amount'],
             $row['number_of_guarantors'],
            $row['can_client_be_self_guarantor'], $row['deduct_installment_beofore_disbursment'],
             $row['does_interest_change_defaulted'],$row['new_interest_rate'],$row['must_have_security'],$row['charge_penalties']);
          // create  and store in array for return in json data
    	    $loanProductArray[] = $loanproduct->returnLoanProductAsArray();
        }


        // bundle s and rows returned into an array to return in the json data
        $returnData = array();
        $returnData['rows_returned'] = $rowCount;
      $returnData['loanproductsetting'] = $loanProductArray;
      // send sms
      //set up response for successful return
      $response = new Response();
      $response->setHttpStatusCode(201);
      $response->setSuccess(true);
      $response->addMessage("Loan product created");
      $response->setData($returnData);
      $response->send();
      exit;
    }
    // if  fails to create due to data types, missing fields or invalid data then send error json
    catch(LoanProductSettingsException $ex) {
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
      $response->addMessage("Failed to upload load product data - check submitted data for errors $ex");
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
