<?php

require_once ("jwt_encode.php");

$payload = '{
	"iss": "Selcom Transsnet API",
	"timestamp": "2018-07-06 12:14:33",
	"method": "openAccount",
	"requestParams": {
		"transid": "01052018161000",
		"firstName": "Nancy",
		"lastName": "Drew",
		"addressCity": "Mwanza",
		"addressCountry": "Tanzania",
		"dob": "1997-01-10",
		"currency": "TZS",
		"customerNo": "255754200200",
		"msisdn": "255754200200"
		
	}
}';



$bearer = Token::sign($payload);


$curl = curl_init(); 
curl_setopt_array($curl, array(
  CURLOPT_URL => "http://127.0.0.1/selcomJWT/",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => /*json_encode*/($payload),
  CURLOPT_HTTPHEADER => array(
    "content-type:application/json",
    "authorization: Bearer " . $bearer,
    "cache-control: no-cache",
    "content-type: application/json"
  ),
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  echo "cURL Error #:" . $err;
} else {
  echo $response;
}