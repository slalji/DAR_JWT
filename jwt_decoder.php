<?php
chdir(dirname(__DIR__));

include_once('vendor\custom\JWT.php');
include_once('vendor\custom\members.php');
include_once('config.php');


/*
 * Get all headers from the HTTP request
 */
$request = $_REQUEST;
$headers = apache_request_headers();

$body = (json_decode(file_get_contents('php://input')));
$publicKey = file_get_contents('public.txt', true);

if (isset($headers['Authorization'])) {
    
    $authHeader = $headers['Authorization'];
     
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
               
                /*
                 * get jwtKey from DB
                 */
                //$member = new Members();
                //$secretKey = $member->getSecret($request['request_id']);
               //print_r($secretKey = $request['request_id']);
                //$secretKey = '$2y$10$jOzDA1saNtPl5hji30iUQOjydhEl8VcJeIKDKZ9UyAijvHHQjv1XW';
                
                $payload = JWT::decode($bearer, $publicKey, array('RS256'));
              
              
               // sendToDoTransaction(json_encode($payload));
               $respArray = ['transId'=>rand(10000000,99999999),'reference'=>rand(1000000000,9999999999),'responseCode' => 200, "Message"=>"{response:'status':'SUCCESS','balance':'345000', 'method': $body->method}"];

               print_r(json_encode($respArray));
              

            } catch (Exception $e) {
                /*
                 * the token was not able to be decoded.
                 * this is likely because the signature was not able to be verified (tampered token)
                 */
                header('HTTP/1.0 401 Unauthorized');
            echo('HTTP/1.0 401 Unauthorized'/*.$e*/);
            }
        } else {
            /*
             * No token was able to be extracted from the authorization header
             */
            header('HTTP/1.0 400 Bad Request');
            echo('HTTP/1.0 400 Bad Request' );
        }
    } else {
        /*
         * The request lacks the authorization token
         */
        header('HTTP/1.0 400 Bad Request');
        echo 'Token not found in request' ;
    }
} else {
    header('HTTP/1.0 405 Method Not Allowed');
    echo 'HTTP/1.0 405 Method Not Allowed' ;
}
