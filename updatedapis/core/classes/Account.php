<?php

require_once('Database.php');

class Account
{
  private $conn;

        function __construct()
        {
          // attempt to set up connections to read and write db connections
          try {
            $writeDB = DB::connectWriteDB();
            $readDB = DB::connectReadDB();
            $this->conn = $writeDB;
          }
          catch(PDOException $ex) {
            error_log("database connection error: ".$ex, 0);
          }

        }

        function create_account($returned_saccoid){
          $res = array();
          try {
                $query = $this->conn->prepare("select members_account_number from members where saccos_sacco_id = :saccoid ORDER BY members_account_number DESC");
                $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_STR);
                $query->execute();
                $rowCount = $query->rowCount();
                $row = $query->fetch(PDO::FETCH_ASSOC);
                if ($rowCount === 0) {
                  $query = $this->conn->prepare('select sacco_code from saccos where sacco_id = :saccoid');
                  $query->bindParam(':saccoid', $returned_saccoid, PDO::PARAM_INT);
                  $query->execute();
                  $rowCount = $query->rowCount();
                    if ($rowCount === 0) {
                          $res['success'] = false;
                    }
                  $row =  $query->fetch(PDO::FETCH_ASSOC);
                  $saccocode = $row['sacco_code'];
                  $account = $saccocode."00001";
                }else{
                  $account = $row['members_account_number']+1;
                }
                return $account;
          } catch (PDOException $ex) {
            $res['success'] = false;
          }

        }

}
