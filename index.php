<?php

//include_once('vendor\custom\JWT.php'); 
use Firebase\JWT;
include_once('config.php');
include_once('Validate.php');
include_once('DB.php');

$headers = apache_request_headers();
$err = array();
$body = (json_decode(file_get_contents('php://input')));

$db = new DB();
//error_log("\r\n".date('Y-m-d H:i:s').' '.$_SERVER['REMOTE_ADDR'].' '.json_encode($body), 3, "request.log");
        
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($body)){
       
        $err = Validate::valid($body);
       
        if (!empty($err) && $err!="" ){                
               
                $message = array();
                $message['status']="ERROR";
                $message['method']='';//.$e->getMessage()." : ";//.$sql;
                $result['resultcode'] ='402';
                $result['result']='Missing Parameters';
                $message['data']=$result;
                $respArray = ['transid'=>'','reference'=>'','responseCode' => 501, "Message"=>($message)];
                $response = json_encode($body);
                error_log("\r\n".date('Y-m-d H:i:s').' '.$response, 3, "request.log");
                return print_r( json_encode($respArray));
        }
        //Verify Signature against client Public Key
        if ( Validate::verify($headers)) {
               
                $result = $db->incoming($body);
                $method = $body->method;
                $response = $db->transaction($body->requestParams,$method);
                $json = json_encode($response);
                print_r($response);
                error_log("\r\n".date('Y-m-d H:i:s').' '.$_SERVER['REMOTE_ADDR'].' '.$response, 3, "request.log");
                return ($response);

        }
        else{
                $message = array();
                $message['status']="ERROR";
                $message['method']='';//.$e->getMessage()." : ";//.$sql;
                $result['resultcode'] ='404';
                $result['result']='Authorization Failure';
                $message['data']=$result;
                $respArray = ['transid'=>'','reference'=>'','responseCode' => 501, "Message"=>($message)];
                $response = json_encode($body);
               
        } error_log("\r\n".date('Y-m-d H:i:s').' 2 '.$response, 3, "request.log");
                return print_r( json_encode($respArray));
         //Log Request
        

}
else{
        error_log("\r\n".date('Y-m-d H:i:s').' '.$_SERVER['REMOTE_ADDR'].' body:'.json_encode($body).' header:'.json_encode($headers), 3, "request.log");
        $message = array();
        $message['status']="ERROR";
        $message['method']='';//.$e->getMessage()." : ";//.$sql;
        $result['resultcode'] ='412';
        if ($_SERVER['REQUEST_METHOD'] === 'POST')
                $result['result']='Invalid JSON Format or Missing Parameters code 106';
        else if (!isset($body))
                $result['result']='Invalid JSON code 107 '.$_SERVER['REMOTE_ADDR'].' '.json_encode($_POST).' '.json_encode($body);
        else
                $result['result']='HTTP 401 NOT FOUND code 108';    
        $message['data']=$result;
        
        $err = ["transid"=>"","reference"=>"","responseCode"=>"412","Message"=>["status"=>"ERROR","method"=>"","message"=>$message]];
        error_log("\r\n".date('Y-m-d H:i:s').' '.$_SERVER['REMOTE_ADDR'].' '.json_encode($result), 3, "request.log");
        print_r(json_encode($result));
        //print_r('HTTP 401 NOT FOUND');
}


?>
