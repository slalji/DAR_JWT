<?php
require_once ("config.php");
require_once ("DB.php");
/**
 * Validates Selcom API Payload of
 * {
*	"iss": APP_NAME,
*	"method": "reserveAmount",
*	"timestamp": "1529998743",
*	"requestParams": {
*		"transid": "580929745048",
*		"String": "String",
*		"String": "String",
*		"String": "String",
*		"String":"String",
*		"currency": "TZS"
*
*	}
*}
*
 *
 * PHP version 5
 *
 * @modal Validate
 * @author   Salma Lalji
 **/
class Validate
{
    public static function valid($payload)    {

        $err = array();
        if (!isset($payload->iss) || empty($payload->iss)) {
            $err[]='parameter issuer "iss" may not be empty';
        }
        if (!isset($payload->timestamp) || empty($payload->timestamp )) {
            $err[]='parameter "timestamp" may not be empty';
        }
        if (isset($payload->timestamp) && !is_numeric((int)$payload->timestamp ) ) {
            $err[]='parameter "timestamp" must be in numeric timestamp format ' .$payload->timestamp ;
        }
        if (!isset($payload->method) || empty($payload->method)) {

            $err[]='parameter "method" may not be empty';
        }
        if (!isset($payload->requestParams) || empty($payload->requestParams)) {
            $err[]='request paramaters may not be empty';
        }
        if (!isset($payload->requestParams->transid) || empty($payload->requestParams->transid)) {
            $err[]='transid request paramater may not be empty ';
        }
        $data='';
        foreach($err as $e)
            $data .=$e.'';


        return ($data);
    }

    public static function verify($headers){
        try {
            /*
             * Look for the 'authorization' header
             */
            $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : $headers['authorization'];

            //if (isset($headers['Authorization']) || isset($headers['authorization'])) {



            if ($authHeader) {
                /*
                 * Extract the jwt from the Bearer
                 */
                list($bearer) = sscanf( $authHeader, 'Bearer %s');
                $bearer = explode(',',$bearer)[0];
                $bearer = str_replace('"','',$bearer);


                if ($bearer) {
                    if($_SERVER['REMOTE_ADDR'] == '127.0.0.1' || $_SERVER['REMOTE_ADDR'] == '192.168.24.52' || $_SERVER['REMOTE_ADDR'] == '10.10.0.2'){
                        $publicKey = file_get_contents(PUBLIC_KEY_DEBUG, true);
                    }
                    else
                        $publicKey = file_get_contents(PUBLIC_KEY_FILENAME, true);
                // if JWT invalid throw exception
                    JWT::decode($bearer, $publicKey, array('RS256'));
                    return true;
                }
                else{
                    header('HTTP/1.0 401 Unauthorized');
                    //echo('HTTP/1.0 401 Unauthorized'/*.$e*/);
                    $err ="Authentication Invalid code 101";
                    error_log("\r\n".date('Y-m-d H:i:s').' bearer not found', 3, "transsnet.log");
        
                    throw new Exception($err);
                }
            }

            else{
                header('HTTP/1.0 401 Unauthorized');
                //echo('HTTP/1.0 401 Unauthorized'/*.$e*/);
                $err ="Authentication missing code 102";
                throw new Exception($err);
            }
        /*}
        else{
            header('HTTP/1.0 401 Unauthorized');
            //echo('HTTP/1.0 401 Unauthorized');
            $err ="Authentication missing";
                        throw new Exception($err);
    }*/
    }
    catch (Exception $e) {
        /*
            * the token was not able to be decoded.
            * this is likely because the signature was not able to be verified (tampered token)
            */
        header('HTTP 1.0 401 Unauthorized');
        //echo('HTTP/1.0 401 Unauthorized'/*.$e*/);
        //echo ' Caught exception: ',  $e->getMessage(), "\n";
        $message = array();
            $message['status']="ERROR";
            $message['method']='';//.$e." : ";//.$sql;
            $result['resultcode'] ='401';
            $result['result']='code 103'.$e->getMessage();
            $message['data']=$result;

        $respArray = ['transid'=>'','reference'=>'','responseCode' => 401, "Message"=>($message)];
        return false; //echo json_encode($respArray);
        //echo json_encode($response = ["transid"=>"","reference"=>"","responseCode"=>"401","Message"=>["status"=>"ERROR","method"=>"","data"=>"HTTP 1.0 401 Unauthorized"]]);


    }

}



    public static function checkAccount($acctNo){
        $conn = DB::getInstance();
        $sql ="select msisdn from accountprofile where accountNo='".$acctNo."'";
        $stmt = $conn->prepare( $sql );
        $stmt->execute();
        $result = $stmt->fetchColumn();
        $msisdn = $result;//)?false:$result['msisdn'];      
      
         if ($msisdn == ''){
           
            return 'account does not exist';
         } 
 
         $state = Validate::_checkCard($msisdn);
            if ($state == 'active'){
                $err[]='this account is '.$state;
            } 
         return'';
       
       

    }
    public static function _getAccountNo($customerNo){
       $conn = DB::getInstance();
        $sql ="select accountNo from accountProfile where customerNo ='$customerNo' || msisdn ='$customerNo' ";

        $stmt = $db->conn->prepare( $sql );
        $stmt->execute();
        $result = $stmt->fetchColumn();
        return $result;

    }
    public static function _getSuspense($accountNo){
        //get accountNo;
       $conn = DB::getInstance();
        //$accountNo = Validate::_getAccountNo($customerNo);

        $sql ="select suspense from card where id ='$accountNo'";

        $stmt = $db->conn->prepare( $sql );
        $stmt->execute();
        $result = $stmt->fetchColumn();
        return $result;

    }
    public static function _checkRef($payload){
        //get accountNo;
       $conn = DB::getInstance();
        $ref = $payload->reference;
       $flag=true;
        try{

            $sql ="select utilitycode, utilityref, dealer, amount from transaction where reference ='$ref'";

            $stmt = $db->conn->prepare( $sql );
            $stmt->execute();
            $rows = $stmt->fetchAll( PDO::FETCH_ASSOC );

            if(!$rows)
               return false;
            $result = ($rows[0]);


            if($result['utilitycode'] != 'reserveAccount'){
                $flag=false;
            }
            else if ($result['utilityref'] != $payload->msisdn){
                $flag=false;
            }
            else if ($result['dealer'] !='TRANSSNET'){
                $flag=false;
            }
            else if ($result['amount'] !=$payload->amount){
                $flag=false;
            }

        }
        catch(Exception $e){
            $flag=false;
            //return false;
        }
        //die(print_r((int)$flag));
        return $flag;

    }
    public static function _checkCard($msisdn){

        $data = isset($err) ? $err :false;
        try{
           $conn = DB::getInstance();
            $sql ="select status, state, active  from card where msisdn='".$msisdn."'";
            $stmt = $conn->prepare( $sql );
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($result){
                $res = $result[0];
          
                if ($res['active'] == 1)
                    return 'inactive';
                else  if ($res['status'] == 0)
                    return 'inactive';
                else  if ($res['state'] != 'ON')
                    return 'inactive';
                else  if ($res['status'] == 'D')
                    return 'delete';
                else
                    return 'active';
                }
            else
                throw new Exception('invalid');
            
        }
        catch(Exception $e){
            return $e->getMessage();
        }
            

    }

    public static function setTinfo($payload){

        //get accountNo;
       $conn = DB::getInstance();
        $arr =array();
        $col =null;
        $value =null;


        try{
            $arr['transid'] = isset($payload['transid'])?$payload['transid']:'';
            $arr['reference'] = isset($payload['reference'])?$payload['reference']:'';
            $arr['transtype'] = isset($payload['transtype'])?$payload['transtype']:'';
            $arr['geocode'] = isset($payload['geocode'])?json_encode($payload['geocode']):'';
            $arr['generateVoucher'] = isset($payload['generateVoucher'])?$payload['generateVoucher']:'';
            $arr['redeemVoucher'] = isset($payload['redeemVoucher'])?$payload['redeemVoucher']:'';
            foreach($arr as $key => $val){
                if ($val){
                    $col.=$key .',';
                    $value.="'".$val."'".',';
                }

            }
            $col = rtrim($col,',');
            $value = rtrim($value,',');
            $sql ="INSERT INTO tinfo ($col) VALUES ($value)";

            $stmt = $db->conn->prepare( $sql );
            $stmt->execute();

            unset($payload['transtype']);
            unset($payload['geocode']);
            unset($payload['generateVoucher']);
            unset($payload['redeemVoucher']);
            return $payload;
        }
        catch (Exception $e) {
            return $e->getMessage();
            return false;
        }

    }
    public static function _checkTransid($transid, $msisdn){

        $data = isset($err) ? $err :false;
           $conn = DB::getInstance();
            $sql ="select id, transid  from transaction where transid='".$transid."' && msisdn='".$msisdn."'";
            $stmt = $conn->prepare( $sql );
            $stmt->execute();
            $result = $stmt->fetchAll();
            return $stmt->rowCount();

    }
    public static function openAccount($payload){

        $err = array();
        try{

            if (!isset($payload->transid) || empty($payload->transid)) {
                $err[]='transid may not be empty';
            }
            if (!isset($payload->customerNo) || empty($payload->customerNo)) {
                return 'customerNo may not be empty';
            }
            if (!isset($payload->firstName) || empty($payload->firstName )) {
                $err[]='firstName may not be empty';
            }
            if (!isset($payload->lastName) || empty($payload->lastName )) {
                $err[]='lastName may not be empty';
            }
            if (!isset($payload->msisdn) || empty($payload->msisdn)) {
                $err[]='msisdn may not be empty';
            }


           $conn = DB::getInstance();
            $sql ="select id from accountProfile where customerNo='".$payload->customerNo."' || msisdn='".$payload->msisdn."'";
            $stmt = $db->conn->prepare( $sql );
            $stmt->execute();
            if ($stmt->rowCount() > 0){
                $err[] ='Account already exists customerNo:' .$payload->customerNo .' msisdn: '.$payload->msisdn;
            }
        }
        catch(Exception $e){
            $err[]= $e->getMessage();
        }
        $data='';
        foreach($err as $e)
            $data .=$e.'';
        return ($data);
    }
    public static function updateAccount($payload) {
        $err = array();
        if (!isset($payload->accountNo) || empty($payload->accountNo)) {
            $err[]='accountNo may not be empty';
        }
        if (!isset($payload->transid) || empty($payload->transid)) {
            $err[]='transid may not be empty';
        }

        $state = self::checkAccount($payload->accountNo); 
        //die($state);      
        if ($state != '')
           return $state;

        $data='';
        foreach($err as $e)
            $data .=$e.'';
        return ($data);
    }
    public static function nameLookup($payload) {
        $err = array();
        if (!isset($payload->accountNo) || empty($payload->accountNo))  {
            return 'accountNo may not be empty nameLookup';
        }
        
        if (!isset($payload->transid) || empty($payload->transid)) {
            $err[]='transid may not be empty';
        }
       
        $state = self::checkAccount($payload->accountNo);       
         if ($state != '')
            $err[]=$state;

       
        $data='';
        foreach($err as $e)
            $data .=$e.'';


        return ($data);
    }
    public static function requestCard($payload) {
        $err = array();
        if (!isset($payload->accountNo) || empty($payload->accountNo))
            if(!isset($payload->msisdn) || empty($payload->msisdn)) {
            $err[]='accountNo or msisdn may not be empty ';
        }

        if (!isset($payload->transid) || empty($payload->transid)) {
            $err[]='transid may not be empty';
        }

        $state = self::checkAccount($payload->accountNo); 
        //die($state);      
        if ($state != '')
           return $state;

        $data='';
        foreach($err as $e)
            $data .=$e.'';
        return ($data);
    }
    public static function transactionLookup($payload){
        $err = array();

        if (!isset($payload->accountNo) || empty($payload->accountNo)) {          
            return 'accountNo  may not be empty';
        }
        if (!isset($payload->msisdn) || empty($payload->msisdn)) {          
            return 'msisdn  may not be empty';
        }
        if (!isset($payload->transid) || empty($payload->transid)) {
            $err[]='transid may not be empty';
        }
        if (!isset($payload->transref) || empty($payload->transref)) {
            return 'transref may not be empty. transref is the transaction id you would like to lookup, where as transid is this current transaction';
        }
        if (!Validate::_checkTransid($payload->transref, $payload->msisdn)) {
            $err[]='this transaction does not exist';
        }
        $state = self::checkAccount($payload->accountNo); 
        //die($state);      
        if ($state != '')
           return $state;

        $data='';
        foreach($err as $e)
            $data .=$e.'';
        return ($data);
    }
    public static function transferFunds($payload)    {
        $err = array();
        if (!isset($payload->accountNo) || empty($payload->accountNo)) {
            $err[]='accountNo may not be empty';
        }
        if (!isset($payload->transid) || empty($payload->transid)) {
            $err[]='transid may not be empty';
        }
        if (!isset($payload->utilityref) || empty($payload->utilityref)) {
            return 'utilityref may not be empty';
        }
        if (!isset($payload->amount) || empty($payload->amount)) {
            $err[]='amount may not be empty';
        }
        if (!isset($payload->currency) || empty($payload->currency)) {
            $err[]='currency may not be empty';
        }

        $state = self::checkAccount($payload->accountNo); 
        //die($state);      
        if ($state != '')
           return $state;

        $data='';
        foreach($err as $e)
            $data .=$e.'';
        return ($data);
    }
    public static function enquiry($payload) {
        $err = array();
        if (!isset($payload->customerNo) || empty($payload->customerNo))
            if(!isset($payload->msisdn) || empty($payload->msisdn)) {
            $err[]='customerNo or msisdn may not be empty ';
        }
        if (!isset($payload->transid) || empty($payload->transid)) {
            $err[]='transid may not be empty';
        }
       /* if(self::checkTransid($payload->transid)){
            $err[]='duplicate transaction';
        }
        */
        $data='';
        foreach($err as $e)
            $data .=$e.'';
        return ($data);
    }
    public static function accountState($payload) {
        $err = array();
        if (!isset($payload->accountNo) || empty($payload->accountNo)) {
            $err[]='accountNo may not be empty';
        }
        if (!isset($payload->statustxt) || empty($payload->statustxt)) {
           return 'status may not be empty';
        }
        if (!isset($payload->transid) || empty($payload->transid)) {
            $err[]='transid may not be empty';
        }
        $state = self::checkAccount($payload->accountNo); 
        //die($state);      
        if ($state != '')
           return $state;

        $data = '';
        foreach($err as $e){
            $data .= $data .' ';
        }

        return ($data);
    }
    public static function reserveAccount($payload) {
        $err = array();
        if (!isset($payload->accountNo) || empty($payload->accountNo))
            if(!isset($payload->msisdn) || empty($payload->msisdn)) {
            return $err[]='accountNo or msisdn may not be empty';
        }

        if (!isset($payload->transid) || empty($payload->transid)) {
            $err[]='transid may not be empty';
        }
        if (!isset($payload->amount) || empty($payload->amount)) {
            $err[]='amount may not be empty';
        }
        $state = self::checkAccount($payload->accountNo); 
        //die($state);      
        if ($state != '')
           return $state;

        $data = '';
        foreach($err as $e){
            $data .= $data .' ';
        }

        return ($data);

    }
    public static function unReserveAccount($payload) {
        $err = array();
        if (!isset($payload->accountNo) || empty($payload->accountNo)) {
            return $err[]='accountNo may not be empty';
        }
        if (!isset($payload->msisdn) || empty($payload->msisdn)) {
            return $err[]='msisdn may not be empty';
        }
        if (!isset($payload->reference) || empty($payload->reference)) {
            return $err[]='reference may not be empty';
        }
        if (!isset($payload->transid) || empty($payload->transid)) {
            $err[]='transid may not be empty';
        }
        if (!isset($payload->amount) || empty($payload->amount)) {
            $err[]='amount may not be empty';
        }

        if(isset($payload->accountNo) && Validate::_getSuspense($payload->accountNo) == 0){
            $err[]='You do not have funds to release at this time';
        }
        /*check ref and msisdn reserveaccount*/
        $flag = Validate::_checkRef($payload);

        if(Validate::_checkRef($payload) != 0){
            $err[]='reference is invalid';
            //die(print_r($err));
        }

        $state = self::checkAccount($payload->accountNo); 
        //die($state);      
        if ($state != '')
           return $state;

        $data = '';
        foreach($err as $e){
            $data .= $data .' ';
        }

        return ($data);
    }
    public static function payUtility($payload)    {
        //die(print_r($payload));
        $err = array();
        /*if (!isset($payload->msisdn) || empty($payload->msisdn))  {
            $err[]='msisdn may not be empty';
        }*/
        if (!isset($payload->transid) || empty($payload->transid)) {
            $err[]='transid may not be empty';
        }
        if (!isset($payload->utilitycode) || empty($payload->utilitycode)) {
            $err[]='utilitycode may not be empty';
        }
        if (!isset($payload->utilityref) || empty($payload->utilityref)) {
            $err[]='utilityref may not be empty';
        }
        if (!isset($payload->amount) || empty($payload->amount)) {
            $err[]='amount may not be empty';
        }
        if (!isset($payload->currency) || empty($payload->currency)) {
            $err[]='currency may not be empty';
        }
        $state = self::checkAccount($payload->accountNo);
        if ($state != '')
            $err[]=$state;
        
       
        $data = '';
        foreach($err as $e){
            $data .= $e .' ';
        }
        return $data;//die(print_r($data));
    }
    public static function cashin($payload)    {
        $err = array();
        if (!isset($payload->msisdn) || empty($payload->msisdn)) {
            return $err[]='msisdn may not be empty';
        }
        if (!isset($payload->transid) || empty($payload->transid)) {
            $err[]='transid may not be empty';
        }

        if (!isset($payload->amount) || empty($payload->amount)) {
            $err[]='amount may not be empty';
        }

        $state = self::checkAccount($payload->accountNo); 
        //die($state);      
        if ($state != '')
           return $state;
        

        $data = '';
        foreach($err as $e){
            $data .= $data .' ';
        }
        return ($data);
    }
    public static function linkAccount($payload)    {
        $err = array();
        if (!isset($payload->accountNo) || empty($payload->accountNo)) {
            return $err[]='accountNo may not be empty';
        }
        if (!isset($payload->transid) || empty($payload->transid)) {
            $err[]='transid may not be empty';
        }

        if (!isset($payload->bankname) || empty($payload->bankname)) {
            $err[]='bank name may not be empty';
        }
        if (!isset($payload->bankbranch) || empty($payload->bankbranch)) {
            $err[]='bank branch name may not be empty';
        }
        if (!isset($payload->bankaccountname) || empty($payload->bankaccountname)) {
            $err[]='bank account name may not be empty';
        }
        if (!isset($payload->bankaccountnumber) || empty($payload->bankaccountnumber)) {
            $err[]='bank account number may not be empty';
        }

        $data='';
        foreach($err as $e)
            $data .=$e.'';
        return ($data);
    }




}
