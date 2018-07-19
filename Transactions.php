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
        $this->reference = DB::getToken(12);
       
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
        $customer = $this->_getAccountNo($data['customerNo']);
        //die($customer.' : '.$data['customerNo']);
        $transid=$data['transid'];
        unset($payload['transid']);
        unset($payload['customerNo']);
        try{
            
            foreach($payload as $key => $val){
                
                    $arr.=$key . '=:'.$key.', ';                    
            }
           
            $arr = rtrim($arr,', ');
           
             
            $sql ="UPDATE accountprofile SET $arr where accountNo = '".$customer."'";
                        
            $stmt = $this->conn->prepare( $sql );
            $state = $this->_pdoBindArray($stmt,$payload);             
            $state->execute();            
            return  $customer;
           

        }catch (Exception $e) {
            
            $message = array();
            $message['status']="ERROR";
            $message['method']='Transaction error at: updateAccount '.$e->getMessage()." : ".$sql;
            
            $respArray = ['transid'=>$data['transid'],$this->reference,'responseCode' => 501, "Message"=>($message)];
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
            $message['message']='Transaction error at: _addAccountProfile '.$e->getMessage()." : ".$sql;
            
            $error = 'Transaction error at: _addAccountProfile '.$e->getMessage();
            throw new Exception($error);
        }
        $error = 'Transaction error at: _addAccountProfile ';//.$e->getMessage();
        //throw new Exception($error);
        return false;
        
    }
    public function _getAccountNo($customerNo){
        
        $sql ="select accountNo from accountProfile where customerNo ='$customerNo' || msisdn ='$customerNo' ";  

        $stmt = $this->conn->prepare( $sql );
        $stmt->execute();            
        $result = $stmt->fetchColumn();
        return $result;           
           
    }
    public function _getSuspense($customerNo){
        //get accountNo;
        
        $accountNo = $this->_getAccountNo($customerNo);

        $sql ="select suspense from card where id ='$accountNo'";  

        $stmt = $this->conn->prepare( $sql );
        $stmt->execute();            
        $result = $stmt->fetchColumn();
        return $result;           
           
    }
    public function _getProfile($accountNo){
        
        $sql ="select * from accountProfile where accountNo ='$accountNo' ";  

        $stmt = $this->conn->prepare( $sql );
        $stmt->execute();            
        $result = $stmt->fetchAll( PDO::FETCH_ASSOC );
        return json_encode($result);
           
    }
    public function _setResponse($method){
        $arr = array();
        $arr['reference']=$this->reference;
        $arr['fulltimestamp'] = date('Y-m-d H:i:s');
        //$arr['transid'] = $data->transid;
        $arr['message'] = $method;
        return $arr;
    }
    public function _addCard($data)   {
        //die(print_r('here'));
        /*
        fulltimestamp create
        Name concat fname lname
        Msisdn required
        Card required
        Status default = 0         
        Dealer = Transsnet
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
        $payload['dealer'] = 'Transset';
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
    public function _checkDupTrans($transid){
        $message = array();
        $message['resultcode']="002";
        $message['result']="Duplicate request";
           
           
        $sql ="select transid from transaction where transid=".$transid;
        $stmt = $this->conn->prepare( $sql );
        $stmt->execute();
        if ($stmt->rowCount() > 0){
            return $message;
        }
            
        else 
            return false;

            
           
    }
    public function openAccount($data)   {
        //check for required fields eg accountNo, msisdn, fname lname
        $err = Validate::openAccount($data); //if 2 MJ then account alread exists status is still 0 change it to one on accountprofile
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
            $last_id = $this->_addCard($payload);// active = 0 not happening
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
        $tier = strtoupper(isset($payload['tier'])?$payload['tier']:$payload['']);
       
        //update accountProfile DB
        $customer = $this->_updateAccountProfile($payload);
        $payload['accountNo']=$customer;
        if(isset($tier)){
       //die('here '.$customer);
            switch($tier){
                case 'B': $tier='B'; break;
                case 'C': $tier='C'; break;
                case 'D': $tier='D'; break;
                default: 'A';
            }
            //$account = isset($payload['customerNo'])?$payload['customerNo']:$payload['msisdn'];
            //$customer = $this->_getAccountNo($account);
            $query = "UPDATE card SET tier='$tier' WHERE id=$customer";
            $this->conn->query($query);
        }
           

        $payload['reference']=$this->reference;
        $payload['fulltimestamp'] = date('Y-m-d H:i:s');
        //$payload['transid'] = $data->transid;
        $payload['method'] = 'updateAccount';
        unset($payload['transid']) ;
    
        $message = array();
        $message['status']="SUCCESS";
        $message['method']="updateAccount";
        $message['data']=$payload;
        $respArray = ['transid'=>$data->transid,'reference'=>$payload['reference'],'responseCode' => 200, "Message"=>($message)];
    
        return (json_encode($respArray));
    }

    public function nameLookup($data)   {
        //check for required fields eg transid return accountprofile info of this transid 
        
        $err = Validate::nameLookup($data);
        if (!empty($err))
            return DB::getErrorResponse($data, $err, $this->reference);
        $payload = (array)$data;    
        $response = $this->_setResponse('nameLookup');
       
        try{
            
            $where = 'where ';             
            $where .= isset($payload['customerNo'])?'customerNo='.$payload['customerNo']:'msisdn='.$payload['msisdn'];
            //$sql ="select firstName, lastName, email, msisdn, accountNo, customerNo, addressLine1, addressCity, addressCountry, dob, currency, gender, nationality from accountprofile ".$where;
            $sql ="select  * from accountprofile ".$where;

            $stmt = $this->conn->prepare( $sql );
            $state = $this->_pdoBindArray($stmt,$payload);            
            $state->execute();            
          
            $result = $state->fetchAll(PDO::FETCH_ASSOC);
            $json = json_encode($result);
            
            $message = array();
            $message['status']="SUCCESS";
            $message['method']="nameLookup";
            $message['data']=$result;
            if (empty($result)){
                $error="account does not exist";
                throw new Exception($error);
            }
            $respArray = ['transid'=>$data->transid,'reference'=>$response['reference'],'responseCode' => 200, "Message"=>($message)];
        }
        catch (Exception $e) {
            
            $message = array();
            $message['status']="ERROR";
            $message['method']='Transaction error at: nameLookup '.$e->getMessage()." : ";//.$sql;
            
            $respArray = ['transid'=>$data->transid,'reference'=>$response['reference'],'responseCode' => 501, "Message"=>($message)];
        }
        return (json_encode($respArray));
    }
    public function transactionLookup($data)   {
        //check for required fields eg transid return accountprofile info of this transid 
        Validate::transactionLookup($data);
        //add to transaction
        //update table card balance =+ amount
        $payload = (array)$data;    
       
       
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
            if (empty($result)){
                $error="transaction does not exist";
                throw new Exception($error);
            }
            $message = array();
            $message['status']="SUCCESS";
            $message['method']="transactionLookup";
            $message['data']=$result;
        
            $respArray = ['transid'=>$data->transid,'reference'=>$payload['reference'],'responseCode' => 200, "Message"=>($message)];
        }
        catch (Exception $e) {
            
            $message = array();
            $message['status']="ERROR";
            $message['method']='Transaction error at: transactionLookup '.$e->getMessage()." : ";//.$sql;
            
            $respArray = ['transid'=>$data->transid,$payload['reference'],'responseCode' => 501, "Message"=>($message)];
        }
        return (json_encode($respArray));
    }
    public function transferFunds($data)   {
        //check for required fields eg transid return accountprofile info of this transid 
        $err = Validate::transferFunds($data);
        if (!empty($err))
            return DB::getErrorResponse($data, $err, $this->reference);        
        $payload = (array)$data;    
        
        try{             
           
            $payload['reference']=$this->reference;
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

            $message = array();
            $message['status']="SUCCESS";
            $message['method']="transferFunds";
            $message['data']=$result;

            $respArray = ['transid'=>$data->transid,'reference'=>$payload['reference'],'responseCode' => 200, "Message"=>($message)];
        
        }
        catch (Exception $e) {
            
            $message = array();
            $message['status']="ERROR";
            $message['method']='Transaction error at: transferFunds '.$e->getMessage()." : ".$sql;
            
            $respArray = ['transid'=>$data->transid,$payload['reference'],'responseCode' => 501, "Message"=>($message)];
        }
        return (json_encode($respArray));
    }
    public function checkBalance($data)   {
        //check for required fields eg transid return accountprofile info of this transid 
        $err = Validate::enquiry($data);
        if (!empty($err))
            return DB::getErrorResponse($data, $err, $this->reference);        
        $payload = (array)$data;    
        
        try{             
           
            $payload['reference']=$this->reference;
            $account = isset($payload['customerNo'])?$payload['customerNo']:$payload['msisdn'];
            unset($payload['transid']);
            $payload['fulltimestamp'] = date('Y-m-d H:i:s');
            $payload['transid'] = $data->transid;
            $payload['message'] = 'checkBalance';
            //add to card DB updateCard ?
            //$this->updateCard($payload);          

                    
                        
            $selcom = new DbHandler();
            $result = $selcom->balanceEnquiry($account);
            foreach ($result as $key => $val){
                $payload[$key]=$val;
            }
            
            $message = array();
            $message['status']="SUCCESS";
            $message['method']="checkBalance";
            $message['data']=$payload;

            $respArray = ['transid'=>$data->transid,'reference'=>$payload['reference'],'responseCode' => 200, "Message"=>($message)];
        
        }
        catch (Exception $e) {
            
            $message = array();
            $message['status']="ERROR";
            $message['method']='Transaction error at: checkBalance '.$e->getMessage()." : ".$sql;
            
            $respArray = ['transid'=>$data->transid,$payload['reference'],'responseCode' => 501, "Message"=>($message)];
        }
        return (json_encode($respArray));
    }
    public function getStatement($data)   {
        //check for required fields eg transid return accountprofile info of this transid 
        $err = Validate::enquiry($data);
        if (!empty($err))
            return DB::getErrorResponse($data, $err, $this->reference);        
        $payload = (array)$data;    
        
        try{             
           
            $payload['reference']=$this->reference;
            $account = isset($payload['customerNo'])?$payload['customerNo']:$payload['msisdn'];
            $days = isset($payload['days'])?$payload['days']:3;
            unset($payload['transid']);
            $payload['fulltimestamp'] = date('Y-m-d H:i:s');
            $payload['transid'] = $data->transid;
            $payload['message'] = 'getStatement';
            //add to card DB updateCard ?
            //$this->updateCard($payload);          

                    
                        
            $selcom = new DbHandler();
            $result = $selcom->statementEnquiry($account,$days);
           
            foreach ($result as $key => $val){
                $payload[$key]=$val;
            }
            
            $message = array();
            $message['status']="SUCCESS";
            $message['method']="getStatement";
            $message['data']=$payload;

            $respArray = ['transid'=>$data->transid,'reference'=>$payload['reference'],'responseCode' => 200, "Message"=>($message)];
        
        }
        catch (Exception $e) {
            
            $message = array();
            $message['status']="ERROR";
            $message['method']='Transaction error at: getStatement '.$e->getMessage()." : ".$sql;
            
            $respArray = ['transid'=>$data->transid,$payload['reference'],'responseCode' => 501, "Message"=>($message)];
        }
        return (json_encode($respArray));
    }
    public function updateAccountStatus($data){
        //check for required fields eg transid return accountprofile info of this transid 
       $err = Validate::accountState($data);
        if (!empty($err))
            return DB::getErrorResponse($data, $err, $this->reference);        
        $payload = (array)$data; 
         
                 
        $payload = (array)$data;
        $payload['reference']=$this->reference;  
        $account = isset($payload['customerNo'])?$payload['customerNo']:$payload['msisdn'];
        $customer = $this->_getAccountNo($account);
        $active = isset($payload['status']) && $payload['status'] === 'open'?'0':'1';
        $payload['accountNo']=$customer;
        unset( $payload['transid']);
                   
        try{
            $sql = "UPDATE card SET active='".$active."' where id='".$customer."'; ";
            $sql2 =" UPDATE accountprofile SET active='".$active."' where accountNo='".$customer."'";
            
            $stmt = $this->conn->query($sql);
            $stmt = $this->conn->query($sql2);

            //$result = $this->_getProfile($customer);
           

            $message = array();
            $message['status']="SUCCESS";
            $message['method']="getStatement";
            $message['data']=$payload;

            $respArray = ['transid'=>$data->transid,'reference'=>$payload['reference'],'responseCode' => 200, "Message"=>($message)];
        
           

        }catch (Exception $e) {
            
            $message = array();
            $message['status']="ERROR";
            $message['method']='Transaction error at: updateAccountStatus '.$e->getMessage()." : ".$sql;
            
            $respArray = ['transid'=>$data->transid,$this->reference,'responseCode' => 501, "Message"=>($message)];
        }
        return json_encode($respArray);
    }
    public function requestCard($data){
        //check for required fields eg transid return accountprofile info of this transid 
       /*$err = Validate::accountState($data);
        if (!empty($err))
            return DB::getErrorResponse($data, $err, $this->reference);        
        $payload = (array)$data; 
        */ 
                 
        $payload = (array)$data;
        $payload['reference']=$this->reference;  
        $account = isset($payload['customerNo'])?$payload['customerNo']:$payload['msisdn'];
        $customer = $this->_getAccountNo($account);
        $profile = json_decode($this->_getProfile($customer), true);
        $msisdn = $profile[0]['msisdn'];
        $name =  $profile[0]['firstName'].' '. $profile[0]['lastName'];
        
      
        $card = "TPAY";
        $pin = '1234';
        $confirmPin = '1234';

        $payload['accountNo']=$customer;
        unset( $payload['transid']);
                   
        try{
           
            $selcom = new DbHandler();
            $result = $selcom->activateCard($card,$msisdn,$name,$pin,$confirmPin);
           

            $message = array();
            $message['status']="SUCCESS";
            $message['method']="requestCard";
            $message['data']=$result;

            $respArray = ['transid'=>$data->transid,'reference'=>$payload['reference'],'responseCode' => 200, "Message"=>($message)];
        
           

        }catch (Exception $e) {
            
            $message = array();
            $message['status']="ERROR";
            $message['method']='Transaction error at: requestCard '.$e->getMessage()." : ".$sql;
            
            $respArray = ['transid'=>$data->transid,$this->reference,'responseCode' => 501, "Message"=>($message)];
        }
        return json_encode($respArray);
    }
    public function cashin($data){
        //check for required fields eg transid return accountprofile info of this transid 
       /*$err = Validate::accountState($data);
        if (!empty($err))
            return DB::getErrorResponse($data, $err, $this->reference);        
        $payload = (array)$data; 
        */ 
                 
        $payload = (array)$data;
        $payload['reference']=$this->reference;  
        $account = isset($payload['customerNo'])?$payload['customerNo']:$payload['msisdn'];
        $customer = $this->_getAccountNo($account);
        $profile = json_decode($this->_getProfile($customer), true);
        $msisdn_acct = $profile[0]['msisdn'];
        $name =  $profile[0]['firstName'].' '. $profile[0]['lastName']; 
        $amount = $payload['amount'];
        $transid = $payload['transid'];
        $msisdn = $payload['msisdn'];   
        

        $payload['accountNo']=$customer;
        unset( $payload['transid']);
                   
        try{
           
            $selcom = new DbHandler();
            $result = $selcom->utilityPayment($transid,'SPCASHIN',$msisdn_acct,$msisdn,$amount);            
           

            $message = array();
            $message['status']="SUCCESS";
            $message['method']="cashin";
            $message['data']=$result;

            $respArray = ['transid'=>$data->transid,'reference'=>$payload['reference'],'responseCode' => 200, "Message"=>($message)];
        
           

        }catch (Exception $e) {
            
            $message = array();
            $message['status']="ERROR";
            $message['method']='Transaction error at: cashin '.$e->getMessage()." : ".$sql;
            
            $respArray = ['transid'=>$data->transid,$this->reference,'responseCode' => 501, "Message"=>($message)];
        }
        return json_encode($respArray);
    }
    public function payUtility($data){
        //check for required fields eg transid return accountprofile info of this transid 
       /*$err = Validate::payUtility($data);
        if (!empty($err))
            return DB::getErrorResponse($data, $err, $this->reference);        
        $payload = (array)$data; 
        */ 
                 
        $payload = (array)$data;
        $payload['reference']=$this->reference;  
        $account = isset($payload['customerNo'])?$payload['customerNo']:$payload['msisdn'];
        $customer = $this->_getAccountNo($account);
        $profile = json_decode($this->_getProfile($customer), true);
        $name =  $profile[0]['firstName'].' '. $profile[0]['lastName'];
        $utilitycode = $payload['utilitycode'];
        $utilityref = $payload['utilityref'];
        $amount = $payload['amount'];
        $transid = $payload['transid'];
        $msisdn = $payload['msisdn'];

        $payload['accountNo']=$customer;
        unset( $payload['transid']);
                   
        try{
           
            $selcom = new DbHandler();
            $result = $selcom->utilityPayment($transid,$utilitycode,$utilityref,$msisdn,$amount,$payload['reference']);            
           

            $message = array();
            $message['status']="SUCCESS";
            $message['method']="payUtility";
            $message['data']=$result;

            $respArray = ['transid'=>$data->transid,'reference'=>$payload['reference'],'responseCode' => 200, "Message"=>($message)];
        
           

        }catch (Exception $e) {
            
            $message = array();
            $message['status']="ERROR";
            $message['method']='Transaction error at: payUtility '.$e->getMessage()." : ".$sql;
            
            $respArray = ['transid'=>$data->transid,$this->reference,'responseCode' => 501, "Message"=>($message)];
        }
        return json_encode($respArray);
    }
    public function reserveAmount($data)   {
        //check for required fields eg transid return accountprofile info of this transid 
        
       /* $err = Validate::reserveAmount($data);
        if (!empty($err))
            return DB::getErrorResponse($data, $err, $this->reference);
         */
        $payload = (array)$data;
        $ref=$this->reference;  
        $account = isset($payload['customerNo'])?$payload['customerNo']:$payload['msisdn'];
        $customer = $this->_getAccountNo($account);
        $amount = $payload['amount'];
        $transid = $payload['transid'];
        $msisdn = $payload['msisdn'];
        $utilityref = $payload['msisdn'];//same account
       
        try{
            //create transaction to debit card of the amount added to suspense
            $selcom = new DbHandler();            
            $result = $selcom->reserveAccount($transid,$ref,$utilityref,$msisdn,$amount);

            /*$sql ="UPDATE card SET suspense=suspense+$amount WHERE id=$customer";
           
            $stmt = $this->conn->prepare( $sql );
            $stmt->execute();
            $selcom = new DbHandler();
            */
            $message = array();
            $message['status']="SUCCESS";
            $message['method']="reserveAmount";
            $message['data']=$result;
            
            $respArray = ['transid'=>$data->transid,'reference'=>$ref,'responseCode' => 200, "Message"=>($message)];
        }
        catch (Exception $e) {
            
            $message = array();
            $message['status']="ERROR";
            $message['method']='Transaction error at: reserveAmount '.$e->getMessage()." : ";//.$sql;
            
            $respArray = ['transid'=>$data->transid,'reference'=>$ref,'responseCode' => 501, "Message"=>($message)];
        }
        return (json_encode($respArray));
    }
    public function unReserveAccount($data)   {
         //check if the reference is the same as on in the transaction
        
       $err = Validate::unReserveAccount($data);
        if (!empty($err))
            return DB::getErrorResponse($data, $err, $this->reference);

        $payload = (array)$data;
        $ref=$this->reference;  
        $account = isset($payload['customerNo'])?$payload['customerNo']:$payload['msisdn'];
        $customer = $this->_getAccountNo($account);
        $amount = $payload['amount'];
        $transid = $payload['transid'];
        $msisdn = $payload['msisdn'];
        $utilityref = $payload['msisdn'];//same account
       
        try{
            //create transaction to debit card of the amount added to suspense
            $selcom = new DbHandler();            
            $result = $selcom->unReserveAccount($transid,$ref,$utilityref,$msisdn,$amount);

            $message = array();
            $message['status']="SUCCESS";
            $message['method']="unReserveAccount";
            $message['data']=$result;
            
            $respArray = ['transid'=>$data->transid,'reference'=>$ref,'responseCode' => 200, "Message"=>($message)];
        }
        catch (Exception $e) {
            
            $message = array();
            $message['status']="ERROR";
            $message['method']='Transaction error at: unReserveAccount '.$e->getMessage()." : ";//.$sql;
            
            $respArray = ['transid'=>$data->transid,'reference'=>$ref,'responseCode' => 501, "Message"=>($message)];
        }
        return (json_encode($respArray));
    }
   

}
