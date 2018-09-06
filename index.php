<?php
//error_reporting(0);
include_once('vendor\custom\JWT.php');
include_once('config.php');
include_once('Validate.php');
include_once('DB.php');

$headers = apache_request_headers();
$err = array();
$body = (json_decode(file_get_contents('php://input')));

//error_log(date('Y-m-d H:i:s').' 1: '.$_SERVER['REMOTE_ADDR'].' '.file_get_contents('php://input'), 3, "transsnet.log");

$db = new DB();
error_log("\r\n".date('Y-m-d H:i:s').' req: '.$_SERVER['REMOTE_ADDR'].' '.json_encode($body), 3, "transsnet.log");



        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($body)){

                $err = Validate::valid($body);

                if (!empty($err) && $err!="" ){

                        $message = array();
                        $message['status']="ERROR";
                       $message['description']='JSON VALIDATE';//.$e->getMessage()." : ";//.$sql;
                        $message['resultcode'] ='402';
                        $message['result']=$err;
                        
                /* $respArray = ['transid'=>'','reference'=>'','responseCode' => 501, "Message"=>($message)];
                        $response = json_encode($body);
                        error_log("\r\n".date('Y-m-d H:i:s').' invalid: '.$response, 3, "transsnet.log");
                        return print_r( json_encode($respArray));
                        */
                        _throwException($message);
                }
                //Verify Signature against client Public Key
                else if ( Validate::verify($headers)) {
                        
                        $res = $db->incoming($body);
                
                        if($res !== true){
                                $message = array();
                                $message['status']="ERROR";
                               $message['description']='SAVE RESPONSE';//.$e->getMessage()." : ";//.$sql;
                                $message['resultcode'] ='404';
                                $message['result']='Authorization Failure';
                        
                                _throwException($message);
                                /*error_log("\r\n".date('Y-m-d H:i:s').' '.$_SERVER['REMOTE_ADDR'].' incomingDB error:'.json_encode($body), 3, "transsnet.log");
                                $result['resultcode'] =501;
                                $result['result']='Invalid Format';
                                $message['data']=$res;
                                $respArray = ['transid'=>'','reference'=>'','responseCode' => 501, "Message"=>($message)];
                                //die (print_r( json_encode($respArray)));
                                return  print_r( json_encode($respArray));
                                */
                                
                        }
                        $message = array();
                        $message['status']="ERROR";
                        $message['description']='REQUEST TIMELIMIT';//.$e->getMessage()." : ";//.$sql;
                        $message['resultcode'] ='402';
                        $dateTime = new DateTime();
                        if (is_numeric($body->timestamp) )
                                $dateTime->setTimestamp($body->timestamp);
                        else{
                                $message['result']='invalid timestamp format, numerical unix only';
                                return _throwException($message);
                        }
                              

                        $today = new DateTime();

                        $since_start = $dateTime->diff($today);
                        $minutes = $since_start->days * 24 * 60;
                        $minutes += $since_start->h * 60;
                        $minutes += $since_start->i;
                         
                        if ($minutes > 5){
                                
                                $message['result']='exceed timelimit';                    
                                
                                _throwException($message);
                        }
                        else {
                                $method = $body->method;
                                $response = $db->transaction($body->requestParams,$method);
                                $json = json_encode($response);
                                print_r($response);
                                //if($body->method !== 'search')
                                        error_log("\r\n".date('Y-m-d H:i:s').' response: '.$_SERVER['REMOTE_ADDR'].' '.$response, 3, "transsnet.log");
                                return ($response);
                        }
                        
                
                }
                else{
                        $message = array();
                        $message['status']="ERROR";
                       $message['description']='JWT VALIDATE';//.$e->getMessage()." : ";//.$sql;
                        $message['resultcode'] ='401';
                        $message['result']='Authorization Failure';
                       
                        _throwException($message);

                } 
                /*error_log("\r\n".date('Y-m-d H:i:s').' auth_err: '.$response, 3, "transsnet.log");
                        return print_r( json_encode($respArray));
                //Log Request*/


        }
        else{
                //error_log("\r\n".date('Y-m-d H:i:s').' err_post_body: '.$_SERVER['REMOTE_ADDR'].' body:'.json_encode($body).' header:'.json_encode($headers), 3, "transsnet.log");
                $message = array();
                $message['status']="ERROR";
               $message['description']='INVALID FORMAT';//.$e->getMessage()." : ";//.$sql;
                $message['resultcode'] ='412';
                if ($_SERVER['REQUEST_METHOD'] === 'POST')
                        $message['result']='Invalid JSON Format or Missing Parameters code 106';
                else if (!isset($body))
                        $message['result']='Invalid JSON code 107 ';
                else
                        $message['result']='HTTP 401 NOT FOUND code 108';
                /*$message['data']=$result;

                $err = ["transid"=>"","reference"=>"","responseCode"=>"412","Message"=>["status"=>"ERROR","method"=>"","message"=>$message]];
                error_log("\r\n".date('Y-m-d H:i:s').' '.$_SERVER['REMOTE_ADDR'].' '.json_encode($result), 3, "transsnet.log");
                print_r(json_encode($result));
                //print_r('HTTP 401 NOT FOUND');
                */
                _throwException($message);
        }

        function _throwException($e){
               //die(print_r($e));
               
                error_log("\r\n".date('Y-m-d H:i:s').' error: '.$_SERVER['REMOTE_ADDR'].' '.json_encode($e), 3, "transsnet.log");
                print_r(json_encode($e));
                //print_r('HTTP 401 NOT FOUND');

        }



?>
