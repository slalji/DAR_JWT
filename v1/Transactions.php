<?php
require_once ("config.php");
require_once ("selcom.card.dbhandler.php");


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
        $this->reference = DB::getToken(12);//rand(100000000000,999999999999);
       /* date_default_timezone_set('Africa/Dar_es_Salaam');
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
        */
        $this->conn = DB::getInstance();
    }
    public function _pdoBindArray(&$poStatement, &$paArray){
       
       
        foreach ($paArray as $k=>$v){
              
            @$poStatement->bindValue(':'.$k,$v);
           
      
        } // foreach
        return $poStatement;
      }
    public function _updateAccountProfile($data){
        $payload = (array)$data;
        $arr = null;
        unset($payload['transid']);
        unset($payload['customerNo']);
        try{
            
            foreach($payload as $key => $val){
                
                    $arr.=$key . '=:'.$key.', ';                    
            }
           
            $arr = rtrim($arr,', ');
           
              
            $sql ="UPDATE accountprofile SET $arr where customerNo = '".$data->customerNo."'";
           
            $stmt = $this->conn->prepare( $sql );
            $state = $this->_pdoBindArray($stmt,$payload);            
            $state->execute();
        }catch (Exception $e) {
            
            $message = array();
            $message['status']="ERROR";
            $message['method']='Transaction error at: updateAccount '.$e->getMessage()." : ".$sql;
            
            $respArray = ['transid'=>$data->transid,$this->reference,'responseCode' => 501, "Message"=>($message)];
        }
    }
    public function _addAccountProfile($data){
        //unset($data['transid']);
        $profile = array();
        $profile['firstName']=isset($data['firstName'])?$data['firstName']:'';
        $profile['lastName']=isset($data['lastName'])?$data['lastName']:'';
        $profile['gender']=isset($data['gender'])?$data['gender']:'';
        $profile['customerNo']=isset($data['customerNo'])?$data['customerNo']:'';
        $profile['accountNo']=isset($data['card'])?$data['accountNo']:'';
        $profile['msisdn']=isset($data['msisdn'])?$data['msisdn']:'';
        $profile['email']=isset($data['email'])?$data['email']:'';
        $profile['status']=isset($data['status'])?$data['status']:0;
        $profile['addressLine1']=isset($data['addressLine1'])?$data['addressLine1']:'';
        $profile['addressCity']=isset($data['addressCity'])?$data['addressCity']:'';
        $profile['addressCountry']=isset($data['email'])?$data['email']:'';
        $profile['dob']=isset($data['dob'])?$data['dob']:'';
        $profile['currency']=isset($data['currency'])?$data['currency']:'TZS';
        $profile['state']=isset($data['state'])?$data['state']:1; //acive=1 closed=0
        $profile['active']=isset($data['active'])?$data['active']:0; //acive=0 closed=1
        $profile['nationality']=isset($data['nationality'])?$data['nationality']:'';
        $profile['balance']=0;
       
        $cols = null;
        $vals = null;
        try{
            
            foreach($profile as $key => $val){
                
                    $cols.=$key.', ';
                    $vals.=':'.$key.', ';
            }
           
            $cols = rtrim($cols,', ');
            $vals = rtrim($vals,', ');
           
            $sql ="INSERT INTO accountprofile (".$cols.") VALUES (".$vals.")";
            $stmt = $this->conn->prepare( $sql );
            $state = $this->_pdoBindArray($stmt,$profile);            
            $state->execute();
        }
        catch (Exception $e) {
           
            $message = array();
            $message['status']="ERROR";
            $message['message']='Transaction error at: addaccountProfile '.$e->getMessage()." : ".$sql;
            
            $error = 'Transaction error at: addaccountProfile '.$e->getMessage();
            throw new Exception($error);
        }
        $error = 'Transaction error at: addaccountProfile ';//.$e->getMessage();
        //throw new Exception($error);
        return false;
        
    }
    public function _addCard($data)   {
        //die(print_r('here'));
        /*
        fulltimestamp create
        Name concat fname lname
        Msisdn required
        Card required
        Status default = 0         
        Dealer = Selcom
        Reference create
        Email if, else ''
        Phone if, else ''
        language default

        */
       #old
		// review active=1 here, we need to update once user has verified
        // $query = "UPDATE card SET msisdn='$msisdn', status='1', fulltimestamp=NOW(),registeredby='SYSTEM', confirmedby='SYSTEM', registertimestamp = NOW(), confirmtimestamp=NOW(), active='0' WHERE card='$cardnum'";

		// $this->pdo_db->query($query);

		// generate sms code
        $request = (array)$data;
        $payload = array();
        $payload['fulltimestamp'] = $request['fulltimestamp'];
        $payload['name'] = $request['firstName']. ' '.$request['lastName'];
        $payload['msisdn'] = $request['msisdn'];
        $payload['card'] = isset($request['card'])?$request['card']:'';
        $payload['dealer'] = 'Selcom';
        $payload['registeredby'] = 'SelcomTranssetAPI';
        $payload['confirmedby'] = 'SelcomTranssetAPI';
        $payload['registertimestamp'] =$request['fulltimestamp'];
        $payload['confirmtimestamp'] = $request['fulltimestamp'];
        $payload['active'] = 1;
        $payload['status'] = 1;        
        $payload['reference'] = $this->reference;//request['reference'];
        $payload['email'] = isset($request['email'])?$request['email']:'';
        $payload['phone'] = isset($request['phone'])?$request['phone']:'';
        //$payload['message'] = $request['message'];       
        
       
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
            $state = $this->_pdoBindArray($stmt,$payload);            
            $state->execute();
            $id = $this->conn->lastInsertId();
           
            $message = array();
            $message['status']="SUCCESS";
            //$message['message']="add Card";
            return $id;
           
            //$respArray = ['transid'=>$data->transid,'reference'=>$this->reference,'responseCode' => 200, "Message"=>($message)];
        }
        catch (Exception $e) {
           
            $message = array();
            $message['status']="ERROR";
            $message['message']='Transaction error at: addcard '.$e->getMessage()." : ".$sql;
            
            $error = 'Transaction error at: addcard '.$e->getMessage();
            throw new Exception($error);
        }
        $error = 'Transaction error at: addcard '.$e->getMessage();
        throw new Exception($error);
        return -1;
        
    }
    public function _addTransaction($data)   {
        /*
        fulltimestamp same as card	
        transid required	
        reference	same as card
        msisdn	required
        message	openAccount
        */
        
        /*$payload = (array)$data;
        $payload = array();
        $payload['fulltimestamp'] = $data['fulltimestamp'];
        $payload['transid'] = $data['transid'];
        $payload['reference'] = $data['reference'];
        $payload['card'] = isset($data['card'])?$data['card']:'';
        $payload['msisdn'] = isset($data['msisdn'])?$data['msisdn']:'';
        $payload['message'] = $data['message'];
        $payload['channel'] = 'TPAY';
        
        $payload['card']=isset($data['customerNo'])?$data['customerNo']:'';
        $payload['message']=isset($data['message'])?$data['message']:'';
        $payload['utilityref']=isset($data['toAccountNo'])?$data['toAccountNo']:'';
        //unset($data['customerNo']);    
        unset($data['toAccountNo']);  
        unset($data['currency']);
        //unset($data['method']); 
        unset($data['transid']);
        unset($data['transType']);  //add this to another table say transsnet_log  
         */
        $cols = null;
        $vals = null;
        try{
            foreach($data as $key => $val){
                
                $payload[$key]=$val;
              
            }
            $payload['channel'] = 'TPAY';
            foreach($payload as $key => $val){
                
                    $cols.=$key.', ';
                    $vals.=':'.$key.', ';
            }
            $cols = rtrim($cols,', ');
            $vals = rtrim($vals,', ');
           
            $sql ="INSERT INTO transaction (".$cols.") VALUES (".$vals.")";
            $stmt = $this->conn->prepare( $sql );
            $state = $this->_pdoBindArray($stmt,$payload);            
            $state->execute();
           
            $message = array();
            $message['status']="SUCCESS";
            $message['method']="_addTransaction";
            return true;
           
            //$respArray = ['transid'=>$data->transid,'reference'=>$this->reference,'responseCode' => 200, "Message"=>($message)];
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
        return false;
        
        
    }
    public function openAccount($data)   {
        //check for required fields eg accountNo, msisdn, fname lname
        $err = Validate::openAccount($data);
        if (!empty($err))
            return DB::getErrorResponse($data, $err, $this->reference);
        try{

            $payload = (array)$data;    
            $payload['accountNo'] = 'TPAY'.DB::getToken(12);       
            $dob=DB::toDate($payload['dob']);                        
            $payload['reference']=$this->reference;
            $payload['fulltimestamp'] = date('Y-m-d H:i:s');
            //$payload['transid'] = $data->transid;
            //$payload['message'] = 'openAccount';
            //unset($payload['transid']);
            

            //add to card DB
            $last_id = $this->_addCard($payload);
            if ($last_id < 0){
                $error = 'Transaction error at: _addCard '.$e->getMessage();
                throw new Exception($error);
            }
            
            //add to transactions DB
            //$this->addTransaction($payload);
            $payload['accountNo'] = $last_id;
            //add to accountProfile DB
            $this->_addAccountProfile($payload);
            $message = array();
            $message['status']="SUCCESS";
            $message['method']="openAccount";
            $message['data']=$payload;
            
            $respArray = ['transid'=>$data->transid,'reference'=>$payload['reference'],'responseCode' => 200, "Message"=>($message)];
        }
        catch (Exception $e) {
            
            $message = array();
            $message['status']="ERROR";
            $message['method']='Transaction error at: openAccount '.$e->getMessage()." : ".$sql;
            
            $respArray = ['transid'=>$data->transid,$this->reference,'responseCode' => 501, "Message"=>($message)];
        }
        
        return (json_encode($respArray));
    }
    public function updateAccount($data)   {
        //check for required fields eg accountNo, transid, fname lname
        //Validate::openAccount($data);
        $payload = (array)$data;
        
        //update accountProfile DB
        $this->_updateAccountProfile($data);
           

            $payload['reference']=$this->reference;//rand(10000000000,9999999999);
            $payload['fulltimestamp'] = date('Y-m-d H:i:s');
            //$payload['transid'] = $data->transid;
            $payload['method'] = 'updateAccount';
            unset($payload['transid']) ;
            //add to card DB updateCard ?
            //$this->updateCard($payload);
            

            //add to transactions DB
           /* $this->addTransaction($payload);
            $resdata = $payload;
            unset($resdata['reference']);
            unset($resdata['fulltimestamp']);
            unset($resdata['transid']);
            $message = array();
            $message['status']="SUCCESS";
            $message['data']=$resdata;
        */
            $message = array();
            $message['status']="SUCCESS";
            $message['method']="updateAccount";
            $message['data']=$payload;
            $respArray = ['transid'=>$data->transid,'reference'=>$payload['reference'],'responseCode' => 200, "Message"=>($message)];
        
        return (json_encode($respArray));
    }

    public function nameLookup($data)   {
        //check for required fields eg transid return accountprofile info of this transid 
        //Validate::nameLookup($data);
        //add to transaction
        //update table card balance =+ amount
        $payload = (array)$data;    
        
        $arr = null;
        //select * from accountprofile join transaction on accountprofile.card = transaction.card where transid = 'TPAY01052018161000'
        try{
             
            $sql ="select firstName, lastName, email, msisdn, accountNo, customerNo, addressLine1, addressCity, addressCountry, dob, currency, gender, nationality from accountprofile where customerNo ='".$data->customerNo."'";
           
            $stmt = $this->conn->prepare( $sql );
            $state = $this->_pdoBindArray($stmt,$payload);            
            $state->execute();


            $payload['reference']=$this->reference;//DB::getToken(19);//rand(10000000000,9999999999);
           
            $payload['fulltimestamp'] = date('Y-m-d H:i:s');
            $payload['transid'] = $data->transid;
            $payload['message'] = 'nameLookup';
            //add to card DB updateCard ?
            //$this->updateCard($payload);
            

            //add to transactions DB
            //$this->_addTransaction($payload); no money movement therefore nothing to trans table
            $result = $state->fetchAll(PDO::FETCH_ASSOC);
            $json = json_encode($result);
            
            $message = array();
            $message['status']="SUCCESS";
            $message['method']="nameLookup";
            $message['data']=$result;
        
            $respArray = ['transid'=>$data->transid,'reference'=>$payload['reference'],'responseCode' => 200, "Message"=>($message)];
        }
        catch (Exception $e) {
            
            $message = array();
            $message['status']="ERROR";
            $message['method']='Transaction error at: nameLookup '.$e->getMessage()." : ".$sql;
            
            $respArray = ['transid'=>$data->transid,$payload['reference'],'responseCode' => 501, "Message"=>($message)];
        }
        return (json_encode($respArray));
    }
    public function transactionLookup($data)   {
        //check for required fields eg transid return accountprofile info of this transid 
        Validate::transactionLookup($data);
        //add to transaction
        //update table card balance =+ amount
        $payload = (array)$data;    
       
        $arr = null;
        //select * from accountprofile join transaction on accountprofile.card = transaction.card where transid = 'TPAY01052018161000'
        try{
             /*transRef is the needle transid */
            $sql ="select * from transaction where transid = '".$data->transRef."'";
          
            $stmt = $this->conn->prepare( $sql );
            $state = $this->_pdoBindArray($stmt,$payload);            
            $state->execute();            
           
            $payload['reference']=$this->reference;//DB::getToken(19);//rand(10000000000,9999999999);
          
            $payload['fulltimestamp'] = date('Y-m-d H:i:s');
            $payload['transid'] = $data->transid;
            $payload['message'] = 'transactionLookup';
            //add to card DB updateCard ?
            //$this->updateCard($payload);
            

            //add to transactions DB no money exchanged, no entry to trans DB
            //$this->addTransaction($payload);

            $result = $state->fetchAll(PDO::FETCH_ASSOC);
            $json = json_encode($result);
           
            $message = array();
            $message['status']="SUCCESS";
            $message['method']="transactionLookup";
            $message['data']=$result;
        
            $respArray = ['transid'=>$data->transid,'reference'=>$payload['reference'],'responseCode' => 200, "Message"=>($message)];
        }
        catch (Exception $e) {
            
            $message = array();
            $message['status']="ERROR";
            $message['method']='Transaction error at: transactionLookup '.$e->getMessage()." : ".$sql;
            
            $respArray = ['transid'=>$data->transid,$payload['reference'],'responseCode' => 501, "Message"=>($message)];
        }
        return (json_encode($respArray));
    }
    public function transferFunds($data)   {
        //check for required fields eg transid return accountprofile info of this transid 
        Validate::transferFunds($data);
        //add to transaction
        //update table card balance =+ amount
        $payload = (array)$data;    
       
        $arr = null;
        //select * from accountprofile join transaction on accountprofile.card = transaction.card where transid = 'TPAY01052018161000'
        try{
             
           
            $payload['reference']=$this->reference;//DB::getToken(19);//rand(10000000000,9999999999);
            $payload['utilityref'] = $payload['toAccountNo'];
            unset($payload['toAccountNo']);
            unset($payload['currency']);
            unset($payload['transid']);
            $payload['fulltimestamp'] = date('Y-m-d H:i:s');
            $payload['transid'] = $data->transid;
            $payload['message'] = 'transferFunds';
            //add to card DB updateCard ?
            //$this->updateCard($payload);          

           
            
           $selcom = new DbHandler();
           $result = $selcom->p2p($payload['transid'],$payload['reference'],$payload['utilityref'], $payload['msisdn'],$payload['amount']);
            //add to transactions DB do not add to trans db till function performed eg transferFunds
           //$this->_addTransaction($payload);
           /*if ($result['resultcode'] == '056'){
               //card not found, send voucher code instead
           }               
           else{*/

            $message = array();
            $message['status']="ERROR";
            $message['method']="transferFunds";
            $message['data']=$result;
        
            $respArray = ['transid'=>$data->transid,'reference'=>$payload['reference'],'responseCode' => 500, "Message"=>($message)];
            
                //die( json_encode($result));;
            /*$sql ="select fulltimestamp, msisdn, amount, message, comments, status, channel from transaction where transid = '".$data->transid."'";
          
            $stmt = $this->conn->prepare( $sql );
            $state = $this->_pdoBindArray($stmt,$payload);            
            $state->execute();            
            
            $result = $state->fetchAll(PDO::FETCH_ASSOC);
            
           
            $message = array();
            $message['status']="SUCCESS";
            $message['method']="transferFunds";
            $message['data']=$result;
        
            $respArray = ['transid'=>$data->transid,'reference'=>$payload['reference'],'responseCode' => 200, "Message"=>($message)];
            */
           //}
        }
        catch (Exception $e) {
            
            $message = array();
            $message['status']="ERROR";
            $message['method']='Transaction error at: transferFunds '.$e->getMessage()." : ".$sql;
            
            $respArray = ['transid'=>$data->transid,$payload['reference'],'responseCode' => 501, "Message"=>($message)];
        }
        return (json_encode($respArray));
    }

}
