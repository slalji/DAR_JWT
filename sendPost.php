<?php

require_once ("jwt_encode.php");
require_once ("DB.php");

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
		"customerNo": "255754200201",
		"msisdn": "255754200200"
		
	}
}';

$transferFunds='{
  "iss": "Selcom Transsnet API",
  "timestamp": "2018-07-06 12:14:33",
  "method": "fundTransfer",
  "requestParams": {
    "transid": "'.DB::getToken(12).'",
    "msisdn": "255789654700",
    "toAccountNo": "255754200200",
    "amount": "10",
    "currency": "TZS"
  }
}
';
$transfundsWithinfo='{
  "iss": "Selcom Transsnet API",
  "timestamp": "2018-07-06 12:14:33",
  "method": "fundTransfer",
  "requestParams": {
    "transid": "'.DB::getToken(12).'",
    "msisdn": "255789654700",
    "toAccountNo": "255754200200",
    "transtype": "fee",
    "geocode": {"lat":"-6.802353","lng":"39.279556"},
    "amount": "10",
    "currency": "TZS"
  }
}';

$nameLookup='{
  "iss": "Selcom Transsnet API",
  "timestamp": "2018-07-06 12:14:33",
  "method": "nameLookup",
  "requestParams": {
    "transid": "'.DB::getToken(12).'",
    "msisdn": "255789654700"
  }
}';
$transactionLookup='{ 
	"iss": "Selcom Transsnet API",
	"timestamp": "2018-07-06 12:14:33",
	"method": "transactionLookup",
	"requestParams": {
		"transid": "'.DB::getToken(12).'",
    "transref": "112217916212",
    "accountNo": "10"
	}
}';

$checkBalance='
{
  "iss": "Selcom Transsnet API",
  "timestamp": "2018-07-06 12:14:33",
  "method": "checkBalance",
  "requestParams": {
    "transid": "'.DB::getToken(12).'",
    "msisdn": "255789654555",
    "accountNo": "10"
  }
}

';
$getStatement='
{
  "iss": "Selcom Transsnet API",
  "timestamp": "2018-07-06 12:14:33",
  "method": "getStatement",
  "requestParams": {
    "transid": "'.DB::getToken(12).'",
    "msisdn": "255789654555",
    "customerNo": "255789654555"
  }
}';

$reserveAccount='
{
  "iss": "Selcom Transsnet API",
  "timestamp": "2018-07-06 12:14:33",
  "method": "reserveAccount",
  "requestParams": {
    "transid": "'.DB::getToken(12).'",
    "msisdn": "255789654700",
    "customerNo": "255789654700",
    "amount":"10",
    "currency":"TZS"
  }
}';
$unReserveAccount='
{
  "iss": "Selcom Transsnet API",
  "timestamp": "2018-07-06 12:14:33",
  "method": "unReserveAccount",
  "requestParams": {
    "transid": "'.DB::getToken(12).'",
    "msisdn": "255789654700",
    "reference":"201471103124",
    "customerNo": "255789654700",
    "amount":"10",
    "currency":"TZS"
  }
}';

$changeState ='
{
	"iss": "Selcom Transsnet API",
	"timestamp": "2018-07-06 12:14:33",
	"method": "changeStatus",
	"requestParams": {
		"transid": "01052018161500",
		"statustxt": "close",
		"customerNo": "255789654555",
		"msisdn": "255789654555"
		
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
  CURLOPT_POSTFIELDS => /*json_encode*/($unReserveAccount),
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