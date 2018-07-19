<?php
require_once ("config.php");
require_once ("Transactions.php");
/**
 *
 * PHP version 5
 *
 * @modal DB
 * @author   Salma Lalji
 **/
 
class DB extends PDO {

    public $conn =null;
    private static $objInstance; 
   

    public function __construct() {
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
    public static function getInstance() {
        if(!self::$objInstance){ 
            date_default_timezone_set('Africa/Dar_es_Salaam');
            $dsn = 'mysql:dbname=' . DB_NAME . ';host=' . DB_HOST;
            $user = DB_USER;
            $pw = DB_PASS;
            try {
                self::$objInstance = new PDO( DB_DSN, DB_USER, DB_PASS ); 
                self::$objInstance->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
                return self::$objInstance;
            }
            catch(PDOException $e) {
                echo 'Connection failed: ' . $e->getMessage();;
            }
        }
        return self::$objInstance; 


    }
    public static function getToken($length)
    {
        return substr(str_shuffle(implode("", (array_merge(range(0, 25))))), 0, $length);
    }

    public static function toDateTime($dt){
        if( $dt == date('Y-m-d H:i:s',strtotime($dt)) ){
            // date is in fact in one of the above formats
            return $dt;
        }
        else
        {
            // date is something else.
           return date('Y-m-d H:i:s',$dt);
        }
    }
    public static function toDate($dt){
        if( $dt == date('Y-m-d',strtotime($dt)) ){
            // date is in fact in one of the above formats
            return $dt;
        }
        else
        {
            // date is something else.
           return date('Y-m-d',$dt);
        }
    }
    public static function getErrorResponse($data, $err, $ref){
            $message = array();
            $message['status']="Internal Server Error";
            //$message['method']="openAccount";
            $message['data']=$err;
            $respArray = ['transid'=>$data->transid,'reference'=>$ref,'responseCode' => 500, "Message"=>($message)];

           return json_encode($respArray); 
    }
    public function incoming($data) {
       
        try{
           // $params = $this->toString($data);
            $params = json_encode((array)$data);
            $paramdatetime = $this->toDateTime($data->timestamp);           

            //date('Y-m-d H:i:s',$data->timestamp);
            $sql= 'INSERT INTO incoming (fulltimestamp, paramtimestamp, method, transid,payload) 
                VALUES (now(), :paramtimestamp, :method, :transid, :payload)';
            $stmt = $this->conn->prepare( $sql );
            $stmt->bindValue( "paramtimestamp", $paramdatetime, PDO::PARAM_STR );            
            $stmt->bindValue( "method", $data->method, PDO::PARAM_STR );
            $stmt->bindValue( "transid", $data->requestParams->transid, PDO::PARAM_STR );
            $stmt->bindValue( "payload", $params , PDO::PARAM_STR);
            $stmt->execute();
            return true; 
        }
        catch(PDOExecption $e) { 
            //$stmt->rollback(); 
            print "Error!: " . $e->getMessage() . $sql."</br>".$data;            
        } 
        catch(Execption $e ) { 
            print "Error!: " . $e->getMessage() . $sql. "</br>".$data; 
        } 
        return false;
    }
    public function transaction($data, $method){
        $transaction = new Transactions();
        
        switch($method){
            case 'openAccount': print_r( $transaction->OpenAccount($data)); break;
            case 'updateAccount': print_r($transaction->UpdateAccount($data)); break;
            case 'addCash': print_r( $transaction->cashin($data)); break;
            case 'payUtility': print_r( $transaction->payUtility($data)); break;
            case 'fundTransfer': print_r( $transaction->transferFunds($data)); break;
            case 'nameLookup': print_r( print_r($transaction->NameLookup($data))); break;
            case 'transactionLookup': print_r( print_r($transaction->TransactionLookup($data))); break;
            case 'reserveAmount': print_r( $transaction->reserveAmount($data)); break;
            case 'unReserveAccount': print_r( $transaction->unReserveAccount($data)); break;
            case 'changeStatus': print_r( $transaction->updateAccountStatus($data)); break;
            case 'requestCard': print_r( $transaction->requestCard($data)); break; //requestVNC
            case 'reversal': print_r( $transaction->Reversal($data)); break;
            case 'exGratiaPayments': print_r( $transaction->ExGratiaPayments($data)); break;
            case 'checkBalance': print_r( $transaction->checkBalance($data)); break;
            case 'getStatement': print_r( $transaction->getStatement($data)); break;
            default: print_r('invalid command method found: '.$method);
            
        }

    }
}

?>