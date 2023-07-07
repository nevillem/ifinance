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


  if (array_key_exists('datebudget', $_GET)) {

    $datebudget = $_GET['datebudget'];

    if($datebudget == '' || validateDate($datebudget)== false) {
      $response = new Response();
      $response->setHttpStatusCode(400);
      $response->setSuccess(false);
      $response->addMessage("search date cannot be blank or must be date");
      $response->send();
      exit;
    }
    $explode= explode("-", $datebudget);
    $dateex= $explode[0];

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
      // we pick the orders name and products under that orders
      try {
        // create db query
        $budgetquery = $writeDB->prepare('SELECT `bdgetid`,`account_name`,`budget_account_id`, `date_budgetted`, `jan`, `feb`, `mar`, `apr`,
           `may`, `jun`, `jul`, `aug`, `sept`, `oct`, `nov`, `dec_`,
           SUM(IFNULL(jan,0)+IFNULL(feb,0)+IFNULL(mar,0)+IFNULL(apr,0)
           +IFNULL(may,0)+IFNULL(jun,0)+IFNULL(jul,0) +IFNULL(aug,0)) AS DLR_TOT_AMT
           FROM budgeting_tb, accounts
           WHERE YEAR(date_budgetted)=:dates  AND accounts.accounts_id= budgeting_tb.budget_account_id
           AND budget_sacco_id = :saccoid GROUP BY bdgetid');
        $budgetquery->bindParam(':dates', $dateex, PDO::PARAM_STR);
        $budgetquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_STR);
        $budgetquery->execute();

        $rowCount = $budgetquery->rowCount();
        if($rowCount === 0) {
          // set up response for unsuccessful return
          $response = new Response();
          $response->setHttpStatusCode(404);
          $response->setSuccess(false);
          $response->addMessage("sacco budget year not found");
          $response->send();
          exit;
        }
        $budgetArray = array();
          while($row = $budgetquery->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $datee = $date_budgetted;
            $exp= explode("-", $date_budgetted);
            $budgets = array(
              "id" => $bdgetid,
              "account" => $account_name,
              "year" => $exp[0],
              "total" => $DLR_TOT_AMT,
              "january" => $jan,
              "febuary" => $feb,
              "march" => $mar,
              "april" => $apr,
              "may" => $may,
              "june" => $jun,
              "july" => $jul,
              "august" => $aug,
              "september" => $sept,
              "october" => $oct,
              "november" => $nov,
              "december" => $dec_,
          );
            $budgetArray[] = $budgets;
          }

        $returnData = array();
        $returnData['rows_returned'] = $rowCount;
        $returnData['budget'] = $budgetArray;
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
        $response->addMessage("failed to get sacco budget");
        $response->send();
        exit;
      }

    }
    elseif($_SERVER['REQUEST_METHOD'] === 'DELETE'){
      try {
        // ADD AUTH TO QUERY
        $query = $writeDB->prepare('DELETE from budgeting_tb where YEAR(date_budgetted) =:dates
          AND budget_sacco_id  = :saccoid');
        $query->bindParam(':dates', $dateex, PDO::PARAM_STR);
        $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_STR);
        $query->execute();
        // get row count
        $rowCount = $query->rowCount();

        if($rowCount === 0):
                // set up response for unsuccessful return
                $response = new Response();
                $response->setHttpStatusCode(404);
                $response->setSuccess(false);
                $response->addMessage("sacco budget not found");
                $response->send();
                exit;
        else:
                $response = new Response();
                $response->setHttpStatusCode(200);
                $response->setSuccess(true);
                $response->addMessage("sacco budget deleted");
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
        $response->addMessage("Failed to delete sacco paid bill - Attached Info");
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

  } elseif(empty($_GET)){
        // get the user profile data
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {

        try {
          $query = $writeDB->prepare('SELECT DISTINCT YEAR(`date_budgetted`) as years
             FROM budgeting_tb WHERE  budget_sacco_id = :saccoid');
          $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_STR);
          $query->execute();
          $rowsCount = $query->rowCount();
          $yearsArray = array();
          while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
          $date=  array('years' =>$years);
          $budgetquery = $writeDB->prepare('SELECT `bdgetid`,`account_name`,`budget_account_id`, `date_budgetted`, `jan`, `feb`, `mar`, `apr`,
             `may`, `jun`, `jul`, `aug`, `sept`, `oct`, `nov`, `dec_`,
             SUM(IFNULL(jan,0)+IFNULL(feb,0)+IFNULL(mar,0)+IFNULL(apr,0)
             +IFNULL(may,0)+IFNULL(jun,0)+IFNULL(jul,0) +IFNULL(aug,0)) AS DLR_TOT_AMT
             FROM budgeting_tb, accounts
             WHERE YEAR(date_budgetted)=:years  AND accounts.accounts_id= budgeting_tb.budget_account_id
             AND budget_sacco_id = :saccoid GROUP BY bdgetid');
          $budgetquery->bindParam(':years', $years, PDO::PARAM_STR);
          $budgetquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_STR);
          $budgetquery->execute();
          // $rowsCount = $budgetquery->rowCount();
          $budgetArray = array();
            while($row = $budgetquery->fetch(PDO::FETCH_ASSOC)) {
              extract($row);
              $datee = $date_budgetted;
              $exp= explode("-", $date_budgetted);
              $budgets = array(
                "id" => $bdgetid,
                "account" => $account_name,
                "year" => $exp[0],
                "total" => $DLR_TOT_AMT,
                "january" => $jan,
                "febuary" => $feb,
                "march" => $mar,
                "april" => $apr,
                "may" => $may,
                "june" => $jun,
                "july" => $jul,
                "august" => $aug,
                "september" => $sept,
                "october" => $oct,
                "november" => $nov,
                "december" => $dec_,
            );
              $budgetArray[] = $budgets;
            }
          $date['budgets'] = $budgetArray;
          $yearsArray[]=$date;
          }
          $returnData = array();
          $returnData['rows_returned'] = $rowsCount;
          $returnData['budgetyears'] = $yearsArray;
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
          if(!isset($jsonData->account) || empty($jsonData->account)|| !is_array($jsonData->account)
          || !isset($jsonData->datebudgeted) || empty($jsonData->datebudgeted)
          || !isset($jsonData->jan) || empty($jsonData->jan)|| !is_array($jsonData->jan)
          || !isset($jsonData->feb) || empty($jsonData->feb) || !is_array($jsonData->feb)
          || !isset($jsonData->mar) || empty($jsonData->mar) || !is_array($jsonData->mar)
          || !isset($jsonData->apr) || empty($jsonData->apr) || !is_array($jsonData->apr)
          || !isset($jsonData->may) || empty($jsonData->may) || !is_array($jsonData->may)
          || !isset($jsonData->jun) || empty($jsonData->jun) || !is_array($jsonData->jun)
          || !isset($jsonData->jul) || empty($jsonData->jul) || !is_array($jsonData->jul)
          || !isset($jsonData->aug) || empty($jsonData->aug) || !is_array($jsonData->aug)
          || !isset($jsonData->sept) || empty($jsonData->sept) || !is_array($jsonData->sept)
          || !isset($jsonData->oct) || empty($jsonData->oct) || !is_array($jsonData->oct)
          || !isset($jsonData->nov) || empty($jsonData->nov) || !is_array($jsonData->nov)
          || !isset($jsonData->dec_) || empty($jsonData->dec_) || !is_array($jsonData->dec_)
          ):

            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            (!isset($jsonData->account)? $response->addMessage("account  field is mandatory and must be provided") : false);
            (empty($jsonData->account)? $response->addMessage("account field cannot be blank") : false);
            (!is_array($jsonData->account)? $response->addMessage("account to spend from field must be array") : false);
            (!isset($jsonData->datebudgeted)? $response->addMessage("date field is mandatory and must be provided") : false);
            (empty($jsonData->datebudgeted)? $response->addMessage(" date field cannot be blank") : false);
            (!isset($jsonData->jan)? $response->addMessage("january field is mandatory and must be provided") : false);
            (empty($jsonData->jan)? $response->addMessage("january field cannot be blank") : false);
            (!is_array($jsonData->jan)? $response->addMessage("january field must be array") : false);
            (!isset($jsonData->feb)? $response->addMessage("febuary field is mandatory and must be provided") : false);
            (empty($jsonData->feb)? $response->addMessage("febuary field cannot be blank") : false);
            (!is_array($jsonData->feb)? $response->addMessage("febuary field must be array") : false);
            (!isset($jsonData->mar)? $response->addMessage("march field is mandatory and must be provided") : false);
            (empty($jsonData->mar)? $response->addMessage("march field cannot be blank") : false);
            (!is_array($jsonData->mar)? $response->addMessage("march field must be array") : false);
            (!isset($jsonData->apr)? $response->addMessage("april field is mandatory and must be provided") : false);
            (empty($jsonData->apr)? $response->addMessage("april field cannot be blank") : false);
            (!is_array($jsonData->apr)? $response->addMessage("april field must be array") : false);
            (!isset($jsonData->may)? $response->addMessage("may field is mandatory and must be provided") : false);
            (empty($jsonData->may)? $response->addMessage("may field cannot be blank") : false);
            (!is_array($jsonData->may)? $response->addMessage("may field must be array") : false);
            (!isset($jsonData->jun)? $response->addMessage("june field is mandatory and must be provided") : false);
            (empty($jsonData->jun)? $response->addMessage("june field cannot be blank") : false);
            (!is_array($jsonData->jun)? $response->addMessage("june field must be array") : false);
            (!isset($jsonData->jul)? $response->addMessage("july field is mandatory and must be provided") : false);
            (empty($jsonData->jul)? $response->addMessage("july field cannot be blank") : false);
            (!is_array($jsonData->jul)? $response->addMessage("july field must be array") : false);
            (!isset($jsonData->aug)? $response->addMessage("august field is mandatory and must be provided") : false);
            (empty($jsonData->aug)? $response->addMessage("august field cannot be blank") : false);
            (!is_array($jsonData->aug)? $response->addMessage("august field must be array") : false);
            (!isset($jsonData->sept)? $response->addMessage("september field is mandatory and must be provided") : false);
            (empty($jsonData->sept)? $response->addMessage("september field cannot be blank") : false);
            (!is_array($jsonData->sept)? $response->addMessage("september field must be array") : false);
            (!isset($jsonData->oct)? $response->addMessage("october field is mandatory and must be provided") : false);
            (empty($jsonData->oct)? $response->addMessage("october field cannot be blank") : false);
            (!is_array($jsonData->oct)? $response->addMessage("october field must be array") : false);
            (!isset($jsonData->nov)? $response->addMessage("november field is mandatory and must be provided") : false);
            (empty($jsonData->nov)? $response->addMessage("november field cannot be blank") : false);
            (!is_array($jsonData->nov)? $response->addMessage("november field must be array") : false);
            (!isset($jsonData->dec_)? $response->addMessage("december field is mandatory and must be provided") : false);
            (empty($jsonData->dec_)? $response->addMessage("december field cannot be blank") : false);
            (!is_array($jsonData->dec_)? $response->addMessage("december field must be an array") : false);
            $response->send();
            exit;
          endif;


          if (!arrayHasOnlyInts($jsonData->account) || !arrayHasOnlyInts($jsonData->jan)|| !arrayHasOnlyInts($jsonData->feb)
          || !arrayHasOnlyInts($jsonData->mar) || !arrayHasOnlyInts($jsonData->apr)|| !arrayHasOnlyInts($jsonData->may)
          || !arrayHasOnlyInts($jsonData->jun) || !arrayHasOnlyInts($jsonData->jul)|| !arrayHasOnlyInts($jsonData->aug)
          || !arrayHasOnlyInts($jsonData->sept)|| !arrayHasOnlyInts($jsonData->oct) ||
           !arrayHasOnlyInts($jsonData->nov)|| !arrayHasOnlyInts($jsonData->dec_)
          ):
            // code...
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            (!arrayHasOnlyInts($jsonData->account)? $response->addMessage("accounts, only numerical data allowed") : false);
            (!arrayHasOnlyInts($jsonData->jan)? $response->addMessage("january, only numerical data allowed") : false);
            (!arrayHasOnlyInts($jsonData->feb)? $response->addMessage("febuary,only numerical data allowed") : false);
            (!arrayHasOnlyInts($jsonData->mar)? $response->addMessage("march,only numerical data allowed") : false);
            (!arrayHasOnlyInts($jsonData->apr)? $response->addMessage("april,only numerical data allowed") : false);
            (!arrayHasOnlyInts($jsonData->may)? $response->addMessage("may,only numerical data allowed") : false);
            (!arrayHasOnlyInts($jsonData->jun)? $response->addMessage("jun,only numerical data allowed") : false);
            (!arrayHasOnlyInts($jsonData->jul)? $response->addMessage("jully,only numerical data allowed") : false);
            (!arrayHasOnlyInts($jsonData->aug)? $response->addMessage("august, only numerical data allowed") : false);
            (!arrayHasOnlyInts($jsonData->sept)? $response->addMessage("september,only numerical data allowed") : false);
            (!arrayHasOnlyInts($jsonData->oct)? $response->addMessage("october,only numerical data allowed") : false);
            (!arrayHasOnlyInts($jsonData->nov)? $response->addMessage("november,only numerical data allowed") : false);
            (!arrayHasOnlyInts($jsonData->dec_)? $response->addMessage("december, only numerical data allowed") : false);
            $response->send();
            exit;
          endif;

          try{
            // $rowCount=0;
            $_account=$jsonData->account;
            $_date=$jsonData->datebudgeted;
            $_jan=$jsonData->jan;
            $_feb=$jsonData->feb;
            $_mar=$jsonData->mar;
            $_apr = $jsonData->apr;
            $_may = $jsonData->may;
            $_jun = $jsonData->jun;
            $_jul = $jsonData->jul;
            $_aug = $jsonData->aug;
            $_sept = $jsonData->sept;
            $_oct = $jsonData->oct;
            $_nov = $jsonData->nov;
            $_dec = $jsonData->dec_;
            $datef= explode("-", $_date);
            $dated= $datef[0];
            $pattern = '%' . $dated . '%';


           try{
           $query = $writeDB->prepare('SELECT * FROM budgeting_tb WHERE budget_account_id =:account AND YEAR(date_budgetted) = :datee
            AND budget_sacco_id=:saccoid');
           foreach ($_account as $i => $n){
           $narray= array(':account'=> $n,
                             'datee'=>$dated,
                             ':saccoid'=>$returned_saccoid
                           );
           $query->execute($narray);
           }

           $rowCount= $query->rowCount();
           if ($rowCount ==0) {
             // code...
             // $writeDB->beginTransaction();
            $budgetquery = $writeDB->prepare('INSERT INTO budgeting_tb (`budget_account_id`, `date_budgetted`,
              `jan`, `feb`, `mar`, `apr`, `may`, `jun`, `jul`, `aug`,
              `sept`,`oct`, `nov`, `dec_`, `budget_sacco_id`)
            VALUES (:account,:dates,:jan,:feb,:mar,:apr,:may,:jun,:jul,:aug,:sept,:oct,:nov,:decc,:saccoid)');
            foreach ($_account as $i => $n){
            $narray= array(':account'=> $n,
                              'dates'=>$_date,
                              ':jan'=>$_jan[$i],
                              ':feb'=>$_feb[$i],
                              ':mar'=>$_mar[$i],
                              ':apr'=>$_apr[$i],
                              ':may'=>$_may[$i],
                              ':jun'=>$_jun[$i],
                              ':jul'=>$_jul[$i],
                              ':aug'=>$_aug[$i],
                              ':sept'=>$_sept[$i],
                              ':oct'=>$_oct[$i],
                              ':nov'=>$_nov[$i],
                              ':decc'=>$_dec[$i],
                              ':saccoid'=>$returned_saccoid
                            );
                $budgetquery->execute($narray);
            }
            $rowCounts= $budgetquery->rowCount();

            // bundle branch and rows returned into an array to return in the json data
            $returnData = array();
            $returnData['rows_returned'] = $rowCounts;
            // set up response for successful return
            $response = new Response();
            $response->setHttpStatusCode(201);
            $response->setSuccess(true);
            $response->setData($returnData);
            $response->addMessage("data saved successfully");
            $response->send();
            exit;
           }
           else {
             // code... update
             $budgetupdatequery = $writeDB->prepare('UPDATE  budgeting_tb SET budget_account_id=:account,
                date_budgetted=:dates, jan=:jan, feb=:feb, mar=:mar, apr=:apr, may=:may, jun=:jun, jul=:jul, aug=:aug,
               sept=:sept, oct=:oct, nov=:nov, dec_=:decc WHERE `budget_sacco_id`=:saccoid
               AND YEAR(date_budgetted) = :dateee AND budget_account_id=:accounts
               ');
             foreach ($_account as $i => $n){
             $narray= array(':account'=> $n,':accounts'=> $n,
                               'dates'=>$_date,
                               ':jan'=>$_jan[$i],
                               ':feb'=>$_feb[$i],
                               ':mar'=>$_mar[$i],
                               ':apr'=>$_apr[$i],
                               ':may'=>$_may[$i],
                               ':jun'=>$_jun[$i],
                               ':jul'=>$_jul[$i],
                               ':aug'=>$_aug[$i],
                               ':sept'=>$_sept[$i],
                               ':oct'=>$_oct[$i],
                               ':nov'=>$_nov[$i],
                               ':decc'=>$_dec[$i],
                               ':dateee'=>$dated,
                               ':saccoid'=>$returned_saccoid
                             );
                 $budgetupdatequery->execute($narray);
             }
             $rowCounts= $budgetupdatequery->rowCount();

             // bundle branch and rows returned into an array to return in the json data
             $returnData = array();
             // $returnData['rows_returned'] = $rowCounts;
             $response = new Response();
             $response->setHttpStatusCode(201);
             $response->setSuccess(true);
             $response->setData($returnData);
             $response->addMessage("data update successfully");
             $response->send();
             exit;

           }

            //commit the change
            // $writeDB->commit();
            }
            catch (PDOException $ex) {
              // $writeDB->rollBack();
              $response = new Response();
              $response->setHttpStatusCode(500);
              $response->setSuccess(false);
              $response->addMessage("There was an issue making the transaction".$ex);
              $response->send();
              exit;
            }

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
