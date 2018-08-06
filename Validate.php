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

        if (!isset($payload->timestamp) || empty($payload->timestamp )) {
            return 'Missing parameter timestamp';
        }
        if (isset($payload->timestamp) && !is_numeric((int)$payload->timestamp ) ) {
            return 'parameter "timestamp" must be in GMT timestamp format ' .$payload->timestamp ;
        }
        if (!isset($payload->method) || empty($payload->method)) {

            return 'Missing parameter method';
        }
        if (!isset($payload->requestParams) || empty($payload->requestParams)) {
            return 'Missing request paramaters';
        }
        if (!isset($payload->requestParams->transid) || empty($payload->requestParams->transid)) {
            return 'Missing parameter transid';
        }
        $num = self::_checkTransid($payload->requestParams->transid);
        if ($num > 0) {
            return ' Duplicate transid';
        }
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
            if ($state != 'active'){
                return 'this account is '.$state;
            } 
         return '';
       
       

    }
    public static function _getAccountNo($customerNo){
       $conn = DB::getInstance();
        $sql ="select accountNo from accountProfile where customerNo ='$customerNo' || msisdn ='$customerNo' ";

        $stmt = $conn->prepare( $sql );
        $stmt->execute();
        $result = $stmt->fetchColumn();
        return $result;

    }
    public static function _getSuspense($accountNo){
        //get accountNo;
       $conn = DB::getInstance();
        //$accountNo = Validate::_getAccountNo($customerNo);

        $sql ="select suspense from card where id ='$accountNo'";

        $stmt = $conn->prepare( $sql );
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

            $stmt = $conn->prepare( $sql );
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
            $arr['comments'] = isset($payload['comments'])?$payload['comments']:'';
           foreach($arr as $key => $val){
                if ($val){
                    $col.=$key .',';
                    $value.="'".$val."'".',';
                }

            }
            $col = rtrim($col,',');
            $value = rtrim($value,',');
            $sql ="INSERT INTO tinfo ($col) VALUES ($value)";

            $stmt = $conn->prepare( $sql );
            $stmt->execute();

            unset($payload['transtype']);
            unset($payload['geocode']);
            unset($payload['comments']);
            return $payload;
        }
        catch (Exception $e) {
            return $e->getMessage();
            return false;
        }

    }
    public static function _checkTransid($transid){

        
           $conn = DB::getInstance();
            $sql ="select id, transid  from transaction where transid='".$transid."'";
            $stmt = $conn->prepare( $sql );
            $stmt->execute();
            $result = $stmt->fetchAll();
            return $stmt->rowCount();

    }
    public static function _checkVendor($vendorName,$vendorBranch,$vendorAccountNumber, $accountNo ){
        $conn = DB::getInstance();
        $sql ="select id from vendor where vendorName='".$vendorName."' && vendorBranch='".$vendorBranch."'  && vendorAccountNumber='".$vendorAccountNumber."' && accountNo='".$accountNo."'";
        $stmt = $conn->prepare( $sql );
        $stmt->execute();
        $result = $stmt->fetchAll();        
        return $stmt->rowCount();
    }
    public static function openAccount($payload){

        $err = array();
        try{

           if (!isset($payload->transid) || empty($payload->transid)) {
                return 'missing parameter transid';
            }
            if (!isset($payload->customerNo) || empty($payload->customerNo)) {
                return 'missing parameter customerNo ';
            }
            if (!isset($payload->firstName) || empty($payload->firstName )) {
                return 'missing parameter firstName ';
            }
            if (!isset($payload->lastName) || empty($payload->lastName )) {
                return 'missing parameter lastName ';
            }
            if (!isset($payload->msisdn) || empty($payload->msisdn)) {
                return 'missing parameter msisdn';
            }
           
           $conn = DB::getInstance();
            $sql ="select accountNo from accountProfile where customerNo='".$payload->customerNo."' || msisdn='".$payload->msisdn."'";
            $stmt = $conn->prepare( $sql );
            $stmt->execute();
            if ($stmt->rowCount() > 0){
                $res = $stmt->fetch(PDO::FETCH_ASSOC);
                return 'Account already exists accountNo:' .($res['accountNo']).' customerNo:'.$payload->customerNo .' msisdn: '.$payload->msisdn;
            }
           //no check for accountNo and state as its new*/
        }
        catch(Exception $e){
            return  $e->getMessage();
        }
       
    }
    public static function updateAccount($payload) {
        $err = array();
        if (!isset($payload->accountNo) || empty($payload->accountNo)) {
            return 'missing parameter accountNo';
        }
        if (!isset($payload->transid) || empty($payload->transid)) {
            return 'missing parameter transid';
        }

       return  $state = self::checkAccount($payload->accountNo); 
        //die($state);      
        //if ($state != 'active')
          

    }
    public static function nameLookup($payload) {
        $err = array();
        if (!isset($payload->accountNo) || empty($payload->accountNo))  {
            return 'missing parameter accountNo nameLookup';
        }
        
        if (!isset($payload->transid) || empty($payload->transid)) {
            return 'missing parameter transid';
        }
       
        return $state = self::checkAccount($payload->accountNo);       
         /*if ($state != '')
           
            */       
       
    }
    public static function requestCard($payload) {
        $err = array();
        if (!isset($payload->accountNo) || empty($payload->accountNo)){            
            return 'missing parameter accountNo ';
        }
        if(!isset($payload->msisdn) || empty($payload->msisdn)) { 
            return 'missing parameter msisdn ';
        }

        if (!isset($payload->transid) || empty($payload->transid)) {
            return 'missing parameter transid';
        }

        $state = self::checkAccount($payload->accountNo); 
        //die($state);      
       
          

       
    }
    public static function transactionLookup($payload){
        $err = array();

        if (!isset($payload->accountNo) || empty($payload->accountNo)) {          
            return 'missing parameter accountNo  ';
        }
        if (!isset($payload->msisdn) || empty($payload->msisdn)) {          
            return 'missing parameter msisdn  ';
        }
        if (!isset($payload->transid) || empty($payload->transid)) { 
            return 'missing parameter transid';
        }
        if (!isset($payload->transref) || empty($payload->transref)) {
            return 'missing parameter transref. transref is the transaction id you would like to lookup, where as transid is this current transaction';
        }
        if (!Validate::_checkTransid($payload->transref, $payload->msisdn)) {
            return 'this transaction does not exist';
        }
       return  $state = self::checkAccount($payload->accountNo); 
        //die($state);      
       
          

      
    }
    public static function transferFunds($payload)    {
        $err = array();
        if (!isset($payload->accountNo) || empty($payload->accountNo)) {
            return 'missing parameter accountNo';
        }
        if (!isset($payload->transid) || empty($payload->transid)) {
            return 'missing parameter transid';
        }
        if (!isset($payload->utilityref) || empty($payload->utilityref)) {
            return 'utilityref ';
        }
        if (!isset($payload->amount) || empty($payload->amount)) {
            return 'missing parameter amount';
        }
        if (!isset($payload->currency) || empty($payload->currency)) {
            return 'missing parameter currency ';
        }

       return  $state = self::checkAccount($payload->accountNo); 
        //die($state);      
       
          

    }
    public static function getStatement($payload) {
        $err = array();
        if (!isset($payload->accountNo) || empty($payload->accountNo)){
            return 'missing parameter accountNo ';
        }
        if (!isset($payload->transid) || empty($payload->transid)) {
            return 'missing parameter transid';
        }
       
       
    }
    public static function checkBalance($payload) {
        $err = array();
        if (!isset($payload->accountNo) || empty($payload->accountNo)){
            return 'missing parameter accountNo ';
        }
        if (!isset($payload->transid) || empty($payload->transid)) {
            return 'missing parameter transid';
        }
       
       
    }
    public static function accountState($payload) {
        $err = array();
        if (!isset($payload->accountNo) || empty($payload->accountNo)) {
            return 'missing parameter accountNo';
        }
        if (!isset($payload->statustxt) || empty($payload->statustxt)) {
           return 'status ';
        }
        if (!isset($payload->transid) || empty($payload->transid)) {
            return 'missing parameter transid';
        }
      // return  $state = self::checkAccount($payload->accountNo); 
        //die($state); 
        $conn = DB::getInstance(); 
        $sql ="select msisdn from accountprofile where accountNo='".$payload->accountNo."'";
        $stmt = $conn->prepare( $sql );
        $stmt->execute();
        $result = $stmt->fetchColumn();
        $msisdn = $result;//)?false:$result['msisdn'];      
      
         if ($msisdn == ''){
           
            return 'account does not exist';
         }     
       
          

       
    }
    public static function reserveAccount($payload) {
        $err = array();
        if (!isset($payload->accountNo) || empty($payload->accountNo))
            if(!isset($payload->msisdn) || empty($payload->msisdn)) {
            return  'accountNo or missing parameter msisdn';
        }

        if (!isset($payload->transid) || empty($payload->transid)) {
            return 'missing parameter transid';
        }
        if (!isset($payload->amount) || empty($payload->amount)) {
            return 'missing parameter amount';
        }
       return  $state = self::checkAccount($payload->accountNo); 
        //die($state);      
       
          

      

    }
    public static function unReserveAccount($payload) {
        $err = array();
        if (!isset($payload->accountNo) || empty($payload->accountNo)) {
            return  'missing parameter accountNo';
        }
        if (!isset($payload->msisdn) || empty($payload->msisdn)) {
            return  'missing parameter msisdn';
        }
        if (!isset($payload->reference) || empty($payload->reference)) {
            return  'missing parameter reference';
        }
        if (!isset($payload->transid) || empty($payload->transid)) {
            return 'missing parameter transid';
        }
        if (!isset($payload->amount) || empty($payload->amount)) {
            return 'missing parameter amount';
        }

        if(isset($payload->accountNo) && Validate::_getSuspense($payload->accountNo) == 0){
            return 'You do not have funds to release at this time';
        }
        /*check ref and msisdn reserveaccount*/
        $flag = Validate::_checkRef($payload);

        if(Validate::_checkRef($payload) != 0){
            return 'reference is invalid';
            //die(print_r($err));
        }

       return  $state = self::checkAccount($payload->accountNo); 
        //die($state);      
       
          

       
    }
    public static function payUtility($payload)    {
        //die(print_r($payload));
        $err = array();
        /*if (!isset($payload->msisdn) || empty($payload->msisdn))  {
            return 'missing parameter msisdn';
        }*/
        if (!isset($payload->transid) || empty($payload->transid)) {
            return 'missing parameter transid';
        }
        if (!isset($payload->utilitycode) || empty($payload->utilitycode)) {
            return 'missing parameter utilitycode ';
        }
        if (!isset($payload->utilityref) || empty($payload->utilityref)) {
            return 'missing parameter utilityref ';
        }
        if (!isset($payload->amount) || empty($payload->amount)) {
            return 'missing parameter amount';
        }
        if (!isset($payload->currency) || empty($payload->currency)) {
            return 'missing parameter currency ';
        }
       return  $state = self::checkAccount($payload->accountNo);
       
           
        
    }
    public static function cashin($payload)    {
        $err = array();
        if (!isset($payload->msisdn) || empty($payload->msisdn)) {
            return 'missing parameter msisdn';
        }
        if (!isset($payload->transid) || empty($payload->transid)) {
            return 'missing parameter transid';
        }

        if (!isset($payload->amount) || empty($payload->amount)) {
            return 'missing parameter amount';
        }

       return  $state = self::checkAccount($payload->accountNo); 
        //die($state);      
       
          
        
    }
    public static function linkAccount($payload)    {
        $err = array();
        if (!isset($payload->accountNo) || empty($payload->accountNo)) {
            return 'missing parameter accountNo';
        }
        if (!isset($payload->transid) || empty($payload->transid)) {
            return 'missing parameter transid';
        }
        if (!isset($payload->vendorType) || empty($payload->vendorType)) {
            return 'missing parameter institution type ';
        }
        if ($payload->vendorType==strtolower('bank') && (!isset($payload->vendorName) || empty($payload->vendorName))) {
            return 'missing parameter institution name ';
        }
        if ($payload->vendorType==strtolower('bank') && (!isset($payload->vendorBranch) || empty($payload->vendorBranch))) {
            return 'institution branch name ';
        }
        if ($payload->vendorType==strtolower('bank') && (!isset($payload->vendorAccountName) || empty($payload->vendorAccountName))) {
            return 'institution account name ';
        }
        if ($payload->vendorType==strtolower('bank') && (!isset($payload->vendorAccountNumber) || empty($payload->vendorAccountNumber))) {
            return 'institution account number ';
        }
        if ($payload->vendorType==strtolower('bank')) {
            $check = self::_checkVendor($payload->vendorName,$payload->vendorBranch,$payload->vendorAccountNumber, $payload->accountNo );
            if($check >=1 ){
                return "Account already linked";
            }
        }

    }




}
