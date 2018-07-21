<?php
//chdir(dirname(__DIR__));

include_once('vendor\custom\JWT.php');
include_once('config.php');
include_once('Validate.php');
include_once('DB.php');

$headers = apache_request_headers();
$err = array();
$body = (json_decode(file_get_contents('php://input')));
//die( print_r(($body->requestParams)));
$db = new DB();
if ($body){
        //Log Request
        $result = $db->incoming($body);

        //Check for Duplicate (if transId exists: reject)

        $err = Validate::valid($body);
        if (!empty($err) && $err!="" ){
                echo ('err:'.json_encode($err));
                return false;
        }
        //Verify Signature against client Public Key
        if ( Validate::verify($headers)) {
        
                $method = $body->method;
                $response = $db->transaction($body->requestParams,$method);
                print_r($response);
        }
}
else{
        print_r('invalid json format');
}
/*if (isset($body)) {
 
    //$payload = JWT::urlsafeB64Decode($request['base64Payload']);
  
   
    //$member = new Members();
    //$secretKey = $member->getSecret($request['requestId']);
    $publicKey = file_get_contents('./public.txt', true);           
    $data = openssl_pkey_get_public($publicKey); 
    $secretKey = openssl_pkey_get_details($data); 
    $txtHeader = '{"alg": "HS256","typ": "JWT"}';
    $txtPayload = $request;

    $base64Header = base64_encode($txtHeader);
    $base64Payload = base64_encode($txtPayload);

    $unsigned = $base64Header .'.'.$base64Payload;
    $algorithm = 'sha256';
    $hash = hash_hmac($algorithm, $unsigned, $secretKey, true);
    $jwt = $base64Header .'.'.$base64Payload.'.'.base64_encode($hash);
    die( print_r($jwt));
    ;

    
}
else{
    header('HTTP/1.0 400 Bad Request, missing parameters');
    echo('HTTP/1.0 400 Bad Request, missing parameters' );
} 
*/

?>
