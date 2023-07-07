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

  if (array_key_exists('loanrolloverid', $_GET)) {

    $loanrolloverid = $_GET['loanrolloverid'];

    if($loanrolloverid == '' || !is_numeric($loanrolloverid)) {
      $response = new Response();
      $response->setHttpStatusCode(400);
      $response->setSuccess(false);
      $response->addMessage("loan rollover ID cannot be blank or must be numeric");
      $response->send();
      exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
      // we pick the orders name and products under that orders
      try {
        // create db query
          // create  and store in array for return in json data
          $loanrolloverquery = $writeDB->prepare('SELECT `loan_rollover_setting_id`, `name_of_loan_product`,
             `fees_calculation_mode`, `fees_charged`, `where_to_charge`
            from loan_rollover_setting, loan_product_settings
            WHERE loan_rollover_setting_id=:loanrolloverid
            AND loan_product=sloan_product_id
            AND  loan_rollover_saccoid = :saccoid');
            $loanrolloverquery->bindParam(':loanrolloverid', $loanrolloverid, PDO::PARAM_STR);
            $loanrolloverquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_STR);
            $loanrolloverquery->execute();

        $loanrolloversettingArray=array();
        while($loanrolloverrow = $loanrolloverquery->fetch(PDO::FETCH_ASSOC)) {
          extract($loanrolloverrow);
          $loan_rollover_setting = array(
            "id" => $loan_rollover_setting_id ,
            "loan_product" => $name_of_loan_product,
            "fees_calculation_mode" => $fees_calculation_mode,
            "fees_charged" => $fees_charged,
            "where_to_charge" => $where_to_charge,
          );
        $loanrolloversettingArray[] = $loan_rollover_setting;
        }

        $returnData = array();
        $returnData['rows_returned'] = $rowCount;
        $returnData['loan_rollover_setting'] = $loanrolloversettingArray;
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
        $response->addMessage("failed to get loan rollover setting");
        $response->send();
        exit;
      }

    } elseif($_SERVER['REQUEST_METHOD'] === 'DELETE'){
      try {
        // ADD AUTH TO QUERY

        $query = $writeDB->prepare('DELETE from  loan_rollover_setting
          where  loan_rollover_setting_id = :loanrolloverid
        AND loan_rollover_saccoid  = :saccoid');
        $query->bindParam(':loanrolloverid', $loanrolloverid, PDO::PARAM_STR);
        $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
        $query->execute();

        // get row count
        $rowCount = $query->rowCount();

        if($rowCount === 0):
                // set up response for unsuccessful return
                $response = new Response();
                $response->setHttpStatusCode(404);
                $response->setSuccess(false);
                $response->addMessage("loan rollover setting  not found");
                $response->send();
                exit;
        else:
                $response = new Response();
                $response->setHttpStatusCode(200);
                $response->setSuccess(true);
                $response->addMessage("loan rollover setting  deleted");
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
        $response->addMessage("Failed to delete loan rollover setting - Attached Info");
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

  } elseif(empty($_GET)){
        // get the user profile data
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {

        try {
          $query = $readDB->prepare('SELECT sloan_product_id,	name_of_loan_product,	loan_type,install_payment,loan_rate_type,
            interest_rate,loan_processing_fees,	minmum_amount,	maxmum_amount,number_of_guarantors,
              penalties,	can_client_be_self_guarantor,deduct_installment_beofore_disbursment,
                does_interest_change_defaulted,	new_interest_rate,must_have_security
                	 from loan_product_settings WHERE  loan_product_saccoid = :saccoid');
          // $query->bindParam(':loanpid', $loanproduct, PDO::PARAM_INT);
          $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
          $query->execute();

          // get row count
          $rowCount = $query->rowCount();

          // create  array to store returned
          $loanProductArray = array();

          while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $loanproduct = array(
            "id"=>$sloan_product_id,
            "product"=>$name_of_loan_product,
            "loan_type"=>$loan_type,
            "install_payment"=>$install_payment,
            "loan_rate_type"=>$loan_rate_type,
            "interest_rate"=>$interest_rate,
            "loan_processing_fees"=>$loan_processing_fees,
            "minmum_amount"=>$minmum_amount,
            "maxmum_amount"=>$maxmum_amount,
            "number_of_guarantors"=>$number_of_guarantors,
            "penalties"=>$penalties,
            "canbeselftguarantor"=>$can_client_be_self_guarantor,
            "deduct_installment_beofore_disbursment"=>$deduct_installment_beofore_disbursment,
            "does_interest_change_defaulted"=>$does_interest_change_defaulted,
            "new_interest_rate"=>$new_interest_rate,
            "must_have_security"=>$must_have_security
          );
            // create  and store in array for return in json data
            $loanrolloverquery = $writeDB->prepare('SELECT *
              from loan_rollover_setting
              WHERE loan_product =:loan_product  AND  loan_rollover_saccoid = :saccoid');
              $loanrolloverquery->bindParam(':loan_product', $sloan_product_id, PDO::PARAM_STR);
              $loanrolloverquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_STR);
              $loanrolloverquery->execute();

          $loanrolloversettingArray=array();
          while($loanrolloverrow = $loanrolloverquery->fetch(PDO::FETCH_ASSOC)) {
            extract($loanrolloverrow);
            $loan_rollover_setting = array(
              "id" => $loan_rollover_setting_id ,
              "fees_calculation_mode" => $fees_calculation_mode,
              "fees_charged" => $fees_charged,
              "where_to_charge" => $where_to_charge,
          );
          $loanrolloversettingArray[] = $loan_rollover_setting;
          }

          $loanproduct["loan_rollover_setting"]=$loanrolloversettingArray;
          $loanProductArray[] = $loanproduct;
         }

          $returnData = array();
          $returnData['rows_returned'] = $rowCount;
          $returnData['loanproducts'] = $loanProductArray;
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
          if(!isset($jsonData->loan_product )||empty($jsonData->loan_product )
          ||!isset($jsonData->fees_calculation_mode)||empty($jsonData->fees_calculation_mode)
          ||!isset($jsonData->fees_charged)||empty($jsonData->fees_charged)
          ||!isset($jsonData->where_to_charge)||empty($jsonData->where_to_charge)
        ) {
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            (!isset($jsonData->loan_product ) ? $response->addMessage("loan product  is mandatory and must be provided") : false);
            (empty($jsonData->loan_product) ? $response->addMessage("loan product field must not be empty") : false);
            (!isset($jsonData->fees_calculation_mode) ? $response->addMessage("fees calculation mode  is mandatory and must be provided") : false);
            (empty($jsonData->fees_calculation_mode) ? $response->addMessage("fees calculation mode field must not be empty") : false);
            (!isset($jsonData->fees_charged) ? $response->addMessage("fees charged is mandatory and must be provided") : false);
            (empty($jsonData->fees_charged) ? $response->addMessage("fees charged field must not be empty") : false);
            (!isset($jsonData->where_to_charge) ? $response->addMessage("where to charge only is mandatory and must be provided") : false);
            (empty($jsonData->where_to_charge) ? $response->addMessage("where to charge only field must not be empty") : false);
            $response->send();
            exit;
          }

            $query = $writeDB->prepare('SELECT * from  loan_rollover_setting
              where loan_product  = :loan_product
               AND loan_rollover_saccoid =:saccoid ');
              $query->bindParam(':loan_product', $jsonData->loan_product, PDO::PARAM_STR);
              $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_STR);
            $query->execute();

            $rowCount = $query->rowCount();
            if ($rowCount > 0) {
              $response = new Response();
              $response->setHttpStatusCode(500);
              $response->setSuccess(false);
              $response->addMessage("already exists");
              $response->send();
              exit;
            }

              $query = $writeDB->prepare('INSERT INTO loan_rollover_setting(`loan_product`,
                 `fees_calculation_mode`, `fees_charged`, `where_to_charge`, `loan_rollover_saccoid`)
               values (:loan_product,:fees_calculation_mode,:fees_charged,:where_to_charge,:saccoid)');
              $query->bindParam(':loan_product', $jsonData->loan_product, PDO::PARAM_STR);
              $query->bindParam(':fees_calculation_mode', $jsonData->fees_calculation_mode, PDO::PARAM_STR);
              $query->bindParam(':fees_charged', $jsonData->fees_charged, PDO::PARAM_STR);
              $query->bindParam(':where_to_charge', $jsonData->where_to_charge, PDO::PARAM_STR);
              $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
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
              $lastID = $writeDB->lastInsertId();

              $loanrolloverquery = $writeDB->prepare('SELECT `loan_rollover_setting_id`, `name_of_loan_product`,
                 `fees_calculation_mode`, `fees_charged`, `where_to_charge`
                from loan_rollover_setting, loan_product_settings
                WHERE loan_rollover_setting_id=:id
                AND loan_product=sloan_product_id');
                $loanrolloverquery->bindParam(':id', $lastID, PDO::PARAM_STR);
                $loanrolloverquery->execute();

            $loanrolloversettingArray=array();
            while($loanrolloverrow = $loanrolloverquery->fetch(PDO::FETCH_ASSOC)) {
              extract($loanrolloverrow);
              $loan_rollover_setting = array(
                "id" => $loan_rollover_setting_id ,
                "loan_product" => $name_of_loan_product,
                "fees_calculation_mode" => $fees_calculation_mode,
                "fees_charged" => $fees_charged,
                "where_to_charge" => $where_to_charge,
              );
            $loanrolloversettingArray[] = $loan_rollover_setting;
            }

            $returnData = array();
            $returnData['rows_returned'] = $rowCount;
            $returnData['loan_rollover_setting'] = $loanrolloversettingArray;
              // set up response for successful return
              $response = new Response();
              $response->setHttpStatusCode(201);
              $response->setSuccess(true);
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
