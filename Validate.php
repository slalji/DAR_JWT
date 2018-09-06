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
            $authHeader = null;
          
            if (isset($headers['Authorization'])) 
                $authHeader = $headers['Authorization'];
            else if (isset($headers['authorization']))
                $authHeader = $headers['authorization'];
            
               

            //            $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : $headers['authorization'];

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
 
         $state = Validate::_checkCard($acctNo);
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

        $sql ="select suspense from card where accountNo ='$accountNo'";

        $stmt = $conn->prepare( $sql );
        $stmt->execute();
        $result = $stmt->fetchColumn();
       
        return $result;

    }
    public static function _checkRef($data){
        //get accountNo;
       $conn = DB::getInstance();
       
        $ref = $data->utilityref;
       $flag='true';
        try{

            $sql ="select utilitycode, utilityref, dealer, amount, reference, transid from transaction where reference ='$ref'";

            $stmt = $conn->prepare( $sql );
            $stmt->execute();
            $rows = $stmt->fetch( PDO::FETCH_ASSOC );

            if($rows)              
                $result = ($rows);
            else
                return 'false';
//die(print_r($data).' '. print_r($rows));
//
            if($result['utilitycode'] !== 'reserveAccount'){
                return $flag='false';
               
            }
            else if ($result['utilityref'] !==  $data->accountNo){
                return $flag='false';
                
            }
            else if (strtoupper($result['dealer']) !='TRANSSNET'){
                return  $flag='false';
               
            }
            else if ($result['transid'] != $data->transref){
                return $flag='false';
            } 

        }
        catch(Exception $e){
           return  $flag='false';
            //return false;
           
        }
        
        //die(print_r((int)$flag));
        //return $flag;

    }
    public static function _checkCard($accountNo){

        $data = isset($err) ? $err :false;
        try{
           $conn = DB::getInstance();
            $sql ="select status, state, active  from card where accountNo='".$accountNo."'";
            $stmt = $conn->prepare( $sql );
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($result){
                $res = $result[0];
          
                if ($res['active'] == 0)
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

    public static function setTinfo($payload, $ref){

        //get accountNo;
       $conn = DB::getInstance();
        $arr =array();
        $col =null;
        $value =null;


        try{
            $arr['transid'] = isset($payload['transid'])?$payload['transid']:'';
            $arr['reference'] = $ref;
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
            return 'setTinfo resultcode:067.1'.$e->getMessage();
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
    public static function _checkVendorCard($cardHolderName,$cardType,$cardNumber, $accountNo ){
        $conn = DB::getInstance();
        $sql ="select id from vendor where cardHolderName='".$cardHolderName."' && cardType='".$cardType."'  && cardNumber='".$cardNumber."' && accountNo='".$accountNo."'";
        $stmt = $conn->prepare( $sql );
        $stmt->execute();
        $result = $stmt->fetchAll();        
        return $stmt->rowCount();
    }        
    public static function _checkBank($bankName,$bankBranch,$bankAccountNumber, $accountNo ){
        $conn = DB::getInstance();
        $sql ="select id from vendor where bankName='".$bankName."' && bankBranch='".$bankBranch."'  && bankAccountNumber='".$bankAccountNumber."' && accountNo='".$accountNo."'";
        $stmt = $conn->prepare( $sql );
        $stmt->execute();
        $result = $stmt->fetchAll();        
        return $stmt->rowCount();
    }
    public static function openAccount($payload){

       
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
            $sql ="select accountNo from accountprofile where customerNo='".$payload->customerNo."' || msisdn='".$payload->msisdn."'";
            $stmt = $conn->prepare( $sql );
            $stmt->execute();
            if ($stmt->rowCount() > 0){
                $res = $stmt->fetch(PDO::FETCH_ASSOC);
                return 'Account already exists';// .($res['accountNo']).' customerNo:'.$payload->customerNo .' msisdn: '.$payload->msisdn;
            }
            //check card if msisdn exists return false
            $sql ="select msisdn from card where msisdn='".$payload->msisdn."'";
            $stmt = $conn->prepare( $sql );
            $stmt->execute();
            if ($stmt->rowCount() > 0){
                $res = $stmt->fetch(PDO::FETCH_ASSOC);
                return 'Account as card already exists';// .($res['accountNo']).' customerNo:'.$payload->customerNo .' msisdn: '.$payload->msisdn;
            }
           //no check for accountNo and state as its new*/
        }
        catch(Exception $e){
            return  $e->getMessage();
        }
       
    }
    public static function updateAccount($payload) {
       
        if (!isset($payload->accountNo) || empty($payload->accountNo)) {
            return 'missing parameter accountNo';
        }
        if (!isset($payload->transid) || empty($payload->transid)) {
            return 'missing parameter transid';
        }
        if (isset($payload->customerNo) || !empty($payload->customerNo)) {
            return 'You cannot change your';
        }

       return  $state = self::checkAccount($payload->accountNo); 
        //die($state);      
        //if ($state != 'active')
          

    }
    public static function nameLookup($payload) {
       
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
       

        if (!isset($payload->accountNo) || empty($payload->accountNo)) {          
            return 'missing parameter accountNo  ';
        }
        /*if (!isset($payload->msisdn) || empty($payload->msisdn)) {          
            return 'missing parameter msisdn  ';
        }*/
        if (!isset($payload->transid) || empty($payload->transid)) { 
            return 'missing parameter transid';
        }
        if (!isset($payload->transref) || empty($payload->transref)) {
            return 'missing parameter transref';
        }
                   
       return  $state = self::checkAccount($payload->accountNo); 
        //die($state);      
       
          

      
    }
    public static function transferFunds($payload)    {
       
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
       
        if (!isset($payload->accountNo) || empty($payload->accountNo)){
            return 'missing parameter accountNo ';
        }
        if (!isset($payload->transid) || empty($payload->transid)) {
            return 'missing parameter transid';
        }
        return  $state = self::checkAccount($payload->accountNo); 
       
    }
    public static function checkBalance($payload) {
       
        if (!isset($payload->accountNo) || empty($payload->accountNo)){
            return 'missing parameter accountNo ';
        }
        if (!isset($payload->transid) || empty($payload->transid)) {
            return 'missing parameter transid';
        }
        return  $state = self::checkAccount($payload->accountNo); 
       
    }
    public static function changeStatus($payload) {
       
        if (!isset($payload->accountNo) || empty($payload->accountNo)) {
            return 'missing parameter accountNo';
        }
        if (!isset($payload->statustxt) || empty($payload->statustxt)) {
           return 'status ';
        }
        if (!isset($payload->transid) || empty($payload->transid)) {
            return 'missing parameter transid';
        }
  
        $conn = DB::getInstance(); 
        $sql ="select msisdn from accountprofile where accountNo='".$payload->accountNo."'";
        $stmt = $conn->prepare( $sql );
        $stmt->execute();
        $result = $stmt->fetchColumn();
        $msisdn = $result;    
      
         if ($msisdn == ''){
           
            return 'account does not exist';
         }       
          

       
    }
    public static function freezeFunds($payload) {
       
        if (!isset($payload->accountNo) || empty($payload->accountNo)){
            
            return  'accountNo or missing parameter accountNo';
        }

        if (!isset($payload->transid) || empty($payload->transid)) {
            return 'missing parameter transid';
        }
        if (!isset($payload->amount) || empty($payload->amount)) {
            return 'missing parameter amount';
        }
        if (self::_getSuspense($payload->accountNo) >0 )
            return 'you cannot reserve funds at this time';
       
        return  $state = self::checkAccount($payload->accountNo); 
        //die($state);      
       
          

      

    }
    public static function unFreezeFunds($payload) {
       
        if (!isset($payload->accountNo) || empty($payload->accountNo)) {
            return  'missing parameter accountNo';
        }
       
        if (!isset($payload->utilityref) || empty($payload->utilityref)) {
            return  'missing parameter utilityref';
        }
        if (!isset($payload->transid) || empty($payload->transid)) {
            return 'missing parameter transid';
        }
         

        if(isset($payload->accountNo) && Validate::_getSuspense($payload->accountNo) == 0){
            return 'You do not have funds to release at this time';
        }
        /*check ref and msisdn reserveaccount*/
        //$flag = Validate::_checkRef($payload);
        $check = Validate::_checkRef($payload);
        //die($check);
        if($check == 'false' ){
            return 'reference is invalid';
            //die(print_r($err));
        }
      
       return  $state = self::checkAccount($payload->accountNo); 
        //die($state);      
       
          

       
    }
    public static function payUtility($payload)    {
        //die(print_r($payload));
       
        if (!isset($payload->accountNo) || empty($payload->accountNo))  {
            return 'missing parameter accountNo';
        }
       
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
        /*if (!isset($payload->currency) || empty($payload->currency)) {
            return 'missing parameter currency ';
        }*/
       return  $state = self::checkAccount($payload->accountNo);
       
           
        
    }
    public static function addCash($payload)    {
       
        if (!isset($payload->accountNo) || empty($payload->accountNo)) {
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
       
        if (!isset($payload->accountNo) || empty($payload->accountNo)) {
            return 'missing parameter accountNo';
        }
        
        if (!isset($payload->transid) || empty($payload->transid)) {
            return 'missing parameter transid';
        }
        if (!isset($payload->vendorType) || empty($payload->vendorType)) {
            return 'missing parameter vendor type ';
        }
        if ($payload->vendorType==strtolower('bank') && (!isset($payload->bankName) || empty($payload->bankName))) {
            return 'missing parameter bank name ';
        }
        if ($payload->vendorType==strtolower('bank') && (!isset($payload->bankBranch) || empty($payload->bankBranch))) {
            return 'bank branch name ';
        }
        if ($payload->vendorType==strtolower('bank') && (!isset($payload->bankAccountName) || empty($payload->bankAccountName))) {
            return 'bank account name ';
        }
        if ($payload->vendorType==strtolower('bank') && (!isset($payload->bankAccountNumber) || empty($payload->bankAccountNumber))) {
            return 'bank account number ';
        }
        if ($payload->vendorType==strtolower('bank') && (isset($payload->vendorType) || !empty($payload->vendorType))) {
            $check = self::_checkBank($payload->bankName,$payload->bankBranch,$payload->bankAccountNumber, $payload->accountNo );
            if($check >=1 ){
                return "Bank account already linked";
            }
        }
        /*else{
            return "VendorType is ".$payload->vendorType;
        }
        if ($payload->vendorType==strtolower('card') && (!isset($payload->cardType) || empty($payload->cardType))) {
            return 'missing parameter bank card Type ';
        }*/
        if ($payload->vendorType==strtolower('card') && (!isset($payload->cardType) || empty($payload->cardType))) {
            return 'card type, such as Mastercard, Visa, American Express ';
        }
        if ($payload->vendorType==strtolower('card') && (!isset($payload->cardHolderName) || empty($payload->cardHolderName))) {
            return 'card holder name as shown on the card ';
        }
        if ($payload->vendorType==strtolower('card') && (!isset($payload->cardNumber) || empty($payload->cardNumber))) {
            return 'card number ';
        }
        if ($payload->vendorType==strtolower('card') && (isset($payload->cardNumber) || !empty($payload->cardNumber))) {
            if(!is_numeric($payload->cardNumber) || strlen($payload->cardNumber) != 16)
            return 'invalid card number format';
        }
        if ($payload->vendorType==strtolower('card') && (!isset($payload->exp) || empty($payload->exp))) {
            return 'expiry date in mm/yy format ';
        }
        if ($payload->vendorType==strtolower('card') && (!isset($payload->cvv) || empty($payload->cvv))) {
            return '3 digits found at the back of the card ';
        }
       /* if ($payload->vendorType==strtolower('card') && (!isset($payload->pin) || empty($payload->pin))) {
            return 'pin number ';
        }
        if ($payload->vendorType==strtolower('card') && (!isset($payload->confirmPin) || empty($payload->confirmPin))) {
            return 'confirm pin number ';
        }
        if ($payload->vendorType==strtolower('card') && ($payload->confirmPin !== $payload->confirmPin)){
            return 'pin numbers do not match ';
        }*/
        if ($payload->vendorType==strtolower('card') && (isset($payload->exp) || !empty($payload->exp))) {
           
            $expDate = explode('/',$payload->exp);
            if(!isset($expDate[1]) || $expDate[0]>12 )
             return 'invalid expiry date format of mm/yy '.$expDate[0];
            if(isset($expDate[1]) && $expDate[1]>99 )
             return 'invalid expiry date format of mm/yy '.$expDate[0].'/'.$expDate[1];            
          }
         

        if ($payload->vendorType==strtolower('card') && (isset($payload->exp) || !empty($payload->exp))) {
           $expDate = explode('/',$payload->exp);
           if('20'.$expDate[1]. str_pad($expDate[0],2,'0') < date('Ym')) {
            return 'card is expired';  
         
         }
        }
        if ($payload->vendorType==strtolower('card') && (isset($payload->vendorType) || !empty($payload->vendorType))) {
            $check = self::_checkVendorCard($payload->cardHolderName,$payload->cardType,$payload->cardNumber, $payload->accountNo );
            if($check >=1 ){
                return $payload->cardType." already linked";
            }
        }
        if ($payload->vendorType==strtolower('card') && (isset($payload->bankName) || isset($payload->bankBranch) || isset($payload->bankAccountName) ||  isset($payload->bankAccountNumber))){
            return "invalid parameters found";
        }
        if ($payload->vendorType==strtolower('bank') && (isset($payload->cardHolderName) || isset($payload->cardNumber) || isset($payload->exp) ||  isset($payload->pin) ||  isset($payload->confirmPin) ||  isset($payload->cvv))){
            return "invalid parameters found";
        }
        return  $state = self::checkAccount($payload->accountNo); 
        
        
        

    }
    public static function unLinkAccount($payload){
        if (!isset($payload->accountNo) || empty($payload->accountNo)) {
            return 'missing parameter accountNo';
        }
       /* if (!isset($payload->msisdn) || empty($payload->msisdn)) {
            return 'missing parameter msisdn';
        }*/
        if (!isset($payload->transid) || empty($payload->transid)) {
            return 'missing parameter transid';
        }

        if (!isset($payload->vendorType) || empty($payload->vendorType)) {
            return 'missing parameter vendorType';
        }

        if (!isset($payload->bankAccountNumber) || empty($payload->bankAccountNumber)) {
            if (!isset($payload->cardNumber) || empty($payload->cardNumber)) {
                return 'missing parameter either bankAccountNumber or cardNumber';
            }
        }
        if ($payload->vendorType==strtolower('bank') && (!isset($payload->bankAccountNumber) || empty($payload->bankAccountNumber))) {
            return 'missing parameter bankAccountNumber ';
        }
        return  $state = self::checkAccount($payload->accountNo); 
        

        
    }
    public static function cashout($payload) {
       
        if (!isset($payload->message) || empty($payload->message)) {
            return 'missing parameter message';
        }
        if (!isset($payload->transid) || empty($payload->transid)) {
            return 'missing parameter transid';
        }
        if (!isset($payload->amount) || empty($payload->amount)) {
            return 'missing parameter amount';
        }

          

    }
    public static function reverseTransaction($payload){
       

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
            return 'missing parameter transref. transref is the transaction id you would like to reverse';
        }
        if (!Validate::_checkTransid($payload->transref, $payload->msisdn)) {
            return 'this transaction does not exist';
        }
       return  $state = self::checkAccount($payload->accountNo); 
        //die($state);      
       
          

      
    }
    public static function pullFromCard($payload){
       

        if (!isset($payload->accountNo) || empty($payload->accountNo)) {          
            return 'missing parameter accountNo  ';
        }
        
        if (!isset($payload->transid) || empty($payload->transid)) { 
            return 'missing parameter transid';
        }
        if (!isset($payload->amount) || empty($payload->amount)) {
            return 'missing parameter amount';
        }
        if (!isset($payload->cardHolderName) || empty($payload->cardHolderName)) {
            return 'missing parameter cardHolderName';
        } 
        if (!isset($payload->cardNumber) || empty($payload->cardNumber)) {
            return 'missing parameter cardNumber';
        }
        if (!isset($payload->cardType) || empty($payload->cardType)) {
            return 'missing parameter cardType';
        }
        if (!isset($payload->exp) || empty($payload->exp)) {
            return 'missing parameter exp for expiry date in format mm/yy';
        }
        if (!isset($payload->cvv) || empty($payload->cvv)) {
            return 'missing parameter cvv, 3 digits from the back of your card';
        }
       if (!is_numeric($payload->cardNumber) || strlen($payload->cardNumber) != 16){
            return 'invalid card number format';
        }
           
            $expDate = explode('/',$payload->exp);
            if(!isset($expDate[1]) || $expDate[0]>12 )
             return 'invalid expiry date format of mm/yy '.$expDate[0];
            if(isset($expDate[1]) && $expDate[1]>99 )
             return 'invalid expiry date format of mm/yy '.$expDate[0].'/'.$expDate[1];            
         
         

        if('20'.$expDate[1]. str_pad($expDate[0],2,'0') < date('Ym')) {
                return 'card is expired';
            }
      
       
       return  $state = self::checkAccount($payload->accountNo); 
        //die($state);      
       
          

      
    }




}
