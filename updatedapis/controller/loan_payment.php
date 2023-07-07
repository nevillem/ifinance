<?php
require_once('../core/initialize.php');
// require '/home/irembo/api.v4.irembofinance.com/core/cronjobintialize.php';
 require_once('Database.php');
 require_once('../model/Response.php');

// require_once('/home/irembo/api.test.irembofinance.com/model/Response.php');
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
// send all sms in the database
$query = $readDB->prepare('SELECT * from loan_applications,loan_product_settings,
  loans_disbursed,members,saccos WHERE  EXISTS (SELECT * FROM members
    WHERE members.member_id= loan_applications.members_member_id)
  AND members.member_id= loan_applications.members_member_id
   AND members.saccos_sacco_id = saccos.sacco_id
   AND loan_applications.loan_app_id = loans_disbursed.loan_applications_loan_app_id
   AND loan_applications.loan_product_product_id=loan_product_settings.sloan_product_id
   AND loans_disbursed.loan_applications_loan_app_id =loan_applications.loan_app_id
   ORDER BY loan_app_id DESC');
$query->execute();
$sent_successfully = array();

 while ($row= $query->fetch(PDO::FETCH_ASSOC)) {
   extract($row);
  $loanapplication= $row['loan_app_id'];
  $saccoid= $row['saccos_sacco_id'];
  $sacconame= $row['sacco_name'];
  $names=$member_fname.' '.$member_mname.' '.$member_lname;
  $accountContact = $member_contact;
  $AccountNumber = $members_account_number;
  $data=array("customnames"=>$names,"disburseid"=>$loan_id,
  "loanappid"=>$loanapplication,"memberaccount"=>$account_disburse_to,
  "loanaccount"=>$account_disburse_from,"branch"=>$branches_branch_id ,
   "accountNumber"=>$AccountNumber,"accountContact"=>$accountContact,"saccoid"=>$saccoid,
   "sacco_name"=>$sacconame,"saccoemail"=>$sacco_email, "smsstatus"=>$sacco_sms_status,
   "emailstatus"=>$sacco_email_status);
  $sent_successfully[]=$data;
 }
 $clients = array_column($sent_successfully, 'customnames');
 $loanapplication = array_column($sent_successfully, 'loanappid');
 $saccoid = array_column($sent_successfully, 'saccoid');
 $disburseid = array_column($sent_successfully, 'disburseid');
 $memberaccount = array_column($sent_successfully, 'memberaccount');
 $loanaccount = array_column($sent_successfully, 'loanaccount');
 $branchid= array_column($sent_successfully, 'branch');
 $accountNumber= array_column($sent_successfully, 'accountNumber');
 $accountcontact= array_column($sent_successfully, 'accountContact');
 $sacconame= array_column($sent_successfully, 'sacco_name');
 $saccoemail= array_column($sent_successfully, 'saccoemail');
 $smsstatus= array_column($sent_successfully, 'smsstatus');
 $emailstatus= array_column($sent_successfully, 'emailstatus');

$datenow= date('Y-m-d');
 $query = $readDB->prepare('SELECT * from loans_disbursed, loan_payment_schedule
   WHERE loans_disbursed.loan_id = loan_payment_schedule.loan_active_loan_id
   AND loan_payment_date <=:datenow  AND loan_active_loan_id=:loanapp AND loan_payment_status="notpaid" ORDER BY loan_payment_date ASC');
 for ($i=0; $i <count($clients); $i++) {
  $accounts= $memberaccount[$i];
  $loanaccounts= $loanaccount[$i];
  $accountNumbers= $accountNumber[$i];
  $accountcontacts= $accountcontact[$i];
  $saccoemails= $saccoemail[$i];
  $sacconames= $sacconame[$i];
  $customnames= $clients[$i];
  $smsstatus= $smsstatus[$i];
  $emailstatus= $emailstatus[$i];

  $maccountsquery = $readDB->prepare('SELECT * from member_accounts,accounts
  WHERE member_accounts_id =:memberaccount AND accounts.accounts_id=member_accounts.member_accounts_account_id');
  $maccountsquery->bindParam(':memberaccount', $accounts, PDO::PARAM_INT);
  $maccountsquery->execute();
  $rowCount = $maccountsquery->rowCount();
  if ($rowCount === 0) {
    $response = new Response();
    $response->setHttpStatusCode(404);
    $response->setSuccess(false);
    $response->addMessage("member not found");
    $response->send();
    exit;
  }
  $loanaccountquery = $readDB->prepare('SELECT accounts_id, opening_balance from accounts where accounts_id = :id');
  $loanaccountquery->bindParam(':id', $loanaccounts, PDO::PARAM_INT);
  $loanaccountquery->execute();
  $loanaccRowCount = $loanaccountquery->rowCount();

  if ($loanaccRowCount === 0) {
    $response = new Response();
    $response->setHttpStatusCode(404);
    $response->setSuccess(false);
    $response->addMessage("sacco loan account not found");
    $response->send();
    exit;
  }
  //get account
  $loanrow = $loanaccountquery->fetch(PDO::FETCH_ASSOC);
  $memberaccountrow = $maccountsquery->fetch(PDO::FETCH_ASSOC);
  $saccoloanaccount = $loanrow['accounts_id'];
  $openingbalance = $loanrow['opening_balance'];
  $AccountBalance = $memberaccountrow['total_deposit'];
  $accountname = $memberaccountrow['account_name'];

  $query->execute(array(":datenow"=>$datenow,":loanapp"=>$disburseid[$i]));
  $rowsCount= $query->rowCount();
  while($rowws =$query->fetch(PDO::FETCH_ASSOC)):
  extract($rowws);
  $supposedpaydate=$loan_payment_date;
  $principalpaid=$loan_total_paid_principal;
  $loanscheduleid=$loan_payment_id;

  $totalpaidquery = $readDB->prepare('SELECT sum(paid_amount) as totalpaid from loan_payment
  WHERE loan_schedule_id =:loanscheduleid AND loan_payment_saccoid=:saccoid ');
  $totalpaidquery->bindParam(':loanscheduleid', $loanscheduleid, PDO::PARAM_INT);
  $totalpaidquery->bindParam(':saccoid', $saccoid[$i], PDO::PARAM_INT);
  $totalpaidquery->execute();
  $rowaccountCount = $totalpaidquery->rowCount();
  $totalpaidrow = $totalpaidquery->fetch(PDO::FETCH_ASSOC);
  // $totalpaid = $totalpaidrow['totalpaid']."\n";
  $totalpaid = $totalpaidrow['totalpaid'];
  if ($AccountBalance >= $principalpaid &&  $totalpaid ==0 ) {
  $principal=$principalpaid-$totalpaid;
  $AccountBalance =$AccountBalance-$principal ;
  $openingbalance=$openingbalance+$principal;
  switch ($principalpaid) {
    case "$principal":
      updateLoanPaymentScedule($writeDB,$loanscheduleid);
      break;
  }
  // updatememberAccount($writeDB, $accounts,$AccountBalance);
  insertLoanPayment($writeDB, $datenow, $principal, $loanscheduleid, $branchid[$i],$saccoid[$i],$accounts,
  $AccountBalance,$saccoloanaccount,$openingbalance,$accountNumbers,$accountcontacts,$sacconames,
  $saccoemails,$smsstatus, $emailstatus,$accountname,$customnames);
  }
  elseif ($AccountBalance >= $principalpaid && $totalpaid >0) {
    $money =$principalpaid-$totalpaid;
    // echo "for $money";
    $principall= $money +$totalpaid;
    switch ($principalpaid) {
      case "$principall":
        updateLoanPaymentScedule($writeDB,$loanscheduleid);
        break;
    }
    $AccountBalance =$AccountBalance-$money ;
    // updatememberAccount($writeDB, $accounts,($AccountBalance-$money));
    insertLoanPayment($writeDB, $datenow, $money, $loanscheduleid, $branchid[$i], $saccoid[$i],
     $accounts,($AccountBalance-$money),$saccoloanaccount,($openingbalance + $money),$accountNumbers,
     $accountcontacts,$sacconames,$saccoemails,$smsstatus, $emailstatus,$accountname,$customnames);
  }
  elseif($AccountBalance < $principalpaid && $AccountBalance !=0) {
  if ($totalpaid !="" &&  ($totalpaid + $AccountBalance) < $principalpaid && $AccountBalance !=0) {
    // echo "string: $AccountBalance";
    $openingbalance=$openingbalance+$AccountBalance;
    insertLoanPayment($writeDB, $datenow, $AccountBalance, $loanscheduleid, $branchid[$i], $saccoid[$i],
    $accounts,0, $saccoloanaccount, ($openingbalance + $AccountBalance),$accountNumbers,$accountcontacts,
    $sacconames,$saccoemails,$smsstatus, $emailstatus,$accountname,$customnames);
    $AccountBalance =0;
  }elseif($totalpaid !=="" &&  ($totalpaid + $AccountBalance) > $principalpaid && $AccountBalance !=0){
     $money =$principalpaid-$totalpaid;
     $principall= $money +$totalpaid;
    switch ($principalpaid) {
      case "$principall":
        updateLoanPaymentScedule($writeDB,$loanscheduleid);
        break;
    }
    $AccountBalance =$AccountBalance-$money ;
    // updatememberAccount($writeDB, $accounts,($AccountBalance-$money));
    insertLoanPayment($writeDB, $datenow, $money, $loanscheduleid, $branchid[$i], $saccoid[$i], $accounts,
    ($AccountBalance-$money),$saccoloanaccount, ($openingbalance + $money),$accountNumbers,$accountcontacts,
    $sacconames,$saccoemails,$smsstatus, $emailstatus,$accountname,$customnames);
    // echo "string3";
  }elseif ($totalpaid =="" || $totalpaid==0 && $AccountBalance < $principalpaid && $AccountBalance !=0) {
    // echo "string4 $AccountBalance";
    insertLoanPayment($writeDB, $datenow, $AccountBalance, $loanscheduleid, $branchid[$i], $saccoid[$i],
    $accounts,0,$saccoloanaccount, ($openingbalance + $AccountBalance),$accountNumbers,$accountcontacts,
    $sacconames,$saccoemails,$smsstatus, $emailstatus,$accountname,$customnames);
    $AccountBalance =0;
  }
  }
  endwhile;
 }

 ?>
