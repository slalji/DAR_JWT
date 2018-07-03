<?php
//chdir(dirname(__DIR__));
/*
$l_sPrivateKey = 'something returned by database when user loged in';
$l_aData = array();

foreach($_POST as $key => $value){
 if($key == 'signature') continue;
 $l_aData[$key] = $value;
}

//This should then be the same as $_POST['signature'];
hash_hmac('sha256',serialize($l_aData),$l_sPrivateKey, false); 
*/
include_once('vendor\custom\JWT.php');
include_once('vendor\custom\members.php');
include_once('config.php');

$request = $_REQUEST;

if (isset($request)) {
 
    $txtHeader = array();
    $txtHeader["alg"]="RS256";
    $txtHeader["typ"]="JWT";
    $txtHeader["iss"]="Selcom Transsnet";
    $txtHeader["sub"]="selcom@transsnet.net";
    $txtHeader["aud"]="https://transset.selcom.net";
    $txtHeader["exp"]="24h";//(',: ,"iss"="Selcom Transsnet","sub":"selcom@transsnet.net","aud":"https://transset.selcom.net", "exp":"24h"}');
    $txtPayload = (json_decode(file_get_contents('php://input')));
   
    $privateKey = file_get_contents('./private.txt', true);
    //print_r($privateKey);
    $res = openssl_pkey_get_private($privateKey); 
    $secretKey = openssl_pkey_get_details($res); 
  
    $jwt = JWT::encode($txtPayload, $privateKey, "RS256",$txtHeader);
    print_r($jwt);

    
}
else{
    header('HTTP/1.0 400 Bad Request');
    echo('HTTP/1.0 400 Bad Request' );
} 

?>
