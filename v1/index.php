<?php
//chdir(dirname(__DIR__));

include_once('vendor\custom\JWT.php');
include_once('vendor\custom\members.php');
include_once('config.php');

$request = $_REQUEST;

if (isset($request['payload']) && isset($request['requestId'])) {
 
    //$payload = JWT::urlsafeB64Decode($request['base64Payload']);
  
   
    $member = new Members();
    $secretKey = $member->getSecret($request['requestId']);
    $txtHeader = '{"alg": "HS256","typ": "JWT"}';
    $txtPayload = $request['payload'];

    $base64Header = base64_encode($txtHeader);
    $base64Payload = base64_encode($txtPayload);

    $unsigned = $base64Header .'.'.$base64Payload;
    $algorithm = 'sha256';
    $hash = hash_hmac($algorithm, $unsigned, $secretKey, true);
    $jwt = $base64Header .'.'.$base64Payload.'.'.base64_encode($hash);
     print_r($jwt);

    
}
else{
    header('HTTP/1.0 400 Bad Request');
    echo('HTTP/1.0 400 Bad Request' );
} 

?>
