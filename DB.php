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
        if(!$this->conn){ 

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
        return self::$conn; 


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
        try{
            if( $dt == date('Y-m-d H:i:s',strtotime($dt)) ){
                // date is in fact in one of the above formats
                
                return $dt;
            }
            else
            { 
                return gmdate('Y-m-d H:i:s',$dt);
            }
        }
        catch (TypeError $error) {
            return $error->getMessage();
        }
        catch(Exception $e){
            return date('Y-m-d H:i:s');
        }
       
    }

    public static function toDate(int $dt){ 
       
       $ddate = date('Y-m-d',$dt);
       return $ddate;
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
        catch(PDOException $e) { 
             
            //$stmt->rollback(); 
            return ( "Error!: " . $e->getMessage());          
        } 
        catch(Execption $e ) { 
            //print "Error!: " . $e->getMessage() . $sql. "</br>".$data; 
        } 
        return false;
    }
    /*public function get_msisdn($accountNo){

        $sql ="select msisdn from accountProfile where accountNo ='$accountNo' ";
    
        $stmt = $this->conn->prepare( $sql );
        $stmt->execute();
        $result = $stmt->fetchColumn();
    
        return $result;
    
    }*/
    public function transaction($data, $method){
        $mnos = array("AMCASHIN" => "Airtel Money", "TPCASHIN"  =>  "Tigo Pesa","VMCASHIN"  =>  "Vodacom M-pesa","EZCASHIN"  =>  "Zantel EzyPesa","SPCASHIN"  =>  "Selcom Card");
        $transaction = new Transactions();
        
        switch($method){
            case 'openAccount':$response = ( $transaction->OpenAccount($data)); return ($response);  break;
            case 'updateAccount':$response = ($transaction->UpdateAccount($data)); return ($response);  break;
            case 'addCash': $response = ( $transaction->addCash($data)); return ($response);  break;
            case 'payUtility': $response = ( $transaction->payutility($data)); return ($response);  break;
            case 'fundTransfer': $response = ( $transaction->transferFunds($data)); return ($response);  break;
            case 'fundTransferBank': $response = ( $transaction->transferFundsBank($data)); return ($response);  break;
            case 'fundTransferWallet': $response = ( $transaction->fundTransferWallet($data)); return ($response);  break;            
            case 'nameLookup':$response = ($transaction->NameLookup($data)); return ($response);  break;
            case 'transactionLookup': $response = ($transaction->TransactionLookup($data)); return ($response);  break;
            case 'linkAccount': $response = ( $transaction->linkAccount($data)); return ($response);  break;
            case 'unLinkAccount': $response = ( $transaction->unLinkAccount($data)); return ($response);  break;
            case 'changeStatus': $response = ( $transaction->changeStatus($data)); return ($response);  break;
            case 'requestCard': $response = ( $transaction->requestCard($data)); return ($response);  break;
            case 'search': $response = ( $transaction->search($data, 'req')); return ($response);  break;
            case 'checkBalance': $response = ( $transaction->checkBalance($data)); return ($response);  break;
            case 'getStatement': $response = ( $transaction->getStatement($data)); return ($response);  break;
            case 'cashout': $response = ( $transaction->cashout($data)); return ($response);  break;
            case 'reverseTransaction': $response = ( $transaction->reverseTransaction($data)); return ($response);  break;
            case 'sendReverseTransactionNotification': $response = ( $transaction->sendReverseTransactionNotification($data)); return ($response);  break;
            case 'sendVoucherNotification': $response = ( $transaction->sendVoucherNotification($data)); return ($response);  break;
            case 'freezeFunds': $response = ( $transaction->freezeFunds($data)); return ($response);  break;
            case 'unFreezeFunds': $response = ( $transaction->unFreezeFunds($data)); return ($response);  break;
            case 'pullFromCard': $response = ( $transaction->pullFromCard($data)); return ($response);  break;
            default: 
            $message = array();
            $message['status']="ERROR";
            $message['method']=$method ;
            $result['resultcode'] = '078';
            $result['result']='invalid method';
            $message['data']=$result;
            $respArray = ['transid'=>'','reference'=>'','responseCode' => 401, "Message"=>($message)];
            error_log("\r\n".date('Y-m-d H:i:s').' '.$_SERVER['REMOTE_ADDR'].' '.json_encode($respArray), 3, "transsnet.log"); print_r(json_encode($respArray));
             
            
        }

    }
}

?>