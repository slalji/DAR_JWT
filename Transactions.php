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
    public function openAccount($payload)
    {
        
              
        try{
            // sendToDoTransaction(json_encode($payload));
            $message = array();
            $message['status']="SUCCESS";
            $message['method']=$payload->method;
            print_r($payload);
            $respArray = ['transId'=>$payload->requestParams->transId,'reference'=>$this->reference,'responseCode' => 200, "Message"=>($message)];


        }
        catch (Exception $e) {
            $message = array();
            $message['status']="ERROR";
            $message['method']='Transaction error at: '.$payload->method;
            
            $respArray = ['transId'=>$payload->requestParams->transId,$this->reference,'responseCode' => 501, "Message"=>($message)];
        }
              
    
        return ($respArray);
    }
}
