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
  $query = $writeDB->prepare('select user_id, branches_branch_id, access_token_expiry, user_status, saccos_sacco_id, user_fullname, user_login_attempts from sessions_users, users where sessions_users.users_user_id = users.user_id and access_token = :accesstoken');
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
}
catch(PDOException $ex) {
  $response = new Response();
  $response->setHttpStatusCode(500);
  $response->setSuccess(false);
  $response->addMessage("There was an issue authenticating - please try again");
  $response->send();
  exit;
}


if (array_key_exists('guarantorid', $_GET)) {

  $guarantorid = $_GET['guarantorid'];

  if($guarantorid == '' || !is_numeric($guarantorid)) {
    $response = new Response();
    $response->setHttpStatusCode(400);
    $response->setSuccess(false);
    $response->addMessage("guarantor ID cannot be blank or must be numeric");
    $response->send();
    exit;
  }

  if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // we pick the orders name and products under that orders
    try {
      // create db query
      $query = $readDB->prepare('SELECT * FROM  members
         WHERE EXISTS(SELECT DISTINCT guarantor FROM  loan_guarantors
           WHERE loan_guarantors.guarantor =members.member_id)
           AND members.member_id=:guarantorid
         AND members.saccos_sacco_id  = :saccoid');
      $query->bindParam(':guarantorid', $guarantorid, PDO::PARAM_INT);
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $query->execute();

      $rowCount = $query->rowCount();
      if($rowCount === 0) {
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("guarantor id not found");
        $response->send();
        exit;
      }
      $guarantorArray = array();
        while($row = $query->fetch(PDO::FETCH_ASSOC)) {
          extract($row);
          $guarantors = array(
            "id" => $member_id,
            "account" => $members_account_number,
            "firstname" => $member_fname,
            "midlename" => $member_mname,
            "lastname" => $member_lname,
        );
        $loanquery = $readDB->prepare('SELECT * from members,loan_guarantors,loan_applications,
          loan_product_settings, loans_disbursed
          where members.member_id= loan_applications.members_member_id
          AND loan_guarantors.guarantor = :memberid
          -- AND loan_guarantors.howtoguarantee=:howtoguarantee
          AND loan_applications.loan_product_product_id=loan_product_settings.sloan_product_id
          AND loans_disbursed.loan_applications_loan_app_id =loan_applications.loan_app_id
          AND loan_applications.loan_app_id=loan_guarantors.loans_app_id
          AND loans_disbursed.loan_status="open" OR loans_disbursed.loan_status="rescheduled"
          OR loans_disbursed.loan_status="rolledover"
          AND loan_applications.saccos_sacco_id = :saccoid
          ORDER BY loan_app_id DESC');
          // $loanquery->bindParam(':howtoguarantee', $howtoguarantee, PDO::PARAM_INT);
          $loanquery->bindParam(':memberid', $member_id, PDO::PARAM_INT);
          $loanquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
          $loanquery->execute();
          $rowcounts=$loanquery->rowCount();
          $amountguaranted=0;
          $activeLoansArray = array();
          $_activeLoansArray = array();

          while($row = $loanquery->fetch(PDO::FETCH_ASSOC)):
            extract($row);
            switch ($howtoguarantee) {
              case 'savings':
              $loan_app = array(
                "id" => $loan_app_id,
                "loanid" => $loan_app_number,
                "account" => $members_account_number,
                "firstname" => $member_fname,
                "midlename" => $member_mname,
                "lastname" => $member_lname,
                "loanproduct" => $name_of_loan_product,
                "amountappliedfor" => $offered_amount,
                "amountoffered" => $offered_amount,
                "amornitization"=>$amornitization_interval,
                "datedisbursed"=>$loan_disbursed_date,
                "totalloanpay" => $loan_balance,
                "totalinterest" => $total_interest,
                "loantype" => $loan_type,
                "status" => $loan_app_status,
                "timestamp" => $loan_app_date,
                "date"=> $loan_app_date
              );
              $amountguaranted +=$amount_to_guarantee;
              $activeLoansArray[] = $loan_app;
              $guarantors['activeloanswithsavings']=$activeLoansArray;
              $guarantors['amountguaranted']=$amountguaranted;
              break;
              case 'collateral':
              $loan_app = array(
                "id" => $loan_app_id,
                "loanid" => $loan_app_number,
                "account" => $members_account_number,
                "firstname" => $member_fname,
                "midlename" => $member_mname,
                "lastname" => $member_lname,
                "loanproduct" => $name_of_loan_product,
                "amountappliedfor" => $offered_amount,
                "amountoffered" => $offered_amount,
                "amornitization"=>$amornitization_interval,
                "datedisbursed"=>$loan_disbursed_date,
                "totalloanpay" => $loan_balance,
                "totalinterest" => $total_interest,
                "loantype" => $loan_type,
                "status" => $loan_app_status,
                "timestamp" => $loan_app_date,
                "date"=> $loan_app_date
              );
              $cquery = $readDB->prepare('SELECT * FROM  collaterals_tb
                WHERE registration_serial_no=:registrationno
                AND collateral_member_id=:memberid
                AND collaterals_tb.collateral_saccoid  = :saccoid');
                $cquery->bindParam(':memberid', $guarantorid, PDO::PARAM_INT);
                $cquery->bindParam(':registrationno', $loan_collateral, PDO::PARAM_INT);
                $cquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
                $cquery->execute();
                $cquery->rowCount();
                while ($a= $cquery->fetch(PDO::FETCH_ASSOC)):
                  extract($a);
                  $collaterals=array("id"=>$collateralid,"collateral"=>$collateral_name, "registrationno"=>$registration_serial_no,"collateralvalue"=>$collateral_value);
                  $collateralArray[] = $collaterals;
                endwhile;
                $_activeLoansArray[] = $loan_app;
                $guarantors['activeloanswithcollateral']=$_activeLoansArray;
                $guarantors['collaterals']=$collateralArray;
            break;
            default:
            break;
            }
        endwhile;
        $guarantorArray[] = $guarantors;
        }

      $returnData = array();
      $returnData['rows_returned'] = $rowCount;
      $returnData['loanguarantor'] = $guarantorArray;
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
      $response->addMessage("failed to get loan guarantor $ex");
      $response->send();
      exit;
    }
  }
  elseif($_SERVER['REQUEST_METHOD'] === 'DELETE'){
    try {
      // ADD AUTH TO QUERY
      $query = $writeDB->prepare('DELETE from loan_guarantors where loan_guarantor_id  =:guarantorid
        AND loan_guarantor_saccoid  = :saccoid');
      $query->bindParam(':guarantorid', $guarantorid, PDO::PARAM_INT);
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_STR);
      $query->execute();

      // get row count
      $rowCount = $query->rowCount();

      if($rowCount === 0):
              // set up response for unsuccessful return
              $response = new Response();
              $response->setHttpStatusCode(404);
              $response->setSuccess(false);
              $response->addMessage("sacco guarantor not found");
              $response->send();
              exit;
      else:
              $response = new Response();
              $response->setHttpStatusCode(200);
              $response->setSuccess(true);
              $response->addMessage("sacco loan guarantor deleted");
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
      $response->addMessage("Failed to delete loan guarantor - Attached Info");
      $response->send();
      exit;
    }

  }
  elseif($_SERVER['REQUEST_METHOD'] === 'PATCH'){
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

        $query = $readDB->prepare('SELECT `member_id` as guarantorss,`member_fname`, `member_mname`,
           `member_lname`, `member_contact`, `members_account_number`,
           `loan_guarantor_saccoid`,`loans_app_id`
           FROM members,loan_guarantors, loan_product_settings
           WHERE members.member_id=loan_guarantors.guarantor
           AND loan_guarantors.loan_guarantor_saccoid = :saccoid');
        $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
        $query->execute();
        $rowsCount = $query->rowCount();

        $guarantorsArray = array();
          while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $guarantors = array(
              "id" => $guarantorss,
              "firstname" => $member_fname,
              "midlename" => $member_mname,
              "lastname" => $member_lname,
              "contact" => $member_contact,
              "accountnumber" => $members_account_number,
          );
          $loanguaranteedquery = $writeDB->prepare('SELECT  loan_applications.loan_app_id,
            member_id,member_fname, member_mname,member_lname, member_contact, members_account_number,
             name_of_loan_product,loan_app_amount,offered_amount,howtoguarantee,
            loan_collateral, amount_to_guarantee  from members,loan_guarantors,
          loan_applications,loan_product_settings
          WHERE  members.member_id=loan_applications.members_member_id
          AND loan_applications.loan_product_product_id=loan_product_settings.sloan_product_id
          AND loan_applications.loan_app_id=loan_guarantors.loans_app_id
          AND loan_applications.saccos_sacco_id  = :saccoid
          AND guarantor=:guarantors
          AND members.member_id=:guarantorr
          AND loan_applications.loan_app_id=:loanappid');
          $loanguaranteedquery->bindParam(':guarantors', $guarantorss, PDO::PARAM_STR);
          $loanguaranteedquery->bindParam(':guarantorr', $guarantorss, PDO::PARAM_STR);
          $loanguaranteedquery->bindParam(':loanappid', $loans_app_id, PDO::PARAM_STR);
          $loanguaranteedquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_STR);
          $loanguaranteedquery->execute();
          $loanguaranteedArray = array();
            while($row = $loanguaranteedquery->fetch(PDO::FETCH_ASSOC)) {
              extract($row);
              if ($howtoguarantee ==='collateral') {
              $collateralquery = $readDB->prepare('SELECT * from collaterals_tb
                where registration_serial_no = :serialnumber AND
              collateral_saccoid=:saccoid');
              $collateralquery->bindParam(':serialnumber', $loan_collateral, PDO::PARAM_INT);
              $collateralquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
              $collateralquery->execute();
              $crow= $collateralquery->fetch(PDO::FETCH_ASSOC);
              $cname=$crow['collateral_name'];
              $referenceno=$crow['registration_serial_no'];

              }
              $gwith= (int)$amount_to_guarantee;
              $amountgwith='';
              if ($gwith !==0) {
              $amountgwith  =$amount_to_guarantee;
              }
              $loanapp = array(
                "id" => $loan_app_id,
                "firstname" => $member_fname,
                "midlename" => $member_mname,
                "lastname" => $member_lname,
                "contact" => $member_contact,
                "accountnumber" => $members_account_number,
                "loanproduct" => $name_of_loan_product,
                "amountapplied" => $loan_app_amount,
                "guaranteedwith"=>$amountgwith,
                "collateral"=>$cname,
                "referenceno"=>$referenceno,
                "offered_amount"=>$offered_amount
            );
              $loanguaranteedArray[] = $loanapp;
            }
            $guarantors['loanapplications']=$loanguaranteedArray;
            $guarantorsArray[] = $guarantors;
          }

        $returnData = array();
        $returnData['rows_returned'] = $rowsCount;
        $returnData['guarantors'] = $guarantorsArray;
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
        // check to make sure that sacco name email and password are not empty and less than 255 long
        if(!isset($jsonData->application) || empty($jsonData->application)|| !is_numeric($jsonData->application)
        || !isset($jsonData->memberid) || empty($jsonData->memberid) || !is_numeric($jsonData->memberid)
        || !isset($jsonData->guarantingway) || empty($jsonData->guarantingway)):
          $response = new Response();
          $response->setHttpStatusCode(400);
          $response->setSuccess(false);
          (!isset($jsonData->application)? $response->addMessage("application field is mandatory and must be provided") : false);
          (empty($jsonData->application)? $response->addMessage("application field cannot be blank") : false);
          (!is_numeric($jsonData->application)? $response->addMessage("application field must be numeric") : false);
          (!isset($jsonData->memberid)? $response->addMessage("loan guarantor field is mandatory and must be provided") : false);
          (empty($jsonData->memberid)? $response->addMessage("loan guarantor field cannot be blank") : false);
          (!is_numeric($jsonData->memberid)? $response->addMessage("loan guarantor field must be numeric") : false);
          (!isset($jsonData->guarantingway)? $response->addMessage("guarantingway field is mandatory and must be provided") : false);
          (empty($jsonData->guarantingway)? $response->addMessage("guarantingway field cannot be blank") : false);
          $response->send();
          exit;
        endif;
        if ($jsonData->guarantingway ==='savings'):
          if (!isset($jsonData->amounttoguarant) || empty($jsonData->amounttoguarant) || !is_numeric($jsonData->amounttoguarant)) {
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            (!isset($jsonData->amounttoguarant)? $response->addMessage("amount to guarant field is mandatory and must be provided") : false);
            (empty($jsonData->amounttoguarant)? $response->addMessage("amount to guarant field cannot be blank") : false);
            (!is_numeric($jsonData->amounttoguarant)? $response->addMessage("amount to guarant field must be numeric") : false);
            $response->send();
            exit;
          }
      endif;

      if ($jsonData->guarantingway ==='collateral'):
        if (!isset($jsonData->collateral) || empty($jsonData->collateral)) {
          $response = new Response();
          $response->setHttpStatusCode(400);
          $response->setSuccess(false);
          (!isset($jsonData->collateral)? $response->addMessage("collateral field is mandatory and must be provided") : false);
          (empty($jsonData->collateral)? $response->addMessage("collateral field cannot be blank") : false);
          $response->send();
          exit;
        }
    endif;
        try{
          // $lastID=0;
          $_guarantor = (int)$jsonData->memberid;
          $_application = (int)$jsonData->application;
          $_guarantingway = trim($jsonData->guarantingway);
          $_amount = (int)$jsonData->amounttoguarant;
          $_serialnumber = $jsonData->collateral;
          $gquery = $readDB->prepare('SELECT COUNT(*) as totalnumber from loan_guarantors
            WHERE loans_app_id = :appid AND loan_guarantor_saccoid  =:saccoid');
          $gquery->bindParam(':appid', $_application, PDO::PARAM_INT);
          $gquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
          $gquery->execute();

          $grow = $gquery->fetch(PDO::FETCH_ASSOC);
          extract($grow);
          // echo $totalnumber;

          $loanpquery = $readDB->prepare('SELECT * from loan_applications,loan_product_settings
            WHERE loan_app_id = :appid
            AND sloan_product_id=loan_product_product_id
            AND saccos_sacco_id =:saccoid');
          $loanpquery->bindParam(':appid', $_application, PDO::PARAM_INT);
          $loanpquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
          $loanpquery->execute();

          $prow = $loanpquery->fetch(PDO::FETCH_ASSOC);
          extract($prow);
          echo $number_of_guarantors;

          if ($number_of_guarantors ===$totalnumber) {
            // code...
            $response = new Response();
            $response->setHttpStatusCode(429);
            $response->setSuccess(false);
            $response->addMessage("you have reached the required number of guarantors");
            $response->send();
            exit;
          }


          if ($jsonData->guarantingway ==='collateral'):
            $collateralquery = $readDB->prepare('SELECT * from collaterals_tb
              WHERE registration_serial_no = :collateral AND collateral_saccoid=:saccoid');
            $collateralquery->bindParam(':collateral', $_serialnumber, PDO::PARAM_INT);
            $collateralquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
            $collateralquery->execute();
            $crowCount = $collateralquery->rowCount();

            if ($crowCount == 0) {
              $response = new Response();
              $response->setHttpStatusCode(202);
              $response->setSuccess(false);
              $response->addMessage("collateral does not exist");
              $response->send();
              exit;
            }
        endif;
          $query = $readDB->prepare('SELECT * from loan_guarantors where guarantor = :guarantor AND loans_app_id=:application');
          $query->bindParam(':guarantor', $_guarantor, PDO::PARAM_INT);
          $query->bindParam(':application', $_application, PDO::PARAM_INT);
          $query->execute();
          $rowCount = $query->rowCount();

          if ($rowCount !== 0) {
            $response = new Response();
            $response->setHttpStatusCode(409);
            $response->setSuccess(false);
            $response->addMessage("guarantor for this loan application already exists");
            $response->send();
            exit;
          }
          $_date =date('Y-m-d H:i:s');
            $query = $writeDB->prepare('INSERT into loan_guarantors(
            `loans_app_id`,
            `guarantor`,
            `howtoguarantee`,
            `amount_to_guarantee`,
            `loan_collateral`,
            `guaranting_date`,
            `loan_guarantor_saccoid`)
            values (:application,:member, :way,:amount,:collateral,:datee, :saccoid)');
            $query->bindParam(':application', $_application, PDO::PARAM_STR);
            $query->bindParam(':member', $_guarantor, PDO::PARAM_STR);
            $query->bindParam(':way', $_guarantingway, PDO::PARAM_STR);
            $query->bindParam(':amount', $_amount, PDO::PARAM_STR);
            $query->bindParam(':collateral', $_serialnumber, PDO::PARAM_STR);
            $query->bindParam(':datee', $_date, PDO::PARAM_STR);
            $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
            $query->execute();
            $lastID = $writeDB->lastInsertId();
            $rowCount = $query->rowCount();
            if ($rowCount === 0) {
              $response = new Response();
              $response->setHttpStatusCode(500);
              $response->setSuccess(false);
              $response->addMessage("internal server error");
              $response->send();
              exit;
            }

            $query = $writeDB->prepare('SELECT * from loan_guarantors where   loan_guarantor_id  = :id');
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
          $guarantorArray = array();
            while($row = $query->fetch(PDO::FETCH_ASSOC)) {
              extract($row);
              $guarantor = array(
                "id" => $loan_guarantor_id,
                "loan_application" => $loans_app_id,
                "guarantor " => $guarantor ,
                "guarantingway" => $howtoguarantee,
                "amount" => $amount_to_guarantee,
                "loancollateral" => $loan_collateral,
                "guarantingdate" => $guaranting_date
            );
              $guarantorArray[] = $guarantor;
            }
            // bundle branch and rows returned into an array to return in the json data
            $returnData = array();
            $returnData['rows_returned'] = $rowCount;
            $returnData['loan_guarantor'] = $guarantorArray;

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

  }
else {
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
