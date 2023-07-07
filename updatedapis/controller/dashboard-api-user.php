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
    // create db query and option select
    $query = $readDB->prepare('SELECT SUM(deposit_amount) as total_deposit_amount from desposit_transactions where users_user_id = :userid and saccos_sacco_id = :saccoid and desposit_status = "successful"');
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
       "totaldeposit" => number_format($total_deposit_amount)
     );
     $transactionArray[] = $transactions;
    }

    // bundle rows returned into an array to return in the json data
    $returnData = array();
    // $returnData['rows_returned'] = $rowCount;
    $returnData['totaldeposit'] = $transactionArray;

    // withdraw amount in the squence
    $query = $readDB->prepare('SELECT SUM(withdraw_amount) as total_withdraw_amount from withdrawal_transactions where users_user_id = :userid and saccos_sacco_id = :saccoid and withdraw_status = "successful"');
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
       "totalwithdraw" => number_format($total_withdraw_amount)
     );
     $transactionArray[] = $transactions;
    }


    // $returnData['rows_returned'] = $rowCount;
    $returnData['totalwithdraw'] = $transactionArray;

    // members amount in the squence
    $query = $readDB->prepare('SELECT * from members where users_user_id = :userid and saccos_sacco_id = :saccoid');
    $query->bindParam(':userid', $returned_id, PDO::PARAM_INT);
    $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
    $query->execute();

    // get row count
    $rowCount = $query->rowCount();
    // create array to store returned task
    $returnData['totalmembers'] = $rowCount;

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
    $returnData['totalsms'] = $rowCount;

    // months for the total deposits and withdraws
    // first month of january
    // deposits
    $query = $readDB->prepare('SELECT SUM(deposit_amount) as deposits  from  desposit_transactions where users_user_id = :userid and saccos_sacco_id = :saccoid and desposit_status = "successful" and desposit_timestamp >= DATE("2022-01-01") and desposit_timestamp <= DATE("2022-01-31")');
    $query->bindParam(':userid', $returned_id, PDO::PARAM_INT);
    $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
    $query->execute();

    $row = $query->fetch(PDO::FETCH_ASSOC);
    $deposits = $row['deposits'];

    // withdraws
    $query = $readDB->prepare('SELECT SUM(withdraw_amount) as withdraws  from  withdrawal_transactions where users_user_id = :userid and saccos_sacco_id = :saccoid and withdraw_status = "successful" and withdraw_timestamp >= DATE("2022-01-01") and withdraw_timestamp <= DATE("2022-01-31")');
    $query->bindParam(':userid', $returned_id, PDO::PARAM_INT);
    $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
    $query->execute();

    $row = $query->fetch(PDO::FETCH_ASSOC);
    $withdraws = $row['withdraws'];

    // make the jaunary array
    $january = array(
      "deposits" => $deposits,
      "withdraws" => $withdraws
    );

    // return the data
    $returnData['jan'] = $january;

    // months for the total deposits and withdraws
    // first month of feb
    // deposits
    $query = $readDB->prepare('SELECT SUM(deposit_amount) as deposits  from  desposit_transactions where users_user_id = :userid and saccos_sacco_id = :saccoid and desposit_status = "successful" and desposit_timestamp >= DATE("2022-02-01") and desposit_timestamp <= DATE("2022-02-28")');
    $query->bindParam(':userid', $returned_id, PDO::PARAM_INT);
    $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
    $query->execute();

    $row = $query->fetch(PDO::FETCH_ASSOC);
    $deposits = $row['deposits'];

    // withdraws
    $query = $readDB->prepare('SELECT SUM(withdraw_amount) as withdraws  from  withdrawal_transactions where users_user_id = :userid and saccos_sacco_id = :saccoid and withdraw_status = "successful" and withdraw_timestamp >= DATE("2022-02-01") and withdraw_timestamp <= DATE("2022-02-28")');
    $query->bindParam(':userid', $returned_id, PDO::PARAM_INT);
    $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
    $query->execute();

    $row = $query->fetch(PDO::FETCH_ASSOC);
    $withdraws = $row['withdraws'];

    // make the jaunary array
    $febraury = array(
      "deposits" => $deposits,
      "withdraws" => $withdraws
    );

    // return the data
    $returnData['feb'] = $febraury;

    // months for the total deposits and withdraws
    // first month of march
    // deposits
    $query = $readDB->prepare('SELECT SUM(deposit_amount) as deposits  from  desposit_transactions where users_user_id = :userid and saccos_sacco_id = :saccoid and desposit_status = "successful" and desposit_timestamp >= DATE("2022-03-01") and desposit_timestamp <= DATE("2022-03-31")');
    $query->bindParam(':userid', $returned_id, PDO::PARAM_INT);
    $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
    $query->execute();

    $row = $query->fetch(PDO::FETCH_ASSOC);
    $deposits = $row['deposits'];

    // withdraws
    $query = $readDB->prepare('SELECT SUM(withdraw_amount) as withdraws  from  withdrawal_transactions where users_user_id = :userid and saccos_sacco_id = :saccoid and withdraw_status = "successful" and withdraw_timestamp >= DATE("2022-03-01") and withdraw_timestamp <= DATE("2022-03-31")');
    $query->bindParam(':userid', $returned_id, PDO::PARAM_INT);
    $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
    $query->execute();

    $row = $query->fetch(PDO::FETCH_ASSOC);
    $withdraws = $row['withdraws'];

    // make the jaunary array
    $march = array(
      "deposits" => $deposits,
      "withdraws" => $withdraws
    );

    // return the data
    $returnData['mar'] = $march;

    // months for the total deposits and withdraws
    // first month of march
    // deposits
    $query = $readDB->prepare('SELECT SUM(deposit_amount) as deposits  from  desposit_transactions where users_user_id = :userid and saccos_sacco_id = :saccoid and desposit_status = "successful" and desposit_timestamp >= DATE("2022-04-01") and desposit_timestamp <= DATE("2022-04-30")');
    $query->bindParam(':userid', $returned_id, PDO::PARAM_INT);
    $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
    $query->execute();

    $row = $query->fetch(PDO::FETCH_ASSOC);
    $deposits = $row['deposits'];

    // withdraws
    $query = $readDB->prepare('SELECT SUM(withdraw_amount) as withdraws  from  withdrawal_transactions where users_user_id = :userid and saccos_sacco_id = :saccoid and withdraw_status = "successful" and withdraw_timestamp >= DATE("2022-04-01") and withdraw_timestamp <= DATE("2022-04-30")');
    $query->bindParam(':userid', $returned_id, PDO::PARAM_INT);
    $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
    $query->execute();

    $row = $query->fetch(PDO::FETCH_ASSOC);
    $withdraws = $row['withdraws'];

    // make the jaunary array
    $april = array(
      "deposits" => $deposits,
      "withdraws" => $withdraws
    );

    // return the data
    $returnData['apr'] = $april;

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
    $response->addMessage("Failed to get transaction".$ex);
    $response->send();
    exit;
  }
