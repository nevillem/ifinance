<?php
// require_once('../core/initialize.php');
// require_once('Database.php');
// require_once('../model/Response.php');

require '/home/irembo/api.v4.irembofinance.com/core/cronjobintialize.php';
require_once('Database.php');
require_once('/home/irembo/api.v4.irembofinance.com/model/Response.php');

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
      $status = 'N';
      $query = $writeDB->prepare('select * from emails where status = :status');
      $query->bindParam(':status', $status, PDO::PARAM_STR);
      $query->execute();

      $rowCount = $query->rowCount();

      if($rowCount > 0) {

        $messages = $query->fetchAll(PDO::FETCH_ASSOC);
         $sent_successfully = array();
        foreach ($messages as $message) {
          $send_sms = send_email($message['email'],$message['message']);
           array_push($sent_successfully, $message['id']);
        }
      }

      if (!empty($sent_successfully)) {
        try{
            // start transaction so we can roll back any updates if something fails
            // $writeDB->beginTransaction();
            foreach ($sent_successfully as $sent_message) {
                $updateSentMessage = $writeDB->prepare('UPDATE emails SET status="Y" WHERE id=:id');
                $updateSentMessage->bindParam(':id', $sent_message, PDO::PARAM_INT);;
                $updateSentMessage->execute();

                $updateSentMessageCount = $updateSentMessage->rowCount();
                // if ($updateSentMessageCount === 0) {
                //     // rollback transactions if any outstanding transactions are present
                //     if($writeDB->inTransaction()) {
                //         $writeDB->rollBack();
                //     }
                // }
            }

            // $writeDB->commit();
        } catch(PDOException $ex) {
            // $writeDB->rollBack();
        }
    }
