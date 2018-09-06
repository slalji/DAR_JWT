<?php
require_once ("config.php");
require_once ("selcom.card.dbhandler.php");
require_once ("vendor/phpmailer/class.phpmailer.php");
require_once ("vendor/phpmailer/send_mail.php");

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
        $accountNo = $payload['accountNo'];//$this->_getAccountNo($data['customerNo']);

        // following you cannot update! msisdn, perhaps manual check with Rosario or Sameer
        unset($payload['transid']);
        unset($payload['customerNo']);
        unset($payload['accountNo']);
        unset($payload['msisdn']);
        $sql='';
        
        try{

            foreach($payload as $key => $val){

                    $arr.=$key . '=:'.$key.', ';
            }

            $arr = rtrim($arr,', ');
            $sql ="UPDATE accountprofile SET $arr where accountNo = '".$accountNo."'";
            $stmt = $this->conn->prepare( $sql );
            $state = $this->_pdoBindArray($stmt,$payload);
            
            if($state->execute())
                return true;

        }catch (Exception $e) {
            return $e->getMessage();
             
        }
    }
    
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
        $profile['active']=1;//active=1 confirmed my Rosario 29 Aug 2018
        $profile['nationality']=isset($data['nationality'])?ucfirst($data['nationality']):'';
        $profile['balance']=0;
        $profile['tier']=isset($data['tier'])?strtoupper($data['tier']):'A';

        $cols = null;
        $vals = null;
        //die(print_r($profile['dob']));
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
            //throw new Exception($error);
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
    public function _get_msisdn($accountNo){

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
    public function _getTransaction($transref){

        $sql ="select * from transaction where transid ='$transref' ";

        $stmt = $this->conn->prepare( $sql );
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
       
        if (!empty($result))
            return ($result);
        else
            return false;

    }
        
    public function _getLinkedCard($accountNo, $name, $number, $cardType, $exp, $cvv){

        $sql ="select * from vendor where accountNo ='$accountNo' && vendorType='card' && cardHolderName = '$name' && cardNumber = '$number' && exp='$exp' && cvv=$cvv ";

        $stmt = $this->conn->prepare( $sql );
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
       
        if (!empty($result))
            return ($result);
        else
            return false;

    }
    public function _getResponse($method, $res, $transid,$ref){
        if (isset($res['transid']))
            unset($res['transid']);
       if (isset($res['reference']))
            unset($res['reference']);
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
private function  _checkTcard($accountNo){
    $sql = 'select id from tcard where accountNo="'.$accountNo.'"';
    $stmt = $this->conn->prepare( $sql );
    $stmt->execute();
    $result = $stmt->fetchAll();
    //die ('here'.print_r($result));
    return $result;

}
private function _mask ( $str, $start = 0, $length = null ) {
    $mask = preg_replace ( "/\S/", "X", $str );
    if( is_null ( $length )) {
        $mask = substr ( $mask, $start );
        $str = substr_replace ( $str, $mask, $start );
    }else{
        $mask = substr ( $mask, $start, $length );
        $str = substr_replace ( $str, $mask, $start, $length );
    }
    return $str;
}
private function _bankLookup($utilitycode, $utilityref){
    $sql = "select bankAccountName from vendor where bankAccountNumber = '$utilityref'";
    $stmt = $this->conn->query( $sql );
    $res = $stmt->fetch(PDO::FETCH_ASSOC);
    if($res){
        return $res['bankAccountName'];
    }
}
private function _mnoLookup($utilityref){
    $sql = "select concat(firstName,' ',lastName) as name from accountProfile where msisdn = '$utilityref'";
    $stmt = $this->conn->query( $sql );
    $res = $stmt->fetch(PDO::FETCH_ASSOC);
    if($res){
        return $res['name'];
    }
}
 
    public function openAccount($data)   {
        //check for required fields eg accountNo, msisdn, fname lname

        try{
            $err = Validate::openAccount($data); //if 2 MJ then account alread exists status is still 0 change it to one on accountprofile
            if (!empty($err))
                throw new Exception($err);

            $payload = (array)$data;
            $payload['accountNo'] = 'PALMPAY'.DB::getToken(12);
            /*if(isset(($payload['dob'])))
                $dob=DB::toDate('Y-m-d',$payload['dob']);*/
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

           
            $respArray = $this->_getError( __FUNCTION__,$e->getMessage(),'061',$data->transid, $this->reference);
            
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
            /*if(isset($payload['dob'])){
                $payload['dob'] = DB::toDate((int)$payload['dob']);           
            }*/

            //update accountProfile DB
            $result = $this->_updateAccountProfile($payload);
            
            if (!$result){
                
                throw new Exception($res);
            }
               
            //$payload['accountNo']=$customer;
            if(isset($tier)){

                switch($tier){
                    case 'B': $tier='B'; break;
                    case 'C': $tier='C'; break;
                    case 'D': $tier='D'; break;
                    default: 'A';
                }

                $query = "UPDATE card SET tier='$tier' WHERE accountNo='".$payload['accountNo']."'";
                $this->conn->query($query);
            } 

            $res=$payload;
          
            $respArray = $this->_getResponse('updateAccount',$res, $payload['transid'], $this->reference);

        }
        catch (Exception $e) {

           
            $respArray = $this->_getError( __FUNCTION__,$e->getMessage(), '062',$data->transid, $this->reference);
           
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

           // $payload['reference']=$this->reference;
            $payload['fulltimestamp'] = date('Y-m-d H:i:s');
                        
            $respArray = $this->_getResponse('linkAccount',$payload, $data->transid, $this->reference);
            }catch (Exception $e) {

               if($e->getMessage() === '999'){
                     //$respArray = $this->_getError( __FUNCTION__,'Vendor/Card account already linked','084',$data->transid, $this->reference);
                     $data->statustxt='close';return $this->unLinkAccount($data);
               }
                   
               else
                    $respArray = $this->_getError( __FUNCTION__,$e->getMessage(),'063',$data->transid, $this->reference);
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
            //$msisdn = $payload['msisdn'];
            $accountNo = $payload['accountNo'];
            $vendoraccountNo = $payload['vendorType'] == 'bank'?$payload['bankAccountNumber']:$payload['cardNumber'];
            $type = $payload['vendorType'] == 'bank'?'bankAccountNumber':'cardNumber';
            $statustxt = $payload['statustxt'];
            $masked = $this->_mask($vendoraccountNo,0,strlen($vendoraccountNo)-4);
            $msg= array('statustxt' => $statustxt,'description'=>$masked.' account closed ');
            $sql = "delete from vendor WHERE $type='$vendoraccountNo' && accountNo='$accountNo'";

            $stmt = $this->conn->prepare($sql); 
            $stmt->execute();
            if (!$stmt->rowCount()) 
                throw new Exception ('Error check parameter values');       

            $payload['reference']=$this->reference;
            $payload['fulltimestamp'] = date('Y-m-d H:i:s');
            $payload['method'] = 'unLinkAccount';
            $result['resultcode']='102';
            
            $result['result']=$msg;
            $res=$msg;
            
            $respArray = $this->_getResponse(__FUNCTION__,$res, $payload['transid'], $this->reference);
            
        }catch (Exception $e) {

            
            $respArray = $this->_getError( __FUNCTION__,$e->getMessage(),'064',$data->transid, $this->reference);
        }
        return (json_encode($respArray));
    }
    public function nameLookup($data)   {
        //check for required fields eg transid return accountprofile info of this transid
        $sql=null;
        try{
            $err = Validate::nameLookup($data);
           $flag=false;
            if (!empty($err))
                throw new Exception($err);
                
            $payload = (array)$data;
           
            $accountNo = $payload['accountNo'];
           

                
           if (!isset($payload['utilitycode'])  &&  isset($payload['utilityref'])){
                
                $res = $this->_mnoLookup($payload['utilityref']);
                if($res)
                    return json_encode($respArray = $this->_getResponse(__FUNCTION__,$res, $payload['transid'], $this->reference));
                else
                    throw new Exception ('invalid account');
            }
          else if (isset($payload['utilitycode'])  &&  isset($payload['utilityref'])){
                               
               $res = $this->_bankLookup($payload['utilitycode'],$payload['utilityref']);
                if ($res)
                    return json_encode($respArray = $this->_getResponse(__FUNCTION__,$res, $payload['transid'], $this->reference));
                else
                    throw new Exception ('invalid account'); 
            } 
            
            $sql ='SELECT firstName, lastName, tier,customerNo,accountNo, msisdn,  REPLACE(REPLACE(status,0,\'close\'),1,\'open\') AS statustxt,  lastupdated from accountprofile  where accountNo="'.$accountNo.'"';
                
                //$sql ="SELECT firstName, lastName, tier,customerNo,accountNo, msisdn,  REPLACE(REPLACE(status,0,\'false\'),1,\'true\') AS statustxt, REPLACE(REPLACE(active,1,\'true\'),0,\'false\') AS activetxt, email, addressLine1,addressCity, addressCountry,dob as dateofbirth,state, gender, nationality, currency, balance, lastupdated from accountprofile where accountNo = '$accountNo'";

                $stmt = $this->conn->query( $sql );
                $res = $stmt->fetch(PDO::FETCH_ASSOC);

                $stmt_b = $this->conn->query('select bankName, bankBranch, bankAccountName, bankAccountNumber from vendor where vendorType="bank" && accountNo="'.$payload['accountNo'].'"');
                $res_b = $stmt_b->fetch(PDO::FETCH_ASSOC);
                
                //PALMPAY628422111032
                $stmt_c = $this->conn->query('select cardHolderName, cardNumber,cardType, exp from vendor where vendorType="card" && accountNo="'.$payload['accountNo'].'"');
                $res_c = $stmt_c->fetch(PDO::FETCH_ASSOC);
                if (!empty($res_b) && !empty($res_c))
                    $merge = array_merge($res_b, $res_c);
                else if (!empty($res_b))
                    $merge = $res_b;
                else if (!empty($res_c))
                    $merge = $res_c;

                if (!empty($merge)){
                    foreach($merge as $key => $val){
                        if ($key == 'bankAccountNumber' || $key == 'cardNumber')
                            $res[$key] = $this->_mask($val,0,strlen($val)-4);
                        else
                        $res[$key] = $val;
                    }
                }
            
                
                $respArray = $this->_getResponse(__FUNCTION__,$res, $payload['transid'], $this->reference);
            }
        //}
            catch (Exception $e) {

            
                $respArray = $this->_getError( __FUNCTION__,$e->getMessage(),'080',$data->transid, $this->reference);

                
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
                $msisdn = $this->_get_msisdn($account);
               
                        /*transref is the needle transid and msisdn should belong*/
                $sql ="select fulltimestamp,t.transid,t.reference,msisdn,amount, message,name,utilitycode,utilityref, transtype, geocode,posId, tinfo.comments from transaction as t LEFT JOIN tinfo ON tinfo.transid=t.transid where t.transid = '".$data->transref."' && msisdn='$msisdn'";
               
                $stmt = $this->conn->query( $sql );
                
                $res = $stmt->fetch(PDO::FETCH_ASSOC);
                if(!$res ){
                    $err = "this transaction does not exist";
                    throw new Exception ($err);
                }
                   
                
                else
                    $respArray = $this->_getResponse('transactionLookup',$res, $payload['transid'], $this->reference);
            }
            catch (Exception $e) {
    
               $respArray = $this->_getError( __FUNCTION__,$e->getMessage(),'066',$data->transid, $this->reference);
    
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
            $flag = 'P2P';  
            $msisdn = self::_get_msisdn($payload['accountNo']);
            $utilityref = self::_get_msisdn($payload['utilityref']);

             
            //save extra info into tinfo table
            if(!Validate::setTinfo($payload, $this->reference)){
                $err="Internal Error 070";
                throw new Exception($err);
            }

             
           //transid, reference, accountNo, accountNo to receiver,amount to transfer
              
            $selcom = new DbHandler();
            $res= $selcom->fundTransfer($payload['transid'],$this->reference,$utilityref, $msisdn,$payload['amount']);
            if ($res['resultcode'] !== '200')
                return json_encode($this->_getError( __FUNCTION__,$res['result'],$res['resultcode'],$data->transid, $this->reference));
            else
                $respArray = $this->_getResponse(__FUNCTION__,$res['result'], $payload['transid'], $this->reference);        }
        catch (Exception $e) {
            
            $respArray = $this->_getError( __FUNCTION__,$e->getMessage(),'067',$data->transid, $this->reference);

        }
       //die(print_r($respArray));
        return (json_encode($respArray));
    }
    public function transferFundsBank($data)   {
        try{
            $result=null;
            //check for required fields eg transid return accountprofile info of this transid
            $err = Validate::transferFunds($data);
            if (!empty($err))
                throw new Exception($err);
               
            $payload = (array)$data;

            $msisdn = self::_get_msisdn($payload['accountNo']);
            $sender = self::_getProfile($payload['accountNo']);
            //check sender card status active state         
           
            
            //check receiver card status active state
            /* check bankHolderName param to transfer to palmpayto bank
            */
            if (!isset($payload['accountHolderName']) || empty($payload['accountHolderName'])){
                    
                $err="missing parameter accountHolderName ";                    
                throw new Exception($err);
            }
            else  if (!isset($payload['bankName']) || empty($payload['bankName'])){
                    
                $err="missing parameter bankName ";                    
                throw new Exception($err);
            }
           
            else
                $flag = 'bank';

           $utilityref  = $payload['utilityref'];
            $dummy = array("accountNo" =>$payload['accountNo'],"balance" => $sender['balance'],"amount" => $payload['amount'],"dated" => date('Y-m-d H:i:s'),"description" => "Successfully sent funds to ".$payload['accountHolderName'].' at the Bank: '.$payload['bankName']);
               $arr = array("resultcode" =>"200","result"  => $dummy);
               $msg = array("status" => "SUCCESS","method" => __FUNCTION__,"data" => $arr);
               $res = array("transid" => $payload['transid'],"reference" =>$this->reference, "responseCode" =>"200","Message" => $msg );
               //$res= $selcom->fundTransfer($payload['transid'],$payload['reference'],$payload['utilityref'], $msisdn,$payload['amount']);

         
            $respArray = $this->_getResponse( __FUNCTION__,$dummy, $payload['transid'], $this->reference);
        }
        catch (Exception $e) {

            
            $respArray = $this->_getError( __FUNCTION__,$e->getMessage(),'067',$data->transid, $this->reference);

        }
       //die(print_r($respArray));
        return (json_encode($respArray));
    }
   
    public function fundTransferWallet($data){        
    try{
        //check for required fields eg transid return accountprofile info of this transid
        $err = Validate::payUtility($data);
        if (!empty($err))
            throw new Exception($err);
        $payload = (array)$data;        
      

        $payload = (array)$data;
        $payload['reference']=$this->reference;
        
        $msisdn = $this->_get_msisdn($payload['accountNo']); //agent has msidn number not accountNo
        
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
       

        $selcom = new DbHandler();
       $res=$payload;
        unset($res['reference']);

        $respArray = $this->_getResponse(__FUNCTION__,$res, $payload['transid'], $this->reference);

    }catch (Exception $e) {
        $respArray = $this->_getError(__FUNCTION__,$e->getMessage(),'073',$data->transid, $this->reference);
    }
    return json_encode($respArray);
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
                $msisdn = $this->_get_msisdn($account);
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

             
            $respArray = $this->_getError( __FUNCTION__,$e->getMessage(),'068',$data->transid, $this->reference);

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

            //$payload['reference']=$this->reference;
            $account = $payload['accountNo'];
            $msisdn = $this->_get_msisdn($account);
             
            $days = isset($payload['days'])?$payload['days']:3;
            $start = isset($payload['start'])?$payload['start']:'';
            $end = isset($payload['end'])?$payload['end']:'';
            $date = isset($payload['date'])?$payload['date']:'';
           
            $payload['fulltimestamp'] = date('Y-m-d H:i:s');
            $payload['method'] = 'getStatement';
            $selcom = new DbHandler();
            if($start !== '' && $end !==''){
                $end = date('Y-m-d', strtotime($end . ' +1 day'));             
                $res = $selcom->statementEnquiryRange($msisdn, $start, $end);
            }
             else if($date !== '' ) {
                $res = $selcom->statementEnquiryDay($msisdn,date('Ymd',strtotime($date)));
               
             }
             else 
                $res = $selcom->statementEnquiry($msisdn,$days); 
              
            $respArray = $this->_getResponse('getStatement',$res, $payload['transid'], $this->reference);

        }
        catch (Exception $e) {

            
            $respArray = $this->_getError( __FUNCTION__,$e->getMessage(),'069',$data->transid, $this->reference);

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
            
            //update accountprofile and card, both status and active variables
            $sql = "UPDATE card SET status='".$status."', active='".$status."' where accountNo='".$accountNo."'; ";
            $sql2 =" UPDATE accountprofile SET status='".$status."', active='".$status."' where accountNo='".$accountNo."'";

            $stmt = $this->conn->query($sql);             

            $stmt2 = $this->conn->query($sql2);
            

            $sql3 ='SELECT firstName, lastName, tier, accountNo, msisdn,  REPLACE(REPLACE(status,0,\'close\'),1,\'open\') AS statustxt, /*REPLACE(REPLACE(active,0,\'close\'),1,\'open\') AS activetxt,*/ lastupdated from accountprofile  where accountNo="'.$accountNo.'"';
            $stmt3 = $this->conn->query($sql3);

            $res = $stmt3->fetch(PDO::FETCH_ASSOC);
            $respArray = $this->_getResponse('changeStatus',$res, $payload['transid'], $this->reference);


        }catch (Exception $e) {

          
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
            $err = Validate::addCash($data);
            
            if (!empty($err))
                throw new Exception($err);
            $payload = (array)$data;


            $payload = (array)$data;
            //$payload['reference']=$this->reference;
            $accountNo = $payload['accountNo'];
           

            $profile = $this->_getProfile($accountNo);
           /* if(!$profile)
                die('here '.print_r($profile));*/
            //$name =  $profile['firstName'].' '. $profile['lastName'];
            $amount = $payload['amount'];
            $msisdn = $profile['msisdn'];

            $utilitycode = 'SPCASHIN'; //$payload['utilitycode'];
            
            $selcom = new DbHandler();
            /*
            * FOR SIMULATOR DO P2P BUT ON PRODUCTION YOU DONT HAVE TO DO ANYTHING, THE CARD BALANCE WILL GET UPDATED SPCASHIN
            *
            */
            $res = $selcom->utilityPayment($payload['transid'],'P2P',$msisdn,$msisdn,$amount,$this->reference);
            if ($res['resultcode'] !== '000')
                return json_encode($this->_getError( __FUNCTION__,$res['result'],$res['resultcode'],$data->transid, $this->reference));
            else
                $respArray = $this->_getResponse(__FUNCTION__,$res['result'], $payload['transid'], $this->reference);


        }catch (Exception $e) {

            
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
            
            $msisdn = $this->_get_msisdn($payload['accountNo']); //agent has msidn number not accountNo
            
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
           

            $selcom = new DbHandler();
//$result = $selcom->utilityPayment($transid,$utilitycode,$utilityref,$msisdn,$amount,$payload['reference']);
            $res=$payload;

            $respArray = $this->_getResponse('payUtility',$res, $payload['transid'], $this->reference);

        }catch (Exception $e) {

           
            $respArray = $this->_getError('payUtility',$e->getMessage(),'073',$data->transid, $this->reference);
        }
        return json_encode($respArray);
    }
    public function freezeFunds($data)   {
        try{
            //check for required fields eg transid return accountprofile info of this transid
           
            $err = Validate::freezeFunds($data);
            if (!empty($err))
                throw new Exception($err);
               
            $payload = (array)$data;
            $ref=$this->reference;
            $accountNo = $payload['accountNo'];        
            $msisdn = self::_get_msisdn($accountNo);
            $amount = $payload['amount'];
            $transid = $payload['transid'];
           // die($transid.' '.$ref.' '.$accountNo.' '.$msisdn.' '.$amount);
            //create transaction to debit card of the amount added to suspense
            $selcom = new DbHandler();
            $res = $selcom->freezeFunds($transid,$ref,$accountNo, $amount);
            die(print_r($res));
            if ($res['resultcode'] !== '200')
                return json_encode($this->_getError( __FUNCTION__,$res['result'],$res['resultcode'],$data->transid, $this->reference));
            else
                $respArray = $this->_getResponse(__FUNCTION__,$res['result'], $payload['transid'], $this->reference);
    }
        catch (Exception $e) {

           
            $respArray = $this->_getError( __FUNCTION__,$e->getMessage(),'074',$data->transid, $this->reference);
        }
        return (json_encode($respArray));
    }
    public function unFreezeFunds($data)   {
        try{
            //check if the reference is the same as on in the transaction

            $err = Validate::unFreezeFunds($data);
            if (!empty($err))
                throw new Exception($err);

            $payload = (array)$data;
            $ref=$this->reference;
            $accountNo = $payload['accountNo'];
            //$customer = $this->_getAccountNo($account);
            $payload['reference'] = $this->reference;
            $payload['msisdn'] = self::_get_msisdn($accountNo);
            //$utilityref = $payload['utilityref'];//previous reference
            //$transref = $payload['transref'];//previous reference


            //create transaction to debit card of the amount added to suspense
            $selcom = new DbHandler();
            $res = $selcom->unFreezeFunds($payload);
             
           
            $respArray = $this->_getResponse(__FUNCTION__,$res['result'], $payload['transid'], $this->reference);
        }
        catch (Exception $e) {

        $respArray = $this->_getError(__FUNCTION__,$e->getMessage(),'081',$data->transid, $this->reference);
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

            $respArray = $this->_getError('requestCard',$e->getMessage(), '079',$data->transid, $this->reference);
            
        }
        return (json_encode($respArray));

    }
    public function search($data, $except){
       
        
        if (!isset($data->transref))
            return  json_encode($this->_getError('transref','transref parameter missing', 077,$data->transid, $this->reference));

            $searchthis =$data->transref;
        //$searchthis ='"transid":"'.$data->transref.'"';
        
        $matches = array();

        $handle = @fopen("transsnet.log", "r");
        if ($handle)
        {	
            while (!feof($handle))
            {
                $buffer = fgets($handle);
                
                if(strpos($buffer, $searchthis) !== FALSE && !(strpos($buffer, $except)))
                //print_r($buffer);
                    $matches[] = ($buffer);
                    
            }
            fclose($handle);
        }

        //show results:
     foreach ($matches as $match){
        $pos = strpos($match, '{');
        $obj = json_decode (substr($match,$pos,strlen($match)));
        print_r(json_encode($obj));
     }
       
       

    }
    public function cashout($data)   {
        //check for required fields eg transid return accountprofile info of this transid
        $sql=null;
        try{
            $err = Validate::cashout($data);
           
            if (!empty($data))
                throw new Exception($err);
                
            $payload = (array)$data;
            
           /* $result = array();
            $result['status']="SUCCESS";
            $result['method']='cashout'; 
            $result['resultcode'] ='082';
            */
            $res=$data->message;//'cashout requested';
            //$res['data']=$result;
            
            
            $respArray = $this->_getResponse(__FUNCTION__,$res, $payload['transid'], $this->reference);
        }
        catch (Exception $e) {

           
            $respArray = $this->_getError(__FUNCTION__,$e->getMessage(),'082',$data->transid, $this->reference);

            
        }
       
        return (json_encode($respArray));
    }
    public function reverseTransaction($data)   {
        //check for required fields eg transid return accountprofile info of this transid
        $sql=null;
        try{
            //same params as transactionLookup which transref you want to reverse
            $err = Validate::reverseTransaction($data);
           
            if (!empty($err))
                throw new Exception($err);
                           
            $payload = (array)$data;
            $fulltimestamp = date('Y-m-d H:i:s'); 
            $accountNo = $payload['accountNo'];
            $profile = self::_getProfile($accountNo);
            $payload['firstName'] =$profile['firstName'];
            $payload['lastName'] = $profile['lastName'];
            $payload['reference'] = $this->reference;
            //$transid = $payload['transid'];
            $trans = self::_getTransaction($payload['transref']);
            
            $payload['amount_on_file'] = $trans['amount'];
            
            $msisdn = $profile['msisdn'];
           
            
            $res='Your request is being processed. You will receive a notificaton when the transaction has been completed';
            
            $respArray = $this->_getResponse(__FUNCTION__,$res, $payload['transid'], $this->reference);

           /* send notification to selcom*/
            $to = "salma@selcom.net";
            $body = '';
            foreach ($payload as $key => $val){
                $body .= $key .': '.$val.'<br>';
            }
            if (send_email( $to, 'Selcom Help Desk', 'reverseTransaction '.$data->transref, '<h2>reverse transaction :</h2><br>'.$body ))
            echo "sent";
           else
             echo "Error";

            
            $respArray = $this->_getResponse('reverseTransaction',$res, $payload['transid'], $this->reference);
        }
        catch (Exception $e) {
           
            $respArray = $this->_getError('reverseTransaction',$e->getMessage(),'083',$data->transid, $this->reference);

            
        }
       
        return (json_encode($respArray));
    }
    public function sendReverseTransactionNotification($data)   {
        //check for required fields eg transid return accountprofile info of this transid
        $sql=null;
        try{
            //same params as transactionLookup which transref you want to reverse
           /* $err = Validate::sendReverseTransactionNotification($data);
           
            if (!empty($err))
                throw new Exception($err);
            */  
            
          $req = array("channel" =>$data->channel,"origReference" =>$data->origReference,"reference" => $this->reference, "terminalId" => $data->terminalId); 
             
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

                $response = json_decode(curl_exec($curl),true);
                $err = curl_error($curl);

                curl_close($curl);

                if ($err) {
                    $curl_err = "cURL Error #:" . print_r($err);
                    throw new Exception($curl_err);
                }  
            
            $respArray = $this->_getResponse('atmWithdrawalReversal',$response['message']['data'], $response['transId'], $this->reference);
        }
        catch (Exception $e) {           
          
            $respArray = $this->_getError('atmWithdrawalReversal',$e->getMessage(),'085',$data->transid, $this->reference);
        }
       
        return (json_encode($respArray));
    }
    public function sendVoucherNotification($data)   {
        //check for required fields eg transid return accountprofile info of this transid
        $sql=null;
        try{
            //same params as transactionLookup which transref you want to reverse
               
            
          $req = array("channel" =>$data->channel,"voucher" =>$data->voucher,"reference" => $this->reference, "terminalId" => $data->terminalid); 
             
            /*send notification to Transsnet*/
                $curl = curl_init();
                curl_setopt_array($curl, array(
                CURLOPT_URL => "http://172.29.204.115:8080/selcom/withdraw/voucher",
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
                $curl_err = "cURL Error #:" . print_r($err);
                throw new Exception($curl_err);
                } 
            
            $respArray = $this->_getResponse('redeemVoucher',$response['message']['data'], $response['transid'], $this->reference);
        }
        catch (Exception $e) {
            
            $respArray = $this->_getError('redeemVoucher',$e->getMessage(),'086',$data->transid, $this->reference);
        }
       
        return (json_encode($respArray));
    }
    public function pullFromCard($data)   {
        //check for required fields eg transid return accountprofile info of this transid
        $sql=null;
        try{
             $err = Validate::pullFromCard($data);
           
            if (!empty($err))
                throw new Exception($err);

        $payload = (array)$data;
        $fulltimestamp = date('Y-m-d H:i:s'); 
        $accountNo = $payload['accountNo'];
        $profile = self::_getProfile($payload['accountNo']);
        $amount = $payload['amount'];
        $balance = (int)$profile['balance']+(int)$payload['amount'];;
        if ($card = self::_getLinkedCard($payload['accountNo'], $payload['cardHolderName'], $payload['cardNumber'], $payload['cardType'],$payload['exp'], $payload['cvv']))
        
            $res = 'Withdrawal amount of : '.$amount.' from card to your PALMPAY, balance of: '.$balance.'';
        else{
            $err ='This card does not exist';
            throw new Exception($err);
        }
    
        
        $respArray = $this->_getResponse(__Function__,$res, $payload['transid'], $this->reference);
              
        }
        catch (Exception $e) {
            
            $respArray = $this->_getError(__Function__,$e->getMessage(),'089',$data->transid, $this->reference);
        }
       
        return (json_encode($respArray));
    }
  
}
    
