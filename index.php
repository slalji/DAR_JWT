<?php
//chdir(dirname(__DIR__));

include_once('vendor\custom\JWT.php'); 
include_once('config.php');
include_once('Validate.php');
include_once('DB.php');

$headers = apache_request_headers();
$err = array();
$body = (json_decode(file_get_contents('php://input')));

$db = new DB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($body)){
        echo isset($body);  
        //Check for Duplicate (if transId exists: reject)

        $err = Validate::valid($body);
       
        if (!empty($err) && $err!="" ){
                
               /* //echo ('err:'.json_encode($err));
                $response = ["transid"=>$body->requestParams->transid,"reference"=>"","responseCode"=>"402","Message"=>["status"=>"ERROR","method"=>"","data"=>json_encode($err)]];
               // print_r($response);
                error_log("\r\n".date('Y-m-d H:i:s').' '.print_r($response), 3, "transsetlog.log");
                return json_encode($response);
                */
                $message = array();
                $message['status']="ERROR";
                $message['method']='';//.$e->getMessage()." : ";//.$sql;
                $result['resultcode'] ='402';
                $result['result']='Missing Parameters';
                $message['data']=$result;
                $respArray = ['transid'=>'','reference'=>'','responseCode' => 501, "Message"=>($message)];
                $response = json_encode($body);
                error_log("\r\n".date('Y-m-d H:i:s').' '.$response, 3, "transsetlog.log");
                return print_r( json_encode($respArray));
        }
        //Verify Signature against client Public Key
        if ( Validate::verify($headers)) {
        
                $result = $db->incoming($body);
                $method = $body->method;
                $response = $db->transaction($body->requestParams,$method);
                print_r($response);
                error_log("\r\n".date('Y-m-d H:i:s').' '.$response, 3, "transsetlog.log");
                return json_encode($response);

        }
        else{
                $message = array();
                $message['status']="ERROR";
                $message['method']='';//.$e->getMessage()." : ";//.$sql;
                $result['resultcode'] ='401';
                $result['result']='Authorization Failure';
                $message['data']=$result;
                $respArray = ['transid'=>'','reference'=>'','responseCode' => 501, "Message"=>($message)];
                $response = json_encode($body);
                error_log("\r\n".date('Y-m-d H:i:s').' '.$response, 3, "transsetlog.log");
                return print_r( json_encode($respArray));
        }
         //Log Request
        

}
else{
        $message = array();
        $message['status']="ERROR";
        $message['method']='';//.$e->getMessage()." : ";//.$sql;
        $result['resultcode'] ='412';
        if ($_SERVER['REQUEST_METHOD'] === 'POST')
                $result['result']='Invalid JSON Format or Missing Parameters';
        else if (!isset($body))
                $result['result']='Invalid JSON';
        else
                $result['result']='HTTP 401 NOT FOUND';    
        $message['data']=$result;
        
        $err = ["transid"=>"","reference"=>"","responseCode"=>"412","Message"=>["status"=>"ERROR","method"=>"","message"=>$message]];
        print_r(json_encode($err));
        //print_r('HTTP 401 NOT FOUND');
}


?>
