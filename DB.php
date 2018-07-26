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
            $message['data']=($err);
            $respArray = ['transid'=>$data->transid,'reference'=>$ref,'responseCode' => 500, "Message"=>($message)];

           return json_encode($respArray); 
    }
    public function incoming($data) {
       
        try{
           // $params = $this->toString($data);
            $params = json_encode((array)$data);
            $paramdatetime = $this->toDateTime($data->timestamp);           
            $method = isset($data->method)?$data->method:'';
            $transid = isset($data->requestParams->transid)?$data->requestParams->transid:'';
            //date('Y-m-d H:i:s',$data->timestamp);
            $sql= 'INSERT INTO incoming (fulltimestamp, paramtimestamp, method, transid,payload) 
                VALUES (now(), :paramtimestamp, :method, :transid, :payload)';
            $stmt = $this->conn->prepare( $sql );
            $stmt->bindValue( "paramtimestamp", $paramdatetime, PDO::PARAM_STR );            
            $stmt->bindValue( "method", $method, PDO::PARAM_STR );
            $stmt->bindValue( "transid", $transid, PDO::PARAM_STR );
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
            case 'openAccount':$response = ( $transaction->OpenAccount($data)); print_r($response); error_log("\r\n".date('Y-m-d H:i:s').' '.$response, 3, "transsetlog.log"); break;
            case 'updateAccount':$response = ($transaction->UpdateAccount($data)); print_r($response); error_log("\r\n".date('Y-m-d H:i:s').' '.$response, 3, "transsetlog.log"); break;
            case 'cashin': $response = ( $transaction->cashin($data)); print_r($response); error_log("\r\n".date('Y-m-d H:i:s').' '.$response, 3, "transsetlog.log"); break;
            case 'payUtility': $response = ( $transaction->payUtility($data)); print_r($response); error_log("\r\n".date('Y-m-d H:i:s').' '.$response, 3, "transsetlog.log"); break;
            case 'fundTransfer': $response = ( $transaction->transferFunds($data)); print_r($response); error_log("\r\n".date('Y-m-d H:i:s').' '.$response, 3, "transsetlog.log"); break;
            case 'nameLookup':$response = ($transaction->NameLookup($data)); print_r($response); error_log("\r\n".date('Y-m-d H:i:s').' '.$response, 3, "transsetlog.log"); break;
            case 'transactionLookup': $response = ($transaction->TransactionLookup($data)); print_r($response); error_log("\r\n".date('Y-m-d H:i:s').' '.$response, 3, "transsetlog.log"); break;
            case 'reserveAccount': $response = ( $transaction->reserveAccount($data)); print_r($response); error_log("\r\n".date('Y-m-d H:i:s').' '.$response, 3, "transsetlog.log"); break;
            case 'unReserveAccount': $response = ( $transaction->unReserveAccount($data)); print_r($response); error_log("\r\n".date('Y-m-d H:i:s').' '.$response, 3, "transsetlog.log"); break;
            case 'changeStatus': $response = ( $transaction->updateAccountStatus($data)); print_r($response); error_log("\r\n".date('Y-m-d H:i:s').' '.$response, 3, "transsetlog.log"); break;
            case 'requestCard': $response = ( $transaction->requestCard($data)); print_r($response); error_log("\r\n".date('Y-m-d H:i:s').' '.$response, 3, "transsetlog.log"); break;
            case 'search': $response = ( $transaction->search($data)); print_r($response);  break;
            //case 'exGratiaPayments': $response = ( $transaction->ExGratiaPayments($data)); print_r($response); error_log("\r\n".date('Y-m-d H:i:s').' '.$response, 3, "transsetlog.log"); break;
            case 'checkBalance': $response = ( $transaction->checkBalance($data)); print_r($response); error_log("\r\n".date('Y-m-d H:i:s').' '.$response, 3, "transsetlog.log"); break;
            case 'getStatement': $response = ( $transaction->getStatement($data)); print_r($response); error_log("\r\n".date('Y-m-d H:i:s').' '.$response, 3, "transsetlog.log"); break;
            default: return json_encode($response = ["transid"=>"","reference"=>"","responseCode"=>"404","Message"=>["status"=>"ERROR","method"=>"","data"=>"invalid command method found: ".$method]]);
            
            
        }

    }
}

?>