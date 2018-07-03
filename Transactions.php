<?php
require_once ("config.php");

/** 
 *
 * PHP version 5
 *
 * @modal Validate
 * @author   Salma Lalji
 **/
class Transactions
{
    private $reference = null;
    private $conn =null;

    public function __construct() {
        $reference = rand(100000000000,999999999999);
        date_default_timezone_set('Africa/Dar_es_Salaam');
        $dsn = 'mysql:dbname=' . DB_NAME . ';host=' . DB_HOST;
        $user = DB_USER;
        $pw = DB_PASS;
        try {
            $this->conn = new PDO( DB_DSN, DB_USER, DB_PASS ); 
            $this->conn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            return $this->conn;
        }
        catch(PDOException $e) {
            echo 'Connection failed: ' . $e->getMessage();;
        }
    }
    public function PDOBindArray(&$poStatement, &$paArray){

        foreach ($paArray as $k=>$v){
      
          @$poStatement->bindValue(':'.$k,$v);
      
        } // foreach
        return $poStatement;
      } // function
    public function openAccount($payload)
    {
             $cols=null;
             $vals=null;
             $bind=null;
             $card=rand(10000000000,9999999999);
        try{
            /*$cols ='card,firstName,lastName,accountNo,msisdn,email,addressLine1,addressCity,addressCountry,dob,currancy';
            $ref =':card,:firstName,:lastName,:accountNo,:msisdn,:email,:addressLine1,:addressCity,:addressCountry,:dob:,:currancy';
            

            $vals .='"'.$card.'", ';
            $vals .='"'.$payload->firstName.'", ';
            $vals .='"'.$payload->lastName.'", ';
            $vals .='"'.$payload->accountNo.'", ';
            $vals .='"'.$payload->msisdn.'", ';
            $vals .='"'.$payload->email.'", ';
            $vals .=isset($payload->addressLine1)?'"'.$payload->addressLine1.'", ':'",';
            $vals .=isset($payload->addressCity)?'"'.$payload->addressCity.'", ':' ,';
            $vals .=isset($payload->addressCountry)?'"'.$payload->addressCountry.'", ':'",';       
            $vals .=isset($payload->dob)?'"'.$payload->dob.'", ':' ,';
            $vals .=isset($payload->currency)?'"'.$payload->currency.'" ':'"'; 
           */
          foreach($payload as $key => $val){
              if ($key != 'transId'){
                $cols.=$key.', ';
                $vals.=':'.$key.', ';
              }        
           
          }
          $cols = rtrim($cols,', ');
          $vals = rtrim($vals,', ');
                $sql = "INSERT INTO account (".$cols.") VALUES (".$vals.")";
                
                $stmt = $this->conn->prepare( $sql );
                $state = $this->PDOBindArray($stmt,$payload);
                $state->execute();

            $message = array();
            $message['status']="SUCCESS";
            $message['method']="openAccount";
           
            $respArray = ['transId'=>$payload->transId,'reference'=>$this->reference,'responseCode' => 200, "Message"=>($message)];
        }
        catch (Exception $e) {
            var_dump($sql);
            $message = array();
            $message['status']="ERROR";
            $message['method']='Transaction error at: openAccount '.$e->getMessage()." : ".$sql;
            
            $respArray = ['transId'=>$payload->transId,$this->reference,'responseCode' => 501, "Message"=>($message)];
        }
        return ($respArray);
    }
}
