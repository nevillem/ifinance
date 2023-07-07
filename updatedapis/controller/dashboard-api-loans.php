<?php

// make request to specific files
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
  $query = $writeDB->prepare('select user_id, access_token_expiry, user_status, saccos_sacco_id, user_fullname, user_login_attempts from sessions_users, users where sessions_users.users_user_id = users.user_id and access_token = :accesstoken');
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

    // END OF AUTH SCRIPT
    if($_SERVER['REQUEST_METHOD'] !== 'GET') {
      // attempt to make request is null
      $response = new Response();
      $response->setHttpStatusCode(405);
      $response->setSuccess(false);
      $response->addMessage("request method not allowed");
      $response->send();
      exit;
    }

  try {
    $returnData = array();
    // create db query and option select
    $query = $readDB->prepare('SELECT count(*) as loan_count,
    SUM(amount_disbursed) as loan_amount from loans_disbursed
    where users_user_id = :userid and saccos_sacco_id = :saccoid');
    $query->bindParam(':userid', $returned_id, PDO::PARAM_INT);
    $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
    $query->execute();

    // get row count
    $rowCount = $query->rowCount();
    // create array to store returned task
    $transactionArray = array();
    while($row = $query->fetch(PDO::FETCH_ASSOC)) {
      extract($row);
     $transactions = array(
       "totalloans" => $loan_amount,
       "count" => $loan_count
     );
     $transactionArray[] = $transactions;
    }
    // bundle rows returned into an array to return in the json data
    // $returnData['rows_returned'] = $rowCount;
    $returnData['totalloans'] = $transactionArray;

    // create db query and option select
    $query = $readDB->prepare('SELECT count(*) as loan_count_active,
     SUM(amount_disbursed) as loan_amount from loans_disbursed
    where users_user_id = :userid and saccos_sacco_id = :saccoid and loan_status="open"');
    $query->bindParam(':userid', $returned_id, PDO::PARAM_INT);
    $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
    $query->execute();

    // get row count
    $rowCount = $query->rowCount();
    // create array to store returned task
    $transactionArray = array();
    while($row = $query->fetch(PDO::FETCH_ASSOC)) {
      extract($row);
     $transactions = array(
       "activeloans" => $loan_amount,
       "count" => $loan_count_active
     );
     $transactionArray[] = $transactions;
    }
    // bundle rows returned into an array to return in the json data
    // $returnData['rows_returned'] = $rowCount;
    $returnData['activeloans'] = $transactionArray;

    // withdraw amount in the squence
    $query = $readDB->prepare('SELECT count(*) as loan_count_app, SUM(loan_app_amount)
    as loan_app_amount from loan_applications where users_user_id = :userid
    and saccos_sacco_id = :saccoid and loan_app_status = "pending"');
    $query->bindParam(':userid', $returned_id, PDO::PARAM_INT);
    $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
    $query->execute();

    // get row count
    $rowCount = $query->rowCount();

    // create array to store returned task
    $transactionArray = array();

    while($row = $query->fetch(PDO::FETCH_ASSOC)) {
      extract($row);
     $transactions = array(
       "totalapp" => $loan_app_amount,
       "count" => $loan_count_app
     );
     $transactionArray[] = $transactions;
    }

    // $returnData['rows_returned'] = $rowCount;
    $returnData['totalloanapp'] = $transactionArray;

    $query = $readDB->prepare('SELECT count(*) as loan_count, SUM(amount_disbursed)
     as loan_amount from loans_disbursed where users_user_id = :userid and saccos_sacco_id = :saccoid
     and loan_status = "overdue"');
    $query->bindParam(':userid', $returned_id, PDO::PARAM_INT);
    $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
    $query->execute();

    // get row count
    $rowCount = $query->rowCount();

    // create array to store returned task
    $overduetransactionArray = array();

    while($row = $query->fetch(PDO::FETCH_ASSOC)) {
      extract($row);
     $transactions = array(
       "totalapp" => $loan_amount,
       "count" => $loan_count
     );
     $overduetransactionArray[] = $transactions;
    }

    // $returnData['rows_returned'] = $rowCount;
    $returnData['overtotalloanapp'] = $overduetransactionArray;

    $memberArray = array();
    // members amount in the squence
    $query = $readDB->prepare('SELECT sum(member_type = "group") as groups ,
     sum(member_type = "individual") as members from members
     where users_user_id = :userid and saccos_sacco_id = :saccoid');
    $query->bindParam(':userid', $returned_id, PDO::PARAM_INT);
    $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
    $query->execute();
    // get row count
    $rowCount = $query->rowCount();
    // create array to store returned task
    // $returnData['totalmembers'] = $rowCount;
    while($row = $query->fetch(PDO::FETCH_ASSOC)) {
      extract($row);
      $member = array(
        'accounts' => $members+$groups,
        'members' => $members,
        'groups' => $groups
     );
    }
    $memberArray[] = $member;
    $returnData['accounts'] = $memberArray;

    // number of logins api
    // members amount in the squence
    $query = $readDB->prepare('SELECT * from user_activity where users_user_id = :userid');
    $query->bindParam(':userid', $returned_id, PDO::PARAM_INT);
    $query->execute();

    // get row count
    $rowCount = $query->rowCount();
    // create array to store returned task
    $returnData['totalactivity'] = $rowCount;

    // check for sms sent by the saccos
    // members amount in the squence
    $query = $readDB->prepare('SELECT * from sms where saccos_sacco_id = :saccoid and status = "Y"');
    $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
    $query->execute();

    // get row count
    $rowCount = $query->rowCount();
    // create array to store returned task
    $returnData['totalsms'] = $rowCount*2;

    // months for the total deposits and withdraws
    // first month of january
    $loansArray = array();
    $query = $readDB->prepare('SELECT SUM(amount_disbursed) as loans,
     DATE_FORMAT(loan_timestamp, "%b") as month  from
     loans_disbursed where users_user_id = :userid and saccos_sacco_id = :saccoid and loan_status = "open"
     group by month(loan_timestamp) order by month(loan_timestamp) asc');
    $query->bindParam(':userid', $returned_id, PDO::PARAM_INT);
    $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
    $query->execute();

    while($row = $query->fetch(PDO::FETCH_ASSOC)) {
      extract($row);
    $loansArray[] = $loans;
    }
    $returnData['loans'] = $loansArray;

    // withdraw array
    $loansAppArray = array();
    $monthloansArray = array();
    $query = $readDB->prepare('SELECT SUM(loan_app_amount) as loansapp, DATE_FORMAT(loan_app_timestamp, "%b")
    as month  from  loan_applications where users_user_id = :userid and saccos_sacco_id = :saccoid
    and loan_app_status = "pending" group by month(loan_app_timestamp) order by month(loan_app_timestamp) asc');
    $query->bindParam(':userid', $returned_id, PDO::PARAM_INT);
    $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
    $query->execute();

    while($row = $query->fetch(PDO::FETCH_ASSOC)) {
      extract($row);
    $loansAppArray[] = $loansapp;
    $monthloansArray[] = $month;

    }
    $returnData['loan_app'] = $loansAppArray;
    $returnData['months'] = $monthloansArray;

    // male and female return data
    $genderArray = array();
    $gquery = $readDB->prepare('SELECT SUM(member_gender = "male") as male,
     SUM(member_gender = "female") as female from members where users_user_id = :userid
     and saccos_sacco_id = :saccoid');
    $gquery->bindParam(':userid', $returned_id, PDO::PARAM_INT);
    $gquery->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
    $gquery->execute();
    // echo $count= $query->rowCount();

    while($row = $gquery->fetch(PDO::FETCH_ASSOC)) {
      extract($row);
      echo $female;
      $gender = array(
        'male' => $male,
        'female' => $female
     );
    }
    $genderArray[] = $gender;
    $returnData['gender'] = $genderArray;

    $loansDueArray = array();
    $query = $readDB->prepare('SELECT * from loans_disbursed,loan_applications
    where loan_status="overdue"
    AND loan_applications_loan_app_id=loan_app_id
    AND loan_applications.users_user_id = :userid and loan_applications.saccos_sacco_id = :saccoid
    order by loan_applications_loan_app_id DESC');
    $query->bindParam(':userid', $returned_id, PDO::PARAM_INT);
    $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
    $query->execute();

    while($row = $query->fetch(PDO::FETCH_ASSOC)) {
    extract($row);
        $loansDue = array(
            "amount"=> ($amount_disbursed + $total_interest),
            "number" => $loan_app_number,
            "balance" => $loan_balance

        );
        $loansDueArray[] = $loansDue;
    }
    $returnData['overdueLoans'] = $loansDueArray;

    $loansDueArrayToday = array();
    $query = $readDB->prepare('SELECT * from loans_disbursed,loan_applications
    where loan_status="overdue"
    AND loan_applications_loan_app_id=loan_app_id
    AND loan_applications.users_user_id = :userid and loan_applications.saccos_sacco_id = :saccoid
    order by loan_applications_loan_app_id DESC');
    $query->bindParam(':userid', $returned_id, PDO::PARAM_INT);
    $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
    $query->execute();

    while($row = $query->fetch(PDO::FETCH_ASSOC)) {
    extract($row);
        $loansDue = array(
          "amount"=> ($amount_disbursed + $total_interest),
          "number" => $loan_app_number,
          "balance" => $loan_balance

        );
        $loansDueArrayToday[] = $loansDue;
    }
    $returnData['overdueLoanstoday'] = $loansDueArrayToday;
    // recent withdraw_status
    $recentWithdrawArray = array();
    $query = $readDB->prepare('SELECT * from withdrawal_transactions, members
      where withdrawal_transactions.members_member_id = members.member_id
      and withdrawal_transactions.users_user_id = :userid
      and withdrawal_transactions.saccos_sacco_id = :saccoid
       and withdraw_status = "successful" order by withdraw_id DESC limit 3');
    $query->bindParam(':userid', $returned_id, PDO::PARAM_INT);
    $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
    $query->execute();

    while($row = $query->fetch(PDO::FETCH_ASSOC)) {
    extract($row);
        $withdraw = array(
            "amount" => $withdraw_amount,
            "account" => $members_account_number,
            "time" => $withdraw_timestamp
        );
        $recentWithdrawArray[] = $withdraw;
    }
    $returnData['recentwithdraws'] = $recentWithdrawArray;
    // get the sahres
      $sharesArray = array();
      $query = $readDB->prepare('SELECT SUM(shares_amount) as shareTotal
      from shares where users_user_id = :userid and saccos_sacco_id = :saccoid');
      $query->bindParam(':userid', $returned_id, PDO::PARAM_INT);
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $query->execute();

      while($row = $query->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
      $sharesArray[] = $shareTotal;
      }
      $returnData['shares'] = $sharesArray;

      // first month of january
      $onedepositsArray = array();
      $query = $readDB->prepare('SELECT SUM(deposit_amount) as deposits,
      DATE_FORMAT(desposit_timestamp, "%Y-%m-%d"), DATE_FORMAT(desposit_timestamp, "%b") as month
      from  desposit_transactions where DATE(desposit_timestamp) = CURDATE() and users_user_id = :userid
      and saccos_sacco_id = :saccoid and desposit_status = "successful"');
      $query->bindParam(':userid', $returned_id, PDO::PARAM_INT);
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $query->execute();

      while($row = $query->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
      $onedepositsArray[] = $deposits;

      }
      $returnData['onedeposits'] = $onedepositsArray;

      // last month of year
      $onewithdrawsArray = array();
      $query = $readDB->prepare('SELECT SUM(withdraw_amount) as withdraws,
      DATE_FORMAT(withdraw_timestamp, "%Y-%m-%d") from  withdrawal_transactions
      where DATE(withdraw_timestamp) = CURDATE() and users_user_id = :userid
      and saccos_sacco_id = :saccoid and withdraw_status = "successful"');
      $query->bindParam(':userid', $returned_id, PDO::PARAM_INT);
      $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
      $query->execute();

      while($row = $query->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
      $onewithdrawsArray[] = $withdraws;
      }
      $returnData['onewithdraws'] = $onewithdrawsArray;
    // set up response for successful return
    $response = new Response();
    $response->setHttpStatusCode(200);
    $response->setSuccess(true);
    $response->toCache(true);
    $response->setData($returnData);
    $response->send();
    exit;
  }
  catch(PDOException $ex) {
    error_log("Database Query Error: ".$ex, 0);
    $response = new Response();
    $response->setHttpStatusCode(500);
    $response->setSuccess(false);
    $response->addMessage("there is a query error ".$ex);
    $response->send();
    exit;
  }
