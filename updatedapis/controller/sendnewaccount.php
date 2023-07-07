<?php

require_once('../core/initialize.php');
require_once('Database.php');


// Dear {first_name},
// following our recent system upgrade, your {SACCO} A/C no. has been updated to {new_account}. For more info. call {sacco_number}.
// Thank you for saving with us.
// Powered by iRembo Finance.

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


      $query = $readDB->prepare('SELECT member_fname, sacco_short_name, members_account_number,member_contact ,sacco_contact, sacco_id from members, saccos where members.saccos_sacco_id = saccos.sacco_id');
      $query->execute();

      $rowCount = $query->rowCount();

      if ($rowCount === 0) {
        echo json_encode('no rows returned');
      }

      $dataArray = array();
      while($row = $query->fetch(PDO::FETCH_ASSOC)):
        extract($row);
        $data = array(
        "contact" => "256".$member_contact,
        "saccoid" => $sacco_id,
        "message" =>  "Dear ".$member_fname.", following our recent system upgrade, your ".$sacco_short_name." A/C no. has been updated to ".$members_account_number.". For more info. call +256".$sacco_contact.". Thank you for saving with us. Powered by iRembo Finance.");
        $dataArray[] = $data;
      endwhile;

      $query = $writeDB->prepare('insert into sms(contact,message,saccos_sacco_id) values (:contact,:message, :saccoid)');
      for ($i=0; $i<$rowCount; $i++) {
        $query->execute(array(
            "message" => $dataArray[$i]['message'],
            "contact" => $dataArray[$i]['contact'],
            "saccoid" => $dataArray[$i]['saccoid']
        ));
      }

      json_encode($dataArray);
