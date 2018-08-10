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
        $customer = $payload['accountNo'];//$this->_getAccountNo($data['customerNo']);

        $transid=$data['transid'];
        unset($payload['transid']);
        unset($payload['customerNo']);
        unset($payload['accountNo']);
        $sql='';
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
            $message['method']='updateAccount';//.$e->getMessage();
            $result['resultcode'] ='501';
            $result['result']=$e->getMessage().' '.$sql;
            $message['data']=$result;

            $respArray = ['transid'=>$data['transid'],$this->reference,'responseCode' => 501, "Message"=>($message)];
        }
    }
    /*public function _linkAccountProfile($data){

        $payload = (array)$data;
        $arr = null;
        $customer = $this->_getAccountNo($data['customerNo']);

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
            $message['method']='updateAccount';//.$e->getMessage()." : ".$sql;
            $result['resultcode'] ='501';
            $result['result']=$e->getMessage();
            $message['data']=$result;
            $respArray = ['transid'=>$data['transid'],$this->reference,'responseCode' => 501, "Message"=>($message)];
        }
    }*/
    public function _addAccountProfile($data){
        //unset($data['transid']);
        $profile = array();
        $profile['firstName']=isset($data['firstName'])?ucfirst($data['firstName']):'';
        $profile['lastName']=isset($data['lastName'])?ucfirst($data['lastName']):'';
        $profile['gender']=isset($data['gender'])?$data['gender']:'';
        $profile['customerNo']=isset($data['customerNo'])?$data['customerNo']:'';
        $profile['accountNo']=isset($data['accountNo'])?$data['accountNo']:'';
        $profile['msisdn']=isset($data['msisdn'])?$data['msisdn']:'';
        $profile['email']=isset($data['email'])?$data['email']:'';
        //$profile['status']=isset($data['status'])?$data['status']:0;
        $profile['addressLine1']=isset($data['addressLine1'])?ucfirst($data['addressLine1']):'';
        $profile['addressCity']=isset($data['addressCity'])?ucfirst($data['addressCity']):'';
        $profile['addressCountry']=isset($data['email'])?strtolower($data['email']):'';
        $profile['dob']=isset($data['dob'])?$data['dob']:null;
        $profile['currency']=isset($data['currency'])?strtoupper($data['currency']):'TZS';
        $profile['status']=1;
        //$profile['active']=isset($data['active'])?$data['active']:0; //acive=0 closed=1
        $profile['nationality']=isset($data['nationality'])?ucfirst($data['nationality']):'';
        $profile['balance']=0;
        $profile['tier']=isset($data['tier'])?strtoupper($data['tier']):'A';

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
    public function get_msisdn($accountNo){

        $sql ="select msisdn from accountprofile where accountNo ='$accountNo' ";
    
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
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
       
        if (!empty($result))
            return ($result);
        else
            return false;

    }
    public function _getResponse($method, $res, $transid,$ref){
        $message = array();
        $message['status']= 'SUCCESS';
        $message['method']=$method;
        $result['resultcode'] =200;
        $result['result']=$res;
        $message['data']=$result;

        return $respArray = ['transid'=>$transid,'reference'=>$ref,'responseCode' =>  200, "Message"=>($message)];

    }
    public function _getError($method, $e, $code, $transid, $reference){
       // $this->_getError('requestCard',$e->getMessage(), 076,$payload['transid'], $this->reference);
         $message = array();
            $message['status']="ERROR";
            $message['method']=$method ;
            $result['resultcode'] = $code;
            $result['result']=$e;
            $message['data']=$result;
            return $respArray = ['transid'=>$transid,'reference'=>$reference,'responseCode' => 501, "Message"=>($message)];
             
    }
    public function _addCard($data)   {

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
        $payload['accountNo'] = $request['accountNo'];
        $payload['name'] = $request['firstName']. ' '.$request['lastName'];
        $payload['msisdn'] = $request['msisdn'];
        //$payload['card'] = isset($request['card'])?$request['card']:'';
        $payload['dealer'] = 'TRANSSNET';
        $payload['registeredby'] = 'SelcomTranssnetAPI';
        $payload['confirmedby'] = 'SelcomTranssnetAPI';
        $payload['registertimestamp'] =$request['fulltimestamp'];
        $payload['confirmtimestamp'] = $request['fulltimestamp'];
        //$payload['active'] = 0;
        $payload['status'] = 1;
        $payload['reference'] = $this->reference;//request['reference'];
        $payload['email'] = isset($request['email'])?$request['email']:'';
        $payload['phone'] = isset($request['phone'])?$request['phone']:'';
        //$payload['message'] = $request['message'];


        $cols = null;
        $vals = null;
        $sql =null;
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
            $message['message']='Transaction error at: addcard '.$e->getMessage()." : ".$payload['msisdn'] .' : '. $request['msisdn'];;

            $error = 'Transaction error at: addcard '.$e->getMessage()." : ".$payload['msisdn'] .' : '. $request['msisdn'];
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
            $payload['channel'] = 'PALMPAY';
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
        /**
 * Checks a 16 digit card number whether the checksum is Luhn-approved.
 * If $create is set to true, return the input + the checksum.
 * @param type $card
 * @param type $create
 * @return mixed
 */
function _checkLuhn($card, $create = false){
    $segments = str_split($card, 15);
    $digits = str_split($segments[0], 1);
    foreach ($digits as $k => $d) {
        if ($k % 2 == 0) {
            $digits[$k] *= 2;
            if (strlen($digits[$k]) > 1) {
                $split = str_split($digits[$k]);
                $digits[$k] = array_sum($split);
            }
        }
    }
    $digits = array_sum($digits)*9;
    $digits = str_split($digits);
    $checksum = $digits[max(array_keys($digits))];

    if ($create == false) {
        if (!isset($segments[1])) {
            return "Invalid input length.";
        }
        if ($checksum == $segments[1]) {
            return 1;
        } else {
            return 0;
        }
    } else {
        return $segments[0].$checksum;
    }
}
public function  _checkTcard($accountNo){
    $sql = 'select id from tcard where accountNo="'.$accountNo.'"';
    $stmt = $this->conn->prepare( $sql );
    $stmt->execute();
    $result = $stmt->fetchAll();
    //die ('here'.print_r($result));
    return $result;

}
    public function openAccount($data)   {
        //check for required fields eg accountNo, msisdn, fname lname

        try{
            $err = Validate::openAccount($data); //if 2 MJ then account alread exists status is still 0 change it to one on accountprofile
            if (!empty($err))
                throw new Exception($err);

            $payload = (array)$data;
            $payload['accountNo'] = 'PALMPAY'.DB::getToken(12);
            if(isset(($payload['dob'])))
                $dob=DB::toDate('Y-m-d',$payload['dob']);
            $payload['reference']=$this->reference;
            $payload['fulltimestamp'] = date('Y-m-d H:i:s');

            //CHECKed PROFILE DB before adding to card DB in validate:openAccount
           
            //add to card DB
            $last_id = $this->_addCard($payload);// active = 0 not happening
            if ($last_id < 0){
                $error = 'Transaction error at: _addCard '.$e->getMessage();
                throw new Exception($error);
            }

            $this->_addAccountProfile($payload);
            $payload['accountNo'] = $this->_getAccountNo($payload['customerNo']);
            $res=$payload;
        
            $respArray = $this->_getResponse('openAccount',$res, $payload['transid'], $this->reference);
        }
        catch (Exception $e) {

           /* $message = array();
            $message['status']="ERROR";
            $message['method']='openAccount';//.$e->getMessage()." : ";
            $result['resultcode'] ='061';
            $result['result']=$e->getMessage();
            $message['data']=$result;
            $respArray = ['transid'=>$data->transid,$this->reference,'responseCode' => 501, "Message"=>($message)];
            */
            $respArray = $this->_getError('openAccount',$e->getMessage(),'061',$data->transid, $this->reference);
            
        }

        return (json_encode($respArray));
    }
    public function updateAccount($data)   {
        try{
            $err = Validate::updateAccount($data); //if 2 MJ then account alread exists status is still 0 change it to one on accountprofile
            if (!empty($err))
                throw new Exception($err);

            $payload = (array)$data;
            $tier =isset($payload['tier'])?strtoupper($payload['tier']):'A';

            //update accountProfile DB
            $customer = $this->_updateAccountProfile($payload);
            $payload['accountNo']=$customer;
            if(isset($tier)){

                switch($tier){
                    case 'B': $tier='B'; break;
                    case 'C': $tier='C'; break;
                    case 'D': $tier='D'; break;
                    default: 'A';
                }

                $query = "UPDATE card SET tier='$tier' WHERE accountNo='$customer'";
                $this->conn->query($query);
            }


            $payload['reference']=$this->reference;
            $payload['fulltimestamp'] = date('Y-m-d H:i:s');
            $payload['method'] = 'updateAccount';
            $res=$payload;
          
            $respArray = $this->_getResponse('updateAccount',$res, $payload['transid'], $this->reference);

        }
        catch (Exception $e) {

           /* $message = array();
            $message['status']="ERROR";
            $message['method']='updateAccount';//.$e->getMessage()." : ";
            $result['resultcode'] ='062';
            $result['result']=$e->getMessage();
            $message['data']=$result;
            $respArray = ['transid'=>$data->transid,$this->reference,'responseCode' => 501, "Message"=>($message)];
            */
            $respArray = $this->_getError('updateAccount',$e->getMessage(), '062',$data->transid, $this->reference);
           
        }

        return (json_encode($respArray));

    }
    public function linkAccount($data)   {
        try{
            $err = Validate::linkAccount($data); //if 2 MJ then account alread exists status is still 0 change it to one on accountprofile
            if (!empty($err))
                throw new Exception($err);

            $payload = (array)$data;
           
            $accountNo = $payload['accountNo'];
            unset($payload['transid']);
            unset($payload['confirmPin']);
           
            $cols = null;
            $vals = null;
        

            foreach($payload as $key => $val){

                    $cols.=$key.', ';
                    $vals.=':'.$key.', ';
            }

            $cols = rtrim($cols,', ');
            $vals = rtrim($vals,', ');

            
            $sql ="INSERT INTO vendor (".$cols.") VALUES (".$vals.")";            

            $stmt = $this->conn->prepare( $sql );
            $state = $this->_pdoBindArray($stmt,$payload);
            
            $state->execute();
       
            //die(print_r($this->conn->lastInsertId()));

            $payload['reference']=$this->reference;
            $payload['fulltimestamp'] = date('Y-m-d H:i:s');
                        
            $respArray = $this->_getResponse('linkAccount',$payload, $data->transid, $this->reference);
            }catch (Exception $e) {

               if($e->getMessage() === '999'){
                     //$respArray = $this->_getError('linkAccount','Vendor/Card account already linked','084',$data->transid, $this->reference);
                     $data->statustxt='close';return $this->unLinkAccount($data);
               }
                   
               else
                    $respArray = $this->_getError('linkAccount',$e->getMessage(),'063',$data->transid, $this->reference);
            }
        return (json_encode($respArray));
    }
    public function unLinkAccount($data)   {
        $sql = '';
        try{
            $err = Validate::unLinkAccount($data); //if 2 MJ then account alread exists status is still 0 change it to one on accountprofile
            if (!empty($err))
                throw new Exception($err);

            $payload = (array)$data;
             
            //$accountNo = $payload['accountNo'];
            $msisdn = $payload['msisdn'];
            $vendoraccountNo = $payload['vendorType'] == 'bank'?$payload['bankAccountNumber']:$payload['cardNumber'];
            $type = $payload['vendorType'] == 'bank'?'bankAccountNumber':'cardNumber';
            $statustxt = $payload['statustxt'];
            
            $msg= array('statustxt' => $statustxt,'description'=>$vendoraccountNo.' account closed ');
            $sql = "delete from vendor WHERE $type='$vendoraccountNo' && msisdn='$msisdn'";

            $stmt = $this->conn->prepare($sql); 
            $stmt->execute();
            if (!$stmt->rowCount()) 
                throw new Exception ('Error UnLinkAccount, check parameter values ');       

            $payload['reference']=$this->reference;
            $payload['fulltimestamp'] = date('Y-m-d H:i:s');
            $payload['method'] = 'unLinkAccount';
            $result['resultcode']='102';
            
            $result['result']=$msg;
            $res=$result;
            
            $respArray = $this->_getResponse('unLinkAccount',$res, $payload['transid'], $this->reference);
            
        }catch (Exception $e) {

            /*$message = array();
            $message['status']="ERROR";
            $message['method']='unLinkAccount';//.$e->getMessage()." : ";
            $result['resultcode'] ='064';
            $result['result']=$e->getMessage();
            $message['data']=$result;
            $respArray = ['transid'=>$data->transid,'reference'=>$this->reference,'responseCode' => 501, "Message"=>($message)];
            */
            $respArray = $this->_getError('unLinkAccount',$e->getMessage(),'064',$data->transid, $this->reference);
        }
        return (json_encode($respArray));
    }
    public function nameLookup($data)   {
        //check for required fields eg transid return accountprofile info of this transid
        $sql=null;
        try{
            $err = Validate::nameLookup($data);
           
            if (!empty($err))
                throw new Exception($err);
                
            $payload = (array)$data;
            
            $where =null;


            if(!isset($payload['accountNo'])){
                $where .= isset($payload['customerNo'])?'where customerNo="'.$payload['customerNo'].'"':' where msisdn="'.$payload['msisdn'].'"';
            }
            else
                $where = "where accountNo='".$payload['accountNo']."'";

            $sql ='SELECT firstName, lastName, tier,customerNo,accountNo, msisdn,  REPLACE(REPLACE(status,0,\'false\'),1,\'true\') AS statustxt, REPLACE(REPLACE(active,0,\'true\'),1,\'false\') AS activetxt, email, addressLine1,addressCity, addressCountry,dob as dateofbirth,state, gender, nationality, currency, balance, lastupdated from accountprofile '.$where;

            $stmt = $this->conn->query( $sql );
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $respArray = $this->_getResponse('nameLookup',$res, $payload['transid'], $this->reference);
        }
        catch (Exception $e) {

           /* $message = array();
            $message['status']="ERROR";
            $message['method']='nameLookup';//.$e->getMessage()." : ";//.$sql;
            $result['resultcode'] ='065';
            $result['result']=$e->getMessage().' : '.$sql;
            $message['data']=$result;
            $respArray = ['transid'=>$data->transid,'reference'=>$this->reference,'responseCode' => 501, "Message"=>($message)];
            */
            $respArray = $this->_getError('nameLookup',$e->getMessage(),'080',$data->transid, $this->reference);

            
        }
       
        return (json_encode($respArray));
    }
    public function transactionLookup($data)   {
        try{
            //check for required fields eg transid return accountprofile info of this transid
            $err = Validate::transactionLookup($data);
            if (!empty($err))
                throw new Exception($err);

            $payload = (array)$data;            
            $account = $payload['accountNo'];
            $msisdn = $this->get_msisdn($account);  

                    /*transref is the needle transid and msisdn should belong*/
            $sql ="select fulltimestamp,t.transid,t.reference,msisdn,amount, message,name,utilitycode,utilityref, transtype, geocode,posId, tinfo.comments from transaction as t INNER JOIN tinfo ON tinfo.transid=t.transid where t.transid = '".$data->transref."' && msisdn='$msisdn'";
           
            $stmt = $this->conn->query( $sql );
            
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
             
           
            $respArray = $this->_getResponse('transactionLookup',$res, $payload['transid'], $this->reference);
        }
        catch (Exception $e) {

            /*$message = array();
            $message['status']="ERROR";
            $message['method']='transactionLookup';//.$e->getMessage()." : ";
            $result['resultcode'] ='066';
            $result['result']=$e->getMessage();
            $message['data']=$result;
            $respArray = ['transid'=>$data->transid,$this->reference,'responseCode' => 501, "Message"=>($message)];
            */
            $respArray = $this->_getError('transactionLookup',$e->getMessage(),'066',$data->transid, $this->reference);

        }
        return (json_encode($respArray));
    }
    public function transferFunds($data)   {
        try{
            $result=null;
            //check for required fields eg transid return accountprofile info of this transid
            $err = Validate::transferFunds($data);
            if (!empty($err))
                throw new Exception($err);
               
            $payload = (array)$data;

            $msisdn = isset($payload['msisdn'])?$payload['msisdn']:self::get_msisdn($payload['accountNo']);
            //check sender card status active state
            $active = Validate::_checkCard($msisdn);
            if ($active != 'active'){
                $err="Account Status ".$active;
                throw new Exception($err);
            }
            
            //check receiver card status active state
            
           
            /*$active = Validate::_checkCard($payload['utilityref']);
           
            if ($active != 'active'){
                $err="Account Status Error: ".$active;
                
                throw new Exception($err);
            }*/

            $payload['reference']=$this->reference;
           

            //save extra info into tinfo table
            if(!Validate::setTinfo($payload)){
                $err="Internal Error 070";
                throw new Exception($err);
            }

            $payload['fulltimestamp'] = date('Y-m-d H:i:s');
            $payload['method'] = 'transferFunds';

            $selcom = new DbHandler();
            $res= $selcom->fundTransfer($payload['transid'],$payload['reference'],$payload['utilityref'], $msisdn,$payload['amount']);
            //$result=$res;
           //die(print_r($res));
            $respArray = $this->_getResponse('transferFunds',$res, $payload['transid'], $this->reference);
        }
        catch (Exception $e) {

            /*$message = array();
            $message['status']="ERROR";
            $message['method']='transferFunds';
            $result['result']=$e->getMessage();
            $result['resultcode'] = '067';
            $message['data']=$result;

            $respArray = ['transid'=>$data->transid,$this->reference,'responseCode' => 501, "Message"=>($message)];
            */
            $respArray = $this->_getError('transferFunds',$e->getMessage(),'067',$data->transid, $this->reference);

        }
       //die(print_r($respArray));
        return (json_encode($respArray));
    }
    public function checkBalance($data)   {
        try{
           
            //check for required fields eg transid return accountprofile info of this transid
            $err = Validate::checkBalance($data);
            if (!empty($err))
                throw new Exception($err);
            $payload = (array)$data;



            $payload['reference']=$this->reference;
            if (isset($payload['accountNo'])){
                $account = $payload['accountNo'];
                $msisdn = $this->get_msisdn($account);
            }                
            else 
                $msisdn = $payload['msisdn'];
            
                $payload['fulltimestamp'] = date('Y-m-d H:i:s');    
                
                $selcom = new DbHandler();
                $result = $selcom->balanceEnquiry($msisdn);
                $payload['balance'] =$result['balance'];
                $payload['available'] =$result['available'];
                $res=$payload;           
                
                $respArray = $this->_getResponse('checkBalance',$res, $payload['transid'], $this->reference);
        }
        catch (Exception $e) {

            /*$message = array();
            $message['status']="ERROR";
            $message['method']='checkBalance';//.$e->getMessage()." : ".$sql;
            $result['resultcode'] ='068';
            $result['result']=$e->getMessage();
            $message['data']=$result;
            $respArray = ['transid'=>$data->transid,$this->reference,'responseCode' => 501, "Message"=>($message)];
            */
            $respArray = $this->_getError('transferFunds',$e->getMessage(),'068',$data->transid, $this->reference);

        }
        return (json_encode($respArray));
    }
    public function getStatement($data)   {
        try{
            //check for required fields eg transid return accountprofile info of this transid
            $err = Validate::getStatement($data);
            if (!empty($err))
                throw new Exception($err);
            $payload = (array)$data;

            $payload['reference']=$this->reference;
            $account = $payload['accountNo'];
            $msisdn = $this->get_msisdn($account);
             
            $days = isset($payload['days'])?$payload['days']:3;
            $start = isset($payload['start'])?$payload['start']:'';
            $end = isset($payload['end'])?$payload['end']:'';
            $date = isset($payload['date'])?$payload['date']:3;
           
            $payload['fulltimestamp'] = date('Y-m-d H:i:s');
            $payload['method'] = 'getStatement';
            $selcom = new DbHandler();
            if($start !== '' && $end !==''){
                $end = date('Y-m-d', strtotime($end . ' +1 day'));             
                $res = $selcom->statementEnquiryRange($msisdn, $start, $end);
            }
             else if($date !== '' ) 
                $res = $selcom->statementEnquiryDay($msisdn,date('Ymd',strtotime($date)));
             else 
                $res = $selcom->statementEnquiry($msisdn,$days); 
              
            $respArray = $this->_getResponse('getStatement',$res, $payload['transid'], $this->reference);

        }
        catch (Exception $e) {

            /*$message = array();
            $message['status']="ERROR";
            $message['method']='getStatement';//.$e->getMessage()." : ";
            $result['resultcode'] ='069';
            $result['result']=$e->getMessage();
            $message['data']=$result;
            $respArray = ['transid'=>$data->transid,$this->reference,'responseCode' => 501, "Message"=>($message)];
            */
            $respArray = $this->_getError('getStatement',$e->getMessage(),'069',$data->transid, $this->reference);

        }
        return (json_encode($respArray));
    }
    public function changeStatus($data){
        try{
            //check for required fields eg transid return accountprofile info of this transid
            $err = Validate::changeStatus($data);
            if (!empty($err))
                throw new Exception($err);
            $payload = (array)$data;


            $payload = (array)$data;
            $payload['reference']=$this->reference;
            $accountNo = $payload['accountNo'];           
            
            $status = isset($payload['statustxt']) && strtolower($payload['statustxt']) === 'open'?'1':'0';
            
            //unset( $payload['transid']);


            $sql = "UPDATE card SET status='".$status."' where accountNo='".$accountNo."'; ";
            $sql2 =" UPDATE accountprofile SET status='".$status."' where accountNo='".$accountNo."'";

            $stmt = $this->conn->query($sql);             

            $stmt2 = $this->conn->query($sql2);
            

            $sql3 ='SELECT firstName, lastName, tier,customerNo,accountNo, msisdn,  REPLACE(REPLACE(status,0,\'close\'),1,\'open\') AS statustxt,  lastupdated from accountprofile  where accountNo="'.$accountNo.'"';
            $stmt3 = $this->conn->query($sql3);

            $res = $stmt3->fetch(PDO::FETCH_ASSOC);
            $respArray = $this->_getResponse('changeStatus',$res, $payload['transid'], $this->reference);


        }catch (Exception $e) {

           /* $message = array();
            $message['status']="ERROR";
            $message['method']='changeStatus';//.$e->getMessage()." : ".$sql;
            $result['resultcode'] ='071';
                $result['result']=$e->getMessage();
                $message['data']=$result;
                $respArray = ['transid'=>$data->transid,$this->reference,'responseCode' => 501, "Message"=>($message)];
                */
        $respArray = $this->_getError('changeStatus',$e->getMessage(),'071',$data->transid, $this->reference);

        }
        
        return json_encode($respArray);
    }
 /**
     * selcom->payUtility is offline, make sure its ready before production
     * if msisdn is wrong doesnt matter as its cash add to that msisdn, snooze you loose
     * * */
    public function addCash($data){
        try{
            //check for required fields eg transid return accountprofile info of this transid
            $err = Validate::cashin($data);
            
            if (!empty($err))
                throw new Exception($err);
            $payload = (array)$data;


            $payload = (array)$data;
            $payload['reference']=$this->reference;
            $customer = $this->_getAccountNo($payload['msisdn']); //agent has msidn number not accountNo

            $profile = json_decode($this->_getProfile($customer), true);
           /* if(!$profile)
                die('here '.print_r($profile));*/
            $name =  $profile[0]['firstName'].' '. $profile[0]['lastName'];
            $amount = $payload['amount'];
            $payload['firstname']= $profile[0]['firstName'];
            $payload['lastname']= $profile[0]['lastName'];
            
            $selcom = new DbHandler();
            //$result = $selcom->utilityPayment($transid,'SPCASHIN',$payload['msisdn'],$payload['msisdn'],$amount,$payload['reference']);
            $res[0]=$payload;
            $respArray = $this->_getResponse('addCash',$res, $payload['transid'], $this->reference);

        }catch (Exception $e) {

            /*$message = array();
            $message['status']="ERROR";
            $message['method']='addCash';//.$e->getMessage();
            $result['resultcode'] ='072';
                $result['result']=$e->getMessage();
                $message['data']=$result;
                $respArray = ['transid'=>$data->transid,$this->reference,'responseCode' => 501, "Message"=>($message)];
                */
            $respArray = $this->_getError('addCash',$e->getMessage(),'072',$data->transid, $this->reference);
        }
        return json_encode($respArray);
    }
    /**
     * selcom->payUtility is offline, make sure its ready before production
     * * */
    public function payUtility($data){
        try{
            //check for required fields eg transid return accountprofile info of this transid
            $err = Validate::payUtility($data);
            if (!empty($err))
                throw new Exception($err);
            $payload = (array)$data;
           

            $payload = (array)$data;
            $payload['reference']=$this->reference;
            
            $msisdn = $this->get_msisdn($payload['accountNo']); //agent has msidn number not accountNo
            
            $profile = $this->_getProfile( $payload['accountNo']);
            if (empty($profile)){
                $err="This Account does not exist";
                throw new Exception($err);
            }
               
            //$msisdn_acct = $profile[0]['msisdn'];
            $name =  $profile['firstName'].' '. $profile['lastName'];
            $utilitycode = $payload['utilitycode'];
            $utilityref = $payload['utilityref'];
            $amount = $payload['amount'];
            $transid = $payload['transid'];
            //$msisdn = $payload['msisdn'];

            //$payload['accountNo']=$customer;
            //unset( $payload['transid']);
           


            $selcom = new DbHandler();
//$result = $selcom->utilityPayment($transid,$utilitycode,$utilityref,$msisdn,$amount,$payload['reference']);
            $res[0]=$payload;

            $respArray = $this->_getResponse('payUtility',$res, $payload['transid'], $this->reference);

        }catch (Exception $e) {

            /*$message = array();
            $message['status']="ERROR";
            $result['resultcode'] ='073';
            $result['result']=$e->getMessage();
            $message['data']=$result;
            $result['resultcode'] ='501';
            $respArray = ['transid'=>$data->transid,$this->reference,'responseCode' => 501, "Message"=>($message)];
            */
            $respArray = $this->_getError('payUtility',$e->getMessage(),'073',$data->transid, $this->reference);
        }
        return json_encode($respArray);
    }
    public function freezeAccount($data)   {
        try{
            //check for required fields eg transid return accountprofile info of this transid

            $err = Validate::reserveAccount($data);
            if (!empty($err))
                throw new Exception($err);

            $payload = (array)$data;
            $ref=$this->reference;
            $account = isset($payload['customerNo'])?$payload['customerNo']:$payload['msisdn'];
            $customer = $this->_getAccountNo($account);
            $amount = $payload['amount'];

            $transid = $payload['transid'];
            $msisdn = $payload['msisdn'];
            $utilityref = $payload['msisdn'];//same account


            //create transaction to debit card of the amount added to suspense
            $selcom = new DbHandler();
            $res[0] = $selcom->reserveAccount($transid,$ref,$msisdn,$amount);

            $respArray = $this->_getResponse('reserveAccount',$res, $payload['transid'], $this->reference);
        }
        catch (Exception $e) {

           /* $message = array();
            $message['status']="ERROR";
            $message['method']='freezeAccount';//.$e->getMessage()." : ";
            $result['resultcode'] ='074';
            $result['result']=$e->getMessage();
            $message['data']=$result;
            $respArray = ['transid'=>$data->transid,$this->reference,'responseCode' => 501, "Message"=>($message)];
            */
            $respArray = $this->_getError('freezeAccount',$e->getMessage(),'074',$data->transid, $this->reference);
        }
        return (json_encode($respArray));
    }
    public function unFreezeAccount($data)   {
        try{
            //check if the reference is the same as on in the transaction

            $err = Validate::unReserveAccount($data);
            if (!empty($err))


            $payload = (array)$data;
            $ref=$this->reference;
            $account = isset($payload['customerNo'])?$payload['customerNo']:$payload['msisdn'];
            $customer = $this->_getAccountNo($account);
            $amount = $payload['amount'];
            $transid = $payload['transid'];
            $msisdn = $payload['msisdn'];
            $utilityref = $payload['msisdn'];//same account


            //create transaction to debit card of the amount added to suspense
            $selcom = new DbHandler();
            $res[0] = $selcom->unReserveAccount($transid,$ref,$payload['reference'],$msisdn,$amount);
            $respArray = $this->_getResponse('unReserveAccount',$res, $payload['transid'], $this->reference);
        }
        catch (Exception $e) {

            /*$message = array();
            $message['status']="ERROR";
            $message['method']='unReserveAccount';//.$e." : ";//.$sql;
            $result['resultcode'] ='081';
                $result['result']=$e->getMessage();
                $message['data']=$result;
                $respArray = ['transid'=>$data->transid,'reference'=>$ref,'responseCode' => 501, "Message"=>($message)];
                */
        $respArray = $this->_getError('unFreezeAccount',$e->getMessage(),'081',$data->transid, $this->reference);
        }
        return (json_encode($respArray));
    }
    public function requestCard($data){
       
        $sql =null;  
        $sql2=null;
        try{
            $err = Validate::requestCard($data);
            if (!empty($err))
                throw new Exception($err);


            if (empty($this->_checkTcard( $data->accountNo))){
                $payload = (array)$data;
                $today=date('Y-m-d H:i:s');;
                $res = array();
                $res['fulltimestamp'] = DB::toDateTime($today);
                $res['accountNo'] = $payload['accountNo'];
                $res['msisdn'] = $payload['msisdn'];
                $card = DB::getToken(16);
                
                do {
                    //echo $i++;
                    $card = '1912'.DB::getToken(12);
                } while ($this->_checkLuhn($card));
                //die ($card.' : '.$this->_checkLuhn($card));

                $profile = $this->_getProfile($payload['accountNo']);
                
                $res['card'] = $card;
                $res['cvv'] = DB::getToken(3);
                $res['exp'] = DB::getToken(2).'/'.rand(2020,2027);
                $res['dealer'] = 'Transsnet';

                $res['registeredby'] = 'SelcomTranssnetAPI';
                $res['confirmedby'] = 'SelcomTranssnetAPI';
                $res['registertimestamp'] = DB::toDateTime($today);
                $res['confirmtimestamp'] = DB::toDateTime($today);
                $res['active'] = 0;
                $res['status'] = 1;
                $res['reference'] = $this->reference;//request['reference'];
                $res['email'] = isset($profile['email'])?$profile['email']:'';
                $res['name'] =  $profile['firstName']. ' ' .$profile['lastName'];
                //$payload['message'] = $request['message'];


                $cols = null;
                $vals = null;
                $sql =null;


                foreach($res as $key => $val){

                        $cols.=$key.', ';
                        $vals.=':'.$key.', ';
                        //error_log("\r\n".date('Y-m-d H:i:s').' '.json_encode(':'.$key.', '.$val), 3, "binding.log");
                }
                $cols = rtrim($cols,', ');
                $vals = rtrim($vals,', ');


                $sql ="INSERT INTO tcard (".$cols.") VALUES (".$vals.")";
               
                $stmt = $this->conn->prepare( $sql );
                $state = $this->_pdoBindArray($stmt,$res);
               
                $state->execute();

                $id = $this->conn->lastInsertId();
                $sql2 = "select name, msisdn, card, cvv, exp, accountNo from tcard where id=".$id;
                $stmt2 =  $this->conn->query( $sql2 );
                $row = $stmt2->fetch(PDO::FETCH_ASSOC);
                $respArray = $this->_getResponse('requestCard',$row, $payload['transid'], $this->reference);
            }
            else{
                $err ="card Already Exists";
                throw new Exception($err);
            }


        }
        catch(Exception $e) {

            /*$message = array();
            $message['status']="ERROR";
            $message['method']='requestCard' ;
            $result['resultcode'] ='076';
            $result['result']=$e->getMessage().$sql;
            $message['data']=$result;
            $respArray = ['transid'=>$data->transid,'reference'=>$this->reference,'responseCode' => 501, "Message"=>($message)];
            */
            $respArray = $this->_getError('requestCard',$e->getMessage(), '079',$payload['transid'], $this->reference);
            
        }
        return (json_encode($respArray));

    }
    public function search($data){
       
        
        if (!isset($data->search))
            return  $this->_getError('search','parameter search missing', 077,$data->transid, $this->reference);

        $searchthis = $data->search;
        
        $matches = array();

        $handle = @fopen("transsetlog.log", "r");
        if ($handle)
        {	$i=0;
            while (!feof($handle))
            {
                $buffer = fgets($handle);
                if(strpos($buffer, $searchthis) !== FALSE)
                print_r($buffer);
                    $matches[$i++] = ($buffer);
                    
            }
            fclose($handle);
        }

        //show results:
        
        return ($matches);

    }
    public function cashout($data)   {
        //check for required fields eg transid return accountprofile info of this transid
        $sql=null;
        try{
            $err = Validate::cashout($data);
           
            if (!empty($data))
                throw new Exception($err);
                
            $payload = (array)$data;
            
            $res = array();
            $res['status']="SUCCESS";
            $res['method']='cashout'; 
            $result['resultcode'] ='082';
            $result['result']=$data->message;//'cashout requested';
            $res['data']=$result;
            
            
            $respArray = $this->_getResponse('nameLookup',$res, $payload['transid'], $this->reference);
        }
        catch (Exception $e) {

           /* $message = array();
            $message['status']="ERROR";
            $message['method']='nameLookup';//.$e->getMessage()." : ";//.$sql;
            $result['resultcode'] ='065';
            $result['result']=$e->getMessage().' : '.$sql;
            $message['data']=$result;
            $respArray = ['transid'=>$data->transid,'reference'=>$this->reference,'responseCode' => 501, "Message"=>($message)];
            */
            $respArray = $this->_getError('cashout',$e->getMessage(),'082',$data->transid, $this->reference);

            
        }
       
        return (json_encode($respArray));
    }
    public function reverseTransaction($data)   {
        //check for required fields eg transid return accountprofile info of this transid
        $sql=null;
        try{
            //same params as transactionLookup which transref you want to reverse
            $err = Validate::transactionLookup($data);
           
            if (!empty($err))
                throw new Exception($err);
                
            $payload = (array)$data; 
            
            $res = array();
            $res['status']="SUCCESS";
            $res['method']='reverseTransaction'; 
            $result['resultcode'] ='083';
            $result['result']='Your request is being processed. You will be notified in 72 hours';
            $res['data']=$result;
           /* send notification to selcom*/
            $from = "salma@selcom.net";
            $headers = "From:" . $from;
           // mail ("salma@selcom.net" ,"reverse transaction requested: " , json_encode($payload),$headers);
            
            $respArray = $this->_getResponse('reverseTransaction',$res, $payload['transid'], $this->reference);
        }
        catch (Exception $e) {
            die(print_r('here'));
           /* $message = array();
            $message['status']="ERROR";
            $message['method']='nameLookup';//.$e->getMessage()." : ";//.$sql;
            $result['resultcode'] ='065';
            $result['result']=$e->getMessage().' : '.$sql;
            $message['data']=$result;
            $respArray = ['transid'=>$data->transid,'reference'=>$this->reference,'responseCode' => 501, "Message"=>($message)];
            */
            $respArray = $this->_getError('reverseTransaction',$e->getMessage(),'083',$data->transid, $this->reference);

            
        }
       
        return (json_encode($respArray));
    }
    public function sendReverseTransactionNotification($data)   {
        //check for required fields eg transid return accountprofile info of this transid
        $sql=null;
        try{
            //same params as transactionLookup which transref you want to reverse
            /*$err = Validate::notification($data);
           
            if (!empty($err))
                throw new Exception($err);
            */    
            
          $req = array("channel" =>$data->channel,"origReference" =>$data->reference,"reference" => $this->reference, "terminalId" => $data->terminalid); 
             
            /*send notification to Transsnet*/
                $curl = curl_init();
                curl_setopt_array($curl, array(
                CURLOPT_URL => "http://172.29.204.115:8080/selcom/withdraw/atmreversal",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => json_encode ($req),
                CURLOPT_HTTPHEADER => array(
                    "content-type:application/json",
                    "cache-control: no-cache",
                    "content-type: application/json"
                ),
                ));

                $response = curl_exec($curl);
                $err = curl_error($curl);

                curl_close($curl);

                if ($err) {
                die( "cURL Error #:" . print_r($err));
                } else {
                die($response);
                }
            
            $respArray = $this->_getResponse('reverseTransaction',$res, $payload['transid'], $this->reference);
        }
        catch (Exception $e) {
            die(print_r('here'));
           /* $message = array();
            $message['status']="ERROR";
            $message['method']='nameLookup';//.$e->getMessage()." : ";//.$sql;
            $result['resultcode'] ='065';
            $result['result']=$e->getMessage().' : '.$sql;
            $message['data']=$result;
            $respArray = ['transid'=>$data->transid,'reference'=>$this->reference,'responseCode' => 501, "Message"=>($message)];
            */
            $respArray = $this->_getError('reverseTransaction',$e->getMessage(),'083',$data->transid, $this->reference);

            
        }
       
        return (json_encode($respArray));
    }
  
}
    
