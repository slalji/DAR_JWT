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

/**
 *
 * PHP version 5
 *
 * @modal DB
 * @author   Salma Lalji
 **/
 
class Token {
    
    public static function sign($txtPayload){
       
        if (isset($txtPayload)) {
        
            $txtHeader = array();
            $txtHeader["alg"]="RS256";
            $txtHeader["typ"]="JWT";
            $txtHeader["iss"]="Selcom Transsnet";
            $txtHeader["sub"]="selcom@transsnet.net";
            $txtHeader["aud"]="selcomTransnet";
            $txtHeader["exp"]="24h";
            
        
            $privateKey = file_get_contents('./private.txt', true);
           
            $res = openssl_pkey_get_private($privateKey); 
            $secretKey = openssl_pkey_get_details($res); 
        
            $jwt = JWT::encode($txtPayload, $privateKey, "RS256",$txtHeader);
            //print_r($jwt);
            return $jwt;

            
        }
        else{
            header('HTTP/1.0 400 Bad Request');
            echo('HTTP/1.0 400 Bad Request'.($txtPayload) );
        } 
    }
}

?>
