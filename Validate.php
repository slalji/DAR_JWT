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
    


}
