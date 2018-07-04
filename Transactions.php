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
      }
    public function addCard($data)   {
        /*
        fulltimestamp create
        Name concat fname lname
        Msisdn required
        Card required
        Status default = 0         
        Dealer = Tpay
        Reference create
        Email if, else ''
        Phone if, else ''
        language default

        */
        $request = (array)$data;
        $payload = array();
        $payload['fulltimestamp'] = $request['fulltimestamp'];
        $payload['name'] = $request['firstName']. ' '.$request['lastName'];
        $payload['msisdn'] = $request['msisdn'];
        $payload['card'] = $request['card'];
        $payload['dealer'] = 'Tpay';
        $payload['reference'] = $request['reference'];
        $payload['email'] = isset($request['email'])?$request['email']:'';
        $payload['phone'] = isset($request['phone'])?$request['phone']:'';
        
       
        $cols = null;
        $vals = null;
        try{
            
            foreach($payload as $key => $val){
                
                    $cols.=$key.', ';
                    $vals.=':'.$key.', ';
            }
            $cols = rtrim($cols,', ');
            $vals = rtrim($vals,', ');
            
            $sql ="INSERT INTO card (".$cols.") VALUES (".$vals.")";
            $stmt = $this->conn->prepare( $sql );
            $state = $this->PDOBindArray($stmt,$payload);            
            $state->execute();
           
            $message = array();
            $message['status']="SUCCESS";
            $message['method']="add Card";
            return true;
           
            //$respArray = ['transId'=>$data->transId,'reference'=>$this->reference,'responseCode' => 200, "Message"=>($message)];
        }
        catch (Exception $e) {
           
            $message = array();
            $message['status']="ERROR";
            $message['method']='Transaction error at: addcard '.$e->getMessage()." : ".$sql;
            
            $error = 'Transaction error at: addcard '.$e->getMessage();
            throw new Exception($error);
        }
        $error = 'Transaction error at: addcard '.$e->getMessage();
        throw new Exception($error);
        return false;
        
    }
    public function addTransaction($data)   {
        /*
        fulltimestamp same as card	
        transid required	
        reference	same as card
        msisdn	required
        message	openAccount
        */
        
        $request = (array)$data;
        $payload = array();
        $payload['fulltimestamp'] = $request['fulltimestamp'];
        $payload['transid'] = $request['transid'];
        $payload['reference'] = $request['reference'];
        $payload['card'] = isset($request['card'])?$request['card']:'';
        $payload['msisdn'] = isset($request['msisdn'])?$request['msisdn']:'';
        $payload['message'] = $request['method'];
              
        $cols = null;
        $vals = null;
        try{
            
            foreach($payload as $key => $val){
                
                    $cols.=$key.', ';
                    $vals.=':'.$key.', ';
            }
            $cols = rtrim($cols,', ');
            $vals = rtrim($vals,', ');
            
            $sql ="INSERT INTO transaction (".$cols.") VALUES (".$vals.")";
            $stmt = $this->conn->prepare( $sql );
            $state = $this->PDOBindArray($stmt,$payload);            
            $state->execute();
           
            $message = array();
            $message['status']="SUCCESS";
            $message['method']="add Card";
            return true;
           
            //$respArray = ['transId'=>$data->transId,'reference'=>$this->reference,'responseCode' => 200, "Message"=>($message)];
        }
        catch (Exception $e) {
            
            $message = array();
            $message['status']="ERROR";
            $message['method']='Transaction error at: addTransaction '.$e->getMessage()." : ".$sql;
            $error = 'Transaction error at: addTransaction '.$e->getMessage();
            throw new Exception($error);
        }
        $error = 'Transaction error at: addTransaction '.$e->getMessage().' '.$sql;;
        throw new Exception($error);
        
        
    }
    public function openAccount($data)   {
        //check for required fields eg accountNo, msisdn, fname lname
        //Validate::openAccount($data);
        $payload = (array)$data;    
        $payload['card'] = rand(10000000000,9999999999);
        $payload['dob'] = $ymd = DateTime::createFromFormat('mdY', $payload['dob'])->format('Y-m-d');
        unset($payload['transId']);
        $cols = null;
        $vals = null;
        try{
            
            foreach($payload as $key => $val){
                
                    $cols.=$key.', ';
                    $vals.=':'.$key.', ';
            }
            $cols = rtrim($cols,', ');
            $vals = rtrim($vals,', ');
            
            $sql ="INSERT INTO account (".$cols.") VALUES (".$vals.")";
            $stmt = $this->conn->prepare( $sql );
            $state = $this->PDOBindArray($stmt,$payload);            
            $state->execute();

            $payload['reference']=rand(10000000000,9999999999);
            $payload['fulltimestamp'] = date('Y-m-d H:i:s');
            $payload['transid'] = $data->transId;
            $payload['method'] = 'openAccount';
            //add to card DB
            $this->addCard($payload);
           

            //add to transactions DB
             $this->addTransaction($payload);

            $message = array();
            $message['status']="SUCCESS";
            $message['method']="openAccount";
           
            $respArray = ['transId'=>$data->transId,'reference'=>$this->reference,'responseCode' => 200, "Message"=>($message)];
        }
        catch (Exception $e) {
            
            $message = array();
            $message['status']="ERROR";
            $message['method']='Transaction error at: openAccount '.$e->getMessage()." : ".$sql;
            
            $respArray = ['transId'=>$data->transId,$this->reference,'responseCode' => 501, "Message"=>($message)];
        }
        return ($respArray);
    }
    public function updateAccount($data)   {
        //check for required fields eg accountNo, transId, fname lname
        //Validate::openAccount($data);
        $payload = (array)$data;    
        
        $arr = null;
        unset($payload['transId']);
        unset($payload['accountNo']);
        try{
            
            foreach($payload as $key => $val){
                
                    $arr.=$key . '=:'.$key.', ';                    
            }
           
            $arr = rtrim($arr,', ');
           
              
            $sql ="UPDATE account SET $arr where accountNo = '".$data->accountNo."'";
           
            $stmt = $this->conn->prepare( $sql );
            $state = $this->PDOBindArray($stmt,$payload);            
            $state->execute();

            $payload['reference']=rand(10000000000,9999999999);
            $payload['fulltimestamp'] = date('Y-m-d H:i:s');
            $payload['transid'] = $data->transId;
            $payload['method'] = 'updateAccount';
            //add to card DB updateCard ?
            //$this->updateCard($payload);
            

            //add to transactions DB
            $this->addTransaction($payload);
           
            $message = array();
            $message['status']="SUCCESS";
            $message['method']="updateAccount";
        
            $respArray = ['transId'=>$data->transId,'reference'=>$this->reference,'responseCode' => 200, "Message"=>($message)];
        }
        catch (Exception $e) {
            
            $message = array();
            $message['status']="ERROR";
            $message['method']='Transaction error at: updateAccount '.$e->getMessage()." : ".$sql;
            
            $respArray = ['transId'=>$data->transId,$this->reference,'responseCode' => 501, "Message"=>($message)];
        }
        return ($respArray);
    }
    


    public function nameLookup($data)   {
        //check for required fields eg transId return accountInfo of this transId 
        //Validate::accountLookup($data);
        //add to transaction
        //update table card balance =+ amount
        $payload = (array)$data;    
        
        $arr = null;
        //select * from account join transaction on account.card = transaction.card where transid = 'TPAY01052018161000'
        try{
            
            /*foreach($payload as $key => $val){
                
                    $arr.=$key . '=:'.$key.', ';                    
            }
           
            $arr = rtrim($arr,', ');
           
            */  
            $sql ="select a.firstName, a.lastName, a.email, a.msisdn, a.card, a.accountNo, a.dob, a.currency from account a  join transaction on a.card = transaction.card where transid = '".$data->transId."'";
           
            $stmt = $this->conn->prepare( $sql );
            $state = $this->PDOBindArray($stmt,$payload);            
            $state->execute();


            $payload['reference']=rand(10000000000,9999999999);
            $payload['fulltimestamp'] = date('Y-m-d H:i:s');
            $payload['transid'] = $data->transId;
            $payload['method'] = 'accountLookup';
            //add to card DB updateCard ?
            //$this->updateCard($payload);
            

            //add to transactions DB
            $this->addTransaction($payload);
            $result = $state->fetchAll(PDO::FETCH_ASSOC);
            $json = json_encode($result);
            
            $message = array();
            $message['status']="SUCCESS";
            $message['method']="nameLookup";
            $message['data']=$json;
        
            $respArray = ['transId'=>$data->transId,'reference'=>$this->reference,'responseCode' => 200, "Message"=>($message)];
        }
        catch (Exception $e) {
            
            $message = array();
            $message['status']="ERROR";
            $message['method']='Transaction error at: nameLookup '.$e->getMessage()." : ".$sql;
            
            $respArray = ['transId'=>$data->transId,$this->reference,'responseCode' => 501, "Message"=>($message)];
        }
        return ($respArray);
    }
    
    //Transaction Lookup

}
