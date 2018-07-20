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
        $data = isset($err) ? $err :false;
    
        return ($err);
    }
    
    public static function verify($headers){
        if (isset($headers['Authorization']) || isset($headers['authorization'])) {
    
            $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : $headers['authorization'];
             
            /*
             * Look for the 'authorization' header
             */
            if ($authHeader) {
                /*
                 * Extract the jwt from the Bearer
                 */
                list($bearer) = sscanf( $authHeader, 'Bearer %s');
                $bearer = explode(',',$bearer)[0];
                $bearer = str_replace('"','',$bearer);
              
                if ($bearer) {
                    try {
                       
                        $publicKey = file_get_contents('public.txt', true);
                   // if JWT invalid throw exception
                        JWT::decode($bearer, $publicKey, array('RS256'));
                        return true;
                        
        
                    } catch (Exception $e) {
                        /*
                         * the token was not able to be decoded.
                         * this is likely because the signature was not able to be verified (tampered token)
                         */
                        header('HTTP/1.0 401 Unauthorized');
                        echo('HTTP/1.0 401 Unauthorized'/*.$e*/);
                        echo ' Caught exception: ',  $e->getMessage(), "\n";
                        }
                    } else {
                    /*
                     * No token was able to be extracted from the authorization header
                     */
                    header('HTTP/1.0 400 Bad Request');
                    echo('HTTP/1.0 400 Bad Request' );
                    echo ' Caught exception: ',  $e->getMessage(), "\n";
                }
            } else {
                /*
                 * The request lacks the authorization token
                 */
                header('HTTP/1.0 400 Bad Request');
                echo 'Token not found in request' ;
                echo ' Caught exception: ',  $e->getMessage(), "\n";
            }
        } else {
            header('HTTP/1.0 405 Method Not Allowed');
            echo 'HTTP/1.0 405 Method Not Allowed' ;
            echo ' Caught exception: ',  $e->getMessage(), "\n";
        }
        return false;
    }
    public static function check($acctNo){
        $db = new DB();
        $sql ="select id from accountProfile where accountNo='".$acctNo."'";
        $stmt = $db->conn->prepare( $sql );                     
        $stmt->execute();
        if ($stmt->rowCount() > 0)
            return true;
        return false;
        
    }
    public static function _getAccountNo($customerNo){
        $db = new DB();
        $sql ="select accountNo from accountProfile where customerNo ='$customerNo' || msisdn ='$customerNo' ";  

        $stmt = $db->conn->prepare( $sql );
        $stmt->execute();            
        $result = $stmt->fetchColumn();
        return $result;           
           
    }
    public static function _getSuspense($customerNo){
        //get accountNo;
        $db = new DB();
        $accountNo = Validate::_getAccountNo($customerNo);

        $sql ="select suspense from card where id ='$accountNo'";  

        $stmt = $db->conn->prepare( $sql );
        $stmt->execute();            
        $result = $stmt->fetchColumn();
        return $result;           
           
    }
    public static function _checkRef($payload){
        //get accountNo;
        $db = new DB();
        //$customerNo = $payload['customerNo'];
        $ref = $payload->reference;
       // $accountNo = Validate::_getAccountNo($customerNo);
       $flag=false;
        try{
           
            $sql ="select utilitycode, utilityref, dealer, amount from transaction where reference ='$ref'";  

            $stmt = $db->conn->prepare( $sql );
            $stmt->execute();            
            $rows = $stmt->fetchAll( PDO::FETCH_ASSOC );
            if(!$rows)
               return false;
            $result = ($rows[0]);
            //die(print_r($result));
            
            if($result['utilitycode'] == 'reserveAccount'){
                $flag=true;
            }
            else if ($result['utilityref'] == $payload->msisdn){
                $flag=true;
            }
            else if ($result['dealer']=='Transsnet'){
                $flag=true;
            }
            else if ($result['amount']==$payload->amount){
                $flag=true;
            }             
            return $flag;
        }
        catch(Exception $e){
            return false;
        }
       
                 
           
    }
    public static function setTinfo($payload){
       
        //get accountNo;
        $db = new DB();
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
            die(print_r($e->getMessage()));
            return false;
        }     
           
    }
    public static function checkTransid($transid){
       
        $data = isset($err) ? $err :false;
            $db = new DB();
            $sql ="select id, transid  from incoming where transid='".$transid."'";
            $stmt = $db->conn->prepare( $sql );                     
            $stmt->execute();
            $result = $stmt->fetchAll();
            if ($stmt->rowCount() > 1){
               return true;
            }
            else
                return false;
        
    }
    public static function openAccount($payload){
       
        $err = array();
        try{
       
            if (!isset($payload->transid) || empty($payload->transid)) {
                $err[]='transid may not be empty';
            }
            if (!isset($payload->customerNo) || empty($payload->customerNo)) {
                $err[]='customerNo may not be empty';
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
            
            $data = isset($err) ? $err :false;
            $db = new DB();
            $sql ="select id from accountProfile where customerNo='".$payload->customerNo."'";
            $stmt = $db->conn->prepare( $sql );                     
            $stmt->execute();
            if ($stmt->rowCount() > 0){
                $err[] ='Account already exists';
            }
        }
        catch(Exception $e){
            $err[]= $e->getMessage();
        }
        return ($err);
    }
    public static function updateAccount($payload) {
        $err = array();
        if (!isset($payload->accountNo) || empty($payload->accountNo)) {
            $err[]='accountNo may not be empty';
        }
        if (!isset($payload->transid) || empty($payload->transid)) {
            $err[]='transid may not be empty';
        }
       
        $data = isset($err) ? $err :false;
    
        return ($err);
    }
    public static function nameLookup($payload) {
        $err = array();
        if (!isset($payload->customerNo) || empty($payload->customerNo)) 
            if(!isset($payload->msisdn) || empty($payload->msisdn)) {
            $err[]='customerNo or msisdn may not be empty';
        }
        if (!isset($payload->transid) || empty($payload->transid)) {
            $err[]='transid may not be empty';
        }
       /* if(checkTransid($payload)){
            $err[]='duplicate transaction';
        }
        */
        $data = isset($err) ? $err :false;
    
        return ($err);
    }
    public static function transactionLookup($payload){
        $err = array();
        if (!isset($payload->accountNo) || empty($payload->accountNo)) {
            $err[]='accountNo may not be empty';
        }
        if (!isset($payload->transid) || empty($payload->transid)) {
            $err[]='transid may not be empty';
        }
        if (!isset($payload->transRef) || empty($payload->transRef)) {
            $err[]='transRef may not be empty. transRef is the transaction id you would like to lookup, where as transid is this current transaction';
        }
        
        $data = isset($err) ? $err :false;
    
        return ($err);
    }
    public static function transferFunds($payload)    {
        $err = array();
        if (!isset($payload->msisdn) || empty($payload->msisdn)) {
            $err[]='msisdn may not be empty';
        }
        if (!isset($payload->transid) || empty($payload->transid)) {
            $err[]='transid may not be empty';
        }        
        if (!isset($payload->toAccountNo) || empty($payload->toAccountNo)) {
            $err[]='toAccountNo may not be empty';
        }
        if (!isset($payload->amount) || empty($payload->amount)) {
            $err[]='amount may not be empty';
        }
        if (!isset($payload->currency) || empty($payload->currency)) {
            $err[]='currency may not be empty';
        }
      
        
        
        $data = isset($err) ? $err :false;
    
        return ($err);
    }
    public static function enquiry($payload) {
        $err = array();
        if (!isset($payload->customerNo) || empty($payload->customerNo)) 
            if(!isset($payload->msisdn) || empty($payload->msisdn)) {
            $err[]='customerNo or msisdn may not be empty';
        }
        if (!isset($payload->transid) || empty($payload->transid)) {
            $err[]='transid may not be empty';
        }
       /* if(self::checkTransid($payload->transid)){
            $err[]='duplicate transaction';
        }
        */
        $data = isset($err) ? $err :false;
    
        return ($err);
    }
    public static function accountState($payload) {
        $err = array();
        if (!isset($payload->customerNo) || empty($payload->customerNo)) 
            if(!isset($payload->msisdn) || empty($payload->msisdn)) {
            $err[]='customerNo or msisdn may not be empty';
        }
        if (!isset($payload->status) || empty($payload->status)) {
            $err[]='status may not be empty';
        }
        if (!isset($payload->transid) || empty($payload->transid)) {
            $err[]='transid may not be empty';
        }
        /*if(self::checkTransid($payload->transid)){
            $err[]='duplicate transaction';
        }
        */
        $data = isset($err) ? $err :false;
    
        return ($err);
    }
    public static function unReserveAccount($payload) {
        $err = array();
        if (!isset($payload->customerNo) || empty($payload->customerNo)) 
            if(!isset($payload->msisdn) || empty($payload->msisdn)) {
            return $err[]='customerNo or msisdn may not be empty';
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
        
        if(Validate::_getSuspense($payload->customerNo) == 0){
            $err[]='You do not have funds to release at this time';
        }
        if(!Validate::_checkRef($payload)){
            $err[]='reference is invalid';
        }
        $data = isset($err) ? $err :false;
    
        return ($data);
    }
    


}
