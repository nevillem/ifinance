<?php
// require_once('Database.php');
// require_once('../core/classes/Account.php');
  // $returned_saccoid = 1;
  // $makeaccount = new Account();
  // $account = $makeaccount->create_account($returned_saccoid);
  // $sacco = 1001000001;
  // $newsacco = $sacco+1;
  //
  // echo json_encode($newsacco);
$password = '4546';
$hashpassword = password_hash($password, PASSWORD_DEFAULT);
 echo $hashpassword;

// $amount = 5000;
// $percentage = 1.4;
// $start = strtotime('2010-01-25');
// $end = strtotime('2010-02-20');
// $days = ceil(abs($end - $start) / 86400);
//
// $balance = $amount + ($amount*($percentage/100)*$days);

// echo json_encode($balance);
// echo json_encode($days);
// $number = 104423445;
// echo substr($number, 0,4);


?>
