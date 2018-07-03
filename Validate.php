<?php
require_once ("config.php");

/**
 * Validates Selcom API Payload of 
 * {
*	"iss": APP_NAME,
*	"method": "reserveAmount",
*	"timestamp": "1529998743",
*	"requestParams": {
*		"transId": "580929745048",
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

    public static function valid($payload)
    {
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
        if (!isset($payload->requestParams->transId) || empty($payload->requestParams->transId)) {
            $err[]='transId request paramater may not be empty ';
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


}
