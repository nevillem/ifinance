<?php

require_once('Database.php');

class Sacconumber
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

        function create_sacco_code(){
          $res = array();
          try {
                $query = $this->conn->prepare("select sacco_code from saccos ORDER BY sacco_code DESC");
                $query->execute();
                $rowCount = $query->rowCount();
                $row = $query->fetch(PDO::FETCH_ASSOC);
                if ($rowCount === 0) {
                  $saccocode = $saccocode."1001";
                }else{
                  $saccocode = $row['sacco_code']+1;
                }
                return $saccocode;
          } catch (PDOException $ex) {
            $res['success'] = false;
          }

        }

}
